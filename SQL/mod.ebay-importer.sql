INSERT INTO `ppb_settings`
(`name`, `value`) VALUES
  ('enable_ebay_importer', '');

ALTER TABLE `ppb_listings`
  ADD `bulk` TINYINT(4) NOT NULL
  AFTER `draft`;

ALTER TABLE `ppb_listings`
  ADD INDEX `active_approved_closed_deleted_draft_bulk` (`active`, `approved`, `closed`, `deleted`, `draft`, `bulk`),
  DROP INDEX `active_approved_closed_deleted_draft`;

# ebay import tool
ALTER TABLE `ppb_listings`
  ADD `ebay_item_id` BIGINT NULL;

ALTER TABLE `ppb_listings`
  ADD INDEX `ebay_item_id` (`ebay_item_id`);

# ebay categories sync plugin
CREATE TABLE `ppb_ebay_categories` (
  `id`               INT     NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name`             TEXT    NOT NULL,
  `category_id`      INT(11) NULL,
  `ebay_category_id` INT(11) NULL,
  FOREIGN KEY (`category_id`) REFERENCES `ppb_categories` (`id`)
    ON DELETE CASCADE
)
  ENGINE = 'InnoDB'
  COLLATE 'utf8_general_ci';

ALTER TABLE `ppb_ebay_categories`
  ADD INDEX `ebay_category_id` (`ebay_category_id`);

# ebay items sync plugin
ALTER TABLE `ppb_listings`
  ADD `ebay_sync_date` DATETIME NULL,
  COMMENT = '';

ALTER TABLE `ppb_listings`
  ADD INDEX `ebay_sync_date` (`ebay_sync_date`);

# ebay usernames sync
CREATE TABLE `ppb_ebay_users` (
  `id`               INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `ebay_username`    VARCHAR(255) NOT NULL,
  `ebay_token`       VARCHAR(255) NOT NULL,
  `ebay_marketplace` VARCHAR(255) NOT NULL,
  `user_id`          INT(11)      NULL,
  `ebay_sync_date`   DATETIME     NULL,
  `created_at`       DATETIME     NOT NULL,
  `updated_at`       DATETIME     NULL,
  FOREIGN KEY (`user_id`) REFERENCES `ppb_users` (`id`)
    ON DELETE CASCADE
)
  ENGINE = 'InnoDB'
  COLLATE 'utf8_general_ci';

ALTER TABLE `ppb_listings`
  ADD `ebay_user_id` INT(11) NULL,
  ADD FOREIGN KEY (`ebay_user_id`) REFERENCES `ppb_ebay_users` (`id`)
  ON DELETE SET NULL;

ALTER TABLE `ppb_ebay_users`
  ADD INDEX `ebay_username` (`ebay_username`);

ALTER TABLE `ppb_ebay_users`
  ADD INDEX `ebay_sync_date` (`ebay_sync_date`);

# @version 2.0
ALTER TABLE `ppb_listings`
  ADD `ebay_raw_data` TEXT COLLATE 'utf8_general_ci' NOT NULL,
  ADD `duplicate` TINYINT NOT NULL;