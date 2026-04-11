<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    $responseId = $_POST['response_id'] ?? null;
    $formDataJson = $_POST['form_data'] ?? null;
    
    if (!$responseId || !$formDataJson) {
        throw new Exception('Missing required fields');
    }
    
    // Check if survey exists and is not approved
    $stmt = $db->prepare("SELECT survey_status FROM dynamic_survey_responses WHERE id = ?");
    $stmt->execute([$responseId]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$existing) {
        throw new Exception('Survey response not found');
    }
    
    if ($existing['survey_status'] === 'approved') {
        throw new Exception('Cannot edit approved surveys');
    }
    
    $formData = json_decode($formDataJson, true);
    if (!$formData) {
        throw new Exception('Invalid form data');
    }
    
    // Get existing form data to preserve files that weren't replaced
    $stmt = $db->prepare("SELECT form_data FROM dynamic_survey_responses WHERE id = ?");
    $stmt->execute([$responseId]);
    $existingResponse = $stmt->fetch(PDO::FETCH_ASSOC);
    $existingFormData = $existingResponse ? json_decode($existingResponse['form_data'], true) : [];
    
    $db->beginTransaction();
    
    // Create uploads directory if it doesn't exist
    $uploadDir = __DIR__ . '/../uploads/surveys/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Process file uploads
    $uploadedFiles = [];
    foreach ($_FILES as $key => $file) {
        if (strpos($key, 'file_') === 0) {
            $fieldId = str_replace('file_', '', $key);
            
            // Handle multiple files
            if (is_array($file['name'])) {
                $uploadedFiles[$fieldId] = [];
                $fileCount = count($file['name']);
                
                for ($i = 0; $i < $fileCount; $i++) {
                    if ($file['error'][$i] === UPLOAD_ERR_OK) {
                        $tmpName = $file['tmp_name'][$i];
                        $originalName = basename($file['name'][$i]);
                        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                        $uniqueName = uniqid() . '_' . time() . '.' . $extension;
                        $destination = $uploadDir . $uniqueName;
                        
                        if (move_uploaded_file($tmpName, $destination)) {
                            $uploadedFiles[$fieldId][] = [
                                'original_name' => $originalName,
                                'stored_name' => $uniqueName,
                                'file_path' => 'uploads/surveys/' . $uniqueName,
                                'file_size' => $file['size'][$i],
                                'mime_type' => $file['type'][$i]
                            ];
                        }
                    }
                }
            } else {
                // Single file
                if ($file['error'] === UPLOAD_ERR_OK) {
                    $tmpName = $file['tmp_name'];
                    $originalName = basename($file['name']);
                    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                    $uniqueName = uniqid() . '_' . time() . '.' . $extension;
                    $destination = $uploadDir . $uniqueName;
                    
                    if (move_uploaded_file($tmpName, $destination)) {
                        $uploadedFiles[$fieldId] = [
                            'original_name' => $originalName,
                            'stored_name' => $uniqueName,
                            'file_path' => 'uploads/surveys/' . $uniqueName,
                            'file_size' => $file['size'],
                            'mime_type' => $file['type']
                        ];
                    }
                }
            }
        }
    }
    
    // Merge new uploaded files into form data
    foreach ($uploadedFiles as $fieldId => $fileInfo) {
        if (isset($formData[$fieldId]) && is_array($formData[$fieldId])) {
            // If existing data is already an array of files, merge or append new ones
            if (isset($fileInfo[0]) && is_array($fileInfo[0])) {
                // New uploads are multiple files
                $formData[$fieldId] = array_merge($formData[$fieldId], $fileInfo);
            } else {
                // New upload is a single file object, append it to the array
                $formData[$fieldId][] = $fileInfo;
            }
        } else {
            // No existing files or it was a single file that we are replacing
            $formData[$fieldId] = $fileInfo;
        }
    }
    
    // 4. Capture Revision
    // Get current revision count for this response
    $stmt = $db->prepare("SELECT MAX(revision_number) FROM dynamic_survey_revisions WHERE response_id = ?");
    $stmt->execute([$responseId]);
    $lastRevision = $stmt->fetchColumn() ?: 0;
    $nextRevision = $lastRevision + 1;

    // Insert new version into revisions table
    $stmt = $db->prepare("INSERT INTO dynamic_survey_revisions 
                          (response_id, revision_number, form_data, site_master_data, updated_by, change_summary) 
                          VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $responseId,
        $nextRevision,
        json_encode($formData),
        $existingResponse['site_master_data'] ?? null,
        Auth::getCurrentUser()['id'] ?? null,
        $_POST['change_summary'] ?? 'Updated via Edit Page'
    ]);

    // 5. Update main survey response
    $stmt = $db->prepare("UPDATE dynamic_survey_responses 
                          SET form_data = ?, 
                              survey_status = 'submitted',
                              submitted_date = NOW()
                          WHERE id = ?");
    
    $stmt->execute([
        json_encode($formData),
        $responseId
    ]);
    
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Survey updated successfully',
        'revision' => $nextRevision
    ]);
    
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    
    error_log('Survey update error: ' . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update survey: ' . $e->getMessage()
    ]);
}
