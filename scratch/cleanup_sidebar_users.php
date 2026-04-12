<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Remove Add User
    $db->query("DELETE FROM menu_items WHERE id = 63");
    
    // Update Permissions URL
    $db->query("UPDATE menu_items SET url = '/admin/users/permissions.php' WHERE id = 65");
    
    echo "Successfully cleaned up sidebar menu items.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
