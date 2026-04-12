<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();

// 1. Update existing Material Operations item
$db->prepare("UPDATE menu_items SET title = 'Material Master', url = 'admin/bulk/material_master.php', icon = 'category' WHERE id = 72")->execute();

$parentId = 68;

// 2. Add new items
$items = [
    ['title' => 'Material Request', 'url' => 'admin/bulk/material_request.php', 'icon' => 'add_shopping_cart'],
    ['title' => 'Request Approvals', 'url' => 'admin/bulk/request_approvals.php', 'icon' => 'fact_check'],
    ['title' => 'Dispatch Center', 'url' => 'admin/bulk/dispatch_center.php', 'icon' => 'local_shipping']
];

foreach ($items as $index => $item) {
    $stmt = $db->prepare("INSERT INTO menu_items (parent_id, title, url, icon, sort_order) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$parentId, $item['title'], $item['url'], $item['icon'], 5 + $index]);
    $newId = $db->lastInsertId();
    
    // Grant admin permission (role = 1 or 'admin')
    // We need to find the correct role value. Usually 'admin' in Karvy. 
    // Let's check role_menu_permissions table for existing admin permissions
    $db->prepare("INSERT INTO role_menu_permissions (role, menu_item_id, can_access) VALUES ('admin', ?, 1)")->execute([$newId]);
}

echo "Menu updated successfully.\n";
