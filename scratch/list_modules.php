<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();

echo "Modules:\n";
$stmt = $db->query("SELECT * FROM modules");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
