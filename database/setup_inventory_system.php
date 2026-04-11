<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Setting up inventory management system...\n";
    
    // Read and execute the SQL file
    $sql = file_get_contents(__DIR__ . '/create_inventory_system.sql');
    
    // Split SQL into individual statements
    $statements = explode(';', $sql);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement) && !preg_match('/^--/', $statement)) {
            try {
                $db->exec($statement);
                echo "✓ Executed statement successfully\n";
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'already exists') === false) {
                    echo "✗ Error executing statement: " . $e->getMessage() . "\n";
                    echo "Statement: " . substr($statement, 0, 100) . "...\n";
                }
            }
        }
    }
    
    echo "\n✓ Inventory system setup completed successfully!\n";
    
} catch (Exception $e) {
    echo "✗ Error setting up inventory system: " . $e->getMessage() . "\n";
}
?>