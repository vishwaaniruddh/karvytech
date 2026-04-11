<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Contractor Role (ID 4) Specific Permissions:\n";
    $stmt = $db->query("
        SELECT rp.*, m.name as module, p.name as permission
        FROM role_permissions rp
        JOIN permissions p ON rp.permission_id = p.id
        JOIN modules m ON p.module_id = m.id
        WHERE rp.role_id = 4 AND m.name = 'surveys'
    ");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo " - rp_id {$row['id']}: {$row['module']}.{$row['permission']} (perm_id: {$row['permission_id']})\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
