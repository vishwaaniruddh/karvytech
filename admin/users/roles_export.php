<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/Role.php';

// Require admin authentication
Auth::requireRole('admin');

$roleModel = new Role();

// Get filters from query parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';

// Get all roles (no pagination for export)
$result = $roleModel->getAllWithPagination(1, 10000, $search, $status);
$roles = $result['roles'];

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="roles_export_' . date('Y-m-d_H-i-s') . '.csv"');
header('Pragma: no-cache');
header('Expires: 0');

// Create file pointer connected to the output stream
$output = fopen('php://output', 'w');

// Add CSV headers
fputcsv($output, [
    'ID',
    'Role Name',
    'Display Name',
    'Description',
    'Status',
    'Created At',
    'Updated At'
]);

// Add role data
foreach ($roles as $role) {
    fputcsv($output, [
        $role['id'],
        $role['name'],
        $role['display_name'],
        $role['description'] ?? '',
        ucfirst($role['status']),
        $role['created_at'],
        $role['updated_at']
    ]);
}

// Close the file pointer
fclose($output);
exit;
?>
