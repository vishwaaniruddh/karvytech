<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();

foreach(['boq_master', 'boq_master_items'] as $t) { 
    echo "Schema for $t:\n"; 
    $s = $db->query("DESCRIBE $t"); 
    print_r($s->fetchAll(PDO::FETCH_ASSOC)); 
}
