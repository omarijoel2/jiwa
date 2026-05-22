DROP PROCEDURE IF EXISTS `winners_selection`.`CheckWinner`;

DELIMITER $$

CREATE PROCEDURE `winners_selection`.`CheckWinner` (IN p_transaction_id BIGINT)
proc_exit: BEGIN
    -- Declare variables
    DECLARE v_short_code INT;
    DECLARE v_account VARCHAR(255);
    DECLARE v_amount DECIMAL(8,2);
    DECLARE v_msisdn VARCHAR(255);
    DECLARE v_customer_name VARCHAR(255);
    DECLARE v_transaction_code VARCHAR(255);
    DECLARE v_cronjob_id BIGINT;
    DECLARE v_winnings DECIMAL(8,2);
    DECLARE v_total_transactions INT DEFAULT 0;
    DECLARE v_winning_percentage DECIMAL(5,2);
    DECLARE v_reset_every INT;
    DECLARE v_period_winners INT DEFAULT 0;
    DECLARE v_condition_id BIGINT;
    DECLARE v_amount_min DECIMAL(8,2);
    DECLARE v_amount_max DECIMAL(8,2);
    DECLARE v_random_value DECIMAL(10, 5);
    DECLARE v_probability DECIMAL(5, 2);
    DECLARE v_expected_winners INT;
    DECLARE v_cronjob_name VARCHAR(255);
    DECLARE v_condition_name VARCHAR(255);
    DECLARE v_current_time TIME;
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_winning_message TEXT;
    DECLARE v_losing_message TEXT;

    -- Cursor declaration
    
    DECLARE cur CURSOR FOR 
        SELECT id, winner_conditions.name, amount_min, amount_max, winnings, winning_percentage, reset_every, winning_message, losing_message
        FROM winners_selection.winner_conditions 
        WHERE cronjob_id = v_cronjob_id 
        AND enabled = 1
        AND v_amount BETWEEN amount_min AND amount_max
        ORDER BY amount_min ASC;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    -- Set timezone
    SET time_zone = '+03:00';

    -- Get transaction details
    SELECT shortcode_id, account, amount, msisdn, customer_name, transaction_code, trans_time
    INTO v_short_code, v_account, v_amount, v_msisdn, v_customer_name, v_transaction_code, v_current_time
    FROM mpesa.transactions
    WHERE id = p_transaction_id;

    -- Find the matching cronjob (only active jobs)
    SELECT id, cronjob_config.name INTO v_cronjob_id, v_cronjob_name
    FROM winners_selection.cronjob_config
    WHERE shortcode = v_short_code
    AND (LOWER(REGEXP_REPLACE(cronjob_config.account, '[^a-zA-Z]', '')) LIKE CONCAT(LEFT(LOWER(REGEXP_REPLACE(v_account, '[^a-zA-Z]', '')),3),"%")
   OR LOWER(REGEXP_REPLACE(cronjob_config.account, '[^a-zA-Z]', '')) LIKE CONCAT("%",RIGHT(LOWER(REGEXP_REPLACE(v_account, '[^a-zA-Z]', '')),3))
   OR LOWER(REGEXP_REPLACE(cronjob_config.account, '[^a-zA-Z]', '')) LIKE CONCAT("%",LOWER(REGEXP_REPLACE(v_account, '[^a-zA-Z]', '')),"%") )
    AND minimum_amount <= v_amount
    AND enabled = 1
    ORDER BY id ASC
    LIMIT 1;

    -- Exit if no active cronjob found
    IF v_cronjob_id IS NULL THEN
        LEAVE proc_exit;
    END IF;

    -- Open the cursor
    OPEN cur;

    -- Loop through winner conditions
    read_loop: LOOP
        FETCH cur INTO v_condition_id, v_condition_name, v_amount_min, v_amount_max, v_winnings, v_winning_percentage, v_reset_every, v_winning_message, v_losing_message;
        
        IF done THEN 
            LEAVE read_loop;
        END IF;

        -- Get the total transactions processed
        SELECT total_transactions, total_winners INTO v_total_transactions, v_period_winners
        FROM winners_selection.winner_tracking
        WHERE condition_id = v_condition_id
        AND cronjob_id = v_cronjob_id
        LIMIT 1;

        -- Calculate expected winners
        SET v_expected_winners = FLOOR((v_winning_percentage / 100) * v_reset_every);

        -- Reset tracking if the reset limit is reached
        IF v_total_transactions >= v_reset_every THEN
            UPDATE winners_selection.winner_tracking
            SET total_transactions = 0, 
                total_winners = 0, 
                last_updated = NOW()
            WHERE condition_id = v_condition_id
            AND cronjob_id = v_cronjob_id;

            -- Reset variables
            SET v_total_transactions = 0;
            SET v_period_winners = 0;
        END IF;

        -- If winners have already reached the expected limit, set probability to zero
        IF v_period_winners >= v_expected_winners THEN
            SET v_probability = 0;
        ELSE
            -- Calculate probability dynamically
            -- SET v_probability = (v_winning_percentage / 100) * 
--                 IF(v_reset_every - v_period_winners > 0, 
--                    (v_reset_every - v_period_winners) / GREATEST(v_reset_every - v_total_transactions, 1), 
--                    0);
				SET v_probability = (v_winning_percentage / 100) * ((v_reset_every - v_period_winners) / (v_reset_every - v_total_transactions + 1));
        END IF;

        -- Generate a random value between 0 and 1
        SET v_random_value = RAND();

        -- Debugging: Log probability and random value
        -- INSERT INTO debug_log (random_value, probability, total_transactions, total_winners, reset_every, expected_winners)
--         VALUES (v_random_value, v_probability, v_total_transactions, v_period_winners, v_reset_every, v_expected_winners);

        -- Check if transaction qualifies as a winner
        IF v_random_value < v_probability THEN
            -- Insert or update winner tracking
            INSERT INTO winners_selection.winner_tracking (condition_id, cronjob_id, total_transactions, total_winners, reset_every)
            VALUES (v_condition_id, v_cronjob_id, 1, 1, v_reset_every)
            ON DUPLICATE KEY UPDATE 
                total_transactions = total_transactions + 1, 
                total_winners = total_winners + 1, 
                last_updated = NOW();

            -- Insert into winners log
            -- INSERT INTO winners_selection.winners_log (msisdn, shortcode, keyword, cronjob_name, condition_name, amount_won, amount_transacted, customer_name, transaction_code)
--             VALUES (v_msisdn, v_short_code, v_account, v_cronjob_id, v_condition_id, v_winnings, v_amount, v_customer_name, v_transaction_code);
			INSERT INTO winners_selection.winners_log (msisdn, shortcode, keyword, cronjob_name, condition_name, amount_won, amount_transacted, customer_name, transaction_code)
            VALUES (v_msisdn, v_short_code, v_account, v_cronjob_name, v_condition_name, v_winnings, v_amount, v_customer_name, v_transaction_code);
            
            -- Insert into queue for unhashing msisdn
            INSERT INTO winners_selection.unhash_queue (hashed_msisdn, shortcode, keyword, amount_won, amount_transacted, customer_name, transaction_code, is_winner, sms)
            VALUES (v_msisdn, v_short_code, v_account, v_winnings, v_amount, v_customer_name, v_transaction_code, 1, v_winning_message);
        ELSE
            -- Only update total transactions if not a winner
            INSERT INTO winners_selection.winner_tracking (condition_id, cronjob_id, total_transactions, total_winners, reset_every)
            VALUES (v_condition_id, v_cronjob_id, 1, 0, v_reset_every)
            ON DUPLICATE KEY UPDATE 
                total_transactions = total_transactions + 1, 
                last_updated = NOW();
                
			-- Insert into queue for unhashing msisdn
            INSERT INTO winners_selection.unhash_queue (hashed_msisdn, shortcode, keyword, amount_won, amount_transacted, customer_name, transaction_code, sms)
            VALUES (v_msisdn, v_short_code, v_account, v_winnings, v_amount, v_customer_name, v_transaction_code, v_losing_message);
        END IF;

    END LOOP;

    -- Close the cursor
    CLOSE cur;

END$$

DELIMITER ;
