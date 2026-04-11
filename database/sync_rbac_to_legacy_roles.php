<?php
/**
 * Sync RBAC roles to legacy role field
 * Ensures backward compatibility with code that checks the 'role' field
 */

require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Syncing RBAC roles to legacy role field...\n\n";
    
    // Get all users with RBAC roles
    $stmt = $db->query("
        SELECT u.id, u.username, u.email, u.role as legacy_role, r.name as rbac_role
        FROM users u
        LEFT JOIN roles r ON u.role_id = r.id
        WHERE u.role_id IS NOT NULL
    ");
    
    $users = $stmt->fetchAll();
    $updateCount = 0;
    
    foreach ($users as $user) {
        // Map RBAC roles to legacy roles
        $legacyRole = null;
        switch ($user['rbac_role']) {
            case 'superadmin':
            case 'admin':
            case 'manager':
                $legacyRole = 'admin';
                break;
            case 'contractor':
                $legacyRole = 'vendor';
                break;
        }
        
        // Update if different or empty
        if ($legacyRole && ($user['legacy_role'] !== $legacyRole || empty($user['legacy_role']))) {
            $updateStmt = $db->prepare("UPDATE users SET role = ? WHERE id = ?");
            $updateStmt->execute([$legacyRole, $user['id']]);
            
            echo "✓ Updated user #{$user['id']} ({$user['username']}): ";
            echo "'{$user['legacy_role']}' → '{$legacyRole}' (RBAC: {$user['rbac_role']})\n";
            $updateCount++;
        } else {
            echo "- User #{$user['id']} ({$user['username']}): Already correct (role: {$user['legacy_role']})\n";
        }
    }
    
    echo "\n";
    echo "========================================\n";
    echo "Summary:\n";
    echo "✓ Users updated: $updateCount\n";
    echo "✓ Total users checked: " . count($users) . "\n";
    echo "========================================\n";
    echo "\nDone! All users now have correct legacy role field.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
