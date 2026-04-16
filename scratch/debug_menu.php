<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Menu.php';

$db = Database::getInstance()->getConnection();

echo "--- Modules ---\n";
$stmt = $db->query("SELECT id, name, display_name FROM modules");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode($row) . "\n";
}

echo "\n--- Permissions (Sample) ---\n";
$stmt = $db->query("SELECT id, module_id, name, display_name FROM permissions LIMIT 20");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode($row) . "\n";
}

echo "\n--- Active Menu Items ---\n";
$stmt = $db->query("SELECT id, parent_id, title, url, module_id FROM menu_items WHERE status = 'active'");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode($row) . "\n";
}

echo "\n--- User Role and Role ID check ---\n";
$stmt = $db->query("SELECT id, username, role, role_id FROM users WHERE username = 'Bela' OR role = 'Inventory'");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode($row) . "\n";
}

echo "\n--- Role Permissions for Inventory ---\n";
$stmt = $db->query("
    SELECT rp.role_id, p.module_id, p.name 
    FROM role_permissions rp 
    JOIN permissions p ON rp.permission_id = p.id 
    JOIN roles r ON rp.role_id = r.id 
    WHERE r.name = 'Inventory'
");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode($row) . "\n";
}
