<?php
/**
 * RBAC User Roles API
 * Handles user role assignment
 */

require_once '../../config/database.php';
require_once '../../config/auth.php';
require_once '../../includes/rbac_helper.php';
require_once '../../models/User.php';
require_once '../../models/Role.php';

header('Content-Type: application/json');

// Require admin access
Auth::requireAuth();
if (!Auth::isAdminOrAbove()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? null;
$userModel = new User();
$roleModel = new Role();

try {
    switch ($action) {
        case 'list':
            // Get all users with roles
            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 20;
            $search = $_GET['search'] ?? '';
            
            $result = $userModel->getAllWithPagination($page, $limit, $search);
            
            // Add role info
            foreach ($result['users'] as &$user) {
                if ($user['role_id']) {
                    $role = $roleModel->getRoleById($user['role_id']);
                    $user['role_name'] = $role['name'];
                    $user['role_display'] = $role['display_name'];
                }
            }
            
            echo json_encode(['success' => true, 'data' => $result]);
            break;

        case 'get':
            // Get user with role and permissions
            $userId = $_GET['id'] ?? null;
            if (!$userId) {
                throw new Exception('User ID required');
            }
            
            $user = $userModel->findWithVendor($userId);
            if (!$user) {
                throw new Exception('User not found');
            }
            
            if ($user['role_id']) {
                $roleInfo = $userModel->getUserRoleWithPermissions($userId);
                $user['role'] = $roleInfo['role'];
                $user['permissions'] = $roleInfo['permissions'];
            }
            
            echo json_encode(['success' => true, 'data' => $user]);
            break;

        case 'assign_role':
            // Assign role to user
            $userId = $_POST['user_id'] ?? null;
            $roleId = $_POST['role_id'] ?? null;
            
            if (!$userId || !$roleId) {
                throw new Exception('User ID and Role ID required');
            }
            
            $userModel->assignRole($userId, $roleId);
            echo json_encode(['success' => true, 'message' => 'Role assigned']);
            break;

        case 'get_user_permissions':
            // Get user permissions
            $userId = $_GET['user_id'] ?? null;
            if (!$userId) {
                throw new Exception('User ID required');
            }
            
            $permissions = $userModel->getUserPermissions($userId);
            
            // Group by module
            $grouped = [];
            foreach ($permissions as $perm) {
                $module = $perm['module_name'];
                if (!isset($grouped[$module])) {
                    $grouped[$module] = [
                        'module_display_name' => $perm['module_display_name'],
                        'permissions' => []
                    ];
                }
                $grouped[$module]['permissions'][] = $perm;
            }
            
            echo json_encode(['success' => true, 'data' => $grouped]);
            break;

        case 'get_available_roles':
            // Get all available roles
            $roles = $roleModel->getAllRoles();
            echo json_encode(['success' => true, 'data' => $roles]);
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
