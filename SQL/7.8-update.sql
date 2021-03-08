SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

ALTER TABLE `{%TABLE_PREFIX%}users_statistics`
  CHANGE `request_uri` `request_uri` TEXT COLLATE 'utf8_general_ci' NOT NULL
  AFTER `remote_addr`;

ALTER TABLE `{%TABLE_PREFIX%}advertising`
  ADD `new_tab` TINYINT NOT NULL  DEFAULT '1'
  AFTER `type`;

INSERT INTO `{%TABLE_PREFIX%}settings`
(`name`, `value`) VALUES
  ('force_stores', '');