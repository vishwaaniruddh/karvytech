<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();
$s = $db->query('SELECT * FROM menu_items');
print_r($s->fetchAll(PDO::FETCH_ASSOC));
