<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();

echo "INDEXES FOR inventory_stock:\n";
$stmt = $db->query("SHOW INDEX FROM inventory_stock");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\nINDEXES FOR boq_items:\n";
$stmt = $db->query("SHOW INDEX FROM boq_items");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
