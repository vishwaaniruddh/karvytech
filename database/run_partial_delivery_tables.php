<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    $sql = file_get_contents(__DIR__ . '/create_partial_delivery_tables.sql');
    
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^\s*--/', $statement)) {
            try {
                $db->exec($statement);
                echo "Executed: " . substr($statement, 0, 50) . "...\n";
            } catch (Exception $e) {
                // Log but continue if it's just a duplicate column/table
                if (strpos($e->getMessage(), 'Duplicate column') !== false || 
                    strpos($e->getMessage(), 'already exists') !== false) {
                    echo "Already exists: " . substr($statement, 0, 50) . "...\n";
                } else {
                    echo "Error: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    
    echo "Partial delivery tables migration completed successfully!\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
?>