<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    $sql = file_get_contents(__DIR__ . '/create_material_addition_log.sql');
    
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $db->exec($statement);
                echo "Executed: " . substr($statement, 0, 50) . "...\n";
            } catch (Exception $e) {
                // Ignore "column already exists" errors
                if (strpos($e->getMessage(), 'Duplicate column name') === false) {
                    throw $e;
                }
                echo "Skipped (already exists): " . substr($statement, 0, 50) . "...\n";
            }
        }
    }
    
    echo "Material addition log migration completed successfully!\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>