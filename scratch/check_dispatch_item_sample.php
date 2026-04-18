<?php
require 'config/database.php';
$db = Database::getInstance()->getConnection();
$row = $db->query("SELECT * FROM inventory_dispatch_items LIMIT 1")->fetch(PDO::FETCH_ASSOC);
print_r($row);
?>
