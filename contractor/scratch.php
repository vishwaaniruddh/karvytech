<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();
print_r($db->query('DESCRIBE sites')->fetchAll(PDO::FETCH_ASSOC));
