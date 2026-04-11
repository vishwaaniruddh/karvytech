<?php
/**
 * RBAC System Test Script
 * Tests permission checking and helper functions
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/rbac_helper.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Role.php';

echo "=== RBAC System Test ===\n\n";

try {
    // Test 1: Get all roles
    echo "Test 1: Get all roles\n";
    $roles = getAllRoles();
    echo "  Found " . count($roles) . " roles\n";
    foreach ($roles as $role) {
        echo "    - {$role['name']}: {$role['display_name']}\n";
    }
    
    // Test 2: Get all modules
    echo "\nTest 2: Get all modules\n";
    $modules = getAllModules();
    echo "  Found " . count($modules) . " modules\n";
    foreach ($modules as $module) {
        echo "    - {$module['name']}: {$module['display_name']}\n";
    }
    
    // Test 3: Get permissions grouped by module
    echo "\nTest 3: Get permissions grouped by module\n";
    $permsGrouped = getAllPermissionsGrouped();
    echo "  Found " . count($permsGrouped) . " modules with permissions\n";
    foreach ($permsGrouped as $module) {
        echo "    - {$module['module_display_name']}: " . count($module['permissions']) . " permissions\n";
    }
    
    // Test 4: Get role permissions
    echo "\nTest 4: Get role permissions\n";
    $roleModel = new Role();
    $adminRole = $roleModel->getRoleByName('admin');
    $adminPerms = $roleModel->getRolePermissions($adminRole['id']);
    echo "  Admin role has " . count($adminPerms) . " permissions\n";
    
    // Test 5: Get role permissions grouped by module
    echo "\nTest 5: Get role permissions grouped by module\n";
    $adminPermsGrouped = $roleModel->getRolePermissionsByModule($adminRole['id']);
    echo "  Admin role has access to " . count($adminPermsGrouped) . " modules:\n";
    foreach ($adminPermsGrouped as $moduleName => $data) {
        echo "    - {$data['module_display_name']}: " . count($data['permissions']) . " permissions\n";
    }
    
    // Test 6: Get user permissions
    echo "\nTest 6: Get user permissions\n";
    $userModel = new User();
    $adminUser = $userModel->findByUsername('admin');
    if ($adminUser) {
        $userPerms = $userModel->getUserPermissions($adminUser['id']);
        echo "  Admin user has " . count($userPerms) . " permissions\n";
        
        // Test 7: Check specific permission
        echo "\nTest 7: Check specific permission\n";
        $hasPermission = $userModel->hasPermission($adminUser['id'], 'users', 'create');
        echo "  Admin user can create users: " . ($hasPermission ? 'YES' : 'NO') . "\n";
        
        $hasPermission = $userModel->hasPermission($adminUser['id'], 'settings', 'manage');
        echo "  Admin user can manage settings: " . ($hasPermission ? 'YES' : 'NO') . "\n";
    }
    
    // Test 8: Get contractor role permissions
    echo "\nTest 8: Get contractor role permissions\n";
    $contractorRole = $roleModel->getRoleByName('contractor');
    $contractorPerms = $roleModel->getRolePermissions($contractorRole['id']);
    echo "  Contractor role has " . count($contractorPerms) . " permissions\n";
    
    // Test 9: Get superadmin role permissions
    echo "\nTest 9: Get superadmin role permissions\n";
    $superadminRole = $roleModel->getRoleByName('superadmin');
    $superadminPerms = $roleModel->getRolePermissions($superadminRole['id']);
    echo "  Superadmin role has " . count($superadminPerms) . " permissions\n";
    
    // Test 10: Get manager role permissions
    echo "\nTest 10: Get manager role permissions\n";
    $managerRole = $roleModel->getRoleByName('manager');
    $managerPerms = $roleModel->getRolePermissions($managerRole['id']);
    echo "  Manager role has " . count($managerPerms) . " permissions\n";
    
    echo "\n✓ All tests completed successfully!\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
?>
