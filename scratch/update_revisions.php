<?php
require_once 'config/database.php';
try {
    $db = Database::getInstance()->getConnection();
    
    // Check if column exists first
    $stmt = $db->query("DESCRIBE dynamic_survey_revisions");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('survey_id', $columns)) {
        $db->exec("ALTER TABLE dynamic_survey_revisions ADD COLUMN survey_id INT NULL AFTER id");
        echo "Added survey_id\n";
    }
    
    if (!in_array('title', $columns)) {
        $db->exec("ALTER TABLE dynamic_survey_revisions ADD COLUMN title VARCHAR(255) NULL AFTER revision_number");
        echo "Added title\n";
    }
    
    if (!in_array('description', $columns)) {
        $db->exec("ALTER TABLE dynamic_survey_revisions ADD COLUMN description TEXT NULL AFTER title");
        echo "Added description\n";
    }
    
    if (!in_array('created_by', $columns)) {
        $db->exec("ALTER TABLE dynamic_survey_revisions ADD COLUMN created_by INT NULL AFTER form_data");
        echo "Added created_by\n";
    }
    
    if (!in_array('created_at', $columns)) {
        $db->exec("ALTER TABLE dynamic_survey_revisions ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER created_by");
        echo "Added created_at\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
