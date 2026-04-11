<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();

$sql = "
CREATE TABLE IF NOT EXISTS dynamic_survey_revisions (
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

try {
    $db->exec($sql);
    echo "Table dynamic_survey_revisions created successfully.\n";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage() . "\n";
}
