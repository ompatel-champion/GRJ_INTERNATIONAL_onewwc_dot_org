SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

INSERT INTO `{%TABLE_PREFIX%}settings`
    (`name`, `value`)
VALUES ('store_only_mode_disable_listings', '0');

INSERT INTO `{%TABLE_PREFIX%}settings`
    (`name`, `value`)
VALUES ('password_min_length', '6'),
       ('password_strength_settings', '');

ALTER TABLE `{%TABLE_PREFIX%}messaging`
    CHANGE `listing_id` `listing_id` int(11) NULL COMMENT 'set for private and public questions' AFTER `receiver_id`,
    ADD `private` tinyint NOT NULL COMMENT '1 = private question' AFTER `listing_id`;

ALTER TABLE `{%TABLE_PREFIX%}messaging`
    ADD INDEX `listing_id_private` (`listing_id`, `private`);

INSERT INTO `{%TABLE_PREFIX%}settings`
    (`name`, `value`)
VALUES ('enable_postmen', '0');

CREATE TABLE `{%TABLE_PREFIX%}postmen_shipper_accounts`
(
    `id`          int(11)      NOT NULL AUTO_INCREMENT,
    `user_id`     int(11)      NOT NULL,
    `description` varchar(255) NOT NULL,
    `slug`        varchar(50)  NOT NULL,
    `address`     text         NOT NULL,
    `type`        varchar(50)  NOT NULL,
    `created_at`  DATETIME     NOT NULL,
    `updated_at`  DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    CONSTRAINT `{%TABLE_PREFIX%}postmen_shipper_accounts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `{%TABLE_PREFIX%}users` (`id`) ON DELETE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

ALTER TABLE `{%TABLE_PREFIX%}listings`
    ADD `closing` tinyint(4) NOT NULL AFTER `closed`;

ALTER TABLE `{%TABLE_PREFIX%}tax_types`
    ADD `order_id` int NOT NULL;

ALTER TABLE `{%TABLE_PREFIX%}listings`
    ADD `tax_type_id` int(11) NULL AFTER `apply_tax`;

ALTER TABLE `{%TABLE_PREFIX%}listings`
    ADD FOREIGN KEY (`tax_type_id`) REFERENCES `{%TABLE_PREFIX%}tax_types` (`id`)
        ON DELETE SET NULL;

ALTER TABLE `{%TABLE_PREFIX%}postmen_shipper_accounts`
    ADD `details` text NOT NULL AFTER `user_id`,
    DROP `description`,
    DROP `slug`,
    DROP `address`,
    DROP `type`;