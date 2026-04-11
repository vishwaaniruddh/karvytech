<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Setting up Installation System...\n";
    
    // Read and execute the SQL file
    $sql = file_get_contents(__DIR__ . '/create_installation_system.sql');
    
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $db->exec($statement);
                echo "✓ Executed: " . substr($statement, 0, 50) . "...\n";
            } catch (PDOException $e) {
                echo "✗ Error executing statement: " . $e->getMessage() . "\n";
                echo "Statement: " . substr($statement, 0, 100) . "...\n";
            }
        }
    }
    
    echo "\nInstallation System setup completed!\n";
    echo "Tables created:\n";
    echo "- installation_delegations\n";
    echo "- installation_progress\n";
    echo "- installation_notifications\n";
    echo "- Updated site_surveys table with installation tracking\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>