<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();

try {
    $db->exec("ALTER TABLE boq_master_items ADD COLUMN quantity INT DEFAULT 1 AFTER boq_item_id");
    $db->exec("ALTER TABLE boq_master_items ADD COLUMN notes TEXT NULL AFTER quantity");
    echo "Columns added successfully to boq_master_items!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
