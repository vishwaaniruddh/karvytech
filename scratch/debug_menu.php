<?php
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance()->getConnection();

echo "--- MENU ITEMS ---\n";
$stmt = $db->query("SELECT * FROM menu_items ORDER BY parent_id, sort_order");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode($row) . "\n";
}

echo "\n--- SUPERADMIN PERMISSIONS ---\n";
$stmt = $db->query("SELECT * FROM users WHERE role = 'superadmin' LIMIT 1");
$superadmin = $stmt->fetch(PDO::FETCH_ASSOC);

if ($superadmin) {
    echo "Superadmin ID: " . $superadmin['id'] . "\n";
    $stmt = $db->prepare("SELECT ump.*, m.title FROM user_menu_permissions ump JOIN menu_items m ON ump.menu_item_id = m.id WHERE ump.user_id = ?");
    $stmt->execute([$superadmin['id']]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode($row) . "\n";
    }
} else {
    echo "No superadmin found.\n";
}

echo "\n--- ROLE PERMISSIONS ---\n";
$stmt = $db->query("SELECT rmp.*, m.title FROM role_menu_permissions rmp JOIN menu_items m ON rmp.menu_item_id = m.id WHERE rmp.role = 'superadmin'");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode($row) . "\n";
}
?>
