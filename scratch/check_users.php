<?php
require_once 'config/database.php';
$db = Database::getInstance()->getConnection();
$q = $db->query('DESCRIBE users');
foreach($q->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}
?>
