<?php
require_once __DIR__ . '/../config/database.php';

class Installation {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function createInstallationDelegation($data) {
        $sql = "INSERT INTO installation_delegations (
            survey_id, site_id, vendor_id, delegated_by, delegation_date,
            expected_start_date, expected_completion_date, priority,
            installation_type, special_instructions, status, notes
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['survey_id'],
            $data['site_id'],
            $data['vendor_id'],
            $data['delegated_by'],
            $data['delegation_date'] ?? date('Y-m-d H:i:s'),
            $data['expected_start_date'] ?? null,
            $data['expected_completion_date'] ?? null,
            $data['priority'] ?? 'medium',
            $data['installation_type'] ?? 'standard',
            $data['special_instructions'] ?? null,
            'assigned',
            $data['notes'] ?? null
        ]);
        
        if ($result) {
            $installationId = $this->db->lastInsertId();
            
            // Update survey status to indicate installation has been delegated
            $this->updateSurveyInstallationStatus($data['survey_id'], 'delegated', $installationId);
            
            return $installationId;
        }
        return false;
    }
    
    public function updateInstallationStatus($id, $status, $updatedBy, $notes = null) {
        $sql = "UPDATE installation_delegations SET 
            status = ?, updated_by = ?, updated_at = NOW()";
        
        $params = [$status, $updatedBy];
        
        if ($notes) {
            $sql .= ", notes = ?";
            $params[] = $notes;
        }
        
        // Add status-specific fields
        switch ($status) {
            case 'in_progress':
                $sql .= ", actual_start_date = NOW()";
                break;
            case 'completed':
                $sql .= ", actual_completion_date = NOW()";
                break;
            case 'on_hold':
            case 'cancelled':
                $sql .= ", hold_reason = ?";
                $params[] = $notes;
                break;
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $id;
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function getAllInstallations($status = null, $vendorId = null, $dateFrom = null, $dateTo = null) {
        $sql = "SELECT id.*, ss.survey_status, ss.technical_remarks as survey_remarks,
                       s.site_id as site_code, s.location,
                       ct.name as city_name, st.name as state_name,
                       v.name as vendor_name, v.company_name as vendor_company_name,
                       v.email as vendor_email, v.phone as vendor_phone,
                       u1.username as delegated_by_name, u2.username as updated_by_name
                FROM installation_delegations id
                LEFT JOIN site_surveys ss ON id.survey_id = ss.id
                LEFT JOIN sites s ON id.site_id = s.id
                LEFT JOIN cities ct ON s.city_id = ct.id
                LEFT JOIN states st ON s.state_id = st.id
                LEFT JOIN vendors v ON id.vendor_id = v.id
                LEFT JOIN users u1 ON id.delegated_by = u1.id
                LEFT JOIN users u2 ON id.updated_by = u2.id";
        
        $conditions = ['s.id IS NOT NULL']; // Only show installations with valid sites
        $params = [];
        
        if ($status) {
            $conditions[] = "id.status = ?";
            $params[] = $status;
        }
        
        if ($vendorId) {
            $conditions[] = "id.vendor_id = ?";
            $params[] = $vendorId;
        }
        
        if ($dateFrom) {
            $conditions[] = "DATE(id.delegation_date) >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $conditions[] = "DATE(id.delegation_date) <= ?";
            $params[] = $dateTo;
        }
        
        $sql .= " WHERE " . implode(" AND ", $conditions);
        $sql .= " ORDER BY id.delegation_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getInstallationDetails($id) {
        $sql = "SELECT id.*, ss.survey_status, ss.technical_remarks as survey_remarks,
                       ss.checkin_datetime, ss.checkout_datetime, ss.working_hours,
                       s.site_id as site_code, s.location,
                       ct.name as city_name, st.name as state_name,
                       v.name as vendor_name, v.company_name as vendor_company_name, 
                       v.email as vendor_email, v.phone as vendor_phone,
                       u1.username as delegated_by_name, u2.username as updated_by_name
                FROM installation_delegations id
                LEFT JOIN site_surveys ss ON id.survey_id = ss.id
                LEFT JOIN sites s ON id.site_id = s.id
                LEFT JOIN cities ct ON s.city_id = ct.id
                LEFT JOIN states st ON s.state_id = st.id
                LEFT JOIN vendors v ON id.vendor_id = v.id
                LEFT JOIN users u1 ON id.delegated_by = u1.id
                LEFT JOIN users u2 ON id.updated_by = u2.id
                WHERE id.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getVendorInstallations($vendorId, $status = null) {
        return $this->getAllInstallations($status, $vendorId);
    }
    
    public function getInstallationBySiteAndVendor($siteId, $vendorId) {
        $sql = "SELECT id.*, s.site_id as site_code, s.location
                FROM installation_delegations id
                LEFT JOIN sites s ON id.site_id = s.id
                WHERE id.site_id = ? AND id.vendor_id = ?
                ORDER BY id.delegation_date DESC
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$siteId, $vendorId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getInstallationStats() {
        $stats = [];
        
        // Total installations
        $stmt = $this->db->query("SELECT COUNT(*) FROM installation_delegations");
        $stats['total'] = $stmt->fetchColumn();
        
        // Status-wise breakdown
        $stmt = $this->db->query("
            SELECT status, COUNT(*) as count 
            FROM installation_delegations 
            GROUP BY status
        ");
        $statusStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($statusStats as $stat) {
            $stats[$stat['status']] = $stat['count'];
        }
        
        // Overdue installations
        $stmt = $this->db->query("
            SELECT COUNT(*) FROM installation_delegations 
            WHERE expected_completion_date < NOW() 
            AND status NOT IN ('completed', 'cancelled')
        ");
        $stats['overdue'] = $stmt->fetchColumn();
        
        return $stats;
    }
    
    public function updateSurveyInstallationStatus($surveyId, $status, $installationId = null) {
        $sql = "UPDATE site_surveys SET installation_status = ?";
        $params = [$status];
        
        if ($installationId) {
            $sql .= ", installation_id = ?";
            $params[] = $installationId;
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $surveyId;
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function addInstallationProgress($installationId, $data) {
        $sql = "INSERT INTO installation_progress (
            installation_id, progress_date, progress_percentage, 
            work_description, photos, issues_faced, next_steps, updated_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $installationId,
            $data['progress_date'] ?? date('Y-m-d'),
            $data['progress_percentage'] ?? 0,
            $data['work_description'] ?? null,
            $data['photos'] ?? null,
            $data['issues_faced'] ?? null,
            $data['next_steps'] ?? null,
            $data['updated_by']
        ]);
    }
    
    public function getInstallationProgress($installationId) {
        $sql = "SELECT ip.*, u.username as updated_by_name
                FROM installation_progress ip
                LEFT JOIN users u ON ip.updated_by = u.id
                WHERE ip.installation_id = ?
                ORDER BY ip.progress_date DESC, ip.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$installationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getVendorInstallationStats($vendorId) {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status IN ('assigned', 'acknowledged', 'in_progress') 
                             AND expected_completion_date < CURDATE() THEN 1 ELSE 0 END) as overdue
                FROM installation_delegations 
                WHERE vendor_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$vendorId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function updateInstallationTimings($installationId, $arrivalTime, $installationStartTime, $updatedBy) {
        $sql = "UPDATE installation_delegations 
                SET actual_start_date = ?, installation_start_time = ?, updated_by = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$arrivalTime, $installationStartTime, $updatedBy, $installationId]);
    }
    
    public function addInstallationProgressUpdate($progressData) {
        $sql = "INSERT INTO installation_progress 
                (installation_id, progress_date, progress_percentage, work_description, 
                 issues_faced, next_steps, updated_by, created_at)
                VALUES (?, CURDATE(), ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $progressData['installation_id'],
            $progressData['progress_percentage'],
            $progressData['work_description'],
            $progressData['issues_faced'],
            $progressData['next_steps'],
            $progressData['updated_by']
        ]);
    }
    
    public function completeInstallation($installationId, $updatedBy) {
        try {
            $this->db->beginTransaction();
            
            // Get installation details to find the site_id
            $installation = $this->getInstallationDetails($installationId);
            if (!$installation) {
                throw new Exception('Installation not found');
            }
            
            // Update installation_delegations table
            $sql = "UPDATE installation_delegations 
                    SET status = 'completed', 
                        actual_completion_date = CURRENT_TIMESTAMP,
                        updated_by = ?, 
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$updatedBy, $installationId]);
            
            // Update sites table
            $siteSql = "UPDATE sites 
                        SET installation_status = 1, 
                            installation_date = CURRENT_TIMESTAMP,
                            updated_at = CURRENT_TIMESTAMP,
                            updated_by = ?
                        WHERE id = ?";
            
            $siteStmt = $this->db->prepare($siteSql);
            $siteStmt->execute([$updatedBy, $installation['site_id']]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error completing installation: " . $e->getMessage());
            return false;
        }
    }
    
    public function getAllWithDetails() {
        $sql = "SELECT id.*, 
                       s.site_id, s.location,
                       s.po_number, s.site_ticket_id,
                       ct.name as city_name, st.name as state_name,
                       v.name as vendor_name, v.company_name as vendor_company_name,
                       id.status,
                       COALESCE(
                           (SELECT AVG(progress_percentage) 
                            FROM installation_progress ip 
                            WHERE ip.installation_id = id.id), 0
                       ) as progress_percentage,
                       id.expected_start_date as start_date,
                       id.expected_completion_date,
                       id.actual_completion_date,
                       id.notes,
                       id.special_instructions as material_usage,
                       COALESCE(
                           (SELECT COUNT(*) 
                            FROM installation_progress ip 
                            WHERE ip.installation_id = id.id AND ip.photos IS NOT NULL), 0
                       ) as files,
                       id.created_at,
                       id.updated_at
                FROM installation_delegations id
                LEFT JOIN sites s ON id.site_id = s.id
                LEFT JOIN cities ct ON s.city_id = ct.id
                LEFT JOIN states st ON s.state_id = st.id
                LEFT JOIN vendors v ON id.vendor_id = v.id
                WHERE s.id IS NOT NULL
                ORDER BY id.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getInstallationProgressWithAttachments($installationId) {
        // Get progress entries
        $sql = "SELECT ip.*, u.username as updated_by_name,
                       COALESCE(ipa_count.total_attachments, 0) as total_attachments,
                       COALESCE(ipa_count.has_final_report, 0) as has_final_report,
                       COALESCE(ipa_count.has_site_snaps, 0) as has_site_snaps,
                       COALESCE(ipa_count.has_excel_sheet, 0) as has_excel_sheet,
                       COALESCE(ipa_count.has_drawing_attachment, 0) as has_drawing_attachment
                FROM installation_progress ip
                LEFT JOIN users u ON ip.updated_by = u.id
                LEFT JOIN (
                    SELECT progress_id, 
                           COUNT(*) as total_attachments,
                           MAX(CASE WHEN attachment_type = 'final_report' THEN 1 ELSE 0 END) as has_final_report,
                           MAX(CASE WHEN attachment_type = 'site_snaps' THEN 1 ELSE 0 END) as has_site_snaps,
                           MAX(CASE WHEN attachment_type = 'excel_sheet' THEN 1 ELSE 0 END) as has_excel_sheet,
                           MAX(CASE WHEN attachment_type = 'drawing_attachment' THEN 1 ELSE 0 END) as has_drawing_attachment
                    FROM installation_progress_attachments 
                    GROUP BY progress_id
                ) ipa_count ON ip.id = ipa_count.progress_id
                WHERE ip.installation_id = ?
                ORDER BY ip.progress_date DESC, ip.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$installationId]);
        $progressEntries = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get attachments for each progress entry
        foreach ($progressEntries as &$entry) {
            $attachmentSql = "SELECT ipa.*, u.username as uploaded_by_name
                              FROM installation_progress_attachments ipa
                              LEFT JOIN users u ON ipa.uploaded_by = u.id
                              WHERE ipa.progress_id = ?
                              ORDER BY ipa.attachment_type, ipa.uploaded_at";
            
            $attachmentStmt = $this->db->prepare($attachmentSql);
            $attachmentStmt->execute([$entry['id']]);
            $entry['attachments'] = $attachmentStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get attachment description from the first attachment (they all have the same description)
            if (!empty($entry['attachments'])) {
                $entry['attachment_description'] = $entry['attachments'][0]['description'];
            }
        }
        
        return $progressEntries;
    }
    
    public function addInstallationProgressUpdateWithId($progressData) {
        $sql = "INSERT INTO installation_progress 
                (installation_id, progress_date, progress_percentage, work_description, 
                 issues_faced, next_steps, updated_by, created_at)
                VALUES (?, CURDATE(), ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $progressData['installation_id'],
            $progressData['progress_percentage'],
            $progressData['work_description'],
            $progressData['issues_faced'],
            $progressData['next_steps'],
            $progressData['updated_by']
        ]);
        
        if ($result) {
            return $this->db->lastInsertId();
        }
        return false;
    }
    
    public function getMaterialUsage($installationId) {
        $sql = "SELECT imu.*, bi.item_name, bi.item_code, bi.unit
                FROM installation_material_usage imu
                LEFT JOIN boq_items bi ON imu.boq_item_id = bi.id
                WHERE imu.installation_id = ?
                ORDER BY imu.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$installationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getAttachments($installationId) {
        $sql = "SELECT ia.*
                FROM installation_attachments ia
                WHERE ia.installation_id = ?
                ORDER BY ia.uploaded_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$installationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getInstallationHistory($siteId) {
        $sql = "SELECT id.*, 
                       COALESCE(NULLIF(v.company_name, ''), v.name) as vendor_name,
                       u.username as delegated_by_name
                FROM installation_delegations id
                LEFT JOIN vendors v ON id.vendor_id = v.id
                LEFT JOIN users u ON id.delegated_by = u.id
                WHERE id.site_id = ?
                ORDER BY id.delegation_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$siteId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>