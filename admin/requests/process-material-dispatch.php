<?php
// Enable full error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/dispatch_debug.log');

// Increase execution time for large dispatches
set_time_limit(300); // 5 minutes
ini_set('memory_limit', '256M'); // Increase memory limit

// Create logs directory if it doesn't exist
$logDir = __DIR__ . '/../../logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

// Debug logging function
function debugLog($message, $data = null) {
    $logFile = __DIR__ . '/../../logs/dispatch_debug.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message";
    if ($data !== null) {
        $logMessage .= " | Data: " . json_encode($data);
    }
    $logMessage .= "\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

debugLog("=== DISPATCH PROCESS STARTED ===");
debugLog("Request Method", $_SERVER['REQUEST_METHOD']);
debugLog("POST Data", $_POST);

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/MaterialRequest.php';
require_once __DIR__ . '/../../models/Inventory.php';
require_once __DIR__ . '/../../includes/validation_helper.php';

debugLog("Required files loaded successfully");

// Require admin authentication
try {
    Auth::requireRole(ADMIN_ROLE);
    debugLog("Authentication successful");
} catch (Exception $e) {
    debugLog("Authentication failed", $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Authentication failed: ' . $e->getMessage()]);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    debugLog("Invalid request method", $_SERVER['REQUEST_METHOD']);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    debugLog("Initializing models");
    $materialRequestModel = new MaterialRequest();
    debugLog("MaterialRequest model created");
    
    $inventoryModel = new Inventory();
    debugLog("Inventory model created");
    
    $currentUser = Auth::getCurrentUser();
    debugLog("Current user retrieved", ['user_id' => $currentUser['id'], 'username' => $currentUser['username']]);
    
    // Validate required fields
    debugLog("Validating required fields");
    $requiredFields = ['material_request_id', 'contact_person_name', 'contact_person_phone', 'dispatch_date', 'delivery_address'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            debugLog("Missing required field", $field);
            throw new Exception("Field '$field' is required");
        }
    }
    debugLog("All required fields present");
    
    // Validate phone number
    debugLog("Validating phone number", $_POST['contact_person_phone']);
    $phoneValidation = validatePhoneNumber($_POST['contact_person_phone'], true);
    if (!$phoneValidation['valid']) {
        debugLog("Phone validation failed", $phoneValidation['message']);
        throw new Exception($phoneValidation['message']);
    }
    $cleanPhone = $phoneValidation['formatted'];
    debugLog("Phone validated successfully", $cleanPhone);
    
    $materialRequestId = intval($_POST['material_request_id']);
    debugLog("Material Request ID", $materialRequestId);
    
    // Get material request details
    debugLog("Fetching material request details");
    try {
        $materialRequest = $materialRequestModel->findWithDetails($materialRequestId);
        debugLog("Material request fetched", ['found' => !empty($materialRequest), 'status' => $materialRequest['status'] ?? 'N/A']);
        
        if (!$materialRequest || $materialRequest['status'] !== 'approved') {
            debugLog("Material request validation failed", [
                'exists' => !empty($materialRequest),
                'status' => $materialRequest['status'] ?? 'N/A',
                'expected' => 'approved'
            ]);
            throw new Exception('Material request not found or not approved');
        }
        debugLog("Material request validation passed");
    } catch (Exception $e) {
        debugLog('Material request fetch error', $e->getMessage());
        throw new Exception('Error fetching material request: ' . $e->getMessage());
    }
    
    // Validate items
    debugLog("Validating dispatch items");
    if (empty($_POST['items']) || !is_array($_POST['items'])) {
        debugLog("Items validation failed", ['items_empty' => empty($_POST['items']), 'is_array' => is_array($_POST['items'] ?? null)]);
        throw new Exception('No items to dispatch');
    }
    debugLog("Items validation passed", ['item_count' => count($_POST['items'])]);
    
    // Parse requested items from material request to validate stock
    debugLog("Parsing requested items from material request");
    $requestedItems = json_decode($materialRequest['items'], true) ?: [];
    debugLog("Requested items parsed", ['count' => count($requestedItems), 'items' => $requestedItems]);
    
    debugLog("Checking stock availability");
    try {
        $stockAvailability = $inventoryModel->checkStockAvailabilityForItems($requestedItems);
        debugLog("Stock availability checked successfully", ['availability_count' => count($stockAvailability)]);
    } catch (Exception $e) {
        debugLog('Stock availability check error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        throw new Exception('Error checking stock availability: ' . $e->getMessage());
    }
    
    // Check if any items are out of stock
    debugLog("Checking for stock issues");
    $stockIssues = [];
    foreach ($stockAvailability as $boqItemId => $stock) {
        debugLog("Checking stock for item", ['boq_item_id' => $boqItemId, 'is_sufficient' => $stock['is_sufficient']]);
        if (!$stock['is_sufficient']) {
            $issue = $stock['item_name'] . ' (Available: ' . $stock['available_quantity'] . ', Required: ' . $stock['requested_quantity'] . ')';
            $stockIssues[] = $issue;
            debugLog("Stock issue found", $issue);
        }
    }
    
    if (!empty($stockIssues)) {
        debugLog("Stock issues prevent dispatch", $stockIssues);
        throw new Exception('Insufficient stock for items: ' . implode(', ', $stockIssues));
    }
    debugLog("No stock issues found");
    
    // Generate dispatch number
    debugLog("Generating dispatch number");
    try {
        $dispatchNumber = $inventoryModel->generateDispatchNumber();
        debugLog("Dispatch number generated", $dispatchNumber);
    } catch (Exception $e) {
        debugLog("Error generating dispatch number", $e->getMessage());
        throw new Exception('Error generating dispatch number: ' . $e->getMessage());
    }
    
    // Prepare dispatch data
    $dispatchData = [
        'dispatch_number' => $dispatchNumber,
        'dispatch_date' => $_POST['dispatch_date'],
        'material_request_id' => $materialRequestId,
        'site_id' => $materialRequest['site_id'],
        'vendor_id' => $materialRequest['vendor_id'],
        'contact_person_name' => $_POST['contact_person_name'],
        'contact_person_phone' => $cleanPhone, // Use validated and cleaned phone number
        'delivery_address' => $_POST['delivery_address'],
        'courier_name' => $_POST['courier_name'] ?? null,
        'tracking_number' => $_POST['pod_number'] ?? null,
        'expected_delivery_date' => $_POST['expected_delivery_date'] ?? null,
        'dispatch_status' => 'dispatched',
        'dispatched_by' => $currentUser['id'],
        'delivery_remarks' => $_POST['dispatch_remarks'] ?? null
    ];
    
    // Create dispatch record
    $dispatchId = $inventoryModel->createDispatch($dispatchData);
    
    if (!$dispatchId) {
        throw new Exception('Failed to create dispatch record');
    }
    
    // Process dispatch items
    $dispatchItems = [];
    $totalItems = 0;
    $totalValue = 0;
    
    foreach ($_POST['items'] as $itemData) {
        // Check if this is a BOQ-based item or installation material
        $isBoqItem = !empty($itemData['boq_item_id']);
        $isMaterialRequest = !empty($itemData['material_name']);
        
        // Skip if neither BOQ item nor material name is provided
        if (!$isBoqItem && !$isMaterialRequest) {
            debugLog("Skipping item - no boq_item_id or material_name", $itemData);
            continue;
        }
        
        // Skip if no dispatch quantity
        if (empty($itemData['dispatch_quantity'])) {
            debugLog("Skipping item - no dispatch quantity", $itemData);
            continue;
        }
        
        $boqItemId = $isBoqItem ? intval($itemData['boq_item_id']) : null;
        $materialName = $itemData['material_name'] ?? null;
        $dispatchQuantity = floatval($itemData['dispatch_quantity']);
        $recordType = $itemData['record_type'] ?? 'cumulative';
        
        if ($dispatchQuantity <= 0) {
            debugLog("Skipping item - dispatch quantity is zero or negative", $itemData);
            continue;
        }
        
        // Get unit cost from inventory stock (average cost) for BOQ items
        $unitCost = 0;
        if ($isBoqItem) {
            debugLog("Getting unit cost for BOQ item", $boqItemId);
            try {
                $stockInfo = $inventoryModel->getStockOverview('', '', false);
                debugLog("Stock overview retrieved", ['stock_count' => count($stockInfo)]);
                
                foreach ($stockInfo as $stock) {
                    if ($stock['boq_item_id'] == $boqItemId) {
                        $unitCost = $stock['unit_cost'] ?? 0;
                        debugLog("Unit cost found for item", ['boq_item_id' => $boqItemId, 'unit_cost' => $unitCost]);
                        break;
                    }
                }
                
                if ($unitCost == 0) {
                    debugLog("No unit cost found for item, using default", $boqItemId);
                    $unitCost = 100; // Default fallback cost
                }
            } catch (Exception $e) {
                debugLog('Stock overview error', [
                    'error' => $e->getMessage(),
                    'boq_item_id' => $boqItemId,
                    'trace' => $e->getTraceAsString()
                ]);
                // Use a default unit cost if stock overview fails
                $unitCost = 100; // Default fallback cost
            }
        } else {
            // For installation materials, use a default cost or get from request
            $unitCost = 0; // Installation materials don't have inventory cost tracking
            debugLog("Installation material - no unit cost tracking", ['material_name' => $materialName]);
        }
        
        // Handle individual records
        $individualRecords = [];
        if (!empty($itemData['individual']) && is_array($itemData['individual'])) {
            foreach ($itemData['individual'] as $individual) {
                $individualRecords[] = [
                    'serial_number' => $individual['serial_number'] ?? null,
                    'batch_number' => $individual['batch_number'] ?? null,
                    'quantity' => floatval($individual['quantity'] ?? 1)
                ];
            }
        }
        
        $dispatchItems[] = [
            'boq_item_id' => $boqItemId,
            'material_name' => $materialName,
            'quantity_dispatched' => $dispatchQuantity,
            'unit_cost' => $unitCost,
            'batch_number' => $itemData['batch_number'] ?? null,
            'individual_records' => !empty($individualRecords) ? json_encode($individualRecords) : null,
            'item_condition' => 'new',
            'remarks' => $itemData['dispatch_notes'] ?? null
        ];
        
        $totalItems++;
        $totalValue += $dispatchQuantity * $unitCost;
        
        debugLog("Item added to dispatch", [
            'boq_item_id' => $boqItemId,
            'material_name' => $materialName,
            'quantity' => $dispatchQuantity,
            'unit_cost' => $unitCost
        ]);
    }
    
    if (empty($dispatchItems)) {
        throw new Exception('No valid items to dispatch');
    }
    
    // Add items to dispatch
    $result = $inventoryModel->addDispatchItems($dispatchId, $dispatchItems);
    
    if (!$result) {
        throw new Exception('Failed to add items to dispatch');
    }
    
    // Update dispatch totals (will be handled by addDispatchItems method)
    
    // Update material request status to dispatched
    $materialRequestModel->updateStatus(
        $materialRequestId, 
        'dispatched', 
        $currentUser['id'], 
        date('Y-m-d H:i:s')
    );
    
    // Create tracking entries for dispatched items
    foreach ($dispatchItems as $item) {
        // Only create tracking for BOQ items (inventory tracked items)
        if (empty($item['boq_item_id'])) {
            debugLog("Skipping tracking entry for non-BOQ item", ['material_name' => $item['material_name']]);
            continue;
        }
        
        if (!empty($item['individual_records'])) {
            // Create separate tracking entries for each individual record
            $individualRecords = json_decode($item['individual_records'], true);
            foreach ($individualRecords as $record) {
                $inventoryModel->createTrackingEntry([
                    'boq_item_id' => $item['boq_item_id'],
                    'serial_number' => $record['serial_number'] ?? null,
                    'batch_number' => $record['batch_number'] ?? $item['batch_number'],
                    'quantity' => $record['quantity'],
                    'current_location_type' => 'in_transit',
                    'current_location_name' => 'In Transit to ' . $materialRequest['site_code'],
                    'site_id' => $materialRequest['site_id'],
                    'vendor_id' => $materialRequest['vendor_id'],
                    'dispatch_id' => $dispatchId,
                    'status' => 'dispatched',
                    'movement_remarks' => 'Dispatched via ' . ($dispatchData['courier_name'] ?: 'courier')
                ]);
            }
        } else {
            // Create single tracking entry for cumulative record
            $inventoryModel->createTrackingEntry([
                'boq_item_id' => $item['boq_item_id'],
                'batch_number' => $item['batch_number'],
                'quantity' => $item['quantity_dispatched'],
                'current_location_type' => 'in_transit',
                'current_location_name' => 'In Transit to ' . $materialRequest['site_code'],
                'site_id' => $materialRequest['site_id'],
                'vendor_id' => $materialRequest['vendor_id'],
                'dispatch_id' => $dispatchId,
                'status' => 'dispatched',
                'movement_remarks' => 'Dispatched via ' . ($dispatchData['courier_name'] ?: 'courier')
            ]);
        }
    }
    
    debugLog("=== DISPATCH PROCESS COMPLETED SUCCESSFULLY ===", [
        'dispatch_id' => $dispatchId,
        'dispatch_number' => $dispatchNumber
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Material dispatch processed successfully',
        'dispatch_id' => $dispatchId,
        'dispatch_number' => $dispatchNumber
    ]);
    
} catch (Exception $e) {
    debugLog('FATAL ERROR - Material dispatch processing failed', [
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug_info' => [
            'file' => basename($e->getFile()),
            'line' => $e->getLine(),
            'error' => $e->getMessage()
        ]
    ]);
}


?>