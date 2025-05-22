ALTER TABLE `placed_orders` 
ADD COLUMN `shipping_cost` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `total_amount`,
ADD COLUMN `shipping_status` ENUM('pending', 'calculated', 'confirmed') NOT NULL DEFAULT 'pending' AFTER `shipping_cost`,
ADD COLUMN `shipping_notes` TEXT NULL AFTER `shipping_status`; 