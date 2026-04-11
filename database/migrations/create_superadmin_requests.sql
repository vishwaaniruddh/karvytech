-- Create superadmin_requests table for approval workflow
CREATE TABLE IF NOT EXISTS `superadmin_requests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `request_type` VARCHAR(50) NOT NULL COMMENT 'Type of request: site_deletion, user_creation, role_change, etc.',
    `request_title` VARCHAR(255) NOT NULL,
    `request_description` TEXT,
    `requested_by` INT NOT NULL,
    `requested_by_name` VARCHAR(100),
    `requested_by_role` VARCHAR(50),
    `request_data` JSON COMMENT 'Stores the actual request data',
    `reference_id` INT COMMENT 'ID of the related entity (site_id, user_id, etc.)',
    `reference_table` VARCHAR(50) COMMENT 'Table name of the related entity',
    `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    `priority` ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    `reviewed_by` INT NULL,
    `reviewed_at` DATETIME NULL,
    `remarks` TEXT NULL COMMENT 'Superadmin remarks on approval/rejection',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_status` (`status`),
    INDEX `idx_request_type` (`request_type`),
    INDEX `idx_requested_by` (`requested_by`),
    INDEX `idx_priority` (`priority`),
    INDEX `idx_created_at` (`created_at`),
    FOREIGN KEY (`requested_by`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`reviewed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add indexes for better performance
ALTER TABLE `superadmin_requests` 
ADD INDEX `idx_status_priority` (`status`, `priority`),
ADD INDEX `idx_request_type_status` (`request_type`, `status`);
