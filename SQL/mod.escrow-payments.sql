## MOD:- ESCROW PAYMENTS
ALTER TABLE `ppb_listings`
ADD `enable_escrow` tinyint(4) NOT NULL;

INSERT INTO `ppb_settings`
(`name`, `value`) VALUES
  ('enable_escrow_payments', '');

ALTER TABLE `ppb_transactions`
ADD `escrow_buyer_admin` tinyint NOT NULL AFTER `transaction_details`,
ADD `escrow_admin_seller` tinyint NOT NULL AFTER `escrow_buyer_admin`;