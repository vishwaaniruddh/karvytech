<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

class BoqItem {
    private $db;
    private $table = 'boq_items';
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($data) {
        // Add created_by from current user
        $currentUser = Auth::getCurrentUser();
        if ($currentUser) {
            $data['created_by'] = $currentUser['id'];
        }
        
        // Convert boolean values
        if (isset($data['need_serial_number'])) {
            $data['need_serial_number'] = $data['need_serial_number'] ? 1 : 0;
        }
        
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute(array_values($data));
        
        return $result ? $this->db->lastInsertId() : false;
    }
    
    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function update($id, $data) {
        // Add updated_by from current user
        $currentUser = Auth::getCurrentUser();
        if ($currentUser) {
            $data['updated_by'] = $currentUser['id'];
        }
        
        // Convert boolean values
        if (isset($data['need_serial_number'])) {
            $data['need_serial_number'] = $data['need_serial_number'] ? 1 : 0;
        }
        
        $fields = array_keys($data);
        $setClause = array_map(function($field) {
            return "$field = ?";
        }, $fields);
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClause) . " WHERE id = ?";
        
        $values = array_values($data);
        $values[] = $id;
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }
    
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    public function getAllWithPagination($page = 1, $limit = 20, $search = '', $category = '', $status = '') {
        $offset = ($page - 1) * $limit;
        
        $whereClause = '';
        $params = [];
        $conditions = [];
        
        // Search functionality
        if (!empty($search)) {
            $conditions[] = "(item_name LIKE ? OR item_code LIKE ? OR description LIKE ?)";
            $searchTerm = "%$search%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        }
        
        // Filter by category
        if (!empty($category)) {
            $conditions[] = "category = ?";
            $params[] = $category;
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
        $sql = "SELECT * FROM {$this->table} $whereClause ORDER BY item_name ASC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }
    
    public function getActive() {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY item_name");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY item_name");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getCategories() {
        $stmt = $this->db->prepare("SELECT DISTINCT category FROM {$this->table} WHERE category IS NOT NULL AND category != '' ORDER BY category");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    public function updateStatus($id, $status) {
        $currentUser = Auth::getCurrentUser();
        $sql = "UPDATE {$this->table} SET status = ?, updated_by = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$status, $currentUser['id'] ?? null, $id]);
    }
    
    public function getStats() {
        $stats = [
            'total' => 0,
            'active' => 0,
            'inactive' => 0,
            'categories' => 0,
            'serial_required' => 0
        ];
        
        // Total items
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table}");
        $stats['total'] = $stmt->fetchColumn();
        
        // Status breakdown
        $stmt = $this->db->query("SELECT status, COUNT(*) as count FROM {$this->table} GROUP BY status");
        $statusStats = $stmt->fetchAll();
        foreach ($statusStats as $stat) {
            $stats[$stat['status']] = $stat['count'];
        }
        
        // Categories count
        $stmt = $this->db->query("SELECT COUNT(DISTINCT category) FROM {$this->table} WHERE category IS NOT NULL AND category != ''");
        $stats['categories'] = $stmt->fetchColumn();
        
        // Serial number required items
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table} WHERE need_serial_number = 1");
        $stats['serial_required'] = $stmt->fetchColumn();
        
        return $stats;
    }
    
    public function validateItemData($data, $isUpdate = false, $id = null) {
        $errors = [];
        
        // Required fields
        if (empty($data['item_name'])) {
            $errors[] = 'Item name is required';
        }
        
        if (empty($data['unit'])) {
            $errors[] = 'Unit is required';
        }
        
        // Check for duplicate item code
        if (!empty($data['item_code'])) {
            $sql = "SELECT id FROM {$this->table} WHERE item_code = ?";
            $params = [$data['item_code']];
            
            if ($isUpdate && $id) {
                $sql .= " AND id != ?";
                $params[] = $id;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            if ($stmt->fetch()) {
                $errors[] = 'Item code already exists';
            }
        }
        
        // Validate status
        if (!empty($data['status']) && !in_array($data['status'], ['active', 'inactive'])) {
            $errors[] = 'Invalid status value';
        }
        
        return $errors;
    }
    
    public function searchByName($query, $limit = 10) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE status = 'active' 
                AND (item_name LIKE ? OR item_code LIKE ?) 
                ORDER BY item_name 
                LIMIT ?";
        
        $searchTerm = "%$query%";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getByCategory($category) {
        $sql = "SELECT * FROM {$this->table} WHERE category = ? AND status = 'active' ORDER BY item_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$category]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getAllItems() {
        // Alias for getAll() method for compatibility
        return $this->getAll();
    }
    
    public function findByItemCode($itemCode) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE item_code = ?");
        $stmt->execute([$itemCode]);
        return $stmt->fetch();
    }
    
    public function findByItemName($itemName) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE item_name = ?");
        $stmt->execute([$itemName]);
        return $stmt->fetch();
    }
    
}
?>