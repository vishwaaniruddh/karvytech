<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/DynamicSurveyResponse.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $surveyId = $_POST['survey_id'];
        $values = $_POST['values'] ?? [];
        
        $responseModel = new DynamicSurveyResponse();
        
        // Handle file uploads
        $uploadedFiles = [];
        if (!empty($_FILES['files'])) {
            $uploadDir = __DIR__ . '/../uploads/surveys/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            
            foreach ($_FILES['files']['name'] as $fieldId => $name) {
                if (is_array($name)) {
                    // Multiple files
                    foreach ($name as $key => $n) {
                        if ($_FILES['files']['error'][$fieldId][$key] === UPLOAD_ERR_OK) {
                            $tmpName = $_FILES['files']['tmp_name'][$fieldId][$key];
                            $ext = pathinfo($n, PATHINFO_EXTENSION);
                            $newName = uniqid() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                            if (move_uploaded_file($tmpName, $uploadDir . $newName)) {
                                $uploadedFiles[$fieldId][] = 'uploads/surveys/' . $newName;
                            }
                        }
                    }
                } else {
                    // Single file
                    if ($_FILES['files']['error'][$fieldId] === UPLOAD_ERR_OK) {
                        $tmpName = $_FILES['files']['tmp_name'][$fieldId];
                        $ext = pathinfo($name, PATHINFO_EXTENSION);
                        $newName = uniqid() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                        if (move_uploaded_file($tmpName, $uploadDir . $newName)) {
                            $uploadedFiles[$fieldId] = 'uploads/surveys/' . $newName;
                        }
                    }
                }
            }
        }
        
        $responseId = $responseModel->submitResponse($surveyId, [
            'values' => $values,
            'site_id' => $_POST['site_id'] ?? null,
            'respondent_id' => $_SESSION['user_id'] ?? null
        ], $uploadedFiles);
        
        echo json_encode(['success' => true, 'response_id' => $responseId]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
?>
