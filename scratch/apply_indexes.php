<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();

$queries = [
    "ALTER TABLE inventory_stock ADD INDEX idx_boq_item (boq_item_id)",
    "ALTER TABLE inventory_stock ADD INDEX idx_item_status (item_status)",
    "ALTER TABLE inventory_stock ADD INDEX idx_warehouse_id (warehouse_id)",
    "ALTER TABLE inventory_stock ADD INDEX idx_location_type (location_type)",
    "ALTER TABLE inventory_stock ADD INDEX idx_created_at (created_at)",
    "ALTER TABLE inventory_stock ADD INDEX idx_activity_status (activity_status)",
    "ALTER TABLE boq_items ADD INDEX idx_boq_status (status)"
];

foreach ($queries as $q) {
    try {
        echo "Executing: $q\n";
        $db->exec($q);
        echo "Success.\n";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
