<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();

$parentId = 68; // Bulk Parent ID

// 1. Insert Image Repository Menu Item
$stmt = $db->prepare("INSERT INTO menu_items (parent_id, title, icon, url, sort_order, status) 
                      VALUES (?, 'Image Repository', 'bulk', '/admin/bulk/image_upload.php', 3, 'active')");
$stmt->execute([$parentId]);
$newMenuId = $db->lastInsertId();

echo "Added Image Repository Menu Item (ID: $newMenuId)\n";

// 2. Grant permission to 'admin' role
$stmt = $db->prepare("INSERT INTO role_menu_permissions (role, menu_item_id, can_access) 
                      VALUES ('admin', ?, 1)
                      ON DUPLICATE KEY UPDATE can_access = 1");
$stmt->execute([$newMenuId]);

echo "Granted permission to 'admin' role\n";

// 3. Grant permission to user ID 1 (assuming it's the current user)
$stmt = $db->prepare("INSERT INTO user_menu_permissions (user_id, menu_item_id, can_access) 
                      VALUES (1, ?, 1)
                      ON DUPLICATE KEY UPDATE can_access = 1");
$stmt->execute([$newMenuId]);

echo "Granted permission to User ID 1\n";
?>
