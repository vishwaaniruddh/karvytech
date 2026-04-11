<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT sr.id, COALESCE(NULLIF(TRIM(CONCAT_WS(' ', u.first_name, u.last_name)), ''), u.username) as surveyor_name FROM dynamic_survey_responses sr LEFT JOIN users u ON sr.surveyor_id = u.id WHERE sr.id = 9");
$stmt->execute();
print_r($stmt->fetch(PDO::FETCH_ASSOC));
