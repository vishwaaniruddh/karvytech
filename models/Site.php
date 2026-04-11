<?php
require_once __DIR__ . '/BaseModel.php';

class Site extends BaseModel {
    protected $table = 'sites';
    
    public function __construct() {
        parent::__construct();
    }
    
    public function getAllWithPagination($page = 1, $limit = 20, $search = '', $filters = []) {
        $offset = ($page - 1) * $limit;
        
        $whereClause = '';
        $params = [];
        $conditions = [];
        
        // Exclude soft-deleted records
        $conditions[] = "s.deleted_at IS NULL";
        
        // Search functionality
        if (!empty($search)) {
            $conditions[] = "(s.site_id LIKE ? OR s.store_id LIKE ? OR s.location LIKE ? OR ct.name LIKE ? OR cu.name LIKE ? OR s.contact_person_name LIKE ?)";
            $searchTerm = "%$search%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }
        
        // Filter by city
        if (!empty($filters['city'])) {
            $conditions[] = "ct.name = ?";
            $params[] = $filters['city'];
        }
        
        // Filter by state
        if (!empty($filters['state'])) {
            $conditions[] = "st.name = ?";
            $params[] = $filters['state'];
        }
        
        // Filter by activity status
        if (!empty($filters['activity_status'])) {
            $conditions[] = "s.activity_status = ?";
            $params[] = $filters['activity_status'];
        }
        
        // Filter by vendor
        if (!empty($filters['vendor'])) {
            $conditions[] = "s.vendor = ?";
            $params[] = $filters['vendor'];
        }
        
        // Filter by survey status
        if (!empty($filters['survey_status'])) {
            switch ($filters['survey_status']) {
                case 'pending':
                    $conditions[] = "(ss.survey_status IS NULL OR ss.survey_status = '')";
                    break;
                case 'submitted':
                    $conditions[] = "ss.survey_status = 'completed'";
                    break;
                case 'approved':
                    $conditions[] = "ss.survey_status = 'approved'";
                    break;
                case 'rejected':
                    $conditions[] = "ss.survey_status = 'rejected'";
                    break;
            }
        }
        
        if (!empty($conditions)) {
            $whereClause = "WHERE " . implode(" AND ", $conditions);
        }
        
        // Get total count
        $countSql = "SELECT COUNT(DISTINCT s.id) FROM {$this->table} s 
                     LEFT JOIN cities ct ON s.city_id = ct.id 
                     LEFT JOIN states st ON s.state_id = st.id 
                     LEFT JOIN countries co ON s.country_id = co.id 
                     LEFT JOIN customers cu ON s.customer_id = cu.id 
                     LEFT JOIN site_delegations sd ON s.id = sd.site_id AND sd.status = 'active'
                     LEFT JOIN vendors v ON sd.vendor_id = v.id
                     LEFT JOIN (
                         SELECT ss1.site_id, ss1.id, ss1.survey_status, ss1.submitted_date
                         FROM site_surveys ss1
                         INNER JOIN (
                             SELECT site_id, MAX(id) as max_id
                             FROM site_surveys
                             GROUP BY site_id
                         ) ss2 ON ss1.site_id = ss2.site_id AND ss1.id = ss2.max_id
                     ) ss ON s.id = ss.site_id
                     LEFT JOIN installation_delegations id ON s.id = id.site_id
                     $whereClause";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();
        
        // Get paginated results with relationships
        $sql = "SELECT s.*, 
                       ct.name as city, st.name as state, co.name as country,
                       cu.name as customer,
                        banks.name as bank_name,
                       sd.id as delegation_id, v.name as delegated_vendor_name,
                       sd.status as delegation_status, sd.delegation_date,
                       COALESCE(ss_dynamic.id, ss_legacy.id) as survey_id, 
                       COALESCE(ss_dynamic.survey_status, ss_legacy.survey_status) as actual_survey_status,
                       COALESCE(ss_dynamic.submitted_date, ss_legacy.submitted_date) as survey_submitted_date,
                       ss_legacy.vendor_id as survey_vendor_id,
                       sv.name as survey_vendor_name,
                       CASE 
                           WHEN ss_dynamic.id IS NOT NULL THEN 'dynamic'
                           ELSE 'legacy'
                       END as survey_type,
                       id.id as installation_id, id.status as installation_delegation_status,
                       CASE 
                           WHEN ss_dynamic.survey_status IN ('completed', 'approved', 'submitted') THEN 1
                           WHEN ss_legacy.survey_status IN ('completed', 'approved') THEN 1
                           ELSE 0
                       END as has_survey_submitted
                FROM {$this->table} s 
                LEFT JOIN cities ct ON s.city_id = ct.id 
                LEFT JOIN states st ON s.state_id = st.id 
                LEFT JOIN countries co ON s.country_id = co.id 
                LEFT JOIN customers cu ON s.customer_id = cu.id 
                LEFT JOIN site_delegations sd ON s.id = sd.site_id AND sd.status = 'active'
                LEFT JOIN vendors v ON sd.vendor_id = v.id
                LEFT JOIN banks ON s.bank_id=banks.id
                LEFT JOIN (
                    SELECT ss1.site_id, ss1.id, ss1.survey_status, ss1.submitted_date, ss1.vendor_id
                    FROM site_surveys ss1
                    INNER JOIN (
                        SELECT site_id, MAX(id) as max_id
                        FROM site_surveys
                        GROUP BY site_id
                    ) ss2 ON ss1.site_id = ss2.site_id AND ss1.id = ss2.max_id
                ) ss_legacy ON s.id = ss_legacy.site_id
                LEFT JOIN (
                    SELECT dr1.site_id, dr1.id, dr1.survey_status, dr1.submitted_date
                    FROM dynamic_survey_responses dr1
                    INNER JOIN (
                        SELECT site_id, MAX(id) as max_id
                        FROM dynamic_survey_responses
                        GROUP BY site_id
                    ) dr2 ON dr1.site_id = dr2.site_id AND dr1.id = dr2.max_id
                ) ss_dynamic ON s.id = ss_dynamic.site_id
                LEFT JOIN vendors sv ON ss_legacy.vendor_id = sv.id
                LEFT JOIN installation_delegations id ON s.id = id.site_id
                $whereClause 
                ORDER BY s.created_at DESC 
                LIMIT $limit OFFSET $offset";

                
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $sites = $stmt->fetchAll();
        
        return [
            'sites' => $sites,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }
    
    public function findBySiteId($siteId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE site_id = ?");
        $stmt->execute([$siteId]);
        return $stmt->fetch();
    }
    
    public function findByStoreId($storeId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE store_id = ?");
        $stmt->execute([$storeId]);
        return $stmt->fetch();
    }
    
    public function findWithRelations($id) {
        $sql = "SELECT s.*, 
                       ct.name as city_name, st.name as state_name, co.name as country_name,
                       cu.name as customer_name,
                       b.name as bank_name,
                       sd.id as delegation_id,
                       v.name as vendor_name,
                       sd.status as delegation_status,
                       sd.delegation_date
                FROM {$this->table} s 
                LEFT JOIN cities ct ON s.city_id = ct.id 
                LEFT JOIN states st ON s.state_id = st.id 
                LEFT JOIN countries co ON s.country_id = co.id 
                LEFT JOIN customers cu ON s.customer_id = cu.id 
                LEFT JOIN banks b ON s.bank_id = b.id
                LEFT JOIN site_delegations sd ON s.id = sd.site_id AND sd.status = 'active'
                LEFT JOIN vendors v ON sd.vendor_id = v.id
                WHERE s.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function getUniqueValues($column) {
        $stmt = $this->db->prepare("SELECT DISTINCT $column FROM {$this->table} WHERE $column IS NOT NULL AND $column != '' ORDER BY $column");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    public function validateSiteData($data, $isUpdate = false, $siteId = null) {
        $errors = [];
        
        // Site ID validation
        if (empty($data['site_id'])) {
            $errors['site_id'] = 'Site ID is required';
        } else {
            // Check if site_id already exists
            $existingSite = $this->findBySiteId($data['site_id']);
            if ($existingSite && (!$isUpdate || $existingSite['id'] != $siteId)) {
                $errors['site_id'] = 'Site ID already exists';
            }
        }
        
        // Store ID validation (optional but unique if provided)
        if (!empty($data['store_id'])) {
            $existingSite = $this->findByStoreId($data['store_id']);
            if ($existingSite && (!$isUpdate || $existingSite['id'] != $siteId)) {
                $errors['store_id'] = 'Store ID already exists';
            }
        }
        
        // Location validation
        if (empty($data['location'])) {
            $errors['location'] = 'Location is required';
        }
        
        // Location foreign key validation
        if (empty($data['country_id']) || $data['country_id'] <= 0) {
            $errors['country_id'] = 'Country is required';
        }
        
        if (empty($data['state_id']) || $data['state_id'] <= 0) {
            $errors['state_id'] = 'State is required';
        }
        
        if (empty($data['city_id']) || $data['city_id'] <= 0) {
            $errors['city_id'] = 'City is required';
        }
        
        // PO Date validation
        if (!empty($data['po_date']) && !$this->isValidDate($data['po_date'])) {
            $errors['po_date'] = 'Invalid PO date format';
        }
        
        // Survey submission date validation
        if (!empty($data['survey_submission_date']) && !$this->isValidDateTime($data['survey_submission_date'])) {
            $errors['survey_submission_date'] = 'Invalid survey submission date format';
        }
        
        // Installation date validation
        if (!empty($data['installation_date']) && !$this->isValidDateTime($data['installation_date'])) {
            $errors['installation_date'] = 'Invalid installation date format';
        }
        
        return $errors;
    }
    
    private function isValidDate($date) {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
    
    private function isValidDateTime($datetime) {
        $d = DateTime::createFromFormat('Y-m-d H:i:s', $datetime);
        return $d && $d->format('Y-m-d H:i:s') === $datetime;
    }
    
    public function getSiteStats() {
        $stats = [];
        
        // Total sites
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table}");
        $stats['total'] = $stmt->fetchColumn();
        
        // Sites by activity status
        $stmt = $this->db->query("SELECT activity_status, COUNT(*) as count FROM {$this->table} WHERE activity_status IS NOT NULL GROUP BY activity_status");
        $statusStats = $stmt->fetchAll();
        foreach ($statusStats as $status) {
            $stats['by_status'][$status['activity_status']] = $status['count'];
        }
        
        // Survey status
        $stmt = $this->db->query("SELECT survey_status, COUNT(*) as count FROM {$this->table} GROUP BY survey_status");
        $surveyStats = $stmt->fetchAll();
        foreach ($surveyStats as $survey) {
            $stats['survey'][($survey['survey_status'] ? 'completed' : 'pending')] = $survey['count'];
        }
        
        // Installation status
        $stmt = $this->db->query("SELECT installation_status, COUNT(*) as count FROM {$this->table} GROUP BY installation_status");
        $installStats = $stmt->fetchAll();
        foreach ($installStats as $install) {
            $stats['installation'][($install['installation_status'] ? 'completed' : 'pending')] = $install['count'];
        }
        
        // Sites by state
        $stmt = $this->db->query("SELECT state, COUNT(*) as count FROM {$this->table} WHERE state IS NOT NULL GROUP BY state ORDER BY count DESC LIMIT 10");
        $stateStats = $stmt->fetchAll();
        foreach ($stateStats as $state) {
            $stats['by_state'][$state['state']] = $state['count'];
        }
        
        // Recent sites (last 30 days)
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table} WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stats['recent'] = $stmt->fetchColumn();
        
        return $stats;
    }
    
    public function getOverallStatistics($search = '', $filters = []) {
        $whereClause = '';
        $params = [];
        $conditions = [];
        
        // Search functionality
        if (!empty($search)) {
            $conditions[] = "(s.site_id LIKE ? OR s.store_id LIKE ? OR s.location LIKE ? OR ct.name LIKE ? OR cu.name LIKE ? OR s.contact_person_name LIKE ?)";
            $searchTerm = "%$search%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }
        
        // Filter by city
        if (!empty($filters['city'])) {
            $conditions[] = "ct.name = ?";
            $params[] = $filters['city'];
        }
        
        // Filter by state
        if (!empty($filters['state'])) {
            $conditions[] = "st.name = ?";
            $params[] = $filters['state'];
        }
        
        // Filter by activity status
        if (!empty($filters['activity_status'])) {
            $conditions[] = "s.activity_status = ?";
            $params[] = $filters['activity_status'];
        }
        
        // Filter by vendor
        if (!empty($filters['vendor'])) {
            $conditions[] = "s.vendor = ?";
            $params[] = $filters['vendor'];
        }
        
        // Filter by survey status
        if (!empty($filters['survey_status'])) {
            switch ($filters['survey_status']) {
                case 'pending':
                    $conditions[] = "(ss.survey_status IS NULL OR ss.survey_status = '')";
                    break;
                case 'submitted':
                    $conditions[] = "ss.survey_status = 'completed'";
                    break;
                case 'approved':
                    $conditions[] = "ss.survey_status = 'approved'";
                    break;
                case 'rejected':
                    $conditions[] = "ss.survey_status = 'rejected'";
                    break;
            }
        }
        
        if (!empty($conditions)) {
            $whereClause = "WHERE " . implode(" AND ", $conditions);
        }
        
        $sql = "SELECT 
                    COUNT(DISTINCT s.id) as total_sites,
                    SUM(CASE WHEN sd.status = 'active' THEN 1 ELSE 0 END) as delegation_active,
                    SUM(CASE WHEN (sd.status IS NULL OR sd.status != 'active') THEN 1 ELSE 0 END) as delegation_pending,
                    SUM(CASE WHEN COALESCE(ss_dynamic.survey_status, ss_legacy.survey_status) = 'approved' THEN 1 ELSE 0 END) as survey_approved,
                    SUM(CASE WHEN (ss_legacy.survey_status IS NULL OR ss_legacy.survey_status = '') AND (ss_dynamic.survey_status IS NULL OR ss_dynamic.survey_status = '') THEN 1 ELSE 0 END) as survey_pending,
                    SUM(CASE WHEN COALESCE(ss_dynamic.survey_status, ss_legacy.survey_status) = 'rejected' THEN 1 ELSE 0 END) as survey_rejected,
                    SUM(CASE WHEN s.installation_status = 1 THEN 1 ELSE 0 END) as installation_done,
                    SUM(CASE WHEN s.installation_status = 0 OR s.installation_status IS NULL THEN 1 ELSE 0 END) as installation_pending
                FROM {$this->table} s 
                LEFT JOIN cities ct ON s.city_id = ct.id 
                LEFT JOIN states st ON s.state_id = st.id 
                LEFT JOIN countries co ON s.country_id = co.id 
                LEFT JOIN customers cu ON s.customer_id = cu.id 
                LEFT JOIN site_delegations sd ON s.id = sd.site_id AND sd.status = 'active'
                LEFT JOIN (
                    SELECT ss1.site_id, ss1.id, ss1.survey_status
                    FROM site_surveys ss1
                    INNER JOIN (
                        SELECT site_id, MAX(id) as max_id
                        FROM site_surveys
                        GROUP BY site_id
                    ) ss2 ON ss1.site_id = ss2.site_id AND ss1.id = ss2.max_id
                ) ss_legacy ON s.id = ss_legacy.site_id
                LEFT JOIN (
                    SELECT dr1.site_id, dr1.id, dr1.survey_status
                    FROM dynamic_survey_responses dr1
                    INNER JOIN (
                        SELECT site_id, MAX(id) as max_id
                        FROM dynamic_survey_responses
                        GROUP BY site_id
                    ) dr2 ON dr1.site_id = dr2.site_id AND dr1.id = dr2.max_id
                ) ss_dynamic ON s.id = ss_dynamic.site_id
                $whereClause";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get customer-wise count
        $customerSql = "SELECT 
                            COALESCE(cu.name, 'No Customer') as customer_name,
                            COUNT(DISTINCT s.id) as site_count
                        FROM {$this->table} s 
                        LEFT JOIN cities ct ON s.city_id = ct.id 
                        LEFT JOIN states st ON s.state_id = st.id 
                        LEFT JOIN customers cu ON s.customer_id = cu.id 
                        LEFT JOIN site_delegations sd ON s.id = sd.site_id AND sd.status = 'active'
                        LEFT JOIN (
                            SELECT ss1.site_id, ss1.id, ss1.survey_status
                            FROM site_surveys ss1
                            INNER JOIN (
                                SELECT site_id, MAX(id) as max_id
                                FROM site_surveys
                                GROUP BY site_id
                            ) ss2 ON ss1.site_id = ss2.site_id AND ss1.id = ss2.max_id
                        ) ss ON s.id = ss.site_id
                        $whereClause
                        GROUP BY cu.id, cu.name
                        ORDER BY site_count DESC";
        
        $stmt = $this->db->prepare($customerSql);
        $stmt->execute($params);
        $stats['customer_counts'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $stats;
    }
    
    public function updateSurveyStatus($id, $status, $submissionDate = null) {
        $data = ['survey_status' => $status ? 1 : 0];
        if ($submissionDate) {
            $data['survey_submission_date'] = $submissionDate;
        }
        return $this->update($id, $data);
    }
    
    public function updateInstallationStatus($id, $status, $installationDate = null) {
        $data = ['installation_status' => $status ? 1 : 0];
        if ($installationDate) {
            $data['installation_date'] = $installationDate;
        }
        return $this->update($id, $data);
    }
    
    public function delegateSite($id, $delegatedVendor) {
        return $this->update($id, [
            'is_delegate' => 1,
            'delegated_vendor' => $delegatedVendor
        ]);
    }
    
    public function undelegateSite($id) {
        return $this->update($id, [
            'is_delegate' => 0,
            'delegated_vendor' => null
        ]);
    }
    
    public function getAllSites() {
        $sql = "SELECT s.*, 
                       ct.name as city_name, st.name as state_name
                FROM {$this->table} s 
                LEFT JOIN cities ct ON s.city_id = ct.id 
                LEFT JOIN states st ON s.state_id = st.id 
                ORDER BY s.site_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    
         // 🔑 UNIQUE SITE TICKET GENERATOR
    public function generateSiteTicketId(): string
{
    $year = date('Y');

    $row = $this->db->query(
        "SELECT site_ticket_id
         FROM sites
         WHERE site_ticket_id LIKE 'KARVY-$year-%'
         ORDER BY id DESC
         LIMIT 1"
    )->fetch();

    if ($row && !empty($row['site_ticket_id'])) {
        $last = (int) substr($row['site_ticket_id'], -6);
        $next = $last + 1;
    } else {
        $next = 1;
    }

    return 'KARVY-' . $year . '-' . str_pad($next, 6, '0', STR_PAD_LEFT);
}

public function getLastTicketSequence(): int
{
    $year = date('Y');

    $row = $this->db->query(
        "SELECT site_ticket_id
         FROM sites
         WHERE site_ticket_id LIKE 'KARVY-$year-%'
         ORDER BY id DESC
         LIMIT 1"
    )->fetch();

    if ($row && !empty($row['site_ticket_id'])) {
        return (int) substr($row['site_ticket_id'], -6);
    }

    return 0;
}

    /**
     * Override delete method to cascade delete related records
     */
    /**
     * Override delete method to implement soft delete
     */
    public function delete($id) {
        try {
            // Get current user for audit
            $currentUser = Auth::getCurrentUser();
            $userId = $currentUser ? $currentUser['id'] : null;
            
            // Soft delete - just mark as deleted
            $stmt = $this->db->prepare("
                UPDATE {$this->table} 
                SET deleted_at = NOW(), 
                    deleted_by = ? 
                WHERE id = ?
            ");
            
            return $stmt->execute([$userId, $id]);
            
        } catch (Exception $e) {
            error_log("Site soft deletion failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Permanently delete a site (hard delete) with backup
     * @param int $id Site ID
     * @param int $requestId Superadmin request ID for backup reference
     * @param int $deletedBy User ID who approved deletion
     */
    public function permanentDelete($id, $requestId = null, $deletedBy = null) {
        try {
            $this->db->beginTransaction();
            
            // Create backups if requestId is provided
            if ($requestId && $deletedBy) {
                require_once __DIR__ . '/DeletedDataBackup.php';
                $backupModel = new DeletedDataBackup();
                
                // Backup the site record
                $site = $this->find($id);
                if ($site) {
                    $backupModel->createBackup(
                        $requestId,
                        'sites',
                        $id,
                        $site,
                        $deletedBy,
                        'Site record backup before permanent deletion'
                    );
                }
                
                // Backup installation_delegations
                $stmt = $this->db->prepare("SELECT * FROM installation_delegations WHERE site_id = ?");
                $stmt->execute([$id]);
                $installations = $stmt->fetchAll();
                
                foreach ($installations as $installation) {
                    $backupModel->createBackup(
                        $requestId,
                        'installation_delegations',
                        $installation['id'],
                        $installation,
                        $deletedBy,
                        'Installation delegation backup'
                    );
                    
                    // Backup installation_materials for this installation
                    $stmt = $this->db->prepare("SELECT * FROM installation_materials WHERE installation_id = ?");
                    $stmt->execute([$installation['id']]);
                    $materials = $stmt->fetchAll();
                    
                    foreach ($materials as $material) {
                        $backupModel->createBackup(
                            $requestId,
                            'installation_materials',
                            $material['id'],
                            $material,
                            $deletedBy,
                            'Installation material backup'
                        );
                    }
                }
                
                // Backup site_surveys
                $stmt = $this->db->prepare("SELECT * FROM site_surveys WHERE site_id = ?");
                $stmt->execute([$id]);
                $surveys = $stmt->fetchAll();
                
                foreach ($surveys as $survey) {
                    $backupModel->createBackup(
                        $requestId,
                        'site_surveys',
                        $survey['id'],
                        $survey,
                        $deletedBy,
                        'Site survey backup'
                    );
                }
                
                // Backup site_delegations
                $stmt = $this->db->prepare("SELECT * FROM site_delegations WHERE site_id = ?");
                $stmt->execute([$id]);
                $delegations = $stmt->fetchAll();
                
                foreach ($delegations as $delegation) {
                    $backupModel->createBackup(
                        $requestId,
                        'site_delegations',
                        $delegation['id'],
                        $delegation,
                        $deletedBy,
                        'Site delegation backup'
                    );
                }
                
                // Backup dynamic_survey_responses
                $stmt = $this->db->prepare("SELECT * FROM dynamic_survey_responses WHERE site_id = ?");
                $stmt->execute([$id]);
                $dynamicSurveys = $stmt->fetchAll();
                
                foreach ($dynamicSurveys as $dynamicSurvey) {
                    $backupModel->createBackup(
                        $requestId,
                        'dynamic_survey_responses',
                        $dynamicSurvey['id'],
                        $dynamicSurvey,
                        $deletedBy,
                        'Dynamic survey response backup'
                    );
                }
                
                // Backup material_requests
                $stmt = $this->db->prepare("SELECT * FROM material_requests WHERE site_id = ?");
                $stmt->execute([$id]);
                $materialRequests = $stmt->fetchAll();
                
                foreach ($materialRequests as $materialRequest) {
                    $backupModel->createBackup(
                        $requestId,
                        'material_requests',
                        $materialRequest['id'],
                        $materialRequest,
                        $deletedBy,
                        'Material request backup'
                    );
                }
            }
            
            // Now proceed with deletion
            // Get installation IDs for this site to delete installation_materials
            $stmt = $this->db->prepare("SELECT id FROM installation_delegations WHERE site_id = ?");
            $stmt->execute([$id]);
            $installationIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Delete installation_materials for all installations of this site
            if (!empty($installationIds)) {
                $placeholders = implode(',', array_fill(0, count($installationIds), '?'));
                $stmt = $this->db->prepare("DELETE FROM installation_materials WHERE installation_id IN ($placeholders)");
                $stmt->execute($installationIds);
            }
            
            // Delete installation_delegations for this site
            $stmt = $this->db->prepare("DELETE FROM installation_delegations WHERE site_id = ?");
            $stmt->execute([$id]);
            
            // Delete site_surveys for this site
            $stmt = $this->db->prepare("DELETE FROM site_surveys WHERE site_id = ?");
            $stmt->execute([$id]);
            
            // Delete site_delegations for this site
            $stmt = $this->db->prepare("DELETE FROM site_delegations WHERE site_id = ?");
            $stmt->execute([$id]);
            
            // Delete dynamic_survey_responses for this site
            $stmt = $this->db->prepare("DELETE FROM dynamic_survey_responses WHERE site_id = ?");
            $stmt->execute([$id]);
            
            // Delete material_requests for this site
            $stmt = $this->db->prepare("DELETE FROM material_requests WHERE site_id = ?");
            $stmt->execute([$id]);
            
            // Finally delete the site itself (hard delete)
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
            $success = $stmt->execute([$id]);
            
            if ($success) {
                $this->db->commit();
                return true;
            } else {
                $this->db->rollBack();
                return false;
            }
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Site permanent deletion failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Restore a soft-deleted site
     */
    public function restore($id) {
        try {
            $stmt = $this->db->prepare("
                UPDATE {$this->table} 
                SET deleted_at = NULL, 
                    deleted_by = NULL 
                WHERE id = ?
            ");
            
            return $stmt->execute([$id]);
            
        } catch (Exception $e) {
            error_log("Site restoration failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get all soft-deleted sites
     */
    public function getTrashed($page = 1, $limit = 20, $search = '') {
        $offset = ($page - 1) * $limit;
        
        $whereClause = 'WHERE s.deleted_at IS NOT NULL';
        $params = [];
        
        if (!empty($search)) {
            $whereClause .= " AND (s.site_id LIKE ? OR s.store_id LIKE ? OR s.city LIKE ? OR s.state LIKE ?)";
            $searchParam = "%$search%";
            $params = [$searchParam, $searchParam, $searchParam, $searchParam];
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) FROM {$this->table} s $whereClause";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();
        
        // Get paginated results
        $sql = "SELECT s.*, 
                       u.username as deleted_by_name,
                       c.name as customer_name
                FROM {$this->table} s
                LEFT JOIN users u ON s.deleted_by = u.id
                LEFT JOIN customers c ON s.customer = c.id
                $whereClause
                ORDER BY s.deleted_at DESC
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
    
    
}
?>