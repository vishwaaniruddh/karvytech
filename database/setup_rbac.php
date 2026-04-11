<?php
/**
 * RBAC System Setup Script
 * Run this script to initialize the role-based access control system
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/logger.php';

$db = Database::getInstance()->getConnection();

try {
    echo "Starting RBAC System Setup...\n\n";
    
    // Read and execute the SQL file
    $sqlFile = __DIR__ . '/create_rbac_system.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL file not found: $sqlFile");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $count = 0;
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $db->exec($statement);
                $count++;
                echo "✓ Executed statement $count\n";
            } catch (PDOException $e) {
                // Some statements might fail if they already exist, that's okay
                echo "⚠ Statement $count: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n✓ RBAC System Setup Completed Successfully!\n";
    echo "Total statements executed: $count\n\n";
    
    // Display summary
    echo "=== RBAC System Summary ===\n";
    
    // Count roles
    $stmt = $db->query("SELECT COUNT(*) FROM roles");
    $roleCount = $stmt->fetchColumn();
    echo "Roles created: $roleCount\n";
    
    // Count modules
    $stmt = $db->query("SELECT COUNT(*) FROM modules");
    $moduleCount = $stmt->fetchColumn();
    echo "Modules created: $moduleCount\n";
    
    // Count permissions
    $stmt = $db->query("SELECT COUNT(*) FROM permissions");
    $permCount = $stmt->fetchColumn();
    echo "Permissions created: $permCount\n";
    
    // Display roles
    echo "\n=== Roles ===\n";
    $stmt = $db->query("SELECT name, display_name FROM roles ORDER BY name");
    foreach ($stmt->fetchAll() as $role) {
        echo "- {$role['name']}: {$role['display_name']}\n";
    }
    
    // Display modules
    echo "\n=== Modules ===\n";
    $stmt = $db->query("SELECT name, display_name FROM modules ORDER BY display_name");
    foreach ($stmt->fetchAll() as $module) {
        echo "- {$module['name']}: {$module['display_name']}\n";
    }
    
    echo "\n✓ Setup completed! You can now use the RBAC system.\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    Logger::error('RBAC Setup Error', ['error' => $e->getMessage()]);
    exit(1);
}
?>
