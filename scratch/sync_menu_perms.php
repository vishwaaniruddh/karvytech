<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    // Get all active menu items
    $stmt = $db->query("SELECT id FROM menu_items WHERE status = 'active'");
    $menuIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "Found " . count($menuIds) . " menu items.\n";

    // Grant access to 'superadmin' role for ALL items
    $insertStmt = $db->prepare("INSERT INTO role_menu_permissions (role, menu_item_id, can_access) VALUES ('superadmin', ?, 1) ON DUPLICATE KEY UPDATE can_access = 1");
    
    foreach ($menuIds as $id) {
        $insertStmt->execute([$id]);
    }
    echo "Granted all menu permissions to 'superadmin' role.\n";

    // Also grant explicitly to user with ID 1 (common superadmin ID) to be safe
    $userInsertStmt = $db->prepare("INSERT INTO user_menu_permissions (user_id, menu_item_id, can_access) VALUES (1, ?, 1) ON DUPLICATE KEY UPDATE can_access = 1");
    foreach ($menuIds as $id) {
        $userInsertStmt->execute([$id]);
    }
    echo "Granted all menu permissions to User ID 1.\n";

    $db->commit();
    echo "Sync complete.\n";

} catch (Exception $e) {
    if (isset($db)) $db->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
