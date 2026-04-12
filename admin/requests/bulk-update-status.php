<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/MaterialRequest.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['request_ids']) || empty($input['status'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$requestIds = $input['request_ids'];
$status = $input['status'];
$processedBy = Auth::getUserId();

$materialRequestModel = new MaterialRequest();
$successCount = 0;
$errors = [];

foreach ($requestIds as $id) {
    if ($materialRequestModel->updateStatus($id, $status, $processedBy, date('Y-m-d H:i:s'))) {
        $successCount++;
    } else {
        $errors[] = "Failed to update Request #$id";
    }
}

echo json_encode([
    'success' => $successCount > 0,
    'message' => "$successCount requests updated to $status",
    'errors' => $errors
]);
