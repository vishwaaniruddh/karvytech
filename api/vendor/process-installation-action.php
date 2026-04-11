<?php
header('Content-Type: application/json');

require_once '../../config/database.php';
require_once '../../includes/jwt_helper.php';
require_once '../../models/Installation.php';

// ========================
// GET TOKEN
// ========================
$headers = getallheaders();

if (empty($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Authorization token missing'
    ]);
    exit;
}

$token = str_replace('Bearer ', '', $headers['Authorization']);

// ========================
// VERIFY TOKEN
// ========================
$userData = JWTHelper::validateToken($token);

if (!$userData) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid or expired token'
    ]);
    exit;
}

// ========================
// ROLE CHECK
// ========================
if ($userData['role'] !== 'vendor' || empty($userData['vendor_id'])) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Vendor access required'
    ]);
    exit;
}

$vendorId = (int)$userData['vendor_id'];
$currentUserId = (int)$userData['user_id'];

$installationModel = new Installation();

try {

    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || empty($input['action']) || empty($input['installation_id'])) {
        throw new Exception('Invalid request data');
    }

    $action = $input['action'];
    $installationId = (int)$input['installation_id'];

    // ========================
    // VERIFY INSTALLATION ACCESS
    // ========================
    $installation = $installationModel->getInstallationDetails($installationId);

    if (!$installation || (int)$installation['vendor_id'] !== $vendorId) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Access denied to this installation'
        ]);
        exit;
    }

    switch ($action) {

        // ========================
        // ACKNOWLEDGE
        // ========================
        case 'acknowledge':
            $result = $installationModel->updateInstallationStatus(
                $installationId,
                'acknowledged',
                $currentUserId
            );

            echo json_encode([
                'success' => (bool)$result,
                'message' => $result
                    ? 'Installation acknowledged successfully'
                    : 'Failed to acknowledge installation'
            ]);
            break;

        // ========================
        // UPDATE TIMINGS
        // ========================
        case 'update_timings':

            if (empty($input['arrival_time']) || empty($input['installation_start_time'])) {
                throw new Exception('Both arrival time and installation start time are required');
            }

            $arrivalTime = $input['arrival_time'];
            $installationStartTime = $input['installation_start_time'];

            $arrivalDateTime = DateTime::createFromFormat('Y-m-d\TH:i', $arrivalTime);
            $installationDateTime = DateTime::createFromFormat('Y-m-d\TH:i', $installationStartTime);

            if (!$arrivalDateTime || !$installationDateTime) {
                throw new Exception('Invalid datetime format');
            }

            if ($installationDateTime <= $arrivalDateTime) {
                throw new Exception('Installation start time must be after arrival time');
            }

            $result = $installationModel->updateInstallationTimings(
                $installationId,
                $arrivalDateTime->format('Y-m-d H:i:s'),
                $installationDateTime->format('Y-m-d H:i:s'),
                $currentUserId
            );

            echo json_encode([
                'success' => (bool)$result,
                'message' => $result
                    ? 'Timings updated successfully'
                    : 'Failed to update timings'
            ]);
            break;

        // ========================
        // PROCEED TO INSTALLATION
        // ========================
        case 'proceed_to_installation':

            if (!$installation['actual_start_date'] || !$installation['installation_start_time']) {
                throw new Exception('Please update arrival and installation start times first');
            }

            $result = $installationModel->updateInstallationStatus(
                $installationId,
                'in_progress',
                $currentUserId
            );

            echo json_encode([
                'success' => (bool)$result,
                'message' => $result
                    ? 'Installation started successfully'
                    : 'Failed to start installation'
            ]);
            break;

        // ========================
        // ADD PROGRESS
        // ========================
        case 'add_progress':

            if (empty($input['progress_percentage']) || empty($input['work_description'])) {
                throw new Exception('Progress percentage and work description are required');
            }

            $progressData = [
                'installation_id' => $installationId,
                'progress_percentage' => (float)$input['progress_percentage'],
                'work_description' => $input['work_description'],
                'issues_faced' => $input['issues_faced'] ?? null,
                'next_steps' => $input['next_steps'] ?? null,
                'updated_by' => $currentUserId
            ];

            $result = $installationModel->addInstallationProgressUpdate($progressData);

            echo json_encode([
                'success' => (bool)$result,
                'message' => $result
                    ? 'Progress updated successfully'
                    : 'Failed to update progress'
            ]);
            break;

        // ========================
        // COMPLETE INSTALLATION
        // ========================
        case 'complete_installation':

            $result = $installationModel->completeInstallation(
                $installationId,
                $currentUserId
            );

            echo json_encode([
                'success' => (bool)$result,
                'message' => $result
                    ? 'Installation completed successfully'
                    : 'Failed to complete installation'
            ]);
            break;

        default:
            throw new Exception('Invalid action');
    }

} catch (Exception $e) {

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
