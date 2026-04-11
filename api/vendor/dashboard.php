<?php
header('Content-Type: application/json');

require_once '../../config/database.php';
require_once '../../includes/jwt_helper.php';
require_once '../../models/SiteDelegation.php';

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

$delegationModel = new SiteDelegation();

try {
    // ========================
    // FETCH DELEGATIONS
    // ========================
    $activeDelegations = $delegationModel->getVendorDelegations($vendorId, 'active');
    $completedDelegations = $delegationModel->getVendorDelegations($vendorId, 'completed');

    // ========================
    // SURVEY STATS
    // ========================
    $pendingSurveys = count(array_filter($activeDelegations, function ($site) {
        return empty($site['survey_status']);
    }));

    $completedSurveys = count(array_filter($activeDelegations, function ($site) {
        return !empty($site['survey_status']);
    }));

    // ========================
    // INSTALLATION STATS
    // ========================
    $pendingInstallations = count(array_filter($activeDelegations, function ($site) {
        return empty($site['installation_status']);
    }));

    $completedInstallations = count(array_filter($activeDelegations, function ($site) {
        return !empty($site['installation_status']);
    }));

    // ========================
    // RECENT ACTIVITIES (LAST 5)
    // ========================
    $recentActivities = array_slice($activeDelegations, 0, 5);

    // ========================
    // RESPONSE
    // ========================
    echo json_encode([
        'success' => true,
        'vendor' => [
            'user_id' => $userData['user_id'],
            'username' => $userData['username'],
            'vendor_id' => $vendorId
        ],
        'stats' => [
            'active_sites' => count($activeDelegations),
            'completed_sites' => count($completedDelegations),
            'total_sites' => count($activeDelegations) + count($completedDelegations),

            'pending_surveys' => $pendingSurveys,
            'completed_surveys' => $completedSurveys,

            'pending_installations' => $pendingInstallations,
            'completed_installations' => $completedInstallations
        ],
        'recent_activities' => $recentActivities
    ]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Dashboard data fetch failed',
        'error' => $e->getMessage()
    ]);
    exit;
}
