<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/SiteSurvey.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    $surveyModel = new SiteSurvey();
    
    // Get form data
    $siteId = $_POST['site_id'] ?? null;
    $delegationId = $_POST['delegation_id'] ?? null;
    $surveyFormId = $_POST['survey_form_id'] ?? null;
    $formDataJson = $_POST['form_data'] ?? null;
    
    if (!$siteId || !$delegationId || !$surveyFormId || !$formDataJson) {
        throw new Exception('Missing required fields');
    }
    
    $formData = json_decode($formDataJson, true);
    if (!$formData) {
        throw new Exception('Invalid form data');
    }
    
    // Get site master data
    $siteMaster = $_POST['site_master'] ?? [];
    
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
    
    // Merge uploaded files info into form data
    foreach ($uploadedFiles as $fieldId => $fileInfo) {
        $formData[$fieldId] = $fileInfo;
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
    
    // DO NOT update delegation status - keep it as 'active'
    // The delegation should remain active even after survey submission
    // $stmt = $db->prepare("UPDATE site_delegations SET status = 'survey_completed' WHERE id = ?");
    // $stmt->execute([$delegationId]);
    
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Survey submitted successfully',
        'response_id' => $responseId
    ]);
    
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    
    // Log error
    error_log('Survey submission error: ' . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Failed to submit survey: ' . $e->getMessage()
    ]);
}
