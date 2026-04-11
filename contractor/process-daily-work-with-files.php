<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../models/MaterialUsage.php';
require_once __DIR__ . '/../models/Installation.php';

// Require vendor authentication
Auth::requireVendor();

$vendorId = Auth::getVendorId();
$currentUser = Auth::getCurrentUser();

header('Content-Type: application/json');

// Log incoming request for debugging
$logFile = __DIR__ . '/../logs/daily_work_upload.log';
$logData = [
    'timestamp' => date('Y-m-d H:i:s'),
    'POST' => $_POST,
    'FILES' => array_map(function($file) {
        return [
            'name' => $file['name'] ?? 'N/A',
            'type' => $file['type'] ?? 'N/A',
            'size' => $file['size'] ?? 'N/A',
            'error' => $file['error'] ?? 'N/A'
        ];
    }, $_FILES)
];
file_put_contents($logFile, json_encode($logData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);

try {
    if (!isset($_POST['action']) || !isset($_POST['installation_id'])) {
        throw new Exception('Invalid request data');
    }
    
    $action = $_POST['action'];
    $installationId = (int)$_POST['installation_id'];
    
    $installationModel = new Installation();
    $materialUsageModel = new MaterialUsage();
    
    // Verify vendor access to this installation
    $installation = $installationModel->getInstallationDetails($installationId);
    if (!$installation || $installation['vendor_id'] != $vendorId) {
        throw new Exception('Access denied');
    }
    
    if ($action === 'save_daily_work_with_files') {
        // Validate required fields
        if (!isset($_POST['day_number']) || !isset($_POST['work_date'])) {
            throw new Exception('Day number and work date are required');
        }
        
        $dayNumber = (int)$_POST['day_number'];
        $workDate = $_POST['work_date'];
        $engineerName = $_POST['engineer_name'] ?? '';
        $remarks = $_POST['remarks'] ?? '';
        $report = $_POST['report'] ?? '';
        
        // Parse material usage JSON
        $materialUsage = [];
        if (isset($_POST['material_usage'])) {
            $materialUsage = json_decode($_POST['material_usage'], true);
            if (!is_array($materialUsage)) {
                $materialUsage = [];
            }
        }
        
        // Save daily work data (this handles its own transaction)
        $result = $materialUsageModel->saveDailyMaterialUsage(
            $installationId,
            $dayNumber,
            $workDate,
            $engineerName,
            $materialUsage,
            $remarks,
            $report
        );
        
        if (!$result) {
            throw new Exception('Failed to save daily work');
        }
        
        // Handle file uploads separately
        $uploadDir = __DIR__ . '/../assets/installation_progress/' . $installationId . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $uploadedFiles = [];
        $pdo = Database::getInstance()->getConnection();
        
        // Handle site photos/videos
        if (isset($_FILES['site_files']) && !empty($_FILES['site_files']['name'][0])) {
            $files = $_FILES['site_files'];
            $fileCount = count($files['name']);
            
            for ($i = 0; $i < $fileCount; $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $originalName = $files['name'][$i];
                    $tmpName = $files['tmp_name'][$i];
                    $fileSize = $files['size'][$i];
                    $mimeType = $files['type'][$i];
                    
                    // Generate unique filename
                    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                    $uniqueName = 'day' . $dayNumber . '_site_' . time() . '_' . $i . '_' . uniqid() . '.' . $extension;
                    $filePath = $uploadDir . $uniqueName;
                    $relativePath = 'assets/installation_progress/' . $installationId . '/' . $uniqueName;
                    
                    // Validate file size (max 10MB)
                    if ($fileSize > 10 * 1024 * 1024) {
                        throw new Exception("File {$originalName} is too large. Maximum size is 10MB.");
                    }
                    
                    // Move uploaded file
                    if (move_uploaded_file($tmpName, $filePath)) {
                        // Save to database
                        try {
                            $stmt = $pdo->prepare("
                                INSERT INTO installation_progress_attachments 
                                (installation_id, progress_id, day_number, attachment_type, file_name, original_name, file_path, file_type, file_size, mime_type, uploaded_by, description) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                            ");
                            
                            $fileType = strpos($mimeType, 'video') !== false ? 'video' : 'photo';
                            $description = "Day {$dayNumber} - Site Progress";
                            
                            $insertResult = $stmt->execute([
                                $installationId,
                                null, // progress_id - we'll link to daily work instead
                                $dayNumber, // day_number
                                'daily_work_site',
                                $uniqueName,
                                $originalName,
                                $relativePath,
                                $fileType,
                                $fileSize,
                                $mimeType,
                                $currentUser['id'],
                                $description
                            ]);
                            
                            if (!$insertResult) {
                                $errorInfo = $stmt->errorInfo();
                                file_put_contents($logFile, "DB Insert Error: " . json_encode($errorInfo) . "\n", FILE_APPEND);
                                throw new Exception("Failed to save file metadata to database: " . $errorInfo[2]);
                            }
                            
                            $uploadedFiles[] = [
                                'type' => 'site',
                                'name' => $originalName,
                                'path' => $relativePath
                            ];
                        } catch (PDOException $e) {
                            file_put_contents($logFile, "PDO Exception: " . $e->getMessage() . "\n", FILE_APPEND);
                            throw new Exception("Database error: " . $e->getMessage());
                        }
                    } else {
                        throw new Exception("Failed to move uploaded file: {$originalName}");
                    }
                }
            }
        }
        
        // Handle individual material photos
        if (isset($_FILES['material_photos'])) {
            foreach ($_FILES['material_photos']['name'] as $materialId => $fileNames) {
                if (empty($fileNames[0])) continue;
                
                $fileCount = count($fileNames);
                for ($i = 0; $i < $fileCount; $i++) {
                    if ($_FILES['material_photos']['error'][$materialId][$i] === UPLOAD_ERR_OK) {
                        $originalName = $_FILES['material_photos']['name'][$materialId][$i];
                        $tmpName = $_FILES['material_photos']['tmp_name'][$materialId][$i];
                        $fileSize = $_FILES['material_photos']['size'][$materialId][$i];
                        $mimeType = $_FILES['material_photos']['type'][$materialId][$i];
                        
                        // Generate unique filename
                        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                        $uniqueName = 'day' . $dayNumber . '_material' . $materialId . '_' . time() . '_' . $i . '_' . uniqid() . '.' . $extension;
                        $filePath = $uploadDir . $uniqueName;
                        $relativePath = 'assets/installation_progress/' . $installationId . '/' . $uniqueName;
                        
                        // Validate file size (max 10MB)
                        if ($fileSize > 10 * 1024 * 1024) {
                            throw new Exception("File {$originalName} is too large. Maximum size is 10MB.");
                        }
                        
                        // Move uploaded file
                        if (move_uploaded_file($tmpName, $filePath)) {
                            // Save to database
                            try {
                                $stmt = $pdo->prepare("
                                    INSERT INTO installation_progress_attachments 
                                    (installation_id, progress_id, day_number, attachment_type, file_name, original_name, file_path, file_type, file_size, mime_type, uploaded_by, description) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                                ");
                                
                                $fileType = strpos($mimeType, 'video') !== false ? 'video' : 'photo';
                                $description = "Day {$dayNumber} - Material ID {$materialId}";
                                
                                $insertResult = $stmt->execute([
                                    $installationId,
                                    null,
                                    $dayNumber, // day_number
                                    'daily_work_material',
                                    $uniqueName,
                                    $originalName,
                                    $relativePath,
                                    $fileType,
                                    $fileSize,
                                    $mimeType,
                                    $currentUser['id'],
                                    $description
                                ]);
                                
                                if (!$insertResult) {
                                    $errorInfo = $stmt->errorInfo();
                                    file_put_contents($logFile, "DB Insert Error (Material): " . json_encode($errorInfo) . "\n", FILE_APPEND);
                                    throw new Exception("Failed to save material file metadata: " . $errorInfo[2]);
                                }
                                
                                $uploadedFiles[] = [
                                    'type' => 'material',
                                    'material_id' => $materialId,
                                    'name' => $originalName,
                                    'path' => $relativePath
                                ];
                            } catch (PDOException $e) {
                                file_put_contents($logFile, "PDO Exception (Material): " . $e->getMessage() . "\n", FILE_APPEND);
                                throw new Exception("Database error: " . $e->getMessage());
                            }
                        } else {
                            throw new Exception("Failed to move uploaded material file: {$originalName}");
                        }
                    }
                }
            }
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Daily work and files saved successfully',
            'uploaded_files' => $uploadedFiles
        ]);
        
    } else {
        throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
