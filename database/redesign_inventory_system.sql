-- Redesigned Inventory System Schema
-- Focus on individual item tracking with proper dispatch management

-- Drop existing tables to recreate with new structure
DROP TABLE IF EXISTS inventory_dispatch_items;
DROP TABLE IF EXISTS inventory_stock_summary;
DROP TABLE IF EXISTS inventory_stock;

-- Redesigned inventory_stock table - Each row represents ONE physical item
CREATE TABLE inventory_stock (
    id INT PRIMARY KEY AUTO_INCREMENT,
    boq_item_id INT NOT NULL,
    serial_number VARCHAR(100) UNIQUE, -- Optional but unique if provided
    batch_number VARCHAR(100),
    
    -- Item details
    unit_cost DECIMAL(10,2) NOT NULL DEFAULT 0,
    purchase_date DATE,
    expiry_date DATE,
    warranty_period INT, -- in months
    
    -- Location and status
    location_type ENUM('warehouse', 'vendor_site', 'in_transit', 'returned') DEFAULT 'warehouse',
    location_id INT, -- site_id or vendor_id depending on location_type
    location_name VARCHAR(255),
    
    -- Item condition and status
    item_status ENUM('available', 'dispatched', 'delivered', 'returned', 'damaged') DEFAULT 'available',
    quality_status ENUM('good', 'damaged', 'expired', 'under_repair') DEFAULT 'good',
    
    -- Supplier and purchase info
    supplier_name VARCHAR(255),
    purchase_order_number VARCHAR(100),
    invoice_number VARCHAR(100),
    
    -- Dispatch tracking
    dispatch_id INT NULL, -- Links to inventory_dispatches when dispatched
    dispatched_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    
    -- Metadata
    notes TEXT,
    created_by INT,
    updated_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign keys
    FOREIGN KEY (boq_item_id) REFERENCES boq_items(id),
    FOREIGN KEY (dispatch_id) REFERENCES inventory_dispatches(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id),
    
    -- Indexes for performance
    INDEX idx_boq_item (boq_item_id),
    INDEX idx_serial_number (serial_number),
    INDEX idx_status (item_status),
    INDEX idx_location (location_type, location_id),
    INDEX idx_dispatch (dispatch_id)
);

-- Updated inventory_dispatch_items table to track specific stock IDs
CREATE TABLE inventory_dispatch_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    dispatch_id INT NOT NULL,
    inventory_stock_id INT NOT NULL, -- Links to specific inventory_stock record
    
    -- Item details at time of dispatch
    boq_item_id INT NOT NULL,
    unit_cost DECIMAL(10,2) NOT NULL,
    item_condition ENUM('new', 'used', 'refurbished') DEFAULT 'new',
    
    -- Dispatch specific info
    dispatch_notes TEXT,
    warranty_period INT, -- in months
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign keys
    FOREIGN KEY (dispatch_id) REFERENCES inventory_dispatches(id) ON DELETE CASCADE,
    FOREIGN KEY (inventory_stock_id) REFERENCES inventory_stock(id),
    FOREIGN KEY (boq_item_id) REFERENCES boq_items(id),
    
    -- Ensure each stock item can only be dispatched once
    UNIQUE KEY unique_stock_dispatch (inventory_stock_id),
    
    -- Indexes
    INDEX idx_dispatch (dispatch_id),
    INDEX idx_stock (inventory_stock_id),
    INDEX idx_boq_item (boq_item_id)
);

-- Create a view for inventory summary (for reporting and dashboard)
CREATE VIEW inventory_summary AS
SELECT 
    bi.id as boq_item_id,
    bi.item_name,
    bi.item_code,
    bi.unit,
    bi.category,
    bi.icon_class,
    
    -- Stock counts by status
    COUNT(CASE WHEN ist.item_status = 'available' THEN 1 END) as available_stock,
    COUNT(CASE WHEN ist.item_status = 'dispatched' THEN 1 END) as dispatched_stock,
    COUNT(CASE WHEN ist.item_status = 'delivered' THEN 1 END) as delivered_stock,
    COUNT(CASE WHEN ist.item_status = 'returned' THEN 1 END) as returned_stock,
    COUNT(CASE WHEN ist.item_status = 'damaged' THEN 1 END) as damaged_stock,
    COUNT(*) as total_stock,
    
    -- Financial summary
    AVG(ist.unit_cost) as avg_unit_cost,
    SUM(CASE WHEN ist.item_status = 'available' THEN ist.unit_cost ELSE 0 END) as available_value,
    SUM(ist.unit_cost) as total_value,
    
    -- Location summary
    COUNT(CASE WHEN ist.location_type = 'warehouse' THEN 1 END) as warehouse_stock,
    COUNT(CASE WHEN ist.location_type = 'vendor_site' THEN 1 END) as vendor_site_stock,
    COUNT(CASE WHEN ist.location_type = 'in_transit' THEN 1 END) as in_transit_stock
    
FROM boq_items bi
LEFT JOIN inventory_stock ist ON bi.id = ist.boq_item_id
WHERE bi.status = 'active'
GROUP BY bi.id, bi.item_name, bi.item_code, bi.unit, bi.category, bi.icon_class;

-- Add some sample data for testing
INSERT INTO inventory_stock (boq_item_id, serial_number, unit_cost, item_status, location_type, supplier_name, created_by) VALUES
-- Cable Manager items
(1, 'CM001', 150.00, 'available', 'warehouse', 'Tech Supplies Ltd', 1),
(1, 'CM002', 150.00, 'available', 'warehouse', 'Tech Supplies Ltd', 1),
(1, 'CM003', 150.00, 'available', 'warehouse', 'Tech Supplies Ltd', 1),

-- 12U Rack items  
(3, 'RACK001', 2500.00, 'available', 'warehouse', 'Rack Solutions Inc', 1),
(3, 'RACK002', 2500.00, 'available', 'warehouse', 'Rack Solutions Inc', 1),

-- 1m Patch Cord items
(7, 'PC001', 25.00, 'available', 'warehouse', 'Cable Corp', 1),
(7, 'PC002', 25.00, 'available', 'warehouse', 'Cable Corp', 1),
(7, 'PC003', 25.00, 'available', 'warehouse', 'Cable Corp', 1),
(7, 'PC004', 25.00, 'available', 'warehouse', 'Cable Corp', 1),
(7, 'PC005', 25.00, 'available', 'warehouse', 'Cable Corp', 1),

-- 2m Pole items
(8, 'POLE001', 75.00, 'available', 'warehouse', 'Pole Manufacturing', 1),
(8, 'POLE002', 75.00, 'available', 'warehouse', 'Pole Manufacturing', 1),
(8, 'POLE003', 75.00, 'available', 'warehouse', 'Pole Manufacturing', 1),

-- 4 Way Junction items
(9, 'JUN001', 45.00, 'available', 'warehouse', 'Junction Systems', 1),
(9, 'JUN002', 45.00, 'available', 'warehouse', 'Junction Systems', 1),
(9, 'JUN003', 45.00, 'available', 'warehouse', 'Junction Systems', 1);

-- Create indexes for better performance
CREATE INDEX idx_inventory_stock_compound ON inventory_stock (boq_item_id, item_status, location_type);
CREATE INDEX idx_inventory_stock_serial ON inventory_stock (serial_number);