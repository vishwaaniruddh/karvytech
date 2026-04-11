<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/compatibility.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

// Try to load PhpSpreadsheet
$phpSpreadsheetAvailable = false;
if (COMPOSER_AVAILABLE) {
    try {
        require_once __DIR__ . '/../../vendor/autoload.php';
        $phpSpreadsheetAvailable = class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet');
    } catch (Exception $e) {
        // PhpSpreadsheet not available
    }
}

if (!$phpSpreadsheetAvailable) {
    die('Error: PhpSpreadsheet library is required to generate Excel templates. Please contact administrator.');
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

// Create new Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('User Upload Template');

// Excel Headers
$headers = [
    'Username',
    'Email', 
    'Phone Number',
    'Password',
    'Role',
    'Status'
];

// Write headers in row 1
$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col . '1', $header);
    
    // Style header row
    $sheet->getStyle($col . '1')->applyFromArray([
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF']
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '4472C4']
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER
        ]
    ]);
    
    // Auto-size columns
    $sheet->getColumnDimension($col)->setAutoSize(true);
    
    $col++;
}

// Write sample data in row 2
$sampleData = [
    'Prabir Datta',
    'prabir@gmail.com',
    '1245369874',
    '12345',
    'admin/vendor',
    'active'
];

$col = 'A';
foreach ($sampleData as $data) {
    $sheet->setCellValue($col . '2', $data);
    $col++;
}

// Set row height
$sheet->getRowDimension(1)->setRowHeight(25);

// Set headers for Excel download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="user_upload_template.xlsx"');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

// Write to output
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>