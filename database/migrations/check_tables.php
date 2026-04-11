<?php
require_once __DIR__ . '/../../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Checking required tables...\n";
    echo "===========================\n\n";
    
    // Check superadmin_requests table
    $stmt = $db->query("SHOW TABLES LIKE 'superadmin_requests'");
    if ($stmt->rowCount() > 0) {
        echo "✓ superadmin_requests table exists\n\n";
        
        echo "Structure:\n";
        $stmt = $db->query("DESCRIBE superadmin_requests");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($columns as $column) {
            echo sprintf("  %-25s %-30s %-10s %s\n", 
                $column['Field'], 
                $column['Type'],
                $column['Key'],
                $column['Null'] === 'NO' ? 'NOT NULL' : 'NULL'
            );
        }
    } else {
        echo "✗ superadmin_requests table does NOT exist\n";
    }
    
    echo "\n";
    
    // Check users table
    $stmt = $db->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "✓ users table exists\n";
    } else {
        echo "✗ users table does NOT exist\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
