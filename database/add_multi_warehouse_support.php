<?php
/**
 * Multi-Warehouse Support Migration Script
 * 
 * This script adds multi-warehouse functionality to the inventory system
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Multi-Warehouse Support Migration ===\n\n";

require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "Environment: " . $db->getEnvironment() . "\n";
    echo "Database: " . $db->getDatabaseName() . "\n\n";
    
    // Start transaction
    $conn->beginTransaction();
    
    // Step 1: Create warehouses table
    echo "Step 1: Creating warehouses table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS warehouses (
        id INT PRIMARY KEY AUTO_INCREMENT,
        warehouse_code VARCHAR(50) UNIQUE NOT NULL,
        name VARCHAR(255) NOT NULL,
        address TEXT NOT NULL,
        city VARCHAR(100),
        state VARCHAR(100),
        pincode VARCHAR(20),
        contact_person VARCHAR(255) NOT NULL,
        contact_phone VARCHAR(20) NOT NULL,
        contact_email VARCHAR(255),
        is_default BOOLEAN DEFAULT FALSE,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        created_by INT,
        updated_by INT,
        
        FOREIGN KEY (created_by) REFERENCES users(id),
        FOREIGN KEY (updated_by) REFERENCES users(id),
        INDEX idx_status (status),
        INDEX idx_warehouse_code (warehouse_code)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql);
    echo "✓ Warehouses table created\n\n";
    
    // Step 2: Insert default Mumbai warehouse
    echo "Step 2: Creating default Mumbai warehouse...\n";
    $sql = "INSERT INTO warehouses 
            (warehouse_code, name, address, city, state, pincode, contact_person, contact_phone, contact_email, is_default, status, created_by)
            VALUES 
            ('WH-MUM-001', 'Mumbai Main Warehouse', 'Main Warehouse Complex, Andheri East', 'Mumbai', 'Maharashtra', '400069', 'Warehouse Manager', '+91-22-12345678', 'mumbai.warehouse@company.com', TRUE, 'active', 1)
            ON DUPLICATE KEY UPDATE id=id";
    
    $conn->exec($sql);
    echo "✓ Mumbai warehouse created (ID: 1)\n\n";
    
    // Step 3: Add warehouse_id to inventory_stock table
    echo "Step 3: Adding warehouse_id to inventory_stock table...\n";
    
    $checkColumn = "SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'inventory_stock' 
                    AND COLUMN_NAME = 'warehouse_id'";
    $result = $conn->query($checkColumn);
    $exists = $result->fetch()['count'] > 0;
    
    if (!$exists) {
        $sql = "ALTER TABLE inventory_stock 
                ADD COLUMN warehouse_id INT NOT NULL DEFAULT 1 AFTER boq_item_id,
                ADD FOREIGN KEY (warehouse_id) REFERENCES warehouses(id),
                ADD INDEX idx_warehouse_stock (warehouse_id, boq_item_id, item_status)";
        $conn->exec($sql);
        echo "✓ warehouse_id column added to inventory_stock\n\n";
    } else {
        echo "⚠ warehouse_id column already exists in inventory_stock\n\n";
    }
    
    // Step 4: Add warehouse_id to inventory_inwards table
    echo "Step 4: Adding warehouse_id to inventory_inwards table...\n";
    
    $checkColumn = "SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'inventory_inwards' 
                    AND COLUMN_NAME = 'warehouse_id'";
    $result = $conn->query($checkColumn);
    $exists = $result->fetch()['count'] > 0;
    
    if (!$exists) {
        $sql = "ALTER TABLE inventory_inwards 
                ADD COLUMN warehouse_id INT NOT NULL DEFAULT 1 AFTER receipt_number,
                ADD FOREIGN KEY (warehouse_id) REFERENCES warehouses(id),
                ADD INDEX idx_warehouse_inward (warehouse_id)";
        $conn->exec($sql);
        echo "✓ warehouse_id column added to inventory_inwards\n\n";
    } else {
        echo "⚠ warehouse_id column already exists in inventory_inwards\n\n";
    }
    
    // Step 5: Add source_warehouse_id to inventory_dispatches table
    echo "Step 5: Adding source_warehouse_id to inventory_dispatches table...\n";
    
    $checkColumn = "SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'inventory_dispatches' 
                    AND COLUMN_NAME = 'source_warehouse_id'";
    $result = $conn->query($checkColumn);
    $exists = $result->fetch()['count'] > 0;
    
    if (!$exists) {
        $sql = "ALTER TABLE inventory_dispatches 
                ADD COLUMN source_warehouse_id INT NOT NULL DEFAULT 1 AFTER dispatch_number,
                ADD FOREIGN KEY (source_warehouse_id) REFERENCES warehouses(id),
                ADD INDEX idx_warehouse_dispatch (source_warehouse_id)";
        $conn->exec($sql);
        echo "✓ source_warehouse_id column added to inventory_dispatches\n\n";
    } else {
        echo "⚠ source_warehouse_id column already exists in inventory_dispatches\n\n";
    }
    
    // Step 6: Create warehouse_transfers table
    echo "Step 6: Creating warehouse_transfers table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS warehouse_transfers (
        id INT PRIMARY KEY AUTO_INCREMENT,
        transfer_number VARCHAR(50) UNIQUE NOT NULL,
        transfer_date DATE NOT NULL,
        source_warehouse_id INT NOT NULL,
        destination_warehouse_id INT NOT NULL,
        transfer_status ENUM('pending', 'in_transit', 'completed', 'cancelled') DEFAULT 'pending',
        total_items INT DEFAULT 0,
        total_value DECIMAL(12,2) DEFAULT 0.00,
        initiated_by INT NOT NULL,
        completed_by INT,
        completed_at TIMESTAMP NULL,
        transfer_notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        FOREIGN KEY (source_warehouse_id) REFERENCES warehouses(id),
        FOREIGN KEY (destination_warehouse_id) REFERENCES warehouses(id),
        FOREIGN KEY (initiated_by) REFERENCES users(id),
        FOREIGN KEY (completed_by) REFERENCES users(id),
        INDEX idx_transfer_date (transfer_date),
        INDEX idx_warehouses (source_warehouse_id, destination_warehouse_id),
        INDEX idx_status (transfer_status),
        
        CONSTRAINT chk_different_warehouses CHECK (source_warehouse_id != destination_warehouse_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql);
    echo "✓ warehouse_transfers table created\n\n";
    
    // Step 7: Create warehouse_transfer_items table
    echo "Step 7: Creating warehouse_transfer_items table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS warehouse_transfer_items (
        id INT PRIMARY KEY AUTO_INCREMENT,
        transfer_id INT NOT NULL,
        inventory_stock_id INT NOT NULL,
        boq_item_id INT NOT NULL,
        unit_cost DECIMAL(10,2) NOT NULL,
        transfer_notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        
        FOREIGN KEY (transfer_id) REFERENCES warehouse_transfers(id) ON DELETE CASCADE,
        FOREIGN KEY (inventory_stock_id) REFERENCES inventory_stock(id),
        FOREIGN KEY (boq_item_id) REFERENCES boq_items(id),
        INDEX idx_transfer (transfer_id),
        INDEX idx_stock (inventory_stock_id),
        INDEX idx_boq_item (boq_item_id),
        
        UNIQUE KEY unique_stock_transfer (inventory_stock_id, transfer_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql);
    echo "✓ warehouse_transfer_items table created\n\n";
    
    // Step 8: Add warehouse columns to inventory_movements table
    echo "Step 8: Adding warehouse columns to inventory_movements table...\n";
    
    $checkColumn = "SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'inventory_movements' 
                    AND COLUMN_NAME = 'source_warehouse_id'";
    $result = $conn->query($checkColumn);
    $exists = $result->fetch()['count'] > 0;
    
    if (!$exists) {
        $sql = "ALTER TABLE inventory_movements 
                ADD COLUMN source_warehouse_id INT AFTER from_location,
                ADD COLUMN destination_warehouse_id INT AFTER to_location,
                ADD FOREIGN KEY (source_warehouse_id) REFERENCES warehouses(id),
                ADD FOREIGN KEY (destination_warehouse_id) REFERENCES warehouses(id),
                ADD INDEX idx_warehouse_movement (source_warehouse_id, destination_warehouse_id)";
        $conn->exec($sql);
        echo "✓ Warehouse columns added to inventory_movements\n\n";
    } else {
        echo "⚠ Warehouse columns already exist in inventory_movements\n\n";
    }
    
    // Step 9: Update inventory_summary view
    echo "Step 9: Updating inventory_summary view...\n";
    $sql = "DROP VIEW IF EXISTS inventory_summary";
    $conn->exec($sql);
    
    $sql = "CREATE VIEW inventory_summary AS
    SELECT 
        bi.id as boq_item_id,
        bi.item_name,
        bi.item_code,
        bi.unit,
        bi.category,
        bi.icon_class,
        
        -- Overall stock counts
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
        
        -- Warehouse distribution
        COUNT(DISTINCT ist.warehouse_id) as warehouse_count,
        
        -- Location summary
        COUNT(CASE WHEN ist.location_type = 'warehouse' THEN 1 END) as warehouse_stock,
        COUNT(CASE WHEN ist.location_type = 'vendor_site' THEN 1 END) as vendor_site_stock,
        COUNT(CASE WHEN ist.location_type = 'in_transit' THEN 1 END) as in_transit_stock
        
    FROM boq_items bi
    LEFT JOIN inventory_stock ist ON bi.id = ist.boq_item_id
    WHERE bi.status = 'active'
    GROUP BY bi.id, bi.item_name, bi.item_code, bi.unit, bi.category, bi.icon_class";
    
    $conn->exec($sql);
    echo "✓ inventory_summary view updated\n\n";
    
    // Step 10: Create warehouse_stock_summary view
    echo "Step 10: Creating warehouse_stock_summary view...\n";
    $sql = "DROP VIEW IF EXISTS warehouse_stock_summary";
    $conn->exec($sql);
    
    $sql = "CREATE VIEW warehouse_stock_summary AS
    SELECT 
        w.id as warehouse_id,
        w.warehouse_code,
        w.name as warehouse_name,
        bi.id as boq_item_id,
        bi.item_name,
        bi.item_code,
        bi.unit,
        bi.category,
        
        -- Stock counts by status
        COUNT(CASE WHEN ist.item_status = 'available' THEN 1 END) as available_stock,
        COUNT(CASE WHEN ist.item_status = 'dispatched' THEN 1 END) as dispatched_stock,
        COUNT(CASE WHEN ist.item_status = 'delivered' THEN 1 END) as delivered_stock,
        COUNT(*) as total_stock,
        
        -- Financial summary
        AVG(ist.unit_cost) as avg_unit_cost,
        SUM(CASE WHEN ist.item_status = 'available' THEN ist.unit_cost ELSE 0 END) as available_value,
        SUM(ist.unit_cost) as total_value
        
    FROM warehouses w
    CROSS JOIN boq_items bi
    LEFT JOIN inventory_stock ist ON w.id = ist.warehouse_id AND bi.id = ist.boq_item_id
    WHERE w.status = 'active' AND bi.status = 'active'
    GROUP BY w.id, w.warehouse_code, w.name, bi.id, bi.item_name, bi.item_code, bi.unit, bi.category";
    
    $conn->exec($sql);
    echo "✓ warehouse_stock_summary view created\n\n";
    
    // Step 11: Update existing inventory_stock records to reference Mumbai warehouse
    echo "Step 11: Updating existing inventory_stock records...\n";
    $sql = "UPDATE inventory_stock SET warehouse_id = 1 WHERE warehouse_id IS NULL OR warehouse_id = 0";
    $conn->exec($sql);
    echo "✓ Updated existing inventory records to Mumbai warehouse\n\n";
    
    // Step 12: Create audit log entry
    echo "Step 12: Creating audit log entry...\n";
    
    // Get count of migrated records
    $countSql = "SELECT COUNT(*) as count FROM inventory_stock WHERE warehouse_id = 1";
    $result = $conn->query($countSql);
    $migratedCount = $result->fetch()['count'];
    
    $auditData = [
        'migration' => 'multi_warehouse_support',
        'warehouses_created' => 1,
        'default_warehouse' => 'Mumbai Main Warehouse (WH-MUM-001)',
        'inventory_records_migrated' => $migratedCount,
        'tables_created' => ['warehouses', 'warehouse_transfers', 'warehouse_transfer_items'],
        'tables_modified' => ['inventory_stock', 'inventory_inwards', 'inventory_dispatches', 'inventory_movements'],
        'views_created' => ['warehouse_stock_summary'],
        'views_updated' => ['inventory_summary']
    ];
    
    $sql = "INSERT INTO audit_logs 
            (user_id, action, table_name, record_id, new_values, ip_address, user_agent) 
            VALUES 
            (1, 'multi_warehouse_migration', 'warehouses', 1, :audit_data, 'system', 'migration_script')";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute(['audit_data' => json_encode($auditData)]);
    echo "✓ Audit log entry created\n\n";
    
    // Commit transaction
    if ($conn->inTransaction()) {
        $conn->commit();
    }
    
    echo "=== Migration Completed Successfully ===\n\n";
    echo "Summary:\n";
    echo "- Warehouses table created\n";
    echo "- Default Mumbai warehouse created (ID: 1)\n";
    echo "- warehouse_id added to inventory_stock, inventory_inwards\n";
    echo "- source_warehouse_id added to inventory_dispatches\n";
    echo "- warehouse_transfers and warehouse_transfer_items tables created\n";
    echo "- Warehouse columns added to inventory_movements\n";
    echo "- inventory_summary view updated with warehouse distribution\n";
    echo "- warehouse_stock_summary view created\n";
    echo "- {$migratedCount} inventory records migrated to Mumbai warehouse\n";
    echo "- Audit log entry created\n\n";
    
    echo "✓ Multi-warehouse support is now enabled!\n";
    
} catch (PDOException $e) {
    // Rollback on error
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    echo "\n✗ Migration failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    
    exit(1);
} catch (Exception $e) {
    // Rollback on error
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    echo "\n✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
