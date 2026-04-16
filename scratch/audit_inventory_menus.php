<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();

echo "Auditing Inventory sub-menus...\n";

// Root Inventory menu
$db->exec("UPDATE menu_items SET module_id = 5 WHERE id = 4");

// Sub-menus
$subMenus = [
    'All Stocks',
    'Material Requests',
    'Material Received',
    'Material Dispatches',
    'Quantity Audits'
];

foreach ($subMenus as $title) {
    $stmt = $db->prepare("UPDATE menu_items SET module_id = 5 WHERE title = ? AND parent_id = 4");
    $stmt->execute([$title]);
    echo "Updated sub-menu '$title' to module 5. Affected: " . $stmt->rowCount() . "\n";
}

echo "\nInventory Menu Audit Complete.\n";
