<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Require vendor authentication
try {
    Auth::requireVendor();
    $vendorId = Auth::getVendorId();
} catch (Exception $e) {
    die("Unauthorized");
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Get filters
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? 'all';
    
    $conditions = ["sd.vendor_id = ?"];
    $params = [$vendorId];
    
    if ($status !== 'all') {
        $conditions[] = "sd.status = ?";
        $params[] = $status;
    }
    
    if (!empty($search)) {
        $conditions[] = "(s.site_id LIKE ? OR s.location LIKE ? OR cu.name LIKE ? OR ct.name LIKE ?)";
        $searchParam = "%$search%";
        $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
    }
    
    $whereSql = "WHERE " . implode(" AND ", $conditions);
    
    $sql = "SELECT sd.delegation_date, s.site_id as site_code, s.location,
                   cu.name as customer_name,
                   ct.name as city_name, st.name as state_name,
                   COALESCE(ss.survey_status, dsr.survey_status, 'pending') as survey_status,
                   ins.status as installation_status
            FROM site_delegations sd
            INNER JOIN sites s ON sd.site_id = s.id
            LEFT JOIN customers cu ON s.customer_id = cu.id
            LEFT JOIN cities ct ON s.city_id = ct.id
            LEFT JOIN states st ON s.state_id = st.id
            LEFT JOIN site_surveys ss ON sd.id = ss.delegation_id
            LEFT JOIN (
                SELECT delegation_id, survey_status, submitted_date 
                FROM dynamic_survey_responses 
                WHERE id IN (SELECT MAX(id) FROM dynamic_survey_responses GROUP BY delegation_id)
            ) dsr ON sd.id = dsr.delegation_id
            LEFT JOIN installation_delegations ins ON s.id = ins.site_id AND ins.vendor_id = sd.vendor_id
            $whereSql
            ORDER BY sd.delegation_date DESC";
            
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $sites = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Site Operations');

    // Headers
    $headers = ['#', 'Site ID', 'Customer', 'Location', 'Geography', 'Survey Status', 'Installation Status', 'Delegated On'];
    $sheet->fromArray($headers, NULL, 'A1');

    // Styling
    $sheet->getStyle('A1:H1')->getFont()->setBold(true);
    $sheet->getStyle('A1:H1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('4F46E5');
    $sheet->getStyle('A1:H1')->getFont()->getColor()->setRGB('FFFFFF');
    $sheet->getStyle('A1:H1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $rowNum = 2;
    foreach ($sites as $index => $site) {
        $sheet->setCellValue('A'. $rowNum, $index + 1);
        $sheet->setCellValue('B'. $rowNum, $site['site_code']);
        $sheet->setCellValue('C'. $rowNum, $site['customer_name'] ?: 'N/A');
        $sheet->setCellValue('D'. $rowNum, $site['location']);
        $sheet->setCellValue('E'. $rowNum, ($site['city_name'] ?: '') . ', ' . ($site['state_name'] ?: ''));
        $sheet->setCellValue('F'. $rowNum, ucfirst($site['survey_status'] ?: 'Pending'));
        $sheet->setCellValue('G'. $rowNum, ucfirst($site['installation_status'] ?: 'Pending'));
        $sheet->setCellValue('H'. $rowNum, $site['delegation_date']);
        $rowNum++;
    }

    // Auto-size columns
    foreach(range('A','H') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Borders
    $sheet->getStyle('A1:H' . ($rowNum-1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="contractor_sites_' . date('Y-m-d') . '.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;

} catch (Exception $e) {
    die("Export Error: " . $e->getMessage());
}
