<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT rmp.*, m.title FROM role_menu_permissions rmp JOIN menu_items m ON rmp.menu_item_id = m.id WHERE rmp.role = 'superadmin'");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode($row) . "\n";
}
?>
