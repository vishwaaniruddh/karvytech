<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();

try {
    $stmt = $db->query("DESCRIBE role_menu_permissions");
    echo "--- role_menu_permissions ---\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode($row) . "\n";
    }
} catch (Exception $e) {
    echo "Table role_menu_permissions does not exist.\n";
}

try {
    $stmt = $db->query("DESCRIBE menu_items");
    echo "\n--- menu_items ---\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode($row) . "\n";
    }
} catch (Exception $e) {
    echo "Table menu_items does not exist.\n";
}
