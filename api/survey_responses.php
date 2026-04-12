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

    if ($action === 'get_stats') {
        // Get overall survey response stats
        $stmt = $db->query("SELECT 
            COUNT(*) as submitted,
            SUM(CASE WHEN survey_status = 'approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN survey_status = 'rejected' THEN 1 ELSE 0 END) as rejected,
            SUM(CASE WHEN survey_status = 'pending' OR survey_status = 'submitted' OR survey_status IS NULL THEN 1 ELSE 0 END) as pending
            FROM dynamic_survey_responses");
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'stats' => $stats]);
        exit;
    }

    if ($action === 'get_customer_stats') {
        // Get customer-wise survey statistics
        $stmt = $db->query("SELECT 
            c.id,
            c.name,
            COUNT(dsr.id) as total_surveys,
            SUM(CASE WHEN dsr.id IS NOT NULL THEN 1 ELSE 0 END) as submitted,
            SUM(CASE WHEN dsr.survey_status = 'approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN dsr.survey_status = 'rejected' THEN 1 ELSE 0 END) as rejected,
            SUM(CASE WHEN dsr.survey_status = 'pending' OR dsr.survey_status = 'submitted' OR dsr.survey_status IS NULL THEN 1 ELSE 0 END) as pending
            FROM customers c
            LEFT JOIN sites s ON c.id = s.customer_id
            LEFT JOIN dynamic_survey_responses dsr ON s.id = dsr.site_id
            WHERE c.status = 'active'
            GROUP BY c.id, c.name
            HAVING total_surveys > 0
            ORDER BY c.name ASC");
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'customers' => $customers]);
        exit;
    }

    if ($action === 'get_all_pending') {
        $stmt = $db->query("SELECT sr.*, s.site_id as site_code, s.location,
                             c.name as customer_name, ds.title as form_title,
                             u.username as surveyor_name
                             FROM dynamic_survey_responses sr
                             JOIN sites s ON sr.site_id = s.id
                             JOIN customers c ON s.customer_id = c.id
                             JOIN dynamic_surveys ds ON sr.survey_form_id = ds.id
                             LEFT JOIN users u ON sr.surveyor_id = u.id
                             WHERE sr.survey_status = 'pending' OR sr.survey_status = 'submitted' OR sr.survey_status IS NULL
                             ORDER BY sr.submitted_date DESC");
        $responses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'responses' => $responses]);
        exit;
    }

    if ($action === 'bulk_process') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['ids']) || empty($input['action'])) {
            throw new Exception("Invalid request parameters");
        }

        $status = ($input['action'] === 'approve') ? 'approved' : 'rejected';
        $remarks = $input['remarks'] ?? '';
        $approvedBy = $_SESSION['user_id'] ?? 0;

        $placeholders = implode(',', array_fill(0, count($input['ids']), '?'));
        $sql = "UPDATE dynamic_survey_responses SET 
                survey_status = ?, 
                approval_remarks = ?, 
                approved_by = ?, 
                approved_date = NOW() 
                WHERE id IN ($placeholders)";
        
        $stmt = $db->prepare($sql);
        $params = array_merge([$status, $remarks, $approvedBy], $input['ids']);
        $stmt->execute($params);

        echo json_encode(['success' => true, 'count' => $stmt->rowCount()]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Invalid action']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
