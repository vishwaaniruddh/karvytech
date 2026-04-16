<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();
$stmt = $db->query("DESCRIBE material_requests");
$fields = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo implode(", ", $fields) . "\n";
