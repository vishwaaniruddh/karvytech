<?php
require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/Site.php';

class SiteDelegation extends BaseModel {
    protected $table = 'site_delegations';
    
    public function __construct() {
        parent::__construct();
    }
    
    public function delegateSite($siteId, $vendorId, $delegatedBy, $notes = '') {
        // Check if site is already delegated
        $existing = $this->getActiveDelegation($siteId);
        if ($existing) {
            throw new Exception('Site is already delegated to another vendor');
        }
        $data = [
            'site_id' => $siteId,
            'vendor_id' => $vendorId,
            'delegated_by' => $delegatedBy,
            'notes' => $notes,
            'status' => 'active'
        ];
        
        $delegationId = $this->create($data);
        
        // Update site delegation status
        $siteModel = new Site();
        $siteModel->update($siteId, [
            'is_delegate' => 1,
            'delegated_vendor' => $this->getVendorName($vendorId)
        ]);
        
        return $delegationId;
    }
    
    public function getActiveDelegation($siteId) {
        $stmt = $this->db->prepare("
            SELECT sd.*, 
                   COALESCE(NULLIF(v.company_name, ''), v.name) as vendor_name,
                   v.company_name as vendor_company_name,
                   v.name as vendor_contact_name,
                   u.username as delegated_by_name
            FROM {$this->table} sd
            INNER JOIN vendors v ON sd.vendor_id = v.id
            INNER JOIN users u ON sd.delegated_by = u.id
            WHERE sd.site_id = ? AND sd.status = 'active'
            LIMIT 1
        ");
        $stmt->execute([$siteId]);
        return $stmt->fetch();
    }
    
    public function completeDelegation($delegationId, $completedBy) {
        $delegation = $this->find($delegationId);
        if (!$delegation) {
            throw new Exception('Delegation not found');
        }
        
        // Update delegation status
        $this->update($delegationId, ['status' => 'completed']);
        
        // Update site delegation status
        $siteModel = new Site();
        $siteModel->update($delegation['site_id'], [
            'is_delegate' => 0,
            'delegated_vendor' => null
        ]);
        
        return true;
    }
    
    public function cancelDelegation($delegationId, $cancelledBy) {
        $delegation = $this->find($delegationId);
        if (!$delegation) {
            throw new Exception('Delegation not found');
        }
        
        // Update delegation status
        $this->update($delegationId, ['status' => 'cancelled']);
        
        // Update site delegation status
        $siteModel = new Site();
        $siteModel->update($delegation['site_id'], [
            'is_delegate' => 0,
            'delegated_vendor' => null
        ]);
        
        return true;
    }
    
    public function getDelegationHistory($siteId) {
        $stmt = $this->db->prepare("
            SELECT sd.*, 
                   COALESCE(NULLIF(v.company_name, ''), v.name) as vendor_name,
                   v.company_name as vendor_company_name,
                   v.name as vendor_contact_name,
                   u.username as delegated_by_name
            FROM {$this->table} sd
            INNER JOIN vendors v ON sd.vendor_id = v.id
            INNER JOIN users u ON sd.delegated_by = u.id
            WHERE sd.site_id = ?
            ORDER BY sd.delegation_date DESC
        ");
        $stmt->execute([$siteId]);
        return $stmt->fetchAll();
    }
    
    public function getDelegationStats() {
        $stats = [];
        
        // Total active delegations
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table} WHERE status = 'active'");
        $stats['active'] = $stmt->fetchColumn();
        
        // Completed delegations
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table} WHERE status = 'completed'");
        $stats['completed'] = $stmt->fetchColumn();
        
        // Cancelled delegations
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table} WHERE status = 'cancelled'");
        $stats['cancelled'] = $stmt->fetchColumn();
        
        // Delegations by vendor
        $stmt = $this->db->query("
            SELECT v.name, COUNT(sd.id) as count
            FROM vendors v
            LEFT JOIN {$this->table} sd ON v.id = sd.vendor_id AND sd.status = 'active'
            WHERE v.status = 'active'
            GROUP BY v.id, v.name
            ORDER BY count DESC
        ");
        $stats['by_vendor'] = $stmt->fetchAll();
        
        return $stats;
    }
    
    public function getVendorDelegations($vendorId, $status = null) {
        $sql = "
            SELECT sd.*, s.site_id, s.location, s.city as legacy_city, s.state as legacy_state, s.country as legacy_country, 
                   s.customer as legacy_customer, s.survey_status as site_survey_status, s.survey_submission_date,
                   u.username as delegated_by_name,
                   ct.name as city, st.name as state, co.name as country,
                   cu.name as customer,
                   COALESCE(ss_dynamic.survey_status, ss_legacy.survey_status) as survey_status,
                   COALESCE(ss_dynamic.submitted_date, ss_legacy.submitted_date) as survey_submitted_date,
                   CASE 
                       WHEN ss_dynamic.id IS NOT NULL THEN 'dynamic'
                       ELSE 'legacy'
                   END as survey_type
            FROM {$this->table} sd
            INNER JOIN sites s ON sd.site_id = s.id
            INNER JOIN users u ON sd.delegated_by = u.id
            LEFT JOIN cities ct ON s.city_id = ct.id
            LEFT JOIN states st ON s.state_id = st.id
            LEFT JOIN countries co ON s.country_id = co.id
            LEFT JOIN customers cu ON s.customer_id = cu.id
            LEFT JOIN site_surveys ss_legacy ON s.id = ss_legacy.site_id AND sd.id = ss_legacy.delegation_id
            LEFT JOIN (
                SELECT dr1.site_id, dr1.id, dr1.survey_status, dr1.submitted_date, dr1.delegation_id
                FROM dynamic_survey_responses dr1
                INNER JOIN (
                    SELECT delegation_id, MAX(id) as max_id
                    FROM dynamic_survey_responses
                    GROUP BY delegation_id
                ) dr2 ON dr1.delegation_id = dr2.delegation_id AND dr1.id = dr2.max_id
            ) ss_dynamic ON sd.id = ss_dynamic.delegation_id
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
    
    public function updateStatus($delegationId, $status) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET status = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$status, $delegationId]);
    }
    
    private function getVendorName($vendorId) {
        $stmt = $this->db->prepare("SELECT name FROM vendors WHERE id = ?");
        $stmt->execute([$vendorId]);
        $result = $stmt->fetch();
        return $result ? $result['name'] : null;
    }
    
    public function findDelegationId($siteId) {
        $stmt = $this->db->prepare("SELECT id FROM  {$this->table} WHERE site_id = ?");
        $stmt->execute([$siteId]);
        $result = $stmt->fetch();
        return $result ? $result['id'] : null;
    }
    
    public function findSiteVendorId($siteId) {
        $stmt = $this->db->prepare("SELECT vendor_id FROM  {$this->table} WHERE site_id = ?");
        $stmt->execute([$siteId]);
        $result = $stmt->fetch();
        return $result ? $result['vendor_id'] : null;
    }
}
?>