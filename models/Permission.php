<?php
require_once __DIR__ . '/BaseModel.php';

class Permission extends BaseModel {
    protected $table = 'permissions';
    
    public function __construct() {
        parent::__construct();
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
     * Get all permissions with pagination and search
     */
    public function getAllWithPagination($page = 1, $limit = 20, $search = '', $moduleId = null) {
        $offset = ($page - 1) * $limit;
        $where = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $where[] = "(p.name LIKE ? OR p.display_name LIKE ? OR m.display_name LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if ($moduleId) {
            $where[] = "p.module_id = ?";
            $params[] = $moduleId;
        }

        $whereClause = implode(" AND ", $where);

        // Count total
        $countSql = "SELECT COUNT(*) FROM permissions p 
                     JOIN modules m ON p.module_id = m.id 
                     WHERE $whereClause";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();

        // Get records
        $sql = "SELECT p.*, m.display_name as module_display_name 
                FROM permissions p 
                JOIN modules m ON p.module_id = m.id 
                WHERE $whereClause 
                ORDER BY m.display_name ASC, p.display_name ASC 
                LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k + 1, $v);
        }
        $stmt->bindValue(count($params) + 1, (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(count($params) + 2, (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return [
            'permissions' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }
    
    /**
     * Get permission by ID with Module info
     */
    public function getPermissionById($id) {
        $sql = "SELECT p.*, m.display_name as module_display_name 
                FROM permissions p 
                JOIN modules m ON p.module_id = m.id 
                WHERE p.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get all permissions grouped by module
     */
    public function getAllPermissionsGrouped() {
        $query = "
            SELECT m.id as module_id, m.name as module_name, m.display_name as module_display_name,
                   p.id as permission_id, p.name, p.display_name, p.action, p.status
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
                    'action' => $row['action'],
                    'status' => $row['status']
                ];
            }
        }
        
        return array_values($result);
    }

    /**
     * Create new module
     */
    public function createModule($name, $displayName, $description = null, $icon = null) {
        $sql = "INSERT INTO modules (name, display_name, description, icon) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$name, $displayName, $description, $icon]);
        return $this->db->lastInsertId();
    }

    /**
     * Validate permission data
     */
    public function validatePermissionData($data, $isUpdate = false) {
        $errors = [];
        
        if (empty($data['module_id'])) {
            $errors['module_id'] = "Module is required.";
        }
        
        if (empty($data['name']) && !$isUpdate) {
            $errors['name'] = "Permission internal name is required.";
        }
        
        if (empty($data['display_name'])) {
            $errors['display_name'] = "Display name is required.";
        }

        return $errors;
    }
}
?>

