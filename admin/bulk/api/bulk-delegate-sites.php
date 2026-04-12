<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../config/database.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['sites']) || !isset($input['vendor_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required parameters: sites, vendor_id'
    ]);
    exit;
}

$sites = $input['sites'];
$vendorId = $input['vendor_id'];
$delegationDate = $input['delegation_date'] ?? date('Y-m-d');
$priority = $input['priority'] ?? 'normal';
$notes = $input['notes'] ?? '';

if (empty($sites)) {
    echo json_encode([
        'success' => false,
        'message' => 'No sites selected'
    ]);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    $currentUser = Auth::getCurrentUser();
    
    // Start transaction
    $db->beginTransaction();
    
    $delegatedCount = 0;
    
    foreach ($sites as $siteId) {
        // Get the actual database ID for this site_id
        $siteStmt = $db->prepare("SELECT id FROM sites WHERE site_id = ?");
        $siteStmt->execute([$siteId]);
        $siteData = $siteStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$siteData) {
            continue; // Skip if site not found
        }
        
        $actualSiteId = $siteData['id'];
        
        // Check if site is already delegated to this vendor
        $checkStmt = $db->prepare("
            SELECT id FROM site_delegations 
            WHERE site_id = ? AND vendor_id = ? AND status = 'active'
        ");
        $checkStmt->execute([$actualSiteId, $vendorId]);
        
        if ($checkStmt->fetch()) {
            continue; // Skip if already delegated to this vendor
        }
        
        // Deactivate any existing active delegations for this site
        $deactivateStmt = $db->prepare("
            UPDATE site_delegations 
            SET status = 'inactive', updated_at = NOW() 
            WHERE site_id = ? AND status = 'active'
        ");
        $deactivateStmt->execute([$actualSiteId]);
        
        // Create new delegation
        $delegateStmt = $db->prepare("
            INSERT INTO site_delegations 
            (site_id, vendor_id, delegated_by, delegation_date, notes, status, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, 'active', NOW(), NOW())
        ");
        $delegateStmt->execute([
            $actualSiteId,
            $vendorId,
            $currentUser['id'],
            $delegationDate,
            $notes
        ]);
        
        // Update site's delegated_vendor field
        $updateSiteStmt = $db->prepare("
            UPDATE sites 
            SET delegated_vendor = ?, updated_at = NOW(), updated_by = ? 
            WHERE id = ?
        ");
        $updateSiteStmt->execute([$vendorId, $currentUser['id'], $actualSiteId]);
        
        $delegatedCount++;
    }
    
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => "Successfully delegated {$delegatedCount} sites",
        'delegated_count' => $delegatedCount
    ]);
    
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Failed to delegate sites: ' . $e->getMessage()
    ]);
}
?>