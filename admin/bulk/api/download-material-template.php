<?php
require_once __DIR__ . '/../../../config/auth.php';

// Auth::requireRole(ADMIN_ROLE);

$filename = "Material_Master_Template_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// Headers
fputcsv($output, [
    'Item Name',
    'Item Code',
    'Unit',
    'Description',
    'Category',
    'Serial Required (Yes/No)'
]);

// Sample data
fputcsv($output, [
    'Fixed Dome Camera',
    'CAM-DOM-001',
    'Nos',
    'High resolution indoor dome camera',
    'CCTV',
    'Yes'
]);

fclose($output);
exit;
