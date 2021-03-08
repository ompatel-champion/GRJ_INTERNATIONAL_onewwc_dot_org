SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

CREATE TABLE `{%TABLE_PREFIX%}newsletters_subscribers` (
  `id`         INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id`    INT(11)      NULL,
  `email`      VARCHAR(255) NOT NULL,
  `created_at` DATETIME     NOT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `{%TABLE_PREFIX%}users` (`id`)
    ON DELETE CASCADE
)
  ENGINE = 'InnoDB'
  COLLATE 'utf8_general_ci';

ALTER TABLE `{%TABLE_PREFIX%}newsletters_recipients`
  ADD `subscriber_id` INT(11) NULL
  AFTER `newsletter_id`;

ALTER TABLE `{%TABLE_PREFIX%}newsletters_recipients`
  ADD FOREIGN KEY (`subscriber_id`) REFERENCES `{%TABLE_PREFIX%}newsletters_subscribers` (`id`)
  ON DELETE CASCADE;

-- workaround because its complicated to remove the user_id foreign key, so wed rather set the user_id field as null from now on
ALTER TABLE `{%TABLE_PREFIX%}newsletters_recipients`
  CHANGE `user_id` `user_id` INT(11) NULL
  AFTER `subscriber_id`;

-- transfer subscribers to new table
INSERT INTO `{%TABLE_PREFIX%}newsletters_subscribers`
(`user_id`, `email`, `created_at`)
  SELECT
    `id`,
    `email`,
    NOW()
  FROM `{%TABLE_PREFIX%}users`
  WHERE `newsletter_subscription` = '1';

INSERT INTO `{%TABLE_PREFIX%}settings`
(`name`, `value`) VALUES
  ('newsletter_subscription_box', '');

ALTER TABLE `{%TABLE_PREFIX%}users`
  CHANGE `birthdate` `birthdate` DATE NULL
  AFTER `salt`;

ALTER TABLE `{%TABLE_PREFIX%}users`
  CHANGE `last_login` `last_login` DATETIME NULL
  AFTER `role`;

CREATE TABLE `{%TABLE_PREFIX%}cache` (
  `id`         INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(255) NOT NULL,
  `type`       VARCHAR(50)  NOT NULL,
  `data`       TEXT         NOT NULL,
  `created_at` DATETIME     NOT NULL
)
  ENGINE = 'InnoDB'
  COLLATE 'utf8_general_ci';

ALTER TABLE `{%TABLE_PREFIX%}cache`
  ADD INDEX `name_type` (`name`, `type`),
  ADD INDEX `created_at` (`created_at`);

ALTER TABLE `{%TABLE_PREFIX%}categories`
  ADD `adult` TINYINT(4) NOT NULL
  AFTER `custom_fees`;

INSERT INTO `{%TABLE_PREFIX%}settings`
(`name`, `value`) VALUES
  ('enable_adult_categories', ''),
  ('adult_categories_splash_page', ''),
  ('adult_categories_splash_page_content', '');

INSERT INTO `{%TABLE_PREFIX%}settings`
(`name`, `value`) VALUES
  ('lazy_load_images', '1');

ALTER TABLE `{%TABLE_PREFIX%}users`
  ADD `ip_address` VARCHAR(50)
COLLATE 'utf8_general_ci' NOT NULL
  AFTER `mail_activated`;