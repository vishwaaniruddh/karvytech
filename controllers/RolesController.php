<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Role.php';
require_once __DIR__ . '/../includes/error_handler.php';
require_once __DIR__ . '/../includes/logger.php';

class RolesController extends BaseController {
    private $roleModel;
    
    public function __construct() {
        parent::__construct();
        $this->roleModel = new Role();
    }
    
    public function index() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $statusFilter = isset($_GET['status']) ? trim($_GET['status']) : '';
        
        $result = $this->roleModel->getAllWithPagination($page, 20, $search, $statusFilter);
        
        return [
            'roles' => $result['roles'],
            'pagination' => [
                'current_page' => $result['page'],
                'total_pages' => $result['pages'],
                'total_records' => $result['total'],
                'limit' => $result['limit']
            ],
            'search' => $search,
            'status_filter' => $statusFilter
        ];
    }
    
    public function store() {
        try {
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'display_name' => trim($_POST['display_name'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'role_category' => $_POST['role_category'] ?? 'internal',
                'status' => $_POST['status'] ?? 'active'
            ];
            
            // Validate data
            $errors = $this->roleModel->validateRoleData($data);
            
            if (!empty($errors)) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $errors
                ], 400);
            }
            
            // Create role
            $roleId = $this->roleModel->createRole($data['name'], $data['display_name'], $data['description'], $data['role_category']);
            
            if ($roleId) {
                ErrorHandler::logUserAction('CREATE_ROLE', 'roles', $roleId, null, $data);
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Role created successfully',
                    'role_id' => $roleId
                ]);
            } else {
                return $this->jsonResponse(['success' => false, 'message' => 'Failed to create role'], 500);
            }
        } catch (Exception $e) {
            Logger::error('Role creation failed', ['error' => $e->getMessage()]);
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    public function update($id) {
        try {
            $role = $this->roleModel->getRoleById($id);
            if (!$role) {
                return $this->jsonResponse(['success' => false, 'message' => 'Role not found'], 404);
            }
            
            $data = [
                'display_name' => trim($_POST['display_name'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'role_category' => $_POST['role_category'] ?? 'internal',
                'status' => $_POST['status'] ?? 'active'
            ];
            
            $errors = $this->roleModel->validateRoleData($data, true, $id);
            if (!empty($errors)) {
                return $this->jsonResponse(['success' => false, 'message' => 'Validation failed', 'errors' => $errors], 400);
            }
            
            $success = $this->roleModel->updateRole($id, $data['display_name'], $data['description'], $data['status'], $data['role_category']);
            
            if ($success) {
                ErrorHandler::logUserAction('UPDATE_ROLE', 'roles', $id, $role, $data);
                return $this->jsonResponse(['success' => true, 'message' => 'Role updated successfully']);
            } else {
                return $this->jsonResponse(['success' => false, 'message' => 'Failed to update role'], 500);
            }
        } catch (Exception $e) {
            Logger::error('Role update failed', ['error' => $e->getMessage(), 'role_id' => $id]);
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    public function delete($id) {
        try {
            $role = $this->roleModel->getRoleById($id);
            if (!$role) {
                return $this->jsonResponse(['success' => false, 'message' => 'Role not found'], 404);
            }

            // Check if there is already a pending request for this role
            require_once __DIR__ . '/../models/SuperadminRequest.php';
            $requestModel = new SuperadminRequest();
            
            $existingRequests = $requestModel->findAll([
                'reference_id' => $id,
                'reference_table' => 'roles',
                'status' => 'pending',
                'request_type' => 'role_deletion'
            ]);

            if (!empty($existingRequests)) {
                return $this->jsonResponse(['success' => false, 'message' => 'A deletion request for this role is already pending approval.'], 400);
            }
            
            // Create the superadmin request
            $currentUser = Auth::getCurrentUser();
            $requestData = [
                'request_type' => 'role_deletion',
                'request_title' => 'Delete Role: ' . $role['display_name'],
                'request_description' => "Request to permanently delete role '{$role['name']}' ({$role['display_name']}).",
                'requested_by' => $currentUser['id'],
                'requested_by_name' => $currentUser['username'] ?? $currentUser['email'],
                'requested_by_role' => $currentUser['role'],
                'reference_id' => $id,
                'reference_table' => 'roles',
                'priority' => 'medium',
                'request_data' => json_encode([
                    'role_id' => $id,
                    'role_name' => $role['name'],
                    'role_display_name' => $role['display_name']
                ]),
                'status' => 'pending'
            ];

            $requestId = $requestModel->create($requestData);
            
            if ($requestId) {
                // Set role status to pending_deletion
                $this->roleModel->update($id, ['status' => 'pending_deletion']);
                
                ErrorHandler::logUserAction('REQUEST_ROLE_DELETION', 'roles', $id, $role, ['request_id' => $requestId]);
                return $this->jsonResponse([
                    'success' => true, 
                    'message' => 'Deletion request submitted successfully. A Superadmin must approve this action.'
                ]);
            } else {
                return $this->jsonResponse(['success' => false, 'message' => 'Failed to create deletion request'], 500);
            }
        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}
