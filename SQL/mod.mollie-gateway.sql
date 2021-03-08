INSERT INTO `ppb_payment_gateways` (`name`, `logo_path`, `type`, `order_id`, `site_fees`, `direct_payment`)
VALUES ('Mollie', '/img/logos/mollie.png', 1, '', '', '');

ALTER TABLE `ppb_transactions`
ADD `mollie_payment_id` varchar(255) NULL;