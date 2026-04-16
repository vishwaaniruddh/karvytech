<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../models/Inventory.php';

// Auth check
if (!Auth::isLoggedIn() || !Auth::isVendor()) {
    die('Unauthorized');
}

$vendorId = Auth::getVendorId();
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

$inventoryModel = new Inventory();
// For export, we fetch a large limit or implement a specific export method. 
// Here we'll use a high limit to get all filtered records.
$data = $inventoryModel->getContractorReceivedMaterialsPaginated($vendorId, 1, 10000, $search, $status, $dateFrom, $dateTo);
$materials = $data['materials'];

$filename = "Material_Received_" . date('Ymd_His') . ".csv";

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// Header
fputcsv($output, [
    'S.No',
    'Dispatch Number',
    'Site ID',
    'Site Location',
    'Dispatch Date',
    'Status',
    'Received By',
    'Confirmation Date'
]);

$i = 1;
foreach ($materials as $row) {
    fputcsv($output, [
        $i++,
        $row['dispatch_number'],
        $row['site_code'],
        $row['site_location'],
        $row['dispatch_date'],
        $row['dispatch_status'],
        $row['received_by'] ?? 'N/A',
        $row['confirmation_date'] ?? 'N/A'
    ]);
}

fclose($output);
exit;
