<?php
require_once __DIR__ . '/BaseModel.php';

class BaseMaster extends BaseModel {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function getAllWithPagination($page = 1, $limit = 20, $search = '', $status = '') {
        $offset = ($page - 1) * $limit;
        
        $whereClause = '';
        $params = [];
        $conditions = [];
        
        // Search functionality
        if (!empty($search)) {
            $conditions[] = "name LIKE ?";
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
        
        // Get paginated results
        $sql = "SELECT * FROM {$this->table} $whereClause ORDER BY name ASC LIMIT $limit OFFSET $offset";
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
    
    public function findByName($name) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE name = ?");
        $stmt->execute([$name]);
        return $stmt->fetch();
    }
    
    public function getActive() {
        return $this->findAll(['status' => 'active']);
    }
    
    public function getAll() {
        return $this->findAll();
    }
    
    public function validateMasterData($data, $isUpdate = false, $recordId = null) {
        $errors = [];
        
        // Name validation
        if (empty($data['name'])) {
            $errors['name'] = 'Name is required';
        } elseif (strlen($data['name']) < 2) {
            $errors['name'] = 'Name must be at least 2 characters';
        } elseif (strlen($data['name']) > 200) {
            $errors['name'] = 'Name must not exceed 200 characters';
        } else {
            // Check if name already exists
            $existingRecord = $this->findByName($data['name']);
            if ($existingRecord && (!$isUpdate || $existingRecord['id'] != $recordId)) {
                $errors['name'] = 'Name already exists';
            }
        }
        
        // Status validation
        if (isset($data['status']) && !in_array($data['status'], ['active', 'inactive'])) {
            $errors['status'] = 'Invalid status selected';
        }
        
        return $errors;
    }
    
    public function toggleStatus($id) {
        $record = $this->find($id);
        if (!$record) {
            return false;
        }
        
        $newStatus = $record['status'] === 'active' ? 'inactive' : 'active';
        return $this->update($id, ['status' => $newStatus]);
    }
    
    public function getMasterStats() {
        $stats = [];
        
        // Total records
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table}");
        $stats['total'] = $stmt->fetchColumn();
        
        // Active records
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table} WHERE status = 'active'");
        $stats['active'] = $stmt->fetchColumn();
        
        // Inactive records
        $stats['inactive'] = $stats['total'] - $stats['active'];
        
        // Recent records (last 30 days)
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table} WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stats['recent'] = $stmt->fetchColumn();
        
        return $stats;
    }
}
?>