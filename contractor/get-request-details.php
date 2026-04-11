<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/MaterialRequest.php';
require_once __DIR__ . '/../models/BoqItem.php';

// Require vendor authentication
Auth::requireVendor();

header('Content-Type: application/json');

$vendorId = Auth::getVendorId();
$requestId = $_GET['id'] ?? null;

if (!$requestId) {
    echo json_encode(['success' => false, 'message' => 'Request ID is required']);
    exit;
}

try {
    $materialRequestModel = new MaterialRequest();
    $request = $materialRequestModel->findWithDetails($requestId);
    
    if (!$request) {
        echo json_encode(['success' => false, 'message' => 'Request not found', 'debug' => ['request_id' => $requestId]]);
        exit;
    }
    
    // Verify the request belongs to this vendor
    if ($request['vendor_id'] != $vendorId) {
        echo json_encode(['success' => false, 'message' => 'Access denied', 'debug' => ['vendor_id' => $vendorId, 'request_vendor_id' => $request['vendor_id']]]);
        exit;
    }
    
    // Parse items JSON and get BOQ item details
    $items = [];
    if ($request['items']) {
        $itemsData = json_decode($request['items'], true);
        if ($itemsData) {
            $boqModel = new BoqItem();
            foreach ($itemsData as $item) {
                $boqItem = null;
                if (isset($item['boq_item_id'])) {
                    $boqItem = $boqModel->find($item['boq_item_id']);
                }
                
                $items[] = [
                    'boq_item_id' => $item['boq_item_id'] ?? null,
                    'item_name' => $boqItem['item_name'] ?? 'Unknown Item',
                    'item_code' => $boqItem['item_code'] ?? ($item['item_code'] ?? 'N/A'),
                    'quantity' => $item['quantity'] ?? 0,
                    'unit' => $boqItem['unit'] ?? ($item['unit'] ?? 'N/A'),
                    'notes' => $item['notes'] ?? ''
                ];
            }
        }
    }
    
    $request['items'] = $items;
    
    echo json_encode([
        'success' => true,
        'request' => $request
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error retrieving request details: ' . $e->getMessage()
    ]);
}
?>