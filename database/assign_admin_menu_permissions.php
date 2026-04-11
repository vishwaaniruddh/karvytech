<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Assigning menu permissions to admin role...\n\n";
    
    // Get all active menu items
    $stmt = $db->query("SELECT id, title FROM menu_items WHERE status = 'active' ORDER BY id");
    $menuItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($menuItems)) {
        echo "❌ No menu items found. Please run database/setup_admin_menu.php first.\n";
        exit(1);
    }
    
    echo "Found " . count($menuItems) . " menu items.\n\n";
    
    // Assign all menu items to admin role
    $stmt = $db->prepare("
        INSERT INTO role_menu_permissions (role, menu_item_id, can_access, created_at) 
        VALUES ('admin', ?, TRUE, NOW())
        ON DUPLICATE KEY UPDATE can_access = TRUE
    ");
    
    $assigned = 0;
    foreach ($menuItems as $item) {
        $stmt->execute([$item['id']]);
        echo "✓ Assigned '{$item['title']}' to admin role\n";
        $assigned++;
    }
    
    echo "\n✅ Successfully assigned {$assigned} menu items to admin role!\n\n";
    
    // Now assign to all existing admin users
    $stmt = $db->query("SELECT id, username, email FROM users WHERE role = 'admin' AND status = 'active'");
    $adminUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($adminUsers)) {
        echo "Found " . count($adminUsers) . " admin users. Assigning menu permissions...\n\n";
        
        $stmt = $db->prepare("
            INSERT INTO user_menu_permissions (user_id, menu_item_id, can_access, created_at) 
            VALUES (?, ?, TRUE, NOW())
            ON DUPLICATE KEY UPDATE can_access = TRUE
        ");
        
        foreach ($adminUsers as $user) {
            echo "Assigning menus to: {$user['username']} ({$user['email']})\n";
            foreach ($menuItems as $item) {
                $stmt->execute([$user['id'], $item['id']]);
            }
            echo "  ✓ Assigned all {$assigned} menu items\n";
        }
        
        echo "\n✅ Successfully assigned menu permissions to all admin users!\n";
    } else {
        echo "ℹ️  No admin users found.\n";
    }
    
    echo "\n🎉 Menu permission assignment completed!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
