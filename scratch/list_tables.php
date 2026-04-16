<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();
$q = $db->query("SHOW TABLES");
$tables = $q->fetchAll(PDO::FETCH_COLUMN);
echo implode("\n", $tables);
