<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Check if reports menu already exists
    $checkSql = "SELECT id FROM menu_items WHERE title = 'Reports' AND url = '/admin/reports/'";
    $stmt = $db->prepare($checkSql);
    $stmt->execute();
    
    if ($stmt->fetch()) {
        echo "Reports menu item already exists.\n";
        exit;
    }
    
    // Insert reports menu item
    $insertSql = "INSERT INTO menu_items (title, url, icon, parent_id, sort_order, status, created_at) 
                  VALUES ('Reports', '/admin/reports/', 'reports', NULL, 50, 'active', NOW())";
    
    $stmt = $db->prepare($insertSql);
    $stmt->execute();
    
    $reportsMenuId = $db->lastInsertId();
    echo "Reports menu item created with ID: $reportsMenuId\n";
    
    // Grant access to admin role
    $rolePermissionSql = "INSERT INTO role_menu_permissions (role, menu_item_id, can_access, created_at) 
                          VALUES ('admin', ?, TRUE, NOW())";
    
    $stmt = $db->prepare($rolePermissionSql);
    $stmt->execute([$reportsMenuId]);
    
    echo "Admin role permission granted for Reports menu.\n";
    
    // Grant access to all existing admin users
    $adminUsersSql = "SELECT id FROM users WHERE role = 'admin'";
    $stmt = $db->prepare($adminUsersSql);
    $stmt->execute();
    $adminUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $userPermissionSql = "INSERT INTO user_menu_permissions (user_id, menu_item_id, can_access, created_at) 
                          VALUES (?, ?, TRUE, NOW())";
    $stmt = $db->prepare($userPermissionSql);
    
    foreach ($adminUsers as $user) {
        $stmt->execute([$user['id'], $reportsMenuId]);
        echo "Permission granted to admin user ID: {$user['id']}\n";
    }
    
    echo "Reports menu setup completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>