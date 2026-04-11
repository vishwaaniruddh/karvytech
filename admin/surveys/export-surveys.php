<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/SiteSurvey.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$surveyModel = new SiteSurvey();

// Get all surveys
$surveys = $surveyModel->getAllSurveys();

// Apply filters if provided
$surveyStatus = $_GET['survey_status'] ?? '';
$installationStatus = $_GET['installation_status'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

if ($surveyStatus || $installationStatus || $dateFrom || $dateTo) {
    $surveys = array_filter($surveys, function($survey) use ($surveyStatus, $installationStatus, $dateFrom, $dateTo) {
        // Filter by survey status
        if ($surveyStatus && $survey['survey_status'] !== $surveyStatus) {
            return false;
        }
        
        // Filter by installation status
        if ($installationStatus && ($survey['installation_status'] ?? 'not_delegated') !== $installationStatus) {
            return false;
        }
        
        // Filter by date range
        $surveyDate = date('Y-m-d', strtotime($survey['created_at']));
        if ($dateFrom && $surveyDate < $dateFrom) {
            return false;
        }
        if ($dateTo && $surveyDate > $dateTo) {
            return false;
        }
        
        return true;
    });
}

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="surveys_export_' . date('Y-m-d_H-i-s') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// Output Excel content
echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
echo '<head>';
echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
echo '<xml>';
echo '<x:ExcelWorkbook>';
echo '<x:ExcelWorksheets>';
echo '<x:ExcelWorksheet>';
echo '<x:Name>Surveys</x:Name>';
echo '<x:WorksheetOptions>';
echo '<x:Print>';
echo '<x:ValidPrinterInfo/>';
echo '</x:Print>';
echo '</x:WorksheetOptions>';
echo '</x:ExcelWorksheet>';
echo '</x:ExcelWorksheets>';
echo '</x:ExcelWorkbook>';
echo '</xml>';
echo '</head>';
echo '<body>';

echo '<table border="1">';
echo '<thead>';
echo '<tr style="background-color: #f3f4f6; font-weight: bold;">';
echo '<th>ID</th>';
echo '<th>Site Code</th>';
echo '<th>Location</th>';
echo '<th>City</th>';
echo '<th>State</th>';
echo '<th>Vendor Name</th>';
echo '<th>Survey Status</th>';
echo '<th>Installation Status</th>';
echo '<th>Survey Date</th>';
echo '<th>Approved Date</th>';
echo '<th>Days Since Approval</th>';
echo '<th>Feasibility</th>';
echo '<th>Site Type</th>';
echo '<th>Power Availability</th>';
echo '<th>Internet Connectivity</th>';
echo '<th>Remarks</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

foreach ($surveys as $survey) {
    // Calculate days since approval
    $daysSinceApproval = '';
    if ($survey['survey_status'] === 'approved') {
        $approvalDate = new DateTime($survey['updated_at'] ?? $survey['created_at']);
        $now = new DateTime();
        $daysSinceApproval = $now->diff($approvalDate)->days;
    }
    
    echo '<tr>';
    echo '<td>' . htmlspecialchars($survey['id']) . '</td>';
    echo '<td>' . htmlspecialchars($survey['site_code'] ?? 'N/A') . '</td>';
    echo '<td>' . htmlspecialchars($survey['location'] ?? 'N/A') . '</td>';
    echo '<td>' . htmlspecialchars($survey['city_name'] ?? 'N/A') . '</td>';
    echo '<td>' . htmlspecialchars($survey['state_name'] ?? 'N/A') . '</td>';
    echo '<td>' . htmlspecialchars($survey['vendor_name'] ?? 'Unknown') . '</td>';
    echo '<td>' . htmlspecialchars(ucfirst($survey['survey_status'])) . '</td>';
    echo '<td>' . htmlspecialchars(ucfirst(str_replace('_', ' ', $survey['installation_status'] ?? 'not_delegated'))) . '</td>';
    echo '<td>' . htmlspecialchars(date('Y-m-d H:i', strtotime($survey['created_at']))) . '</td>';
    echo '<td>' . ($survey['survey_status'] === 'approved' ? htmlspecialchars(date('Y-m-d H:i', strtotime($survey['updated_at'] ?? $survey['created_at']))) : 'N/A') . '</td>';
    echo '<td>' . ($daysSinceApproval !== '' ? $daysSinceApproval : 'N/A') . '</td>';
    echo '<td>' . htmlspecialchars($survey['feasibility'] ?? 'N/A') . '</td>';
    echo '<td>' . htmlspecialchars($survey['site_type'] ?? 'N/A') . '</td>';
    echo '<td>' . htmlspecialchars($survey['power_availability'] ?? 'N/A') . '</td>';
    echo '<td>' . htmlspecialchars($survey['internet_connectivity'] ?? 'N/A') . '</td>';
    echo '<td>' . htmlspecialchars($survey['remarks'] ?? '') . '</td>';
    echo '</tr>';
}

echo '</tbody>';
echo '</table>';
echo '</body>';
echo '</html>';

exit;
?>
