<?php
header('Content-Type: application/json');
error_reporting(E_ERROR | E_PARSE);

require_once '../../config/database.php';
require_once '../../includes/jwt_helper.php';
require_once '../../models/BoqItem.php';

/*
|--------------------------------------------------------------------------
| TOKEN
|--------------------------------------------------------------------------
*/
$headers = getallheaders();

if (empty($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Token missing']);
    exit;
}

$token = str_replace('Bearer ', '', $headers['Authorization']);
$userData = JWTHelper::validateToken($token);

if (!$userData || $userData['role'] !== 'vendor') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

/*
|--------------------------------------------------------------------------
| FETCH BOQ ITEMS
|--------------------------------------------------------------------------
*/
try {
    $boqModel = new BoqItem();
    $items = $boqModel->getActive();

    echo json_encode([
        'success' => true,
        'items' => $items
    ]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load BOQ items'
    ]);
    exit;
}
