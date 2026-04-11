<?php
header('Content-Type: application/json');
error_reporting(E_ERROR | E_PARSE);

require_once '../../config/database.php';
require_once '../../includes/jwt_helper.php';
require_once '../../models/SiteSurvey.php';
require_once '../../models/SiteDelegation.php';
require_once '../../models/Site.php';

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

$userId   = (int)$userData['user_id'];
$vendorId = (int)$userData['vendor_id'];

/*
|--------------------------------------------------------------------------
| METHOD CHECK
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| INPUT
|--------------------------------------------------------------------------
*/
$siteId       = $_POST['site_id'] ?? null;
$delegationId = $_POST['delegation_id'] ?? null;

if (!$siteId || !$delegationId) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'site_id and delegation_id are required'
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| VERIFY DELEGATION OWNERSHIP
|--------------------------------------------------------------------------
*/
$delegationModel = new SiteDelegation();
$delegation = $delegationModel->find($delegationId);

if (!$delegation || (int)$delegation['vendor_id'] !== $vendorId) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized delegation access'
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| FILE UPLOAD CONFIG
|--------------------------------------------------------------------------
*/
$photoFields = [
    'floor_height_photo'     => 'floor_height_photos',
    'ceiling_photo'          => 'ceiling_photos',
    'analytic_photos'        => 'analytic_photos',
    'existing_poe_photos'    => 'existing_poe_photos',
    'space_new_rack_photo'   => 'space_new_rack_photos',
    'new_poe_photos'         => 'new_poe_photos',
    'rrl_photos'             => 'rrl_photos',
    'kptl_photos'            => 'kptl_photos',
    'site_photos'            => 'site_photos'
];

$uploadedPhotos = [];
$uploadErrors   = [];

$year  = date('Y');
$month = date('m');
$uploadDir = __DIR__ . "/../../assets/uploads/surveys/$year/$month/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

/*
|--------------------------------------------------------------------------
| HANDLE FILES
|--------------------------------------------------------------------------
*/
foreach ($photoFields as $input => $dbField) {

    $uploadedPhotos[$dbField] = [];

    if (!isset($_FILES[$input])) {
        $uploadedPhotos[$dbField] = null;
        continue;
    }

    foreach ($_FILES[$input]['name'] as $i => $name) {

        if (!$name) continue;

        $tmp  = $_FILES[$input]['tmp_name'][$i];
        $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $size = $_FILES[$input]['size'][$i];

        if (!in_array($ext, ['jpg','jpeg','png'])) {
            $uploadErrors[] = "$name invalid file type";
            continue;
        }

        if ($size > 10 * 1024 * 1024) {
            $uploadErrors[] = "$name too large";
            continue;
        }

        $newName = uniqid($input.'_') . '.' . $ext;
        move_uploaded_file($tmp, $uploadDir . $newName);

        $uploadedPhotos[$dbField][] =
            "assets/uploads/surveys/$year/$month/$newName";
    }

    $uploadedPhotos[$dbField] =
        $uploadedPhotos[$dbField]
            ? json_encode($uploadedPhotos[$dbField])
            : null;
}

/*
|--------------------------------------------------------------------------
| PREPARE DATA
|--------------------------------------------------------------------------
*/
$data = [
    'site_id'       => $siteId,
    'vendor_id'     => $vendorId,
    'delegation_id' => $delegationId,
    'survey_status' => 'completed',
    'submitted_date'=> date('Y-m-d H:i:s'),
    'created_by'    => $userId,

    // timings
    'checkin_datetime'  => $_POST['checkin_datetime'] ?? null,
    'checkout_datetime' => $_POST['checkout_datetime'] ?? null,
    'working_hours'     => $_POST['working_hours'] ?? null,

    // site
    'store_model' => $_POST['store_model'] ?? null,

    // floor / ceiling
    'floor_height'         => $_POST['floor_height'] ?? null,
    'floor_height_photos'  => $uploadedPhotos['floor_height_photos'],
    'ceiling_type'         => $_POST['ceiling_type'] ?? null,
    'ceiling_photos'       => $uploadedPhotos['ceiling_photos'],

    // cameras
    'total_cameras'    => $_POST['total_cameras'] ?? null,
    'analytic_cameras' => $_POST['analytic_cameras'] ?? null,
    'analytic_photos'  => $uploadedPhotos['analytic_photos'],

    // poe
    'existing_poe_rack'   => $_POST['existing_poe_rack'] ?? null,
    'existing_poe_photos' => $uploadedPhotos['existing_poe_photos'],
    'space_new_rack'      => $_POST['space_new_rack'] ?? null,
    'space_new_rack_photos'=> $uploadedPhotos['space_new_rack_photos'],
    'new_poe_rack'        => $_POST['new_poe_rack'] ?? null,
    'new_poe_photos'      => $uploadedPhotos['new_poe_photos'],

    // misc
    'zones_recommended' => $_POST['zones_recommended'] ?? null,
    'rrl_delivery_status'=> $_POST['rrl_delivery_status'] ?? null,
    'rrl_photos'         => $uploadedPhotos['rrl_photos'],
    'kptl_space'         => $_POST['kptl_space'] ?? null,
    'kptl_photos'        => $uploadedPhotos['kptl_photos'],

    // technical
    'site_accessibility' => $_POST['site_accessibility'] ?? null,
    'power_availability' => $_POST['power_availability'] ?? null,
    'network_connectivity'=> $_POST['network_connectivity'] ?? null,
    'space_adequacy'     => $_POST['space_adequacy'] ?? null,

    // photos
    'site_photos' => $uploadedPhotos['site_photos'],

    // remarks
    'technical_remarks'      => $_POST['technical_remarks'] ?? null,
    'challenges_identified'  => $_POST['challenges_identified'] ?? null,
    'recommendations'        => $_POST['recommendations'] ?? null,
    'estimated_completion_days'=> $_POST['estimated_completion_days'] ?? null
];

/*
|--------------------------------------------------------------------------
| SAVE
|--------------------------------------------------------------------------
*/
$surveyModel = new SiteSurvey();
$surveyId = $surveyModel->createComprehensive($data);

if ($surveyId) {

    // update site + delegation
    $siteModel = new Site();
    $siteModel->updateSurveyStatus($siteId, true, date('Y-m-d H:i:s'));
    $delegationModel->updateStatus($delegationId, 'completed');

    echo json_encode([
        'success' => true,
        'message' => 'Survey submitted successfully',
        'survey_id' => $surveyId,
        'upload_warnings' => $uploadErrors
    ]);
    exit;
}

http_response_code(500);
echo json_encode([
    'success' => false,
    'message' => 'Survey submission failed',
    'upload_errors' => $uploadErrors
]);
exit;
