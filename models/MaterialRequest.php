<?php
require_once __DIR__ . '/../config/database.php';

class MaterialRequest {
    private $db;
    private $table = 'material_requests';
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (
            site_id, vendor_id, survey_id, request_date, required_date, 
            request_notes, items, status, created_date
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['site_id'],
            $data['vendor_id'],
            $data['survey_id'],
            $data['request_date'],
            $data['required_date'],
            $data['request_notes'],
            $data['items'],
            $data['status'],
            $data['created_date']
        ]);
        
        return $result ? $this->db->lastInsertId() : false;
    }
    
    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function findWithDetails($id) {
        $sql = "SELECT mr.*, 
                       s.site_id as site_code, s.location, s.location as site_location,
                       COALESCE(NULLIF(s.customer, ''), s.branch, 'Internal Project') as site_name,
                       COALESCE(NULLIF(s.contact_person_name, ''), 'N/A') as site_contact,
                       COALESCE(NULLIF(s.contact_person_number, ''), 'N/A') as site_phone,
                       COALESCE(v.company_name, 'In-house Admin') as vendor_company_name,
                       COALESCE(v.name, 'Administrator') as vendor_name,
                       COALESCE(v.name, 'Administrator') as vendor_contact,
                       COALESCE(v.phone, 'Internal') as vendor_phone,
                       COALESCE(NULLIF(dv.company_name, ''), NULLIF(s.delegated_vendor, ''), 'Direct Operations') as delegated_vendor_name,
                       COALESCE(dsr.survey_status, ss.survey_status, s.survey_status, 'No Survey') as unified_survey_status,
                       COALESCE(dsr.submitted_date, ss.submitted_date, s.survey_submission_date) as unified_survey_date
                FROM {$this->table} mr
                LEFT JOIN sites s ON mr.site_id = s.id
                LEFT JOIN vendors v ON mr.vendor_id = v.id
                LEFT JOIN site_delegations sd ON s.id = sd.site_id AND sd.status = 'active'
                LEFT JOIN vendors dv ON sd.vendor_id = dv.id
                LEFT JOIN site_surveys ss ON s.id = ss.site_id
                LEFT JOIN (
                    SELECT dr1.site_id, dr1.survey_status, dr1.submitted_date
                    FROM dynamic_survey_responses dr1
                    INNER JOIN (
                        SELECT site_id, MAX(id) as max_id
                        FROM dynamic_survey_responses
                        GROUP BY site_id
                    ) dr2 ON dr1.id = dr2.max_id
                ) dsr ON s.id = dsr.site_id
                WHERE mr.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function findByVendor($vendorId, $limit = 50, $offset = 0) {
        $sql = "SELECT mr.*, 
                       s.site_id as site_code, s.location,
                       ss.survey_status
                FROM {$this->table} mr
                LEFT JOIN sites s ON mr.site_id = s.id
                LEFT JOIN site_surveys ss ON mr.survey_id = ss.id
                WHERE mr.vendor_id = ?
                ORDER BY mr.created_date DESC
                LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$vendorId, $limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function findBySite($siteId) {
        $sql = "SELECT mr.*, 
                       s.site_id as site_code, s.location,
                       v.name as vendor_name
                FROM {$this->table} mr
                LEFT JOIN sites s ON mr.site_id = s.id
                LEFT JOIN vendors v ON mr.vendor_id = v.id
                WHERE mr.site_id = ?
                ORDER BY mr.created_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$siteId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function update($id, $data) {
        $fields = [];
        $values = [];
        
        foreach ($data as $key => $value) {
            if ($key !== 'id') {
                $fields[] = "$key = ?";
                $values[] = $value;
            }
        }
        
        $values[] = $id;
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }
    
    public function updateStatus($id, $status, $processedBy = null, $processedDate = null) {
        $sql = "UPDATE {$this->table} SET status = ?, processed_by = ?, processed_date = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$status, $processedBy, $processedDate, $id]);
    }
    
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    public function getAllWithPagination($page = 1, $limit = 20, $filters = []) {
        $offset = ($page - 1) * $limit;
        
        $whereClause = '';
        $params = [];
        $conditions = [];
        
        // Filter by status
        if (!empty($filters['status'])) {
            $conditions[] = "mr.status = ?";
            $params[] = $filters['status'];
        }
        
        // Filter by vendor
        if (!empty($filters['vendor_id'])) {
            $conditions[] = "mr.vendor_id = ?";
            $params[] = $filters['vendor_id'];
        }
        
        // Filter by site
        if (!empty($filters['site_id'])) {
            $conditions[] = "mr.site_id = ?";
            $params[] = $filters['site_id'];
        }
        
        if (!empty($conditions)) {
            $whereClause = "WHERE " . implode(" AND ", $conditions);
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) FROM {$this->table} mr $whereClause";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();
        
        // Get paginated results
        $sql = "SELECT mr.*, 
                       s.site_id as site_code, s.location,
                       v.name as vendor_name,
                       v.company_name as vendor_company_name,
                       ss.survey_status
                FROM {$this->table} mr
                LEFT JOIN sites s ON mr.site_id = s.id
                LEFT JOIN vendors v ON mr.vendor_id = v.id
                LEFT JOIN site_surveys ss ON mr.survey_id = ss.id
                $whereClause
                ORDER BY mr.created_date DESC
                LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'requests' => $requests,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }
    
    public function getStats() {
        $stats = [
            'total' => 0,
            'pending' => 0,
            'approved' => 0,
            'dispatched' => 0,
            'completed' => 0,
            'rejected' => 0
        ];
        
        // Total requests
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table}");
        $stats['total'] = $stmt->fetchColumn();
        
        // Status breakdown
        $stmt = $this->db->query("SELECT status, COUNT(*) as count FROM {$this->table} GROUP BY status");
        $statusStats = $stmt->fetchAll();
        foreach ($statusStats as $stat) {
            if (isset($stats[$stat['status']])) {
                $stats[$stat['status']] = $stat['count'];
            }
        }
        
        return $stats;
    }
    
    public function getDispatchedRequestsForVendor($vendorId) {
        $sql = "SELECT mr.*, 
                       s.site_id as site_code, s.location,
                       v.name as vendor_name, v.contact_person, v.phone, v.email,
                       id.dispatch_number, id.dispatch_date, id.courier_name, id.tracking_number,
                       id.dispatch_status, id.expected_delivery_date, id.delivery_remarks
                FROM {$this->table} mr
                LEFT JOIN sites s ON mr.site_id = s.id
                LEFT JOIN vendors v ON mr.vendor_id = v.id
                LEFT JOIN inventory_dispatches id ON mr.id = id.material_request_id
                WHERE mr.vendor_id = ? AND mr.status = 'dispatched'
                ORDER BY mr.created_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$vendorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function createRequest($data) {
        // Create items array for single material request
        $items = [[
            'material_name' => $data['material_name'],
            'boq_item_id' => $data['boq_item_id'] ?? null,
            'item_code' => $data['item_code'] ?? null,
            'unit' => $data['unit'] ?? 'units',
            'quantity' => $data['quantity_requested'],
            'current_stock' => $data['current_stock'] ?? 0,
            'priority' => $data['priority'] ?? 'medium',
            'reason' => $data['reason']
        ]];
        
        $sql = "INSERT INTO {$this->table} (
            site_id, vendor_id, survey_id, request_date, required_date,
            request_notes, items, status, created_date
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['site_id'],
            $data['vendor_id'],
            $data['installation_id'] ?? null, // Using installation_id as survey_id for now
            $data['request_date'],
            date('Y-m-d', strtotime('+3 days')), // Default required date
            $data['reason'],
            json_encode($items),
            $data['status'] ?? 'pending',
            date('Y-m-d H:i:s')
        ]);
        
        return $result ? $this->db->lastInsertId() : false;
    }
    
    public function getVendorRequests($vendorId) {
        $sql = "SELECT mr.*, 
                       s.site_id, s.location
                FROM {$this->table} mr
                LEFT JOIN sites s ON mr.site_id = s.id
                WHERE mr.vendor_id = ?
                ORDER BY mr.created_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$vendorId]);
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Parse items JSON for each request and calculate totals
        foreach ($requests as &$request) {
            $request['total_items'] = 0;
            $request['total_quantity'] = 0;
            
            if ($request['items']) {
                $items_data = json_decode($request['items'], true);
                if ($items_data && is_array($items_data)) {
                    $request['items_data'] = $items_data;
                    $request['total_items'] = count($items_data);
                    
                    // Calculate total quantity
                    $total_qty = 0;
                    foreach ($items_data as $item) {
                        if (isset($item['quantity']) && is_numeric($item['quantity'])) {
                            $total_qty += (int)$item['quantity'];
                        }
                    }
                    $request['total_quantity'] = $total_qty;
                } else {
                    $request['items_data'] = [];
                }
            } else {
                $request['items_data'] = [];
            }
        }
        
        return $requests;
    }
    
    public function getAllWithDetails() {
        $sql = "SELECT mr.*, 
                       s.site_id, s.location,
                       s.po_number, s.site_ticket_id,
                       ct.name as city_name, st.name as state_name,
                       v.name as vendor_name, v.company_name as vendor_company_name,
                       id.dispatch_status, id.dispatch_date, id.courier_name, 
                       id.tracking_number, id.expected_delivery_date, id.actual_delivery_date
                FROM {$this->table} mr
                INNER JOIN sites s ON mr.site_id = s.id
                LEFT JOIN cities ct ON s.city_id = ct.id
                LEFT JOIN states st ON s.state_id = st.id
                LEFT JOIN vendors v ON mr.vendor_id = v.id
                LEFT JOIN inventory_dispatches id ON mr.id = id.material_request_id
                ORDER BY mr.created_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>