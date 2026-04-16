<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare('SELECT id, site_id, delegation_id, survey_status FROM dynamic_survey_responses WHERE id = 12');
$stmt->execute();
print_r($stmt->fetch());

$stmt = $db->prepare('SELECT s.id, s.site_id FROM sites s WHERE s.site_id = "TAP1"');
$stmt->execute();
print_r($stmt->fetch());
