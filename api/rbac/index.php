<?php
/**
 * RBAC REST API
 * Handles all RBAC operations via REST endpoints
 */

require_once '../../config/database.php';
require_once '../../config/auth.php';
require_once '../../models/Role.php';
require_once '../../models/Permission.php';
require_once '../../models/User.php';

header('Content-Type: application/json');

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/karvy/api/rbac/', '', $path);
$parts = explode('/', trim($path, '/'));

$action = $parts[0] ?? '';
$id = $parts[1] ?? null;

try {
    // Check authentication
    if (!Auth::isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    // Check permission
    if (!Auth::hasPermission('users', 'manage_roles')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Permission denied']);
        exit;
    }

    $roleModel = new Role();
    $permissionModel = new Permission();
    $userModel = new User();

    // Route requests
    switch ($action) {
        // ROLES ENDPOINTS
        case 'roles':
            if ($method === 'GET') {
                if ($id) {
                    // Get single role
                    $role = $roleModel->getRoleById($id);
                    if (!$role) {
                        http_response_code(404);
                        echo json_encode(['success' => false, 'message' => 'Role not found']);
                        exit;
                    }
                    $permissions = $roleModel->getRolePermissions($id);
                    echo json_encode([
                        'success' => true,
                        'data' => [
                            'role' => $role,
                            'permissions' => $permissions
                        ]
                    ]);
                } else {
                    // Get all roles
                    $roles = $roleModel->getAllRoles();
                    echo json_encode([
                        'success' => true,
                        'data' => $roles
                    ]);
                }
            } elseif ($method === 'PUT' && $id) {
                // Update role permissions
                $input = json_decode(file_get_contents('php://input'), true);
                $permissionIds = $input['permissions'] ?? [];
                
                $roleModel->assignPermissionsToRole($id, $permissionIds);
                echo json_encode([
                    'success' => true,
                    'message' => 'Role permissions updated successfully'
                ]);
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;

        // MODULES ENDPOINTS
        case 'modules':
            if ($method === 'GET') {
                if ($id) {
                    // Get single module with permissions
                    $module = $permissionModel->getModuleById($id);
                    if (!$module) {
                        http_response_code(404);
                        echo json_encode(['success' => false, 'message' => 'Module not found']);
                        exit;
                    }
                    $permissions = $permissionModel->getModulePermissions($id);
                    echo json_encode([
                        'success' => true,
                        'data' => [
                            'module' => $module,
                            'permissions' => $permissions
                        ]
                    ]);
                } else {
                    // Get all modules
                    $modules = $permissionModel->getAllModules();
                    echo json_encode([
                        'success' => true,
                        'data' => $modules
                    ]);
                }
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;

        // PERMISSIONS ENDPOINTS
        case 'permissions':
            if ($method === 'GET') {
                if ($id) {
                    // Get single permission
                    $permission = $permissionModel->getPermissionById($id);
                    if (!$permission) {
                        http_response_code(404);
                        echo json_encode(['success' => false, 'message' => 'Permission not found']);
                        exit;
                    }
                    echo json_encode([
                        'success' => true,
                        'data' => $permission
                    ]);
                } else {
                    // Get all permissions grouped by module
                    $permissions = $permissionModel->getAllPermissionsGrouped();
                    echo json_encode([
                        'success' => true,
                        'data' => $permissions
                    ]);
                }
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;

        // USER ROLES ENDPOINTS
        case 'user-roles':
            if ($method === 'GET') {
                if ($id) {
                    // Get user's role and permissions
                    $user = $userModel->find($id);
                    if (!$user) {
                        http_response_code(404);
                        echo json_encode(['success' => false, 'message' => 'User not found']);
                        exit;
                    }
                    $permissions = $userModel->getUserPermissions($id);
                    echo json_encode([
                        'success' => true,
                        'data' => [
                            'user' => $user,
                            'permissions' => $permissions
                        ]
                    ]);
                } else {
                    // Get all users with roles
                    $db = Database::getInstance()->getConnection();
                    $stmt = $db->query("
                        SELECT u.id, u.username, u.email, r.name as role_name, r.display_name as role_display
                        FROM users u
                        LEFT JOIN roles r ON u.role_id = r.id
                        ORDER BY r.display_name DESC, u.username ASC
                    ");
                    $users = $stmt->fetchAll();
                    echo json_encode([
                        'success' => true,
                        'data' => $users
                    ]);
                }
            } elseif ($method === 'PUT' && $id) {
                // Assign role to user
                $input = json_decode(file_get_contents('php://input'), true);
                $roleId = $input['role_id'] ?? null;
                
                if (!$roleId) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'role_id is required']);
                    exit;
                }
                
                $userModel->assignRole($id, $roleId);
                echo json_encode([
                    'success' => true,
                    'message' => 'User role assigned successfully'
                ]);
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;

        // STATISTICS ENDPOINT
        case 'stats':
            if ($method === 'GET') {
                $db = Database::getInstance()->getConnection();
                
                $stmt = $db->query('SELECT COUNT(*) FROM roles');
                $roleCount = $stmt->fetchColumn();
                
                $stmt = $db->query('SELECT COUNT(*) FROM modules');
                $moduleCount = $stmt->fetchColumn();
                
                $stmt = $db->query('SELECT COUNT(*) FROM permissions');
                $permCount = $stmt->fetchColumn();
                
                $stmt = $db->query('SELECT COUNT(*) FROM role_permissions');
                $rpCount = $stmt->fetchColumn();
                
                $stmt = $db->query('SELECT COUNT(*) FROM users WHERE role_id IS NOT NULL');
                $userCount = $stmt->fetchColumn();
                
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'roles' => $roleCount,
                        'modules' => $moduleCount,
                        'permissions' => $permCount,
                        'role_permissions' => $rpCount,
                        'users_with_roles' => $userCount
                    ]
                ]);
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;

        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
