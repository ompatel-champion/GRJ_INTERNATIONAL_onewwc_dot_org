## MOD:- BANK TRANSFER
CREATE TABLE `ppb_banks` (
  `id`       INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name`     VARCHAR(255) NOT NULL,
  `order_id` INT          NOT NULL
) ENGINE='InnoDB';

CREATE TABLE `ppb_bank_accounts` (
  `id`      INT     NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT(11) NULL
  COMMENT 'if NULL, we have an admin bank account',
  `account` TEXT    NOT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `ppb_users` (`id`)
    ON DELETE CASCADE
) ENGINE='InnoDB';

INSERT INTO `ppb_payment_gateways` (`name`, `logo_path`, `type`)
VALUES ('BankTransfer', '/img/logos/bank-transfer.png', 'online');


CREATE TABLE `ppb_bank_transfers` (
  `id`                     INT                                              NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `transaction_id`         INT(11)                                          NOT NULL,
  `bank_account_id`        INT(11)                                          NULL,
  `account_holder_name`    VARCHAR(255)                                     NOT NULL,
  `transfer_type`          VARCHAR(255)                                     NOT NULL,
  `transfer_date`          DATETIME                                         NOT NULL,
  `reference_number`       VARCHAR(255)                                     NOT NULL,
  `additional_information` TEXT                                             NOT NULL,
  `transfer_status`        ENUM('pending', 'paid', 'declined', 'cancelled') NOT NULL DEFAULT 'pending',
  `created_at`             DATETIME                                         NOT NULL,
  `updated_at`             DATETIME                                         NOT NULL,
  FOREIGN KEY (`transaction_id`) REFERENCES `ppb_transactions` (`id`)
    ON DELETE CASCADE,
  FOREIGN KEY (`bank_account_id`) REFERENCES `ppb_bank_accounts` (`id`)
    ON DELETE SET NULL
) ENGINE='InnoDB';
