<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Adding Installation menu to the system...\n";
    
    // Check if Installation menu already exists
    $stmt = $db->prepare("SELECT id FROM menu_items WHERE title = 'Installation' AND parent_id IS NULL");
    $stmt->execute();
    $existingMenu = $stmt->fetch();
    
    if ($existingMenu) {
        echo "Installation menu already exists with ID: " . $existingMenu['id'] . "\n";
        $installationMenuId = $existingMenu['id'];
    } else {
        // Insert Installation main menu item
        $stmt = $db->prepare("
            INSERT INTO menu_items (parent_id, title, icon, url, sort_order, status) 
            VALUES (NULL, 'Installation', 'settings', '/admin/installations/', 60, 'active')
        ");
        $stmt->execute();
        $installationMenuId = $db->lastInsertId();
        echo "✓ Created Installation main menu with ID: $installationMenuId\n";
    }
    
    // Add submenu items
    $subMenuItems = [
        [
            'title' => 'All Installations',
            'icon' => 'inventory',
            'url' => '/admin/installations/',
            'sort_order' => 1
        ],
        [
            'title' => 'Active Installations',
            'icon' => 'requests',
            'url' => '/admin/installations/?status=in_progress',
            'sort_order' => 2
        ],
        [
            'title' => 'Completed Installations',
            'icon' => 'reports',
            'url' => '/admin/installations/?status=completed',
            'sort_order' => 3
        ]
    ];
    
    foreach ($subMenuItems as $item) {
        // Check if submenu item already exists
        $stmt = $db->prepare("SELECT id FROM menu_items WHERE title = ? AND parent_id = ?");
        $stmt->execute([$item['title'], $installationMenuId]);
        $existingSubMenu = $stmt->fetch();
        
        if (!$existingSubMenu) {
            $stmt = $db->prepare("
                INSERT INTO menu_items (parent_id, title, icon, url, sort_order, status) 
                VALUES (?, ?, ?, ?, ?, 'active')
            ");
            $stmt->execute([
                $installationMenuId,
                $item['title'],
                $item['icon'],
                $item['url'],
                $item['sort_order']
            ]);
            echo "✓ Created submenu: " . $item['title'] . "\n";
        } else {
            echo "- Submenu already exists: " . $item['title'] . "\n";
        }
    }
    
    // Add permissions for admin role
    $stmt = $db->prepare("
        INSERT IGNORE INTO role_menu_permissions (role, menu_item_id, can_access) 
        SELECT 'admin', id, TRUE FROM menu_items WHERE title = 'Installation' OR parent_id = ?
    ");
    $stmt->execute([$installationMenuId]);
    echo "✓ Added admin permissions for Installation menu\n";
    
    // Add installation icon to dynamic sidebar if not exists
    echo "\nInstallation menu setup completed!\n";
    echo "Menu structure:\n";
    echo "- Installation (Main Menu)\n";
    echo "  - All Installations\n";
    echo "  - Active Installations\n";
    echo "  - Completed Installations\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>