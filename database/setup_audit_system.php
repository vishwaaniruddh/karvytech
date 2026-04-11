<?php
/**
 * Setup Audit System
 * Run this script to create audit tables and initialize the system
 */

require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Setting up Audit System...\n\n";
    
    // Read SQL file
    $sql = file_get_contents(__DIR__ . '/create_audit_system.sql');
    
    // Split into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }
        
        try {
            $db->exec($statement);
            $successCount++;
            
            // Extract table name for display
            if (preg_match('/CREATE TABLE.*?`([^`]+)`/i', $statement, $matches)) {
                echo "✓ Created table: {$matches[1]}\n";
            }
        } catch (PDOException $e) {
            $errorCount++;
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n";
    echo "========================================\n";
    echo "Audit System Setup Complete!\n";
    echo "========================================\n";
    echo "Successfully executed: $successCount statements\n";
    echo "Errors: $errorCount\n";
    echo "\n";
    echo "Tables created:\n";
    echo "  - audit_logs (main audit log table)\n";
    echo "  - audit_sessions (user session tracking)\n";
    echo "  - audit_api_stats (API statistics)\n";
    echo "\n";
    echo "Next steps:\n";
    echo "1. Access the audit module at: /admin/audit/\n";
    echo "2. Include audit_integration.php in your files to enable logging\n";
    echo "3. Use AuditMiddleware for API logging\n";
    echo "\n";
    
} catch (Exception $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
