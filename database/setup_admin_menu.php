<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Setting up admin menu system...\n";
    
    // Clear existing menu data
    $db->exec("DELETE FROM user_menu_permissions");
    $db->exec("DELETE FROM role_menu_permissions");
    $db->exec("DELETE FROM menu_items");
    
    // Insert main menu items
    $menuItems = [
        // Main Section
        ['id' => 1, 'parent_id' => null, 'title' => 'Dashboard', 'icon' => 'dashboard', 'url' => '/admin/dashboard.php', 'sort_order' => 1],
        ['id' => 2, 'parent_id' => null, 'title' => 'Sites', 'icon' => 'location', 'url' => '/admin/sites/', 'sort_order' => 2],
        
        // Administration Section
        ['id' => 10, 'parent_id' => null, 'title' => 'Administration', 'icon' => 'settings', 'url' => null, 'sort_order' => 10],
        ['id' => 11, 'parent_id' => 10, 'title' => 'Users', 'icon' => 'users', 'url' => '/admin/users/', 'sort_order' => 1],
        ['id' => 12, 'parent_id' => 10, 'title' => 'Vendors', 'icon' => 'vendor', 'url' => '/admin/vendors/', 'sort_order' => 2],
        ['id' => 13, 'parent_id' => 10, 'title' => 'Masters', 'icon' => 'settings', 'url' => '/admin/masters/', 'sort_order' => 3],
        ['id' => 14, 'parent_id' => 10, 'title' => 'BOQ Management', 'icon' => 'boq', 'url' => '/admin/boq/', 'sort_order' => 4],
        
        // Inventory Section
        ['id' => 20, 'parent_id' => null, 'title' => 'Inventory', 'icon' => 'inventory', 'url' => null, 'sort_order' => 20],
        ['id' => 21, 'parent_id' => 20, 'title' => 'All Stocks', 'icon' => 'inventory', 'url' => '/admin/inventory/', 'sort_order' => 1],
        ['id' => 22, 'parent_id' => 20, 'title' => 'Material Requests', 'icon' => 'requests', 'url' => '/admin/requests/', 'sort_order' => 2],
        ['id' => 23, 'parent_id' => 20, 'title' => 'Material Received', 'icon' => 'inventory', 'url' => '/admin/inventory/inwards/', 'sort_order' => 3],
        ['id' => 24, 'parent_id' => 20, 'title' => 'Material Dispatches', 'icon' => 'inventory', 'url' => '/admin/inventory/dispatches/', 'sort_order' => 4],
        
        // Operations Section
        ['id' => 30, 'parent_id' => null, 'title' => 'Operations', 'icon' => 'settings', 'url' => null, 'sort_order' => 30],
        ['id' => 31, 'parent_id' => 30, 'title' => 'Surveys', 'icon' => 'reports', 'url' => '/admin/surveys/', 'sort_order' => 1],
        ['id' => 32, 'parent_id' => 30, 'title' => 'Installations', 'icon' => 'installation', 'url' => '/admin/installations/', 'sort_order' => 2],
        ['id' => 33, 'parent_id' => 30, 'title' => 'Reports', 'icon' => 'reports', 'url' => '/admin/reports/', 'sort_order' => 3],
    ];
    
    $stmt = $db->prepare("
        INSERT INTO menu_items (id, parent_id, title, icon, url, sort_order, status) 
        VALUES (?, ?, ?, ?, ?, ?, 'active')
    ");
    
    foreach ($menuItems as $item) {
        $stmt->execute([
            $item['id'],
            $item['parent_id'],
            $item['title'],
            $item['icon'],
            $item['url'],
            $item['sort_order']
        ]);
    }
    
    echo "Menu items created successfully.\n";
    
    echo "Menu items created successfully.\n";
    echo "Note: No default permissions assigned. Use the Users module to assign menu permissions to individual users.\n";
    
    echo "Admin menu system setup completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error setting up admin menu: " . $e->getMessage() . "\n";
}
?>