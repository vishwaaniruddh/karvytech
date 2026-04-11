<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();

try {
    // Check if there are already revisions
    $countStmt = $db->query("SELECT COUNT(*) FROM dynamic_survey_revisions");
    $exists = $countStmt->fetchColumn();
    
    if ($exists > 0) {
        echo "Revisions already exist. Skipping initial migration.\n";
        exit;
    }

    $sql = "
    INSERT INTO dynamic_survey_revisions (response_id, revision_number, form_data, site_master_data, updated_by, updated_at, change_summary)
    SELECT id, 1, form_data, site_master_data, surveyor_id, COALESCE(submitted_date, NOW()), 'Initial Submission'
    FROM dynamic_survey_responses
    ";
    
    $count = $db->exec($sql);
    echo "Migrated $count surveys to initial revision record.\n";
} catch (PDOException $e) {
    echo "Error during migration: " . $e->getMessage() . "\n";
}
