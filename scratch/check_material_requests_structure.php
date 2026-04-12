<?php
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance()->getConnection();

echo "Material Requests table structure:\n";
echo "==================================\n";

$stmt = $db->query("DESCRIBE material_requests");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($columns as $column) {
    echo sprintf("%-20s %-15s %-5s %-5s %-10s %s\n", 
        $column['Field'], 
        $column['Type'], 
        $column['Null'], 
        $column['Key'], 
        $column['Default'] ?: 'NULL',
        $column['Extra']
    );
}

echo "\nSample data from material_requests:\n";
echo "===================================\n";

$stmt = $db->query("SELECT * FROM material_requests LIMIT 3");
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($requests as $request) {
    echo "ID: " . $request['id'] . "\n";
    foreach ($request as $key => $value) {
        if ($key !== 'id') {
            echo "  $key: " . ($value ?: 'NULL') . "\n";
        }
    }
    echo "\n";
}