<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();

$stmt = $db->query("DESCRIBE sites");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $col) {
    if (strpos($col['Field'], 'customer') !== false) {
        print_r($col);
    }
}
