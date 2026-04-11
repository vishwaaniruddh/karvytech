<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // First, find the Inventory parent menu item
    $stmt = $db->prepare("SELECT id FROM menu_items WHERE title = 'Inventory' AND parent_id IS NULL");
    $stmt->execute();
    $inventoryMenu = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$inventoryMenu) {
        echo "Inventory parent menu not found. Creating it first...\n";
        
        // Create Inventory parent menu
        $stmt = $db->prepare("INSERT INTO menu_items (parent_id, title, icon, url, sort_order, status) VALUES (NULL, 'Inventory', 'inventory', NULL, 30, 'active')");
        $stmt->execute();
        $inventoryMenuId = $db->lastInsertId();
        
        echo "Created Inventory parent menu with ID: $inventoryMenuId\n";
    } else {
        $inventoryMenuId = $inventoryMenu['id'];
        echo "Found existing Inventory menu with ID: $inventoryMenuId\n";
    }
    
    // Check if Quantity Audits menu already exists
    $stmt = $db->prepare("SELECT id FROM menu_items WHERE title = 'Quantity Audits' AND parent_id = ?");
    $stmt->execute([$inventoryMenuId]);
    $existingAudit = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingAudit) {
        echo "Quantity Audits menu already exists with ID: " . $existingAudit['id'] . "\n";
    } else {
        // Add Quantity Audits menu item
        $stmt = $db->prepare("INSERT INTO menu_items (parent_id, title, icon, url, sort_order, status) VALUES (?, 'Quantity Audits', 'reports', 'admin/inventory/quantity-audits.php', 60, 'active')");
        $stmt->execute([$inventoryMenuId]);
        $auditMenuId = $db->lastInsertId();
        
        echo "Created Quantity Audits menu item with ID: $auditMenuId\n";
        
        // Grant access to admin users
        // First, get all admin users
        $stmt = $db->prepare("SELECT id FROM users WHERE role = 'admin'");
        $stmt->execute();
        $adminUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($adminUsers as $admin) {
            $stmt = $db->prepare("INSERT INTO user_menu_permissions (user_id, menu_item_id, can_access) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE can_access = 1");
            $stmt->execute([$admin['id'], $auditMenuId]);
            echo "Granted access to admin user ID: " . $admin['id'] . "\n";
        }
        
        // Also grant role-based permission for admin role
        $stmt = $db->prepare("INSERT INTO role_menu_permissions (role, menu_item_id, can_access) VALUES ('admin', ?, 1) ON DUPLICATE KEY UPDATE can_access = 1");
        $stmt->execute([$auditMenuId]);
        echo "Granted role-based access to admin role\n";
    }
    
    echo "Quantity Audits menu setup completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error setting up menu: " . $e->getMessage() . "\n";
}
?>