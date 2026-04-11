-- Add partial delivery statuses to support partial delivery scenarios

-- Update inventory_dispatches table to include partial delivery status
ALTER TABLE `inventory_dispatches` 
MODIFY COLUMN `dispatch_status` ENUM('prepared', 'dispatched', 'in_transit', 'delivered', 'partially_delivered', 'returned', 'cancelled') DEFAULT 'prepared';

-- Update material_requests table to include partial fulfillment status  
ALTER TABLE `material_requests` 
MODIFY COLUMN `status` ENUM('draft', 'pending', 'approved', 'dispatched', 'partially_dispatched', 'partially_fulfilled', 'completed', 'rejected', 'cancelled') DEFAULT 'pending';