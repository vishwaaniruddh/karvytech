<?php
/**
 * RBAC System Verification Script
 */

require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance()->getConnection();

echo "=== RBAC System Verification ===\n\n";

try {
    // Check roles
    $stmt = $db->query('SELECT COUNT(*) FROM roles');
    $roleCount = $stmt->fetchColumn();
    echo "✓ Roles: $roleCount\n";
    
    // Check modules
    $stmt = $db->query('SELECT COUNT(*) FROM modules');
    $moduleCount = $stmt->fetchColumn();
    echo "✓ Modules: $moduleCount\n";
    
    // Check permissions
    $stmt = $db->query('SELECT COUNT(*) FROM permissions');
    $permCount = $stmt->fetchColumn();
    echo "✓ Permissions: $permCount\n";
    
    // Check role_permissions
    $stmt = $db->query('SELECT COUNT(*) FROM role_permissions');
    $rpCount = $stmt->fetchColumn();
    echo "✓ Role-Permission mappings: $rpCount\n";
    
    // Check users with role_id
    $stmt = $db->query('SELECT COUNT(*) FROM users WHERE role_id IS NOT NULL');
    $userCount = $stmt->fetchColumn();
    echo "✓ Users with role_id: $userCount\n";
    
    echo "\n=== Sample Data ===\n\n";
    
    // Show sample roles
    echo "Roles:\n";
    $stmt = $db->query('SELECT name, display_name FROM roles ORDER BY name');
    foreach ($stmt->fetchAll() as $role) {
        echo "  - {$role['name']}: {$role['display_name']}\n";
    }
    
    echo "\nModules:\n";
    $stmt = $db->query('SELECT name, display_name FROM modules ORDER BY display_name');
    foreach ($stmt->fetchAll() as $module) {
        echo "  - {$module['name']}: {$module['display_name']}\n";
    }
    
    echo "\n✓ RBAC System is ready to use!\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
