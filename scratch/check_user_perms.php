<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();

$userId = 57;
echo "--- PERMISSIONS for User ID $userId ---\n";
// User specific permissions
$stmt = $db->prepare("SELECT p.name, p.display_name, m.name as module_name FROM user_permissions up JOIN permissions p ON up.permission_id = p.id JOIN modules m ON p.module_id = m.id WHERE up.user_id = ?");
$stmt->execute([$userId]);
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode($row) . "\n";
}

// Role based permissions
$stmt = $db->prepare("SELECT p.name, p.display_name, m.name as module_name 
                      FROM users u 
                      JOIN role_permissions rp ON u.role_id = rp.role_id 
                      JOIN permissions p ON rp.permission_id = p.id 
                      JOIN modules m ON p.module_id = m.id 
                      WHERE u.id = ?");
$stmt->execute([$userId]);
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode($row) . "\n";
}
?>
