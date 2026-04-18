<?php
require 'config/database.php';
$db = Database::getInstance()->getConnection();
$stmt = $db->query("DESCRIBE inventory_dispatch_items");
$cols = [];
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $cols[] = $row['Field'];
}
echo implode(', ', $cols);
?>
