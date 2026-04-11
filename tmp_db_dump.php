<?php
require_once __DIR__ . '/config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    $output = "--- MENU ITEMS ---\n";
    $stmt = $db->query("SELECT * FROM menu_items ORDER BY parent_id, sort_order");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $output .= "ID: {$row['id']}, Parent: " . ($row['parent_id'] ?? 'NULL') . ", Title: {$row['title']}, URL: {$row['url']}, Icon: {$row['icon']}\n";
    }
    
    $output .= "\n--- MODULES ---\n";
    $stmt = $db->query("SELECT * FROM modules");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $output .= "ID: {$row['id']}, Name: {$row['name']}, Display: {$row['display_name']}\n";
    }
    
    $output .= "\n--- PERMISSIONS (Sample) ---\n";
    $stmt = $db->query("SELECT * FROM permissions LIMIT 50");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $output .= "ID: {$row['id']}, Module ID: {$row['module_id']}, Name: {$row['name']}, Display: {$row['display_name']}\n";
    }

    $output .= "\n--- ROLES ---\n";
    $stmt = $db->query("SELECT * FROM roles");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $output .= "ID: {$row['id']}, Name: {$row['name']}, Display: {$row['display_name']}\n";
    }

    file_put_contents(__DIR__ . '/db_dump.txt', $output);
    echo "Dump created in db_dump.txt\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
