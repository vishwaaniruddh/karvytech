<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();

// 1. Restore Material Operations
$db->prepare("UPDATE menu_items SET title = 'Material Operations', url = '/admin/bulk/materials.php', icon = 'inventory', sort_order = 4 WHERE id = 72")->execute();

// 2. Remove the individual ones
$db->prepare("DELETE FROM menu_items WHERE id IN (76, 77, 78)")->execute();
$db->prepare("DELETE FROM role_menu_permissions WHERE menu_item_id IN (76, 77, 78)")->execute();

echo "Menu reverted to unified 'Material Operations'.\n";
