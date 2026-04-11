<?php
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance()->getConnection();

try {
    $db->beginTransaction();

    // 1. Get all active menu items
    $stmt = $db->query("SELECT id FROM menu_items WHERE status = 'active'");
    $menuIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "Found " . count($menuIds) . " active menu items.\n";

    // 2. Sync Role Permissions for 'superadmin'
    $stmtRole = $db->prepare("INSERT INTO role_menu_permissions (role, menu_item_id, can_access) 
                              VALUES ('superadmin', ?, 1) 
                              ON DUPLICATE KEY UPDATE can_access = 1");
    
    foreach ($menuIds as $id) {
        $stmtRole->execute([$id]);
    }
    echo "Synchronized role_menu_permissions for 'superadmin'.\n";

    // 3. Sync User Permissions for Superadmin (ID 57)
    $userId = 57;
    $stmtUser = $db->prepare("INSERT INTO user_menu_permissions (user_id, menu_item_id, can_access) 
                              VALUES (?, ?, 1) 
                              ON DUPLICATE KEY UPDATE can_access = 1");
    
    foreach ($menuIds as $id) {
        $stmtUser->execute([$userId, $id]);
    }
    echo "Synchronized user_menu_permissions for user ID 57.\n";

    $db->commit();
    echo "SUCCESS: Permissions synchronized successfully.\n";

} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
