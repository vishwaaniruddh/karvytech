<?php
require_once 'c:/xampp/htdocs/project/config/database.php';
try {
    $db = Database::getInstance()->getConnection();
    $db->exec("ALTER TABLE dynamic_survey_fields MODIFY COLUMN field_type ENUM('text','number','textarea','select','radio','checkbox','file','date','datetime') NOT NULL");
    echo "Schema updated successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
