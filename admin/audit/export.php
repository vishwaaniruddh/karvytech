<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/AuditLog.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$auditLog = new AuditLog();

// Get filter parameters
$filters = [
    'username' => $_GET['username'] ?? '',
    'action_type' => $_GET['action_type'] ?? '',
    'status_code' => $_GET['status_code'] ?? '',
    'endpoint' => $_GET['endpoint'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? '',
    'limit' => $_GET['limit'] ?? 1000
];

// Get logs
$logs = $auditLog->getAllLogs($filters);

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="audit_logs_' . date('Y-m-d_H-i-s') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// Output Excel content
echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
echo '<head>';
echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
echo '</head>';
echo '<body>';

echo '<table border="1">';
echo '<thead>';
echo '<tr style="background-color: #f3f4f6; font-weight: bold;">';
echo '<th>ID</th>';
echo '<th>User ID</th>';
echo '<th>Username</th>';
echo '<th>User Role</th>';
echo '<th>Action Type</th>';
echo '<th>Endpoint</th>';
echo '<th>HTTP Method</th>';
echo '<th>Status Code</th>';
echo '<th>IP Address</th>';
echo '<th>Execution Time (s)</th>';
echo '<th>Session ID</th>';
echo '<th>Timestamp</th>';
echo '<th>Error Message</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

foreach ($logs as $log) {
    echo '<tr>';
    echo '<td>' . htmlspecialchars($log['id']) . '</td>';
    echo '<td>' . htmlspecialchars($log['user_id'] ?? 'N/A') . '</td>';
    echo '<td>' . htmlspecialchars($log['username'] ?? 'N/A') . '</td>';
    echo '<td>' . htmlspecialchars($log['user_role'] ?? 'N/A') . '</td>';
    echo '<td>' . htmlspecialchars($log['action_type']) . '</td>';
    echo '<td>' . htmlspecialchars($log['endpoint']) . '</td>';
    echo '<td>' . htmlspecialchars($log['http_method'] ?? 'N/A') . '</td>';
    echo '<td>' . htmlspecialchars($log['status_code'] ?? 'N/A') . '</td>';
    echo '<td>' . htmlspecialchars($log['ip_address'] ?? 'N/A') . '</td>';
    echo '<td>' . htmlspecialchars($log['execution_time'] ?? 'N/A') . '</td>';
    echo '<td>' . htmlspecialchars($log['session_id'] ?? 'N/A') . '</td>';
    echo '<td>' . htmlspecialchars(date('Y-m-d H:i:s', strtotime($log['created_at']))) . '</td>';
    echo '<td>' . htmlspecialchars($log['error_message'] ?? '') . '</td>';
    echo '</tr>';
}

echo '</tbody>';
echo '</table>';
echo '</body>';
echo '</html>';

exit;
?>
