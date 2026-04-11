<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

try {
    // Create new spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Sites Template');
    
    // Headers
    $headers = [
        'Site ID',
        'Store ID',
        'Location',
        'Country',
        'State', 
        'City',
        'Branch',
        'Customer',
        'Bank',
        'PO Number',
        'PO Date',
        'Remarks'
    ];
    
    // Sample data
    $sampleData = [
        [
            'SITE001',
            'STORE001',
            '123 Main Street, Business District',
            'India',
            'Maharashtra',
            'Mumbai',
            'Andheri Branch',
            'ABC Corporation',
            'HDFC Bank',
            'PO2024001',
            '2024-01-15',
            'High priority installation'
        ],
        [
            'SITE002',
            'STORE002',
            '456 Commercial Road, IT Park',
            'India',
            'Karnataka',
            'Bangalore',
            'Whitefield Branch',
            'XYZ Ltd',
            'ICICI Bank',
            'PO2024002',
            '2024-01-20',
            'Standard installation'
        ]
    ];
    
    // Set headers
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . '1', $header);
        $col++;
    }
    
    // Style headers
    $lastColumn = chr(64 + count($headers)); // Convert to letter (A=65, so 64+1=A)
    $headerRange = 'A1:' . $lastColumn . '1';
    $sheet->getStyle($headerRange)->applyFromArray([
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF']
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '4472C4']
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000000']
            ]
        ]
    ]);
    
    // Add sample data
    $row = 2;
    foreach ($sampleData as $data) {
        $col = 'A';
        foreach ($data as $value) {
            $sheet->setCellValue($col . $row, $value);
            $col++;
        }
        $row++;
    }
    
    // Auto-size columns
    $lastColumn = chr(64 + count($headers));
    foreach (range('A', $lastColumn) as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }
    
    // Add data validation and comments for important columns
    $sheet->getComment('A1')->getText()->createTextRun('Required field. Must be unique.');
    $sheet->getComment('C1')->getText()->createTextRun('Required field. Complete address.');
    $sheet->getComment('D1')->getText()->createTextRun('Must match existing country in master data.');
    $sheet->getComment('E1')->getText()->createTextRun('Must match existing state in master data.');
    $sheet->getComment('F1')->getText()->createTextRun('Must match existing city in master data.');
    $sheet->getComment('K1')->getText()->createTextRun('Date format: YYYY-MM-DD or DD/MM/YYYY');
    
    // Set headers for download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="sites_bulk_upload_template.xlsx"');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Expires: 0');
    
    // Write file
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    
} catch (Exception $e) {
    // Fallback to CSV if Excel generation fails
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="sites_bulk_upload_template.csv"');
    
    $csvHeaders = [
        'Site ID',
        'Store ID',
        'Location',
        'Country',
        'State', 
        'City',
        'Branch',
        'Customer',
        'Bank',
        'PO Number',
        'PO Date',
        'Remarks'
    ];
    
    $sampleData = [
        [
            'SITE001',
            'STORE001',
            '123 Main Street, Business District',
            'India',
            'Maharashtra',
            'Mumbai',
            'Andheri Branch',
            'ABC Corporation',
            'HDFC Bank',
            'PO2024001',
            '2024-01-15',
            'High priority installation'
        ],
        [
            'SITE002',
            'STORE002',
            '456 Commercial Road, IT Park',
            'India',
            'Karnataka',
            'Bangalore',
            'Whitefield Branch',
            'XYZ Ltd',
            'ICICI Bank',
            'PO2024002',
            '2024-01-20',
            'Standard installation'
        ]
    ];
    
    $output = fopen('php://output', 'w');
    fputcsv($output, $csvHeaders);
    foreach ($sampleData as $row) {
        fputcsv($output, $row);
    }
    fclose($output);
}

exit;
?>