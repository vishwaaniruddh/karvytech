<?php
require_once __DIR__ . '/../config/database.php';
$conn = Database::getInstance()->getConnection();
echo "Dispatches count: " . $conn->query("SELECT COUNT(*) FROM inventory_dispatches")->fetchColumn() . "\n";
echo "Inventory Summary count: " . $conn->query("SELECT COUNT(*) FROM inventory_summary")->fetchColumn() . "\n";
$first = $conn->query("SELECT * FROM inventory_dispatches LIMIT 1")->fetch(PDO::FETCH_ASSOC);
echo "First dispatch row:\n";
print_r($first);
?>
