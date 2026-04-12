<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../config/database.php';

// Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$filename = $input['filename'] ?? '';

if (!$filename || strpos($filename, '..') !== false) {
    echo json_encode(['success' => false, 'message' => 'Invalid filename']);
    exit;
}

$filePath = __DIR__ . '/../../../uploads/tmp_images/' . $filename;

if (file_exists($filePath)) {
    unlink($filePath);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'File not found']);
}
