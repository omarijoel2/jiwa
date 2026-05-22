USE winners_selection;

DELIMITER $$

CREATE PROCEDURE `winners_selection`.`CheckWinner` (IN p_transaction_id BIGINT)
proc_exit: BEGIN
    -- Declare variables
    DECLARE v_short_code INT;
    DECLARE v_account VARCHAR(255);
    DECLARE v_amount DECIMAL(8,2);
    DECLARE v_msisdn VARCHAR(255);
    DECLARE v_cronjob_id BIGINT;
    DECLARE v_winnings DECIMAL(8,2);
    DECLARE v_total_transactions INT DEFAULT 0;
    DECLARE v_winning_percentage DECIMAL(5,2);
    DECLARE v_reset_every INT;
    DECLARE v_period_winners INT DEFAULT 0;
    DECLARE v_cronjob_name VARCHAR(255);
    DECLARE v_condition_name VARCHAR(255);
    DECLARE v_today DATE;
    DECLARE v_condition_id BIGINT;
    DECLARE v_amount_min DECIMAL(8,2);
    DECLARE v_amount_max DECIMAL(8,2);
    DECLARE v_random_value DECIMAL(10, 5);
    DECLARE v_probability DECIMAL(5, 2);
    DECLARE done INT DEFAULT FALSE;

    -- Cursor declaration
    DECLARE cur CURSOR FOR 
        SELECT id, name, amount_min, amount_max, winnings, winning_percentage, reset_every
        FROM winners_selection.winner_conditions 
        WHERE cronjob_id = v_cronjob_id 
        AND enabled = 1
        AND v_amount BETWEEN amount_min AND amount_max
        ORDER BY amount_min ASC;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    -- Set today's date
    SET time_zone = '+03:00';
    SET v_today = CURDATE();

    -- Get transaction details
    SELECT shortcode_id, account, amount, msisdn
    INTO v_short_code, v_account, v_amount, v_msisdn
    FROM mpesa_test.transactions
    WHERE id = p_transaction_id;

    -- Find the matching cronjob (only active jobs)
    SELECT id, name INTO v_cronjob_id, v_cronjob_name
    FROM winners_selection.cronjob_config
    WHERE shortcode = v_short_code
    AND account = v_account
    AND minimum_amount <= v_amount
    AND enabled = 1
    AND (start_date IS NULL OR v_today >= start_date)
    AND (end_date IS NULL OR v_today <= end_date)
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
        FETCH cur INTO v_condition_id, v_condition_name, v_amount_min, v_amount_max, v_winnings, v_winning_percentage, v_reset_every;
        
        IF done THEN 
            LEAVE read_loop;
        END IF;

        -- Get tracking data
        SELECT total_transactions, total_winners INTO v_total_transactions, v_period_winners
        FROM winners_selection.winner_tracking
        WHERE condition_id = v_condition_id
        AND cronjob_id = v_cronjob_id
        LIMIT 1;

        -- Reset if limit is reached
        IF v_total_transactions >= v_reset_every THEN
            SET v_total_transactions = 0;
            SET v_period_winners = 0;
        END IF;

        -- Adjust probability dynamically
        SET v_probability = (v_winning_percentage / 100) * ((v_reset_every - v_period_winners) / (v_reset_every - v_total_transactions + 1));

        -- Generate random value
        SET v_random_value = RAND();

        -- Determine winner
        IF v_random_value < v_probability THEN
            -- Update tracking
            INSERT INTO winners_selection.winner_tracking (condition_id, cronjob_id, total_transactions, total_winners, reset_every)
            VALUES (v_condition_id, v_cronjob_id, 1, 1, v_reset_every)
            ON DUPLICATE KEY UPDATE 
                total_transactions = total_transactions + 1, 
                total_winners = total_winners + 1, 
                last_updated = NOW();

            -- Insert into winners log
            INSERT INTO winners_selection.winners_log (msisdn, shortcode, keyword, cronjob_name, condition_name, amount_won)
            VALUES (v_msisdn, v_short_code, v_account, v_cronjob_name, v_condition_name, v_winnings);

            -- Insert into queue for unhashing msisdn
            -- INSERT INTO winners_selection.msisdn_queue (hashed_msisdn, shortcode, keyword, cronjob_name, condition_name, amount_won)
            -- VALUES (v_msisdn, v_short_code, v_account, v_cronjob_name, v_condition_name, v_winnings);

        ELSE
            -- Update only transaction count
            INSERT INTO winners_selection.winner_tracking (condition_id, cronjob_id, total_transactions, reset_every)
            VALUES (v_condition_id, v_cronjob_id, 1, v_reset_every)
            ON DUPLICATE KEY UPDATE 
                total_transactions = total_transactions + 1, 
                last_updated = NOW();
        END IF;

    END LOOP;

    -- Close cursor
    CLOSE cur;

END$$

DELIMITER ;
