<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/Inventory.php';

// Require vendor authentication
Auth::requireVendor();

$vendorId = Auth::getVendorId();
$inventoryModel = new Inventory();

header('Content-Type: application/json');

try {
    // Get pagination and filter parameters
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? max(1, min(100, intval($_GET['limit']))) : 10;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $statusFilter = isset($_GET['status']) ? trim($_GET['status']) : '';
    
    $result = $inventoryModel->getReceivedMaterialsForVendor($vendorId, $page, $limit, $search, $statusFilter);
    
    // Enrich data if needed
    foreach ($result['data'] as &$dispatch) {
        $dispatch['courier_info'] = !empty($dispatch['courier_name']) ? $dispatch['courier_name'] . ($dispatch['tracking_number'] ? ' (' . $dispatch['tracking_number'] . ')' : '') : 'N/A';
    }

    echo json_encode([
        'success' => true,
        'data' => $result['data'],
        'pagination' => [
            'total' => $result['total'],
            'page' => $result['page'],
            'limit' => $result['limit'],
            'totalPages' => $result['totalPages']
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
