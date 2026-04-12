<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();

echo "Schema for permissions:\n";
$stmt = $db->query("DESCRIBE permissions");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\nSample permissions:\n";
$stmt = $db->query("SELECT * FROM permissions LIMIT 5");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
