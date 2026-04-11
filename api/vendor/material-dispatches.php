<?php
header('Content-Type: application/json');

require_once '../../config/database.php';
require_once '../../includes/jwt_helper.php';
require_once '../../models/MaterialRequest.php';

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
| MODEL
|--------------------------------------------------------------------------
*/
$materialRequestModel = new MaterialRequest();

try {

    /*
    |--------------------------------------------------------------------------
    | FETCH DISPATCHED REQUESTS
    |--------------------------------------------------------------------------
    */
    $dispatches = $materialRequestModel->getDispatchedRequestsForVendor($vendorId);

    /*
    |--------------------------------------------------------------------------
    | STATS
    |--------------------------------------------------------------------------
    */
    $stats = [
        'total_dispatches' => count($dispatches),
        'pending' => 0,
        'delivered' => 0,
        'confirmed' => 0
    ];

    foreach ($dispatches as $dispatch) {
        $status = $dispatch['dispatch_status'] ?? 'pending';

        if (isset($stats[$status])) {
            $stats[$status]++;
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
        'dispatches' => $dispatches
    ]);
    exit;

} catch (Exception $e) {

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch material dispatches',
        'error' => $e->getMessage()
    ]);
    exit;
}
