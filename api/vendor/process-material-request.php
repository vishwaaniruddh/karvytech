<?php
header('Content-Type: application/json');
error_reporting(E_ERROR | E_PARSE);

require_once '../../config/database.php';
require_once '../../includes/jwt_helper.php';
require_once '../../models/MaterialRequest.php';
require_once '../../models/SiteSurvey.php';

/*
|--------------------------------------------------------------------------
| GET TOKEN
|--------------------------------------------------------------------------
*/
$headers = getallheaders();

if (empty($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authorization token missing']);
    exit;
}

$token = str_replace('Bearer ', '', $headers['Authorization']);

/*
|--------------------------------------------------------------------------
| VERIFY TOKEN
|--------------------------------------------------------------------------
*/
$userData = JWTHelper::validateToken($token);

if (!$userData || $userData['role'] !== 'vendor') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid or expired token']);
    exit;
}

$vendorId = (int)$userData['vendor_id'];

/*
|--------------------------------------------------------------------------
| INPUT (FORM DATA)
|--------------------------------------------------------------------------
*/
$siteId        = $_POST['site_id'] ?? null;
$surveyId      = $_POST['survey_id'] ?? null;
$requestDate   = $_POST['request_date'] ?? date('Y-m-d');
$requiredDate  = $_POST['required_date'] ?? null;
$requestNotes  = $_POST['request_notes'] ?? null;
$isDraft       = !empty($_POST['save_draft']);

/*
|--------------------------------------------------------------------------
| ITEMS (JSON STRING OR ARRAY)
|--------------------------------------------------------------------------
*/
$itemsRaw = $_POST['items'] ?? '[]';

if (is_string($itemsRaw)) {
    $items = json_decode($itemsRaw, true);
} else {
    $items = $itemsRaw;
}

if (!$siteId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Site ID is required']);
    exit;
}

/*
|--------------------------------------------------------------------------
| SURVEY VALIDATION
|--------------------------------------------------------------------------
*/
$surveyModel = new SiteSurvey();

if (!$surveyId) {
    $survey = $surveyModel->findBySiteAndVendor($siteId, $vendorId);
    if (!$survey) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No survey found for this site']);
        exit;
    }
    $surveyId = $survey['id'];
}

/*
|--------------------------------------------------------------------------
| ITEMS VALIDATION
|--------------------------------------------------------------------------
*/
if (!$isDraft && empty($items)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'At least one material item is required']);
    exit;
}

/*
|--------------------------------------------------------------------------
| PREPARE ITEMS
|--------------------------------------------------------------------------
*/
$cleanItems = [];

foreach ($items as $item) {
    if (!empty($item['boq_item_id']) && !empty($item['quantity'])) {
        $cleanItems[] = [
            'boq_item_id' => (int)$item['boq_item_id'],
            'item_code'   => $item['item_code'] ?? '',
            'quantity'    => (int)$item['quantity'],
            'unit'        => $item['unit'] ?? '',
            'notes'       => $item['notes'] ?? ''
        ];
    }
}

/*
|--------------------------------------------------------------------------
| SAVE REQUEST
|--------------------------------------------------------------------------
*/
$requestData = [
    'site_id'       => $siteId,
    'vendor_id'     => $vendorId,
    'survey_id'     => $surveyId,
    'request_date'  => $requestDate,
    'required_date' => $requiredDate,
    'request_notes' => $requestNotes,
    'status'        => $isDraft ? 'draft' : 'pending',
    'items'         => json_encode($cleanItems),
    'created_date'  => date('Y-m-d H:i:s')
];

try {
    $materialRequestModel = new MaterialRequest();
    $requestId = $materialRequestModel->create($requestData);

    if (!$requestId) {
        throw new Exception('Insert failed');
    }

    echo json_encode([
        'success' => true,
        'message' => $isDraft
            ? 'Material request draft saved'
            : 'Material request submitted',
        'request_id' => $requestId
    ]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save material request',
        'error'   => $e->getMessage()
    ]);
    exit;
}
