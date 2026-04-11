<?php
require_once 'c:/xampp/htdocs/project/config/database.php';
$db = Database::getInstance()->getConnection();
$stmt = $db->query('DESCRIBE dynamic_survey_fields');
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}
?>
