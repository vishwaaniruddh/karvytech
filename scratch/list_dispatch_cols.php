<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();
$stmt = $db->query("DESCRIBE inventory_dispatches");
$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($res as $r) {
    echo $r['Field'] . "\n";
}
