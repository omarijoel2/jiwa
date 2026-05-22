USE winners_selection;

-- =============================================================================
-- Trigger: after_transaction_insert
-- Fires after every new transaction is saved by the C2B callback.
-- Calls CheckWinner to evaluate if this customer is a winner.
-- Note: previously pointed to mpesa.transactions — corrected to
--       winners_selection.transactions to match what the app writes to.
-- =============================================================================
DELIMITER $$

CREATE TRIGGER `after_transaction_insert`
AFTER INSERT ON `winners_selection`.`transactions`
FOR EACH ROW
BEGIN
    CALL winners_selection.CheckWinner(NEW.id);
END$$

DELIMITER ;

-- =============================================================================
-- Trigger: reset_winner_tracking_after_update
-- Resets tracking counters when a winning condition is modified.
-- =============================================================================
DELIMITER $$

CREATE TRIGGER reset_winner_tracking_after_update
AFTER UPDATE ON winners_selection.winner_conditions
FOR EACH ROW
BEGIN
    UPDATE winners_selection.winner_tracking
    SET total_transactions = 0,
        total_winners      = 0,
        last_updated       = NOW(),
        reset_every        = NEW.reset_every
    WHERE condition_id = NEW.id;
END$$

DELIMITER ;

-- =============================================================================
-- Trigger: after_insert_winner_condition
-- Auto-creates a tracking row whenever a new winning condition is added.
-- =============================================================================
DELIMITER $$

CREATE TRIGGER after_insert_winner_condition
AFTER INSERT ON winners_selection.winner_conditions
FOR EACH ROW
BEGIN
    INSERT INTO winners_selection.winner_tracking
        (condition_id, total_transactions, total_winners, last_updated, reset_every, cronjob_id)
    VALUES
        (NEW.id, 0, 0, NOW(), NEW.reset_every, NEW.cronjob_id);
END$$

DELIMITER ;

-- =============================================================================
-- Trigger: after_delete_winner_condition
-- Cleans up tracking rows when a winning condition is deleted.
-- =============================================================================
DELIMITER $$

CREATE TRIGGER after_delete_winner_condition
AFTER DELETE ON winners_selection.winner_conditions
FOR EACH ROW
BEGIN
    DELETE FROM winners_selection.winner_tracking
    WHERE condition_id = OLD.id;
END$$

DELIMITER ;

-- =============================================================================
-- Trigger: check_msisdn_exists
-- After a new row is inserted into unhash_queue, check the contacts table to
-- see if we already know this hashed MSISDN. If yes, mark as unhashed
-- immediately (no external API call needed).
-- =============================================================================
DELIMITER $$

CREATE TRIGGER check_msisdn_exists
AFTER INSERT ON winners_selection.unhash_queue
FOR EACH ROW
BEGIN
    IF NEW.is_checked_in_db = 0 THEN
        IF EXISTS (
            SELECT 1 FROM winners_selection.contacts
            WHERE contacts.hashed_msisdn = NEW.hashed_msisdn
        ) THEN
            UPDATE winners_selection.unhash_queue
            SET is_checked_in_db = 1,
                is_unhashed      = 1,
                unhashed_msisdn  = (
                    SELECT contacts.unhashed_msisdn
                    FROM winners_selection.contacts
                    WHERE contacts.hashed_msisdn = NEW.hashed_msisdn
                    LIMIT 1
                )
            WHERE id = NEW.id;
        ELSE
            UPDATE winners_selection.unhash_queue
            SET is_checked_in_db = 1,
                is_unhashed      = 0
            WHERE id = NEW.id;
        END IF;
    END IF;
END$$

DELIMITER ;

-- =============================================================================
-- Trigger: update_into_contacts
-- After a row in unhash_queue is marked as unhashed (is_unhashed flips 0→1),
-- persist the decoded MSISDN into the contacts lookup table.
-- Fixed: original trigger was missing hashed_msisdn in the INSERT column list.
-- =============================================================================
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
