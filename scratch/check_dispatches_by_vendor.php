<?php
require 'config/database.php';
$db = Database::getInstance()->getConnection();
$rows = $db->query('SELECT vendor_id, COUNT(*) as count FROM inventory_dispatches GROUP BY vendor_id')->fetchAll(PDO::FETCH_ASSOC);
print_r($rows);
?>
