<?php
require_once __DIR__ . '/BaseModel.php';

class Role extends BaseModel {
    protected $table = 'roles';
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Get all roles
     */
    public function getAllRoles($status = 'active') {
        $query = "SELECT * FROM roles";
        $params = [];
        
        if ($status) {
            $query .= " WHERE status = ?";
            $params[] = $status;
        }
        
        $query .= " ORDER BY name ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get all roles with pagination and search
     */
    public function getAllWithPagination($page = 1, $limit = 20, $search = '', $statusFilter = '') {
        $offset = ($page - 1) * $limit;
        $where = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $where[] = "(name LIKE ? OR display_name LIKE ? OR description LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($statusFilter)) {
            $where[] = "status = ?";
            $params[] = $statusFilter;
        }

        $whereClause = implode(" AND ", $where);

        // Count total
        $countSql = "SELECT COUNT(*) FROM roles WHERE $whereClause";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();

        // Get records
        $sql = "SELECT * FROM roles WHERE $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        
        $executionParams = $params;
        $executionParams[] = (int)$limit;
        $executionParams[] = (int)$offset;
        
        // PDO needs types for LIMIT/OFFSET if passed in execute
        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k + 1, $v);
        }
        $stmt->bindValue(count($params) + 1, (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(count($params) + 2, (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $roles = $stmt->fetchAll();

        return [
            'roles' => $roles,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }
    
    /**
     * Get role by ID
     */
    public function getRoleById($roleId) {
        return $this->find($roleId);
    }
    
    /**
     * Get role by name
     */
    public function getRoleByName($name) {
        $stmt = $this->db->prepare("SELECT * FROM roles WHERE name = ?");
        $stmt->execute([$name]);
        return $stmt->fetch();
    }
    
    /**
     * Create new role
     */
    public function createRole($name, $displayName, $description = null, $roleCategory = 'internal') {
        return $this->create([
            'name' => $name,
            'display_name' => $displayName,
            'description' => $description,
            'role_category' => $roleCategory,
            'status' => 'active'
        ]);
    }
    
    /**
     * Update role
     */
    public function updateRole($roleId, $displayName, $description = null, $status = null, $roleCategory = null) {
        $data = [
            'display_name' => $displayName,
            'description' => $description
        ];
        if ($status) {
            $data['status'] = $status;
        }
        if ($roleCategory) {
            $data['role_category'] = $roleCategory;
        }
        return $this->update($roleId, $data);
    }

    /**
     * Delete role
     */
    public function deleteRole($roleId) {
        // Check if role is in use
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE role = (SELECT name FROM roles WHERE id = ?)");
        $stmt->execute([$roleId]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Cannot delete role that is currently assigned to users.");
        }
        return $this->delete($roleId);
    }

    /**
     * Validate role data
     */
    public function validateRoleData($data, $isUpdate = false, $id = null) {
        $errors = [];
        
        if (empty($data['name']) && !$isUpdate) {
            $errors['name'] = "Role name is required.";
        } elseif (!$isUpdate) {
            // Check if name already exists
            $existing = $this->getRoleByName($data['name']);
            if ($existing) {
                $errors['name'] = "Role name already exists.";
            }
        }

        if (empty($data['display_name'])) {
            $errors['display_name'] = "Display name is required.";
        }

        return $errors;
    }
    
    /**
     * Get all permissions for a role
     */
    public function getRolePermissions($roleId) {
        $query = "
            SELECT p.*, m.name as module_name, m.display_name as module_display_name
            FROM permissions p
            JOIN modules m ON p.module_id = m.id
            JOIN role_permissions rp ON p.id = rp.permission_id
            WHERE rp.role_id = ? AND p.status = 'active'
            ORDER BY m.display_name, p.display_name
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$roleId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get permissions grouped by module for a role
     */
    public function getRolePermissionsByModule($roleId) {
        $permissions = $this->getRolePermissions($roleId);
        $grouped = [];
        
        foreach ($permissions as $perm) {
            $moduleName = $perm['module_name'];
            if (!isset($grouped[$moduleName])) {
                $grouped[$moduleName] = [
                    'module_display_name' => $perm['module_display_name'],
                    'permissions' => []
                ];
            }
            $grouped[$moduleName]['permissions'][] = $perm;
        }
        
        return $grouped;
    }
    
    /**
     * Assign permission to role
     */
    public function assignPermissionToRole($roleId, $permissionId) {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE role_id = role_id"
            );
            return $stmt->execute([$roleId, $permissionId]);
        } catch (PDOException $e) {
            throw new Exception("Failed to assign permission: " . $e->getMessage());
        }
    }
    
    /**
     * Remove permission from role
     */
    public function removePermissionFromRole($roleId, $permissionId) {
        try {
            $stmt = $this->db->prepare(
                "DELETE FROM role_permissions WHERE role_id = ? AND permission_id = ?"
            );
            return $stmt->execute([$roleId, $permissionId]);
        } catch (PDOException $e) {
            throw new Exception("Failed to remove permission: " . $e->getMessage());
        }
    }
    
    /**
     * Assign multiple permissions to role
     */
    public function assignPermissionsToRole($roleId, $permissionIds) {
        try {
            // First, remove all existing permissions
            $stmt = $this->db->prepare("DELETE FROM role_permissions WHERE role_id = ?");
            $stmt->execute([$roleId]);
            
            if (empty($permissionIds)) {
                return true;
            }
            
            // Then add new permissions
            $stmt = $this->db->prepare(
                "INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)"
            );
            
            foreach ($permissionIds as $permissionId) {
                $stmt->execute([$roleId, $permissionId]);
            }
            
            return true;
        } catch (PDOException $e) {
            throw new Exception("Failed to assign permissions: " . $e->getMessage());
        }
    }

    /**
     * Get all sidebar menu permissions for a role
     */
    public function getRoleMenuPermissions($roleId) {
        $stmt = $this->db->prepare("
            SELECT menu_item_id, can_access 
            FROM role_menu_permissions 
            WHERE role_id = ?
        ");
        $stmt->execute([$roleId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Assign sidebar menu permissions to role
     */
    public function assignMenuPermissionsToRole($roleId, $menuItemIds) {
        try {
            // First, remove all existing menu permissions
            $stmt = $this->db->prepare("DELETE FROM role_menu_permissions WHERE role_id = ?");
            $stmt->execute([$roleId]);
            
            if (empty($menuItemIds)) {
                return true;
            }
            
            // Then add new menu permissions
            $stmt = $this->db->prepare(
                "INSERT INTO role_menu_permissions (role_id, menu_item_id, can_access) VALUES (?, ?, 1)"
            );
            
            foreach ($menuItemIds as $menuItemId) {
                $stmt->execute([$roleId, $menuItemId]);
            }
            
            return true;
        } catch (PDOException $e) {
            throw new Exception("Failed to assign menu permissions: " . $e->getMessage());
        }
    }
}
?>

