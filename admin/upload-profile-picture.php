<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/User.php';

// Set JSON response header
header('Content-Type: application/json');

// Require admin authentication
try {
    Auth::requireRole(ADMIN_ROLE);
    $currentUser = Auth::getCurrentUser();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
    exit;
}

$file = $_FILES['profile_picture'];

// Validate file type
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
$fileType = mime_content_type($file['tmp_name']);

if (!in_array($fileType, $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and GIF are allowed']);
    exit;
}

// Validate file size (2MB max)
$maxSize = 2 * 1024 * 1024; // 2MB in bytes
if ($file['size'] > $maxSize) {
    echo json_encode(['success' => false, 'message' => 'File size too large. Maximum 2MB allowed']);
    exit;
}

// Create uploads directory if it doesn't exist
$uploadDir = __DIR__ . '/../uploads/profiles/';
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        echo json_encode(['success' => false, 'message' => 'Failed to create upload directory']);
        exit;
    }
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'profile_' . $currentUser['id'] . '_' . time() . '.' . $extension;
$filepath = $uploadDir . $filename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    echo json_encode(['success' => false, 'message' => 'Failed to save uploaded file']);
    exit;
}

// Update user profile picture in database
$userModel = new User();
$user = $userModel->find($currentUser['id']);

// Delete old profile picture if exists
if (!empty($user['profile_picture'])) {
    $oldFile = $uploadDir . $user['profile_picture'];
    if (file_exists($oldFile)) {
        unlink($oldFile);
    }
}

// Update database
$updateData = [
    'profile_picture' => $filename,
    'updated_at' => date('Y-m-d H:i:s')
];

if ($userModel->update($currentUser['id'], $updateData)) {
    // Update session data
    $updatedUser = $userModel->find($currentUser['id']);
    Auth::updateSession($updatedUser);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Profile picture updated successfully',
        'filename' => $filename
    ]);
} else {
    // Delete uploaded file if database update fails
    if (file_exists($filepath)) {
        unlink($filepath);
    }
    echo json_encode(['success' => false, 'message' => 'Failed to update profile picture in database']);
}
?>