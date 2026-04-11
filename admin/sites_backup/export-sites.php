<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';

// Require admin authentication
Auth::requireRole('admin');

// Get parameters
$search = $_GET['search'] ?? '';
$city = $_GET['city'] ?? '';
$state = $_GET['state'] ?? '';
$status = $_GET['status'] ?? '';
$survey_status = $_GET['survey_status'] ?? '';

try {
    $db = Database::getInstance()->getConnection();
    
    // Build the query with all necessary joins
    $sql = "SELECT 
                s.id,
                s.site_name,
                s.site_code,
                s.address,
                c.name as city_name,
                st.name as state_name,
                co.name as country_name,
                s.pincode,
                s.contact_person,
                s.contact_phone,
                s.contact_email,
                s.status,
                s.created_at,
                s.updated_at,
                COALESCE(ss.survey_status, 'pending') as survey_status,
                ss.survey_date,
                ss.surveyor_name,
                v.name as vendor_name
            FROM sites s
            LEFT JOIN cities c ON s.city_id = c.id
            LEFT JOIN states st ON c.state_id = st.id
            LEFT JOIN countries co ON st.country_id = co.id
            LEFT JOIN site_surveys ss ON s.id = ss.site_id
            LEFT JOIN vendors v ON ss.vendor_id = v.id
            WHERE 1=1";
    
    $params = [];
    
    // Add search filter
    if ($search) {
        $sql .= " AND (s.site_name LIKE ? OR s.site_code LIKE ? OR s.address LIKE ? OR s.contact_person LIKE ? OR s.contact_email LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    // Add city filter
    if ($city) {
        $sql .= " AND s.city_id = ?";
        $params[] = $city;
    }
    
    // Add state filter
    if ($state) {
        $sql .= " AND c.state_id = ?";
        $params[] = $state;
    }
    
    // Add status filter
    if ($status) {
        $sql .= " AND s.status = ?";
        $params[] = $status;
    }
    
    // Add survey status filter
    if ($survey_status) {
        if ($survey_status === 'pending') {
            $sql .= " AND (ss.survey_status IS NULL OR ss.survey_status = 'pending')";
        } else {
            $sql .= " AND ss.survey_status = ?";
            $params[] = $survey_status;
        }
    }
    
    $sql .= " ORDER BY s.created_at DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="sites_export_' . date('Y-m-d_H-i-s') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Create file pointer connected to the output stream
    $output = fopen('php://output', 'w');
    
    // Add CSV headers
    $headers = [
        'ID',
        'Site Name',
        'Site Code',
        'Address',
        'City',
        'State',
        'Country',
        'Pincode',
        'Contact Person',
        'Contact Phone',
        'Contact Email',
        'Status',
        'Survey Status',
        'Survey Date',
        'Surveyor Name',
        'Vendor Name',
        'Created At',
        'Updated At'
    ];
    
    fputcsv($output, $headers);
    
    // Add data rows
    foreach ($data as $row) {
        $csvRow = [
            $row['id'],
            $row['site_name'],
            $row['site_code'],
            $row['address'],
            $row['city_name'],
            $row['state_name'],
            $row['country_name'],
            $row['pincode'],
            $row['contact_person'],
            $row['contact_phone'],
            $row['contact_email'],
            $row['status'],
            $row['survey_status'],
            $row['survey_date'],
            $row['surveyor_name'],
            $row['vendor_name'],
            $row['created_at'],
            $row['updated_at']
        ];
        fputcsv($output, $csvRow);
    }
    
    // Close the file pointer
    fclose($output);
    exit;
    
} catch (Exception $e) {
    error_log('Sites export error: ' . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    echo 'Export failed: ' . $e->getMessage();
    exit;
}
?>