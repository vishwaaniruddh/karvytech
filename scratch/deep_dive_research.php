<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();

$requestId = 90;
echo "--- Deep Dive for Request #$requestId ---\n";

// 1. Material Request Record
$stmt = $db->prepare("SELECT site_id, vendor_id, status FROM material_requests WHERE id = ?");
$stmt->execute([$requestId]);
$mr = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Material Request: " . print_r($mr, true) . "\n";

if ($mr) {
    $sitePid = $mr['site_id'];
    $vendorId = $mr['vendor_id'];

    // 2. Site Details
    $stmt = $db->prepare("SELECT * FROM sites WHERE id = ?");
    $stmt->execute([$sitePid]);
    $site = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Site Record (ID $sitePid): " . print_r($site, true) . "\n";

    // 3. Vendor Info (Requesting Vendor)
    $stmt = $db->prepare("SELECT * FROM vendors WHERE id = ?");
    $stmt->execute([$vendorId]);
    $vendor = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Vendor Record (ID $vendorId): " . print_r($vendor, true) . "\n";

    // 4. Site Delegation (Delegated Vendor)
    $stmt = $db->prepare("SELECT sd.*, v.company_name, v.name as contact_name FROM site_delegations sd LEFT JOIN vendors v ON sd.vendor_id = v.id WHERE sd.site_id = ? AND sd.status = 'active'");
    $stmt->execute([$sitePid]);
    $delegation = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Active Delegation for Site $sitePid: " . print_r($delegation, true) . "\n";

    // 5. Legacy Survey
    $stmt = $db->prepare("SELECT * FROM site_surveys WHERE site_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$sitePid]);
    $legacySurvey = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Legacy Survey: " . print_r($legacySurvey, true) . "\n";

    // 6. Dynamic Survey
    $stmt = $db->prepare("SELECT * FROM dynamic_survey_responses WHERE site_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$sitePid]);
    $dynamicSurvey = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Dynamic Survey: " . print_r($dynamicSurvey, true) . "\n";
}
?>
