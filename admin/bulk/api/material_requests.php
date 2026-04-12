<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../models/MaterialRequest.php';

// Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$mrModel = new MaterialRequest();
$db = Database::getInstance()->getConnection();

try {
    if ($action === 'get_pending') {
        $stmt = $db->query("SELECT mr.*, s.site_id as site_code, s.location, v.name as vendor_name, v.company_name as vendor_company_name 
                             FROM material_requests mr
                             JOIN sites s ON mr.site_id = s.id
                             LEFT JOIN vendors v ON mr.vendor_id = v.id
                             WHERE mr.status = 'pending'
                             ORDER BY mr.created_date DESC");
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'requests' => $requests]);
        exit;
    }

    if ($action === 'get_approved') {
        $stmt = $db->query("SELECT mr.*, s.site_id as site_code, s.location, v.name as vendor_name 
                             FROM material_requests mr
                             JOIN sites s ON mr.site_id = s.id
                             LEFT JOIN vendors v ON mr.vendor_id = v.id
                             WHERE mr.status = 'approved'
                             ORDER BY mr.created_date DESC");
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'requests' => $requests]);
        exit;
    }

    if ($action === 'process') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['id']) || empty($input['status'])) {
            throw new Exception("Invalid request parameters");
        }

        $id = $input['id'];
        $status = $input['status'];
        $remarks = $input['remarks'] ?? '';
        $processedBy = $_SESSION['user_id'] ?? 0;

        $mrModel->updateStatus($id, $status, $processedBy, date('Y-m-d H:i:s'));
        
        // Also update remarks if provided (if table has such field)
        // $stmt = $db->prepare("UPDATE material_requests SET processing_remarks = ? WHERE id = ?");
        // $stmt->execute([$remarks, $id]);

        echo json_encode(['success' => true]);
        exit;
    }

    if ($action === 'create_admin') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['site_id']) || empty($input['items'])) {
            throw new Exception("Incomplete requisition data");
        }

        $siteId = $input['site_id'];
        $items = $input['items'];
        $requiredDate = $input['required_date'] ?? date('Y-m-d', strtotime('+3 days'));
        
        $sql = "INSERT INTO material_requests (site_id, vendor_id, request_date, required_date, items, status, created_date, created_at) 
                VALUES (?, ?, ?, ?, ?, 'pending', NOW(), NOW())";
        
        $stmt = $db->prepare($sql);
        // Admin created requests are automatically 'approved'
        $stmt->execute([
            $siteId,
            0, // System/Admin
            date('Y-m-d'),
            $requiredDate,
            json_encode($items),
        ]);

        echo json_encode(['success' => true]);
        exit;
    }

    if ($action === 'create_bulk_admin') {
        require_once __DIR__ . '/../../../models/AuditLog.php';
        $auditModel = new AuditLog();
        $currentUser = Auth::getCurrentUser();

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['site_ids']) || empty($input['items'])) {
            throw new Exception("Incomplete deployment data");
        }

        $siteIds = $input['site_ids'];
        $items = $input['items'];
        $requiredDate = $input['required_date'] ?? date('Y-m-d', strtotime('+3 days'));
        $notes = $input['notes'] ?? '';

        $db->beginTransaction();
        try {
            foreach ($siteIds as $siteId) {
                // Backend verification of survey approval
                $stmt = $db->prepare("
                    SELECT 
                        COALESCE(dsr.survey_status, ss.survey_status) as status
                    FROM sites s
                    LEFT JOIN (
                        SELECT d1.site_id, d1.survey_status, d1.id
                        FROM dynamic_survey_responses d1
                        INNER JOIN (SELECT site_id, MAX(id) as max_id FROM dynamic_survey_responses GROUP BY site_id) d2 ON d1.id = d2.max_id
                    ) dsr ON s.id = dsr.site_id
                    LEFT JOIN (
                        SELECT s1.site_id, s1.survey_status, s1.id
                        FROM site_surveys s1
                        INNER JOIN (SELECT site_id, MAX(id) as max_id FROM site_surveys GROUP BY site_id) s2 ON s1.id = s2.max_id
                    ) ss ON s.id = ss.site_id
                    WHERE s.id = ?
                ");
                $stmt->execute([$siteId]);
                $siteData = $stmt->fetch();
                
                if (!$siteData || $siteData['status'] !== 'approved') {
                    continue; // Skip sites without approved survey for safety
                }

                $sql = "INSERT INTO material_requests (site_id, vendor_id, request_date, required_date, items, request_notes, status, created_date, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW(), NOW())";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    $siteId,
                    0, // System
                    date('Y-m-d'),
                    $requiredDate,
                    json_encode($items),
                    $notes
                ]);
            }

            // Record Audit Log
            $auditModel->log([
                'user_id' => $currentUser['id'] ?? null,
                'username' => $currentUser['username'] ?? 'Anonymous',
                'user_role' => $currentUser['role'] ?? 'admin',
                'action_type' => 'bulk_material_request_creation',
                'endpoint' => 'admin/bulk/api/material_requests.php?action=create_bulk_admin',
                'request_data' => json_encode(['sites_count' => count($siteIds), 'notes' => $notes]),
                'status_code' => 200
            ]);

            $db->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
        exit;
    }

    if ($action === 'dispatch') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['id'])) {
            throw new Exception("Invalid request parameters");
        }

        $id = $input['id'];
        $dispatchData = [
            'courier_partner' => $input['courier_name'] ?? '',
            'tracking_number' => $input['tracking_number'] ?? '',
            'dispatch_date' => $input['dispatch_date'] ?? date('Y-m-d')
        ];

        // 1. Update status to 'dispatched'
        $stmt = $db->prepare("UPDATE material_requests SET 
                              status = 'dispatched', 
                              dispatch_details = ?, 
                              processed_date = NOW() 
                              WHERE id = ?");
        $stmt->execute([json_encode($dispatchData), $id]);

        // 2. Also insert into inventory_dispatches if table exists
        // (Assuming checking for table existence first or just ignoring if not critical)
        try {
            $stmt = $db->prepare("INSERT INTO inventory_dispatches 
                (material_request_id, dispatch_number, dispatch_date, courier_name, tracking_number, dispatch_status) 
                VALUES (?, ?, ?, ?, ?, 'in_transit')");
            $dispatchNumber = 'DSP-' . time() . '-' . $id;
            $stmt->execute([$id, $dispatchNumber, $dispatchData['dispatch_date'], $dispatchData['courier_partner'], $dispatchData['tracking_number']]);
        } catch (Exception $e) { /* Table might not exist yet or other issue */ }

        echo json_encode(['success' => true]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Invalid action']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
