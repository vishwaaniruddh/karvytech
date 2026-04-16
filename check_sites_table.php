<?php
require_once 'config/database.php';

$db = Database::getInstance()->getConnection();
$stmt = $db->query('DESCRIBE sites');
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Sites table columns:\n";
foreach ($columns as $col) {
    echo "- {$col['Field']} ({$col['Type']})\n";
}
