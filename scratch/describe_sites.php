<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();

echo "Schema for sites:\n";
$stmt = $db->query("DESCRIBE sites");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
