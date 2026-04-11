<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/SiteSurvey.php';
require_once __DIR__ . '/../models/SiteDelegation.php';

// Require vendor authentication
Auth::requireVendor();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $userId = Auth::getUserId();
    $vendorId = Auth::getVendorId(); // This is the vendor company ID, not the user ID
    $siteId = $_POST['site_id'] ?? null;
    $delegationId = $_POST['delegation_id'] ?? null;
    
    // Debug: Log IDs for troubleshooting
    error_log("Survey submission - User ID: " . $userId . ", Vendor ID: " . $vendorId);
    
    if (!$siteId || !$delegationId) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }
    
    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'User ID not found in session']);
        exit;
    }
    
    // Verify delegation belongs to vendor
    $delegationModel = new SiteDelegation();
    $delegation = $delegationModel->find($delegationId);
    
    if (!$delegation || $delegation['vendor_id'] != $vendorId) {
        echo json_encode([
            'success' => false, 
            'message' => 'Unauthorized access',
            'debug_info' => [
                'delegation_vendor_id' => $delegation['vendor_id'] ?? 'null',
                'session_vendor_id' => $vendorId
            ]
        ]);
        exit;
    }
    
    // Handle multiple file uploads for different photo types
    $photoFields = [
        'floor_height_photo' => 'floor_height_photos',
        'ceiling_photo' => 'ceiling_photos',
        'analytic_photos' => 'analytic_photos',
        'existing_poe_photos' => 'existing_poe_photos',
        'space_new_rack_photo' => 'space_new_rack_photos',
        'new_poe_photos' => 'new_poe_photos',
        'rrl_photos' => 'rrl_photos',
        'kptl_photos' => 'kptl_photos',
        'site_photos' => 'site_photos'
    ];
    
    $uploadedPhotos = [];
    $currentYear = date('Y');
    $currentMonth = date('m');
    $uploadDir = __DIR__ . '/../assets/uploads/surveys/' . $currentYear . '/' . $currentMonth . '/';
    
    // Create directory structure if it doesn't exist
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            throw new Exception('Failed to create upload directory: ' . $uploadDir);
        }
    }
    
    $uploadErrors = [];
    
    foreach ($photoFields as $inputName => $dbField) {
        $uploadedPhotos[$dbField] = [];
        
        if (isset($_FILES[$inputName]) && !empty($_FILES[$inputName]['name'])) {
            try {
                // Handle multiple files
                if (is_array($_FILES[$inputName]['name'])) {
                    foreach ($_FILES[$inputName]['name'] as $key => $filename) {
                        if (!empty($filename)) {
                            try {
                                $uploadedFile = handleFileUpload($_FILES[$inputName], $key, $uploadDir);
                                if ($uploadedFile) {
                                    $uploadedPhotos[$dbField][] = $uploadedFile;
                                }
                            } catch (Exception $e) {
                                $uploadErrors[] = "Error uploading {$inputName}[{$key}]: " . $e->getMessage();
                            }
                        }
                    }
                } else {
                    // Handle single file
                    if (!empty($_FILES[$inputName]['name'])) {
                        try {
                            $uploadedFile = handleSingleFileUpload($_FILES[$inputName], $uploadDir);
                            if ($uploadedFile) {
                                $uploadedPhotos[$dbField][] = $uploadedFile;
                            }
                        } catch (Exception $e) {
                            $uploadErrors[] = "Error uploading {$inputName}: " . $e->getMessage();
                        }
                    }
                }
            } catch (Exception $e) {
                $uploadErrors[] = "Error processing {$inputName}: " . $e->getMessage();
            }
        }
        
        // Convert array to JSON string for database storage
        $uploadedPhotos[$dbField] = !empty($uploadedPhotos[$dbField]) ? json_encode($uploadedPhotos[$dbField]) : null;
    }
    
    // If there were upload errors, include them in the response but don't fail the entire submission
    if (!empty($uploadErrors)) {
        error_log('Photo upload errors: ' . implode('; ', $uploadErrors));
    }
    
    // Prepare survey data
    $surveyData = [
        'site_id' => $siteId,
        'vendor_id' => $vendorId, 
        'delegation_id' => $delegationId,
        'survey_status' => 'completed',
        'submitted_date' => date('Y-m-d H:i:s'),
        'created_by'=>$userId,
        // Check-in/Check-out
        'checkin_datetime' => $_POST['checkin_datetime'] ?? null,
        'checkout_datetime' => $_POST['checkout_datetime'] ?? null,
        'working_hours' => $_POST['working_hours'] ?? null,
        
        // Site Information
        'store_model' => $_POST['store_model'] ?? null,
        
        // Floor and Ceiling
        'floor_height' => $_POST['floor_height'] ?? null,
        'floor_height_photos' => $uploadedPhotos['floor_height_photos'],
        'ceiling_type' => $_POST['ceiling_type'] ?? null,
        'ceiling_photos' => $uploadedPhotos['ceiling_photos'],
        
        // Camera Assessment
        'total_cameras' => $_POST['total_cameras'] ?? null,
        'analytic_cameras' => $_POST['analytic_cameras'] ?? null,
        'analytic_photos' => $uploadedPhotos['analytic_photos'],
        
        // POE Rack Assessment
        'existing_poe_rack' => $_POST['existing_poe_rack'] ?? null,
        'existing_poe_photos' => $uploadedPhotos['existing_poe_photos'],
        'space_new_rack' => $_POST['space_new_rack'] ?? null,
        'space_new_rack_photos' => $uploadedPhotos['space_new_rack_photos'],
        'new_poe_rack' => $_POST['new_poe_rack'] ?? null,
        'new_poe_photos' => $uploadedPhotos['new_poe_photos'],
        
        // Zone Assessment
        'zones_recommended' => $_POST['zones_recommended'] ?? null,
        
        // Material Status
        'rrl_delivery_status' => $_POST['rrl_delivery_status'] ?? null,
        'rrl_photos' => $uploadedPhotos['rrl_photos'],
        'kptl_space' => $_POST['kptl_space'] ?? null,
        'kptl_photos' => $uploadedPhotos['kptl_photos'],
        
        // Technical Assessment
        'site_accessibility' => $_POST['site_accessibility'] ?? null,
        'power_availability' => $_POST['power_availability'] ?? null,
        'network_connectivity' => $_POST['network_connectivity'] ?? null,
        'space_adequacy' => $_POST['space_adequacy'] ?? null,
        
        // Site Photos
        'site_photos' => $uploadedPhotos['site_photos'],
        
        // Survey Findings
        'technical_remarks' => $_POST['technical_remarks'] ?? null,
        'challenges_identified' => $_POST['challenges_identified'] ?? null,
        'recommendations' => $_POST['recommendations'] ?? null,
        'estimated_completion_days' => $_POST['estimated_completion_days'] ?? null
    ];
    
    // Create survey
    $surveyModel = new SiteSurvey();
    $result = $surveyModel->createComprehensive($surveyData);
    
    if ($result) {
        // Update the sites table to reflect survey submission
        try {
            require_once __DIR__ . '/../models/Site.php';
            $siteModel = new Site();
            $siteModel->updateSurveyStatus($siteId, true, date('Y-m-d H:i:s'));
            
            // Also update the delegation status to completed
            $delegationModel->updateStatus($delegationId, 'completed');
            
        } catch (Exception $e) {
            error_log('Failed to update site/delegation status: ' . $e->getMessage());
            // Don't fail the entire operation for this
        }
        
        $response = [
            'success' => true, 
            'message' => 'Site feasibility survey submitted successfully!',
            'survey_id' => $result
        ];
        
        // Include upload errors if any occurred
        if (!empty($uploadErrors)) {
            $response['upload_warnings'] = $uploadErrors;
            $response['message'] .= ' Note: Some photo uploads had issues.';
        }
        
        echo json_encode($response);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to submit survey to database. Please try again.',
            'error_details' => 'Database insertion failed',
            'upload_errors' => $uploadErrors ?? []
        ]);
    }
    
} catch (Exception $e) {
    error_log('Survey submission error: ' . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'An error occurred while submitting the survey.',
        'error_details' => $e->getMessage(),
        'error_code' => $e->getCode(),
        'error_file' => basename($e->getFile()),
        'error_line' => $e->getLine()
    ]);
}

function handleFileUpload($fileArray, $index, $uploadDir) {
    $filename = $fileArray['name'][$index];
    $tmpName = $fileArray['tmp_name'][$index];
    $fileSize = $fileArray['size'][$index];
    $error = $fileArray['error'][$index];
    
    // Check for upload errors
    if ($error !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];
        throw new Exception('File upload error for ' . $filename . ': ' . ($errorMessages[$error] ?? 'Unknown error'));
    }
    
    $fileExtension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (!in_array($fileExtension, $allowedExtensions)) {
        throw new Exception('Invalid file type for ' . $filename . '. Allowed types: ' . implode(', ', $allowedExtensions));
    }
    
    if ($fileSize > 10 * 1024 * 1024) { // 10MB limit
        throw new Exception('File size too large for ' . $filename . '. Maximum size: 10MB');
    }
    
    $sanitizedFilename = preg_replace('/[^a-zA-Z0-9._-]/', '_', pathinfo($filename, PATHINFO_FILENAME));
    $newFilename = $sanitizedFilename . '_' . uniqid() . '_' . time() . '.' . $fileExtension;
    $uploadPath = $uploadDir . $newFilename;
    
    if (!move_uploaded_file($tmpName, $uploadPath)) {
        throw new Exception('Failed to move uploaded file: ' . $filename);
    }
    
    $currentYear = date('Y');
    $currentMonth = date('m');
    return 'assets/uploads/surveys/' . $currentYear . '/' . $currentMonth . '/' . $newFilename;
}

function handleSingleFileUpload($file, $uploadDir) {
    $filename = $file['name'];
    $tmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $error = $file['error'];
    
    // Check for upload errors
    if ($error !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];
        throw new Exception('File upload error for ' . $filename . ': ' . ($errorMessages[$error] ?? 'Unknown error'));
    }
    
    $fileExtension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (!in_array($fileExtension, $allowedExtensions)) {
        throw new Exception('Invalid file type for ' . $filename . '. Allowed types: ' . implode(', ', $allowedExtensions));
    }
    
    if ($fileSize > 10 * 1024 * 1024) { // 10MB limit
        throw new Exception('File size too large for ' . $filename . '. Maximum size: 10MB');
    }
    
    $sanitizedFilename = preg_replace('/[^a-zA-Z0-9._-]/', '_', pathinfo($filename, PATHINFO_FILENAME));
    $newFilename = $sanitizedFilename . '_' . uniqid() . '_' . time() . '.' . $fileExtension;
    $uploadPath = $uploadDir . $newFilename;
    
    if (!move_uploaded_file($tmpName, $uploadPath)) {
        throw new Exception('Failed to move uploaded file: ' . $filename);
    }
    
    $currentYear = date('Y');
    $currentMonth = date('m');
    return 'assets/uploads/surveys/' . $currentYear . '/' . $currentMonth . '/' . $newFilename;
}
?>