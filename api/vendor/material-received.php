<?php
header('Content-Type: application/json');

require_once '../../config/database.php';
require_once '../../includes/jwt_helper.php';
require_once '../../models/MaterialRequest.php';
require_once '../../models/Inventory.php';

/*
|--------------------------------------------------------------------------
| GET TOKEN
|--------------------------------------------------------------------------
*/
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

/*
|--------------------------------------------------------------------------
| VERIFY TOKEN
|--------------------------------------------------------------------------
*/
$userData = JWTHelper::validateToken($token);

if (!$userData) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid or expired token'
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| ROLE CHECK
|--------------------------------------------------------------------------
*/
if ($userData['role'] !== 'vendor' || empty($userData['vendor_id'])) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Vendor access required'
    ]);
    exit;
}

$vendorId = (int)$userData['vendor_id'];

/*
|--------------------------------------------------------------------------
| MODELS
|--------------------------------------------------------------------------
*/
$inventoryModel = new Inventory();
$materialRequestModel = new MaterialRequest();

try {

    /*
    |--------------------------------------------------------------------------
    | FETCH RECEIVED MATERIALS
    |--------------------------------------------------------------------------
    */
    $receivedMaterials = $inventoryModel->getReceivedMaterialsForVendor($vendorId);

    /*
    |--------------------------------------------------------------------------
    | GROUP BY MATERIAL REQUEST
    |--------------------------------------------------------------------------
    */
    $materialsByRequest = [];

    foreach ($receivedMaterials as $material) {
        $requestId = $material['material_request_id'] ?? 'no_request';

        if (!isset($materialsByRequest[$requestId])) {
            $materialsByRequest[$requestId] = [
                'request_info' => null,
                'dispatches' => []
            ];
        }

        // attach request info once
        if (
            $requestId !== 'no_request' &&
            $materialsByRequest[$requestId]['request_info'] === null
        ) {
            $materialsByRequest[$requestId]['request_info']
                = $materialRequestModel->findWithDetails($requestId);
        }

        $materialsByRequest[$requestId]['dispatches'][] = $material;
    }

    /*
    |--------------------------------------------------------------------------
    | STATS (dashboard use ke liye)
    |--------------------------------------------------------------------------
    */
    $stats = [
        'total_requests' => count($materialsByRequest),
        'total_dispatches' => count($receivedMaterials),
        'delivered' => 0,
        'confirmed' => 0
    ];

    foreach ($receivedMaterials as $m) {
        if (($m['dispatch_status'] ?? '') === 'delivered') {
            $stats['delivered']++;
        }
        if (($m['dispatch_status'] ?? '') === 'confirmed') {
            $stats['confirmed']++;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | RESPONSE
    |--------------------------------------------------------------------------
    */
    echo json_encode([
        'success' => true,
        'vendor' => [
            'user_id'   => $userData['user_id'],
            'username'  => $userData['username'],
            'vendor_id' => $vendorId
        ],
        'stats' => $stats,
        'materials_received' => $materialsByRequest
    ]);
    exit;

} catch (Exception $e) {

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch received materials',
        'error' => $e->getMessage()
    ]);
    exit;
}
