<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/DynamicSurvey.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$surveyModel = new DynamicSurvey();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'save') {
    try {
        $id = $_POST['survey_id'] ?? null;
        $title = $_POST['title'];
        $description = $_POST['description'];
        $status = $_POST['status'];
        $formType = $_POST['form_type'] ?? 'survey';
        $customerId = $_POST['customer_id'] ?: null;
        $fieldsData = $_POST['fields'] ?? [];
        
        $db = Database::getInstance()->getConnection();
        
        if ($id) {
            // Update logic
            $db->beginTransaction();
            $surveyModel->update($id, [
                'title' => $title,
                'description' => $description,
                'status' => $status,
                'form_type' => $formType,
                'customer_id' => $customerId,
                'created_by' => $_SESSION['user_id'] ?? null
            ]);
            
            // Re-create fields (Simpler for now: delete and insert)
            $db->prepare("DELETE FROM dynamic_survey_fields WHERE survey_id = ?")->execute([$id]);
            
            foreach ($fieldsData as $index => $field) {
                // Ensure file_config is JSON string
                $fileConfig = null;
                if ($field['field_type'] === 'file' && isset($field['file_config'])) {
                    $fileConfig = json_encode($field['file_config']);
                }
                
                $db->prepare("INSERT INTO dynamic_survey_fields (survey_id, label, field_type, is_required, options, file_config, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)")
                    ->execute([
                        $id,
                        $field['label'],
                        $field['field_type'],
                        isset($field['is_required']) ? 1 : 0,
                        $field['options'] ?? null,
                        $fileConfig,
                        $index
                    ]);
            }
            $db->commit();
            echo json_encode(['success' => true, 'id' => $id]);
        } else {
            // Create logic
            $formattedFields = [];
            foreach ($fieldsData as $field) {
                $fileConfig = null;
                if ($field['field_type'] === 'file' && isset($field['file_config'])) {
                    $fileConfig = json_encode($field['file_config']);
                }
                
                $formattedFields[] = [
                    'label' => $field['label'],
                    'field_type' => $field['field_type'],
                    'is_required' => isset($field['is_required']) ? 1 : 0,
                    'options' => $field['options'] ?? null,
                    'file_config' => $fileConfig
                ];
            }
            
            $newId = $surveyModel->createWithFields([
                'title' => $title,
                'description' => $description,
                'status' => $status,
                'form_type' => $formType,
                'customer_id' => $customerId,
                'created_by' => $_SESSION['user_id'] ?? null
            ], $formattedFields);
            
            echo json_encode(['success' => true, 'id' => $newId]);
        }
    } catch (Exception $e) {
        if (isset($db) && $db->inTransaction()) $db->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'get_customers') {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT id, name as company_name FROM customers ORDER BY name ASC");
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'customers' => $customers]);
    exit;
}

if ($_GET['id'] ?? null) {
    $survey = $surveyModel->find($_GET['id']);
    if ($survey) {
        $fields = $surveyModel->getFields($survey['id']);
        echo json_encode(['success' => true, 'survey' => $survey, 'fields' => $fields]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Survey not found']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
?>
