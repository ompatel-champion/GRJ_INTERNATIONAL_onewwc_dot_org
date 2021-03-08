INSERT INTO `ppb_settings`
(`name`, `value`) VALUES
  ('enable_facebook_login', ''),
  ('facebook_app_id', ''),
  ('facebook_app_secret', '');

ALTER TABLE `ppb_users`
  ADD `facebook_id` bigint NULL;