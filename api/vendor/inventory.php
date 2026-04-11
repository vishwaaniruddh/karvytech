<?php
header('Content-Type: application/json');

require_once '../../config/database.php';
require_once '../../includes/jwt_helper.php';
require_once '../../models/Inventory.php';
require_once '../../models/MaterialRequest.php';
require_once '../../models/BoqItem.php';

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
$boqModel = new BoqItem();

try {

    /*
    |--------------------------------------------------------------------------
    | FETCH DATA
    |--------------------------------------------------------------------------
    */
    $receivedMaterials = $inventoryModel->getReceivedMaterialsForVendor($vendorId);
    $materialRequests  = $materialRequestModel->getVendorRequests($vendorId);

    /*
    |--------------------------------------------------------------------------
    | CALCULATE STATS
    |--------------------------------------------------------------------------
    */
    $stats = [
        'total_requests'     => count($materialRequests),
        'pending_acceptance' => 0,
        'delivered_materials'=> 0,
        'confirmed_materials'=> 0,
        'total_items'        => 0,
        'unique_materials'   => []
    ];

    foreach ($receivedMaterials as $material) {

        $status = $material['dispatch_status'] ?? 'delivered';

        if ($status === 'dispatched') $stats['pending_acceptance']++;
        if ($status === 'delivered')  $stats['delivered_materials']++;
        if ($status === 'confirmed')  $stats['confirmed_materials']++;

        // Fetch dispatch items
        $dispatchItems = $inventoryModel->getDispatchItems($material['id']);
        $stats['total_items'] += count($dispatchItems);

        foreach ($dispatchItems as $item) {
            $stats['unique_materials'][$item['boq_item_id']] = $item['item_name'];
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
        'stats' => [
            'total_requests'      => $stats['total_requests'],
            'pending_acceptance'  => $stats['pending_acceptance'],
            'delivered_materials' => $stats['delivered_materials'],
            'confirmed_materials' => $stats['confirmed_materials'],
            'total_items'         => $stats['total_items'],
            'unique_material_count'=> count($stats['unique_materials'])
        ],
        'recent_requests' => array_slice($materialRequests, 0, 5),
        'recent_received_materials' => array_slice($receivedMaterials, 0, 5)
    ]);
    exit;

} catch (Exception $e) {

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch inventory data',
        'error'   => $e->getMessage()
    ]);
    exit;
}
