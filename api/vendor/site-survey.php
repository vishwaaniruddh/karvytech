<?php
header('Content-Type: application/json');
error_reporting(E_ERROR | E_PARSE);

require_once '../../config/database.php';
require_once '../../includes/jwt_helper.php';
require_once '../../models/SiteSurvey.php';

/*
|--------------------------------------------------------------------------
| TOKEN
|--------------------------------------------------------------------------
*/
$headers = getallheaders();
$token = str_replace('Bearer ', '', $headers['Authorization'] ?? '');

$userData = JWTHelper::validateToken($token);

if (!$userData || $userData['role'] !== 'vendor') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$vendorId = (int)$userData['vendor_id'];

/*
|--------------------------------------------------------------------------
| INPUT
|--------------------------------------------------------------------------
*/
$siteId = $_GET['site_id'] ?? null;

if (!$siteId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'site_id required']);
    exit;
}

/*
|--------------------------------------------------------------------------
| FETCH SURVEY
|--------------------------------------------------------------------------
*/
$surveyModel = new SiteSurvey();
$survey = $surveyModel->findBySiteAndVendor($siteId, $vendorId);

if (!$survey) {
    echo json_encode([
        'success' => true,
        'survey' => null,
        'message' => 'No survey found'
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'survey' => $survey
]);
exit;
