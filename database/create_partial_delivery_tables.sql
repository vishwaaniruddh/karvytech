-- Create tables to support partial delivery management

-- Create dispatch notes table
CREATE TABLE IF NOT EXISTS `dispatch_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dispatch_id` int(11) NOT NULL,
  `note` text NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_dispatch_id` (`dispatch_id`),
  KEY `idx_created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add fields to material_requests table for partial delivery tracking
ALTER TABLE `material_requests` 
ADD COLUMN `request_type` ENUM('standard', 'urgent', 'missing_items_followup') DEFAULT 'standard' AFTER `status`,
ADD COLUMN `parent_request_id` int(11) DEFAULT NULL AFTER `request_type`,
ADD COLUMN `parent_dispatch_id` int(11) DEFAULT NULL AFTER `parent_request_id`,
ADD INDEX `idx_parent_request` (`parent_request_id`),
ADD INDEX `idx_parent_dispatch` (`parent_dispatch_id`);