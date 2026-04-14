<?php
require_once 'config/database.php';
try {
    $db = Database::getInstance()->getConnection();
    $db->exec("ALTER TABLE dynamic_surveys ADD COLUMN created_by INT NULL");
    $db->exec("ALTER TABLE dynamic_surveys ADD CONSTRAINT fk_survey_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL");
    echo "Column added successfully";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
