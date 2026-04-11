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
$sections = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Build structured field list with section hierarchy
$structuredFields = [];
foreach ($sections as $section) {
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

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="Survey_Responses_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $customerName) . '_' . date('Y-m-d_His') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// Start output
echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';
echo '<Styles>';
echo '<Style ss:ID="sectionHeader">';
echo '<Font ss:Bold="1" ss:Color="#FFFFFF"/>';
echo '<Interior ss:Color="#4472C4" ss:Pattern="Solid"/>';
echo '<Alignment ss:Horizontal="Center" ss:Vertical="Center"/>';
echo '</Style>';
echo '<Style ss:ID="fieldHeader">';
echo '<Font ss:Bold="1"/>';
echo '<Interior ss:Color="#D9E1F2" ss:Pattern="Solid"/>';
echo '<Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>';
echo '</Style>';
echo '</Styles>';
echo '<Worksheet ss:Name="Survey Responses">';
echo '<Table>';

// Calculate column spans for merged headers
$siteDataCols = 15; // Fixed site data columns
$totalCols = $siteDataCols + count($structuredFields);

// Row 1: Section headers (merged cells)
echo '<Row ss:Height="30">';
// Site data section
echo '<Cell ss:MergeAcross="' . ($siteDataCols - 1) . '" ss:StyleID="sectionHeader"><Data ss:Type="String">Site & Response Information</Data></Cell>';

// Form sections with merged cells
$currentSection = null;
$sectionStartCol = $siteDataCols;
$sectionFieldCount = 0;

foreach ($structuredFields as $idx => $fieldInfo) {
    if ($currentSection !== $fieldInfo['full_path']) {
        // Output previous section merge if exists
        if ($currentSection !== null && $sectionFieldCount > 0) {
            if ($sectionFieldCount > 1) {
                echo '<Cell ss:MergeAcross="' . ($sectionFieldCount - 1) . '" ss:StyleID="sectionHeader"><Data ss:Type="String">' . htmlspecialchars($currentSection) . '</Data></Cell>';
            } else {
                echo '<Cell ss:StyleID="sectionHeader"><Data ss:Type="String">' . htmlspecialchars($currentSection) . '</Data></Cell>';
            }
        }
        
        // Start new section
        $currentSection = $fieldInfo['full_path'];
        $sectionFieldCount = 1;
    } else {
        $sectionFieldCount++;
    }
}

// Output last section
if ($currentSection !== null && $sectionFieldCount > 0) {
    if ($sectionFieldCount > 1) {
        echo '<Cell ss:MergeAcross="' . ($sectionFieldCount - 1) . '" ss:StyleID="sectionHeader"><Data ss:Type="String">' . htmlspecialchars($currentSection) . '</Data></Cell>';
    } else {
        echo '<Cell ss:StyleID="sectionHeader"><Data ss:Type="String">' . htmlspecialchars($currentSection) . '</Data></Cell>';
    }
}
echo '</Row>';

// Row 2: Field headers
echo '<Row ss:Height="40">';
echo '<Cell ss:StyleID="fieldHeader"><Data ss:Type="String">Response ID</Data></Cell>';
echo '<Cell ss:StyleID="fieldHeader"><Data ss:Type="String">Submitted Date</Data></Cell>';
echo '<Cell ss:StyleID="fieldHeader"><Data ss:Type="String">Status</Data></Cell>';
echo '<Cell ss:StyleID="fieldHeader"><Data ss:Type="String">Surveyor</Data></Cell>';
echo '<Cell ss:StyleID="fieldHeader"><Data ss:Type="String">Site ID</Data></Cell>';
echo '<Cell ss:StyleID="fieldHeader"><Data ss:Type="String">Store ID</Data></Cell>';
echo '<Cell ss:StyleID="fieldHeader"><Data ss:Type="String">Site Ticket ID</Data></Cell>';
echo '<Cell ss:StyleID="fieldHeader"><Data ss:Type="String">Branch</Data></Cell>';
echo '<Cell ss:StyleID="fieldHeader"><Data ss:Type="String">Location</Data></Cell>';
echo '<Cell ss:StyleID="fieldHeader"><Data ss:Type="String">City</Data></Cell>';
echo '<Cell ss:StyleID="fieldHeader"><Data ss:Type="String">State</Data></Cell>';
echo '<Cell ss:StyleID="fieldHeader"><Data ss:Type="String">Country</Data></Cell>';
echo '<Cell ss:StyleID="fieldHeader"><Data ss:Type="String">Pincode</Data></Cell>';
echo '<Cell ss:StyleID="fieldHeader"><Data ss:Type="String">Customer</Data></Cell>';
echo '<Cell ss:StyleID="fieldHeader"><Data ss:Type="String">Vendor</Data></Cell>';

// Form field headers (just field labels)
foreach ($structuredFields as $fieldInfo) {
    echo '<Cell ss:StyleID="fieldHeader"><Data ss:Type="String">' . htmlspecialchars($fieldInfo['field_label']) . '</Data></Cell>';
}
echo '</Row>';

// Data rows
foreach ($responses as $response) {
    $formData = json_decode($response['form_data'], true);
    
    echo '<Row>';
    echo '<Cell><Data ss:Type="Number">' . $response['id'] . '</Data></Cell>';
    echo '<Cell><Data ss:Type="String">' . htmlspecialchars(date('Y-m-d H:i:s', strtotime($response['submitted_date']))) . '</Data></Cell>';
    echo '<Cell><Data ss:Type="String">' . htmlspecialchars(ucfirst($response['survey_status'])) . '</Data></Cell>';
    echo '<Cell><Data ss:Type="String">' . htmlspecialchars($response['surveyor_name'] ?? 'Unknown') . '</Data></Cell>';
    
    // Site data
    echo '<Cell><Data ss:Type="String">' . htmlspecialchars($response['site_id'] ?? '') . '</Data></Cell>';
    echo '<Cell><Data ss:Type="String">' . htmlspecialchars($response['store_id'] ?? '') . '</Data></Cell>';
    echo '<Cell><Data ss:Type="String">' . htmlspecialchars($response['site_ticket_id'] ?? '') . '</Data></Cell>';
    echo '<Cell><Data ss:Type="String">' . htmlspecialchars($response['branch'] ?? '') . '</Data></Cell>';
    echo '<Cell><Data ss:Type="String">' . htmlspecialchars($response['location'] ?? '') . '</Data></Cell>';
    echo '<Cell><Data ss:Type="String">' . htmlspecialchars($response['city_name'] ?? '') . '</Data></Cell>';
    echo '<Cell><Data ss:Type="String">' . htmlspecialchars($response['state_name'] ?? '') . '</Data></Cell>';
    echo '<Cell><Data ss:Type="String">' . htmlspecialchars($response['country_name'] ?? '') . '</Data></Cell>';
    echo '<Cell><Data ss:Type="String">' . htmlspecialchars($response['pincode'] ?? '') . '</Data></Cell>';
    echo '<Cell><Data ss:Type="String">' . htmlspecialchars($response['customer_name'] ?? '') . '</Data></Cell>';
    echo '<Cell><Data ss:Type="String">' . htmlspecialchars($response['vendor'] ?? '') . '</Data></Cell>';
    
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
        
        echo '<Cell><Data ss:Type="String">' . htmlspecialchars($cellValue) . '</Data></Cell>';
    }
    echo '</Row>';
}

echo '</Table>';
echo '</Worksheet>';
echo '</Workbook>';
exit;
?>
