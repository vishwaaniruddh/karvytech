<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/User.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    $db = Database::getInstance()->getConnection();
    $userModel = new User();
    
    switch ($action) {
        case 'permissions':
            $userId = $_GET['user_id'] ?? 0;
            
            if (!$userId) {
                echo json_encode([
                    'success' => false,
                    'message' => 'User ID is required'
                ]);
                exit;
            }
            
            $permissions = $userModel->getUserPermissions($userId);
            
            echo json_encode([
                'success' => true,
                'permissions' => $permissions
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
            
            $userId = $_POST['user_id'] ?? 0;
            $roleId = $_POST['role_id'] ?? 0;
            $permissionIds = $_POST['permissions'] ?? [];
            
            if (!$userId) {
                echo json_encode([
                    'success' => false,
                    'message' => 'User ID is required'
                ]);
                exit;
            }
            
            if (!$roleId) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Role ID is required'
                ]);
                exit;
            }
            
            // Get role's default permissions
            $stmt = $db->prepare("
                SELECT permission_id 
                FROM role_permissions 
                WHERE role_id = ?
            ");
            $stmt->execute([$roleId]);
            $rolePermissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $rolePermSet = array_flip($rolePermissions);
            
            // Get current user permissions
            $currentUser = Auth::getCurrentUser();
            
            // Start transaction
            $db->beginTransaction();
            
            try {
                // Clear existing user-specific permissions
                $stmt = $db->prepare("DELETE FROM user_permissions WHERE user_id = ?");
                $stmt->execute([$userId]);
                
                // Add user-specific permissions (only those different from role)
                $stmt = $db->prepare("
                    INSERT INTO user_permissions (user_id, permission_id, granted_by, notes, status)
                    VALUES (?, ?, ?, ?, 'active')
                ");
                
                foreach ($permissionIds as $permId) {
                    // Only add if not in role's default permissions
                    if (!isset($rolePermSet[$permId])) {
                        $stmt->execute([
                            $userId,
                            $permId,
                            $currentUser['id'],
                            'Custom permission granted by admin'
                        ]);
                    }
                }
                
                // Also track removed permissions (permissions in role but not selected)
                foreach ($rolePermissions as $rolePermId) {
                    if (!in_array($rolePermId, $permissionIds)) {
                        // Add as inactive to track removal
                        $stmt = $db->prepare("
                            INSERT INTO user_permissions (user_id, permission_id, granted_by, notes, status)
                            VALUES (?, ?, ?, ?, 'inactive')
                        ");
                        $stmt->execute([
                            $userId,
                            $rolePermId,
                            $currentUser['id'],
                            'Permission removed from user'
                        ]);
                    }
                }
                
                $db->commit();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'User permissions updated successfully'
                ]);
                
            } catch (Exception $e) {
                $db->rollBack();
                throw $e;
            }
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
    }
    
} catch (Exception $e) {
    error_log('Users API error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}
?>
