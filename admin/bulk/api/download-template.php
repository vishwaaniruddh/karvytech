<?php
require_once __DIR__ . '/../../../config/auth.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$type = $_GET['type'] ?? '';

if (!$type) {
    http_response_code(400);
    echo 'Template type is required';
    exit;
}

switch ($type) {
    case 'sites':
        $filename = 'sites_import_template.csv';
        $headers = [
            'site_id',
            'location', 
            'customer_id',
            'city_id',
            'state_id',
            'priority',
            'notes'
        ];
        
        $sampleData = [
            [
                'SITE001',
                '123 Main Street, Downtown Area, City Name',
                '1',
                '1',
                '1',
                'normal',
                'Sample site for import'
            ],
            [
                'SITE002',
                '456 Business Park, Industrial Zone, City Name',
                '2',
                '2',
                '1',
                'high',
                'High priority site'
            ]
        ];
        break;
        
    default:
        http_response_code(400);
        echo 'Invalid template type';
        exit;
}

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');

// Create CSV output
$output = fopen('php://output', 'w');

// Write headers
fputcsv($output, $headers);

// Write sample data
foreach ($sampleData as $row) {
    fputcsv($output, $row);
}

fclose($output);
exit;
?>