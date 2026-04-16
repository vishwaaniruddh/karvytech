<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../models/Inventory.php';

// Auth check
if (!Auth::isLoggedIn() || !Auth::isVendor()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$vendorId = Auth::getVendorId();
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

$inventoryModel = new Inventory();
$data = $inventoryModel->getContractorReceivedMaterialsPaginated($vendorId, $page, $limit, $search, $status, $dateFrom, $dateTo);

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'data' => $data['materials'],
    'pagination' => [
        'page' => $page,
        'limit' => $limit,
        'total' => $data['total'],
        'pages' => $data['pages']
    ]
]);
