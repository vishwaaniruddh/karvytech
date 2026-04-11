<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();
$stmt = $db->query("SHOW TABLES");
while($row = $stmt->fetch(PDO::FETCH_NUM)) {
    echo $row[0] . "\n";
}
?>
