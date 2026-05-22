DROP TABLE winners_selection.unhash_queue_tasks;

CREATE TABLE winners_selection.unhash_queue_tasks (
                                                      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                                                      unhash_queue_id INT NOT NULL,
                                                      hashed_msisdn VARCHAR(255),
                                                      requires_processing TINYINT DEFAULT 1,
                                                      PRIMARY KEY (id)
);

DELIMITER $$
CREATE TRIGGER winners_selection.check_msisdn_exists
    AFTER INSERT ON winners_selection.unhash_queue
    FOR EACH ROW
BEGIN
    INSERT INTO winners_selection.unhash_queue_tasks (unhash_queue_id, hashed_msisdn)
    VALUES (NEW.id, NEW.hashed_msisdn);
END $$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE winners_selection.ProcessUnhashTasks()
BEGIN
    -- Join tasks with contacts and update the unhash_queue table
    UPDATE winners_selection.unhash_queue uq
        JOIN winners_selection.unhash_queue_tasks tasks ON uq.id = tasks.unhash_queue_id
        LEFT JOIN winners_selection.contacts c ON uq.hashed_msisdn = c.hashed_msisdn
    SET uq.is_checked_in_db = 1,
        uq.is_unhashed = CASE WHEN c.hashed_msisdn IS NOT NULL THEN 1 ELSE 0 END,
        uq.unhashed_msisdn = c.unhashed_msisdn
    WHERE tasks.requires_processing = 1;

    -- Mark tasks as processed
    UPDATE winners_selection.unhash_queue_tasks
    SET requires_processing = 0
    WHERE requires_processing = 1;
END $$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE ProcessUnhashTasks()
BEGIN
    -- Step 1: Join tasks with contacts and update the unhash_queue table
    UPDATE winners_selection.unhash_queue uq
    JOIN winners_selection.unhash_queue_tasks tasks ON uq.id = tasks.unhash_queue_id
    LEFT JOIN winners_selection.contacts c ON uq.hashed_msisdn = c.hashed_msisdn
    SET uq.is_checked_in_db = 1,
        uq.is_unhashed = CASE WHEN c.hashed_msisdn IS NOT NULL THEN 1 ELSE 0 END,
        uq.unhashed_msisdn = c.unhashed_msisdn
    WHERE tasks.requires_processing = 1;

    -- Step 2: Mark tasks as processed
    UPDATE winners_selection.unhash_queue_tasks
    SET requires_processing = 0
    WHERE requires_processing = 1;

    -- Step 3: Delete processed tasks
    DELETE FROM winners_selection.unhash_queue_tasks
    WHERE requires_processing = 0;
END $$
DELIMITER ;
