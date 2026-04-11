-- Add delivery confirmation columns to inventory_dispatches table
-- Run this migration to support document uploads and detailed confirmation tracking

ALTER TABLE `inventory_dispatches` 
ADD COLUMN `delivery_date` DATE NULL AFTER `delivery_remarks`,
ADD COLUMN `delivery_time` TIME NULL AFTER `delivery_date`,
ADD COLUMN `received_by` VARCHAR(255) NULL AFTER `delivery_time`,
ADD COLUMN `received_by_phone` VARCHAR(20) NULL AFTER `received_by`,
ADD COLUMN `actual_delivery_address` TEXT NULL AFTER `received_by_phone`,
ADD COLUMN `delivery_notes` TEXT NULL AFTER `actual_delivery_address`,
ADD COLUMN `lr_copy_path` VARCHAR(500) NULL AFTER `delivery_notes`,
ADD COLUMN `additional_documents` JSON NULL AFTER `lr_copy_path`,
ADD COLUMN `item_confirmations` JSON NULL AFTER `additional_documents`,
ADD COLUMN `confirmed_by` INT(11) NULL AFTER `item_confirmations`,
ADD COLUMN `confirmation_date` TIMESTAMP NULL AFTER `confirmed_by`;

-- Add index for confirmed_by foreign key
ALTER TABLE `inventory_dispatches`
ADD INDEX `idx_confirmed_by` (`confirmed_by`);

-- Update dispatch_status enum to include 'confirmed' status
ALTER TABLE `inventory_dispatches` 
MODIFY COLUMN `dispatch_status` ENUM('prepared', 'dispatched', 'in_transit', 'delivered', 'confirmed', 'returned') DEFAULT 'prepared';
