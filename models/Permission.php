<?php

class Permission {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Get all modules
     */
    public function getAllModules($status = 'active') {
        $query = "SELECT * FROM modules";
        $params = [];
        
        if ($status) {
            $query .= " WHERE status = ?";
            $params[] = $status;
        }
        
        $query .= " ORDER BY display_name ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get module by ID
     */
    public function getModuleById($moduleId) {
        $stmt = $this->db->prepare("SELECT * FROM modules WHERE id = ?");
        $stmt->execute([$moduleId]);
        return $stmt->fetch();
    }
    
    /**
     * Get module by name
     */
    public function getModuleByName($name) {
        $stmt = $this->db->prepare("SELECT * FROM modules WHERE name = ?");
        $stmt->execute([$name]);
        return $stmt->fetch();
    }
    
    /**
     * Get all permissions for a module
     */
    public function getModulePermissions($moduleId) {
        $query = "
            SELECT * FROM permissions 
            WHERE module_id = ? AND status = 'active'
            ORDER BY display_name ASC
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$moduleId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get permission by ID
     */
    public function getPermissionById($permissionId) {
        $stmt = $this->db->prepare("SELECT * FROM permissions WHERE id = ?");
        $stmt->execute([$permissionId]);
        return $stmt->fetch();
    }
    
    /**
     * Get permission by module and name
     */
    public function getPermissionByModuleAndName($moduleId, $name) {
        $stmt = $this->db->prepare(
            "SELECT * FROM permissions WHERE module_id = ? AND name = ?"
        );
        $stmt->execute([$moduleId, $name]);
        return $stmt->fetch();
    }
    
    /**
     * Get all permissions grouped by module
     */
    public function getAllPermissionsGrouped() {
        $query = "
            SELECT m.id as module_id, m.name as module_name, m.display_name as module_display_name,
                   p.id as permission_id, p.name, p.display_name, p.action
            FROM modules m
            LEFT JOIN permissions p ON m.id = p.module_id AND p.status = 'active'
            WHERE m.status = 'active'
            ORDER BY m.display_name, p.display_name
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $moduleId = $row['module_id'];
            if (!isset($result[$moduleId])) {
                $result[$moduleId] = [
                    'module_id' => $moduleId,
                    'module_name' => $row['module_name'],
                    'module_display_name' => $row['module_display_name'],
                    'permissions' => []
                ];
            }
            
            if ($row['permission_id']) {
                $result[$moduleId]['permissions'][] = [
                    'id' => $row['permission_id'],
                    'name' => $row['name'],
                    'display_name' => $row['display_name'],
                    'action' => $row['action']
                ];
            }
        }
        
        return array_values($result);
    }
    
    /**
     * Get all permissions grouped by module (keyed by module name)
     */
    public function getAllPermissionsByModule() {
        $query = "
            SELECT m.name as module_name, m.display_name as module_display_name,
                   p.id, p.name, p.display_name, p.action
            FROM modules m
            LEFT JOIN permissions p ON m.id = p.module_id AND p.status = 'active'
            WHERE m.status = 'active'
            ORDER BY m.display_name, p.display_name
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $moduleName = $row['module_name'];
            if (!isset($result[$moduleName])) {
                $result[$moduleName] = [
                    'name' => $moduleName,
                    'display_name' => $row['module_display_name'],
                    'permissions' => []
                ];
            }
            
            if ($row['id']) {
                $result[$moduleName]['permissions'][] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'display_name' => $row['display_name'],
                    'action' => $row['action']
                ];
            }
        }
        
        return $result;
    }
    
    /**
     * Create new module
     */
    public function createModule($name, $displayName, $description = null, $icon = null) {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO modules (name, display_name, description, icon) VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([$name, $displayName, $description, $icon]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Failed to create module: " . $e->getMessage());
        }
    }
    
    /**
     * Create new permission
     */
    public function createPermission($moduleId, $name, $displayName, $description = null, $action = null) {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO permissions (module_id, name, display_name, description, action) 
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->execute([$moduleId, $name, $displayName, $description, $action]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Failed to create permission: " . $e->getMessage());
        }
    }
    
    /**
     * Update permission
     */
    public function updatePermission($permissionId, $displayName, $description = null, $status = null) {
        try {
            $query = "UPDATE permissions SET display_name = ?, description = ?";
            $params = [$displayName, $description];
            
            if ($status) {
                $query .= ", status = ?";
                $params[] = $status;
            }
            
            $query .= " WHERE id = ?";
            $params[] = $permissionId;
            
            $stmt = $this->db->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            throw new Exception("Failed to update permission: " . $e->getMessage());
        }
    }
}
?>
