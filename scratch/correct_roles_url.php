<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    $sql = "UPDATE menu_items SET url = '/admin/users/roles.php' WHERE title = 'Roles' AND parent_id = 10";
    $success = $db->query($sql);
    
    if ($success) {
        echo "Successfully updated Roles menu URL to /admin/users/roles.php\n";
    } else {
        echo "Failed to update Roles menu URL.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
