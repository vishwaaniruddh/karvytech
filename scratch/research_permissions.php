<?php
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance()->getConnection();

echo "--- PERMISSIONS TABLE ---\n";
$stmt = $db->query('DESCRIBE permissions');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\n--- MODULES TABLE ---\n";
$stmt = $db->query('DESCRIBE modules');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\n--- MODELS DIRECTORY ---\n";
$files = scandir(__DIR__ . '/../models');
print_r($files);
