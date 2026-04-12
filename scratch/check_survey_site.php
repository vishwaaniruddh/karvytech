<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();
$s = $db->query('SELECT id, site_id FROM dynamic_survey_responses WHERE id = 9');
print_r($s->fetch(PDO::FETCH_ASSOC));
