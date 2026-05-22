-- =============================================================================
-- migration.sql
-- Run this against the winners_selection database to fix all schema issues.
-- =============================================================================

USE winners_selection;

-- -----------------------------------------------------------------------------
-- 1. Add missing columns to unhash_queue
--    is_sms_decode: tracks whether the SMS template has been formatted
--    sms:           stores the formatted SMS message ready to send
-- -----------------------------------------------------------------------------
ALTER TABLE winners_selection.unhash_queue
    ADD COLUMN IF NOT EXISTS sms TEXT NULL AFTER transaction_code,
    ADD COLUMN IF NOT EXISTS is_sms_decode TINYINT(1) NOT NULL DEFAULT 0 AFTER is_unhashed;

-- -----------------------------------------------------------------------------
-- 2. Add missing columns to cronjob_config
--    start_date / end_date:             date-range gate for campaigns
--    sms_template_winner:               template sent to winners
--    sms_template_participant:          template sent to non-winners
--
--    Supported placeholders in templates:
--      {username}        customer name
--      {sent_amount}     amount the customer sent
--      {winning_amount}  amount won
--      {keyword}         M-Pesa account / keyword
--      {paybill}         M-Pesa paybill shortcode
-- -----------------------------------------------------------------------------
ALTER TABLE winners_selection.cronjob_config
    ADD COLUMN IF NOT EXISTS start_date DATE NULL AFTER enabled,
    ADD COLUMN IF NOT EXISTS end_date DATE NULL AFTER start_date,
    ADD COLUMN IF NOT EXISTS sms_template_winner TEXT NULL AFTER end_date,
    ADD COLUMN IF NOT EXISTS sms_template_participant TEXT NULL AFTER sms_template_winner;

-- Set default SMS templates for any campaigns that don't have one yet
UPDATE winners_selection.cronjob_config
SET sms_template_winner = 'Congratulations {username}! You have won KES {winning_amount} for sending KES {sent_amount} to {keyword} paybill {paybill}. Your payout is being processed.'
WHERE sms_template_winner IS NULL OR sms_template_winner = '';

UPDATE winners_selection.cronjob_config
SET sms_template_participant = 'Thank you {username} for sending KES {sent_amount} to {keyword} paybill {paybill}. Keep sending for a chance to win!'
WHERE sms_template_participant IS NULL OR sms_template_participant = '';

-- -----------------------------------------------------------------------------
-- 3. Fix the after_transaction_insert trigger
--    Old trigger was on mpesa.transactions — this app writes to
--    winners_selection.transactions, so the trigger must live there.
-- -----------------------------------------------------------------------------
DROP TRIGGER IF EXISTS winners_selection.after_transaction_insert;

DELIMITER $$
CREATE TRIGGER `after_transaction_insert`
AFTER INSERT ON `winners_selection`.`transactions`
FOR EACH ROW
BEGIN
    CALL winners_selection.CheckWinner(NEW.id);
END$$
DELIMITER ;

-- -----------------------------------------------------------------------------
-- 4. Fix the update_into_contacts trigger
--    Original trigger was missing hashed_msisdn in the INSERT column list
--    which caused the ON DUPLICATE KEY UPDATE clause to fail.
-- -----------------------------------------------------------------------------
DROP TRIGGER IF EXISTS winners_selection.update_into_contacts;

DELIMITER $$
CREATE TRIGGER update_into_contacts
AFTER UPDATE ON winners_selection.unhash_queue
FOR EACH ROW
BEGIN
    IF NEW.is_unhashed = 1 AND OLD.is_unhashed = 0 THEN
        INSERT INTO winners_selection.contacts
            (hashed_msisdn, unhashed_msisdn, shortcode, keyword, customer_name)
        VALUES
            (NEW.hashed_msisdn, NEW.unhashed_msisdn, NEW.shortcode, NEW.keyword, NEW.customer_name)
        ON DUPLICATE KEY UPDATE
            unhashed_msisdn = VALUES(unhashed_msisdn),
            keyword         = VALUES(keyword),
            customer_name   = VALUES(customer_name);
    END IF;
END$$
DELIMITER ;

-- -----------------------------------------------------------------------------
-- 5. Fix and replace the CheckWinner stored procedure
--    Changes:
--      a) Reads from winners_selection.transactions (was mpesa_test.transactions)
--      b) Fetches customer_name and transaction_code from the transaction row
--      c) Inserts winners AND participants into unhash_queue (was commented out)
--      d) winners_log INSERT now includes all NOT NULL columns
--      e) SMS templates fetched from cronjob_config and stored in unhash_queue
-- -----------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS winners_selection.CheckWinner;

DELIMITER $$

CREATE PROCEDURE `winners_selection`.`CheckWinner` (IN p_transaction_id BIGINT)
proc_exit: BEGIN
    DECLARE v_short_code        INT;
    DECLARE v_account           VARCHAR(255);
    DECLARE v_amount            DECIMAL(8,2);
    DECLARE v_msisdn            VARCHAR(255);
    DECLARE v_customer_name     VARCHAR(120);
    DECLARE v_transaction_code  VARCHAR(255);
    DECLARE v_cronjob_id        BIGINT;
    DECLARE v_winnings          DECIMAL(8,2);
    DECLARE v_total_transactions INT DEFAULT 0;
    DECLARE v_winning_percentage DECIMAL(5,2);
    DECLARE v_reset_every       INT;
    DECLARE v_period_winners    INT DEFAULT 0;
    DECLARE v_cronjob_name      VARCHAR(255);
    DECLARE v_condition_name    VARCHAR(255);
    DECLARE v_today             DATE;
    DECLARE v_condition_id      BIGINT;
    DECLARE v_amount_min        DECIMAL(8,2);
    DECLARE v_amount_max        DECIMAL(8,2);
    DECLARE v_random_value      DECIMAL(10,5);
    DECLARE v_probability       DECIMAL(5,2);
    DECLARE v_sms_winner        TEXT;
    DECLARE v_sms_participant   TEXT;
    DECLARE done                INT DEFAULT FALSE;

    DECLARE cur CURSOR FOR
        SELECT id, name, amount_min, amount_max, winnings, winning_percentage, reset_every
        FROM winners_selection.winner_conditions
        WHERE cronjob_id = v_cronjob_id
          AND enabled    = 1
          AND v_amount BETWEEN amount_min AND amount_max
        ORDER BY amount_min ASC;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    SET time_zone = '+03:00';
    SET v_today   = CURDATE();

    -- Fetch transaction details from winners_selection.transactions
    SELECT shortcode_id,
           account,
           amount,
           msisdn,
           COALESCE(customer_name, ''),
           transaction_code
    INTO v_short_code, v_account, v_amount, v_msisdn, v_customer_name, v_transaction_code
    FROM winners_selection.transactions
    WHERE id = p_transaction_id;

    -- Find the matching active campaign
    SELECT id,
           name,
           COALESCE(sms_template_winner,     'Congratulations {username}! You won KES {winning_amount} for sending KES {sent_amount} to {keyword} paybill {paybill}.'),
           COALESCE(sms_template_participant, 'Thank you {username} for sending KES {sent_amount} to {keyword} paybill {paybill}. Keep sending for a chance to win!')
    INTO v_cronjob_id, v_cronjob_name, v_sms_winner, v_sms_participant
    FROM winners_selection.cronjob_config
    WHERE shortcode        = v_short_code
      AND account          = v_account
      AND minimum_amount   <= v_amount
      AND enabled          = 1
      AND (start_date IS NULL OR v_today >= start_date)
      AND (end_date   IS NULL OR v_today <= end_date)
    ORDER BY id ASC
    LIMIT 1;

    IF v_cronjob_id IS NULL THEN
        LEAVE proc_exit;
    END IF;

    OPEN cur;

    read_loop: LOOP
        FETCH cur INTO v_condition_id, v_condition_name,
                       v_amount_min, v_amount_max,
                       v_winnings, v_winning_percentage, v_reset_every;

        IF done THEN
            LEAVE read_loop;
        END IF;

        SELECT total_transactions, total_winners
        INTO   v_total_transactions, v_period_winners
        FROM   winners_selection.winner_tracking
        WHERE  condition_id = v_condition_id
          AND  cronjob_id   = v_cronjob_id
        LIMIT 1;

        IF v_total_transactions >= v_reset_every THEN
            SET v_total_transactions = 0;
            SET v_period_winners     = 0;
        END IF;

        SET v_probability  = (v_winning_percentage / 100)
                           * ((v_reset_every - v_period_winners)
                             / (v_reset_every - v_total_transactions + 1));
        SET v_random_value = RAND();

        IF v_random_value < v_probability THEN
            -- Winner path --
            INSERT INTO winners_selection.winner_tracking
                (condition_id, cronjob_id, total_transactions, total_winners, reset_every)
            VALUES
                (v_condition_id, v_cronjob_id, 1, 1, v_reset_every)
            ON DUPLICATE KEY UPDATE
                total_transactions = total_transactions + 1,
                total_winners      = total_winners      + 1,
                last_updated       = NOW();

            -- Log the winner (all NOT NULL columns included)
            INSERT INTO winners_selection.winners_log
                (msisdn, shortcode, keyword, cronjob_name, condition_name,
                 amount_won, amount_transacted, customer_name, transaction_code)
            VALUES
                (v_msisdn, v_short_code, v_account, v_cronjob_name, v_condition_name,
                 v_winnings, v_amount, v_customer_name, v_transaction_code);

            -- Queue for MSISDN unhashing → SMS → B2C payout
            INSERT INTO winners_selection.unhash_queue
                (hashed_msisdn, shortcode, keyword, amount_won, amount_transacted,
                 customer_name, transaction_code, sms, is_winner)
            VALUES
                (v_msisdn, v_short_code, v_account, v_winnings, v_amount,
                 v_customer_name, v_transaction_code, v_sms_winner, 1);

        ELSE
            -- Non-winner path: update counter only
            INSERT INTO winners_selection.winner_tracking
                (condition_id, cronjob_id, total_transactions, reset_every)
            VALUES
                (v_condition_id, v_cronjob_id, 1, v_reset_every)
            ON DUPLICATE KEY UPDATE
                total_transactions = total_transactions + 1,
                last_updated       = NOW();

            -- Queue for participation SMS (no payout)
            INSERT INTO winners_selection.unhash_queue
                (hashed_msisdn, shortcode, keyword, amount_won, amount_transacted,
                 customer_name, transaction_code, sms, is_winner)
            VALUES
                (v_msisdn, v_short_code, v_account, 0, v_amount,
                 v_customer_name, v_transaction_code, v_sms_participant, 0);
        END IF;

    END LOOP;

    CLOSE cur;

END$$

DELIMITER ;
