<?php
require_once __DIR__ . '/../config/auth.php';
// constants.php is already included by auth.php
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
    
    // Verify vendor access to this installation
    $installation = $installationModel->getInstallationDetails($installationId);
    if (!$installation || $installation['vendor_id'] != $vendorId) {
        throw new Exception('Access denied');
    }
    
    switch ($action) {
        case 'acknowledge':
            $result = $installationModel->updateInstallationStatus($installationId, 'acknowledged', $currentUser['id']);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Installation acknowledged successfully']);
            } else {
                throw new Exception('Failed to acknowledge installation');
            }
            break;
            
        case 'update_timings':
            if (!isset($input['arrival_time']) || !isset($input['installation_start_time'])) {
                throw new Exception('Both arrival time and installation start time are required');
            }
            
            $arrivalTime = $input['arrival_time'];
            $installationStartTime = $input['installation_start_time'];
            
            // Validate datetime format
            $arrivalDateTime = DateTime::createFromFormat('Y-m-d\TH:i', $arrivalTime);
            $installationDateTime = DateTime::createFromFormat('Y-m-d\TH:i', $installationStartTime);
            
            if (!$arrivalDateTime || !$installationDateTime) {
                throw new Exception('Invalid datetime format');
            }
            
            // Installation start time should be after arrival time
            if ($installationDateTime <= $arrivalDateTime) {
                throw new Exception('Installation start time must be after arrival time');
            }
            
            $result = $installationModel->updateInstallationTimings(
                $installationId, 
                $arrivalDateTime->format('Y-m-d H:i:s'),
                $installationDateTime->format('Y-m-d H:i:s'),
                $currentUser['id']
            );
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Timings updated successfully']);
            } else {
                throw new Exception('Failed to update timings');
            }
            break;
            
        case 'proceed_to_installation':
            // Check if timings are set
            if (!$installation['actual_start_date'] || !$installation['installation_start_time']) {
                throw new Exception('Please update arrival and installation start times first');
            }
            
            $result = $installationModel->updateInstallationStatus($installationId, 'in_progress', $currentUser['id']);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Installation started successfully']);
            } else {
                throw new Exception('Failed to start installation');
            }
            break;
            
        case 'add_progress':
            if (!isset($input['progress_percentage']) || !isset($input['work_description'])) {
                throw new Exception('Progress percentage and work description are required');
            }
            
            $progressData = [
                'installation_id' => $installationId,
                'progress_percentage' => (float)$input['progress_percentage'],
                'work_description' => $input['work_description'],
                'issues_faced' => $input['issues_faced'] ?? null,
                'next_steps' => $input['next_steps'] ?? null,
                'updated_by' => $currentUser['id']
            ];
            
            $result = $installationModel->addInstallationProgressUpdate($progressData);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Progress updated successfully']);
            } else {
                throw new Exception('Failed to update progress');
            }
            break;
            
        case 'complete_installation':
            $result = $installationModel->completeInstallation($installationId, $currentUser['id']);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Installation completed successfully']);
            } else {
                throw new Exception('Failed to complete installation');
            }
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>