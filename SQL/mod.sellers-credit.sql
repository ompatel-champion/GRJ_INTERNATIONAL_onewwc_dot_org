INSERT INTO `ppb_payment_gateways` (`name`, `logo_path`, `type`, `order_id`, `site_fees`, `direct_payment`)
VALUES ('SellersCredit', '/img/logos/sellers-credit.png', 'online', '', '', '');

INSERT INTO `ppb_settings`
(`name`, `value`) VALUES
  ('enable_sellers_credit', '1'),
  ('sellers_credit_minimum_withdrawal_limit', '50');


CREATE TABLE `ppb_balance_withdrawals` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` int(11) NOT NULL,
  `amount` decimal(16,2) NOT NULL,
  `currency` varchar(50) NOT NULL,
  `status` enum('pending','paid','declined','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NULL,
  FOREIGN KEY (`user_id`) REFERENCES `ppb_users` (`id`) ON DELETE CASCADE
) COMMENT='' ENGINE='InnoDB';

## part 2
INSERT INTO `ppb_settings`
(`name`, `value`) VALUES
  ('mandatory_credit_payments', ''),
  ('automatic_credit_payments', '');