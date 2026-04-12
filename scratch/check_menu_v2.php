<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();

echo "--- ROLES ---\n";
$stmt = $db->query("SELECT DISTINCT role FROM role_menu_permissions");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\n--- RECENT MENU ITEMS ---\n";
$stmt = $db->query("SELECT * FROM menu_items ORDER BY id DESC LIMIT 10");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\n--- PERMISSIONS FOR ID 72, 73, 74, 75, 76, 77, 78 ---\n";
$stmt = $db->query("SELECT * FROM role_menu_permissions WHERE menu_item_id IN (72, 76, 77, 78)");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
