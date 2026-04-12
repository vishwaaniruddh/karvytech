<?php
require_once __DIR__ . '/../config/database.php';
$conn = Database::getInstance()->getConnection();
$res = $conn->query("SELECT id, site_id FROM inventory_dispatches WHERE site_id NOT IN (SELECT id FROM sites)")->fetchAll(PDO::FETCH_ASSOC);
echo "Orphaned dispatches count: " . count($res) . "\n";
print_r($res);
?>
