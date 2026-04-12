<?php
require_once __DIR__ . '/BaseModel.php';

class User extends BaseModel {
    protected $table = 'users';
    
    public function __construct() {
        parent::__construct();
    }
    
    public function create($data) {
        // Hash password before storing
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            $data['plain_password'] = $data['password']; // Store plain password for testing
            unset($data['password']);
        }
        
        $userId = parent::create($data);
        
        // Auto-assign menu permissions for admin users
        if ($userId && isset($data['role']) && $data['role'] === 'admin') {
            $this->assignAllMenuPermissions($userId);
        }
        
        return $userId;
    }
    
    /**
     * Assign all active menu items to a user
     */
    private function assignAllMenuPermissions($userId) {
        try {
            // Get all active menu items
            $stmt = $this->db->query("SELECT id FROM menu_items WHERE status = 'active'");
            $menuItems = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (!empty($menuItems)) {
                // Prepare insert statement
                $stmt = $this->db->prepare("
                    INSERT INTO user_menu_permissions (user_id, menu_item_id, can_access, created_at) 
                    VALUES (?, ?, TRUE, NOW())
                    ON DUPLICATE KEY UPDATE can_access = TRUE
                ");
                
                // Assign each menu item
                foreach ($menuItems as $menuItemId) {
                    $stmt->execute([$userId, $menuItemId]);
                }
            }
        } catch (Exception $e) {
            // Log error but don't fail user creation
            error_log("Failed to assign menu permissions to user {$userId}: " . $e->getMessage());
        }
    }
    
    public function update($id, $data) {
        // Get current user data to check if role is changing
        $currentUser = $this->find($id);
        $roleChanged = isset($data['role']) && $currentUser && $currentUser['role'] !== $data['role'];
        
        // Hash password if provided
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            $data['plain_password'] = $data['password']; // Store plain password for testing
            unset($data['password']);
        } else {
            // Remove password field if empty (don't update password)
            unset($data['password']);
        }
        
        $success = parent::update($id, $data);
        
        // Auto-assign menu permissions if role changed to admin
        if ($success && $roleChanged && $data['role'] === 'admin') {
            $this->assignAllMenuPermissions($id);
        }
        
        return $success;
    }
    
    public function findByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }
    
    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    public function findByPhone($phone) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE phone = ?");
        $stmt->execute([$phone]);
        return $stmt->fetch();
    }
    
    public function findByEmailOrPhone($emailOrPhone) {
        $stmt = $this->db->prepare("
            SELECT u.*, r.name as role 
            FROM {$this->table} u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE u.email = ? OR u.phone = ?
        ");
        $stmt->execute([$emailOrPhone, $emailOrPhone]);
        return $stmt->fetch();
    }
    
    public function updateToken($userId, $token) {
        return $this->update($userId, ['jwt_token' => $token]);
    }
    
    public function getAllWithPagination($page = 1, $limit = 20, $search = '', $roleFilter = '', $statusFilter = '') {
        $offset = ($page - 1) * $limit;
        
        $whereClause = '';
        $params = [];
        
        // Build search condition
        if (!empty($search)) {
            $whereClause = "WHERE (u.username LIKE ? OR u.email LIKE ? OR u.role LIKE ? OR v.name LIKE ? OR r.display_name LIKE ?)";
            $searchTerm = "%$search%";
            $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm];
        }
        
        // Add role filter
        if (!empty($roleFilter)) {
            if (!empty($whereClause)) {
                $whereClause .= " AND r.name = ?";
            } else {
                $whereClause = "WHERE r.name = ?";
            }
            $params[] = $roleFilter;
        }

        // Add status filter
        if (!empty($statusFilter)) {
            if (!empty($whereClause)) {
                $whereClause .= " AND u.status = ?";
            } else {
                $whereClause = "WHERE u.status = ?";
            }
            $params[] = $statusFilter;
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) FROM {$this->table} u 
                     LEFT JOIN vendors v ON u.vendor_id = v.id 
                     LEFT JOIN roles r ON u.role_id = r.id 
                     $whereClause";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();
        
        // Get paginated results with vendor and role information
        $sql = "SELECT u.*, v.name as vendor_name, r.name as rbac_role, r.display_name as rbac_role_display
                FROM {$this->table} u 
                LEFT JOIN vendors v ON u.vendor_id = v.id 
                LEFT JOIN roles r ON u.role_id = r.id
                $whereClause 
                ORDER BY u.created_at DESC 
                LIMIT $limit OFFSET $offset";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll();
        
        return [
            'users' => $users,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }
    
    public function getVendors() {
        return $this->findAll(['role' => 'vendor', 'status' => 'active']);
    }
    
    public function validateUserData($data, $isUpdate = false, $userId = null) {
        $errors = [];
        
        // Username validation
        if (empty($data['username'])) {
            $errors['username'] = 'Username is required';
        } elseif (strlen($data['username']) < 3) {
            $errors['username'] = 'Username must be at least 3 characters';
        } elseif (strlen($data['username']) > 50) {
            $errors['username'] = 'Username must not exceed 50 characters';
        } else {
            // Check if username already exists
            $existingUser = $this->findByUsername($data['username']);
            if ($existingUser && (!$isUpdate || $existingUser['id'] != $userId)) {
                $errors['username'] = 'Username already exists';
            }
        }
        
        // Email validation
        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        } else {
            // Check if email already exists
            $existingUser = $this->findByEmail($data['email']);
            if ($existingUser && (!$isUpdate || $existingUser['id'] != $userId)) {
                $errors['email'] = 'Email already exists';
            }
        }
        
        // Phone validation
        if (empty($data['phone'])) {
            $errors['phone'] = 'Phone number is required';
        } elseif (!preg_match('/^[\+]?[1-9][\d]{0,15}$/', $data['phone'])) {
            $errors['phone'] = 'Invalid phone number format';
        } else {
            // Check if phone already exists
            $existingUser = $this->findByPhone($data['phone']);
            if ($existingUser && (!$isUpdate || $existingUser['id'] != $userId)) {
                $errors['phone'] = 'Phone number already exists';
            }
        }
        
        // Password validation (only for new users or when password is provided)
        if (!$isUpdate || !empty($data['password'])) {
            if (empty($data['password'])) {
                $errors['password'] = 'Password is required';
            } elseif (strlen($data['password']) < 6) {
                $errors['password'] = 'Password must be at least 6 characters';
            }
        }
        
        // Role validation
        if (empty($data['role'])) {
            $errors['role'] = 'Role is required';
        } elseif (!in_array($data['role'], ['admin', 'vendor'])) {
            $errors['role'] = 'Invalid role selected';
        }
        
        // Status validation
        if (isset($data['status']) && !in_array($data['status'], ['active', 'disabled'])) {
            $errors['status'] = 'Invalid status selected';
        }
        
        // Vendor validation for vendor role
        if ($data['role'] === 'vendor') {
            if (empty($data['vendor_id'])) {
                $errors['vendor_id'] = 'Please select a vendor when role is vendor';
            } else {
                // Check if vendor exists and is active
                $stmt = $this->db->prepare("SELECT id FROM vendors WHERE id = ? AND status = 'active'");
                $stmt->execute([$data['vendor_id']]);
                if (!$stmt->fetch()) {
                    $errors['vendor_id'] = 'Selected vendor is not valid or inactive';
                }
            }
        }
        
        return $errors;
    }
    
    public function findWithVendor($id) {
        $stmt = $this->db->prepare("
            SELECT u.*, v.name as vendor_name, v.company_name 
            FROM {$this->table} u 
            LEFT JOIN vendors v ON u.vendor_id = v.id 
            WHERE u.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function getUserStats() {
        $stats = [];
        
        // Total users
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table}");
        $stats['total'] = $stmt->fetchColumn();
        
        // Active users
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table} WHERE status = 'active'");
        $stats['active'] = $stmt->fetchColumn();
        
        // Disabled users
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table} WHERE status = 'disabled'");
        $stats['disabled'] = $stmt->fetchColumn();
        
        // Users by role
        $stmt = $this->db->query("SELECT role, COUNT(*) as count FROM {$this->table} GROUP BY role");
        $roleStats = $stmt->fetchAll();
        foreach ($roleStats as $role) {
            $stats['by_role'][$role['role']] = $role['count'];
        }
        
        // Recent users (last 30 days)
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table} WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stats['recent'] = $stmt->fetchColumn();
        
        return $stats;
    }
    
    /**
     * Get all permissions for a user (role-based + user-specific)
     */
    public function getUserPermissions($userId) {
        $query = "
            SELECT DISTINCT p.*, m.name as module_name, m.display_name as module_display_name,
                   CASE WHEN up.id IS NOT NULL THEN 'user' ELSE 'role' END as permission_source
            FROM permissions p
            JOIN modules m ON p.module_id = m.id
            LEFT JOIN role_permissions rp ON p.id = rp.permission_id
            LEFT JOIN users u ON u.role_id = rp.role_id AND u.id = ?
            LEFT JOIN user_permissions up ON p.id = up.permission_id AND up.user_id = ?
            WHERE p.status = 'active' AND m.status = 'active'
            AND (
                -- Include role permissions that are not explicitly removed
                (u.id = ? AND NOT EXISTS (
                    SELECT 1 FROM user_permissions up_removed 
                    WHERE up_removed.user_id = ? 
                    AND up_removed.permission_id = p.id 
                    AND up_removed.status = 'inactive'
                ))
                OR 
                -- Include user-specific active permissions
                (up.user_id = ? AND up.status = 'active')
            )
            ORDER BY m.display_name, p.display_name
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId, $userId, $userId, $userId, $userId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get only role-based permissions for a user
     */
    public function getRolePermissions($userId) {
        $query = "
            SELECT DISTINCT p.*, m.name as module_name, m.display_name as module_display_name
            FROM permissions p
            JOIN modules m ON p.module_id = m.id
            JOIN role_permissions rp ON p.id = rp.permission_id
            JOIN users u ON u.role_id = rp.role_id
            WHERE u.id = ? AND p.status = 'active' AND m.status = 'active'
            ORDER BY m.display_name, p.display_name
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get only user-specific permissions (overrides)
     */
    public function getUserSpecificPermissions($userId) {
        $query = "
            SELECT p.*, m.name as module_name, m.display_name as module_display_name,
                   up.granted_at, up.granted_by, up.notes,
                   u.username as granted_by_username
            FROM user_permissions up
            JOIN permissions p ON up.permission_id = p.id
            JOIN modules m ON p.module_id = m.id
            LEFT JOIN users u ON up.granted_by = u.id
            WHERE up.user_id = ? AND up.status = 'active' AND p.status = 'active'
            ORDER BY m.display_name, p.display_name
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Grant specific permission to user
     */
    public function grantPermission($userId, $permissionId, $grantedBy = null, $notes = null) {
        $query = "
            INSERT INTO user_permissions (user_id, permission_id, granted_by, notes, status)
            VALUES (?, ?, ?, ?, 'active')
            ON DUPLICATE KEY UPDATE 
                granted_by = VALUES(granted_by),
                notes = VALUES(notes),
                status = 'active',
                granted_at = CURRENT_TIMESTAMP
        ";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$userId, $permissionId, $grantedBy, $notes]);
    }
    
    /**
     * Revoke specific permission from user
     */
    public function revokePermission($userId, $permissionId) {
        $query = "DELETE FROM user_permissions WHERE user_id = ? AND permission_id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$userId, $permissionId]);
    }
    
    /**
     * Check if user has a specific permission (checks both role and user-specific)
     */
    public function hasSpecificPermission($userId, $permissionId) {
        $query = "
            SELECT COUNT(*) FROM (
                SELECT p.id FROM permissions p
                JOIN role_permissions rp ON p.id = rp.permission_id
                JOIN users u ON u.role_id = rp.role_id
                WHERE u.id = ? AND p.id = ? AND p.status = 'active'
                UNION
                SELECT p.id FROM permissions p
                JOIN user_permissions up ON p.id = up.permission_id
                WHERE up.user_id = ? AND p.id = ? AND up.status = 'active' AND p.status = 'active'
            ) as combined_permissions
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId, $permissionId, $userId, $permissionId]);
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Check if user has specific permission
     */
    public function hasPermission($userId, $moduleName, $permissionName = 'view') {
        $query = "
            SELECT COUNT(*) as has_perm
            FROM permissions p
            JOIN modules m ON p.module_id = m.id
            LEFT JOIN role_permissions rp ON p.id = rp.permission_id
            LEFT JOIN users u ON u.role_id = rp.role_id AND u.id = ?
            LEFT JOIN user_permissions up ON p.id = up.permission_id AND up.user_id = ?
            WHERE m.name = ? AND p.name = ? AND p.status = 'active' AND m.status = 'active'
            AND (
                -- Include role permissions that are not explicitly removed
                (u.id = ? AND NOT EXISTS (
                    SELECT 1 FROM user_permissions up_removed 
                    WHERE up_removed.user_id = ? 
                    AND up_removed.permission_id = p.id 
                    AND up_removed.status = 'inactive'
                ))
                OR 
                -- Include user-specific active permissions
                (up.user_id = ? AND up.status = 'active')
            )
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId, $userId, $moduleName, $permissionName, $userId, $userId, $userId]);
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Get user role with permissions
     */
    public function getUserRoleWithPermissions($userId) {
        $user = $this->find($userId);
        if (!$user || !$user['role_id']) {
            return null;
        }
        
        require_once __DIR__ . '/Role.php';
        $roleModel = new Role();
        $role = $roleModel->getRoleById($user['role_id']);
        $permissions = $roleModel->getRolePermissionsByModule($user['role_id']);
        
        return [
            'user' => $user,
            'role' => $role,
            'permissions' => $permissions
        ];
    }
    
    /**
     * Assign role to user
     */
    public function assignRole($userId, $roleId) {
        return $this->update($userId, ['role_id' => $roleId]);
    }
}