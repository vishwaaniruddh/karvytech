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
    echo json_encode(['success' => false, 'message' => 'Authorization token missing']);
    exit;
}

$token = str_replace('Bearer ', '', $headers['Authorization']);

// ========================
// VERIFY TOKEN
// ========================
$userData = JWTHelper::validateToken($token);

if (!$userData) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid or expired token']);
    exit;
}

// ========================
// ROLE + VENDOR CHECK
// ========================
if ($userData['role'] !== 'vendor' || empty($userData['vendor_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Vendor access required']);
    exit;
}

$vendorId = (int)$userData['vendor_id'];

$delegationModel = new SiteDelegation();

// ========================
// FETCH DATA
// ========================
$activeSites = $delegationModel->getVendorDelegations($vendorId, 'active');
$completedSites = $delegationModel->getVendorDelegations($vendorId, 'completed');

$sites = [];

foreach ($activeSites as $site) {
    $site['delegation_status'] = 'active';
    $sites[] = $site;
}

foreach ($completedSites as $site) {
    $site['delegation_status'] = 'completed';
    $sites[] = $site;
}

// ========================
// RESPONSE
// ========================
echo json_encode([
    'success' => true,
    'stats' => [
        'active' => count($activeSites),
        'completed' => count($completedSites),
        'total' => count($sites)
    ],
    'sites' => $sites
]);
exit;
