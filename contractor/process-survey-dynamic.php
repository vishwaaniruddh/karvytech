<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/SiteSurvey.php';

// Auth check for contractor/vendor
if (Auth::getRole() !== 'contractor' && Auth::getRole() !== 'vendor' && Auth::getRole() !== 'superadmin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Get form data
    $siteId = $_POST['site_id'] ?? null;
    $delegationId = $_POST['delegation_id'] ?? null;
    $surveyFormId = $_POST['survey_form_id'] ?? null;
    $formDataJson = $_POST['form_data'] ?? null;
    $siteMaster = $_POST['site_master'] ?? [];
    
    if (!$siteId || !$delegationId || !$surveyFormId || !$formDataJson) {
        throw new Exception('Missing required fields');
    }
    
    // Fetch site code for folder naming
    $stmt = $db->prepare("SELECT site_id FROM sites WHERE id = ?");
    $stmt->execute([$siteId]);
    $siteMasterRec = $stmt->fetch(PDO::FETCH_ASSOC);
    $siteCode = $siteMasterRec ? $siteMasterRec['site_id'] : 'Site_' . $siteId;
    // Sanitize site code for folder name
    $siteFolder = preg_replace('/[^A-Za-z0-9_\-]/', '_', $siteCode);

    $formData = json_decode($formDataJson, true);
    if (!is_array($formData)) {
        throw new Exception('Invalid form data');
    }
    
    $db->beginTransaction();
    
    // Create site-specific uploads directory
    $baseUploadDir = __DIR__ . '/../uploads/surveys/';
    $uploadDir = $baseUploadDir . $siteFolder . '/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Process file uploads
    $uploadedFiles = [];
    foreach ($_FILES as $key => $fileGroup) {
        if (strpos($key, 'file_') === 0) {
            $fieldId = str_replace('file_', '', $key);
            $fieldId = str_replace('[]', '', $fieldId); // Remove array suffix if present
            
            if (!isset($uploadedFiles[$fieldId])) {
                $uploadedFiles[$fieldId] = [];
            }

            // Handle multiple files in the same key
            if (is_array($fileGroup['name'])) {
                $fileCount = count($fileGroup['name']);
                for ($i = 0; $i < $fileCount; $i++) {
                    if ($fileGroup['error'][$i] === UPLOAD_ERR_OK) {
                        $tmpName = $fileGroup['tmp_name'][$i];
                        $originalName = basename($fileGroup['name'][$i]);
                        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                        $uniqueName = uniqid() . '_' . time() . '_' . $i . '.' . $extension;
                        $destination = $uploadDir . $uniqueName;
                        
                        if (move_uploaded_file($tmpName, $destination)) {
                            $uploadedFiles[$fieldId][] = [
                                'original_name' => $originalName,
                                'stored_name' => $uniqueName,
                                'file_path' => 'uploads/surveys/' . $siteFolder . '/' . $uniqueName,
                                'file_size' => $fileGroup['size'][$i],
                                'mime_type' => $fileGroup['type'][$i]
                            ];
                        }
                    }
                }
            } else {
                // Single file
                if ($fileGroup['error'] === UPLOAD_ERR_OK) {
                    $tmpName = $fileGroup['tmp_name'];
                    $originalName = basename($fileGroup['name']);
                    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                    $uniqueName = uniqid() . '_' . time() . '.' . $extension;
                    $destination = $uploadDir . $uniqueName;
                    
                    if (move_uploaded_file($tmpName, $destination)) {
                        $uploadedFiles[$fieldId][] = [
                            'original_name' => $originalName,
                            'stored_name' => $uniqueName,
                            'file_path' => 'uploads/surveys/' . $siteFolder . '/' . $uniqueName,
                            'file_size' => $fileGroup['size'],
                            'mime_type' => $fileGroup['type']
                        ];
                    }
                }
            }
        }
    }
    
    // Merge uploaded files info into form data
    foreach ($uploadedFiles as $fieldId => $filesArray) {
        if (empty($filesArray)) continue;
        $formData[$fieldId] = $filesArray;
    }
    
    // Prepare survey data
    $surveyData = [
        'site_id' => $siteId,
        'delegation_id' => $delegationId,
        'survey_form_id' => $surveyFormId,
        'surveyor_id' => $_SESSION['user_id'] ?? null,
        'survey_status' => 'submitted',
        'submitted_date' => date('Y-m-d H:i:s'),
        'form_data' => json_encode($formData),
        'site_master_data' => json_encode($siteMaster)
    ];
    
    // Insert survey response
    $stmt = $db->prepare("INSERT INTO dynamic_survey_responses 
        (site_id, delegation_id, survey_form_id, surveyor_id, survey_status, submitted_date, form_data, site_master_data) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $surveyData['site_id'],
        $surveyData['delegation_id'],
        $surveyData['survey_form_id'],
        $surveyData['surveyor_id'],
        $surveyData['survey_status'],
        $surveyData['submitted_date'],
        $surveyData['form_data'],
        $surveyData['site_master_data']
    ]);
    
    $responseId = $db->lastInsertId();

    // Create initial revision record
    $stmt = $db->prepare("INSERT INTO dynamic_survey_revisions 
                          (response_id, revision_number, form_data, site_master_data, updated_by, change_summary) 
                          VALUES (?, 1, ?, ?, ?, 'Initial Submission')");
    $stmt->execute([
        $responseId,
        $surveyData['form_data'],
        $surveyData['site_master_data'],
        $surveyData['surveyor_id']
    ]);
    
    // Update site master survey status for visibility
    $stmt = $db->prepare("UPDATE sites SET survey_status = 'submitted', survey_submission_date = ? WHERE id = ?");
    $stmt->execute([$surveyData['submitted_date'], $siteId]);
    
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Survey assessment submitted successfully',
        'response_id' => $responseId
    ]);
    
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    
    error_log('Contractor Survey submission error: ' . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Failed to submit survey: ' . $e->getMessage()
    ]);
}
