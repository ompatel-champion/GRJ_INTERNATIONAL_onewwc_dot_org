SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

INSERT INTO `{%TABLE_PREFIX%}settings`
    (`name`, `value`)
VALUES ('email_address_change_confirmation', '');

ALTER TABLE `{%TABLE_PREFIX%}listings`
    ADD `auto_relist_pending` tinyint(4) NOT NULL AFTER `relist_until_sold`;

ALTER TABLE `{%TABLE_PREFIX%}cache`
    ADD INDEX `type` (`type`);

ALTER TABLE `{%TABLE_PREFIX%}advertising`
    CHANGE `type` `type` enum ('image','code','html') COLLATE 'utf8_general_ci' NOT NULL AFTER `category_ids`;

ALTER TABLE `{%TABLE_PREFIX%}advertising`
    CHANGE `new_tab` `new_tab` tinyint(4) NOT NULL AFTER `type`;

INSERT INTO `{%TABLE_PREFIX%}settings`
    (`name`, `value`)
VALUES ('disable_store_categories', '');

ALTER TABLE `{%TABLE_PREFIX%}offers`
    ADD `sale_listing_id` int(11) NULL AFTER `receiver_id`;
ALTER TABLE `{%TABLE_PREFIX%}offers`
    ADD FOREIGN KEY (`sale_listing_id`) REFERENCES `{%TABLE_PREFIX%}sales_listings` (`id`) ON DELETE SET NULL;

INSERT INTO `{%TABLE_PREFIX%}payment_gateways` (`name`, `logo_path`, `type`, `order_id`, `site_fees`, `direct_payment`)
VALUES ('Coinbase', '/img/logos/coinbase.png', 1, '', '', '');

ALTER TABLE `{%TABLE_PREFIX%}transactions`
    ADD `coinbase_commerce_charge_id` varchar(255) COLLATE 'utf8_general_ci' NULL;