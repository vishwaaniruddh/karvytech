<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();

try {
    $db->exec("DROP TABLE IF EXISTS role_menu_permissions");
    $sql = "CREATE TABLE role_menu_permissions (
        id INT AUTO_INCREMENT PRIMARY KEY, 
        role_id INT NOT NULL, 
        menu_item_id INT NOT NULL, 
        can_access TINYINT(1) DEFAULT 1, 
        UNIQUE KEY (role_id, menu_item_id), 
        FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE, 
        FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE
    )";
    $db->exec($sql);
    echo "Table role_menu_permissions recreated successfully with role_id.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
