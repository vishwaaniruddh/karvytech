<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/Site.php';
require_once __DIR__ . '/../../models/SiteSurvey.php';
require_once __DIR__ . '/../../models/MaterialRequest.php';
require_once __DIR__ . '/../../models/Installation.php';
require_once __DIR__ . '/../../models/Vendor.php';
require_once __DIR__ . '/../../models/User.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$reportType = $_POST['report_type'] ?? '';

if (!$reportType) {
    die('Invalid report type');
}

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $reportType . '_report_' . date('Y-m-d_H-i-s') . '.csv"');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

// Create output stream
$output = fopen('php://output', 'w');

try {
    switch ($reportType) {
        case 'sites':
            exportSitesReport($output);
            break;
        case 'surveys':
            exportSurveysReport($output);
            break;
        case 'materials':
            exportMaterialsReport($output);
            break;
        case 'installations':
            exportInstallationsReport($output);
            break;
        default:
            die('Unknown report type');
    }
} catch (Exception $e) {
    fputcsv($output, ['Error', $e->getMessage()]);
}

fclose($output);
exit;

function exportSitesReport($output) {
    $siteModel = new Site();
    
    // CSV Headers
    $headers = [
        'Site ID',
        'Location',
        'PO Number',
        'Site Ticket Id',
        'City',
        'State', 
        'Country',
        'Customer',
        'Vendor',
        'Delegation Status',
        'Survey Status',
        'Created Date',
        'Updated Date'
    ];
    fputcsv($output, $headers);
    
    // Get all sites with details
    $result = $siteModel->getAllWithPagination(1, 10000); // Get all sites
    $sites = $result['sites'];
    
    foreach ($sites as $site) {
        if($site['is_delegate']==0){
            $site_delegate_status = 'Not Delegate';
        }else{
            $site_delegate_status = 'Delegated';
        }
        
        $row = [
            // $site['site_id'] ?? '',
            // $site['location'] ?? '',
            // $site['address'] ?? '',
            // $site['city_name'] ?? '',
            // $site['state_name'] ?? '',
            // $site['country_name'] ?? '',
            // $site['customer_name'] ?? '',
            // $site['bank_name'] ?? '',
            // $site['vendor_name'] ?? '',
            // $site['status'] ?? '',
            // $site['actual_survey_status'] ?? 'No Survey',
            // $site['created_at'] ?? '',
            // $site['updated_at'] ?? ''
            // $site['delegated_vendor_name'] ?? '',
            // $site['delegation_status'] ?? '',
            
            
        $site['site_id'] ?? '',
        $site['location'] ?? '',
        $site['po_number'] ?? '',
        $site['site_ticket_id'] ?? '',
        $site['city'] ?? '',
        $site['state'] ?? '',
        $site['country'] ?? '',
        $site['customer'] ?? '',
        $site['delegated_vendor'] ?? '',
        $site_delegate_status,
        $site['actual_survey_status'] ?? 'No Survey',
        $site['created_at'] ?? '',
        $site['updated_at'] ?? ''
            
            
        ];
        fputcsv($output, $row);
    }
}

function exportSurveysReport($output) {
    $surveyModel = new SiteSurvey();
    
    // CSV Headers
    $headers = [
        'Survey ID',
        'Site ID',
        'Site Location',
        'City',
        'State',
        'PO Number',
        'Site Ticket Id',
        'Company Name',
        'Survey Status',
        'Submitted Date',
        'Approved Date',
        'Approved By',
        'Survey Data',
        'Notes',
        'Created Date'
    ];
    fputcsv($output, $headers);
    
    // Get all surveys with details
    $surveys = $surveyModel->getAllWithDetails();
    
    foreach ($surveys as $survey) {
        $row = [
            $survey['id'] ?? '',
            $survey['site_id'] ?? '',
            $survey['location'] ?? '',
            $survey['city_name'] ?? '',
            $survey['state_name'] ?? '',
            $survey['po_number'] ?? '',
            $survey['site_ticket_id'] ?? '',
            $survey['vendor_company_name'] ?: $survey['vendor_name'] ?? '',
            $survey['survey_status'] ?? '',
            $survey['submitted_date'] ?? '',
            $survey['approved_date'] ?? '',
            $survey['approved_by_name'] ?? '',
            $survey['survey_data'] ?? '',
            $survey['notes'] ?? '',
            $survey['created_at'] ?? ''
        ];
        fputcsv($output, $row);
    }
}

function exportMaterialsReport($output) {
    $materialModel = new MaterialRequest();
    
    // CSV Headers
    $headers = [
        'Request ID',
        'Site ID',
        'Site Location',
        'City',
        'State',
        'PO Number',
        'Site Ticket Id',
        'Company Name',
        'Request Status',
        'Request Date',
        'Required Date',
        'Total Items',
        'Total Quantity',
        'Request Notes',
        'Dispatch Status',
        'Dispatch Date',
        'Courier Name',
        'Tracking Number',
        'Expected Delivery',
        'Actual Delivery',
        'Created Date'
    ];
    fputcsv($output, $headers);
    
    // Get all material requests with details
    $requests = $materialModel->getAllWithDetails();
    
    foreach ($requests as $request) {
        // Calculate totals from items JSON
        $totalItems = 0;
        $totalQuantity = 0;
        
        if ($request['items']) {
            $items = json_decode($request['items'], true);
            if ($items && is_array($items)) {
                $totalItems = count($items);
                foreach ($items as $item) {
                    if (isset($item['quantity']) && is_numeric($item['quantity'])) {
                        $totalQuantity += (int)$item['quantity'];
                    }
                }
            }
        }
        
        $row = [
            $request['id'] ?? '',
            $request['site_id'] ?? '',
            $request['location'] ?? '',
            $request['city_name'] ?? '',
            $request['state_name'] ?? '',
            $request['po_number'] ?? '',
            $request['site_ticket_id'] ?? '',
            $request['vendor_company_name'] ?: $request['vendor_name'] ?? '',
            $request['status'] ?? '',
            $request['request_date'] ?? '',
            $request['required_date'] ?? '',
            $totalItems,
            $totalQuantity,
            $request['request_notes'] ?? '',
            $request['dispatch_status'] ?? '',
            $request['dispatch_date'] ?? '',
            $request['courier_name'] ?? '',
            $request['tracking_number'] ?? '',
            $request['expected_delivery_date'] ?? '',
            $request['actual_delivery_date'] ?? '',
            $request['created_date'] ?? ''
        ];
        fputcsv($output, $row);
    }
}

function exportInstallationsReport($output) {
    $installationModel = new Installation();
    
    // CSV Headers
    $headers = [
        'Installation ID',
        'Site ID',
        'Site Location',
        'City',
        'State',
        'PO Number',
        'Site Ticket Id',
        'Company Name',
        'Installation Status',
        'Progress Percentage',
        'Start Date',
        'Expected Completion',
        'Actual Completion',
        'Installation Notes',
        'Material Usage',
        'Files Uploaded',
        'Created Date',
        'Updated Date'
    ];
    fputcsv($output, $headers);
    
    // Get all installations with details
    $installations = $installationModel->getAllWithDetails();
    
    foreach ($installations as $installation) {
        // Count files if available
        $filesCount = 0;
        if ($installation['files']) {
            $files = json_decode($installation['files'], true);
            if ($files && is_array($files)) {
                $filesCount = count($files);
            }
        }
        
        $row = [
            $installation['id'] ?? '',
            $installation['site_id'] ?? '',
            $installation['location'] ?? '',
            $installation['city_name'] ?? '',
            $installation['state_name'] ?? '',
            $installation['po_number'] ?? '',
            $installation['site_ticket_id'] ?? '',
            $installation['vendor_company_name'] ?: $installation['vendor_name'] ?? '',
            $installation['status'] ?? '',
            $installation['progress_percentage'] ?? '0',
            $installation['start_date'] ?? '',
            $installation['expected_completion_date'] ?? '',
            $installation['actual_completion_date'] ?? '',
            $installation['notes'] ?? '',
            $installation['material_usage'] ?? '',
            $filesCount . ' files',
            $installation['created_at'] ?? '',
            $installation['updated_at'] ?? ''
        ];
        fputcsv($output, $row);
    }
}
?>