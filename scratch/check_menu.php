<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT * FROM menu_items WHERE title LIKE '%Material%'");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
