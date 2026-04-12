<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    // 1. Make Users (ID 10) a folder
    $db->prepare("UPDATE menu_items SET url = NULL WHERE id = 10")->execute();
    echo "Users (10) converted to folder.\n";

    // 2. Add sub-menus for Users
    $submenus = [
        [
            'parent_id' => 10,
            'title' => 'All Users',
            'icon' => 'users',
            'url' => '/admin/users/',
            'sort_order' => 1
        ],
        [
            'parent_id' => 10,
            'title' => 'Add User',
            'icon' => 'users',
            'url' => '/admin/users/index.php?action=add',
            'sort_order' => 2
        ],
        [
            'parent_id' => 10,
            'title' => 'Roles',
            'icon' => 'audit',
            'url' => '/admin/users/assign-role.php',
            'sort_order' => 3
        ],
        [
            'parent_id' => 10,
            'title' => 'Permissions',
            'icon' => 'settings',
            'url' => '/admin/users/manage-permissions.php',
            'sort_order' => 4
        ]
    ];

    $checkStmt = $db->prepare("SELECT id FROM menu_items WHERE parent_id = 10 AND title = ?");
    $insertStmt = $db->prepare("INSERT INTO menu_items (parent_id, title, icon, url, sort_order, status) VALUES (?, ?, ?, ?, ?, 'active')");
    $updateStmt = $db->prepare("UPDATE menu_items SET icon = ?, url = ?, sort_order = ? WHERE id = ?");

    foreach ($submenus as $menu) {
        $checkStmt->execute([$menu['title']]);
        $existing = $checkStmt->fetch();
        
        if ($existing) {
            $updateStmt->execute([$menu['icon'], $menu['url'], $menu['sort_order'], $existing['id']]);
            echo "Updated existing submenu: " . $menu['title'] . "\n";
        } else {
            $insertStmt->execute([$menu['parent_id'], $menu['title'], $menu['icon'], $menu['url'], $menu['sort_order']]);
            echo "Created new submenu: " . $menu['title'] . "\n";
        }
    }

    $db->commit();
    echo "Users menu restructure complete.\n";

} catch (Exception $e) {
    if (isset($db)) $db->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
