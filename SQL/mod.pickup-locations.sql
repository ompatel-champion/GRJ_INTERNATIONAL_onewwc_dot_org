CREATE TABLE `ppb_store_pickup_locations` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` int(11) NOT NULL,
  `address` text NOT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `ppb_users` (`id`) ON DELETE CASCADE
) COMMENT=''; -- 0.248 s
ALTER TABLE `ppb_store_pickup_locations` AUTO_INCREMENT=1001;

ALTER TABLE `ppb_sales`
ADD `store_pickup_location_id` int NULL;



# part 2
ALTER TABLE `ppb_store_pickup_locations`
CHANGE `user_id` `user_id` int(11) NULL AFTER `id`;

#part 3
ALTER TABLE `ppb_store_pickup_locations`
ADD `latitude` decimal(16,2) NOT NULL,
ADD `longitude` decimal(16,2) NOT NULL AFTER `latitude`;

ALTER TABLE `ppb_store_pickup_locations`
ADD `price` decimal(16,2) NOT NULL AFTER `address`,
ADD `currency` varchar(100) NOT NULL AFTER `price`;