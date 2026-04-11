<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/Inventory.php';
require_once __DIR__ . '/../includes/audit_integration.php';

header('Content-Type: application/json');

// Require vendor authentication
Auth::requireVendor();

$vendorId = Auth::getVendorId();
$currentUser = Auth::getCurrentUser();

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    $requestId = $_POST['request_id'] ?? null;
    $dispatchId = $_POST['dispatch_id'] ?? null;
    $receivedQuantities = $_POST['received_quantities'] ?? [];
    
    if (!$requestId || !$dispatchId || empty($receivedQuantities)) {
        throw new Exception('Missing required data');
    }
    
    $inventoryModel = new Inventory();
    
    // Verify the dispatch belongs to this vendor
    $dispatchDetails = $inventoryModel->getDispatchById($dispatchId);
    if (!$dispatchDetails || $dispatchDetails['vendor_id'] != $vendorId) {
        throw new Exception('Unauthorized access to dispatch');
    }
    
    // Verify the dispatch is in a status that allows updates
    if (!in_array($dispatchDetails['dispatch_status'], ['delivered', 'confirmed'])) {
        throw new Exception('Can only update quantities for delivered or confirmed dispatches');
    }
    
    // Get current delivery confirmation details
    $deliveryConfirmation = $inventoryModel->getDeliveryConfirmationDetails($dispatchId);
    if (!$deliveryConfirmation) {
        throw new Exception('No delivery confirmation found');
    }
    
    // Parse current item confirmations
    $currentConfirmations = $deliveryConfirmation['item_confirmations'] ?? [];
    $updatedConfirmations = [];
    
    // Track changes for audit log
    $changes = [];
    
    // Update the received quantities
    foreach ($receivedQuantities as $boqItemId => $newQuantity) {
        $newQuantity = intval($newQuantity);
        $oldQuantity = 0;
        
        // Find existing confirmation for this item
        $existingConfirmation = null;
        foreach ($currentConfirmations as $confirmation) {
            if ($confirmation['boq_item_id'] == $boqItemId) {
                $existingConfirmation = $confirmation;
                $oldQuantity = $confirmation['received_quantity'] ?? 0;
                break;
            }
        }
        
        // Only process if quantity changed
        if ($oldQuantity != $newQuantity) {
            $changes[] = [
                'boq_item_id' => $boqItemId,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity
            ];
        }
        
        // Update or create confirmation entry
        if ($existingConfirmation) {
            $existingConfirmation['received_quantity'] = $newQuantity;
            $existingConfirmation['updated_at'] = date('Y-m-d H:i:s');
            $updatedConfirmations[] = $existingConfirmation;
        } else {
            $updatedConfirmations[] = [
                'boq_item_id' => $boqItemId,
                'received_quantity' => $newQuantity,
                'updated_at' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    // Add unchanged confirmations
    foreach ($currentConfirmations as $confirmation) {
        $found = false;
        foreach ($updatedConfirmations as $updated) {
            if ($updated['boq_item_id'] == $confirmation['boq_item_id']) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            $updatedConfirmations[] = $confirmation;
        }
    }
    
    if (empty($changes)) {
        echo json_encode([
            'success' => false,
            'message' => 'No changes detected'
        ]);
        exit;
    }
    
    // Get dispatch items to compare dispatched vs received quantities
    $dispatchItems = $inventoryModel->getDispatchItemsSummary($dispatchId);
    
    // Calculate delivery status based on received quantities
    $totalDispatched = 0;
    $totalReceived = 0;
    $hasPartialItems = false;
    $hasFullyReceivedItems = false;
    
    foreach ($dispatchItems as $dispatchItem) {
        $boqItemId = $dispatchItem['boq_item_id'];
        $dispatchedQty = $dispatchItem['quantity_dispatched'];
        $receivedQty = 0;
        
        // Find received quantity for this item
        foreach ($updatedConfirmations as $confirmation) {
            if ($confirmation['boq_item_id'] == $boqItemId) {
                $receivedQty = $confirmation['received_quantity'] ?? 0;
                break;
            }
        }
        
        $totalDispatched += $dispatchedQty;
        $totalReceived += $receivedQty;
        
        if ($receivedQty > 0 && $receivedQty < $dispatchedQty) {
            $hasPartialItems = true;
        } elseif ($receivedQty > 0 && $receivedQty >= $dispatchedQty) {
            $hasFullyReceivedItems = true;
        }
    }
    
    // Determine new dispatch status
    $newDispatchStatus = $dispatchDetails['dispatch_status']; // Keep current status as default
    
    if ($totalReceived == 0) {
        // No items received - keep as dispatched/delivered
        $newDispatchStatus = 'dispatched';
    } elseif ($totalReceived >= $totalDispatched) {
        // All items fully received
        $newDispatchStatus = 'confirmed';
    } elseif ($hasPartialItems || ($totalReceived > 0 && $totalReceived < $totalDispatched)) {
        // Some items partially received or total received is less than dispatched
        $newDispatchStatus = 'partially_delivered';
    } else {
        // Default to confirmed if we have received items
        $newDispatchStatus = 'confirmed';
    }
    
    // Update the dispatch with new item confirmations and status
    $updateData = [
        'item_confirmations' => json_encode($updatedConfirmations),
        'confirmation_date' => date('Y-m-d H:i:s'), // Update confirmation timestamp
        'dispatch_status' => $newDispatchStatus
    ];
    
    $result = $inventoryModel->updateDispatchStatus($dispatchId, $updateData);
    
    if (!$result) {
        throw new Exception('Failed to update delivery confirmation');
    }
    
    // Log the changes for audit
    $changesSummary = [];
    foreach ($changes as $change) {
        $changesSummary[] = "BOQ Item {$change['boq_item_id']}: {$change['old_quantity']} → {$change['new_quantity']}";
    }
    
    $statusChanged = $newDispatchStatus !== $dispatchDetails['dispatch_status'];
    $auditDescription = "Contractor updated received quantities for dispatch {$dispatchDetails['dispatch_number']}: " . implode(', ', $changesSummary);
    
    if ($statusChanged) {
        $auditDescription .= " | Status changed from '{$dispatchDetails['dispatch_status']}' to '{$newDispatchStatus}'";
    }
    
    auditLogAction(
        'delivery_quantities_updated',
        "contractor/update-received-quantities.php?dispatch_id={$dispatchId}",
        200,
        [
            'request_id' => $requestId,
            'dispatch_number' => $dispatchDetails['dispatch_number'],
            'changes' => $changes,
            'changes_summary' => implode(', ', $changesSummary),
            'old_status' => $dispatchDetails['dispatch_status'],
            'new_status' => $newDispatchStatus,
            'status_changed' => $statusChanged,
            'total_dispatched' => $totalDispatched,
            'total_received' => $totalReceived,
            'updated_by' => $currentUser['username'],
            'vendor_id' => $vendorId,
            'description' => $auditDescription
        ]
    );
    
    echo json_encode([
        'success' => true,
        'message' => 'Quantities updated successfully',
        'changes_count' => count($changes),
        'status_changed' => $statusChanged,
        'new_status' => $newDispatchStatus,
        'old_status' => $dispatchDetails['dispatch_status']
    ]);
    
} catch (Exception $e) {
    error_log("Error updating received quantities: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>