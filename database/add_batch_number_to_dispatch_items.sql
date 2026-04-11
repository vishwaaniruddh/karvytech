-- Add batch_number field to inventory_dispatch_items table
-- This allows storing batch numbers entered during dispatch creation

ALTER TABLE `inventory_dispatch_items` 
ADD COLUMN `batch_number` VARCHAR(100) DEFAULT NULL AFTER `item_condition`,
ADD INDEX `idx_batch_number` (`batch_number`);

-- Update existing records to get batch numbers from linked inventory_stock
UPDATE `inventory_dispatch_items` idi
JOIN `inventory_stock` ist ON idi.inventory_stock_id = ist.id
SET idi.batch_number = ist.batch_number
WHERE idi.batch_number IS NULL AND ist.batch_number IS NOT NULL;