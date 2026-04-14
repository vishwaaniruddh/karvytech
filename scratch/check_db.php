<?php
require_once 'config/database.php';
$db = Database::getInstance()->getConnection();
$q = $db->query('DESCRIBE dynamic_surveys');
foreach($q->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}
?>
