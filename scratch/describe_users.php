<?php
require 'config/database.php';
$db = Database::getInstance()->getConnection();
$cols = $db->query('DESCRIBE users')->fetchAll(PDO::FETCH_ASSOC);
print_r($cols);
?>
