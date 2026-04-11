<?php

class Role {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
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
     * Get role by ID
     */
    public function getRoleById($roleId) {
        $stmt = $this->db->prepare("SELECT * FROM roles WHERE id = ?");
        $stmt->execute([$roleId]);
        return $stmt->fetch();
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
    public function createRole($name, $displayName, $description = null) {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO roles (name, display_name, description) VALUES (?, ?, ?)"
            );
            $stmt->execute([$name, $displayName, $description]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Failed to create role: " . $e->getMessage());
        }
    }
    
    /**
     * Update role
     */
    public function updateRole($roleId, $displayName, $description = null, $status = null) {
        try {
            $query = "UPDATE roles SET display_name = ?, description = ?";
            $params = [$displayName, $description];
            
            if ($status) {
                $query .= ", status = ?";
                $params[] = $status;
            }
            
            $query .= " WHERE id = ?";
            $params[] = $roleId;
            
            $stmt = $this->db->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            throw new Exception("Failed to update role: " . $e->getMessage());
        }
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
}
?>
