<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../config/database.php';

header('Content-Type: application/json');

try {
    Auth::requireVendor();
    $vendorId = Auth::getVendorId();
    $siteId = $_GET['site_id'] ?? null;
    $delegationId = $_GET['delegation_id'] ?? null;
    $type = $_GET['type'] ?? 'all'; // boq, survey, installation, material

    if (!$siteId) throw new Exception("Site ID is required");

    $db = Database::getInstance()->getConnection();
    $response = ['success' => true, 'data' => []];

    // 1. BOQ Data (Site-Specific Material Manifest)
    if ($type == 'all' || $type == 'boq') {
        $stmt = $db->prepare("
            SELECT mr.items, mr.id as request_id, cu.name as customer_name 
            FROM material_requests mr
            INNER JOIN sites s ON mr.site_id = s.id
            LEFT JOIN customers cu ON s.customer_id = cu.id
            WHERE mr.site_id = ? 
            ORDER BY mr.id DESC LIMIT 1
        ");
        $stmt->execute([$siteId]);
        $request = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $boqData = null;
        if ($request) {
            $items = json_decode($request['items'], true);
            if (!empty($items) && is_array($items)) {
                $boqItemIds = array_filter(array_column($items, 'boq_item_id'));
                if (!empty($boqItemIds)) {
                    $placeholders = implode(',', array_fill(0, count($boqItemIds), '?'));
                    $stmt = $db->prepare("SELECT id, item_name, unit FROM boq_items WHERE id IN ($placeholders)");
                    $stmt->execute(array_values($boqItemIds));
                    $itemMaster = [];
                    foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                        $itemMaster[$row['id']] = $row;
                    }
                    
                    foreach ($items as &$item) {
                        if (empty($item['item_name']) && isset($item['boq_item_id'])) {
                            $item['item_name'] = $itemMaster[$item['boq_item_id']]['item_name'] ?? 'Unknown Item';
                        }
                        if (empty($item['unit']) && isset($item['boq_item_id'])) {
                            $item['unit'] = $itemMaster[$item['boq_item_id']]['unit'] ?? 'Unit';
                        }
                    }
                }
            }
            $boqData = [
                'id' => $request['request_id'],
                'customer_name' => $request['customer_name'],
                'items' => $items ?: []
            ];
        }
        $response['data']['boq'] = $boqData;
    }

    // 2. Survey Info
    if ($type == 'all' || $type == 'survey') {
        $stmt = $db->prepare("
            SELECT sd.delegation_date as survey_delegation_date, sd.notes as survey_notes,
                   COALESCE(ss.survey_status, dsr.survey_status) as status,
                   COALESCE(ss.submitted_date, dsr.submitted_date) as submitted_date
            FROM site_delegations sd
            LEFT JOIN site_surveys ss ON sd.id = ss.delegation_id
            LEFT JOIN (
                SELECT delegation_id, survey_status, submitted_date 
                FROM dynamic_survey_responses 
                WHERE id IN (SELECT MAX(id) FROM dynamic_survey_responses GROUP BY delegation_id)
            ) dsr ON sd.id = dsr.delegation_id
            WHERE sd.site_id = ? AND sd.vendor_id = ?
            ORDER BY sd.delegation_date DESC LIMIT 1
        ");
        $stmt->execute([$siteId, $vendorId]);
        $response['data']['survey'] = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 3. Installation Details
    if ($type == 'all' || $type == 'installation') {
        $stmt = $db->prepare("
            SELECT id.*, u.username as assigned_by
            FROM installation_delegations id
            LEFT JOIN users u ON id.delegated_by = u.id
            WHERE id.site_id = ? AND id.vendor_id = ?
            ORDER BY id.delegation_date DESC LIMIT 1
        ");
        $stmt->execute([$siteId, $vendorId]);
        $response['data']['installation'] = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 4. Material Information
    if ($type == 'all' || $type == 'material') {
        $stmt = $db->prepare("
            SELECT mr.*, id.dispatch_number, id.dispatch_status, id.courier_name, id.tracking_number
            FROM material_requests mr
            LEFT JOIN inventory_dispatches id ON mr.id = id.material_request_id
            WHERE mr.site_id = ? AND mr.vendor_id = ?
            ORDER BY mr.created_date DESC
        ");
        $stmt->execute([$siteId, $vendorId]);
        $response['data']['materials'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
