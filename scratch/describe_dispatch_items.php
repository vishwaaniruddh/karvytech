<?php
require 'config/database.php';
$db = Database::getInstance()->getConnection();
$cols = $db->query('DESCRIBE inventory_dispatch_items')->fetchAll(PDO::FETCH_ASSOC);
print_r($cols);
?>
