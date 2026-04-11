<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Menu.php';

try {
    $db = Database::getInstance()->getConnection();
    $menuModel = new Menu();
    
    echo "Granting menu permissions to admin users...\n";
    
    // Get all admin users
    $stmt = $db->prepare("SELECT id, username FROM users WHERE role = 'admin' AND status = 'active'");
    $stmt->execute();
    $adminUsers = $stmt->fetchAll();
    
    if (empty($adminUsers)) {
        echo "No active admin users found.\n";
        exit;
    }
    
    // Get all menu items
    $allMenus = $menuModel->getAllMenuItems();
    
    foreach ($adminUsers as $admin) {
        echo "Granting permissions to admin: " . $admin['username'] . "\n";
        
        // Grant access to all menu items for admin users
        foreach ($allMenus as $menu) {
            $menuModel->setUserPermission($admin['id'], $menu['id'], true);
        }
        
        echo "  - Granted access to " . count($allMenus) . " menu items\n";
    }
    
    echo "Admin permissions granted successfully!\n";
    
} catch (Exception $e) {
    echo "Error granting admin permissions: " . $e->getMessage() . "\n";
}
?>