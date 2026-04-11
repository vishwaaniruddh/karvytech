-- Create quantity_audit table for tracking contractor quantity corrections
CREATE TABLE IF NOT EXISTS `quantity_audit` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `dispatch_id` int(11) NOT NULL,
    `boq_item_id` int(11) NOT NULL,
    `vendor_id` int(11) NOT NULL,
    `original_quantity` decimal(10,2) NOT NULL,
    `corrected_quantity` decimal(10,2) NOT NULL,
    `reason` text NOT NULL,
    `status` enum('pending','approved','rejected') DEFAULT 'pending',
    `admin_notes` text DEFAULT NULL,
    `reviewed_by` int(11) DEFAULT NULL,
    `reviewed_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_dispatch_id` (`dispatch_id`),
    KEY `idx_boq_item_id` (`boq_item_id`),
    KEY `idx_vendor_id` (`vendor_id`),
    KEY `idx_status` (`status`),
    KEY `idx_created_at` (`created_at`),
    CONSTRAINT `fk_quantity_audit_dispatch` FOREIGN KEY (`dispatch_id`) REFERENCES `inventory_dispatches` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_quantity_audit_boq_item` FOREIGN KEY (`boq_item_id`) REFERENCES `boq_items` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_quantity_audit_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_quantity_audit_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add indexes for better performance
CREATE INDEX `idx_quantity_audit_compound` ON `quantity_audit` (`dispatch_id`, `boq_item_id`, `status`);
CREATE INDEX `idx_quantity_audit_review` ON `quantity_audit` (`status`, `created_at`);