<?php
error_reporting(E_ERROR | E_PARSE);
header('Content-Type: application/json');


require_once '../../includes/jwt_helper.php';
require_once '../../models/Site.php';
require_once '../../models/SiteDelegation.php';
require_once '../../models/SiteSurvey.php';

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


$delegationId = $_POST['id'] ?? null;

if (!$delegationId) {
    echo json_encode(['success' => false, 'message' => 'delegation_id required']);
    exit;
}

$delegationModel = new SiteDelegation();
$siteModel = new Site();
$surveyModel = new SiteSurvey();

$delegation = $delegationModel->find($delegationId);

if (!$delegation || $delegation['vendor_id'] != $vendorId) {
    echo json_encode(['success' => false, 'message' => 'Invalid delegation']);
    exit;
}

$site = $siteModel->findWithRelations($delegation['site_id']);
$existingSurvey = $surveyModel->findByDelegation($delegationId);
$existingSurvey = !empty($existingSurvey) ? $existingSurvey[0] : null;

echo json_encode([
    'success' => true,
    'site' => $site,
    'delegation' => $delegation,
    'existing_survey' => $existingSurvey
]);
