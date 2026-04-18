<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Inventory.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Require vendor authentication
Auth::requireVendor();

$vendorId = Auth::getVendorId();
$inventoryModel = new Inventory();

try {
    // Get filter parameters
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $statusFilter = isset($_GET['status']) ? trim($_GET['status']) : '';
    
    // Fetch all filtered data (limit 10000 for export)
    $result = $inventoryModel->getReceivedMaterialsForVendor($vendorId, 1, 10000, $search, $statusFilter);
    
    if (empty($result['data'])) {
        die('No data to export');
    }
    
    // Fetch items for each dispatch using correct table (boq_items)
    $db = Database::getInstance()->getConnection();
    foreach ($result['data'] as &$dispatch) {
        $stmt = $db->prepare("
            SELECT idi.boq_item_id, bi.item_name, bi.item_code, bi.unit,
                   COUNT(*) as quantity_dispatched
            FROM inventory_dispatch_items idi
            LEFT JOIN boq_items bi ON idi.boq_item_id = bi.id
            WHERE idi.dispatch_id = :dispatch_id
            GROUP BY idi.boq_item_id, idi.item_condition, idi.batch_number
            ORDER BY bi.item_name
        ");
        $stmt->execute([':dispatch_id' => $dispatch['id']]);
        $dispatch['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Create new Spreadsheet object
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Material Receipts');
    
    // Define Headers
    $headers = ['#', 'Site Code', 'Manifest ID', 'Courier', 'Tracking Number', 'Items', 'Request ID', 'Status', 'Dispatch Date'];
    $columnLetter = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($columnLetter . '1', $header);
        $columnLetter++;
    }
    
    // Header Style
    $headerRange = 'A1:I1';
    $sheet->getStyle($headerRange)->applyFromArray([
        'font' => [
            'bold' => true,
            'color' => ['argb' => 'FFFFFFFF'],
            'size' => 11,
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['argb' => 'FF4472C4'],
        ],
    ]);
    
    // Set column widths
    $sheet->getColumnDimension('A')->setWidth(5);
    $sheet->getColumnDimension('B')->setWidth(15);
    $sheet->getColumnDimension('C')->setWidth(20);
    $sheet->getColumnDimension('D')->setWidth(20);
    $sheet->getColumnDimension('E')->setWidth(20);
    $sheet->getColumnDimension('F')->setWidth(45); // Items
    $sheet->getColumnDimension('G')->setWidth(15);
    $sheet->getColumnDimension('H')->setWidth(15);
    $sheet->getColumnDimension('I')->setWidth(20);
    
    // Add Data
    $rowNumber = 2;
    foreach ($result['data'] as $index => $dispatch) {
        // Build items string
        $itemLines = [];
        if (!empty($dispatch['items'])) {
            foreach ($dispatch['items'] as $item) {
                $itemName = $item['item_name'] ?? 'Unknown Item';
                $quantity = $item['quantity_dispatched'] ?? 0;
                $unit = $item['unit'] ?? 'Unit';
                $itemLines[] = "• $itemName ($quantity $unit)";
            }
        } else {
            $itemLines[] = "No items";
        }
        $itemsText = implode("\n", $itemLines);
        
        $siteCode = $dispatch['site_code'] ?? 'N/A';
        $manifestId = $dispatch['dispatch_number'] ?? '';
        $courier = $dispatch['courier_name'] ?? 'Internal';
        $tracking = $dispatch['tracking_number'] ?? '--';
        $requestId = 'REQ-' . str_pad($dispatch['material_request_id'] ?? 0, 4, '0', STR_PAD_LEFT);
        $status = strtoupper(str_replace('_', ' ', $dispatch['dispatch_status'] ?? 'delivered'));
        $dispatchDate = $dispatch['dispatch_date'] ?? '';
        
        $sheet->setCellValue('A' . $rowNumber, $index + 1);
        $sheet->setCellValue('B' . $rowNumber, $siteCode);
        $sheet->setCellValue('C' . $rowNumber, $manifestId);
        $sheet->setCellValue('D' . $rowNumber, $courier);
        $sheet->setCellValue('E' . $rowNumber, $tracking);
        $sheet->setCellValue('F' . $rowNumber, $itemsText);
        $sheet->setCellValue('G' . $rowNumber, $requestId);
        $sheet->setCellValue('H' . $rowNumber, $status);
        $sheet->setCellValue('I' . $rowNumber, $dispatchDate);
        
        // Wrap text for items
        $sheet->getStyle('F' . $rowNumber)->getAlignment()->setWrapText(true);
        $sheet->getStyle('A' . $rowNumber . ':I' . $rowNumber)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
        
        $rowNumber++;
    }
    
    // Add borders to all data
    $lastRow = $rowNumber - 1;
    $sheet->getStyle('A1:I' . $lastRow)->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['argb' => 'FFD0D0D0'],
            ],
        ],
    ]);
    
    // Generate filename
    $filename = 'Material_Receipts_' . date('Y-m-d_His') . '.xlsx';
    
    // Clear any output buffers
    if (ob_get_length()) ob_clean();
    
    // Set headers for Excel download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
    
} catch (Exception $e) {
    error_log("Export error: " . $e->getMessage());
    if (ob_get_length()) ob_clean();
    header('Content-Type: text/plain');
    die('Export failed: ' . $e->getMessage());
}
