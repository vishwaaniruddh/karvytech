<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

// Get current user
$currentUser = Auth::getCurrentUser();
if (!$currentUser) {
    echo "No user logged in\n";
    exit;
}

echo "Current User: " . $currentUser['username'] . " (ID: " . $currentUser['id'] . ", Role: " . $currentUser['role'] . ")\n\n";

$db = Database::getInstance()->getConnection();

// Check user permissions for Survey menus
$stmt = $db->prepare("
    SELECT m.id, m.title, m.parent_id, m.url, ump.can_access
    FROM menu_items m
    LEFT JOIN user_menu_permissions ump ON m.id = ump.menu_item_id AND ump.user_id = ?
    WHERE m.title LIKE '%Survey%' OR m.parent_id = (SELECT id FROM menu_items WHERE title = 'Survey' AND parent_id IS NULL)
    ORDER BY m.parent_id, m.sort_order
");
$stmt->execute([$currentUser['id']]);
$permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Survey Menu Permissions:\n";
echo "========================\n";
foreach ($permissions as $perm) {
    echo sprintf("ID: %d, Title: %s, Parent: %s, URL: %s, Access: %s\n", 
        $perm['id'], 
        $perm['title'], 
        $perm['parent_id'] ?: 'NULL',
        $perm['url'] ?: 'NULL',
        $perm['can_access'] === null ? 'NOT SET' : ($perm['can_access'] ? 'YES' : 'NO')
    );
}

// Check if there are any role-based permissions
echo "\nRole-based permissions:\n";
echo "=======================\n";
$stmt = $db->prepare("
    SELECT m.id, m.title, rmp.can_access
    FROM menu_items m
    LEFT JOIN role_menu_permissions rmp ON m.id = rmp.menu_item_id AND rmp.role = ?
    WHERE m.title LIKE '%Survey%' OR m.parent_id = (SELECT id FROM menu_items WHERE title = 'Survey' AND parent_id IS NULL)
    ORDER BY m.parent_id, m.sort_order
");
$stmt->execute([$currentUser['role']]);
$rolePermissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($rolePermissions as $perm) {
    echo sprintf("ID: %d, Title: %s, Access: %s\n", 
        $perm['id'], 
        $perm['title'], 
        $perm['can_access'] === null ? 'NOT SET' : ($perm['can_access'] ? 'YES' : 'NO')
    );
}