<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/Installation.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$installationModel = new Installation();

// Get filter parameters
$statusFilter = $_GET['status'] ?? '';
$vendorFilter = $_GET['vendor'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

// Get all installations with filters
$installations = $installationModel->getAllInstallations(
    $statusFilter ?: null, 
    $vendorFilter ?: null,
    $dateFrom ?: null,
    $dateTo ?: null
);

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="installations_export_' . date('Y-m-d_H-i-s') . '.xls"');
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
echo '<x:Name>Installations</x:Name>';
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
echo '<th>#</th>';
echo '<th>ID</th>';
echo '<th>Site Code</th>';
echo '<th>Location</th>';
echo '<th>City</th>';
echo '<th>State</th>';
echo '<th>Vendor Name</th>';
echo '<th>Vendor Phone</th>';
echo '<th>Status</th>';
echo '<th>Priority</th>';
echo '<th>Installation Type</th>';
echo '<th>Delegation Date</th>';
echo '<th>Delegated By</th>';
echo '<th>Acknowledged Date</th>';
echo '<th>Expected Start Date</th>';
echo '<th>Expected Completion Date</th>';
echo '<th>Actual Start Date</th>';
echo '<th>Actual Completion Date</th>';
echo '<th>Days Since Delegation</th>';
echo '<th>Days Without Start</th>';
echo '<th>Is Overdue</th>';
echo '<th>Special Instructions</th>';
echo '<th>Notes</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

$serialNumber = 1;
foreach ($installations as $installation) {
    // Calculate days since delegation
    $delegationDate = new DateTime($installation['delegation_date']);
    $now = new DateTime();
    $daysSinceDelegation = $now->diff($delegationDate)->days;
    
    // Calculate days without start (for assigned/acknowledged status)
    $daysWithoutStart = '';
    if (in_array($installation['status'], ['assigned', 'acknowledged']) && !$installation['actual_start_date']) {
        $daysWithoutStart = $daysSinceDelegation;
    }
    
    // Check if overdue
    $isOverdue = 'No';
    if ($installation['expected_completion_date']) {
        $expectedDate = new DateTime($installation['expected_completion_date']);
        if ($expectedDate < $now && !in_array($installation['status'], ['completed', 'cancelled'])) {
            $isOverdue = 'Yes';
        }
    }
    
    echo '<tr>';
    echo '<td>' . $serialNumber++ . '</td>';
    echo '<td>' . htmlspecialchars($installation['id']) . '</td>';
    echo '<td>' . htmlspecialchars($installation['site_code'] ?? 'N/A') . '</td>';
    echo '<td>' . htmlspecialchars($installation['location'] ?? 'N/A') . '</td>';
    echo '<td>' . htmlspecialchars($installation['city_name'] ?? 'N/A') . '</td>';
    echo '<td>' . htmlspecialchars($installation['state_name'] ?? 'N/A') . '</td>';
    echo '<td>' . htmlspecialchars($installation['vendor_name'] ?? 'Unknown') . '</td>';
    echo '<td>' . htmlspecialchars($installation['vendor_phone'] ?? 'N/A') . '</td>';
    echo '<td>' . htmlspecialchars(ucfirst(str_replace('_', ' ', $installation['status']))) . '</td>';
    echo '<td>' . htmlspecialchars(ucfirst($installation['priority'])) . '</td>';
    echo '<td>' . htmlspecialchars(ucfirst(str_replace('_', ' ', $installation['installation_type'] ?? 'standard'))) . '</td>';
    echo '<td>' . htmlspecialchars(date('Y-m-d H:i', strtotime($installation['delegation_date']))) . '</td>';
    echo '<td>' . htmlspecialchars($installation['delegated_by_name'] ?? 'Unknown') . '</td>';
    echo '<td>' . ($installation['acknowledged_date'] ?? ($installation['status'] === 'acknowledged' ? date('Y-m-d H:i', strtotime($installation['updated_at'] ?? $installation['delegation_date'])) : 'Not acknowledged')) . '</td>';
    echo '<td>' . ($installation['expected_start_date'] ? htmlspecialchars(date('Y-m-d', strtotime($installation['expected_start_date']))) : 'Not set') . '</td>';
    echo '<td>' . ($installation['expected_completion_date'] ? htmlspecialchars(date('Y-m-d', strtotime($installation['expected_completion_date']))) : 'Not set') . '</td>';
    echo '<td>' . ($installation['actual_start_date'] ? htmlspecialchars(date('Y-m-d H:i', strtotime($installation['actual_start_date']))) : 'Not started') . '</td>';
    echo '<td>' . ($installation['actual_completion_date'] ? htmlspecialchars(date('Y-m-d H:i', strtotime($installation['actual_completion_date']))) : 'Not completed') . '</td>';
    echo '<td>' . $daysSinceDelegation . '</td>';
    echo '<td>' . ($daysWithoutStart !== '' ? $daysWithoutStart : 'N/A') . '</td>';
    echo '<td>' . $isOverdue . '</td>';
    echo '<td>' . htmlspecialchars($installation['special_instructions'] ?? '') . '</td>';
    echo '<td>' . htmlspecialchars($installation['notes'] ?? '') . '</td>';
    echo '</tr>';
}

echo '</tbody>';
echo '</table>';
echo '</body>';
echo '</html>';

exit;
?>
