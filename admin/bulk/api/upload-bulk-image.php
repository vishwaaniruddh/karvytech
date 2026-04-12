<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../config/database.php';

// Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

if (!isset($_FILES['image'])) {
    echo json_encode(['success' => false, 'message' => 'No image uploaded']);
    exit;
}

$file = $_FILES['image'];
$uploadDir = __DIR__ . '/../../../uploads/tmp_images/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = time() . '_' . rand(1000, 9999) . '.' . $ext;
$targetPath = $uploadDir . $filename;

if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    echo json_encode(['success' => true, 'filename' => $filename]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
}
