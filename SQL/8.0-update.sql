SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

ALTER TABLE `{%TABLE_PREFIX%}content_sections`
  ADD `handle` VARCHAR(255) COLLATE 'utf8_general_ci' NOT NULL
  AFTER `name`,
  ADD `type` ENUM ('single', 'multiple', 'tree') COLLATE 'utf8_general_ci' NOT NULL DEFAULT 'single'
  AFTER `handle`,
  ADD `locale` VARCHAR(50) COLLATE 'utf8_general_ci' NOT NULL
  AFTER `type`,
  ADD `uri` TEXT COLLATE 'utf8_general_ci' NOT NULL
  AFTER `slug`,
  ADD `active` TINYINT NOT NULL
  AFTER `uri`,
  DROP `menu_id`,
  CHANGE `order_id` `order_id` INT(11) NULL
  AFTER `parent_id`,
  DROP `meta_title`,
  DROP `meta_description`;

UPDATE `{%TABLE_PREFIX%}content_sections`
SET `type` = 'tree'
WHERE `parent_id` IS NOT NULL;

SET @treeIds = (SELECT GROUP_CONCAT(`parent_id`)
                FROM `{%TABLE_PREFIX%}content_sections`
                WHERE `parent_id` IS NOT NULL);

UPDATE `{%TABLE_PREFIX%}content_sections`
SET `type` = 'tree'
WHERE find_in_set(`id`, @treeIds);

ALTER TABLE `{%TABLE_PREFIX%}content_pages`
RENAME TO `{%TABLE_PREFIX%}content_entries`;

ALTER TABLE `{%TABLE_PREFIX%}content_entries`
  ADD `short_description` TEXT COLLATE 'utf8_general_ci' NOT NULL
  AFTER `title`,
  CHANGE `content` `content` TEXT COLLATE 'utf8_general_ci' NOT NULL
  AFTER `short_description`,
  ADD `image_path` TEXT COLLATE 'utf8_general_ci' NOT NULL
  AFTER `content`,
  ADD `type` ENUM ('standard', 'post') COLLATE 'utf8_general_ci' NOT NULL DEFAULT 'standard'
  AFTER `image_path`,
  CHANGE `language` `locale` VARCHAR(255) COLLATE 'utf8_general_ci' NOT NULL
  AFTER `type`,
  CHANGE `slug` `slug` VARCHAR(255) COLLATE 'utf8_general_ci' NOT NULL
  AFTER `locale`,
  ADD `meta_title` VARCHAR(255) COLLATE 'utf8_general_ci' NOT NULL
  AFTER `slug`,
  ADD `meta_description` TEXT COLLATE 'utf8_general_ci' NOT NULL
  AFTER `meta_title`,
  ADD `user_id` INT(11) NULL
  AFTER `meta_description`,
  ADD `draft` TINYINT NOT NULL
  AFTER `order_id`,
  ADD `post_date` DATETIME NULL
  AFTER `draft`,
  ADD `expiry_date` DATETIME NULL
  AFTER `post_date`;

ALTER TABLE `{%TABLE_PREFIX%}content_entries`
  ADD FOREIGN KEY (`user_id`) REFERENCES `{%TABLE_PREFIX%}users` (`id`)
  ON DELETE SET NULL;

UPDATE `{%TABLE_PREFIX%}settings`
SET `value` = 'eight'
WHERE `name` = 'default_theme';

INSERT INTO `{%TABLE_PREFIX%}settings`
(`name`, `value`) VALUES
  ('enable_short_description', '1'),
  ('short_description_character_length', '');

ALTER TABLE `{%TABLE_PREFIX%}listings`
  CHANGE `subtitle` `short_description` text COLLATE 'utf8_general_ci' NOT NULL
  AFTER `name`;

ALTER TABLE `{%TABLE_PREFIX%}listings`
  ADD `relist_until_sold` tinyint(4) NOT NULL
  AFTER `auto_relist_sold`;

ALTER TABLE `{%TABLE_PREFIX%}custom_fields`
  ADD `alias` varchar(255) COLLATE 'utf8_general_ci' NOT NULL
  AFTER `element`;

ALTER TABLE `{%TABLE_PREFIX%}listings`
  CHANGE `listing_type` `listing_type` ENUM ('auction', 'product', 'wanted', 'reverse', 'first_bidder', 'classified')
COLLATE 'utf8_general_ci' NOT NULL DEFAULT 'auction'
  AFTER `id`;

INSERT INTO `{%TABLE_PREFIX%}settings`
(`name`, `value`) VALUES
  ('enable_classifieds', '0');

CREATE TABLE `{%TABLE_PREFIX%}content_menus` (
  `id`      int          NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name`    varchar(255) NOT NULL,
  `handle`  varchar(255) NOT NULL,
  `content` text         NOT NULL
)
  ENGINE = 'InnoDB'
  COLLATE 'utf8_general_ci';

ALTER TABLE `{%TABLE_PREFIX%}content_sections`
  ADD `entry_view_file` varchar(500) COLLATE 'utf8_general_ci' NOT NULL
  AFTER `active`;

INSERT INTO `{%TABLE_PREFIX%}settings`
(`name`, `value`) VALUES
  ('enable_listing_updates', '0');


ALTER TABLE `{%TABLE_PREFIX%}advertising`
  ADD `image_title` text COLLATE 'utf8_general_ci' NOT NULL
  AFTER `content`,
  ADD `direct_link` tinyint NOT NULL
  AFTER `url`;

ALTER TABLE `{%TABLE_PREFIX%}sales`
  ADD `payment_method_id` int(11) NULL;

INSERT INTO `{%TABLE_PREFIX%}settings`
(`name`, `value`) VALUES
  ('private_site_request_seller_privileges', '0');

ALTER TABLE `{%TABLE_PREFIX%}users`
  ADD `request_selling_privileges` tinyint(4) NOT NULL
  AFTER `is_seller`;

INSERT INTO `{%TABLE_PREFIX%}settings`
(`name`, `value`) VALUES
  ('enable_listings_sharing', '1'),
  ('enable_email_friend', '1'),
  ('enable_social_media_widget', '0'),
  ('enable_social_media_user', '0'),
  ('enable_rss', '1');

INSERT INTO `{%TABLE_PREFIX%}settings`
(`name`, `value`) VALUES
  ('social_media_user_type', 'all');

INSERT INTO `{%TABLE_PREFIX%}settings`
(`name`, `value`) VALUES
  ('favicon', 'favicon.png');

INSERT INTO `{%TABLE_PREFIX%}settings`
(`name`, `value`) VALUES
  ('enable_hpfeat', '1'),
  ('enable_catfeat', '1'),
  ('enable_highlighted', '1');

INSERT INTO `{%TABLE_PREFIX%}settings`
(`name`, `value`) VALUES
  ('enable_home_page_advert_carousel', '1'),
  ('home_page_advert_carousel_autoplay', '1'),
  ('home_page_advert_carousel_speed', '3000'),
  ('hpfeat_items_row_desktop', '4'),
  ('hpfeat_items_row_phone', '1'),
  ('recent_items_row_desktop', '4'),
  ('recent_items_row_phone', '2'),
  ('ending_items_row_desktop', '4'),
  ('ending_items_row_phone', '2'),
  ('popular_items_row_desktop', '4'),
  ('popular_items_row_phone', '2'),
  ('catfeat_box', 'grid'),
  ('catfeat_items_row_desktop', '3'),
  ('catfeat_items_row_phone', '1'),
  ('eight_theme_header_type', 'header.one'),
  ('eight_theme_footer_type', 'footer.one'),
  ('eight_theme_color_theme', 'theme-blue');

ALTER TABLE `{%TABLE_PREFIX%}categories`
  ADD `is_header_menu` tinyint(4) NOT NULL
  AFTER `adult`;

UPDATE `{%TABLE_PREFIX%}categories`
SET `is_header_menu` = '1'
WHERE `parent_id` IS NULL;

ALTER TABLE `{%TABLE_PREFIX%}newsletters_subscribers`
  ADD `confirmed` tinyint NOT NULL AFTER `email`;

INSERT INTO `{%TABLE_PREFIX%}settings`
(`name`, `value`) VALUES
  ('newsletter_subscription_email_confirmation', '');