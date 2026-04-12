<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();

$tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo "Tables:\n";
foreach ($tables as $table) {
    if (strpos($table, 'boq') !== false || strpos($table, 'material') !== false || strpos($table, 'item') !== false) {
        echo " - $table\n";
    }
}

$targetTables = ['boq_masters', 'boq_items', 'material_requests', 'material_request_items'];
foreach ($targetTables as $table) {
    if (in_array($table, $tables)) {
        echo "\nSchema for $table:\n";
        $stmt = $db->query("DESCRIBE $table");
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}
