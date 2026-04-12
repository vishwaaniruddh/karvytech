<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Permission.php';
require_once __DIR__ . '/../includes/error_handler.php';
require_once __DIR__ . '/../includes/logger.php';

class PermissionsController extends BaseController {
    private $permissionModel;
    
    public function __construct() {
        parent::__construct();
        $this->permissionModel = new Permission();
    }
    
    public function index() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $moduleId = isset($_GET['module_id']) && !empty($_GET['module_id']) ? (int)$_GET['module_id'] : null;
        
        $result = $this->permissionModel->getAllWithPagination($page, 20, $search, $moduleId);
        $modules = $this->permissionModel->getAllModules();
        
        return [
            'permissions' => $result['permissions'],
            'pagination' => [
                'current_page' => $result['page'],
                'total_pages' => $result['pages'],
                'total_records' => $result['total'],
                'limit' => $result['limit']
            ],
            'modules' => $modules,
            'search' => $search,
            'module_id' => $moduleId
        ];
    }
    
    public function store() {
        try {
            $data = [
                'module_id' => (int)($_POST['module_id'] ?? 0),
                'name' => trim($_POST['name'] ?? ''),
                'display_name' => trim($_POST['display_name'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'status' => $_POST['status'] ?? 'active'
            ];
            
            // Validate
            $errors = $this->permissionModel->validatePermissionData($data);
            if (!empty($errors)) {
                return $this->jsonResponse(['success' => false, 'message' => 'Validation failed', 'errors' => $errors], 400);
            }
            
            $id = $this->permissionModel->create($data);
            if ($id) {
                ErrorHandler::logUserAction('CREATE_PERMISSION', 'permissions', $id, null, $data);
                return $this->jsonResponse(['success' => true, 'message' => 'Permission created successfully', 'id' => $id]);
            }
            return $this->jsonResponse(['success' => false, 'message' => 'Failed to create permission'], 500);
        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    public function update($id) {
        try {
            $permission = $this->permissionModel->find($id);
            if (!$permission) {
                return $this->jsonResponse(['success' => false, 'message' => 'Permission not found'], 404);
            }
            
            $data = [
                'module_id' => (int)($_POST['module_id'] ?? $permission['module_id']),
                'display_name' => trim($_POST['display_name'] ?? $permission['display_name']),
                'description' => trim($_POST['description'] ?? $permission['description']),
                'status' => $_POST['status'] ?? $permission['status']
            ];
            
            $errors = $this->permissionModel->validatePermissionData($data, true);
            if (!empty($errors)) {
                return $this->jsonResponse(['success' => false, 'message' => 'Validation failed', 'errors' => $errors], 400);
            }
            
            $success = $this->permissionModel->update($id, $data);
            if ($success) {
                ErrorHandler::logUserAction('UPDATE_PERMISSION', 'permissions', $id, $permission, $data);
                return $this->jsonResponse(['success' => true, 'message' => 'Permission updated successfully']);
            }
            return $this->jsonResponse(['success' => false, 'message' => 'Failed to update permission'], 500);
        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    public function delete($id) {
        try {
            $permission = $this->permissionModel->find($id);
            if (!$permission) {
                return $this->jsonResponse(['success' => false, 'message' => 'Permission not found'], 404);
            }

            // Check if there is already a pending request
            require_once __DIR__ . '/../models/SuperadminRequest.php';
            $requestModel = new SuperadminRequest();
            $existing = $requestModel->findAll(['reference_id' => $id, 'reference_table' => 'permissions', 'status' => 'pending']);
            if (!empty($existing)) {
                return $this->jsonResponse(['success' => false, 'message' => 'Deletion request already pending.'], 400);
            }
            
            // Create request
            $currentUser = Auth::getCurrentUser();
            $requestData = [
                'request_type' => 'permission_deletion',
                'request_title' => 'Delete Permission: ' . $permission['display_name'],
                'request_description' => "Request to delete permission '{$permission['name']}' from module ID {$permission['module_id']}.",
                'requested_by' => $currentUser['id'],
                'requested_by_name' => $currentUser['username'] ?? $currentUser['email'],
                'requested_by_role' => $currentUser['role'],
                'reference_id' => $id,
                'reference_table' => 'permissions',
                'priority' => 'medium',
                'request_data' => json_encode($permission),
                'status' => 'pending'
            ];

            $requestId = $requestModel->create($requestData);
            if ($requestId) {
                $this->permissionModel->update($id, ['status' => 'pending_deletion']);
                ErrorHandler::logUserAction('REQUEST_PERMISSION_DELETION', 'permissions', $id, $permission, ['request_id' => $requestId]);
                return $this->jsonResponse(['success' => true, 'message' => 'Deletion request submitted for superadmin approval.']);
            }
            return $this->jsonResponse(['success' => false, 'message' => 'Failed to submit request'], 500);
        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}
