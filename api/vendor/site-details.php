<?php
header('Content-Type: application/json');

// clean JSON output
error_reporting(E_ERROR | E_PARSE);

require_once '../../config/database.php';
require_once '../../includes/jwt_helper.php';
require_once '../../models/Site.php';
require_once '../../models/SiteDelegation.php';
require_once '../../models/DelegationLayout.php';

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
| INPUT
|--------------------------------------------------------------------------
*/
$delegationId = $_POST['id'] ?? null;

if (!$delegationId) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Delegation ID is required'
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| MODELS
|--------------------------------------------------------------------------
*/
$siteModel       = new Site();
$delegationModel = new SiteDelegation();
$layoutModel     = new DelegationLayout();

try {

    /*
    |--------------------------------------------------------------------------
    | FETCH VENDOR DELEGATIONS (ACTIVE + COMPLETED)
    |--------------------------------------------------------------------------
    */
    $active   = $delegationModel->getVendorDelegations($vendorId, 'active');
    $completed = $delegationModel->getVendorDelegations($vendorId, 'completed');

    $allDelegations = array_merge($active, $completed);

    $siteData   = null;
    $delegation = null;

    foreach ($allDelegations as $row) {
        if ($row['id'] == $delegationId) {

            $delegation = [
                'id'              => $row['id'],
                'vendor_id'       => $vendorId,
                'delegation_date' => $row['delegation_date'] ?? null,
                'status'          => $row['status'] ?? null,
                'notes'           => $row['notes'] ?? null
            ];

            // fetch site with relations
            $siteWithRelations = $siteModel->findWithRelations($row['site_id']);

            $siteData = array_merge($row, $siteWithRelations ?: []);
            break;
        }
    }

    if (!$siteData) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Site not found or not assigned to this vendor'
        ]);
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | FETCH LAYOUT FILES
    |--------------------------------------------------------------------------
    */
    $layouts = $layoutModel->getLayoutsByDelegation($delegation['id']);

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
        'site' => array_merge($siteData, [
            'delegation_id'     => $delegation['id'],
            'delegation_date'   => $delegation['delegation_date'],
            'delegation_status' => $delegation['status'],
            'notes'             => $delegation['notes'],
            'layout_files'      => $layouts
        ])
    ]);
    exit;

} catch (Exception $e) {

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load site details',
        'error' => $e->getMessage()
    ]);
    exit;
}
