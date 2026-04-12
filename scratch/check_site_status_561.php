<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();
$s = $db->prepare('SELECT id, survey_status FROM sites WHERE id = ?');
$s->execute([561]);
print_r($s->fetch(PDO::FETCH_ASSOC));
