<?php
/**
 * Add day_number column to installation_progress_attachments table
 * This allows us to link attachments to specific daily work entries
 */

require_once __DIR__ . '/../config/database.php';

try {
    $pdo = Database::getInstance()->getConnection();
    
    echo "Adding day_number column to installation_progress_attachments table...\n";
    
    // Check if column already exists
    $stmt = $pdo->query("SHOW COLUMNS FROM installation_progress_attachments LIKE 'day_number'");
    $columnExists = $stmt->fetch();
    
    if (!$columnExists) {
        // Add day_number column
        $sql = "ALTER TABLE installation_progress_attachments 
                ADD COLUMN day_number INT NULL AFTER progress_id,
                ADD INDEX idx_day_number (day_number)";
        
        $pdo->exec($sql);
        echo "✓ Added day_number column with index\n";
    } else {
        echo "✓ day_number column already exists\n";
    }
    
    // Verify the changes
    $stmt = $pdo->query("SHOW COLUMNS FROM installation_progress_attachments");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nCurrent table structure:\n";
    foreach ($columns as $column) {
        echo "  - " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
    
    echo "\n✓ SUCCESS: Table is ready for daily work attachments!\n";
    
} catch (PDOException $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>
