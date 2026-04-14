<?php
require_once 'config/database.php';
$db = Database::getInstance()->getConnection();

$tables = ['dynamic_surveys', 'dynamic_survey_sections', 'dynamic_survey_fields', 'dynamic_survey_revisions', 'dynamic_survey_responses', 'dynamic_survey_response_values'];

foreach ($tables as $table) {
    echo "--- $table ---\n";
    try {
        $stmt = $db->query("DESCRIBE $table");
        foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            echo "  " . $row['Field'] . "\n";
        }
    } catch (Exception $e) {
        echo "  ERROR: " . $e->getMessage() . "\n";
    }
}
