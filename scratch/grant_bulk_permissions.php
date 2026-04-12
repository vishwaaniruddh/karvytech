<?php
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance()->getConnection();

// Get all active users
$stmt = $db->query("SELECT id, username, role FROM users WHERE status = 'active'");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get Bulk Operations menu IDs
$stmt = $db->query("
    SELECT id, title, parent_id 
    FROM menu_items 
    WHERE title = 'Bulk Operations' OR parent_id = (SELECT id FROM menu_items WHERE title = 'Bulk Operations' AND parent_id IS NULL)
    ORDER BY parent_id, sort_order
");
$bulkMenus = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Bulk Operations Menus:\n";
foreach ($bulkMenus as $menu) {
    echo "- ID: {$menu['id']}, Title: {$menu['title']}, Parent: " . ($menu['parent_id'] ?: 'NULL') . "\n";
}

echo "\nGranting Bulk Operations menu permissions to all active users...\n";

foreach ($users as $user) {
    foreach ($bulkMenus as $menu) {
        $stmt = $db->prepare("
            INSERT INTO user_menu_permissions (user_id, menu_item_id, can_access) 
            VALUES (?, ?, 1)
            ON DUPLICATE KEY UPDATE can_access = 1
        ");
        $stmt->execute([$user['id'], $menu['id']]);
        echo "Granted access to '{$menu['title']}' for user '{$user['username']}'\n";
    }
}

echo "\nBulk Operations permissions granted successfully!\n";