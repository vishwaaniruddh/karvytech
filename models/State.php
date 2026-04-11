<?php
require_once __DIR__ . '/BaseMaster.php';

class State extends BaseMaster {
    protected $table = 'states';
    
    public function __construct() {
        parent::__construct();
    }
    
    public function getAllWithPagination($page = 1, $limit = 20, $search = '', $status = '', $filters = []) {
        $offset = ($page - 1) * $limit;
        
        $whereClause = '';
        $params = [];
        $conditions = [];
        
        // Search functionality
        if (!empty($search)) {
            $conditions[] = "(s.name LIKE ? OR c.name LIKE ? OR z.name LIKE ?)";
            $searchTerm = "%$search%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        }
        
        // Filter by status
        if (!empty($status)) {
            $conditions[] = "s.status = ?";
            $params[] = $status;
        }
        
        // Filter by country_id
        if (!empty($filters['country_id'])) {
            $conditions[] = "s.country_id = ?";
            $params[] = $filters['country_id'];
        }
        
        if (!empty($conditions)) {
            $whereClause = "WHERE " . implode(" AND ", $conditions);
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) FROM {$this->table} s 
                     LEFT JOIN countries c ON s.country_id = c.id 
                     LEFT JOIN zones z ON s.zone_id = z.id 
                     $whereClause";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();
        
        // Get paginated results with relations
        $sql = "SELECT s.*, c.name as country_name, z.name as zone_name 
                FROM {$this->table} s 
                LEFT JOIN countries c ON s.country_id = c.id 
                LEFT JOIN zones z ON s.zone_id = z.id 
                $whereClause 
                ORDER BY s.name ASC 
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
    
    public function getAllWithRelations($page = 1, $limit = 20, $search = '', $status = '') {
        return $this->getAllWithPagination($page, $limit, $search, $status);
    }
    
    public function getByCountry($countryId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE country_id = ? AND status = 'active' ORDER BY name");
        $stmt->execute([$countryId]);
        return $stmt->fetchAll();
    }
    
    public function getCountries() {
        $stmt = $this->db->prepare("SELECT * FROM countries WHERE status = 'active' ORDER BY name");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function validateStateData($data, $isUpdate = false, $recordId = null) {
        $errors = parent::validateMasterData($data, $isUpdate, $recordId);
        
        // Country validation
        if (empty($data['country_id'])) {
            $errors['country_id'] = 'Country is required';
        }
        
        return $errors;
    }
}
?>