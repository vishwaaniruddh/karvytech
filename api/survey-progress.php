<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$db = Database::getInstance()->getConnection();

try {
    switch ($action) {
        case 'start':
            // Start a new survey or resume existing draft
            $delegationId = $_POST['delegation_id'] ?? null;
            $surveyFormId = $_POST['survey_form_id'] ?? null;
            $siteId = $_POST['site_id'] ?? null;
            
            if (!$delegationId || !$surveyFormId || !$siteId) {
                throw new Exception('Missing required parameters');
            }
            
            // Check if draft already exists
            $stmt = $db->prepare("SELECT id, form_data, survey_started_at FROM dynamic_survey_responses 
                                 WHERE delegation_id = ? AND is_draft = 1 ORDER BY id DESC LIMIT 1");
            $stmt->execute([$delegationId]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) {
                // Resume existing draft
                echo json_encode([
                    'success' => true,
                    'action' => 'resumed',
                    'response_id' => $existing['id'],
                    'form_data' => json_decode($existing['form_data'], true),
                    'started_at' => $existing['survey_started_at']
                ]);
            } else {
                // Create new draft
                $stmt = $db->prepare("INSERT INTO dynamic_survey_responses 
                    (site_id, delegation_id, survey_form_id, surveyor_id, survey_status, is_draft, survey_started_at, last_saved_at) 
                    VALUES (?, ?, ?, ?, 'draft', 1, NOW(), NOW())");
                $stmt->execute([$siteId, $delegationId, $surveyFormId, $_SESSION['user_id'] ?? null]);
                
                $responseId = $db->lastInsertId();
                
                echo json_encode([
                    'success' => true,
                    'action' => 'started',
                    'response_id' => $responseId,
                    'started_at' => date('Y-m-d H:i:s')
                ]);
            }
            break;
            
        case 'save_draft':
            // Save progress without submitting
            $responseId = $_POST['response_id'] ?? null;
            $formDataJson = $_POST['form_data'] ?? null;
            $siteMasterJson = $_POST['site_master'] ?? null;
            
            if (!$responseId || !$formDataJson) {
                throw new Exception('Missing required parameters');
            }
            
            $stmt = $db->prepare("UPDATE dynamic_survey_responses 
                                 SET form_data = ?, site_master_data = ?, last_saved_at = NOW() 
                                 WHERE id = ? AND is_draft = 1");
            $stmt->execute([$formDataJson, $siteMasterJson, $responseId]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Draft saved successfully',
                'saved_at' => date('Y-m-d H:i:s')
            ]);
            break;
            
        case 'end_survey':
            // Mark survey as ended (ready for preview)
            $responseId = $_POST['response_id'] ?? null;
            $formDataJson = $_POST['form_data'] ?? null;
            $siteMasterJson = $_POST['site_master'] ?? null;
            
            if (!$responseId || !$formDataJson) {
                throw new Exception('Missing required parameters');
            }
            
            $stmt = $db->prepare("UPDATE dynamic_survey_responses 
                                 SET form_data = ?, site_master_data = ?, survey_ended_at = NOW(), last_saved_at = NOW() 
                                 WHERE id = ? AND is_draft = 1");
            $stmt->execute([$formDataJson, $siteMasterJson, $responseId]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Survey ended. Ready for preview.',
                'ended_at' => date('Y-m-d H:i:s')
            ]);
            break;
            
        case 'submit':
            // Final submission after preview
            $responseId = $_POST['response_id'] ?? null;
            
            if (!$responseId) {
                throw new Exception('Missing response ID');
            }
            
            // Check if already approved (locked)
            $stmt = $db->prepare("SELECT approval_status FROM dynamic_survey_responses WHERE id = ?");
            $stmt->execute([$responseId]);
            $survey = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($survey && $survey['approval_status'] === 'approved') {
                throw new Exception('Survey is already approved and cannot be modified');
            }
            
            $stmt = $db->prepare("UPDATE dynamic_survey_responses 
                                 SET is_draft = 0, survey_status = 'submitted', submitted_date = NOW() 
                                 WHERE id = ? AND is_draft = 1");
            $stmt->execute([$responseId]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Survey submitted successfully',
                'submitted_at' => date('Y-m-d H:i:s')
            ]);
            break;
            
        case 'get_status':
            // Get current survey status
            $delegationId = $_GET['delegation_id'] ?? null;
            
            if (!$delegationId) {
                throw new Exception('Missing delegation ID');
            }
            
            $stmt = $db->prepare("SELECT id, survey_status, is_draft, approval_status, survey_started_at, 
                                        survey_ended_at, submitted_date, last_saved_at, form_data, site_master_data 
                                 FROM dynamic_survey_responses 
                                 WHERE delegation_id = ? 
                                 ORDER BY id DESC LIMIT 1");
            $stmt->execute([$delegationId]);
            $survey = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($survey) {
                echo json_encode([
                    'success' => true,
                    'survey' => $survey
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'survey' => null
                ]);
            }
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
