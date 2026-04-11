<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;

// Require admin authentication
Auth::requireRole('admin');

// Get parameters
$search = $_GET['search'] ?? '';
$city = $_GET['city'] ?? '';
$state = $_GET['state'] ?? '';
$activity_status = $_GET['activity_status'] ?? '';
$survey_status = $_GET['survey_status'] ?? '';

try {
    $db = Database::getInstance()->getConnection();
    
    // Build the query matching the structure from Site model
    $sql = "SELECT 
                s.id,
                s.site_id,
                s.site_ticket_id,
                s.store_id,
                s.location,
                s.pincode,
                s.branch,
                ct.name as city,
                st.name as state,
                co.name as country,
                cu.name as customer,
                s.contact_person_name,
                s.contact_person_number,
                s.contact_person_email,
                s.vendor,
                s.po_number,
                s.po_date,
                s.activity_status,
                s.remarks,
                sd.status as delegation_status,
                v.name as delegated_vendor_name,
                sd.delegation_date,
                ss.survey_status as actual_survey_status,
                ss.submitted_date as survey_submitted_date,
                sv.name as survey_vendor_name,
                s.installation_status,
                s.installation_date,
                s.is_material_request_generated,
                s.created_at,
                s.updated_at,
                s.created_by,
                s.updated_by
            FROM sites s
            LEFT JOIN cities ct ON s.city_id = ct.id
            LEFT JOIN states st ON s.state_id = st.id
            LEFT JOIN countries co ON s.country_id = co.id
            LEFT JOIN customers cu ON s.customer_id = cu.id
            LEFT JOIN site_delegations sd ON s.id = sd.site_id AND sd.status = 'active'
            LEFT JOIN vendors v ON sd.vendor_id = v.id
            LEFT JOIN (
                SELECT ss1.site_id, ss1.id, ss1.survey_status, ss1.submitted_date, ss1.vendor_id
                FROM site_surveys ss1
                INNER JOIN (
                    SELECT site_id, MAX(id) as max_id
                    FROM site_surveys
                    GROUP BY site_id
                ) ss2 ON ss1.site_id = ss2.site_id AND ss1.id = ss2.max_id
            ) ss ON s.id = ss.site_id
            LEFT JOIN vendors sv ON ss.vendor_id = sv.id
            WHERE 1=1";
    
    $params = [];
    
    // Add search filter
    if ($search) {
        $sql .= " AND (s.site_id LIKE ? OR s.store_id LIKE ? OR s.location LIKE ? OR ct.name LIKE ? OR cu.name LIKE ? OR s.contact_person_name LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    // Add city filter
    if ($city) {
        $sql .= " AND ct.name = ?";
        $params[] = $city;
    }
    
    // Add state filter
    if ($state) {
        $sql .= " AND st.name = ?";
        $params[] = $state;
    }
    
    // Add activity status filter
    if ($activity_status) {
        $sql .= " AND s.activity_status = ?";
        $params[] = $activity_status;
    }
    
    // Add survey status filter
    if ($survey_status) {
        switch ($survey_status) {
            case 'pending':
                $sql .= " AND (ss.survey_status IS NULL OR ss.survey_status = '')";
                break;
            case 'submitted':
                $sql .= " AND ss.survey_status = 'completed'";
                break;
            case 'approved':
                $sql .= " AND ss.survey_status = 'approved'";
                break;
            case 'rejected':
                $sql .= " AND ss.survey_status = 'rejected'";
                break;
        }
    }
    
    $sql .= " ORDER BY s.created_at DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Create new Spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Sites Export');
    
    // Define column widths
    $columnWidths = [
        'A' => 8,  'B' => 15, 'C' => 15, 'D' => 12, 'E' => 25, 'F' => 10, 'G' => 12,
        'H' => 12, 'I' => 12, 'J' => 12, 'K' => 18, 'L' => 15, 'M' => 15, 'N' => 20,
        'O' => 15, 'P' => 12, 'Q' => 12, 'R' => 15, 'S' => 25, 'T' => 12, 'U' => 15,
        'V' => 12, 'W' => 12, 'X' => 15, 'Y' => 15, 'Z' => 12, 'AA' => 15, 'AB' => 10,
        'AC' => 15, 'AD' => 15, 'AE' => 12, 'AF' => 12
    ];
    
    foreach ($columnWidths as $col => $width) {
        $sheet->getColumnDimension($col)->setWidth($width);
    }
    
    // Row 1: Section headers (merged cells)
    $sectionHeaders = [
        ['range' => 'A1:G1', 'text' => 'Site Information', 'color' => '4472C4'],
        ['range' => 'H1:K1', 'text' => 'Location Details', 'color' => '4472C4'],
        ['range' => 'L1:P1', 'text' => 'Contact & Customer', 'color' => '4472C4'],
        ['range' => 'Q1:S1', 'text' => 'Purchase Order', 'color' => '4472C4'],
        ['range' => 'T1:V1', 'text' => 'Delegation', 'color' => '4472C4'],
        ['range' => 'W1:Y1', 'text' => 'Survey', 'color' => '4472C4'],
        ['range' => 'Z1:AA1', 'text' => 'Installation', 'color' => '4472C4'],
        ['range' => 'AB1', 'text' => 'Material', 'color' => '4472C4'],
        ['range' => 'AC1:AF1', 'text' => 'Audit Information', 'color' => '4472C4']
    ];
    
    foreach ($sectionHeaders as $section) {
        $sheet->mergeCells($section['range']);
        $sheet->setCellValue(explode(':', $section['range'])[0], $section['text']);
        $sheet->getStyle($section['range'])->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $section['color']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['bottom' => ['borderStyle' => Border::BORDER_THICK]]
        ]);
    }
    $sheet->getRowDimension(1)->setRowHeight(30);
    
    // Row 2: Field headers
    $headers = [
        'ID', 'Site ID', 'Site Ticket ID', 'Store ID', 'Location', 'Pincode', 'Branch',
        'City', 'State', 'Country',
        'Customer', 'Contact Name', 'Contact Number', 'Contact Email', 'Vendor',
        'PO Number', 'PO Date', 'Activity Status',
        'Remarks',
        'Delegation Status', 'Delegated Vendor', 'Delegation Date',
        'Survey Status', 'Survey Date', 'Survey Vendor',
        'Installation Status', 'Installation Date',
        'Material Request',
        'Created At', 'Updated At', 'Created By', 'Updated By'
    ];
    
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . '2', $header);
        $col++;
    }
    
    $sheet->getStyle('A2:AF2')->applyFromArray([
        'font' => ['bold' => true, 'size' => 10],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ]);
    $sheet->getRowDimension(2)->setRowHeight(35);
    
    // Data rows
    $row = 3;
    foreach ($data as $item) {
        $sheet->setCellValue('A' . $row, $item['id']);
        $sheet->setCellValue('B' . $row, $item['site_id'] ?? '');
        $sheet->setCellValue('C' . $row, $item['site_ticket_id'] ?? '');
        $sheet->setCellValue('D' . $row, $item['store_id'] ?? '');
        $sheet->setCellValue('E' . $row, $item['location'] ?? '');
        $sheet->setCellValue('F' . $row, $item['pincode'] ?? '');
        $sheet->setCellValue('G' . $row, $item['branch'] ?? '');
        $sheet->setCellValue('H' . $row, $item['city'] ?? '');
        $sheet->setCellValue('I' . $row, $item['state'] ?? '');
        $sheet->setCellValue('J' . $row, $item['country'] ?? '');
        $sheet->setCellValue('K' . $row, $item['customer'] ?? '');
        $sheet->setCellValue('L' . $row, $item['contact_person_name'] ?? '');
        $sheet->setCellValue('M' . $row, $item['contact_person_number'] ?? '');
        $sheet->setCellValue('N' . $row, $item['contact_person_email'] ?? '');
        $sheet->setCellValue('O' . $row, $item['vendor'] ?? '');
        $sheet->setCellValue('P' . $row, $item['po_number'] ?? '');
        $sheet->setCellValue('Q' . $row, $item['po_date'] ?? '');
        $sheet->setCellValue('R' . $row, $item['activity_status'] ?? '');
        $sheet->setCellValue('S' . $row, $item['remarks'] ?? '');
        $sheet->setCellValue('T' . $row, $item['delegation_status'] ?? '');
        $sheet->setCellValue('U' . $row, $item['delegated_vendor_name'] ?? '');
        $sheet->setCellValue('V' . $row, $item['delegation_date'] ?? '');
        $sheet->setCellValue('W' . $row, $item['actual_survey_status'] ?? 'Pending');
        $sheet->setCellValue('X' . $row, $item['survey_submitted_date'] ?? '');
        $sheet->setCellValue('Y' . $row, $item['survey_vendor_name'] ?? '');
        $sheet->setCellValue('Z' . $row, $item['installation_status'] ? 'Done' : 'Pending');
        $sheet->setCellValue('AA' . $row, $item['installation_date'] ?? '');
        $sheet->setCellValue('AB' . $row, $item['is_material_request_generated'] ? 'Yes' : 'No');
        $sheet->setCellValue('AC' . $row, $item['created_at'] ?? '');
        $sheet->setCellValue('AD' . $row, $item['updated_at'] ?? '');
        $sheet->setCellValue('AE' . $row, $item['created_by'] ?? '');
        $sheet->setCellValue('AF' . $row, $item['updated_by'] ?? '');
        
        $row++;
    }
    
    // Apply borders and alignment to data cells
    if ($row > 3) {
        $sheet->getStyle('A3:AF' . ($row - 1))->applyFromArray([
            'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E0E0E0']]]
        ]);
    }
    
    // Output the file
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="Sites_Export_' . date('Y-m-d_His') . '.xlsx"');
    header('Cache-Control: max-age=0');
    header('Cache-Control: max-age=1');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: cache, must-revalidate');
    header('Pragma: public');
    
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
    
} catch (Exception $e) {
    error_log('Sites export error: ' . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    echo 'Export failed: ' . $e->getMessage();
    exit;
}
?>
