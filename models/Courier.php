<?php
require_once __DIR__ . '/BaseMaster.php';

class Courier extends BaseMaster {
    protected $table = 'couriers';
    
    public function __construct() {
        parent::__construct();
    }
    
    // Override getAllWithPagination to handle courier_name instead of name
    public function getAllWithPagination($page = 1, $limit = 20, $search = '', $status = '') {
        $offset = ($page - 1) * $limit;
        
        $whereClause = '';
        $params = [];
        $conditions = [];
        
        // Search functionality
        if (!empty($search)) {
            $conditions[] = "courier_name LIKE ?";
            $params[] = "%$search%";
        }
        
        // Filter by status
        if (!empty($status)) {
            $conditions[] = "status = ?";
            $params[] = $status;
        }
        
        if (!empty($conditions)) {
            $whereClause = "WHERE " . implode(" AND ", $conditions);
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) FROM {$this->table} $whereClause";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();
        
        // Get paginated results with user info
        $sql = "SELECT c.*, 
                       u1.username as created_by_name,
                       u2.username as updated_by_name
                FROM {$this->table} c
                LEFT JOIN users u1 ON c.created_by = u1.id
                LEFT JOIN users u2 ON c.updated_by = u2.id
                $whereClause 
                ORDER BY c.courier_name ASC 
                LIMIT $limit OFFSET $offset";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $records = $stmt->fetchAll();
        
        return [
            'records' => $records,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }
    
    // Override validateMasterData to handle courier_name
    public function validateMasterData($data, $isUpdate = false, $recordId = null) {
        $errors = [];
        
        // Courier name validation
        if (empty($data['name'])) {
            $errors['name'] = 'Courier name is required';
        } elseif (strlen($data['name']) < 2) {
            $errors['name'] = 'Courier name must be at least 2 characters';
        } elseif (strlen($data['name']) > 255) {
            $errors['name'] = 'Courier name must not exceed 255 characters';
        } else {
            // Check if courier name already exists
            if ($this->courierNameExists($data['name'], $recordId)) {
                $errors['name'] = 'Courier name already exists';
            }
        }
        
        // Status validation
        if (isset($data['status']) && !in_array($data['status'], ['active', 'inactive'])) {
            $errors['status'] = 'Invalid status selected';
        }
        
        return $errors;
    }
    
    /**
     * Get all couriers with optional filtering
     * @param string $search Search term for courier name
     * @param string|null $status Filter by status (active/inactive)
     * @return array List of couriers
     */
    public function getAllCouriers($search = '', $status = null) {
        $sql = "
            SELECT c.*, 
                   u1.username as created_by_name,
                   u2.username as updated_by_name
            FROM couriers c
            LEFT JOIN users u1 ON c.created_by = u1.id
            LEFT JOIN users u2 ON c.updated_by = u2.id
            WHERE 1=1
        ";
        
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND c.courier_name LIKE ?";
            $params[] = "%$search%";
        }
        
        if ($status !== null && in_array($status, ['active', 'inactive'])) {
            $sql .= " AND c.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY c.courier_name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get single courier by ID
     * @param int $id Courier ID
     * @return array|false Courier data or false if not found
     */
    public function getCourierById($id) {
        $stmt = $this->db->prepare("
            SELECT c.*, 
                   u1.username as created_by_name,
                   u2.username as updated_by_name
            FROM couriers c
            LEFT JOIN users u1 ON c.created_by = u1.id
            LEFT JOIN users u2 ON c.updated_by = u2.id
            WHERE c.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Create new courier (override to handle courier_name and audit fields)
     * @param array $data Courier data
     * @return int|false Last insert ID or false on failure
     */
    public function create($data) {
        $userId = Auth::getCurrentUser()['id'] ?? 1;
        
        $stmt = $this->db->prepare("
            INSERT INTO couriers (courier_name, status, created_by, updated_by) 
            VALUES (?, ?, ?, ?)
        ");
        
        if ($stmt->execute([
            $data['name'],
            $data['status'] ?? 'active',
            $userId,
            $userId
        ])) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Update existing courier (override to handle courier_name and audit fields)
     * @param int $id Courier ID
     * @param array $data Courier data
     * @return bool Success status
     */
    public function update($id, $data) {
        $userId = Auth::getCurrentUser()['id'] ?? 1;
        
        $stmt = $this->db->prepare("
            UPDATE couriers 
            SET courier_name = ?, status = ?, updated_by = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['name'],
            $data['status'] ?? 'active',
            $userId,
            $id
        ]);
    }
    
    /**
     * Check if courier name exists
     * @param string $courierName Courier name to check
     * @param int|null $excludeId Courier ID to exclude from check (for updates)
     * @return bool True if name exists, false otherwise
     */
    public function courierNameExists($courierName, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM couriers WHERE courier_name = ?";
        $params = [$courierName];
        
        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }
    
    /**
     * Get active couriers only (for dropdowns)
     * @return array List of active couriers
     */
    public function getActiveCouriers() {
        $stmt = $this->db->prepare("
            SELECT id, courier_name 
            FROM couriers 
            WHERE status = 'active' 
            ORDER BY courier_name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Toggle courier status (override to handle audit fields)
     * @param int $id Courier ID
     * @return bool Success status
     */
    public function toggleStatus($id) {
        $userId = Auth::getCurrentUser()['id'] ?? 1;
        
        $stmt = $this->db->prepare("
            UPDATE couriers 
            SET status = CASE WHEN status = 'active' THEN 'inactive' ELSE 'active' END,
                updated_by = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        
        return $stmt->execute([$userId, $id]);
    }
    
    /**
     * Delete courier (soft delete by setting status to inactive)
     * @param int $id Courier ID
     * @param int $userId User ID performing the action
     * @return bool Success status
     */
    public function deleteCourier($id, $userId) {
        $stmt = $this->db->prepare("
            UPDATE couriers 
            SET status = 'inactive', updated_by = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        
        return $stmt->execute([$userId, $id]);
    }
}
?>