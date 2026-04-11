-- Create material addition log table to track when materials are added to installations
-- This helps track material flow from dispatches to installations

CREATE TABLE IF NOT EXISTS `material_addition_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `installation_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `quantity_added` decimal(10,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `added_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_installation_material` (`installation_id`, `material_id`),
  KEY `idx_added_at` (`added_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add updated_at column to installation_materials if it doesn't exist
ALTER TABLE `installation_materials` 
ADD COLUMN `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `used_quantity`;