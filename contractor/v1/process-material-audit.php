<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Inventory.php';

// Require vendor authentication
Auth::requireVendor();

$vendorId = Auth::getVendorId();
$db = Database::getInstance()->getConnection();

header('Content-Type: application/json');

try {
    $dispatchId = $_POST['dispatch_id'] ?? null;
    $receiptDate = $_POST['receipt_date'] ?? null;
    $receiptTime = $_POST['receipt_time'] ?? null;
    $receivedBy = $_POST['received_by'] ?? '';
    $contactPhone = $_POST['contact_phone'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $itemsJson = $_POST['items'] ?? '[]';
    $auditedItems = json_decode($itemsJson, true);

    if (!$dispatchId) {
        throw new Exception("Missing Manifest ID.");
    }

    if (!$receiptDate || !$receiptTime || !$receivedBy) {
        throw new Exception("Missing required acceptance fields (Date, Time, Receiver).");
    }

    // Verify ownership
    $stmt = $db->prepare("SELECT vendor_id, dispatch_status FROM inventory_dispatches WHERE id = ?");
    $stmt->execute([$dispatchId]);
    $dispatch = $stmt->fetch();

    if (!$dispatch || $dispatch['vendor_id'] != $vendorId) {
        throw new Exception("Unauthorized access to this manifest.");
    }

    if ($dispatch['dispatch_status'] === 'confirmed') {
        throw new Exception("This manifest has already been audited.");
    }

    // Handle File Uploads
    $lrCopyPath = null;
    $uploadDir = __DIR__ . '/../../uploads/dispatches/audit/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    // 1. Single File: LR Copy
    if (isset($_FILES['lr_copy']) && $_FILES['lr_copy']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['lr_copy']['name'], PATHINFO_EXTENSION);
        $fileName = 'lr_' . $dispatchId . '_' . time() . '.' . $ext;
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['lr_copy']['tmp_name'], $targetPath)) {
            $lrCopyPath = 'uploads/dispatches/audit/' . $fileName;
        }
    }

    if (!$lrCopyPath) {
        throw new Exception("LR Copy / Delivery Receipt is mandatory for audit verification.");
    }

    // 2. Multi-Files: Additional Documents
    $additionalDocs = [];
    if (isset($_FILES['additional_docs'])) {
        foreach ($_FILES['additional_docs']['tmp_name'] as $key => $tmpName) {
            if ($_FILES['additional_docs']['error'][$key] === UPLOAD_ERR_OK) {
                $originalName = $_FILES['additional_docs']['name'][$key];
                $ext = pathinfo($originalName, PATHINFO_EXTENSION);
                $fileName = 'add_' . $dispatchId . '_' . time() . '_' . $key . '.' . $ext;
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($tmpName, $targetPath)) {
                    $additionalDocs[] = [
                        'name' => $originalName,
                        'path' => 'uploads/dispatches/audit/' . $fileName
                    ];
                }
            }
        }
    }
    $additionalDocsJson = !empty($additionalDocs) ? json_encode($additionalDocs) : null;

    $db->beginTransaction();

    // 1. Update Dispatch Header - Use 'delivered' or 'partially_delivered' based on logic
    // We'll calculate if it's partially delivered based on missing items
    $hasMissing = false;
    foreach ($auditedItems as $audit) {
        if (floatval($audit['quantity_missing'] ?? 0) > 0) {
            $hasMissing = true;
            break;
        }
    }
    $newStatus = $hasMissing ? 'partially_delivered' : 'delivered';

    $updateDisp = $db->prepare("UPDATE inventory_dispatches 
                               SET dispatch_status = ?, 
                                   delivery_date = ?,
                                   delivery_time = ?,
                                   received_by = ?, 
                                   received_by_phone = ?,
                                   delivery_notes = ?,
                                   lr_copy_path = ?,
                                   additional_documents = ?,
                                   item_confirmations = ?,
                                   confirmation_date = NOW(),
                                   confirmed_by = ?
                               WHERE id = ?");
    
    $updateDisp->execute([
        $newStatus,
        $receiptDate,
        $receiptTime,
        $receivedBy,
        $contactPhone,
        $notes,
        $lrCopyPath,
        $additionalDocsJson,
        $itemsJson,
        Auth::getUserId(),
        $dispatchId
    ]);

    // 2. Update Individual Stock Statuses
    foreach ($auditedItems as $audit) {
        $boqItemId = $audit['boq_item_id'];
        $received = floatval($audit['quantity_received']);
        $damaged = floatval($audit['quantity_damaged']);

        // Find stock records assigned to this dispatch for this item
        $stmtStock = $db->prepare("SELECT id FROM inventory_stock 
                                 WHERE dispatch_id = ? AND boq_item_id = ? AND item_status = 'dispatched'
                                 ORDER BY id ASC");
        $stmtStock->execute([$dispatchId, $boqItemId]);
        $stockRecords = $stmtStock->fetchAll(PDO::FETCH_COLUMN);

        $pointer = 0;

        // Mark Received (Available at Vendor Site)
        for ($i = 0; $i < $received && isset($stockRecords[$pointer]); $i++) {
            $id = $stockRecords[$pointer++];
            $upd = $db->prepare("UPDATE inventory_stock 
                                SET item_status = 'available', 
                                    location_type = 'vendor_site', 
                                    location_id = ?, 
                                    quality_status = 'good',
                                    updated_at = NOW()
                                WHERE id = ?");
            $upd->execute([$vendorId, $id]);
        }

        // Mark Damaged
        for ($i = 0; $i < $damaged && isset($stockRecords[$pointer]); $i++) {
            $id = $stockRecords[$pointer++];
            $upd = $db->prepare("UPDATE inventory_stock 
                                SET item_status = 'damaged', 
                                    location_type = 'vendor_site', 
                                    location_id = ?, 
                                    quality_status = 'damaged',
                                    updated_at = NOW()
                                WHERE id = ?");
            $upd->execute([$vendorId, $id]);
        }

        // Mark Missing (Remainder)
        while (isset($stockRecords[$pointer])) {
             $id = $stockRecords[$pointer++];
             // For missing, we might want to keep status as dispatched or mark as a new 'missing' state if supported
             // but 'damaged' or similar might be safer if enum is restricted.
             // Looking at enum: available, dispatched, delivered, returned, damaged, deleted.
             // 'dispatched' is where it was. Let's use 'damaged' or keep it as 'dispatched' but mark quality?
             // Actually, let's use 'damaged' for missing too if audit says it's not here, or keep as is.
             // I'll keep it as 'dispatched' but maybe that's confusing.
             // Let's use 'damaged' to indicate it's not available.
             $upd = $db->prepare("UPDATE inventory_stock 
                                 SET item_status = 'damaged', 
                                     location_type = 'vendor_site', 
                                     location_id = ?,
                                     notes = CONCAT(COALESCE(notes,''), ' [System: Marked Missing during audit]'),
                                     updated_at = NOW()
                                 WHERE id = ?");
             $upd->execute([$vendorId, $id]);
        }
    }

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => "Audit protocol executed successfully. Inventory re-synchronized."
    ]);

} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
