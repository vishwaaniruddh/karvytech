<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/MaterialRequest.php';
require_once __DIR__ . '/../models/Inventory.php';

// Require vendor authentication
Auth::requireRole(VENDOR_ROLE);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $currentUser = Auth::getCurrentUser();
    $vendorId = $currentUser['vendor_id'];
    
    $materialRequestModel = new MaterialRequest();
    $inventoryModel = new Inventory();
    
    // Validate required fields
    $requiredFields = ['request_id', 'dispatch_id', 'delivery_date', 'delivery_time', 'received_by', 'delivery_address'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Field '$field' is required");
        }
    }
    
    $requestId = intval($_POST['request_id']);
    $dispatchId = intval($_POST['dispatch_id']);
    
    // Verify ownership
    $materialRequest = $materialRequestModel->findWithDetails($requestId);
    if (!$materialRequest || $materialRequest['vendor_id'] != $vendorId) {
        throw new Exception('Unauthorized access to this request');
    }
    
    // Verify dispatch exists and is in correct status
    $dispatchDetails = $inventoryModel->getDispatchByRequestId($requestId);
    if (!$dispatchDetails || $dispatchDetails['id'] != $dispatchId) {
        throw new Exception('Invalid dispatch details');
    }
    
    if ($dispatchDetails['dispatch_status'] !== 'dispatched' && $dispatchDetails['dispatch_status'] !== 'in_transit') {
        throw new Exception('Dispatch is not in a confirmable state');
    }
    
    // Handle file uploads
    $uploadedFiles = [];
    $uploadDir = __DIR__ . '/../uploads/delivery_confirmations/';
    
    // Create upload directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Handle LR copy upload (required)
    if (!isset($_FILES['lr_copy']) || $_FILES['lr_copy']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('LR copy upload is required');
    }
    
    $lrCopyFile = $_FILES['lr_copy'];
    $lrCopyExtension = pathinfo($lrCopyFile['name'], PATHINFO_EXTENSION);
    $lrCopyFilename = 'lr_copy_' . $requestId . '_' . time() . '.' . $lrCopyExtension;
    $lrCopyPath = $uploadDir . $lrCopyFilename;
    
    if (!move_uploaded_file($lrCopyFile['tmp_name'], $lrCopyPath)) {
        throw new Exception('Failed to upload LR copy');
    }
    
    $uploadedFiles['lr_copy'] = 'uploads/delivery_confirmations/' . $lrCopyFilename;
    
    // Handle additional documents (optional)
    if (isset($_FILES['additional_documents']) && is_array($_FILES['additional_documents']['name'])) {
        $additionalDocs = [];
        for ($i = 0; $i < count($_FILES['additional_documents']['name']); $i++) {
            if ($_FILES['additional_documents']['error'][$i] === UPLOAD_ERR_OK) {
                $docFile = [
                    'name' => $_FILES['additional_documents']['name'][$i],
                    'tmp_name' => $_FILES['additional_documents']['tmp_name'][$i]
                ];
                
                $docExtension = pathinfo($docFile['name'], PATHINFO_EXTENSION);
                $docFilename = 'additional_doc_' . $requestId . '_' . $i . '_' . time() . '.' . $docExtension;
                $docPath = $uploadDir . $docFilename;
                
                if (move_uploaded_file($docFile['tmp_name'], $docPath)) {
                    $additionalDocs[] = 'uploads/delivery_confirmations/' . $docFilename;
                }
            }
        }
        if (!empty($additionalDocs)) {
            $uploadedFiles['additional_documents'] = $additionalDocs;
        }
    }
    
    // Process delivery confirmation data
    $deliveryData = [
        'delivery_date' => $_POST['delivery_date'],
        'delivery_time' => $_POST['delivery_time'],
        'received_by' => $_POST['received_by'],
        'received_by_phone' => $_POST['received_by_phone'] ?? null,
        'delivery_address' => $_POST['delivery_address'],
        'delivery_notes' => $_POST['delivery_notes'] ?? null,
        'lr_copy_path' => $uploadedFiles['lr_copy'],
        'additional_documents' => !empty($uploadedFiles['additional_documents']) ? json_encode($uploadedFiles['additional_documents']) : null,
        'confirmed_by' => $currentUser['id'],
        'confirmation_date' => date('Y-m-d H:i:s')
    ];
    
    // Process item confirmations
    $itemConfirmations = [];
    if (!empty($_POST['items']) && is_array($_POST['items'])) {
        foreach ($_POST['items'] as $item) {
            if (!empty($item['boq_item_id']) && isset($item['received_quantity'])) {
                $itemConfirmations[] = [
                    'boq_item_id' => intval($item['boq_item_id']),
                    'received_quantity' => floatval($item['received_quantity']),
                    'condition' => $item['condition'] ?? 'good',
                    'notes' => $item['notes'] ?? null
                ];
            }
        }
    }
    
    $deliveryData['item_confirmations'] = json_encode($itemConfirmations);
    
    // Update dispatch status to delivered
    $result = $inventoryModel->confirmDelivery($dispatchId, $deliveryData);
    
    if (!$result) {
        throw new Exception('Failed to confirm delivery');
    }
    
    // Update material request status to completed
    $materialRequestModel->updateStatus(
        $requestId, 
        'completed', 
        $currentUser['id'], 
        date('Y-m-d H:i:s')
    );
    
    // Create notification for admin
    $inventoryModel->createDeliveryNotification([
        'request_id' => $requestId,
        'dispatch_id' => $dispatchId,
        'vendor_id' => $vendorId,
        'message' => "Delivery confirmed for Request #{$requestId} by vendor",
        'type' => 'delivery_confirmation',
        'created_by' => $currentUser['id']
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Delivery confirmed successfully',
        'request_id' => $requestId,
        'dispatch_id' => $dispatchId
    ]);
    
} catch (Exception $e) {
    error_log('Delivery confirmation error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>