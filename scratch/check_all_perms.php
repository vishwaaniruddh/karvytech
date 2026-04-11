<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();

echo "--- MISSING USER PERMISSIONS (Superadmin 57) ---\n";
$stmt = $db->query("SELECT id, title FROM menu_items WHERE status = 'active' AND id NOT IN (SELECT menu_item_id FROM user_menu_permissions WHERE user_id = 57 AND can_access = 1)");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode($row) . "\n";
}

echo "\n--- MISSING ROLE PERMISSIONS (superadmin role) ---\n";
$stmt = $db->query("SELECT id, title FROM menu_items WHERE status = 'active' AND id NOT IN (SELECT menu_item_id FROM role_menu_permissions WHERE role = 'superadmin' AND can_access = 1)");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode($row) . "\n";
}
?>
