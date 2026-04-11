<?php
require_once __DIR__ . '/BaseMaster.php';
require_once __DIR__ . '/../config/auth.php';

class BoqMaster extends BaseMaster {
    protected $table = 'boq_master';
    protected $primaryKey = 'id';
    
    public function __construct() {
        parent::__construct();
    }
    
    public function create($data) {
        // Add created_by from current user
        $currentUser = Auth::getCurrentUser();
        if ($currentUser) {
            $data['created_by'] = $currentUser['id'];
        }
        
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));
        
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data) {
        // Add updated_by from current user
        $currentUser = Auth::getCurrentUser();
        if ($currentUser) {
            $data['updated_by'] = $currentUser['id'];
        }
        
        $fields = array_keys($data);
        $setClause = array_map(function($field) {
            return "$field = ?";
        }, $fields);
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClause) . " WHERE {$this->primaryKey} = ?";
        
        $params = array_values($data);
        $params[] = $id;
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function find($id) {
        $stmt = $this->db->prepare("SELECT b.*, c.name as customer_name, u.username as created_by_name 
                                    FROM {$this->table} b 
                                    JOIN customers c ON b.customer_id = c.id 
                                    LEFT JOIN users u ON b.created_by = u.id
                                    WHERE b.{$this->primaryKey} = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
        return $stmt->execute([$id]);
    }
    
    public function getAllWithPagination($page = 1, $limit = 20, $search = '', $status = '') {
        $offset = ($page - 1) * $limit;
        
        $whereClause = '';
        $params = [];
        $conditions = [];
        
        // Search functionality
        if (!empty($search)) {
            $conditions[] = "(b.boq_name LIKE ? OR c.name LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        // Filter by status
        if (!empty($status)) {
            $conditions[] = "b.status = ?";
            $params[] = $status;
        }
        
        if (!empty($conditions)) {
            $whereClause = "WHERE " . implode(" AND ", $conditions);
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) FROM {$this->table} b 
                     LEFT JOIN customers c ON b.customer_id = c.id 
                     $whereClause";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();
        
        // Get paginated results with user and customer information
        $sql = "SELECT b.*, 
                       c.name as customer_name,
                       cu.username as created_by_name,
                       uu.username as updated_by_name,
                       (SELECT COUNT(*) FROM boq_master_items WHERE boq_master_id = b.id) as item_count
                FROM {$this->table} b 
                LEFT JOIN customers c ON b.customer_id = c.id
                LEFT JOIN users cu ON b.created_by = cu.id 
                LEFT JOIN users uu ON b.updated_by = uu.id 
                $whereClause 
                ORDER BY b.created_at DESC 
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
    
    public function toggleStatus($id) {
        $record = $this->find($id);
        if (!$record) {
            return false;
        }
        
        $newStatus = $record['status'] === 'active' ? 'inactive' : 'active';
        return $this->update($id, ['status' => $newStatus]);
    }
    
    public function validateBoqData($data, $isUpdate = false, $recordId = null) {
        $errors = [];
        
        // BOQ name validation
        if (empty($data['boq_name'])) {
            $errors['boq_name'] = 'BOQ name is required';
        } elseif (strlen($data['boq_name']) < 2) {
            $errors['boq_name'] = 'BOQ name must be at least 2 characters';
        } elseif (strlen($data['boq_name']) > 200) {
            $errors['boq_name'] = 'BOQ name must not exceed 200 characters';
        } else {
            // Check if name already exists
            $existingRecord = $this->findByName($data['boq_name']);
            if ($existingRecord && (!$isUpdate || $existingRecord[$this->primaryKey] != $recordId)) {
                $errors['boq_name'] = 'BOQ name already exists';
            }
        }
        
        // Status validation
        if (isset($data['status']) && !in_array($data['status'], ['active', 'inactive'])) {
            $errors['status'] = 'Invalid status selected';
        }
        
        return $errors;
    }
    
    public function findByName($name) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE boq_name = ?");
        $stmt->execute([$name]);
        return $stmt->fetch();
    }
    
    public function getActive() {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY boq_name");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY boq_name");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getBoqStats() {
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