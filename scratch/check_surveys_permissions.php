<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Roles:\n";
    $stmt = $db->query("SELECT id, name FROM roles");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo " - {$row['id']}: {$row['name']}\n";
    }
    
    echo "\nPermissions for 'contractor' role (ID 4):\n";
    $stmt = $db->query("
        SELECT m.name as module_name, p.name as perm_name
        FROM role_permissions rp
        JOIN permissions p ON rp.permission_id = p.id
        JOIN modules m ON p.module_id = m.id
        JOIN roles r ON rp.role_id = r.id
        WHERE r.id = 4 OR r.name = 'contractor'
    ");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo " - {$row['module_name']}.{$row['perm_name']}\n";
    }

    echo "\nSearching for 'surveys.approve' permission overall:\n";
    $stmt = $db->query("
        SELECT r.name as role_name, m.name as module_name, p.name as perm_name
        FROM role_permissions rp
        JOIN permissions p ON rp.permission_id = p.id
        JOIN modules m ON p.module_id = m.id
        JOIN roles r ON rp.role_id = r.id
        WHERE m.name = 'surveys' AND p.name = 'approve'
    ");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo " - Role '{$row['role_name']}' has {$row['module_name']}.{$row['perm_name']}\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
