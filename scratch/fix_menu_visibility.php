<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();

$roles = ['admin', 'super_admin'];
$parentId = 68;

// Re-map and sync all material related items
$materialItems = [
    72 => ['title' => 'Material Master', 'url' => '/admin/bulk/material_master.php', 'icon' => 'category', 'sort' => 4],
    76 => ['title' => 'Material Request', 'url' => '/admin/bulk/material_request.php', 'icon' => 'add_shopping_cart', 'sort' => 5],
    77 => ['title' => 'Request Approvals', 'url' => '/admin/bulk/request_approvals.php', 'icon' => 'fact_check', 'sort' => 6],
    78 => ['title' => 'Dispatch Center', 'url' => '/admin/bulk/dispatch_center.php', 'icon' => 'local_shipping', 'sort' => 7]
];

foreach ($materialItems as $id => $data) {
    // Update Item
    $stmt = $db->prepare("UPDATE menu_items SET title = ?, url = ?, icon = ?, sort_order = ? WHERE id = ?");
    $stmt->execute([$data['title'], $data['url'], $data['icon'], $data['sort'], $id]);
    
    // Clear and Grant Permissions
    $db->prepare("DELETE FROM role_menu_permissions WHERE menu_item_id = ?")->execute([$id]);
    foreach ($roles as $role) {
        $db->prepare("INSERT INTO role_menu_permissions (role, menu_item_id, can_access) VALUES (?, ?, 1)")->execute([$role, $id]);
    }
}

// Adjust conflicting items to avoid overlap
$db->prepare("UPDATE menu_items SET sort_order = 10 WHERE id = 73")->execute(); // Data Import/Export
$db->prepare("UPDATE menu_items SET sort_order = 11 WHERE id = 74")->execute(); // System Operations

echo "Menu permissions and URLs synchronized for admin and super_admin.\n";
