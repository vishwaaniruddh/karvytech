<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Migrating installation_progress table to new structure...\n";
    
    // Check if the table has the old structure
    $stmt = $db->prepare("DESCRIBE installation_progress");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $hasOldStructure = false;
    $hasNewStructure = false;
    
    foreach ($columns as $column) {
        if ($column['Field'] === 'site_id') {
            $hasOldStructure = true;
        }
        if ($column['Field'] === 'installation_id') {
            $hasNewStructure = true;
        }
    }
    
    if ($hasNewStructure) {
        echo "Table already has the new structure. No migration needed.\n";
        exit;
    }
    
    if ($hasOldStructure) {
        echo "Found old structure. Migrating...\n";
        
        // Backup existing data if any
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM installation_progress");
        $stmt->execute();
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            echo "Backing up existing data...\n";
            $db->exec("CREATE TABLE installation_progress_backup AS SELECT * FROM installation_progress");
        }
        
        // Drop the old table
        echo "Dropping old table...\n";
        $db->exec("DROP TABLE installation_progress");
        
        // Create new table with correct structure
        echo "Creating new table structure...\n";
        $sql = "CREATE TABLE installation_progress (
            id INT AUTO_INCREMENT PRIMARY KEY,
            installation_id INT NOT NULL,
            progress_date DATE NOT NULL,
            progress_percentage DECIMAL(5,2) DEFAULT 0.00,
            work_description TEXT NULL,
            photos TEXT NULL,
            issues_faced TEXT NULL,
            next_steps TEXT NULL,
            updated_by INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            
            FOREIGN KEY (installation_id) REFERENCES installation_delegations(id) ON DELETE CASCADE,
            FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE CASCADE,
            
            INDEX idx_installation_id (installation_id),
            INDEX idx_progress_date (progress_date)
        )";
        
        $db->exec($sql);
        
        echo "Migration completed successfully!\n";
        
        if ($result['count'] > 0) {
            echo "Note: Old data was backed up to 'installation_progress_backup' table.\n";
            echo "You may need to manually migrate the data if needed.\n";
        }
    } else {
        echo "Unknown table structure. Please check manually.\n";
    }
    
} catch (Exception $e) {
    echo "Error during migration: " . $e->getMessage() . "\n";
}
?>