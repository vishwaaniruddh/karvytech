<?php
require_once __DIR__ . '/BaseMaster.php';

class City extends BaseMaster {
    protected $table = 'cities';
    
    public function __construct() {
        parent::__construct();
    }
    
    // public function getAllWithRelations($page = 1, $limit = 20, $search = '', $status = '') {
    //     $offset = ($page - 1) * $limit;
        
    //     $whereClause = '';
    //     $params = [];
    //     $conditions = [];
        
    //     // Search functionality
    //     if (!empty($search)) {
    //         $conditions[] = "(ci.name LIKE ? OR s.name LIKE ? OR c.name LIKE ? OR z.name LIKE ?)";
    //         $searchTerm = "%$search%";
    //         $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    //     }
        
    //     // Filter by status
    //     if (!empty($status)) {
    //         $conditions[] = "ci.status = ?";
    //         $params[] = $status;
    //     }
        
    //     if (!empty($conditions)) {
    //         $whereClause = "WHERE " . implode(" AND ", $conditions);
    //     }
        
    //     // Get total count
    //     $countSql = "SELECT COUNT(*) FROM {$this->table} ci 
    //                  LEFT JOIN states s ON ci.state_id = s.id 
    //                  LEFT JOIN countries c ON ci.country_id = c.id 
    //                  LEFT JOIN zones z ON ci.zone_id = z.id 
    //                  $whereClause";
    //     $stmt = $this->db->prepare($countSql);
    //     $stmt->execute($params);
    //     $total = $stmt->fetchColumn();
        
    //     // Get paginated results with relations
    //     $sql = "SELECT ci.*, s.name as state_name, c.name as country_name, z.name as zone_name 
    //             FROM {$this->table} ci 
    //             LEFT JOIN states s ON ci.state_id = s.id 
    //             LEFT JOIN countries c ON ci.country_id = c.id 
    //             LEFT JOIN zones z ON ci.zone_id = z.id 
    //             $whereClause 
    //             ORDER BY ci.name ASC 
    //             LIMIT $limit OFFSET $offset";
    //     $stmt = $this->db->prepare($sql);
    //     $stmt->execute($params);
    //     $records = $stmt->fetchAll();
        
    //     return [
    //         'records' => $records,
    //         'total' => $total,
    //         'page' => $page,
    //         'limit' => $limit,
    //         'pages' => ceil($total / $limit)
    //     ];
    // }
    
    public function getByState($stateId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE state_id = ? AND status = 'active' ORDER BY name");
        $stmt->execute([$stateId]);
        return $stmt->fetchAll();
    }
    
    public function getCountries() {
        $stmt = $this->db->prepare("SELECT * FROM countries WHERE status = 'active' ORDER BY name");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getStates() {
        $stmt = $this->db->prepare("SELECT * FROM states WHERE status = 'active' ORDER BY name");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getStateById($stateId) {
        $stmt = $this->db->prepare("SELECT * FROM states WHERE id = ?");
        $stmt->execute([$stateId]);
        return $stmt->fetch();
    }
    
    public function getAllWithRelations($page = 1, $limit = 20, $search = '', $status = '') {
        $offset = ($page - 1) * $limit;
        
        $whereClause = '';
        $params = [];
        $conditions = [];
        
        // Search functionality
        if (!empty($search)) {
            $conditions[] = "(c.name LIKE ? OR s.name LIKE ? OR co.name LIKE ?)";
            $searchTerm = "%$search%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        }
        
        // Filter by status
        if (!empty($status)) {
            $conditions[] = "c.status = ?";
            $params[] = $status;
        }
        
        if (!empty($conditions)) {
            $whereClause = "WHERE " . implode(" AND ", $conditions);
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) FROM {$this->table} c 
                     LEFT JOIN states s ON c.state_id = s.id 
                     LEFT JOIN countries co ON c.country_id = co.id 
                     LEFT JOIN zones z ON s.zone_id = z.id 
                     $whereClause";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();
        
        // Get paginated results with relations
        $sql = "SELECT c.*, s.name as state_name, co.name as country_name, z.name as zone_name 
                FROM {$this->table} c 
                LEFT JOIN states s ON c.state_id = s.id 
                LEFT JOIN countries co ON c.country_id = co.id 
                LEFT JOIN zones z ON s.zone_id = z.id 
                $whereClause 
                ORDER BY c.name ASC 
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
    
    public function validateCityData($data, $isUpdate = false, $recordId = null) {
        $errors = parent::validateMasterData($data, $isUpdate, $recordId);
        
        // State validation
        if (empty($data['state_id'])) {
            $errors['state_id'] = 'State is required';
        }
        
        // Country validation
        if (empty($data['country_id'])) {
            $errors['country_id'] = 'Country is required';
        }
        
        return $errors;
    }
}
?>