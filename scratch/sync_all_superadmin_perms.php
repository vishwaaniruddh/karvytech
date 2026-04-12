<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    // 1. Identify the new menu items under Users (ID 10)
    $newSubmenuTitles = ['All Users', 'Add User', 'Roles', 'Permissions'];
    $stmt = $db->prepare("SELECT id FROM menu_items WHERE parent_id = 10 AND title IN (?, ?, ?, ?)");
    $stmt->execute($newSubmenuTitles);
    $newMenuIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($newMenuIds)) {
        die("Error: Could not find the new submenu items in the database. Please ensure restructure_users_menu.php was run successfully.\n");
    }

    echo "Found " . count($newMenuIds) . " menu items to sync.\n";

    // 2. Find all superadmins and users with direct permissions to ID 10
    // We'll sync to anyone who has access to the parent 'Users' (10)
    $stmt = $db->prepare("
        SELECT DISTINCT user_id FROM user_menu_permissions WHERE menu_item_id = 10 AND can_access = 1
        UNION
        SELECT id as user_id FROM users WHERE role = 'superadmin'
    ");
    $stmt->execute();
    $userIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "Found " . count($userIds) . " users to grant permissions to.\n";

    // 3. Grant permissions
    $insertStmt = $db->prepare("INSERT INTO user_menu_permissions (user_id, menu_item_id, can_access) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE can_access = 1");
    
    foreach ($userIds as $userId) {
        foreach ($newMenuIds as $menuId) {
            $insertStmt->execute([$userId, $menuId]);
        }
    }

    // 4. Also ensure superadmin role has access
    $roleInsertStmt = $db->prepare("INSERT INTO role_menu_permissions (role, menu_item_id, can_access) VALUES ('superadmin', ?, 1) ON DUPLICATE KEY UPDATE can_access = 1");
    foreach ($newMenuIds as $menuId) {
        $roleInsertStmt->execute([$menuId]);
    }

    $db->commit();
    echo "Permission sync for all superadmins and relevant users completed successfully.\n";

} catch (Exception $e) {
    if (isset($db)) $db->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
