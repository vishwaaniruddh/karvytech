<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../models/Inventory.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $inventoryModel = new Inventory();
    
    // Validate required fields
    $requiredFields = ['dispatch_number', 'dispatch_date', 'contact_person_name', 'delivery_address'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Field '$field' is required");
        }
    }
    
    // Validate items
    if (empty($_POST['items']) || !is_array($_POST['items'])) {
        throw new Exception('At least one item is required');
    }
    
    // Prepare dispatch data
    $dispatchData = [
        'dispatch_number' => $_POST['dispatch_number'],
        'dispatch_date' => $_POST['dispatch_date'],
        'material_request_id' => !empty($_POST['material_request_id']) ? intval($_POST['material_request_id']) : null,
        'site_id' => !empty($_POST['site_id']) ? intval($_POST['site_id']) : null,
        'vendor_id' => !empty($_POST['vendor_id']) ? intval($_POST['vendor_id']) : null,
        'contact_person_name' => $_POST['contact_person_name'],
        'contact_person_phone' => $_POST['contact_person_phone'] ?? null,
        'delivery_address' => $_POST['delivery_address'],
        'courier_name' => $_POST['courier_name'] ?? null,
        'tracking_number' => $_POST['tracking_number'] ?? null,
        'expected_delivery_date' => $_POST['expected_delivery_date'] ?? null,
        'delivery_remarks' => $_POST['delivery_remarks'] ?? null
    ];
    
    // If this dispatch is from a material request, update the request status
    if ($dispatchData['material_request_id']) {
        require_once __DIR__ . '/../../../models/MaterialRequest.php';
        $materialRequestModel = new MaterialRequest();
        $currentUser = Auth::getCurrentUser();
        
        // Update material request status to dispatched
        $materialRequestModel->updateStatus(
            $dispatchData['material_request_id'], 
            'dispatched', 
            $currentUser['id'], 
            date('Y-m-d H:i:s')
        );
    }
    
    // Create dispatch
    $dispatchId = $inventoryModel->createDispatch($dispatchData);
    
    if (!$dispatchId) {
        throw new Exception('Failed to create dispatch');
    }
    
    // Prepare items data
    $items = [];
    foreach ($_POST['items'] as $itemData) {
        if (empty($itemData['boq_item_id']) || empty($itemData['quantity_dispatched']) || empty($itemData['unit_cost'])) {
            continue; // Skip incomplete items
        }
        
        $items[] = [
            'boq_item_id' => intval($itemData['boq_item_id']),
            'quantity_dispatched' => floatval($itemData['quantity_dispatched']),
            'unit_cost' => floatval($itemData['unit_cost']),
            'batch_number' => $itemData['batch_number'] ?? null,
            'serial_numbers' => !empty($itemData['serial_numbers']) ? explode(',', $itemData['serial_numbers']) : null,
            'item_condition' => $itemData['item_condition'] ?? 'new',
            'warranty_period' => $itemData['warranty_period'] ?? null,
            'remarks' => $itemData['remarks'] ?? null
        ];
    }
    
    if (empty($items)) {
        throw new Exception('No valid items found');
    }
    
    // Add items to dispatch
    $result = $inventoryModel->addDispatchItems($dispatchId, $items);
    
    if (!$result) {
        throw new Exception('Failed to add items to dispatch');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Dispatch created successfully',
        'dispatch_id' => $dispatchId
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>