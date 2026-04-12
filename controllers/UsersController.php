<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../includes/error_handler.php';
require_once __DIR__ . '/../includes/logger.php';

class UsersController extends BaseController {
    private $userModel;
    
    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
    }
    
    public function index() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $roleFilter = isset($_GET['role']) ? trim($_GET['role']) : '';
        $statusFilter = isset($_GET['status']) ? trim($_GET['status']) : '';
        
        $result = $this->userModel->getAllWithPagination($page, 20, $search, $roleFilter, $statusFilter);
        
        // Fetch dashboard stats
        $stats = [
            'total' => $this->db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
            'active' => $this->db->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetchColumn(),
            'admins' => $this->db->query("SELECT COUNT(u.id) FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.role IN ('admin', 'superadmin') OR r.name IN ('admin', 'superadmin')")->fetchColumn(),
            'vendors' => $this->db->query("SELECT COUNT(u.id) FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.role IN ('vendor', 'contractor') OR r.name IN ('vendor', 'contractor')")->fetchColumn(),
            'disabled' => $this->db->query("SELECT COUNT(*) FROM users WHERE status = 'disabled'")->fetchColumn()
        ];
        
        return [
            'users' => $result['users'],
            'pagination' => [
                'current_page' => $result['page'],
                'total_pages' => $result['pages'],
                'total_records' => $result['total'],
                'limit' => $result['limit']
            ],
            'stats' => $stats,
            'search' => $search,
            'role_filter' => $roleFilter,
            'status_filter' => $statusFilter
        ];
    }
    
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->store();
        }
        
        // Return empty user for form
        return ['user' => null];
    }
    
    public function store() {
        try {
            $data = [
                'username' => trim($_POST['username'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'role' => $_POST['role'] ?? '',
                'status' => $_POST['status'] ?? 'active'
            ];
            
            // Add vendor_id if role is vendor
            if ($data['role'] === 'vendor' && !empty($_POST['vendor_id'])) {
                $data['vendor_id'] = (int)$_POST['vendor_id'];
            } else {
                $data['vendor_id'] = null;
            }
            
            // Validate data
            $errors = $this->userModel->validateUserData($data);
            
            if (!empty($errors)) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $errors
                ], 400);
            }
            
            // Create user
            $userId = $this->userModel->create($data);
            
            if ($userId) {
                // Log the action
                ErrorHandler::logUserAction('CREATE_USER', 'users', $userId, null, $data);
                
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'User created successfully',
                    'user_id' => $userId
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to create user'
                ], 500);
            }
            
        } catch (Exception $e) {
            Logger::error('User creation failed', ['error' => $e->getMessage()]);
            return $this->jsonResponse([
                'success' => false,
                'message' => 'An error occurred while creating the user'
            ], 500);
        }
    }
    
    public function show($id) {
        $user = $this->userModel->findWithVendor($id);
        
        if (!$user) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
        
        // Remove password hash from response
        unset($user['password_hash']);
        
        return $this->jsonResponse([
            'success' => true,
            'user' => $user
        ]);
    }
    
    public function edit($id) {
        $user = $this->userModel->findWithVendor($id);
        
        if (!$user) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->update($id);
        }
        
        // Remove password hash from response
        unset($user['password_hash']);
        
        return $this->jsonResponse([
            'success' => true,
            'user' => $user
        ]);
    }
    
    public function update($id) {
        try {
            $user = $this->userModel->find($id);
            
            if (!$user) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }
            
            $data = [
                'username' => trim($_POST['username'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'role' => $_POST['role'] ?? '',
                'status' => $_POST['status'] ?? 'active'
            ];
            
            // Add vendor_id if role is vendor
            if ($data['role'] === 'vendor' && !empty($_POST['vendor_id'])) {
                $data['vendor_id'] = (int)$_POST['vendor_id'];
            } else {
                $data['vendor_id'] = null;
            }
            
            // Validate data
            $errors = $this->userModel->validateUserData($data, true, $id);
            
            if (!empty($errors)) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $errors
                ], 400);
            }
            
            // Update user
            $success = $this->userModel->update($id, $data);
            
            if ($success) {
                // Log the action
                ErrorHandler::logUserAction('UPDATE_USER', 'users', $id, $user, $data);
                
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'User updated successfully'
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to update user'
                ], 500);
            }
            
        } catch (Exception $e) {
            Logger::error('User update failed', ['error' => $e->getMessage(), 'user_id' => $id]);
            return $this->jsonResponse([
                'success' => false,
                'message' => 'An error occurred while updating the user'
            ], 500);
        }
    }
    
    public function delete($id) {
        try {
            $user = $this->userModel->find($id);
            
            if (!$user) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }
            
            // Prevent deletion of current user
            $currentUser = Auth::getCurrentUser();
            if ($currentUser['id'] == $id) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'You cannot delete your own account'
                ], 400);
            }
            
            // Check if user has assigned sites
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM sites WHERE vendor = ?");
            $stmt->execute([$id]);
            $assignedSites = $stmt->fetchColumn();
            
            if ($assignedSites > 0) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Cannot delete user with assigned sites. Please reassign sites first.'
                ], 400);
            }
            
            // Delete user
            $success = $this->userModel->delete($id);
            
            if ($success) {
                // Log the action
                ErrorHandler::logUserAction('DELETE_USER', 'users', $id, $user, null);
                
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'User deleted successfully'
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to delete user'
                ], 500);
            }
            
        } catch (Exception $e) {
            Logger::error('User deletion failed', ['error' => $e->getMessage(), 'user_id' => $id]);
            return $this->jsonResponse([
                'success' => false,
                'message' => 'An error occurred while deleting the user'
            ], 500);
        }
    }
    
    public function toggleStatus($id) {
        try {
            $user = $this->userModel->find($id);
            
            if (!$user) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }
            
            // Prevent deactivation of current user
            $currentUser = Auth::getCurrentUser();
            if ($currentUser['id'] == $id && $user['status'] === 'active') {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'You cannot deactivate your own account'
                ], 400);
            }
            
            $newStatus = $user['status'] === 'active' ? 'disabled' : 'active';
            
            $success = $this->userModel->update($id, ['status' => $newStatus]);
            
            if ($success) {
                // Log the action
                ErrorHandler::logUserAction('TOGGLE_USER_STATUS', 'users', $id, 
                    ['status' => $user['status']], 
                    ['status' => $newStatus]
                );
                
                return $this->jsonResponse([
                    'success' => true,
                    'message' => "User {$newStatus} successfully",
                    'new_status' => $newStatus
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to update user status'
                ], 500);
            }
            
        } catch (Exception $e) {
            Logger::error('User status toggle failed', ['error' => $e->getMessage(), 'user_id' => $id]);
            return $this->jsonResponse([
                'success' => false,
                'message' => 'An error occurred while updating user status'
            ], 500);
        }
    }
}