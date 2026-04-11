<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();

echo "--- MODULES ---\n";
$stmt = $db->query("SELECT * FROM modules");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode($row) . "\n";
}

echo "--- PERMISSIONS (Surveys related) ---\n";
$stmt = $db->query("SELECT p.*, m.name as module_name FROM permissions p JOIN modules m ON p.module_id = m.id WHERE m.name LIKE '%survey%' OR p.name LIKE '%survey%'");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode($row) . "\n";
}
?>
