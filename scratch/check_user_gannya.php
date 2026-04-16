<?php
require 'config/database.php';
$db = Database::getInstance()->getConnection();
$user = $db->query("SELECT * FROM users WHERE username LIKE '%Gannya%' OR name LIKE '%Gannya%'")->fetch(PDO::FETCH_ASSOC);
print_r($user);
?>
