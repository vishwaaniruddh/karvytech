<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();

$stmt = $db->prepare("
    SELECT p.id, p.module_id, p.name, m.name as module_name
    FROM role_permissions rp 
    JOIN permissions p ON rp.permission_id = p.id 
    JOIN modules m ON p.module_id = m.id
    JOIN users u ON u.role_id = rp.role_id 
    WHERE u.username = 'Bela'
");
$stmt->execute();
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode($row) . "\n";
}
