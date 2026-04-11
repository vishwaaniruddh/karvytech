<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Adding installation_start_time column to installation_delegations table...\n";
    
    // Check if column already exists
    $stmt = $db->prepare("SHOW COLUMNS FROM installation_delegations LIKE 'installation_start_time'");
    $stmt->execute();
    $columnExists = $stmt->fetch();
    
    if (!$columnExists) {
        $sql = "ALTER TABLE installation_delegations 
                ADD COLUMN installation_start_time TIMESTAMP NULL 
                AFTER actual_start_date";
        $db->exec($sql);
        echo "Column 'installation_start_time' added successfully!\n";
    } else {
        echo "Column 'installation_start_time' already exists.\n";
    }
    
    echo "Migration completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>