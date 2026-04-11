<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();

$tables = ['modules', 'permissions', 'module_permissions', 'role_permissions', 'user_permissions'];

foreach ($tables as $table) {
    echo "--- TABLE: $table ---\n";
    $stmt = $db->query("DESCRIBE $table");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode($row) . "\n";
    }
    
    echo "--- DATA: $table (Top 5) ---\n";
    $stmt = $db->query("SELECT * FROM $table LIMIT 5");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode($row) . "\n";
    }
    echo "\n";
}
?>
