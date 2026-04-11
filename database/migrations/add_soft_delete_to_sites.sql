-- Add soft delete columns to sites table
ALTER TABLE `sites` 
ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL AFTER `updated_by`,
ADD COLUMN `deleted_by` INT NULL DEFAULT NULL AFTER `deleted_at`,
ADD INDEX `idx_deleted_at` (`deleted_at`);

-- Add foreign key for deleted_by
ALTER TABLE `sites`
ADD CONSTRAINT `fk_sites_deleted_by` 
FOREIGN KEY (`deleted_by`) REFERENCES `users`(`id`) ON DELETE SET NULL;
