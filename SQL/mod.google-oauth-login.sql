INSERT INTO `ppb_settings`
(`name`, `value`) VALUES
  ('enable_google_oauth_login', ''),
  ('google_oauth_client_id', ''),
  ('google_oauth_client_secret', '');

ALTER TABLE `ppb_users`
  ADD `google_oauth_id` varchar (255) NULL;