<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Migrating BOQ Items table to new structure...\n";
    
    // Add missing columns
    $alterStatements = [
        "ALTER TABLE boq_items ADD COLUMN category varchar(100) DEFAULT NULL AFTER unit",
        "ALTER TABLE boq_items ADD COLUMN status enum('active', 'inactive') DEFAULT 'active' AFTER category",
        "ALTER TABLE boq_items ADD COLUMN need_serial_number tinyint(1) DEFAULT 0 AFTER status",
        "ALTER TABLE boq_items ADD COLUMN icon_class varchar(60) DEFAULT NULL AFTER need_serial_number",
        "ALTER TABLE boq_items ADD COLUMN created_by int(11) DEFAULT NULL AFTER icon_class",
        "ALTER TABLE boq_items ADD COLUMN updated_by int(11) DEFAULT NULL AFTER created_by"
    ];
    
    foreach ($alterStatements as $statement) {
        try {
            $db->exec($statement);
            echo "✓ " . substr($statement, 0, 60) . "...\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "- Column already exists: " . substr($statement, 0, 60) . "...\n";
            } else {
                echo "✗ Error: " . $e->getMessage() . "\n";
            }
        }
    }
    
    // Add indexes
    $indexStatements = [
        "ALTER TABLE boq_items ADD INDEX idx_status (status)",
        "ALTER TABLE boq_items ADD INDEX idx_category (category)",
        "ALTER TABLE boq_items ADD UNIQUE KEY item_code (item_code)"
    ];
    
    foreach ($indexStatements as $statement) {
        try {
            $db->exec($statement);
            echo "✓ " . substr($statement, 0, 60) . "...\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate key name') !== false || strpos($e->getMessage(), 'already exists') !== false) {
                echo "- Index already exists: " . substr($statement, 0, 60) . "...\n";
            } else {
                echo "✗ Index error: " . $e->getMessage() . "\n";
            }
        }
    }
    
    // Clear existing data and insert the BOQ data from the original file
    echo "\nClearing existing data and inserting BOQ items...\n";
    $db->exec("DELETE FROM boq_items");
    $db->exec("ALTER TABLE boq_items AUTO_INCREMENT = 1");
    
    // Insert the BOQ data
    $insertData = [
        [2, 'RACK-6U-001', '6U Rack with One Additional Tray', '6U Network Rack with Additional Tray for Equipment', 'Nos', 'Racks & Enclosures', 'active', 0, 'fas fa-server'],
        [3, 'RACK-12U-001', '12U Rack with One Additional Tray', '12U Network Rack with Additional Tray for Equipment', 'Nos', 'Racks & Enclosures', 'active', 0, 'fas fa-server'],
        [4, 'PP-24P-001', '24 Port Patch Panels', '24 Port Network Patch Panel', 'Nos', 'Network Components', 'active', 0, 'fas fa-plug'],
        [5, 'LBL-PP-001', 'Patch Panel Labeling', 'Labeling for Patch Panels', 'Set', 'Accessories', 'active', 0, 'fas fa-tags'],
        [6, 'CM-001', 'Cable Manager', 'Cable Management System', 'Nos', 'Accessories', 'active', 0, 'fas fa-project-diagram'],
        [7, 'PC-1M-001', '1m Patch Cord', '1 Meter Patch Cord Cable', 'Nos', 'Cables', 'active', 0, 'fas fa-ethernet'],
        [8, 'PC-5M-001', '5m Patch Cord', '5 Meter Patch Cord Cable', 'Nos', 'Cables', 'active', 0, 'fas fa-ethernet'],
        [9, 'IO-FP-001', 'I/O Box Kit - Face Plate', 'Input/Output Box Face Plate', 'Nos', 'I/O Components', 'active', 0, 'fas fa-square'],
        [10, 'IO-BB-001', 'I/O Box Kit - Back Box', 'Input/Output Box Back Box', 'Nos', 'I/O Components', 'active', 0, 'fas fa-cube'],
        [11, 'IO-SOC-001', 'I/O Box Kit - IO Socket', 'Input/Output Socket', 'Nos', 'I/O Components', 'active', 0, 'fas fa-plug'],
        [12, 'CAT6-UTP-001', 'Cat6 23 AWG UTP Cable (per Meter)', 'Category 6 UTP Cable 23 AWG', 'Meter', 'Cables', 'active', 0, 'fas fa-ethernet'],
        [13, 'PVC-25MM-001', '25 mm Conducting PVC Pipes (White)', '25mm White PVC Conducting Pipes', 'Meter', 'Conduits', 'active', 0, 'fas fa-grip-lines'],
        [14, 'FLEX-20MM-001', 'Flexible Pipe (20mm)', '20mm Flexible Conduit Pipe', 'Meter', 'Conduits', 'active', 0, 'fas fa-grip-lines'],
        [15, 'CT-200MM-001', 'Cable Ties (200 mm)', '200mm Cable Ties', 'Pcs', 'Accessories', 'active', 0, 'fas fa-link'],
        [16, 'CT-400MM-001', 'Cable Ties (400 mm)', '400mm Cable Ties', 'Pcs', 'Accessories', 'active', 0, 'fas fa-link'],
        [17, 'SCR-0.5-001', 'Screws 1/2', '1/2 inch Screws', 'Pcs', 'Hardware', 'active', 0, 'fas fa-tools'],
        [18, 'SCR-3.5-001', 'Screws 3.5', '3.5mm Screws', 'Pcs', 'Hardware', 'active', 0, 'fas fa-tools'],
        [19, 'PVC-SAD-001', 'PVC Pipe Saddles', 'PVC Pipe Mounting Saddles', 'Pcs', 'Hardware', 'active', 0, 'fas fa-grip-horizontal'],
        [20, 'ANG-L-001', 'L Slotted Angle', 'L-shaped Slotted Angle', 'Meter', 'Hardware', 'active', 0, 'fas fa-ruler-combined'],
        [21, 'BEND-25MM-001', '25mm Bend', '25mm Pipe Bend', 'Nos', 'Conduits', 'active', 0, 'fas fa-share'],
        [22, 'JUN-3W-001', '3 Way Junction', '3 Way Junction Box', 'Nos', 'Conduits', 'active', 0, 'fas fa-project-diagram'],
        [23, 'JUN-4W-001', '4 Way Junction', '4 Way Junction Box', 'Nos', 'Conduits', 'active', 0, 'fas fa-project-diagram'],
        [24, 'BOX-6X6-001', 'PVC Square Box 6x6', '6x6 PVC Square Junction Box', 'Nos', 'Conduits', 'active', 0, 'fas fa-square'],
        [25, 'BOX-5X5-001', 'PVC Square Box 5x5', '5x5 PVC Square Junction Box', 'Nos', 'Conduits', 'active', 0, 'fas fa-square'],
        [26, 'POLE-1M-001', '1m Pole', '1 Meter Mounting Pole', 'Nos', 'Mounting', 'active', 0, 'fas fa-grip-vertical'],
        [27, 'POLE-2M-001', '2m Pole', '2 Meter Mounting Pole', 'Nos', 'Mounting', 'active', 0, 'fas fa-grip-vertical'],
        [28, 'POLE-3M-001', '3m Pole', '3 Meter Mounting Pole', 'Nos', 'Mounting', 'active', 0, 'fas fa-grip-vertical'],
        [29, 'CAM-JIO-001', 'Jio Cameras', 'Jio Security Cameras', 'Nos', 'Cameras', 'active', 1, 'fas fa-video'],
        [30, 'BRG-JIO-001', 'Jio Bridge Devices', 'Jio Network Bridge Devices', 'Nos', 'Network Devices', 'active', 1, 'fas fa-wifi'],
        [31, 'SW-CIS-24P-001', '24 Port CISCO Meraki POE Switches', '24 Port CISCO Meraki POE Network Switch', 'Nos', 'Network Devices', 'active', 1, 'fas fa-network-wired']
    ];
    
    $insertSql = "INSERT INTO boq_items (id, item_code, item_name, description, unit, category, status, need_serial_number, icon_class) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($insertSql);
    
    $insertedCount = 0;
    foreach ($insertData as $row) {
        if ($stmt->execute($row)) {
            $insertedCount++;
        }
    }
    
    echo "✓ Inserted {$insertedCount} BOQ items\n";
    
    // Set AUTO_INCREMENT
    $db->exec("ALTER TABLE boq_items AUTO_INCREMENT = 32");
    
    // Verify the migration
    echo "\n=== Verification ===\n";
    
    // Check table structure
    $stmt = $db->query("DESCRIBE boq_items");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Updated table structure:\n";
    foreach ($columns as $column) {
        echo "  - {$column['Field']} ({$column['Type']})\n";
    }
    
    // Check data count
    $stmt = $db->query("SELECT COUNT(*) as total FROM boq_items");
    $total = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "\nTotal BOQ items: {$total['total']}\n";
    
    // Check categories
    $stmt = $db->query("SELECT category, COUNT(*) as count FROM boq_items GROUP BY category ORDER BY category");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "\nCategories:\n";
    foreach ($categories as $cat) {
        echo "  - {$cat['category']}: {$cat['count']} items\n";
    }
    
    // Test the BoqItem model
    echo "\n=== Testing BoqItem Model ===\n";
    require_once __DIR__ . '/../models/BoqItem.php';
    
    $boqModel = new BoqItem();
    
    // Test getActive method
    $activeItems = $boqModel->getActive();
    echo "Active items from model: " . count($activeItems) . "\n";
    
    // Test getCategories method
    $categories = $boqModel->getCategories();
    echo "Categories from model: " . implode(', ', $categories) . "\n";
    
    echo "\n✅ BOQ migration completed successfully!\n";
    echo "You can now access the BOQ management at: /admin/boq/\n";
    
} catch (Exception $e) {
    echo "❌ Error migrating BOQ: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>