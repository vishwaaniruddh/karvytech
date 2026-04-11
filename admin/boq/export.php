<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/BoqItem.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

try {
    $boqModel = new BoqItem();
    $items = $boqModel->getAll();
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="boq_items_' . date('Y-m-d_H-i-s') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Create file pointer
    $output = fopen('php://output', 'w');
    
    // Add CSV headers
    fputcsv($output, [
        'ID',
        'Item Code',
        'Item Name',
        'Description',
        'Unit',
        'Category',
        'Status',
        'Serial Number Required',
        'Icon Class',
        'Created At',
        'Updated At'
    ]);
    
    // Add data rows
    foreach ($items as $item) {
        fputcsv($output, [
            $item['id'],
            $item['item_code'],
            $item['item_name'],
            $item['description'],
            $item['unit'],
            $item['category'],
            $item['status'],
            $item['need_serial_number'] ? 'Yes' : 'No',
            $item['icon_class'],
            $item['created_at'],
            $item['updated_at']
        ]);
    }
    
    fclose($output);
    
} catch (Exception $e) {
    error_log('BOQ export error: ' . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    echo 'An error occurred while exporting BOQ items.';
}
?>