-- test database to simulate production environment for the mpesa system
-- CREATE DATABASE IF NOT EXISTS mpesa_test;
-- USE mpesa_test;

-- CREATE TABLE `transactions` (
--     `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
--     `account` VARCHAR(255) NOT NULL,
--     `transaction_code` VARCHAR(255) NOT NULL,
--     `shortcode_id` INT UNSIGNED NOT NULL,
--     `msisdn` VARCHAR(255) NOT NULL,
--     `amount` DECIMAL(8,2) NOT NULL,
--     `trans_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     PRIMARY KEY (`id`)
-- );
CREATE TABLE `transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `account` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `transaction_code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `shortcode_id` int unsigned NOT NULL,
  `msisdn` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `customer_name` varchar(120) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `has_notified` int NOT NULL DEFAULT '0',
  `channel` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `source` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `trans_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `amount` decimal(8,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1333800 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;


CREATE DATABASE IF NOT EXISTS winners_selection;
USE winners_selection;

CREATE TABLE winners_selection.shortcodes (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `shortcode` INT UNSIGNED NOT NULL,
    `shortcode_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`)
);

-- Table: cronjob_config (Stores cronjob settings)

CREATE TABLE winners_selection.cronjob_config (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `shortcode` INT UNSIGNED NOT NULL,
    `account` VARCHAR(255) NOT NULL,
    `minimum_amount` DECIMAL(8,2) NOT NULL,
    `enabled` BOOLEAN DEFAULT TRUE,
    PRIMARY KEY (`id`)
);

-- Winner Tracking (Tracks Transactions Per Condition)
CREATE TABLE winners_selection.winner_tracking (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    condition_id BIGINT UNSIGNED NOT NULL,
    cronjob_id BIGINT UNSIGNED NOT NULL,
    total_transactions INT DEFAULT 0,
    total_winners INT DEFAULT 0,
    reset_every INT NOT NULL DEFAULT 10,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (condition_id) REFERENCES winners_selection.winner_conditions(id) ON DELETE CASCADE,
    FOREIGN KEY (cronjob_id) REFERENCES winners_selection.cronjob_config(id) ON DELETE CASCADE,
    UNIQUE (condition_id, cronjob_id)
);

-- Winners Log (Stores Winners)
-- CREATE TABLE winners_selection.winners_log (
--     id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
--     msisdn VARCHAR(255) NOT NULL,
--     shortcode INT UNSIGNED NOT NULL,
--     keyword VARCHAR(255) NOT NULL,
--     cronjob_name VARCHAR(255) NOT NULL,
--     condition_name VARCHAR(255) NOT NULL,
--     amount_won DECIMAL(8,2) NOT NULL,
--     timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     PRIMARY KEY (id)
-- );

-- TODO: CURRENT UPDATED WINNERS LOG
CREATE TABLE winners_selection.winners_log (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    msisdn VARCHAR(500) NOT NULL,
    shortcode INT UNSIGNED NOT NULL,
    keyword VARCHAR(255) NOT NULL,
    cronjob_name VARCHAR(255) NOT NULL,
    condition_name VARCHAR(255) NOT NULL,
    amount_won DECIMAL(8,2) NOT NULL,
    amount_transacted DECIMAL(8,2) NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    transaction_code VARCHAR(255) NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);



-- Winner Conditions (Defines How Winners Are Chosen)
CREATE TABLE winners_selection.winner_conditions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    cronjob_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    amount_min DECIMAL(8,2) NOT NULL,
    amount_max DECIMAL(8,2) NOT NULL,
    winnings DECIMAL(8,2) NOT NULL,
    winning_percentage DECIMAL(5,2) NOT NULL DEFAULT 40.00,  -- Chance of winning
    reset_every INT NOT NULL DEFAULT 10,  -- Reset count after N transactions
    enabled TINYINT(1) DEFAULT 1,
    PRIMARY KEY (id),
    FOREIGN KEY (cronjob_id) REFERENCES winners_selection.cronjob_config(id) ON DELETE CASCADE
);

-- queue for unhashing msisdn
-- INSERT INTO winners_selection.unhash_queue (hashed_msisdn, shortcode, keyword, amount_won, amount_transacted, customer_name, transaction_code)
            -- VALUES (v_msisdn, v_short_code, v_account, v_winnings, v_amount, v_customer_name, v_transaction_code);
CREATE TABLE winners_selection.unhash_queue (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    hashed_msisdn VARCHAR(255) NOT NULL,
    unhashed_msisdn VARCHAR(255) DEFAULT NULL,
    shortcode INT UNSIGNED NOT NULL,
    keyword VARCHAR(255) NOT NULL,
    amount_won DECIMAL(8,2) NOT NULL,
    amount_transacted DECIMAL(8,2) NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    transaction_code VARCHAR(255) NOT NULL,
    is_checked_in_db TINYINT(1) DEFAULT 0, -- 0 = not checked in db, 1 = checked in db
    is_unhashed TINYINT(1) DEFAULT 0, -- 0 = not unhashed, 1 = unhashed
    is_sent_sms TINYINT(1) DEFAULT 0, -- 0 = not sent, 1 = sent
    is_winner TINYINT(1) DEFAULT 0, -- 0 = not winner, 1 = winner
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);





-- create contacts table
CREATE TABLE winners_selection.contacts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    hashed_msisdn VARCHAR(255) NOT NULL,
    unhashed_msisdn VARCHAR(255) NOT NULL,
    shortcode INT UNSIGNED NOT NULL,
    keyword VARCHAR(255) NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);

create table transactions
(
    id bigint unsigned auto_increment primary key,
    account          varchar(255)                        not null,
    transaction_code varchar(255)                        not null,
    type             varchar(255)                        not null,
    shortcode_id     int unsigned                        not null,
    msisdn           varchar(255)                        not null,
    customer_name    varchar(120)                        null,
    trans_time       timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    amount           decimal(8, 2)                       not null,
    created_at       timestamp                           null
);





-- users table
CREATE TABLE winners_selection.users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    remember_token VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);

-- roles table
CREATE TABLE winners_selection.roles (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) UNIQUE NOT NULL,
    description TEXT NULL,
    PRIMARY KEY (id)
);

-- permissions table
CREATE TABLE permissions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) UNIQUE NOT NULL,
    description TEXT NULL,
    PRIMARY KEY (id)
);

-- role_permissions table
CREATE TABLE role_permissions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    role_id BIGINT UNSIGNED NOT NULL,
    permission_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
);


-- custom user permissions
CREATE TABLE user_permissions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    permission_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
);

