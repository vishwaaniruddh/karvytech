<?php
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance()->getConnection();

// Get all users
$stmt = $db->query("SELECT id, username, role FROM users WHERE status = 'active'");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Active Users:\n";
foreach ($users as $user) {
    echo "- ID: {$user['id']}, Username: {$user['username']}, Role: {$user['role']}\n";
}

// Get Survey menu IDs
$stmt = $db->query("
    SELECT id, title, parent_id 
    FROM menu_items 
    WHERE title = 'Survey' OR parent_id = (SELECT id FROM menu_items WHERE title = 'Survey' AND parent_id IS NULL)
    ORDER BY parent_id, sort_order
");
$surveyMenus = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "\nSurvey Menus:\n";
foreach ($surveyMenus as $menu) {
    echo "- ID: {$menu['id']}, Title: {$menu['title']}, Parent: " . ($menu['parent_id'] ?: 'NULL') . "\n";
}

// Grant permissions to all active users for all survey menus
echo "\nGranting Survey menu permissions to all active users...\n";

foreach ($users as $user) {
    foreach ($surveyMenus as $menu) {
        $stmt = $db->prepare("
            INSERT INTO user_menu_permissions (user_id, menu_item_id, can_access) 
            VALUES (?, ?, 1)
            ON DUPLICATE KEY UPDATE can_access = 1
        ");
        $stmt->execute([$user['id'], $menu['id']]);
        echo "Granted access to '{$menu['title']}' for user '{$user['username']}'\n";
    }
}

echo "\nPermissions granted successfully!\n";