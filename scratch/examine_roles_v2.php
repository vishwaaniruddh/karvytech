<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();
echo "--- ROLES TABLE --- \n";
$stmt = $db->query("DESCRIBE roles");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode($row) . "\n";
}
echo "--- ROLE DATA ---\n";
$stmt = $db->query("SELECT * FROM roles");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode($row) . "\n";
}
echo "--- ENUM CHECK role_menu_permissions ---\n";
$stmt = $db->query("SHOW COLUMNS FROM role_menu_permissions LIKE 'role'");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo json_encode($row) . "\n";
?>
