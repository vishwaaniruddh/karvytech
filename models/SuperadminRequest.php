<?php
require_once __DIR__ . '/BaseModel.php';

class SuperadminRequest extends BaseModel {
    protected $table = 'superadmin_requests';
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Create a new request
     */
    public function createRequest($data) {
        $fields = ['request_type', 'request_title', 'request_description', 'requested_by', 
                   'requested_by_name', 'requested_by_role', 'request_data', 'reference_id', 
                   'reference_table', 'priority'];
        
        $values = [];
        $placeholders = [];
        
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $values[] = $data[$field];
                $placeholders[] = '?';
            }
        }
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', array_keys($data)) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Get all requests with pagination and filters
     */
    public function getAllWithPagination($page = 1, $limit = 20, $filters = []) {
        $offset = ($page - 1) * $limit;
        
        $conditions = [];
        $params = [];
        
        // Filter by status
        if (!empty($filters['status'])) {
            $conditions[] = "sr.status = ?";
            $params[] = $filters['status'];
        }
        
        // Filter by request type
        if (!empty($filters['request_type'])) {
            $conditions[] = "sr.request_type = ?";
            $params[] = $filters['request_type'];
        }
        
        // Filter by priority
        if (!empty($filters['priority'])) {
            $conditions[] = "sr.priority = ?";
            $params[] = $filters['priority'];
        }
        
        // Search
        if (!empty($filters['search'])) {
            $conditions[] = "(sr.request_title LIKE ? OR sr.request_description LIKE ? OR sr.requested_by_name LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        
        // Get total count
        $countSql = "SELECT COUNT(*) FROM {$this->table} sr $whereClause";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();
        
        // Get paginated results
        $sql = "SELECT sr.*, 
                       u.username as reviewed_by_name
                FROM {$this->table} sr
                LEFT JOIN users u ON sr.reviewed_by = u.id
                $whereClause
                ORDER BY 
                    CASE sr.priority 
                        WHEN 'urgent' THEN 1
                        WHEN 'high' THEN 2
                        WHEN 'medium' THEN 3
                        WHEN 'low' THEN 4
                    END,
                    sr.created_at DESC
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
    
    /**
     * Get statistics
     */
    public function getStats() {
        $sql = "SELECT 
                    COUNT(*) as total_requests,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                    SUM(CASE WHEN priority = 'urgent' AND status = 'pending' THEN 1 ELSE 0 END) as urgent_pending,
                    SUM(CASE WHEN priority = 'high' AND status = 'pending' THEN 1 ELSE 0 END) as high_pending
                FROM {$this->table}";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetch();
    }
    
    /**
     * Approve a request
     */
    public function approve($id, $reviewedBy, $remarks = null) {
        $sql = "UPDATE {$this->table} 
                SET status = 'approved', 
                    reviewed_by = ?, 
                    reviewed_at = NOW(),
                    remarks = ?
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$reviewedBy, $remarks, $id]);
    }
    
    /**
     * Reject a request
     */
    public function reject($id, $reviewedBy, $remarks) {
        $sql = "UPDATE {$this->table} 
                SET status = 'rejected', 
                    reviewed_by = ?, 
                    reviewed_at = NOW(),
                    remarks = ?
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$reviewedBy, $remarks, $id]);
    }
    
    /**
     * Get request by ID
     */
    public function find($id) {
        $sql = "SELECT sr.*, 
                       u.username as reviewed_by_name,
                       req_user.username as requested_by_username,
                       req_user.email as requested_by_email
                FROM {$this->table} sr
                LEFT JOIN users u ON sr.reviewed_by = u.id
                LEFT JOIN users req_user ON sr.requested_by = req_user.id
                WHERE sr.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get request types
     */
    public function getRequestTypes() {
        $sql = "SELECT DISTINCT request_type FROM {$this->table} ORDER BY request_type";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
