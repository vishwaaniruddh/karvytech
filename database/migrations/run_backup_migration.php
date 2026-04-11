<?php
/**
 * Run the deleted_data_backups table migration
 * This script creates the table needed for backup and restore functionality
 */

require_once __DIR__ . '/../../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Starting migration: create_deleted_data_backups\n";
    echo "==========================================\n\n";
    
    // Read the SQL file
    $sql = file_get_contents(__DIR__ . '/create_deleted_data_backups.sql');
    
    // Execute the SQL
    $db->exec($sql);
    
    echo "✓ Table 'deleted_data_backups' created successfully!\n";
    echo "✓ Indexes created successfully!\n";
    echo "✓ Foreign keys created successfully!\n\n";
    
    // Verify the table was created
    $stmt = $db->query("SHOW TABLES LIKE 'deleted_data_backups'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Migration completed successfully!\n\n";
        
        // Show table structure
        echo "Table structure:\n";
        echo "----------------\n";
        $stmt = $db->query("DESCRIBE deleted_data_backups");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($columns as $column) {
            echo sprintf("  %-20s %-30s %s\n", 
                $column['Field'], 
                $column['Type'], 
                $column['Null'] === 'NO' ? 'NOT NULL' : 'NULL'
            );
        }
        
        echo "\n✓ Ready to use backup and restore functionality!\n";
    } else {
        echo "✗ Error: Table was not created\n";
    }
    
} catch (PDOException $e) {
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
