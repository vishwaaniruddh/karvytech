<?php
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/export_error.log');
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Increase resource limits for heavy Excel generation
set_time_limit(300); 
ini_set('memory_limit', '512M');

require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../models/Inventory.php';
require_once __DIR__ . '/../../../models/Site.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

try {
    // Auth check
    $currentUser = Auth::requireRole(ADMIN_ROLE);

    $inventoryModel = new Inventory();
    $siteModel = new Site();

    // Get filters
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? '';
    $siteId = $_GET['site_id'] ?? '';
    $requestId = $_GET['request_id'] ?? '';

    // Get data without pagination limit
    $dispatchesData = $inventoryModel->getDispatches(1, 999999, $search, $status, $siteId, $requestId);
    $dispatches = $dispatchesData['dispatches'];

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Material Dispatches Advanced');

    // Style Settings
    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E293B']] // Slate-800
    ];

    $groupHeaderStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => '1E293B'], 'size' => 11],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']] // Slate-100
    ];

    $dataStyle = [
        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
        'font' => ['size' => 9]
    ];

    // Define Group Headers
    $sheet->setCellValue('A1', 'General Info');
    $sheet->mergeCells('A1:C1');

    $sheet->setCellValue('D1', 'Ship To - Address (Consignee)');
    $sheet->mergeCells('D1:G1');

    $sheet->setCellValue('H1', 'Dispatch From (Consignor)');
    $sheet->mergeCells('H1:K1');

    $sheet->setCellValue('L1', 'Logistics Details');
    $sheet->mergeCells('L1:P1');

    $sheet->setCellValue('Q1', 'Material Details');
    $sheet->mergeCells('Q1:U1');

    $sheet->getStyle('A1:U1')->applyFromArray($groupHeaderStyle);

    // Define Main Headers
    $headers = [
        'A2' => 'S.No', 'B2' => 'Dispatch #', 'C2' => 'Req #',
        'D2' => 'Customer', 'E2' => 'Address', 'F2' => 'Contact Person', 'G2' => 'Contact Number',
        'H2' => 'Name', 'I2' => 'Address', 'J2' => 'Contact Person', 'K2' => 'Contact Number',
        'L2' => 'Dispatch Through', 'M2' => 'Dispatch Date', 'N2' => 'Tracking / Pocket No', 'O2' => 'Expected Arrival', 'P2' => 'Status',
        'Q2' => 'Material Name', 'R2' => 'Code', 'S2' => 'Qty', 'T2' => 'Unit', 'U2' => 'Rate (₹)'
    ];

    foreach ($headers as $cell => $value) {
        $sheet->setCellValue($cell, $value);
        $sheet->getStyle($cell)->applyFromArray($headerStyle);
    }

    $row = 3;
    $sno = 1;

    foreach ($dispatches as $summary) {
        $details = $inventoryModel->getDispatchDetails($summary['id']);
        if (!$details) continue;

        $items = $details['items'] ?? [];
        if (empty($items)) {
            $items = [['item_name' => 'N/A', 'item_code' => 'N/A', 'quantity_dispatched' => 0, 'unit' => 'N/A', 'unit_cost' => 0]];
        }
        
        $itemCount = count($items);
        $startRow = $row;
        
        $consignorName = "KARVY TECHNOLOGIES PVT. LTD.";
        $consignorAddr = "401, 4th Floor, 58 West, Road No. 19, Andheri (West), Mumbai - 400053";
        $consignorContact = $details['dispatched_by_name'] ?? "Authorized Personnel";
        $consignorPhone = "--";

        $dispatchDate = date('d-M-Y', strtotime($details['dispatch_date']));
        $expectedArrival = $details['expected_delivery_date'] ? date('d-M-Y', strtotime($details['expected_delivery_date'])) : '--';
        
        foreach ($items as $idx => $item) {
            if ($idx === 0) {
                $sheet->setCellValue('A' . $row, $sno);
                $sheet->setCellValue('B' . $row, $details['dispatch_number']);
                $sheet->setCellValue('C' . $row, $details['material_request_id'] ? 'REQ-' . str_pad($details['material_request_id'], 6, '0', STR_PAD_LEFT) : '--');
                $sheet->setCellValue('D' . $row, $details['site_name'] ?: 'N/A');
                $sheet->setCellValue('E' . $row, $details['delivery_address'] ?: '--');
                $sheet->setCellValue('F' . $row, $details['contact_person_name'] ?: '--');
                $sheet->setCellValue('G' . $row, $details['contact_person_phone'] ?: '--');
                $sheet->setCellValue('H' . $row, $consignorName);
                $sheet->setCellValue('I' . $row, $consignorAddr);
                $sheet->setCellValue('J' . $row, $consignorContact);
                $sheet->setCellValue('K' . $row, $consignorPhone);
                $sheet->setCellValue('L' . $row, $details['courier_name'] ?: 'Internal');
                $sheet->setCellValue('M' . $row, $dispatchDate);
                $sheet->setCellValue('N' . $row, $details['tracking_number'] ?: '--');
                $sheet->setCellValue('O' . $row, $expectedArrival);
                $sheet->setCellValue('P' . $row, ucfirst(str_replace('_', ' ', $details['dispatch_status'])));
            }
            
            $sheet->setCellValue('Q' . $row, $item['item_name']);
            $sheet->setCellValue('R' . $row, $item['item_code']);
            $sheet->setCellValue('S' . $row, $item['quantity_dispatched']);
            $sheet->setCellValue('T' . $row, $item['unit']);
            $sheet->setCellValue('U' . $row, $item['unit_cost']);
            
            $sheet->getStyle('A' . $row . ':U' . $row)->applyFromArray($dataStyle);
            $row++;
        }

        if ($itemCount > 1) {
            $endRow = $row - 1;
            $mergeCols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P'];
            foreach ($mergeCols as $col) {
                $sheet->mergeCells($col . $startRow . ':' . $col . $endRow);
            }
        }
        $sno++;
    }

    // Set optimized column widths (fixed widths are much faster than AutoSize)
    $widths = [
        'A' => 6, 'B' => 22, 'C' => 15, 'D' => 30, 'E' => 45, 'F' => 20, 'G' => 15,
        'H' => 30, 'I' => 45, 'J' => 20, 'K' => 15, 'L' => 18, 'M' => 15, 'N' => 25,
        'O' => 15, 'P' => 15, 'Q' => 35, 'R' => 15, 'S' => 10, 'T' => 10, 'U' => 15
    ];
    foreach ($widths as $col => $width) {
        $sheet->getColumnDimension($col)->setWidth($width);
        $sheet->getStyle($col)->getAlignment()->setWrapText(true);
    }

    $sheet->getStyle('S3:S' . ($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('U3:U' . ($row - 1))->getNumberFormat()->setFormatCode('#,##0.00');

    $filename = 'Advanced_Material_Dispatches_' . date('Ymd_His') . '.xlsx';

    // ob_clean buffer if it exists
    if (ob_get_length()) ob_end_clean();

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;

} catch (Exception $e) {
    if (ob_get_length()) ob_end_clean();
    header('Content-Type: text/plain');
    echo "Export Error: " . $e->getMessage();
    exit;
}
