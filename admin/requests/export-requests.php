<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/MaterialRequest.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$materialRequestModel = new MaterialRequest();

// Handle filters
$filters = [
    'status' => $_GET['status'] ?? '',
    'vendor_id' => $_GET['vendor_id'] ?? '',
    'site_id' => $_GET['site_id'] ?? ''
];

// Get data without pagination limit for export
$requestsData = $materialRequestModel->getAllWithPagination(1, 999999, $filters);
$requests = $requestsData['requests'];

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Material Requests');

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
    'B1' => 'Request ID',
    'C1' => 'Request Date',
    'D1' => 'Site ID',
    'E1' => 'Location',
    'F1' => 'Vendor Name',
    'G1' => 'Company Name',
    'H1' => 'Required Date',
    'I1' => 'Status',
    'J1' => 'Total Quantity',
    'K1' => 'Notes'
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
foreach ($requests as $request) {
    // Parse items to get total quantity
    $totalQty = 0;
    if (!empty($request['items'])) {
        $items = json_decode($request['items'], true);
        if (is_array($items)) {
            foreach ($items as $item) {
                $totalQty += (int)($item['quantity'] ?? 0);
            }
        }
    }

    $sheet->setCellValue('A' . $row, $sno++);
    $sheet->setCellValue('B' . $row, 'REQ#' . $request['id']);
    $sheet->setCellValue('C' . $row, date('d M Y', strtotime($request['request_date'])));
    $sheet->setCellValue('D' . $row, $request['site_code'] ?? 'N/A');
    $sheet->setCellValue('E' . $row, $request['location'] ?? 'N/A');
    $sheet->setCellValue('F' . $row, $request['vendor_name'] ?? 'N/A');
    $sheet->setCellValue('G' . $row, $request['vendor_company_name'] ?? 'N/A');
    $sheet->setCellValue('H' . $row, date('d M Y', strtotime($request['required_date'])));
    $sheet->setCellValue('I' . $row, ucfirst($request['status']));
    $sheet->setCellValue('J' . $row, $totalQty);
    $sheet->setCellValue('K' . $row, $request['request_notes']);
    
    $sheet->getStyle('A' . $row . ':K' . $row)->applyFromArray($dataStyle);
    $row++;
}

// Auto-size columns
foreach (range('A', 'K') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Generate File
$filename = 'Material_Requests_' . date('Ymd_His') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
