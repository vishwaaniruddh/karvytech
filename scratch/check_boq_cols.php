<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();
$q = $db->query("SHOW TABLES LIKE '%boq%'");
while($r = $q->fetch()) {
    $table = $r[0];
    echo "Table: $table\n";
    $stmt = $db->query("DESCRIBE $table");
    foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
        echo "  - {$col['Field']} ({$col['Type']})\n";
    }
}
