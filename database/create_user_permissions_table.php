<?php
/**
 * Create user_permissions table
 */

require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Creating user_permissions table...\n";
    
    $sql = file_get_contents(__DIR__ . '/add_user_specific_permissions.sql');
    
    // Execute the SQL
    $db->exec($sql);
    
    echo "✅ Table created successfully!\n";
    
    // Verify table exists
    $stmt = $db->query("SHOW TABLES LIKE 'user_permissions'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Table verified: user_permissions exists\n";
        
        // Show table structure
        $stmt = $db->query("DESCRIBE user_permissions");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\nTable structure:\n";
        foreach ($columns as $column) {
            echo "  - {$column['Field']} ({$column['Type']})\n";
        }
    } else {
        echo "❌ Table verification failed\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
