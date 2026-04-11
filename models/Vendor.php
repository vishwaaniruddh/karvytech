<?php
require_once __DIR__ . '/BaseModel.php';

class Vendor extends BaseModel {
    protected $table = 'vendors';
    
    public function __construct() {
        parent::__construct();
    }
    
    public function create($data) {
        // Generate vendor code if not provided
        if (empty($data['vendor_code'])) {
            $data['vendor_code'] = $this->generateVendorCode();
        }
        
        // Hash mobility password if provided
        if (!empty($data['mobility_password'])) {
            $data['mobility_password'] = password_hash($data['mobility_password'], PASSWORD_DEFAULT);
        }
        
        $fields = [
            'vendor_code', 'name', 'company_name', 'email', 'phone', 'address',
            'mobility_id', 'mobility_password', 'contact_person',
            'bank_name', 'account_number', 'ifsc_code', 'gst_number',
            'pan_card_number', 'aadhaar_number', 'msme_number', 'esic_number',
            'pf_number', 'pvc_status', 'experience_letter_path', 'photograph_path'
        ];
        
        $placeholders = [];
        $values = [];
        
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $placeholders[] = "$field = ?";
                $values[] = $data[$field];
            }
        }
        
        $sql = "INSERT INTO {$this->table} SET " . implode(', ', $placeholders);
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute($values)) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    public function update($id, $data) {
        // Hash mobility password if provided and changed
        if (!empty($data['mobility_password'])) {
            $data['mobility_password'] = password_hash($data['mobility_password'], PASSWORD_DEFAULT);
        } else {
            unset($data['mobility_password']); // Don't update if empty
        }
        
        $fields = [
            'vendor_code', 'name', 'company_name', 'email', 'phone', 'address',
            'mobility_id', 'mobility_password', 'contact_person',
            'bank_name', 'account_number', 'ifsc_code', 'gst_number',
            'pan_card_number', 'aadhaar_number', 'msme_number', 'esic_number',
            'pf_number', 'pvc_status', 'experience_letter_path', 'photograph_path'
        ];
        
        $placeholders = [];
        $values = [];
        
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $placeholders[] = "$field = ?";
                $values[] = $data[$field];
            }
        }
        
        if (empty($placeholders)) {
            return false;
        }
        
        $values[] = $id;
        $sql = "UPDATE {$this->table} SET " . implode(', ', $placeholders) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($values);
    }
    
    public function getActiveVendors() {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY name");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getAllVendors($search = '', $status = '') {
        $whereClause = '';
        $params = [];
        $conditions = [];
        
        if (!empty($search)) {
            $conditions[] = "(name LIKE ? OR vendor_code LIKE ? OR email LIKE ? OR phone LIKE ? OR company_name LIKE ?)";
            $searchTerm = "%$search%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }
        
        if (!empty($status)) {
            $conditions[] = "status = ?";
            $params[] = $status;
        }
        
        if (!empty($conditions)) {
            $whereClause = "WHERE " . implode(" AND ", $conditions);
        }
        
        $sql = "SELECT * FROM {$this->table} $whereClause ORDER BY name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function findByVendorCode($vendorCode) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE vendor_code = ?");
        $stmt->execute([$vendorCode]);
        return $stmt->fetch();
    }
    
    public function generateVendorCode() {
        $stmt = $this->db->query("SELECT COUNT(*) + 1 as next_id FROM {$this->table}");
        $result = $stmt->fetch();
        return 'VND' . str_pad($result['next_id'], 4, '0', STR_PAD_LEFT);
    }
    
    public function getAllWithPagination($page = 1, $limit = 20, $search = '', $status = '') {
        $offset = ($page - 1) * $limit;
        
        $whereClause = '';
        $params = [];
        $conditions = [];
        
        if (!empty($search)) {
            $conditions[] = "(name LIKE ? OR vendor_code LIKE ? OR email LIKE ? OR phone LIKE ? OR company_name LIKE ?)";
            $searchTerm = "%$search%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }
        
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
        $sql = "SELECT * FROM {$this->table} $whereClause ORDER BY name LIMIT $limit OFFSET $offset";
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
    
    public function getVendorStats() {
        $stats = [];
        
        // Total vendors
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table} WHERE status = 'active'");
        $stats['total_active'] = $stmt->fetchColumn();
        
        // Vendors with active delegations
        $stmt = $this->db->query("
            SELECT COUNT(DISTINCT v.id) 
            FROM {$this->table} v 
            INNER JOIN site_delegations sd ON v.id = sd.vendor_id 
            WHERE v.status = 'active' AND sd.status = 'active'
        ");
        $stats['with_delegations'] = $stmt->fetchColumn();
        
        // Vendors with complete documentation (check if columns exist first)
        try {
            $stmt = $this->db->query("
                SELECT COUNT(*) FROM {$this->table} 
                WHERE status = 'active' 
                AND gst_number IS NOT NULL 
                AND pan_card_number IS NOT NULL
            ");
            $stats['with_documents'] = $stmt->fetchColumn();
        } catch (PDOException $e) {
            // If columns don't exist yet, set to 0
            $stats['with_documents'] = 0;
        }
        
        return $stats;
    }
    
    
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    
    
    
    public function getVendorDelegations($vendorId, $status = null) {
        $sql = "
            SELECT sd.*, s.site_id, s.location, s.city, s.state, u.username as delegated_by_name
            FROM site_delegations sd
            INNER JOIN sites s ON sd.site_id = s.id
            INNER JOIN users u ON sd.delegated_by = u.id
            WHERE sd.vendor_id = ?
        ";
        
        $params = [$vendorId];
        
        if ($status) {
            $sql .= " AND sd.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY sd.delegation_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function uploadFile($file, $type, $vendorId) {
        $uploadDir = __DIR__ . '/../uploads/vendors/' . $vendorId . '/';
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $allowedTypes = [
            'experience_letter' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'],
            'photograph' => ['jpg', 'jpeg', 'png']
        ];
        
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($fileExtension, $allowedTypes[$type])) {
            throw new Exception("Invalid file type for $type");
        }
        
        $fileName = $type . '_' . time() . '.' . $fileExtension;
        $filePath = $uploadDir . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            return 'uploads/vendors/' . $vendorId . '/' . $fileName;
        }
        
        throw new Exception("Failed to upload $type");
    }
}
?>