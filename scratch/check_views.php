<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();

$stmt = $db->query("SHOW CREATE VIEW inventory_summary");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo "inventory_summary:\n";
echo $row['Create View'] . "\n\n";

$stmt = $db->query("SHOW CREATE VIEW warehouse_stock_summary");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo "warehouse_stock_summary:\n";
echo $row['Create View'] . "\n";
