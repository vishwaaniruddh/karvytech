<?php
require_once __DIR__ . '/../config/auth.php';
// constants.php is already included by auth.php
require_once __DIR__ . '/../models/Installation.php';

// Require vendor authentication
Auth::requireVendor();

$vendorId = Auth::getVendorId();
$currentUser = Auth::getCurrentUser();

header('Content-Type: application/json');

try {
    if (!isset($_POST['action']) || !isset($_POST['installation_id'])) {
        throw new Exception('Invalid request data');
    }
    
    $action = $_POST['action'];
    $installationId = (int)$_POST['installation_id'];
    
    $installationModel = new Installation();
    
    // Verify vendor access to this installation
    $installation = $installationModel->getInstallationDetails($installationId);
    if (!$installation || $installation['vendor_id'] != $vendorId) {
        throw new Exception('Access denied');
    }
    
    if ($action === 'add_progress_with_attachments') {
        // Validate required fields
        if (empty($_POST['progress_percentage']) || empty($_POST['work_description'])) {
            throw new Exception('Progress percentage and work description are required');
        }
        
        // Start database transaction
        $pdo = Database::getInstance()->getConnection();
        $pdo->beginTransaction();
        
        try {
            // Add progress update
            $progressData = [
                'installation_id' => $installationId,
                'progress_percentage' => (float)$_POST['progress_percentage'],
                'work_description' => $_POST['work_description'],
                'issues_faced' => $_POST['issues_faced'] ?? null,
                'next_steps' => $_POST['next_steps'] ?? null,
                'updated_by' => $currentUser['id']
            ];
            
            $progressId = $installationModel->addInstallationProgressUpdateWithId($progressData);
            if (!$progressId) {
                throw new Exception('Failed to add progress update');
            }
            
            // Handle file uploads
            $uploadDir = __DIR__ . '/../assets/installation_progress/' . $installationId . '/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $attachmentTypes = ['final_report', 'site_snaps', 'excel_sheet', 'drawing_attachment'];
            $uploadedFiles = [];
            $totalAttachments = 0;
            
            foreach ($attachmentTypes as $type) {
                if (isset($_FILES[$type]) && !empty($_FILES[$type]['name'][0])) {
                    $files = $_FILES[$type];
                    $fileCount = count($files['name']);
                    
                    for ($i = 0; $i < $fileCount; $i++) {
                        if ($files['error'][$i] === UPLOAD_ERR_OK) {
                            $originalName = $files['name'][$i];
                            $tmpName = $files['tmp_name'][$i];
                            $fileSize = $files['size'][$i];
                            $mimeType = $files['type'][$i];
                            
                            // Generate unique filename
                            $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                            $uniqueName = $type . '_' . time() . '_' . $i . '_' . uniqid() . '.' . $extension;
                            $filePath = $uploadDir . $uniqueName;
                            $relativePath = 'assets/installation_progress/' . $installationId . '/' . $uniqueName;
                            
                            // Validate file size (max 10MB)
                            if ($fileSize > 10 * 1024 * 1024) {
                                throw new Exception("File {$originalName} is too large. Maximum size is 10MB.");
                            }
                            
                            // Move uploaded file
                            if (move_uploaded_file($tmpName, $filePath)) {
                                // Save to database
                                $stmt = $pdo->prepare("
                                    INSERT INTO installation_progress_attachments 
                                    (installation_id, progress_id, attachment_type, file_name, original_name, file_path, file_type, file_size, mime_type, uploaded_by, description) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                                ");
                                
                                $stmt->execute([
                                    $installationId,
                                    $progressId,
                                    $type,
                                    $uniqueName,
                                    $originalName,
                                    $relativePath,
                                    $extension,
                                    $fileSize,
                                    $mimeType,
                                    $currentUser['id'],
                                    $_POST['attachment_description'] ?? null
                                ]);
                                
                                $uploadedFiles[] = [
                                    'type' => $type,
                                    'original_name' => $originalName,
                                    'file_path' => $relativePath,
                                    'file_size' => $fileSize
                                ];
                                
                                $totalAttachments++;
                            } else {
                                throw new Exception("Failed to upload file: {$originalName}");
                            }
                        }
                    }
                }
            }
            
            // Update progress record with attachment flags
            if ($totalAttachments > 0) {
                $updateSql = "UPDATE installation_progress SET ";
                $updateParams = [];
                $updateFields = [];
                
                foreach ($attachmentTypes as $type) {
                    $hasFiles = false;
                    foreach ($uploadedFiles as $file) {
                        if ($file['type'] === $type) {
                            $hasFiles = true;
                            break;
                        }
                    }
                    $updateFields[] = "has_" . $type . " = ?";
                    $updateParams[] = $hasFiles ? 1 : 0;
                }
                
                $updateFields[] = "total_attachments = ?";
                $updateParams[] = $totalAttachments;
                $updateParams[] = $progressId;
                
                $updateSql .= implode(', ', $updateFields) . " WHERE id = ?";
                
                $updateStmt = $pdo->prepare($updateSql);
                $updateStmt->execute($updateParams);
            }
            
            $pdo->commit();
            
            echo json_encode([
                'success' => true, 
                'message' => 'Progress updated successfully with ' . $totalAttachments . ' attachments',
                'progress_id' => $progressId,
                'uploaded_files' => $uploadedFiles,
                'total_attachments' => $totalAttachments
            ]);
            
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    } else {
        throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>