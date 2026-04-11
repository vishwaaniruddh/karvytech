<?php
/**
 * RBAC REST API Verification
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

header('Content-Type: application/json');

try {
    // Simulate login for testing
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query('SELECT * FROM users WHERE username = "admin" LIMIT 1');
    $user = $stmt->fetch();

    if (!$user) {
        throw new Exception('Admin user not found');
    }

    Auth::login($user);

    // Get statistics
    $stmt = $db->query('SELECT COUNT(*) FROM roles');
    $roleCount = $stmt->fetchColumn();
    
    $stmt = $db->query('SELECT COUNT(*) FROM modules');
    $moduleCount = $stmt->fetchColumn();
    
    $stmt = $db->query('SELECT COUNT(*) FROM permissions');
    $permCount = $stmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'message' => 'RBAC REST API is operational',
        'data' => [
            'user' => [
                'username' => $user['username'],
                'role' => Auth::getRole(),
                'has_manage_roles_permission' => Auth::hasPermission('users', 'manage_roles')
            ],
            'database' => [
                'roles' => $roleCount,
                'modules' => $moduleCount,
                'permissions' => $permCount
            ],
            'endpoints' => [
                'GET /api/rbac/roles',
                'GET /api/rbac/roles/{id}',
                'PUT /api/rbac/roles/{id}',
                'GET /api/rbac/modules',
                'GET /api/rbac/modules/{id}',
                'GET /api/rbac/permissions',
                'GET /api/rbac/permissions/{id}',
                'GET /api/rbac/user-roles',
                'GET /api/rbac/user-roles/{id}',
                'PUT /api/rbac/user-roles/{id}',
                'GET /api/rbac/stats'
            ],
            'frontend' => 'http://localhost/karvy/admin/rbac/dashboard.html'
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
