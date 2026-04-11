<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Menu.php';
require_once __DIR__ . '/models/Permission.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "--- MENU ITEMS ---\n";
    $stmt = $db->query("SELECT * FROM menu_items ORDER BY parent_id, sort_order");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$row['id']}, Parent: " . ($row['parent_id'] ?? 'NULL') . ", Title: {$row['title']}, URL: {$row['url']}\n";
    }
    
    echo "\n--- MODULES ---\n";
    $stmt = $db->query("SELECT * FROM modules");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$row['id']}, Name: {$row['name']}, Display: {$row['display_name']}\n";
    }
    
    echo "\n--- PERMISSIONS ---\n";
    $stmt = $db->query("SELECT * FROM permissions LIMIT 20");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$row['id']}, Module ID: {$row['module_id']}, Name: {$row['name']}, Display: {$row['display_name']}\n";
    }

    echo "\n--- ROLES ---\n";
    $stmt = $db->query("SELECT * FROM roles");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$row['id']}, Name: {$row['name']}, Display: {$row['display_name']}\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
