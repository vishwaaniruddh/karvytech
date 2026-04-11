<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';

// Require admin authentication
Auth::requireRole('admin');

// Get parameters
$type = $_GET['type'] ?? 'banks';
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';

// Validate type
$allowedTypes = ['banks', 'customers', 'zones', 'countries', 'states', 'cities', 'boq', 'vendors'];
if (!in_array($type, $allowedTypes)) {
    $type = 'banks';
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Build query based on type
    $sql = '';
    $params = [];
    $headers = [];
    
    switch ($type) {
        case 'banks':
            $sql = "SELECT id, name, status, created_at FROM banks WHERE 1=1";
            $headers = ['ID', 'Name', 'Status', 'Created At'];
            break;
            
        case 'customers':
            $sql = "SELECT id, name, status, created_at FROM customers WHERE 1=1";
            $headers = ['ID', 'Name', 'Status', 'Created At'];
            break;
            
        case 'zones':
            $sql = "SELECT id, name, status, created_at FROM zones WHERE 1=1";
            $headers = ['ID', 'Name', 'Status', 'Created At'];
            break;
            
        case 'countries':
            $sql = "SELECT id, name, status, created_at FROM countries WHERE 1=1";
            $headers = ['ID', 'Name', 'Status', 'Created At'];
            break;
            
        case 'states':
            $sql = "SELECT s.id, s.name, c.name as country_name, s.status, s.created_at 
                    FROM states s 
                    LEFT JOIN countries c ON s.country_id = c.id 
                    WHERE 1=1";
            $headers = ['ID', 'Name', 'Country', 'Status', 'Created At'];
            break;
            
        case 'cities':
            $sql = "SELECT c.id, c.name, s.name as state_name, co.name as country_name, c.status, c.created_at 
                    FROM cities c 
                    LEFT JOIN states s ON c.state_id = s.id 
                    LEFT JOIN countries co ON c.country_id = co.id 
                    WHERE 1=1";
            $headers = ['ID', 'Name', 'State', 'Country', 'Status', 'Created At'];
            break;
            
        case 'boq':
            $sql = "SELECT id, item_name as name, item_code, unit, is_serial_number_required as serial_required, status, created_at FROM boq_items WHERE 1=1";
            $headers = ['ID', 'Item Name', 'Item Code', 'Unit', 'Serial Required', 'Status', 'Created At'];
            break;
            
        case 'vendors':
            $sql = "SELECT id, vendor_code, name, company_name, email, phone, contact_person, 
                    bank_name, account_number, ifsc_code, gst_number, status, created_at 
                    FROM vendors WHERE 1=1";
            $headers = ['ID', 'Vendor Code', 'Name', 'Company Name', 'Email', 'Phone', 'Contact Person', 
                       'Bank Name', 'Account Number', 'IFSC Code', 'GST Number', 'Status', 'Created At'];
            break;
    }
    
    // Add search filter
    if ($search) {
        if ($type === 'boq') {
            $sql .= " AND (item_name LIKE ? OR item_code LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        } elseif ($type === 'customers' || $type === 'banks' || $type === 'zones' || $type === 'countries') {
            $sql .= " AND name LIKE ?";
            $params[] = "%$search%";
        } elseif ($type === 'vendors') {
            $sql .= " AND (name LIKE ? OR company_name LIKE ? OR vendor_code LIKE ? OR email LIKE ? OR phone LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        } elseif ($type === 'states') {
            $sql .= " AND s.name LIKE ?";
            $params[] = "%$search%";
        } elseif ($type === 'cities') {
            $sql .= " AND c.name LIKE ?";
            $params[] = "%$search%";
        } else {
            $sql .= " AND name LIKE ?";
            $params[] = "%$search%";
        }
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
    header('Content-Disposition: attachment; filename="' . $type . '_export_' . date('Y-m-d_H-i-s') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Create file pointer connected to the output stream
    $output = fopen('php://output', 'w');
    
    // Add CSV headers
    fputcsv($output, $headers);
    
    // Add data rows
    foreach ($data as $row) {
        $csvRow = [];
        foreach ($row as $value) {
            $csvRow[] = $value;
        }
        fputcsv($output, $csvRow);
    }
    
    // Close the file pointer
    fclose($output);
    exit;
    
} catch (Exception $e) {
    error_log('Export error: ' . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    echo 'Export failed: ' . $e->getMessage();
    exit;
}
?>