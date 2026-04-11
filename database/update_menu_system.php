<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Creating menu system tables...\n";
    
    // Create menu_items table
    $db->exec("
        CREATE TABLE IF NOT EXISTS menu_items (
            id INT PRIMARY KEY AUTO_INCREMENT,
            parent_id INT NULL,
            title VARCHAR(100) NOT NULL,
            icon VARCHAR(100) NULL,
            url VARCHAR(255) NULL,
            sort_order INT DEFAULT 0,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (parent_id) REFERENCES menu_items(id) ON DELETE CASCADE,
            INDEX idx_parent (parent_id),
            INDEX idx_sort_order (sort_order),
            INDEX idx_status (status)
        )
    ");
    echo "✓ Created menu_items table\n";
    
    // Create user_menu_permissions table
    $db->exec("
        CREATE TABLE IF NOT EXISTS user_menu_permissions (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            menu_item_id INT NOT NULL,
            can_access BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_menu (user_id, menu_item_id),
            INDEX idx_user (user_id),
            INDEX idx_menu (menu_item_id)
        )
    ");
    echo "✓ Created user_menu_permissions table\n";
    
    // Create role_menu_permissions table
    $db->exec("
        CREATE TABLE IF NOT EXISTS role_menu_permissions (
            id INT PRIMARY KEY AUTO_INCREMENT,
            role ENUM('admin', 'vendor') NOT NULL,
            menu_item_id INT NOT NULL,
            can_access BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE,
            UNIQUE KEY unique_role_menu (role, menu_item_id),
            INDEX idx_role (role),
            INDEX idx_menu (menu_item_id)
        )
    ");
    echo "✓ Created role_menu_permissions table\n";
    
    // Check if menu items already exist
    $stmt = $db->query("SELECT COUNT(*) FROM menu_items");
    if ($stmt->fetchColumn() == 0) {
        echo "Inserting menu items...\n";
        
        // Insert menu items
        $menuItems = [
            // Main Menu Items
            [1, NULL, 'Dashboard', 'dashboard', '/admin/dashboard.php', 1],
            [2, NULL, 'Sites', 'location', '/admin/sites/', 2],
            [3, NULL, 'Admin', 'settings', NULL, 3],
            [4, NULL, 'Inventory', 'inventory', '/admin/inventory/', 4],
            [5, NULL, 'Material Requests', 'requests', '/admin/requests/', 5],
            [6, NULL, 'Reports', 'reports', '/admin/reports/', 6],
            
            // Admin Submenu Items
            [10, 3, 'Users', 'users', '/admin/users/', 1],
            [11, 3, 'Location', 'location-sub', NULL, 2],
            [12, 3, 'Business', 'business', NULL, 3],
            [13, 3, 'BOQ', 'boq', '/admin/boq/', 4],
            
            // Location Submenu Items
            [20, 11, 'Countries', 'country', '/admin/masters/?type=countries', 1],
            [21, 11, 'Zones', 'zone', '/admin/masters/?type=zones', 2],
            [22, 11, 'States', 'state', '/admin/masters/?type=states', 3],
            [23, 11, 'Cities', 'city', '/admin/masters/?type=cities', 4],
            
            // Business Submenu Items
            [30, 12, 'Banks', 'bank', '/admin/masters/?type=banks', 1],
            [31, 12, 'Customers', 'customer', '/admin/masters/?type=customers', 2],
            [32, 12, 'Vendors', 'vendor', '/admin/vendors/', 3]
        ];
        
        $stmt = $db->prepare("INSERT INTO menu_items (id, parent_id, title, icon, url, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($menuItems as $item) {
            $stmt->execute($item);
        }
        echo "✓ Inserted menu items\n";
        
        // Insert role permissions
        $rolePermissions = [
            // Admin role - full access
            ['admin', 1], ['admin', 2], ['admin', 3], ['admin', 4], ['admin', 5], ['admin', 6],
            ['admin', 10], ['admin', 11], ['admin', 12], ['admin', 13],
            ['admin', 20], ['admin', 21], ['admin', 22], ['admin', 23],
            ['admin', 30], ['admin', 31], ['admin', 32],
            
            // Vendor role - limited access
            ['vendor', 1], ['vendor', 2], ['vendor', 5]
        ];
        
        $stmt = $db->prepare("INSERT INTO role_menu_permissions (role, menu_item_id, can_access) VALUES (?, ?, TRUE)");
        foreach ($rolePermissions as $perm) {
            $stmt->execute($perm);
        }
        echo "✓ Inserted role permissions\n";
    } else {
        echo "Menu items already exist, skipping...\n";
    }
    
    echo "\nMenu system created successfully!\n";
    
} catch (Exception $e) {
    echo "Error creating menu system: " . $e->getMessage() . "\n";
}
?>