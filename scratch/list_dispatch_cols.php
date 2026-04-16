<?php
require 'config/database.php';
$db = Database::getInstance()->getConnection();
$stmt = $db->query("DESCRIBE inventory_dispatches");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . "\n";
}
?>
