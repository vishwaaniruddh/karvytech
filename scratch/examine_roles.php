<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();
$stmt = $db->query("DESCRIBE role_menu_permissions");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode($row) . "\n";
}
echo "--- ROLE COLUMN CONTENT ---\n";
$stmt = $db->query("SELECT DISTINCT role FROM roles");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode($row) . "\n";
}
?>
