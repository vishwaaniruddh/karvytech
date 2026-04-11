<?php
// ========================
// DEBUG (temporary – prod me off kar dena)
// ========================
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once '../../config/database.php';
require_once '../../includes/jwt_helper.php';
require_once '../../models/SiteSurvey.php';

/* ========================
   GET AUTH HEADER (CASE SAFE)
======================== */
$headers = getallheaders();
$authHeader = '';

if (isset($headers['Authorization'])) {
    $authHeader = $headers['Authorization'];
} elseif (isset($headers['authorization'])) {
    $authHeader = $headers['authorization'];
}

if (!$authHeader) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Authorization token missing'
    ]);
    exit;
}

$token = str_replace('Bearer ', '', $authHeader);

/* ========================
   VERIFY JWT (UNCHANGED)
======================== */
$userData = JWTHelper::validateToken($token);

if (!$userData) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid or expired token'
    ]);
    exit;
}

/* ========================
   ROLE CHECK (VENDOR)
======================== */
if ($userData['role'] !== 'vendor' || empty($userData['vendor_id'])) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Vendor access required'
    ]);
    exit;
}

$vendorId = (int)$userData['vendor_id'];

/* ========================
   REQUIRED FIELDS
======================== */
$delegationId = $_POST['delegation_id'] ?? null;
$siteId       = $_POST['site_id'] ?? null;

if (!$delegationId || !$siteId) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields'
    ]);
    exit;
}

/* ========================
   PREPARE DATA
======================== */
$data = [
    'vendor_id'         => $vendorId,
    'site_id'           => $siteId,
    'delegation_id'     => $delegationId,
    'store_model'       => $_POST['store_model'] ?? '',
    'floor_height'      => $_POST['floor_height'] ?? '',
    'ceiling_type'      => $_POST['ceiling_type'] ?? '',
    'total_cameras'     => $_POST['total_cameras'] ?? '',
    'technical_remarks' => $_POST['technical_remarks'] ?? '',
    'recommendations'   => $_POST['recommendations'] ?? '',
    'working_hours'     => $_POST['working_hours'] ?? '',
    'checkin_datetime'  => $_POST['checkin_datetime'] ?? '',
    'checkout_datetime' => $_POST['checkout_datetime'] ?? '',
    'estimated_completion_days' => $_POST['estimated_completion_days'] ?? '',
    'submitted_date'    => date('Y-m-d H:i:s'),
];

/* ========================
   SAVE SURVEY
======================== */
try {
    $surveyModel = new SiteSurvey();
    $surveyId = $surveyModel->create($data);

    /* ========================
       IMAGE UPLOADS (SAFE)
    ======================== */
    $imageMap = [
        'floor_height_photo'     => 'floor_height',
        'ceiling_photos'         => 'ceiling_type',
        'analytic_photos'        => 'analytic_cameras',
        'existing_poe_photos'    => 'existing_poe_rack',
        'space_new_rack_photos'  => 'space_new_rack',
        'new_poe_photos'         => 'new_poe_rack',
        'rrl_photos'             => 'rrl_delivery_status',
        'kptl_photos'            => 'kptl_space',
        'site_photos'            => null,
    ];

    foreach ($imageMap as $field => $type) {
        if (!empty($_FILES[$field])) {
            $surveyModel->uploadImages($surveyId, $_FILES[$field], $type);
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Survey submitted successfully'
    ]);
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Survey submission failed',
        'error' => $e->getMessage()
    ]);
    exit;
}



// <?php
// error_reporting(E_ERROR | E_PARSE);
// header('Content-Type: application/json');

// require_once '../../config/database.php';
// require_once '../../includes/jwt_helper.php';
// require_once '../../models/SiteSurvey.php';

// /* ========================
//   GET AUTH HEADER
// ======================== */
// $headers = getallheaders();

// if (empty($headers['Authorization'])) {
//     http_response_code(401);
//     echo json_encode([
//         'success' => false,
//         'message' => 'Authorization token missing'
//     ]);
//     exit;
// }

// $token = str_replace('Bearer ', '', $headers['Authorization']);

// /* ========================
//   VERIFY JWT
// ======================== */
// $userData = JWTHelper::validateToken($token);

// if (!$userData) {
//     http_response_code(401);
//     echo json_encode([
//         'success' => false,
//         'message' => 'Invalid or expired token'
//     ]);
//     exit;
// }

// /* ========================
//   ROLE CHECK (VENDOR)
// ======================== */
// if ($userData['role'] !== 'vendor' || empty($userData['vendor_id'])) {
//     http_response_code(403);
//     echo json_encode([
//         'success' => false,
//         'message' => 'Vendor access required'
//     ]);
//     exit;
// }

// $vendorId = (int)$userData['vendor_id'];

// /* ========================
//   REQUIRED FIELDS
// ======================== */
// $delegationId = $_POST['delegation_id'] ?? null;
// $siteId       = $_POST['site_id'] ?? null;

// if (!$delegationId || !$siteId) {
//     echo json_encode([
//         'success' => false,
//         'message' => 'Missing required fields'
//     ]);
//     exit;
// }

// /* ========================
//   PREPARE DATA
// ======================== */
// $data = [
//     'vendor_id'         => $vendorId,
//     'site_id'           => $siteId,
//     'delegation_id'     => $delegationId,
//     'store_model'       => $_POST['store_model'] ?? '',
//     'floor_height'      => $_POST['floor_height'] ?? '',
//     'ceiling_type'      => $_POST['ceiling_type'] ?? '',
//     'total_cameras'     => $_POST['total_cameras'] ?? '',
//     'technical_remarks' => $_POST['technical_remarks'] ?? '',
//     'recommendations'   => $_POST['recommendations'] ?? '',
//     'working_hours'     => $_POST['working_hours'] ?? '',
//     'checkin_datetime'  => $_POST['checkin_datetime'] ?? '',
//     'checkout_datetime' => $_POST['checkout_datetime'] ?? '',
//     'submitted_date'    => date('Y-m-d H:i:s'),
// ];

// /* ========================
//   SAVE SURVEY
// ======================== */
// try {
//     $surveyModel = new SiteSurvey();
//     $surveyId = $surveyModel->create($data);

//     /* ========================
//       IMAGE UPLOADS
//     ======================== */
//     if (!empty($_FILES['floor_height_photo'])) {
//         $surveyModel->uploadImages($surveyId, $_FILES['floor_height_photo'], 'floor_height');
//     }
//     if (!empty($_FILES['ceiling_photos'])) {
//         $surveyModel->uploadImages($surveyId, $_FILES['ceiling_photos'], 'ceiling_type');
//     }
//     if (!empty($_FILES['analytic_photos'])) {
//         $surveyModel->uploadImages($surveyId, $_FILES['analytic_photos'], 'analytic_cameras');
//     }
//     if (!empty($_FILES['existing_poe_photos'])) {
//         $surveyModel->uploadImages($surveyId, $_FILES['existing_poe_photos'], 'existing_poe_rack');
//     }
//     if (!empty($_FILES['space_new_rack_photos'])) {
//         $surveyModel->uploadImages($surveyId, $_FILES['space_new_rack_photos'], 'space_new_rack');
//     }
//     if (!empty($_FILES['new_poe_photos'])) {
//         $surveyModel->uploadImages($surveyId, $_FILES['new_poe_photos'], 'new_poe_rack');
//     }
//     if (!empty($_FILES['rrl_photos'])) {
//         $surveyModel->uploadImages($surveyId, $_FILES['rrl_photos'], 'rrl_delivery_status');
//     }
//     if (!empty($_FILES['kptl_photos'])) {
//         $surveyModel->uploadImages($surveyId, $_FILES['kptl_photos'], 'kptl_space');
//     }
//     if (!empty($_FILES['site_photos'])) {
//         $surveyModel->uploadImages($surveyId, $_FILES['site_photos']);
//     }

//     echo json_encode([
//         'success' => true,
//         'message' => 'Survey submitted successfully'
//     ]);
//     exit;

// } catch (Exception $e) {
//     http_response_code(500);
//     echo json_encode([
//         'success' => false,
//         'message' => 'Survey submission failed',
//         'error' => $e->getMessage()
//     ]);
//     exit;
// }
