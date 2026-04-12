<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();

$queries = [
    "SELECT COUNT(*) FROM inventory_stock",
    "SELECT COUNT(*) FROM boq_items",
    "SELECT COUNT(*) FROM inventory_dispatches",
    "SELECT COUNT(*) FROM inventory_inwards"
];

foreach ($queries as $q) {
    echo "$q: " . $db->query($q)->fetchColumn() . "\n";
}
