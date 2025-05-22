-- Shipping zones table
CREATE TABLE IF NOT EXISTS `shipping_zones` (
  `zone_id` int(11) NOT NULL AUTO_INCREMENT,
  `zone_name` varchar(100) NOT NULL,
  `min_distance` decimal(10,2) NOT NULL,
  `max_distance` decimal(10,2) NOT NULL,
  `base_cost` decimal(10,2) NOT NULL,
  `cost_per_km` decimal(10,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`zone_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Shipping surcharges table
CREATE TABLE IF NOT EXISTS `shipping_surcharges` (
  `surcharge_id` int(11) NOT NULL AUTO_INCREMENT,
  `surcharge_type` enum('rush_hour','bad_weather','difficult_area','heavy_load') NOT NULL,
  `surcharge_amount` decimal(10,2) NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`surcharge_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default shipping zones for Tasikmalaya
INSERT INTO `shipping_zones` (`zone_name`, `min_distance`, `max_distance`, `base_cost`, `cost_per_km`, `is_active`) VALUES
('Zone 1 (0-3 km)', 0.00, 3.00, 6000.00, 2000.00, 1),
('Zone 2 (3-8 km)', 3.01, 8.00, 8000.00, 2500.00, 1),
('Zone 3 (8-15 km)', 8.01, 15.00, 10000.00, 3000.00, 1);

-- Insert default surcharges
INSERT INTO `shipping_surcharges` (`surcharge_type`, `surcharge_amount`, `start_time`, `end_time`, `is_active`) VALUES
('rush_hour', 3000.00, '07:00:00', '09:00:00', 1),
('rush_hour', 3000.00, '16:00:00', '18:00:00', 1),
('bad_weather', 5000.00, NULL, NULL, 1),
('difficult_area', 3000.00, NULL, NULL, 1),
('heavy_load', 2000.00, NULL, NULL, 1); 