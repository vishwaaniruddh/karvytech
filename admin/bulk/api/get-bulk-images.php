<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../config/database.php';

// Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

$uploadDir = __DIR__ . '/../../../uploads/tmp_images/';
$images = [];

// Determine base URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$basePath = str_replace('admin/bulk/api/get-bulk-images.php', '', $_SERVER['PHP_SELF']);
$baseUrl = $protocol . $host . $basePath;

if (is_dir($uploadDir)) {
    $files = scandir($uploadDir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $images[] = [
            'filename' => $file,
            'path' => 'uploads/tmp_images/' . $file,
            'url' => $baseUrl . 'uploads/tmp_images/' . $file
        ];
    }
}

// Sort newest first
usort($images, function($a, $b) {
    return filemtime(__DIR__ . '/../../../' . $b['path']) - filemtime(__DIR__ . '/../../../' . $a['path']);
});

echo json_encode(['success' => true, 'images' => $images]);
