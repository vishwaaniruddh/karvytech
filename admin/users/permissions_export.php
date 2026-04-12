<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/Permission.php';

// Require admin authentication
Auth::requireRole('admin');

$permissionModel = new Permission();

// Get filters from query parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$moduleId = isset($_GET['module_id']) && !empty($_GET['module_id']) ? (int)$_GET['module_id'] : null;

// Get all permissions (no pagination for export)
$result = $permissionModel->getAllWithPagination(1, 10000, $search, $moduleId);
$permissions = $result['permissions'];

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="permissions_export_' . date('Y-m-d_H-i-s') . '.csv"');
header('Pragma: no-cache');
header('Expires: 0');

// Create file pointer connected to the output stream
$output = fopen('php://output', 'w');

// Add CSV headers
fputcsv($output, [
    'ID',
    'Module',
    'Internal Name',
    'Display Name',
    'Description',
    'Status',
    'Created At'
]);

// Add data
foreach ($permissions as $perm) {
    fputcsv($output, [
        $perm['id'],
        $perm['module_display_name'],
        $perm['name'],
        $perm['display_name'],
        $perm['description'] ?? '',
        ucfirst($perm['status']),
        $perm['created_at']
    ]);
}

// Close the file pointer
fclose($output);
exit;
?>
