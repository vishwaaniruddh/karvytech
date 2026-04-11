<?php
// Database installation script
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/error_handler.php';
require_once __DIR__ . '/../includes/logger.php';

try {
    echo "Starting database installation...\n";
    
    // Read the schema file
    $schemaFile = __DIR__ . '/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("Schema file not found: $schemaFile");
    }
    
    $schema = file_get_contents($schemaFile);
    if ($schema === false) {
        throw new Exception("Failed to read schema file");
    }
    
    // Connect to MySQL without specifying database
    $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    // Split schema into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $schema)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt);
        }
    );
    
    // Execute each statement
    foreach ($statements as $statement) {
        if (trim($statement)) {
            echo "Executing: " . substr($statement, 0, 50) . "...\n";
            $pdo->exec($statement);
        }
    }
    
    echo "Database installation completed successfully!\n";
    echo "Default admin user created:\n";
    echo "Username: admin\n";
    echo "Password: admin123\n";
    echo "Please change the default password after first login.\n";
    
    Logger::info("Database installation completed successfully");
    
} catch (Exception $e) {
    echo "Database installation failed: " . $e->getMessage() . "\n";
    Logger::error("Database installation failed", ['error' => $e->getMessage()]);
    exit(1);
}
?>