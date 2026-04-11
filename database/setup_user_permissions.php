<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Setting up user-specific permissions system...\n\n";
    
    // Read and execute SQL file
    $sql = file_get_contents(__DIR__ . '/add_user_specific_permissions.sql');
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $db->exec($statement);
            echo "✓ Executed: " . substr($statement, 0, 50) . "...\n";
        }
    }
    
    echo "\n✅ User-specific permissions system setup complete!\n\n";
    
    // Verify table was created
    $stmt = $db->query("SHOW TABLES LIKE 'user_permissions'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Table 'user_permissions' created successfully\n";
        
        // Show table structure
        $stmt = $db->query("DESCRIBE user_permissions");
        echo "\nTable structure:\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "  - {$row['Field']} ({$row['Type']})\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
