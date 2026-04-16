<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../models/Site.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

try {
    $siteModel = new Site();
    $db = Database::getInstance()->getConnection();
    
    // Get filters
    $search = $_GET['search'] ?? '';
    $filters = [
        'city' => $_GET['city'] ?? '',
        'state' => $_GET['state'] ?? '',
        'activity_status' => $_GET['activity_status'] ?? '',
        'survey_status' => $_GET['survey_status'] ?? '',
        'requisition_id' => $_GET['requisition_id'] ?? ''
    ];

    $result = $siteModel->getAllWithPagination(1, 1000000, $search, $filters);
    $sites = $result['sites'];

    // Pre-fetch all BOQ item names to avoid queries in loop
    $boqStmt = $db->query("SELECT id, item_name FROM boq_items");
    $boqNames = $boqStmt->fetchAll(PDO::FETCH_KEY_PAIR);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Sites Export');

    // Define columns
    // A: #, B: Site ID / Ticket, C: Store / PO, D: Client / Location, E: Survey Vendor, F: Inst Vendor, G: Surveyor, H: Date/Time, I: Reports, J: Material Status, K: Req #, L: Installer, M: Completed, N: Data
    
    // Header Row 1: Groups
    $sheet->setCellValue('A1', '1. Masters Data');
    $sheet->mergeCells('A1:D1');
    
    $sheet->setCellValue('E1', '2. Delegation');
    $sheet->mergeCells('E1:F1');
    
    $sheet->setCellValue('G1', '3. Survey Status');
    $sheet->mergeCells('G1:J1'); // Changed to 4 columns
    
    $sheet->setCellValue('K1', '4. Material Part');
    $sheet->mergeCells('K1:O1'); // Increased to 5 columns
    
    $sheet->setCellValue('P1', '5. Installation');
    $sheet->mergeCells('P1:S1'); // Shifted and sized to 4 columns

    // Header Row 2: Sub-columns
    $headers = [
        '#', 'Site ID / Ticket', 'Store / PO', 'Client / Location',
        'Survey Vendor', 'Inst Vendor',
        'Surveyor', 'Date/Time', 'Status', 'View Link',
        'Req #', 'Material Details', 'Status', 'Dispatch', 'Delivery',
        'Installer', 'CompletedAt', 'Status', 'View Link'
    ];
    $sheet->fromArray($headers, NULL, 'A2');

    // Styling Headers
    $headerStyle = [
        'font' => ['bold' => true, 'size' => 11],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
            'wrapText' => true
        ],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E9ECEF']]
    ];
    $sheet->getStyle('A1:S2')->applyFromArray($headerStyle);

    // Section Colors (Grouping)
    $sheet->getStyle('A1:D1')->getFill()->getStartColor()->setRGB('F8FAFC'); // Masters
    $sheet->getStyle('E1:F1')->getFill()->getStartColor()->setRGB('F0FDF4'); // Delegation
    $sheet->getStyle('G1:J1')->getFill()->getStartColor()->setRGB('EFF6FF'); // Survey
    $sheet->getStyle('K1:O1')->getFill()->getStartColor()->setRGB('FFFBEB'); // Material
    $sheet->getStyle('P1:S1')->getFill()->getStartColor()->setRGB('F5F3FF'); // Installation

    // Data Row starting point
    $rowNum = 3;
    foreach ($sites as $index => $site) {
        // Enriquecer con datos de instalación y materiales (utilizar lógica similar a get-sites-advanced.php)
        
        // 1. Installation Vendor
        $instStmt = $db->prepare("
            SELECT v.name as vendor_name 
            FROM installation_delegations id 
            JOIN vendors v ON id.vendor_id = v.id 
            WHERE id.site_id = ? 
            LIMIT 1
        ");
        $instStmt->execute([$site['id']]);
        $instVendor = $instStmt->fetchColumn() ?: '-';
        
        // 2. Material Request & Dispatch Info
        $matStmt = $db->prepare("
            SELECT mr.status as request_status, mr.id as request_id, mr.items,
                   md.id as dispatch_id, md.acknowledgment_status as delivery_status
            FROM material_requests mr
            LEFT JOIN material_dispatches md ON mr.id = md.material_request_id
            WHERE mr.site_id = ? 
            ORDER BY mr.id DESC, md.id DESC LIMIT 1
        ");
        $matStmt->execute([$site['id']]);
        $matData = $matStmt->fetch(PDO::FETCH_ASSOC);
        
        $matReqNo = $matData ? 'REQ-' . str_pad($matData['request_id'], 6, '0', STR_PAD_LEFT) : '-';
        $matDetails = '-';
        if ($matData && !empty($matData['items'])) {
            $items = json_decode($matData['items'], true);
            if (is_array($items)) {
                $detailsArr = [];
                foreach ($items as $item) {
                    $name = $item['material_name'] ?? $item['item_name'] ?? null;
                    if (!$name && !empty($item['boq_item_id'])) {
                        $name = $boqNames[$item['boq_item_id']] ?? 'Unknown';
                    }
                    $detailsArr[] = ($name ?: 'Item') . " (Qty: " . ($item['quantity'] ?? 0) . ")";
                }
                $matDetails = implode("\n", $detailsArr);
            }
        }
        
        $matStatus = $matData ? $matData['request_status'] : 'Pending';
        $dispatchStatus = $matData && $matData['dispatch_id'] ? 'Dispatched' : 'Pending';
        $deliveryStatus = $matData && $matData['dispatch_id'] ? (ucfirst($matData['delivery_status'] ?? 'In-Transit')) : '-';
        
        // 3. Installation Person/Time & Status
        $instLogStmt = $db->prepare("
            SELECT u.username, id.updated_at, id.status as inst_status
            FROM installation_delegations id 
            LEFT JOIN users u ON id.updated_by = u.id 
            WHERE id.site_id = ? 
            ORDER BY id.id DESC LIMIT 1
        ");
        $instLogStmt->execute([$site['id']]);
        $instLog = $instLogStmt->fetch(PDO::FETCH_ASSOC);
        $installer = $instLog ? $instLog['username'] : '-';
        $instDate = ($instLog && $instLog['inst_status'] === 'completed') ? $instLog['updated_at'] : '-';
        $instStatusLabel = $instLog ? ucfirst($instLog['inst_status']) : 'Pending';

        $sheet->setCellValue('A'. $rowNum, $index + 1);
        $sheet->setCellValue('B'. $rowNum, $site['site_id'] . "\n" . ($site['site_ticket_id'] ?? ''));
        $sheet->setCellValue('C'. $rowNum, ($site['store_id'] ?? '-') . "\n" . ($site['po_number'] ?? '-'));
        $sheet->setCellValue('D'. $rowNum, ($site['customer'] ?? '-') . "\n" . ($site['city'] ?? '-') . ", " . ($site['state'] ?? '-'));
        $sheet->setCellValue('E'. $rowNum, $site['delegated_vendor_name'] ?? '-');
        $sheet->setCellValue('F'. $rowNum, $instVendor);
        
        // Survey
        $sheet->setCellValue('G'. $rowNum, $site['surveyor_name'] ?? '-');
        $sheet->setCellValue('H'. $rowNum, $site['survey_submitted_date'] ?? '-');
        $sheet->setCellValue('I'. $rowNum, $site['actual_survey_status'] ?? 'Pending');
        $sheet->setCellValue('J'. $rowNum, $site['has_survey_submitted'] ? 'Report Submitted' : 'Pending');
        
        // Material
        $sheet->setCellValue('K'. $rowNum, $matReqNo);
        $sheet->setCellValue('L'. $rowNum, $matDetails);
        $sheet->setCellValue('M'. $rowNum, $matStatus);
        $sheet->setCellValue('N'. $rowNum, $dispatchStatus);
        $sheet->setCellValue('O'. $rowNum, $deliveryStatus);
        
        // Installation
        $sheet->setCellValue('P'. $rowNum, $installer);
        $sheet->setCellValue('Q'. $rowNum, $instDate);
        $sheet->setCellValue('R'. $rowNum, $instStatusLabel);
        $sheet->setCellValue('S'. $rowNum, $site['installation_id'] ? 'Data Completed' : 'Pending');

        // Enable multiline
        $sheet->getStyle('B'.$rowNum.':D'.$rowNum)->getAlignment()->setWrapText(true);
        $sheet->getStyle('L'.$rowNum)->getAlignment()->setWrapText(true);
        
        $rowNum++;
    }

    // Auto-size columns (up to S)
    foreach(range('A','S') as $columnID) {
        if (!in_array($columnID, ['B','C','D', 'L'])) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        } else {
            $sheet->getColumnDimension($columnID)->setWidth(25);
        }
    }

    // Full table borders
    $sheet->getStyle('A1:S' . ($rowNum-1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

    // Set headers and download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="sites_export_' . date('Y-m-d_His') . '.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;

} catch (Exception $e) {
    die("Export failed: " . $e->getMessage());
}
