<?php
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance()->getConnection();

echo "--- MENU ITEMS (Masters related) ---\n";
$stmt = $db->query("SELECT * FROM menu_items WHERE id IN (51, 58, 59, 60) OR parent_id IN (51, 58, 59, 60) ORDER BY id");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode($row) . "\n";
}

echo "\n--- PERMISSIONS (Superadmin 57) ---\n";
$stmt = $db->prepare("SELECT ump.*, m.title FROM user_menu_permissions ump JOIN menu_items m ON ump.menu_item_id = m.id WHERE ump.user_id = 57 AND ump.menu_item_id IN (51, 58, 59, 60)");
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode($row) . "\n";
}
?>
