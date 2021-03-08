INSERT INTO `ppb_settings`
(`name`, `value`) VALUES
  ('enable_google_plus_login', ''),
  ('google_plus_client_id', ''),
  ('google_plus_client_secret', '');

ALTER TABLE `ppb_users`
  ADD `google_plus_id` varchar (255) NULL;