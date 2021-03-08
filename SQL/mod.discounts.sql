CREATE TABLE `ppb_discount_rules` (
  `id`                 INT(11)                  NOT NULL AUTO_INCREMENT,
  `name`               VARCHAR(255)             NOT NULL,
  `description`        TEXT                     NOT NULL,
  `user_id`            INT(11)                           DEFAULT NULL
  COMMENT 'if user = null, the discount will apply to listings from all users',
  `expiration_date`    DATETIME                          DEFAULT NULL,
  `reduction_amount`   DECIMAL(16, 2)           NOT NULL,
  `reduction_type`     ENUM ('flat', 'percent') NOT NULL,
  `assigned_users`     TEXT                     NOT NULL,
  `assigned_roles`     TEXT                     NOT NULL
  COMMENT 'assign to user roles (buyer, seller, etc)',
  `assigned_listings`  TEXT                     NOT NULL,
  `conditions`         TEXT                     NOT NULL,
  `priority`           INT(11)                  NOT NULL
  COMMENT 'higher number = higher priority',
  `stop_further_rules` TINYINT(4)               NOT NULL
  COMMENT 'stop further rules from applying',
  `active`             TINYINT(4)               NOT NULL,
  `created_at`         DATETIME                 NOT NULL,
  `updated_at`         DATETIME                          DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `ppb_discount_rules_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `ppb_users` (`id`)
    ON DELETE CASCADE
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

ALTER TABLE `ppb_discount_rules`
  ADD INDEX `user_id_active_priority` (`user_id`, `active`, `priority`);

-- @version 2.0
ALTER TABLE `ppb_discount_rules`
  ADD `start_date` DATETIME NULL
  AFTER `user_id`,
  DROP `assigned_users`,
  DROP `assigned_roles`,
  DROP `assigned_listings`;

ALTER TABLE `ppb_listings`
  ADD `enable_discount_rule` TINYINT(4) NOT NULL,
  ADD `discount_start_date` DATETIME DEFAULT NULL,
  ADD `discount_expiration_date` DATETIME DEFAULT NULL,
  ADD `discount_reduction_amount` DECIMAL(16, 2) NOT NULL,
  ADD `discount_reduction_type` ENUM ('flat', 'percent') NOT NULL,
  ADD `discount_stop_further_rules` TINYINT(4) NOT NULL;