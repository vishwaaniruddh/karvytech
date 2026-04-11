<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../models/MaterialUsage.php';
require_once __DIR__ . '/../models/Installation.php';

// Require vendor authentication
Auth::requireVendor();

$vendorId = Auth::getVendorId();
$currentUser = Auth::getCurrentUser();

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['action']) || !isset($input['installation_id'])) {
        throw new Exception('Invalid request data');
    }
    
    $action = $input['action'];
    $installationId = (int)$input['installation_id'];
    
    $installationModel = new Installation();
    $materialUsageModel = new MaterialUsage();
    
    // Verify vendor access to this installation
    $installation = $installationModel->getInstallationDetails($installationId);
    if (!$installation || $installation['vendor_id'] != $vendorId) {
        throw new Exception('Access denied');
    }
    
    switch ($action) {
        case 'initialize_materials':
            // Initialize materials for the installation (first time setup)
            if (!isset($input['materials'])) {
                throw new Exception('Materials data is required');
            }
            
            $result = $materialUsageModel->initializeInstallationMaterials($installationId, $input['materials']);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Materials initialized successfully']);
            } else {
                throw new Exception('Failed to initialize materials');
            }
            break;
            
        case 'save_daily_work':
            // Save daily work progress
            if (!isset($input['day_number']) || !isset($input['work_date']) || !isset($input['material_usage'])) {
                throw new Exception('Day number, work date, and material usage are required');
            }
            
            $result = $materialUsageModel->saveDailyMaterialUsage(
                $installationId,
                $input['day_number'],
                $input['work_date'],
                $input['engineer_name'] ?? '',
                $input['material_usage'],
                $input['remarks'] ?? '',
                $input['report'] ?? ''
            );
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Daily work saved successfully']);
            } else {
                throw new Exception('Failed to save daily work');
            }
            break;
            
        case 'checkout_day':
            // Check out a day (update main material quantities)
            if (!isset($input['day_number'])) {
                throw new Exception('Day number is required');
            }
            
            $dayNumber = (int)$input['day_number'];
            
            // Check if day is already checked out
            if ($materialUsageModel->isDayCheckedOut($installationId, $dayNumber)) {
                throw new Exception('Day ' . $dayNumber . ' is already checked out');
            }
            
            $result = $materialUsageModel->checkoutDay($installationId, $dayNumber);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Day checked out successfully']);
            } else {
                throw new Exception('Failed to check out day');
            }
            break;
            
        case 'get_materials':
            // Get current material status
            $materials = $materialUsageModel->getInstallationMaterials($installationId);
            echo json_encode(['success' => true, 'materials' => $materials]);
            break;
            
        case 'get_daily_work':
            // Get daily work data
            $dailyWork = $materialUsageModel->getDailyWorkByDay($installationId);
            echo json_encode(['success' => true, 'daily_work' => $dailyWork]);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>