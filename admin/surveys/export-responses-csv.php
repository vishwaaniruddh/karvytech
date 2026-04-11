<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';

$customerId = $_GET['customer_id'] ?? null;

if (!$customerId) {
    die('Customer ID required');
}

$db = Database::getInstance()->getConnection();

// Get customer name
$stmt = $db->prepare("SELECT name FROM customers WHERE id = ?");
$stmt->execute([$customerId]);
$customerName = $stmt->fetchColumn();

// Get survey form for this customer
$stmt = $db->prepare("SELECT id, title, description FROM dynamic_surveys 
                      WHERE (customer_id = ? OR customer_id IS NULL) 
                      AND form_type = 'survey' AND status = 'active' 
                      ORDER BY customer_id DESC LIMIT 1");
$stmt->execute([$customerId]);
$surveyForm = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$surveyForm) {
    die('No survey form found for this customer');
}

// Get survey structure with proper hierarchy
$stmt = $db->prepare("SELECT * FROM dynamic_survey_sections 
                     WHERE survey_id = ? AND parent_section_id IS NULL 
                     ORDER BY sort_order ASC");
$stmt->execute([$surveyForm['id']]);
$parentSections = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Build structured field list with section hierarchy
$structuredFields = [];
foreach ($parentSections as $section) {
    // Get direct fields of parent section
    $stmt = $db->prepare("SELECT * FROM dynamic_survey_fields 
                         WHERE section_id = ? ORDER BY sort_order ASC");
    $stmt->execute([$section['id']]);
    $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($fields as $field) {
        $structuredFields[] = [
            'field_id' => $field['id'],
            'section_name' => $section['title'],
            'subsection_name' => null,
            'field_label' => $field['label'],
            'field_type' => $field['field_type'],
            'full_path' => $section['title']
        ];
    }
    
    // Get subsections
    $stmt = $db->prepare("SELECT * FROM dynamic_survey_sections 
                         WHERE parent_section_id = ? ORDER BY sort_order ASC");
    $stmt->execute([$section['id']]);
    $subsections = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($subsections as $subsection) {
        $stmt = $db->prepare("SELECT * FROM dynamic_survey_fields 
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

// Get responses with full site data
$stmt = $db->prepare("SELECT sr.*, 
                     s.site_id, s.store_id, s.site_ticket_id, s.branch, s.location, 
                     s.pincode, s.contact_person_name, s.contact_person_number, s.contact_person_email,
                     s.vendor,
                     CONCAT_WS(' ', u.first_name, u.last_name) as surveyor_name,
                     c.name as customer_name,
                     ci.name as city_name,
                     st.name as state_name,
                     co.name as country_name
                     FROM dynamic_survey_responses sr
                     LEFT JOIN sites s ON sr.site_id = s.id
                     LEFT JOIN users u ON sr.surveyor_id = u.id
                     LEFT JOIN customers c ON s.customer_id = c.id
                     LEFT JOIN cities ci ON s.city_id = ci.id
                     LEFT JOIN states st ON s.state_id = st.id
                     LEFT JOIN countries co ON s.country_id = co.id
                     WHERE sr.survey_form_id = ?
                     ORDER BY sr.submitted_date DESC");
$stmt->execute([$surveyForm['id']]);
$responses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="Survey_Responses_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $customerName) . '_' . date('Y-m-d_His') . '.csv"');
header('Pragma: no-cache');
header('Expires: 0');

// Create output stream
$output = fopen('php://output', 'w');

// UTF-8 BOM for Excel compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Header row
$headers = [
    'Response ID',
    'Submitted Date',
    'Status',
    'Surveyor',
    'Site ID',
    'Store ID',
    'Site Ticket ID',
    'Branch',
    'Location',
    'City',
    'State',
    'Country',
    'Pincode',
    'Customer',
    'Vendor'
];

// Add form field headers
foreach ($structuredFields as $fieldInfo) {
    $headers[] = $fieldInfo['full_path'] . ' - ' . $fieldInfo['field_label'];
}

fputcsv($output, $headers);

// Data rows
foreach ($responses as $response) {
    $formData = json_decode($response['form_data'], true);
    
    $row = [
        $response['id'],
        date('Y-m-d H:i:s', strtotime($response['submitted_date'])),
        ucfirst($response['survey_status']),
        $response['surveyor_name'] ?? 'Unknown',
        $response['site_id'] ?? '',
        $response['store_id'] ?? '',
        $response['site_ticket_id'] ?? '',
        $response['branch'] ?? '',
        $response['location'] ?? '',
        $response['city_name'] ?? '',
        $response['state_name'] ?? '',
        $response['country_name'] ?? '',
        $response['pincode'] ?? '',
        $response['customer_name'] ?? '',
        $response['vendor'] ?? ''
    ];
    
    // Form data
    foreach ($structuredFields as $fieldInfo) {
        $value = $formData[$fieldInfo['field_id']] ?? '';
        
        // Handle different field types
        if ($fieldInfo['field_type'] === 'file' && is_array($value)) {
            // For files, show file names
            if (isset($value['original_name'])) {
                $cellValue = $value['original_name'];
            } else {
                $fileNames = array_map(function($file) {
                    return $file['original_name'] ?? '';
                }, $value);
                $cellValue = implode(', ', $fileNames);
            }
        } elseif (is_array($value)) {
            // For checkboxes or multi-select
            $cellValue = implode(', ', $value);
        } else {
            $cellValue = $value;
        }
        
        $row[] = $cellValue;
    }
    
    fputcsv($output, $row);
}

fclose($output);
exit;
?>
