<?php
require 'config/database.php';
$db = Database::getInstance()->getConnection();
$cols = $db->query("SHOW COLUMNS FROM inventory_dispatch_items LIKE 'serial_numbers'")->fetch();
echo $cols ? 'YES' : 'NO';
?>
