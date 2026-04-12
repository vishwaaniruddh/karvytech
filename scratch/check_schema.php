<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();

function getCol($db, $table, $col) {
    $stmt = $db->query("DESCRIBE $table $col");
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

echo "inventory_stock.boq_item_id: ";
print_r(getCol($db, 'inventory_stock', 'boq_item_id'));

echo "\nboq_items.id: ";
print_r(getCol($db, 'boq_items', 'id'));
