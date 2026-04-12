<?php
require_once __DIR__ . '/../../../models/Inventory.php';
require_once __DIR__ . '/../../../models/Site.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Auth check
$currentUser = Auth::requireRole(ADMIN_ROLE);

$inventoryModel = new Inventory();
$siteModel = new Site();

// Get filters
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$siteId = $_GET['site_id'] ?? '';

// Get data without pagination limit
$dispatchesData = $inventoryModel->getDispatches(1, 999999, $search, $status, $siteId);
$dispatches = $dispatchesData['dispatches'];

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Material Dispatches');

// Header Styles
$headerStyle = [
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF'],
        'size' => 11
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '1F2937'] // gray-800
    ]
];

// Headers
$headers = [
    'A1' => '#',
    'B1' => 'Dispatch Number',
    'C1' => 'Date & Time',
    'D1' => 'Site ID',
    'E1' => 'Site Name',
    'F1' => 'Vendor / Company',
    'G1' => 'Destination Address',
    'H1' => 'Contact Person',
    'I1' => 'Phone',
    'J1' => 'Courier / Tracking',
    'K1' => 'Total Items',
    'L1' => 'Total Value (₹)',
    'M1' => 'Status'
];

foreach ($headers as $cell => $value) {
    $sheet->setCellValue($cell, $value);
    $sheet->getStyle($cell)->applyFromArray($headerStyle);
}

// Data Row Styling
$dataStyle = [
    'alignment' => [
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => 'E5E7EB'], // gray-200
        ],
    ],
];

$row = 2;
$sno = 1;
foreach ($dispatches as $dispatch) {
    $dateTime = date('d M Y, h:i A', strtotime($dispatch['dispatch_date']));
    $courierInfo = $dispatch['courier_name'] . ($dispatch['tracking_number'] ? " (" . $dispatch['tracking_number'] . ")" : "");
    
    $sheet->setCellValue('A' . $row, $sno++);
    $sheet->setCellValue('B' . $row, $dispatch['dispatch_number']);
    $sheet->setCellValue('C' . $row, $dateTime);
    $sheet->setCellValue('D' . $row, $dispatch['site_code'] ?? 'N/A');
    $sheet->setCellValue('E' . $row, $dispatch['site_name'] ?? 'N/A');
    $sheet->setCellValue('F' . $row, $dispatch['vendor_company_name'] ?? 'Internal');
    $sheet->setCellValue('G' . $row, $dispatch['delivery_address']);
    $sheet->setCellValue('H' . $row, $dispatch['contact_person_name']);
    $sheet->setCellValue('I' . $row, $dispatch['contact_person_phone']);
    $sheet->setCellValue('J' . $row, $courierInfo);
    $sheet->setCellValue('K' . $row, $dispatch['total_items']);
    $sheet->setCellValue('L' . $row, $dispatch['total_value']);
    $sheet->setCellValue('M' . $row, ucfirst(str_replace('_', ' ', $dispatch['dispatch_status'])));
    
    $sheet->getStyle('A' . $row . ':M' . $row)->applyFromArray($dataStyle);
    $row++;
}

// Auto-size columns
foreach (range('A', 'M') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Set Number Format for Value
$sheet->getStyle('L2:L' . ($row - 1))->getNumberFormat()->setFormatCode('#,##0.00');

// Generate File
$filename = 'Material_Dispatches_' . date('Ymd_His') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
