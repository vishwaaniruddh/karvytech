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

$installationModel = new Installation();

try {

    // ========================
    // FETCH INSTALLATIONS
    // ========================
    $installations = $installationModel->getVendorInstallations($vendorId);

    // ========================
    // STATUS COUNTS
    // ========================
    $stats = [
        'assigned'     => 0,
        'acknowledged' => 0,
        'in_progress'  => 0,
        'completed'    => 0,
        'on_hold'      => 0,
        'cancelled'    => 0,
        'total'        => count($installations)
    ];

    foreach ($installations as $row) {
        $status = $row['status'] ?? 'assigned';
        if (isset($stats[$status])) {
            $stats[$status]++;
        }
    }

    // ========================
    // RESPONSE
    // ========================
    echo json_encode([
        'success' => true,
        'vendor' => [
            'user_id'   => $userData['user_id'],
            'username'  => $userData['username'],
            'vendor_id' => $vendorId
        ],
        'stats' => $stats,
        'installations' => $installations
    ]);
    exit;

} catch (Exception $e) {

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch installations',
        'error' => $e->getMessage()
    ]);
    exit;
}
