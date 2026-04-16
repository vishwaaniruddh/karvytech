<?php
require_once 'config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    $sql = file_get_contents('database/migrations/add_survey_tracking_fields.sql');
    
    // Execute the SQL
    $db->exec($sql);
    
    echo "✓ Migration completed successfully!\n";
    echo "Survey tracking fields have been added to dynamic_survey_responses table.\n";
    
} catch (Exception $e) {
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
}
