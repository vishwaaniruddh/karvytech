<?php
require_once '../../config/database.php';
try {
    $db = Database::getInstance()->getConnection();
    echo "Connected to: " . Database::getInstance()->getDatabaseName() . "<br>";
    
    $tables = ['dynamic_surveys', 'dynamic_survey_sections', 'dynamic_survey_fields'];
    
    foreach ($tables as $table) {
        echo "<h3>$table</h3>";
        $stmt = $db->query("DESCRIBE $table");
        echo "<pre>";
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
        echo "</pre>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
