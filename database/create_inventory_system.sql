-- Create comprehensive inventory management system

-- 1. Inventory Stock Table (Overall inventory levels)
CREATE TABLE IF NOT EXISTS `inventory_stock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `boq_item_id` int(11) NOT NULL,
  `current_stock` decimal(10,2) DEFAULT 0.00,
  `reserved_stock` decimal(10,2) DEFAULT 0.00,
  `available_stock` decimal(10,2) GENERATED ALWAYS AS (`current_stock` - `reserved_stock`) STORED,
  `minimum_stock` decimal(10,2) DEFAULT 0.00,
  `maximum_stock` decimal(10,2) DEFAULT 0.00,
  `unit_cost` decimal(10,2) DEFAULT 0.00,
  `total_value` decimal(12,2) GENERATED ALWAYS AS (`current_stock` * `unit_cost`) STORED,
  `last_updated` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `boq_item_id` (`boq_item_id`),
  FOREIGN KEY (`boq_item_id`) REFERENCES `boq_items`(`id`) ON DELETE CASCADE,
  INDEX `idx_stock_levels` (`current_stock`, `minimum_stock`),
  INDEX `idx_available_stock` (`available_stock`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Inward Receipts Table (Material receiving)
CREATE TABLE IF NOT EXISTS `inventory_inwards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `receipt_number` varchar(50) NOT NULL,
  `receipt_date` date NOT NULL,
  `supplier_name` varchar(255) NOT NULL,
  `supplier_contact` varchar(100) DEFAULT NULL,
  `purchase_order_number` varchar(100) DEFAULT NULL,
  `invoice_number` varchar(100) DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `total_amount` decimal(12,2) DEFAULT 0.00,
  `received_by` int(11) NOT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `status` enum('pending', 'verified', 'rejected') DEFAULT 'pending',
  `remarks` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `receipt_number` (`receipt_number`),
  INDEX `idx_receipt_date` (`receipt_date`),
  INDEX `idx_supplier` (`supplier_name`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Inward Receipt Items Table (Individual items in each receipt)
CREATE TABLE IF NOT EXISTS `inventory_inward_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inward_id` int(11) NOT NULL,
  `boq_item_id` int(11) NOT NULL,
  `quantity_received` decimal(10,2) NOT NULL,
  `unit_cost` decimal(10,2) NOT NULL,
  `total_cost` decimal(12,2) GENERATED ALWAYS AS (`quantity_received` * `unit_cost`) STORED,
  `batch_number` varchar(100) DEFAULT NULL,
  `serial_numbers` json DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `quality_status` enum('good', 'damaged', 'rejected') DEFAULT 'good',
  `remarks` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`inward_id`) REFERENCES `inventory_inwards`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`boq_item_id`) REFERENCES `boq_items`(`id`) ON DELETE CASCADE,
  INDEX `idx_inward_boq` (`inward_id`, `boq_item_id`),
  INDEX `idx_batch` (`batch_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Material Dispatches Table (Outward to vendors/sites)
CREATE TABLE IF NOT EXISTS `inventory_dispatches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dispatch_number` varchar(50) NOT NULL,
  `dispatch_date` date NOT NULL,
  `material_request_id` int(11) DEFAULT NULL,
  `site_id` int(11) DEFAULT NULL,
  `vendor_id` int(11) DEFAULT NULL,
  `contact_person_name` varchar(255) NOT NULL,
  `contact_person_phone` varchar(20) DEFAULT NULL,
  `delivery_address` text NOT NULL,
  `courier_name` varchar(255) DEFAULT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `expected_delivery_date` date DEFAULT NULL,
  `actual_delivery_date` date DEFAULT NULL,
  `dispatch_status` enum('prepared', 'dispatched', 'in_transit', 'delivered', 'returned') DEFAULT 'prepared',
  `total_items` int(11) DEFAULT 0,
  `total_value` decimal(12,2) DEFAULT 0.00,
  `dispatched_by` int(11) NOT NULL,
  `received_by_name` varchar(255) DEFAULT NULL,
  `received_by_signature` varchar(500) DEFAULT NULL,
  `delivery_remarks` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dispatch_number` (`dispatch_number`),
  FOREIGN KEY (`material_request_id`) REFERENCES `material_requests`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`site_id`) REFERENCES `sites`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE SET NULL,
  INDEX `idx_dispatch_date` (`dispatch_date`),
  INDEX `idx_site_vendor` (`site_id`, `vendor_id`),
  INDEX `idx_status` (`dispatch_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Dispatch Items Table (Individual items in each dispatch)
CREATE TABLE IF NOT EXISTS `inventory_dispatch_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dispatch_id` int(11) NOT NULL,
  `boq_item_id` int(11) NOT NULL,
  `quantity_dispatched` decimal(10,2) NOT NULL,
  `unit_cost` decimal(10,2) NOT NULL,
  `total_cost` decimal(12,2) GENERATED ALWAYS AS (`quantity_dispatched` * `unit_cost`) STORED,
  `batch_number` varchar(100) DEFAULT NULL,
  `serial_numbers` json DEFAULT NULL,
  `item_condition` enum('new', 'used', 'refurbished') DEFAULT 'new',
  `warranty_period` varchar(100) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`dispatch_id`) REFERENCES `inventory_dispatches`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`boq_item_id`) REFERENCES `boq_items`(`id`) ON DELETE CASCADE,
  INDEX `idx_dispatch_boq` (`dispatch_id`, `boq_item_id`),
  INDEX `idx_batch` (`batch_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Material Tracking Table (Track material location and status)
CREATE TABLE IF NOT EXISTS `inventory_tracking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `boq_item_id` int(11) NOT NULL,
  `batch_number` varchar(100) DEFAULT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `current_location_type` enum('warehouse', 'in_transit', 'site', 'vendor', 'returned', 'damaged') NOT NULL,
  `current_location_id` int(11) DEFAULT NULL,
  `current_location_name` varchar(255) DEFAULT NULL,
  `site_id` int(11) DEFAULT NULL,
  `vendor_id` int(11) DEFAULT NULL,
  `dispatch_id` int(11) DEFAULT NULL,
  `inward_id` int(11) DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `status` enum('available', 'reserved', 'dispatched', 'installed', 'damaged', 'returned') DEFAULT 'available',
  `last_movement_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  `movement_remarks` text DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`boq_item_id`) REFERENCES `boq_items`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`site_id`) REFERENCES `sites`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`dispatch_id`) REFERENCES `inventory_dispatches`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`inward_id`) REFERENCES `inventory_inwards`(`id`) ON DELETE SET NULL,
  INDEX `idx_location` (`current_location_type`, `current_location_id`),
  INDEX `idx_site_vendor` (`site_id`, `vendor_id`),
  INDEX `idx_serial` (`serial_number`),
  INDEX `idx_batch` (`batch_number`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Stock Movements Table (Audit trail of all stock movements)
CREATE TABLE IF NOT EXISTS `inventory_movements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `boq_item_id` int(11) NOT NULL,
  `movement_type` enum('inward', 'outward', 'adjustment', 'transfer', 'return') NOT NULL,
  `reference_type` enum('inward_receipt', 'dispatch', 'adjustment', 'transfer', 'return') NOT NULL,
  `reference_id` int(11) NOT NULL,
  `reference_number` varchar(100) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_cost` decimal(10,2) DEFAULT 0.00,
  `total_value` decimal(12,2) GENERATED ALWAYS AS (`quantity` * `unit_cost`) STORED,
  `from_location` varchar(255) DEFAULT NULL,
  `to_location` varchar(255) DEFAULT NULL,
  `batch_number` varchar(100) DEFAULT NULL,
  `serial_numbers` json DEFAULT NULL,
  `movement_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  `remarks` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`boq_item_id`) REFERENCES `boq_items`(`id`) ON DELETE CASCADE,
  INDEX `idx_movement_type` (`movement_type`),
  INDEX `idx_reference` (`reference_type`, `reference_id`),
  INDEX `idx_movement_date` (`movement_date`),
  INDEX `idx_boq_date` (`boq_item_id`, `movement_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Material Reconciliation Table (Periodic stock reconciliation)
CREATE TABLE IF NOT EXISTS `inventory_reconciliation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reconciliation_number` varchar(50) NOT NULL,
  `reconciliation_date` date NOT NULL,
  `reconciliation_type` enum('full', 'partial', 'cycle') DEFAULT 'partial',
  `status` enum('in_progress', 'completed', 'approved', 'rejected') DEFAULT 'in_progress',
  `total_items_checked` int(11) DEFAULT 0,
  `total_discrepancies` int(11) DEFAULT 0,
  `total_value_difference` decimal(12,2) DEFAULT 0.00,
  `conducted_by` int(11) NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reconciliation_number` (`reconciliation_number`),
  INDEX `idx_reconciliation_date` (`reconciliation_date`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Reconciliation Items Table (Individual item reconciliation details)
CREATE TABLE IF NOT EXISTS `inventory_reconciliation_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reconciliation_id` int(11) NOT NULL,
  `boq_item_id` int(11) NOT NULL,
  `system_quantity` decimal(10,2) NOT NULL,
  `physical_quantity` decimal(10,2) NOT NULL,
  `difference_quantity` decimal(10,2) GENERATED ALWAYS AS (`physical_quantity` - `system_quantity`) STORED,
  `unit_cost` decimal(10,2) NOT NULL,
  `value_difference` decimal(12,2) GENERATED ALWAYS AS ((`physical_quantity` - `system_quantity`) * `unit_cost`) STORED,
  `discrepancy_reason` varchar(500) DEFAULT NULL,
  `action_taken` varchar(500) DEFAULT NULL,
  `batch_number` varchar(100) DEFAULT NULL,
  `location_checked` varchar(255) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`reconciliation_id`) REFERENCES `inventory_reconciliation`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`boq_item_id`) REFERENCES `boq_items`(`id`) ON DELETE CASCADE,
  INDEX `idx_reconciliation_boq` (`reconciliation_id`, `boq_item_id`),
  INDEX `idx_discrepancy` (`difference_quantity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create triggers to automatically update inventory stock when items are received or dispatched

DELIMITER $$

-- Trigger to update stock when inward items are added
CREATE TRIGGER `update_stock_on_inward` 
AFTER INSERT ON `inventory_inward_items`
FOR EACH ROW
BEGIN
    INSERT INTO `inventory_stock` (`boq_item_id`, `current_stock`, `unit_cost`)
    VALUES (NEW.boq_item_id, NEW.quantity_received, NEW.unit_cost)
    ON DUPLICATE KEY UPDATE 
        `current_stock` = `current_stock` + NEW.quantity_received,
        `unit_cost` = ((current_stock * unit_cost) + (NEW.quantity_received * NEW.unit_cost)) / (current_stock + NEW.quantity_received);
END$$

-- Trigger to update stock when dispatch items are added
CREATE TRIGGER `update_stock_on_dispatch` 
AFTER INSERT ON `inventory_dispatch_items`
FOR EACH ROW
BEGIN
    UPDATE `inventory_stock` 
    SET `current_stock` = `current_stock` - NEW.quantity_dispatched
    WHERE `boq_item_id` = NEW.boq_item_id;
END$$

-- Trigger to create movement record on inward
CREATE TRIGGER `create_movement_on_inward` 
AFTER INSERT ON `inventory_inward_items`
FOR EACH ROW
BEGIN
    DECLARE receipt_number VARCHAR(50);
    SELECT `receipt_number` INTO receipt_number FROM `inventory_inwards` WHERE `id` = NEW.inward_id;
    
    INSERT INTO `inventory_movements` 
    (`boq_item_id`, `movement_type`, `reference_type`, `reference_id`, `reference_number`, `quantity`, `unit_cost`, `to_location`, `batch_number`, `created_by`)
    VALUES 
    (NEW.boq_item_id, 'inward', 'inward_receipt', NEW.inward_id, receipt_number, NEW.quantity_received, NEW.unit_cost, 'Warehouse', NEW.batch_number, 1);
END$$

-- Trigger to create movement record on dispatch
CREATE TRIGGER `create_movement_on_dispatch` 
AFTER INSERT ON `inventory_dispatch_items`
FOR EACH ROW
BEGIN
    DECLARE dispatch_number VARCHAR(50);
    DECLARE delivery_address TEXT;
    SELECT `dispatch_number`, `delivery_address` INTO dispatch_number, delivery_address FROM `inventory_dispatches` WHERE `id` = NEW.dispatch_id;
    
    INSERT INTO `inventory_movements` 
    (`boq_item_id`, `movement_type`, `reference_type`, `reference_id`, `reference_number`, `quantity`, `unit_cost`, `from_location`, `to_location`, `batch_number`, `created_by`)
    VALUES 
    (NEW.boq_item_id, 'outward', 'dispatch', NEW.dispatch_id, dispatch_number, NEW.quantity_dispatched, NEW.unit_cost, 'Warehouse', delivery_address, NEW.batch_number, 1);
END$$

DELIMITER ;

-- Initialize inventory stock for all existing BOQ items
INSERT INTO `inventory_stock` (`boq_item_id`, `current_stock`, `unit_cost`)
SELECT `id`, 0, 0 FROM `boq_items` WHERE `status` = 'active'
ON DUPLICATE KEY UPDATE `boq_item_id` = `boq_item_id`;

-- Create indexes for better performance
CREATE INDEX `idx_inventory_stock_low` ON `inventory_stock` (`boq_item_id`, `current_stock`, `minimum_stock`);
CREATE INDEX `idx_dispatch_tracking` ON `inventory_dispatches` (`site_id`, `vendor_id`, `dispatch_status`);
CREATE INDEX `idx_tracking_location` ON `inventory_tracking` (`current_location_type`, `site_id`, `vendor_id`);