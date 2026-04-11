<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/Inventory.php';
require_once __DIR__ . '/../models/MaterialRequest.php';

// Require vendor authentication
Auth::requireRole(VENDOR_ROLE);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$currentUser = Auth::getCurrentUser();
$vendorId = $currentUser['vendor_id'];
$action = $_POST['action'] ?? '';
$dispatchId = $_POST['dispatch_id'] ?? null;

if (!$dispatchId) {
    echo json_encode(['success' => false, 'message' => 'Dispatch ID is required']);
    exit;
}

try {
    $inventoryModel = new Inventory();
    $materialRequestModel = new MaterialRequest();
    
    // Verify the dispatch belongs to this vendor
    $dispatch = $inventoryModel->getDispatchDetails($dispatchId);
    if (!$dispatch || $dispatch['vendor_id'] != $vendorId) {
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        exit;
    }
    
    if ($action === 'accept_individual_item') {
        $boqItemId = $_POST['boq_item_id'] ?? null;
        
        if (!$boqItemId) {
            echo json_encode(['success' => false, 'message' => 'BOQ Item ID is required']);
            exit;
        }
        
        // Get dispatch items for this specific BOQ item
        $dispatchItems = $inventoryModel->getDispatchItems($dispatchId);
        $targetItems = array_filter($dispatchItems, function($item) use ($boqItemId) {
            return $item['boq_item_id'] == $boqItemId;
        });
        
        if (empty($targetItems)) {
            echo json_encode(['success' => false, 'message' => 'Item not found in dispatch']);
            exit;
        }
        
        // Create individual acceptance record
        $acceptanceData = [
            'dispatch_id' => $dispatchId,
            'boq_item_id' => $boqItemId,
            'accepted_quantity' => count($targetItems), // Number of individual items
            'accepted_by' => $currentUser['id'],
            'accepted_date' => date('Y-m-d H:i:s'),
            'acceptance_notes' => 'Individual item acceptance',
            'condition' => 'good'
        ];
        
        // Update the dispatch items status to accepted
        foreach ($targetItems as $item) {
            $inventoryModel->updateStockItemStatus($item['inventory_stock_id'], 'delivered', $dispatchId);
        }
        
        // Create acceptance record in item_confirmations
        $existingConfirmations = json_decode($dispatch['item_confirmations'] ?? '[]', true);
        $existingConfirmations[] = [
            'boq_item_id' => $boqItemId,
            'received_quantity' => count($targetItems),
            'condition' => 'good',
            'notes' => 'Individual item acceptance',
            'accepted_date' => date('Y-m-d H:i:s'),
            'accepted_by' => $currentUser['id']
        ];
        
        // Update dispatch with new confirmations
        $inventoryModel->updateDispatchStatus($dispatchId, [
            'item_confirmations' => json_encode($existingConfirmations)
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Item accepted successfully'
        ]);
        
    } elseif ($action === 'accept_request') {
        $requestId = $_POST['request_id'] ?? null;
        
        if (!$requestId) {
            echo json_encode(['success' => false, 'message' => 'Request ID is required']);
            exit;
        }
        
        // Get all dispatch items
        $dispatchItems = $inventoryModel->getDispatchItems($dispatchId);
        
        if (empty($dispatchItems)) {
            echo json_encode(['success' => false, 'message' => 'No items found in dispatch']);
            exit;
        }
        
        // Create confirmations for all items
        $itemConfirmations = [];
        $itemsByBoq = [];
        
        // Group items by BOQ item ID
        foreach ($dispatchItems as $item) {
            $boqId = $item['boq_item_id'];
            if (!isset($itemsByBoq[$boqId])) {
                $itemsByBoq[$boqId] = [];
            }
            $itemsByBoq[$boqId][] = $item;
        }
        
        // Create confirmations for each BOQ item
        foreach ($itemsByBoq as $boqId => $items) {
            $itemConfirmations[] = [
                'boq_item_id' => $boqId,
                'received_quantity' => count($items),
                'condition' => 'good',
                'notes' => 'Full request acceptance',
                'accepted_date' => date('Y-m-d H:i:s'),
                'accepted_by' => $currentUser['id']
            ];
            
            // Update stock status for all items
            foreach ($items as $item) {
                $inventoryModel->updateStockItemStatus($item['inventory_stock_id'], 'delivered', $dispatchId);
            }
        }
        
        // Update dispatch status to confirmed
        $deliveryData = [
            'dispatch_status' => 'confirmed',
            'delivery_date' => date('Y-m-d'),
            'delivery_time' => date('H:i:s'),
            'received_by' => $currentUser['username'],
            'received_by_phone' => $currentUser['phone'] ?? '',
            'delivery_notes' => 'Full request accepted by vendor',
            'item_confirmations' => json_encode($itemConfirmations),
            'confirmed_by' => $currentUser['id'],
            'confirmation_date' => date('Y-m-d H:i:s')
        ];
        
        $result = $inventoryModel->confirmDelivery($dispatchId, $deliveryData);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Request accepted successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to update dispatch status'
            ]);
        }
        
    } elseif ($action === 'accept_all_dispatches') {
        $requestId = $_POST['request_id'] ?? null;
        
        if (!$requestId) {
            echo json_encode(['success' => false, 'message' => 'Request ID is required']);
            exit;
        }
        
        // Get all dispatches for this request and vendor
        $allDispatches = $inventoryModel->getReceivedMaterialsForVendor($vendorId);
        $requestDispatches = array_filter($allDispatches, function($dispatch) use ($requestId) {
            return $dispatch['material_request_id'] == $requestId && $dispatch['dispatch_status'] === 'dispatched';
        });
        
        if (empty($requestDispatches)) {
            echo json_encode(['success' => false, 'message' => 'No pending dispatches found for this request']);
            exit;
        }
        
        $processedCount = 0;
        $errors = [];
        
        foreach ($requestDispatches as $dispatch) {
            try {
                // Get all dispatch items
                $dispatchItems = $inventoryModel->getDispatchItems($dispatch['id']);
                
                if (!empty($dispatchItems)) {
                    // Create confirmations for all items
                    $itemConfirmations = [];
                    $itemsByBoq = [];
                    
                    // Group items by BOQ item ID
                    foreach ($dispatchItems as $item) {
                        $boqId = $item['boq_item_id'];
                        if (!isset($itemsByBoq[$boqId])) {
                            $itemsByBoq[$boqId] = [];
                        }
                        $itemsByBoq[$boqId][] = $item;
                    }
                    
                    // Create confirmations for each BOQ item
                    foreach ($itemsByBoq as $boqId => $items) {
                        $itemConfirmations[] = [
                            'boq_item_id' => $boqId,
                            'received_quantity' => count($items),
                            'condition' => 'good',
                            'notes' => 'Bulk acceptance - Request #' . $requestId,
                            'accepted_date' => date('Y-m-d H:i:s'),
                            'accepted_by' => $currentUser['id']
                        ];
                        
                        // Update stock status for all items
                        foreach ($items as $item) {
                            $inventoryModel->updateStockItemStatus($item['inventory_stock_id'], 'delivered', $dispatch['id']);
                        }
                    }
                    
                    // Update dispatch status to confirmed
                    $deliveryData = [
                        'dispatch_status' => 'confirmed',
                        'delivery_date' => date('Y-m-d'),
                        'delivery_time' => date('H:i:s'),
                        'received_by' => $currentUser['username'],
                        'received_by_phone' => $currentUser['phone'] ?? '',
                        'delivery_notes' => 'Bulk acceptance for Request #' . $requestId,
                        'item_confirmations' => json_encode($itemConfirmations),
                        'confirmed_by' => $currentUser['id'],
                        'confirmation_date' => date('Y-m-d H:i:s')
                    ];
                    
                    $result = $inventoryModel->confirmDelivery($dispatch['id'], $deliveryData);
                    
                    if ($result) {
                        $processedCount++;
                    } else {
                        $errors[] = "Failed to process dispatch #" . $dispatch['dispatch_number'];
                    }
                }
            } catch (Exception $e) {
                $errors[] = "Error processing dispatch #" . $dispatch['dispatch_number'] . ": " . $e->getMessage();
            }
        }
        
        if ($processedCount > 0) {
            $message = "Successfully accepted $processedCount dispatch(es) for Request #$requestId";
            if (!empty($errors)) {
                $message .= ". Some errors occurred: " . implode(", ", $errors);
            }
            echo json_encode([
                'success' => true,
                'message' => $message,
                'processed_count' => $processedCount,
                'errors' => $errors
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No dispatches were processed. Errors: ' . implode(", ", $errors)
            ]);
        }
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    error_log("Error in process-request-acceptance.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while processing the request: ' . $e->getMessage()
    ]);
}
?>