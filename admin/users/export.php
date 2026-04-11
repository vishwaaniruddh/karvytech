<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/User.php';

// Require admin authentication
Auth::requireRole('admin');

$userModel = new User();

// Get filters from query parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$role = isset($_GET['role']) ? trim($_GET['role']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';

$filters = [
    'role' => $role,
    'status' => $status
];

// Get all users (no pagination for export)
$result = $userModel->getAllWithPagination(1, 10000, $search, $filters);
$users = $result['users'];

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="users_export_' . date('Y-m-d_H-i-s') . '.csv"');
header('Pragma: no-cache');
header('Expires: 0');

// Create file pointer connected to the output stream
$output = fopen('php://output', 'w');

// Add CSV headers
fputcsv($output, [
    'ID',
    'Username',
    'Email',
    'Phone',
    'Role',
    'Vendor Name',
    'Status',
    'Created At',
    'Updated At'
]);

// Add user data
foreach ($users as $user) {
    fputcsv($output, [
        $user['id'],
        $user['username'],
        $user['email'],
        $user['phone'] ?? '',
        ucfirst($user['role']),
        $user['vendor_name'] ?? '',
        ucfirst($user['status']),
        $user['created_at'],
        $user['updated_at']
    ]);
}

// Close the file pointer
fclose($output);
exit;
?>