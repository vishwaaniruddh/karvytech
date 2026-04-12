<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/MaterialRequest.php';

// Require vendor authentication
//Auth::requireVendor();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
  //  $vendorId = Auth::getVendorId();
    $vendorId = $_POST['vendor_id'] ?? null;
    $siteId = $_POST['site_id'] ?? null;
    $surveyId = $_POST['survey_id'] ?? null;
    $isDraft = isset($_POST['save_draft']);
    
    if (!$siteId) {
        echo json_encode(['success' => false, 'message' => 'Site ID is required']);
        exit;
    }
    
    // Fetch the correct survey_id for this site and vendor
    require_once __DIR__ . '/../models/SiteSurvey.php';
    $surveyModel = new SiteSurvey();
    
    if (!$surveyId) {
        // If no survey_id provided, find the survey for this site and vendor
        // Check dynamic surveys first
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id FROM dynamic_survey_responses WHERE site_id = ? ORDER BY submitted_date DESC LIMIT 1");
        $stmt->execute([$siteId]);
        $dynamicSurvey = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($dynamicSurvey) {
            $surveyId = $dynamicSurvey['id'];
            $surveyType = 'dynamic';
        } else {
            // Fallback to legacy surveys
            $survey = $surveyModel->findBySiteAndVendor($siteId, $vendorId);
            if ($survey) {
                $surveyId = $survey['id'];
                $surveyType = 'legacy';
            } else {
                echo json_encode([
                    'success' => false, 
                    'message' => 'No survey found for this site. Please complete the site survey first.',
                    'debug' => "No survey found for site_id: $siteId, vendor_id: $vendorId"
                ]);
                exit;
            }
        }
    } else {
        // Validate the provided survey_id
        // Check dynamic surveys first
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT site_id FROM dynamic_survey_responses WHERE id = ?");
        $stmt->execute([$surveyId]);
        $dynamicSurvey = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($dynamicSurvey) {
            // Verify the dynamic survey belongs to this site
            if ($dynamicSurvey['site_id'] != $siteId) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Survey does not match the selected site. Please refresh the page and try again.',
                    'debug' => "Dynamic Survey site_id: {$dynamicSurvey['site_id']}, provided site_id: $siteId"
                ]);
                exit;
            }
            $surveyType = 'dynamic';
        } else {
            // Fallback to legacy surveys
            $survey = $surveyModel->find($surveyId);
            if (!$survey) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Invalid survey reference. Please refresh the page and try again.',
                    'debug' => "Survey ID $surveyId not found in either dynamic or legacy systems"
                ]);
                exit;
            }
            
            // Verify the legacy survey belongs to this site
            if ($survey['site_id'] != $siteId) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Survey does not match the selected site. Please refresh the page and try again.',
                    'debug' => "Legacy Survey site_id: {$survey['site_id']}, provided site_id: $siteId"
                ]);
                exit;
            }
            $surveyType = 'legacy';
        }
    }
    
    // Validate required fields for non-draft submissions
    if (!$isDraft) {
        $requiredFields = ['request_date', 'required_date'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
                exit;
            }
        }
        
        // Check if items are provided
        if (empty($_POST['items']) || !is_array($_POST['items'])) {
            echo json_encode(['success' => false, 'message' => 'At least one material item is required']);
            exit;
        }
    }
    
    // Prepare material request data
    $requestData = [
        'site_id' => $siteId,
        'vendor_id' => $vendorId,
        'survey_id' => $surveyId,
        'request_date' => $_POST['request_date'] ?? date('Y-m-d'),
        'required_date' => $_POST['required_date'] ?? null,
        'request_notes' => $_POST['request_notes'] ?? null,
        'status' => $isDraft ? 'draft' : 'pending',
        'created_date' => date('Y-m-d H:i:s')
    ];
    
    // Prepare items data
    $items = [];
    if (!empty($_POST['items']) && is_array($_POST['items'])) {
        foreach ($_POST['items'] as $item) {
            if (!empty($item['boq_item_id']) && !empty($item['quantity'])) {
                $items[] = [
                    'boq_item_id' => (int)$item['boq_item_id'],
                    'item_code' => $item['item_code'] ?? '',
                    'quantity' => (int)$item['quantity'],
                    'unit' => $item['unit'] ?? '',
                    'notes' => $item['notes'] ?? ''
                ];
            }
        }
    }
    
    $requestData['items'] = json_encode($items);
    
    // Create material request
    $materialRequestModel = new MaterialRequest();
    $result = $materialRequestModel->create($requestData);
    
    if ($result) {
        $message = $isDraft ? 'Material request draft saved successfully!' : 'Material request submitted successfully!';
        echo json_encode([
            'success' => true, 
            'message' => $message,
            'request_id' => $result
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to save material request. Please try again.'
        ]);
    }
    
} catch (Exception $e) {
    error_log('Material request submission error: ' . $e->getMessage());
    
    // Provide user-friendly error messages for common issues
    $userMessage = 'An error occurred while processing the material request.';
    
    if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
        $userMessage = 'Invalid site or vendor information. Please refresh the page and try again.';
    } elseif (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        $userMessage = 'A similar request already exists. Please check your existing requests.';
    } elseif (strpos($e->getMessage(), 'Data too long') !== false) {
        $userMessage = 'Request data is too large. Please reduce the number of items or notes length.';
    }
    
    echo json_encode([
        'success' => false, 
        'message' => $userMessage,
        'debug' => $e->getMessage() // Keep for debugging, can be removed in production
    ]);
}
?>