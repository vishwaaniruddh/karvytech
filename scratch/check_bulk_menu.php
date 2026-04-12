<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT * FROM menu_items WHERE parent_id = 68 ORDER BY sort_order ASC");
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($items as $item) {
    echo "ID: {$item['id']} | Title: {$item['title']} | Sort: {$item['sort_order']} | URL: {$item['url']} | Permission: ";
    $p = $db->query("SELECT role FROM role_menu_permissions WHERE menu_item_id = {$item['id']}")->fetchAll(PDO::FETCH_ASSOC);
    echo implode(', ', array_column($p, 'role')) . "\n";
}
