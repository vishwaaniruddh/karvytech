<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();
$stmt = $db->query('DESCRIBE dynamic_survey_revisions');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
