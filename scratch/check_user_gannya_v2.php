<?php
require 'config/database.php';
$db = Database::getInstance()->getConnection();
$user = $db->query("SELECT id, username, vendor_id, role FROM users WHERE username LIKE '%Gannya%'")->fetch(PDO::FETCH_ASSOC);
print_r($user);
?>
