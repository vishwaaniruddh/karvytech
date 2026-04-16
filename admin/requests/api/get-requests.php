<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../models/MaterialRequest.php';

header('Content-Type: application/json');

// Check authentication
Auth::requireRole(ADMIN_ROLE);

$materialRequestModel = new MaterialRequest();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;

$filters = [
    'status' => $_GET['status'] ?? '',
    'vendor_id' => $_GET['vendor_id'] ?? '',
    'site_id' => $_GET['site_id'] ?? '',
    'request_id' => $_GET['request_id'] ?? ''
];

try {
    $requestsData = $materialRequestModel->getAllWithPagination($page, $limit, $filters);
    $stats = $materialRequestModel->getStats();

    echo json_encode([
        'success' => true,
        'requests' => $requestsData['requests'],
        'pagination' => [
            'total' => $requestsData['total'],
            'page' => $requestsData['page'],
            'limit' => $requestsData['limit'],
            'pages' => $requestsData['pages']
        ],
        'stats' => $stats
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
