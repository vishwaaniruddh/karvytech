<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();

try {
    echo "Dropping dynamic_survey_revisions...\n";
    $db->exec("DROP TABLE IF EXISTS dynamic_survey_revisions;");
    
    echo "Creating dynamic_survey_revisions with correct schema...\n";
    $sql = "
    CREATE TABLE dynamic_survey_revisions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        response_id INT NOT NULL,
        revision_number INT NOT NULL,
        form_data LONGTEXT,
        site_master_data TEXT,
        updated_by INT,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        change_summary TEXT,
        INDEX (response_id),
        FOREIGN KEY (response_id) REFERENCES dynamic_survey_responses(id) ON DELETE CASCADE,
        FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $db->exec($sql);
    
    echo "Table created correctly.\n";
    
    echo "Migrating existing surveys to Revision #1...\n";
    $migrationSql = "
    INSERT INTO dynamic_survey_revisions (response_id, revision_number, form_data, site_master_data, updated_by, updated_at, change_summary)
    SELECT id, 1, form_data, site_master_data, surveyor_id, COALESCE(submitted_date, NOW()), 'Initial Submission'
    FROM dynamic_survey_responses
    ";
    $count = $db->exec($migrationSql);
    echo "Migrated $count surveys.\n";

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
}
