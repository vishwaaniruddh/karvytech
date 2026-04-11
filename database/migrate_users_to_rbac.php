<?php
/**
 * User Migration Script
 * Migrates existing users to the new RBAC system
 * Maps old role field to new role_id
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/logger.php';
require_once __DIR__ . '/../models/Role.php';
require_once __DIR__ . '/../models/User.php';

$db = Database::getInstance()->getConnection();
$roleModel = new Role();
$userModel = new User();

try {
    echo "Starting User Migration to RBAC System...\n\n";
    
    // Get all roles
    $roles = $roleModel->getAllRoles(null);
    $roleMap = [];
    foreach ($roles as $role) {
        $roleMap[$role['name']] = $role['id'];
    }
    
    echo "Available roles:\n";
    foreach ($roleMap as $name => $id) {
        echo "  - $name (ID: $id)\n";
    }
    echo "\n";
    
    // Get all users without role_id
    $stmt = $db->query("SELECT id, username, role FROM users WHERE role_id IS NULL");
    $usersToMigrate = $stmt->fetchAll();
    
    if (empty($usersToMigrate)) {
        echo "✓ All users already have role_id assigned!\n";
        exit(0);
    }
    
    echo "Found " . count($usersToMigrate) . " users to migrate:\n\n";
    
    $migrated = 0;
    $failed = 0;
    
    foreach ($usersToMigrate as $user) {
        $oldRole = $user['role'];
        $userId = $user['id'];
        $username = $user['username'];
        
        // Map old role to new role
        $newRoleId = null;
        
        if ($oldRole === 'admin') {
            $newRoleId = $roleMap['admin'] ?? null;
        } elseif ($oldRole === 'vendor') {
            $newRoleId = $roleMap['contractor'] ?? null;
        } else {
            $newRoleId = $roleMap['contractor'] ?? null;
        }
        
        if ($newRoleId) {
            try {
                $userModel->assignRole($userId, $newRoleId);
                echo "✓ $username (ID: $userId): $oldRole → " . array_search($newRoleId, $roleMap) . "\n";
                $migrated++;
            } catch (Exception $e) {
                echo "✗ $username (ID: $userId): Failed - " . $e->getMessage() . "\n";
                $failed++;
            }
        } else {
            echo "✗ $username (ID: $userId): No matching role found for '$oldRole'\n";
            $failed++;
        }
    }
    
    echo "\n=== Migration Summary ===\n";
    echo "Total users: " . count($usersToMigrate) . "\n";
    echo "Migrated: $migrated\n";
    echo "Failed: $failed\n";
    
    if ($failed === 0) {
        echo "\n✓ Migration completed successfully!\n";
    } else {
        echo "\n⚠ Migration completed with errors. Please review failed users.\n";
    }
    
    // Display migrated users
    echo "\n=== Migrated Users ===\n";
    $stmt = $db->query("
        SELECT u.id, u.username, u.email, r.name as role_name, r.display_name as role_display
        FROM users u
        LEFT JOIN roles r ON u.role_id = r.id
        ORDER BY u.created_at DESC
        LIMIT 20
    ");
    
    foreach ($stmt->fetchAll() as $user) {
        echo sprintf(
            "ID: %d | Username: %s | Email: %s | Role: %s\n",
            $user['id'],
            $user['username'],
            $user['email'],
            $user['role_display'] ?? 'Not assigned'
        );
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    Logger::error('User Migration Error', ['error' => $e->getMessage()]);
    exit(1);
}
?>
