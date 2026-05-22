USE mpesa;

DELIMITER $$

CREATE TRIGGER `mpesa`.`after_transaction_insert`
AFTER INSERT ON `mpesa`.`transactions`
FOR EACH ROW
BEGIN
    CALL winners_selection.CheckWinner(NEW.id);
END$$

DELIMITER ;

-- old working=========================================================
USE winners_selection;

DELIMITER $$

CREATE TRIGGER reset_winner_tracking_after_update
AFTER UPDATE ON winners_selection.winner_conditions
FOR EACH ROW
BEGIN
    -- Reset tracking data when a condition is updated
    UPDATE winners_selection.winner_tracking
    SET total_transactions = 0, 
        total_winners = 0, 
        last_updated = NOW()
    WHERE condition_id = NEW.id;
END$$

DELIMITER ;

-- ================================================
USE winners_selection;

DELIMITER $$

CREATE TRIGGER after_insert_winner_condition
AFTER INSERT ON winners_selection.winner_conditions
FOR EACH ROW
BEGIN
    -- Insert a new tracking record when a condition is added
    INSERT INTO winners_selection.winner_tracking (
        condition_id, 
        total_transactions, 
        total_winners, 
        last_updated, 
        reset_every, 
        cronjob_id
    ) 
    VALUES (
        NEW.id, 
        0, 
        0, 
        NOW(), 
        NEW.reset_every, 
        NEW.cronjob_id
    );
END$$

DELIMITER ;

-- NEW WORKING=============================================================
USE winners_selection;

DELIMITER $$

CREATE TRIGGER reset_winner_tracking_after_update
AFTER UPDATE ON winners_selection.winner_conditions
FOR EACH ROW
BEGIN
    -- Reset tracking data when a condition is updated
    UPDATE winners_selection.winner_tracking
    SET total_transactions = 0, 
        total_winners = 0, 
        last_updated = NOW(),
        reset_every = NEW.reset_every
    WHERE condition_id = NEW.id;
END$$

DELIMITER ;

-- ====================================================
USE winners_selection;

DELIMITER $$

CREATE TRIGGER after_delete_winner_condition
AFTER DELETE ON winners_selection.winner_conditions
FOR EACH ROW
BEGIN
    -- Delete the tracking record when a condition is removed
    DELETE FROM winners_selection.winner_tracking 
    WHERE condition_id = OLD.id;
END$$

DELIMITER ;

-- =======================================================

-- create trigger for checking if msisdn already exists in contacts table, if exists, set is_checked_in_db to 1 and set is_unhashed to 1, and update unhash_queue.unhashed_msisdn 
-- else if not found in contacts table, set is_checked_in_db to 1 and set is_unhashed to 0
DELIMITER $$
CREATE TRIGGER check_msisdn_exists
AFTER INSERT ON winners_selection.unhash_queue
FOR EACH ROW
BEGIN
    IF NEW.is_checked_in_db = 0 THEN
        IF EXISTS (SELECT 1 FROM winners_selection.contacts WHERE contacts.hashed_msisdn = NEW.hashed_msisdn) THEN
            UPDATE winners_selection.unhash_queue SET is_checked_in_db = 1, is_unhashed = 1 WHERE id = NEW.id;
            -- also update the unhash_queue.unhashed_msisdn
            UPDATE winners_selection.unhash_queue SET unhashed_msisdn = (SELECT contacts.unhashed_msisdn FROM winners_selection.contacts WHERE contacts.hashed_msisdn = NEW.hashed_msisdn) WHERE id = NEW.id;
        ELSE
            UPDATE winners_selection.unhash_queue SET is_checked_in_db = 1, is_unhashed = 0 WHERE id = NEW.id;
        END IF;
    END IF;
END $$
DELIMITER ;

-- create trigger for checking if msisdn is_unhashed, if unhashed and if contact already exists do nothing, else insert into contacts table where msisdn and shortcode match 
DELIMITER $$

CREATE TRIGGER update_into_contacts
AFTER UPDATE ON winners_selection.unhash_queue
FOR EACH ROW
BEGIN
    IF NEW.is_unhashed = 1 AND OLD.is_unhashed = 0 THEN
        INSERT INTO winners_selection.contacts (unhashed_msisdn, shortcode)
        VALUES (NEW.unhashed_msisdn, NEW.shortcode)
        ON DUPLICATE KEY UPDATE
            hashed_msisdn = VALUES(hashed_msisdn),
            unhashed_msisdn = VALUES(unhashed_msisdn),
            shortcode = VALUES(shortcode),
            keyword = VALUES(keyword),
            customer_name = VALUES(customer_name);
    END IF;
END $$

DELIMITER ;


-- TODO: fix this trigger for sending sms 
-- create trigger to check if unhash_queue.is_sent = 1 on update, if is_sent = 1 then insert into transactions_queue attaching winners_log.id as FK
CREATE TRIGGER check_is_sent
    BEFORE UPDATE ON winners_selection.unhash_queue
    FOR EACH ROW
    BEGIN
        IF NEW.is_sent = 1 THEN
            INSERT INTO winners_selection.transactions_queue (unhash_queue_id, winners_log_id)
            VALUES (NEW.id, NEW.id);
        END IF;
    END
