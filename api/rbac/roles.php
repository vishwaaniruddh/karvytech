<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Role.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    $roleModel = new Role();
    
    switch ($action) {
        case 'permissions':
            $roleId = $_GET['role_id'] ?? 0;
            
            if (!$roleId) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Role ID is required'
                ]);
                exit;
            }
            
            $permissions = $roleModel->getRolePermissions($roleId);
            $menuPermissions = $roleModel->getRoleMenuPermissions($roleId);
            
            echo json_encode([
                'success' => true,
                'permissions' => $permissions,
                'menu_permissions' => $menuPermissions
            ]);
            break;
            
        case 'update_permissions':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode([
                    'success' => false,
                    'message' => 'Method not allowed'
                ]);
                exit;
            }
            
            $roleId = $_POST['role_id'] ?? 0;
            $permissionIds = $_POST['permissions'] ?? [];
            $menuItemIds = $_POST['menu_items'] ?? [];
            
            if (!$roleId) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Role ID is required'
                ]);
                exit;
            }
            
            $roleModel->assignPermissionsToRole($roleId, $permissionIds);
            $roleModel->assignMenuPermissionsToRole($roleId, $menuItemIds);
            
            echo json_encode([
                'success' => true,
                'message' => 'Role permissions updated successfully'
            ]);
            break;
            
        case 'list':
            $roles = $roleModel->getAllRoles();
            echo json_encode([
                'success' => true,
                'roles' => $roles
            ]);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
    }
    
} catch (Exception $e) {
    error_log('Roles API error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}
?>
