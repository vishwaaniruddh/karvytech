<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();
echo "--- ROLE PERMISSIONS ('superadmin') ---\n";
$stmt = $db->query("SELECT * FROM role_menu_permissions WHERE role = 'superadmin' LIMIT 5");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode($row) . "\n";
}
echo "--- USER PERMISSIONS (57) ---\n";
$stmt = $db->query("SELECT * FROM user_menu_permissions WHERE user_id = 57 LIMIT 5");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode($row) . "\n";
}
?>
