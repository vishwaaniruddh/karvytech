<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/Installation.php';
require_once __DIR__ . '/../models/MaterialUsage.php';
require_once __DIR__ . '/../models/MaterialRequest.php';
require_once __DIR__ . '/../models/BoqItem.php';

// Require vendor authentication
Auth::requireVendor();

header('Content-Type: application/json');

$vendorId = Auth::getVendorId();
$installationId = isset($_POST['installation_id']) ? (int)$_POST['installation_id'] : 0;

if (!$installationId) {
    echo json_encode(['success' => false, 'message' => 'Installation ID is required']);
    exit;
}

try {
    $installationModel = new Installation();
    $materialUsageModel = new MaterialUsage();
    $materialRequestModel = new MaterialRequest();
    $boqModel = new BoqItem();
    
    // Get installation details and verify vendor access
    $installation = $installationModel->getInstallationDetails($installationId);
    if (!$installation || $installation['vendor_id'] != $vendorId) {
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        exit;
    }
    
    // Clear existing materials for this installation
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("DELETE FROM installation_materials WHERE installation_id = ?");
    $stmt->execute([$installationId]);
    
    // Get materials from material requests for this site
    $materialRequests = $materialRequestModel->findBySite($installation['site_id']);
    
    $materialsToInitialize = [];
    
    if (!empty($materialRequests)) {
        // Process each material request
        foreach ($materialRequests as $request) {
            // Only include approved or dispatched requests
            if (!in_array($request['status'], ['approved', 'dispatched', 'fulfilled', 'partially_fulfilled'])) {
                continue;
            }
            
            if ($request['items']) {
                $itemsData = json_decode($request['items'], true);
                if ($itemsData && is_array($itemsData)) {
                    foreach ($itemsData as $item) {
                        // Get BOQ item details if available
                        $materialName = 'Unknown Item';
                        $materialUnit = 'Nos';
                        
                        if (isset($item['boq_item_id'])) {
                            $boqItem = $boqModel->find($item['boq_item_id']);
                            if ($boqItem) {
                                $materialName = $boqItem['item_name'];
                                $materialUnit = $boqItem['unit'];
                            }
                        } elseif (isset($item['material_name'])) {
                            $materialName = $item['material_name'];
                        } elseif (isset($item['item_name'])) {
                            $materialName = $item['item_name'];
                        }
                        
                        if (isset($item['unit'])) {
                            $materialUnit = $item['unit'];
                        }
                        
                        $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 0;
                        
                        if ($quantity > 0) {
                            // Check if material already exists in our list (to avoid duplicates)
                            $found = false;
                            foreach ($materialsToInitialize as &$existingMaterial) {
                                if ($existingMaterial['name'] === $materialName) {
                                    $existingMaterial['total_qty'] += $quantity;
                                    $found = true;
                                    break;
                                }
                            }
                            
                            if (!$found) {
                                $materialsToInitialize[] = [
                                    'name' => $materialName,
                                    'total_qty' => $quantity,
                                    'unit' => $materialUnit
                                ];
                            }
                        }
                    }
                }
            }
        }
    }
    
    // If no materials found in requests, return error
    if (empty($materialsToInitialize)) {
        echo json_encode([
            'success' => false, 
            'message' => 'No approved material requests found for this site. Please ensure materials have been requested and approved.'
        ]);
        exit;
    }
    
    // Initialize materials in database
    $materialUsageModel->initializeInstallationMaterials($installationId, $materialsToInitialize);
    
    echo json_encode([
        'success' => true,
        'message' => 'Materials refreshed successfully from material requests',
        'materials_count' => count($materialsToInitialize)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error refreshing materials: ' . $e->getMessage()
    ]);
}
?>
