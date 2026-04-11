<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/Vendor.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

try {
    $vendorModel = new Vendor();
    
    // Handle filters
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? '';
    
    $vendors = $vendorModel->getAllVendors($search, $status);
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="vendors_export_' . date('Y-m-d_H-i-s') . '.csv"');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add CSV headers
    $headers = [
        'Vendor Code',
        'Name',
        'Company Name',
        'Email',
        'Phone',
        'Address',
        'Mobility ID',
        'Bank Name',
        'Account Number',
        'IFSC Code',
        'GST Number',
        'PAN Card',
        'Aadhaar Number',
        'MSME Number',
        'ESIC Number',
        'PF Number',
        'PVC Status',
        'Status',
        'Created Date'
    ];
    
    fputcsv($output, $headers);
    
    // Add vendor data
    foreach ($vendors as $vendor) {
        $row = [
            $vendor['vendor_code'] ?? '',
            $vendor['name'] ?? '',
            $vendor['company_name'] ?? '',
            $vendor['email'] ?? '',
            $vendor['phone'] ?? '',
            $vendor['address'] ?? '',
            $vendor['mobility_id'] ?? '',
            $vendor['bank_name'] ?? '',
            $vendor['account_number'] ?? '',
            $vendor['ifsc_code'] ?? '',
            $vendor['gst_number'] ?? '',
            $vendor['pan_card_number'] ?? '',
            $vendor['aadhaar_number'] ?? '',
            $vendor['msme_number'] ?? '',
            $vendor['esic_number'] ?? '',
            $vendor['pf_number'] ?? '',
            $vendor['pvc_status'] ?? '',
            $vendor['status'] ?? '',
            $vendor['created_at'] ?? ''
        ];
        
        fputcsv($output, $row);
    }
    
    fclose($output);
    
} catch (Exception $e) {
    // If there's an error, redirect back with error message
    header('Location: index.php?error=' . urlencode($e->getMessage()));
    exit;
}
?>