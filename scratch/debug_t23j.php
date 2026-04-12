<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();
$siteId = 'T23J';
$stmt = $db->prepare("SELECT id FROM sites WHERE site_id = ?");
$stmt->execute([$siteId]);
$site = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Site ID for $siteId: " . ($site['id'] ?? 'Not found') . "\n";

if ($site) {
    echo "Recent installation delegations for site ID {$site['id']}:\n";
    $stmt = $db->prepare("
        SELECT id.*, v.name as vendor_name, v.company_name 
        FROM installation_delegations id 
        LEFT JOIN vendors v ON id.vendor_id = v.id 
        WHERE id.site_id = ? 
        ORDER BY id.id DESC
    ");
    $stmt->execute([$site['id']]);
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
}
?>
