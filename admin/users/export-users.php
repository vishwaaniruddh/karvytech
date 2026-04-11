<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';

// Require admin authentication
Auth::requireRole('admin');

// Get parameters
$search = $_GET['search'] ?? '';
$role = $_GET['role'] ?? '';
$status = $_GET['status'] ?? '';

try {
    $db = Database::getInstance()->getConnection();
    
    // Build the query
    $sql = "SELECT 
                id,
                username,
                email,
                phone,
                role,
                status,
                created_at,
                updated_at
            FROM users 
            WHERE 1=1";
    
    $params = [];
    
    // Add search filter
    if ($search) {
        $sql .= " AND (username LIKE ? OR email LIKE ? OR phone LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    // Add role filter
    if ($role) {
        $sql .= " AND role = ?";
        $params[] = $role;
    }
    
    // Add status filter
    if ($status) {
        $sql .= " AND status = ?";
        $params[] = $status;
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="users_export_' . date('Y-m-d_H-i-s') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Create file pointer connected to the output stream
    $output = fopen('php://output', 'w');
    
    // Add CSV headers
    $headers = [
        'ID',
        'Username',
        'Email',
        'Phone',
        'Role',
        'Status',
        'Created At',
        'Updated At'
    ];
    
    fputcsv($output, $headers);
    
    // Add data rows
    foreach ($data as $row) {
        $csvRow = [
            $row['id'],
            $row['username'],
            $row['email'],
            $row['phone'] ?? 'N/A',
            $row['role'],
            $row['status'],
            $row['created_at'],
            $row['updated_at']
        ];
        fputcsv($output, $csvRow);
    }
    
    // Close the file pointer
    fclose($output);
    exit;
    
} catch (Exception $e) {
    error_log('Users export error: ' . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    echo 'Export failed: ' . $e->getMessage();
    exit;
}
?>