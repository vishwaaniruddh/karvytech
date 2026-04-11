<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php'; // Ensure user is logged in

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$db = Database::getInstance()->getConnection();

try {
    if ($action === 'get_customers') {
        $stmt = $db->query("SELECT id, name FROM customers WHERE status = 'active' ORDER BY name ASC");
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'customers' => $customers]);
        exit;
    }

    if ($action === 'get_report') {
        $selectedCustomer = $_GET['customer_id'] ?? null;
        if (!$selectedCustomer) {
            echo json_encode(['success' => false, 'message' => 'Customer ID is required']);
            exit;
        }

        // 1. Get survey form
        $stmt = $db->prepare("SELECT id, title, description FROM dynamic_surveys 
                              WHERE (customer_id = ? OR customer_id IS NULL) 
                              AND form_type = 'survey' AND status = 'active' 
                              ORDER BY customer_id DESC LIMIT 1");
        $stmt->execute([$selectedCustomer]);
        $surveyForm = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$surveyForm) {
            echo json_encode(['success' => true, 'surveyForm' => null]);
            exit;
        }

        // 2. Build structured field list (flattened for table headers)
        $stmt = $db->prepare("SELECT * FROM dynamic_survey_sections 
                             WHERE survey_id = ? AND parent_section_id IS NULL 
                             ORDER BY sort_order ASC");
        $stmt->execute([$surveyForm['id']]);
        $parentSections = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $structuredFields = [];
        foreach ($parentSections as $section) {
            // Fields in parent section
            $stmt = $db->prepare("SELECT id, label, field_type FROM dynamic_survey_fields 
                                 WHERE section_id = ? ORDER BY sort_order ASC");
            $stmt->execute([$section['id']]);
            $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($fields as $field) {
                $structuredFields[] = [
                    'field_id' => $field['id'],
                    'section_name' => $section['title'],
                    'field_label' => $field['label'],
                    'field_type' => $field['field_type'],
                    'full_path' => $section['title']
                ];
            }
            
            // Subsections
            $stmt = $db->prepare("SELECT * FROM dynamic_survey_sections 
                                 WHERE parent_section_id = ? ORDER BY sort_order ASC");
            $stmt->execute([$section['id']]);
            $subsections = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($subsections as $subsection) {
                $stmt = $db->prepare("SELECT id, label, field_type FROM dynamic_survey_fields 
                                     WHERE section_id = ? ORDER BY sort_order ASC");
                $stmt->execute([$subsection['id']]);
                $subFields = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($subFields as $field) {
                    $structuredFields[] = [
                        'field_id' => $field['id'],
                        'section_name' => $section['title'],
                        'subsection_name' => $subsection['title'],
                        'field_label' => $field['label'],
                        'field_type' => $field['field_type'],
                        'full_path' => $section['title'] . ' > ' . $subsection['title']
                    ];
                }
            }
        }

        // 3. Get responses
        $stmt = $db->prepare("SELECT sr.*, s.site_id as site_code, 
                             CONCAT_WS(' ', u.first_name, u.last_name) as surveyor_name,
                             c.name as customer_name
                             FROM dynamic_survey_responses sr
                             LEFT JOIN sites s ON sr.site_id = s.id
                             LEFT JOIN users u ON sr.surveyor_id = u.id
                             LEFT JOIN customers c ON s.customer_id = c.id
                             WHERE sr.survey_form_id = ?
                             ORDER BY sr.submitted_date DESC");
        $stmt->execute([$surveyForm['id']]);
        $responses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'surveyForm' => $surveyForm,
            'structuredFields' => $structuredFields,
            'responses' => $responses
        ]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Invalid action']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
