<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();
$stmt = $db->query("SHOW TABLES LIKE 'dynamic_survey_revisions'");
$exists = $stmt->fetch();
if ($exists) {
    echo "Table dynamic_survey_revisions exists.\n";
    $stmt = $db->query("SELECT COUNT(*) FROM dynamic_survey_revisions");
    echo "Count: " . $stmt->fetchColumn() . "\n";
} else {
    echo "Table dynamic_survey_revisions does NOT exist.\n";
}
