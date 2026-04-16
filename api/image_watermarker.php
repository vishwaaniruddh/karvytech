<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/image_watermark.php';
// echo __DIR__ . '/../image_watermark.php';
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);



Auth::requireRole(ADMIN_ROLE);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST request allowed');
    }

    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Image upload required');
    }

    $sourcePath = $_FILES['image']['tmp_name'];

    $lines = [];

    if (!empty($_POST['text'])) {
        $lines[] = trim($_POST['text']);
    }

    if (!empty($_POST['address'])) {
        $lines[] = trim($_POST['address']);
    }

    if (!empty($_POST['lat']) && !empty($_POST['lon'])) {
        $lines[] = trim($_POST['lat']) . ', ' . trim($_POST['lon']);
    } elseif (!empty($_POST['lat'])) {
        $lines[] = 'Lat: ' . trim($_POST['lat']);
    } elseif (!empty($_POST['lon'])) {
        $lines[] = 'Lon: ' . trim($_POST['lon']);
    }

    if (!empty($_POST['datetime'])) {
        $lines[] = trim($_POST['datetime']);
    } else {
        $lines[] = date('d-m-Y H:i:s');
    }

    $watermarkText = implode("\n", $lines);

    $userSetFontSize = isset($_POST['font_size']) && is_numeric($_POST['font_size']);
    $fontSize  = $userSetFontSize ? (int)$_POST['font_size'] : 28;
    $autoScale = !$userSetFontSize;

    $fontPath = __DIR__ . '/../arial.ttf';

    $targetDirectory = __DIR__ . '/../uploads/geotag_images/';

    if (!is_dir($targetDirectory)) {
        mkdir($targetDirectory, 0777, true);
    }

    $fileName = 'geotag_' . time() . '.jpg';
    $outputFile = $targetDirectory . $fileName;

    $success = addTextWatermark(
        $sourcePath,
        $watermarkText,
        $outputFile,
        $fontSize,
        $fontPath,
        [255, 255, 255],
        20,
        40,
        $autoScale
    );

    if ($success) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Watermark applied successfully',
            'saved_at' => '/uploads/geotag_images/' . $fileName,
            'image_url' => 'https://karvy.sarsspl.com/uploads/geotag_images/'. $fileName,
        ]);
    } else {
        throw new Exception('Watermark process failed');
    }

} catch (Exception $e) {

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);

}