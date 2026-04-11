<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../models/Installation.php';

// Require vendor authentication
Auth::requireVendor();

$vendorId = Auth::getVendorId();

header('Content-Type: application/json');

try {
    if (!isset($_POST['installation_id']) || !isset($_POST['day_number'])) {
        throw new Exception('Installation ID and day number are required');
    }
    
    $installationId = (int)$_POST['installation_id'];
    $dayNumber = (int)$_POST['day_number'];
    $materialId = isset($_POST['material_id']) ? (int)$_POST['material_id'] : null;
    
    $installationModel = new Installation();
    
    // Verify vendor access to this installation
    $installation = $installationModel->getInstallationDetails($installationId);
    if (!$installation || $installation['vendor_id'] != $vendorId) {
        throw new Exception('Access denied');
    }
    
    // Get site ID for folder structure
    $siteId = $installation['site_id'];
    
    // Create upload directory structure: assets/installation/siteid/day{dayNumber}
    $uploadDir = __DIR__ . "/../assets/installation/{$siteId}/day{$dayNumber}";
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            throw new Exception('Failed to create upload directory');
        }
    }
    
    $uploadedFiles = [];
    
    if (!empty($_FILES['files']['name'][0])) {
        $fileCount = count($_FILES['files']['name']);
        
        for ($i = 0; $i < $fileCount; $i++) {
            if ($_FILES['files']['error'][$i] === UPLOAD_ERR_OK) {
                $fileName = $_FILES['files']['name'][$i];
                $tmpName = $_FILES['files']['tmp_name'][$i];
                $fileSize = $_FILES['files']['size'][$i];
                $fileType = $_FILES['files']['type'][$i];
                
                // Validate file type
                $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'video/mp4', 'video/avi', 'video/mov'];
                if (!in_array($fileType, $allowedTypes)) {
                    continue; // Skip invalid files
                }
                
                // Generate unique filename
                $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
                $uniqueFileName = time() . '_' . $i . '_' . uniqid() . '.' . $fileExtension;
                
                // Add material prefix if it's a material-specific photo
                if ($materialId) {
                    $uniqueFileName = "material_{$materialId}_" . $uniqueFileName;
                }
                
                $targetPath = $uploadDir . '/' . $uniqueFileName;
                
                if (move_uploaded_file($tmpName, $targetPath)) {
                    $uploadedFiles[] = [
                        'original_name' => $fileName,
                        'file_name' => $uniqueFileName,
                        'file_path' => "assets/installation/{$siteId}/day{$dayNumber}/{$uniqueFileName}",
                        'file_size' => $fileSize,
                        'file_type' => strpos($fileType, 'image') !== false ? 'image' : 'video',
                        'material_id' => $materialId,
                        'url' => BASE_URL . "/assets/installation/{$siteId}/day{$dayNumber}/{$uniqueFileName}"
                    ];
                }
            }
        }
    }
    
    if (empty($uploadedFiles)) {
        throw new Exception('No files were uploaded successfully');
    }
    
    // TODO: Save file information to database (daily_work_photos table)
    // For now, just return the uploaded file information
    
    echo json_encode([
        'success' => true,
        'message' => count($uploadedFiles) . ' file(s) uploaded successfully',
        'files' => $uploadedFiles
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>