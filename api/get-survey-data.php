<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!Auth::isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$responseId = $_GET['response_id'] ?? null;

if (!$responseId) {
    echo json_encode(['success' => false, 'message' => 'Missing response_id']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Fetch survey response with site details
    $stmt = $db->prepare("
        SELECT sr.*, 
               ds.title as survey_title, 
               ds.description as survey_description,
               s.site_id, s.store_id, s.site_ticket_id,
               s.city, s.state, s.country, s.zone, s.customer, s.vendor
        FROM dynamic_survey_responses sr
        LEFT JOIN dynamic_surveys ds ON sr.survey_form_id = ds.id
        LEFT JOIN sites s ON sr.site_id = s.id
        WHERE sr.id = ?
    ");
    $stmt->execute([$responseId]);
    $response = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$response) {
        echo json_encode(['success' => false, 'message' => 'Survey response not found']);
        exit;
    }
    
    // Get form structure
    $formStructure = ['sections' => []];
    
    // Fetch main sections
    $stmt = $db->prepare("SELECT * FROM dynamic_survey_sections WHERE survey_id = ? AND parent_section_id IS NULL ORDER BY sort_order ASC");
    $stmt->execute([$response['survey_form_id']]);
    $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($sections as &$section) {
        // Get fields for this section
        $stmt = $db->prepare("SELECT * FROM dynamic_survey_fields WHERE section_id = ? ORDER BY sort_order ASC");
        $stmt->execute([$section['id']]);
        $section['fields'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get subsections
        $stmt = $db->prepare("SELECT * FROM dynamic_survey_sections WHERE parent_section_id = ? ORDER BY sort_order ASC");
        $stmt->execute([$section['id']]);
        $subsections = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($subsections as &$subsection) {
            // Get fields for subsection
            $stmt = $db->prepare("SELECT * FROM dynamic_survey_fields WHERE section_id = ? ORDER BY sort_order ASC");
            $stmt->execute([$subsection['id']]);
            $subsection['fields'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        $section['subsections'] = $subsections;
    }
    
    $formStructure['sections'] = $sections;
    
    // Parse form data
    $formData = json_decode($response['form_data'], true) ?? [];
    $siteMasterData = json_decode($response['site_master_data'], true) ?? [];
    
    echo json_encode([
        'success' => true,
        'response' => [
            'id' => $response['id'],
            'site_id' => $response['site_id'],
            'store_id' => $response['store_id'],
            'site_ticket_id' => $response['site_ticket_id'],
            'survey_title' => $response['survey_title'],
            'survey_description' => $response['survey_description'],
            'survey_status' => $response['survey_status'],
            'survey_started_at' => $response['survey_started_at'],
            'survey_ended_at' => $response['survey_ended_at'],
            'is_draft' => $response['is_draft'],
            'last_saved_at' => $response['last_saved_at'],
            'site_info' => [
                'city' => $response['city'],
                'state' => $response['state'],
                'country' => $response['country'],
                'zone' => $response['zone'],
                'customer' => $response['customer'],
                'vendor' => $response['vendor']
            ]
        ],
        'formStructure' => $formStructure,
        'formData' => $formData,
        'siteMasterData' => $siteMasterData
    ]);
    
} catch (Exception $e) {
    error_log('Get survey data error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch survey data: ' . $e->getMessage()
    ]);
}
