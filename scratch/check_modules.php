<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();
$stmt = $db->query('SELECT * FROM modules');
$modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "MODULES:\n";
print_r($modules);
