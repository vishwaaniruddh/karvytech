<?php
require_once '../../config/auth.php';
require_once '../../includes/rbac_helper.php';
require_once '../../models/Inventory.php';
require_once '../../models/BoqItem.php';

// Check permissions
requirePermission('inventory', 'view');

$currentUser = Auth::getCurrentUser();
$inventoryModel = new Inventory();
$boqModel = new BoqItem();

// Handle audit approval/rejection
if ($_POST && isset($_POST['action']) && isset($_POST['audit_id'])) {
    requirePermission('inventory', 'edit');
    
    $auditId = (int)$_POST['audit_id'];
    $action = $_POST['action'];
    $adminNotes = trim($_POST['admin_notes'] ?? '');
    
    if (in_array($action, ['approved', 'rejected'])) {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Update audit record
            $updateSql = "UPDATE quantity_audit SET 
                status = ?, 
                admin_notes = ?, 
                reviewed_by = ?, 
                reviewed_at = NOW() 
                WHERE id = ?";
            
            $stmt = $db->prepare($updateSql);
            $stmt->execute([$action, $adminNotes, $currentUser['id'], $auditId]);
            
            // If approved, update the actual delivery confirmation
            if ($action === 'approved') {
                // Get audit details
                $auditSql = "SELECT * FROM quantity_audit WHERE id = ?";
                $auditStmt = $db->prepare($auditSql);
                $auditStmt->execute([$auditId]);
                $audit = $auditStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($audit) {
                    // Get current item confirmations from inventory_dispatches
                    $getConfirmationsSql = "SELECT item_confirmations FROM inventory_dispatches WHERE id = ?";
                    $getStmt = $db->prepare($getConfirmationsSql);
                    $getStmt->execute([$audit['dispatch_id']]);
                    $currentConfirmations = $getStmt->fetchColumn();
                    
                    if ($currentConfirmations) {
                        $itemConfirmations = json_decode($currentConfirmations, true) ?: [];
                        
                        // Update the specific item's received quantity
                        foreach ($itemConfirmations as &$confirmation) {
                            if ($confirmation['boq_item_id'] == $audit['boq_item_id']) {
                                $confirmation['received_quantity'] = $audit['corrected_quantity'];
                                break;
                            }
                        }
                        
                        // Update the inventory_dispatches table
                        $updateDeliverySql = "UPDATE inventory_dispatches 
                            SET item_confirmations = ? 
                            WHERE id = ?";
                        
                        $deliveryStmt = $db->prepare($updateDeliverySql);
                        $deliveryStmt->execute([
                            json_encode($itemConfirmations), 
                            $audit['dispatch_id']
                        ]);
                    }
                }
            }
            
            $message = "Audit request " . ($action === 'approved' ? 'approved' : 'rejected') . " successfully.";
            $messageType = 'success';
            
        } catch (Exception $e) {
            $message = "Error processing audit: " . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Get filter parameters
$status = $_GET['status'] ?? 'pending';
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = (int)($_GET['limit'] ?? 20);
$offset = ($page - 1) * $limit;

// Get audit records
$db = Database::getInstance()->getConnection();

$whereClauses = [];
$params = [];

if ($status !== 'all') {
    $whereClauses[] = "qa.status = ?";
    $params[] = $status;
}

// Add search functionality
if (!empty($search)) {
    $whereClauses[] = "(v.company_name LIKE ? OR v.name LIKE ? OR bi.item_name LIKE ? OR bi.item_code LIKE ? OR s.site_id LIKE ? OR id.dispatch_number LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam, $searchParam]);
}

$whereClause = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

$sql = "SELECT 
    qa.*,
    v.company_name,
    v.name as vendor_name,
    bi.item_name,
    bi.item_code,
    bi.unit,
    id.dispatch_number,
    mr.id as request_id,
    s.site_id,
    u.username as reviewer_name
FROM quantity_audit qa
LEFT JOIN vendors v ON qa.vendor_id = v.id
LEFT JOIN boq_items bi ON qa.boq_item_id = bi.id
LEFT JOIN inventory_dispatches id ON qa.dispatch_id = id.id
LEFT JOIN material_requests mr ON id.material_request_id = mr.id
LEFT JOIN sites s ON mr.site_id = s.id
LEFT JOIN users u ON qa.reviewed_by = u.id
$whereClause
ORDER BY qa.created_at DESC
LIMIT $limit OFFSET $offset";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$audits = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count for pagination
$countSql = "SELECT COUNT(*) FROM quantity_audit qa 
LEFT JOIN vendors v ON qa.vendor_id = v.id
LEFT JOIN boq_items bi ON qa.boq_item_id = bi.id
LEFT JOIN inventory_dispatches id ON qa.dispatch_id = id.id
LEFT JOIN material_requests mr ON id.material_request_id = mr.id
LEFT JOIN sites s ON mr.site_id = s.id
$whereClause";
$countStmt = $db->prepare($countSql);
$countStmt->execute($params);
$totalRecords = $countStmt->fetchColumn();
$totalPages = ceil($totalRecords / $limit);

$title = 'Quantity Audits Management';
ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h3 class="card-title mb-0 font-weight-bold">Quantity Audits Management</h3>
                            <small class="text-muted">Manage contractor quantity audit requests</small>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-end">
                                <div class="input-group" style="max-width: 300px;">
                                    <input type="text" class="form-control form-control-sm" 
                                           placeholder="Search audits..." 
                                           value="<?php echo htmlspecialchars($search); ?>" 
                                           id="searchInput">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary btn-sm" type="button" onclick="performSearch()">
                                            <i class="fas fa-search"></i>
                                        </button>
                                        <?php if (!empty($search)): ?>
                                        <button class="btn btn-outline-danger btn-sm" type="button" onclick="clearSearch()">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status Filter Tabs -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <ul class="nav nav-pills nav-fill">
                                <li class="nav-item">
                                    <a href="?status=pending<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>&limit=<?php echo $limit; ?>" 
                                       class="nav-link <?php echo $status === 'pending' ? 'active' : ''; ?>">
                                        <i class="fas fa-clock"></i> Pending
                                        <?php
                                        $pendingCount = $db->prepare("SELECT COUNT(*) FROM quantity_audit WHERE status = 'pending'");
                                        $pendingCount->execute();
                                        $pendingTotal = $pendingCount->fetchColumn();
                                        ?>
                                        <span class="badge badge-light ml-1"><?php echo $pendingTotal; ?></span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="?status=approved<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>&limit=<?php echo $limit; ?>" 
                                       class="nav-link <?php echo $status === 'approved' ? 'active' : ''; ?>">
                                        <i class="fas fa-check"></i> Approved
                                        <?php
                                        $approvedCount = $db->prepare("SELECT COUNT(*) FROM quantity_audit WHERE status = 'approved'");
                                        $approvedCount->execute();
                                        $approvedTotal = $approvedCount->fetchColumn();
                                        ?>
                                        <span class="badge badge-light ml-1"><?php echo $approvedTotal; ?></span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="?status=rejected<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>&limit=<?php echo $limit; ?>" 
                                       class="nav-link <?php echo $status === 'rejected' ? 'active' : ''; ?>">
                                        <i class="fas fa-times"></i> Rejected
                                        <?php
                                        $rejectedCount = $db->prepare("SELECT COUNT(*) FROM quantity_audit WHERE status = 'rejected'");
                                        $rejectedCount->execute();
                                        $rejectedTotal = $rejectedCount->fetchColumn();
                                        ?>
                                        <span class="badge badge-light ml-1"><?php echo $rejectedTotal; ?></span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="?status=all<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>&limit=<?php echo $limit; ?>" 
                                       class="nav-link <?php echo $status === 'all' ? 'active' : ''; ?>">
                                        <i class="fas fa-list"></i> All Records
                                        <?php
                                        $allCount = $db->prepare("SELECT COUNT(*) FROM quantity_audit");
                                        $allCount->execute();
                                        $allTotal = $allCount->fetchColumn();
                                        ?>
                                        <span class="badge badge-light ml-1"><?php echo $allTotal; ?></span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <?php if (isset($message)): ?>
                    <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?> alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                    <?php endif; ?>

                    <?php if (empty($audits)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-clipboard-list fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">No audit requests found</h5>
                        <p class="text-muted">
                            <?php if (!empty($search)): ?>
                                No audit requests match your search criteria.
                                <br><a href="?status=<?php echo $status; ?>&limit=<?php echo $limit; ?>" class="btn btn-sm btn-outline-primary mt-2">Clear Search</a>
                            <?php else: ?>
                                No quantity audit requests match the current filter.
                            <?php endif; ?>
                        </p>
                    </div>
                    <?php else: ?>
                    
                    <!-- Results Summary -->
                    <div class="bg-light px-3 py-2 border-bottom">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    Showing <?php echo count($audits); ?> of <?php echo number_format($totalRecords); ?> audit requests
                                    <?php if (!empty($search)): ?>
                                    for "<?php echo htmlspecialchars($search); ?>"
                                    <?php endif; ?>
                                </small>
                            </div>
                            <div class="col-md-6 text-right">
                                <small class="text-muted">
                                    Page <?php echo $page; ?> of <?php echo $totalPages; ?>
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 60px;">#</th>
                                    <th style="width: 120px;">Audit Info</th>
                                    <th style="width: 200px;">Contractor</th>
                                    <th style="width: 250px;">Material Details</th>
                                    <th style="width: 150px;">Quantities</th>
                                    <th style="width: 200px;">Reason</th>
                                    <th style="width: 120px;">Status</th>
                                    <th style="width: 140px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $serialNumber = $offset + 1;
                                foreach ($audits as $audit): 
                                ?>
                                <tr>
                                    <td class="text-center">
                                        <span class="badge badge-secondary"><?php echo $serialNumber++; ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="font-weight-bold text-primary">#<?php echo $audit['id']; ?></span>
                                            <small class="text-muted"><?php echo date('d M Y', strtotime($audit['created_at'])); ?></small>
                                            <small class="text-muted"><?php echo date('H:i', strtotime($audit['created_at'])); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="font-weight-medium"><?php echo htmlspecialchars($audit['company_name']); ?></span>
                                            <small class="text-muted"><?php echo htmlspecialchars($audit['vendor_name']); ?></small>
                                            <small class="text-info">Site: <?php echo htmlspecialchars($audit['site_id']); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="font-weight-medium"><?php echo htmlspecialchars($audit['item_name']); ?></span>
                                            <small class="text-muted"><?php echo htmlspecialchars($audit['item_code']); ?></small>
                                            <small class="text-secondary">Dispatch: <?php echo htmlspecialchars($audit['dispatch_number']); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="text-muted small">Original:</span>
                                                <span class="badge badge-light"><?php echo number_format($audit['original_quantity']); ?></span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="text-muted small">Corrected:</span>
                                                <span class="badge badge-primary"><?php echo number_format($audit['corrected_quantity']); ?></span>
                                            </div>
                                            <small class="text-muted text-center"><?php echo htmlspecialchars($audit['unit']); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="reason-cell">
                                            <?php if (strlen($audit['reason']) > 60): ?>
                                            <span class="reason-preview"><?php echo htmlspecialchars(substr($audit['reason'], 0, 60)); ?>...</span>
                                            <button type="button" class="btn btn-link btn-sm p-0 ml-1" onclick="toggleReason(this)" data-full-reason="<?php echo htmlspecialchars($audit['reason']); ?>">
                                                <i class="fas fa-expand-alt"></i>
                                            </button>
                                            <?php else: ?>
                                            <span><?php echo htmlspecialchars($audit['reason']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClasses = [
                                            'pending' => 'warning',
                                            'approved' => 'success',
                                            'rejected' => 'danger'
                                        ];
                                        $statusClass = $statusClasses[$audit['status']] ?? 'secondary';
                                        ?>
                                        <div class="d-flex flex-column align-items-center">
                                            <span class="badge badge-<?php echo $statusClass; ?> mb-1">
                                                <?php echo ucfirst($audit['status']); ?>
                                            </span>
                                            <?php if ($audit['reviewed_at']): ?>
                                            <small class="text-muted text-center">
                                                <?php echo htmlspecialchars($audit['reviewer_name']); ?>
                                                <br>
                                                <?php echo date('d M H:i', strtotime($audit['reviewed_at'])); ?>
                                            </small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group-vertical btn-group-sm" role="group">
                                            <?php if ($audit['status'] === 'pending'): ?>
                                            <button type="button" class="btn btn-success btn-sm mb-1" onclick="reviewAudit(<?php echo $audit['id']; ?>, 'approved')" title="Approve Audit">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="reviewAudit(<?php echo $audit['id']; ?>, 'rejected')" title="Reject Audit">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                            <?php else: ?>
                                            <button type="button" class="btn btn-info btn-sm" onclick="viewAuditDetails(<?php echo htmlspecialchars(json_encode($audit)); ?>)" title="View Details">
                                                <i class="fas fa-eye"></i> Details
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Enhanced Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <div class="bg-light px-3 py-3 border-top">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <span class="text-muted mr-3">Items per page:</span>
                                    <select class="form-control form-control-sm" style="width: auto;" onchange="changeItemsPerPage(this.value)">
                                        <option value="10" <?php echo $limit == 10 ? 'selected' : ''; ?>>10</option>
                                        <option value="20" <?php echo $limit == 20 ? 'selected' : ''; ?>>20</option>
                                        <option value="50" <?php echo $limit == 50 ? 'selected' : ''; ?>>50</option>
                                        <option value="100" <?php echo $limit == 100 ? 'selected' : ''; ?>>100</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <nav aria-label="Audit pagination">
                                    <ul class="pagination pagination-sm justify-content-end mb-0">
                                        <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?status=<?php echo $status; ?>&page=1<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>&limit=<?php echo $limit; ?>">
                                                <i class="fas fa-angle-double-left"></i>
                                            </a>
                                        </li>
                                        <li class="page-item">
                                            <a class="page-link" href="?status=<?php echo $status; ?>&page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>&limit=<?php echo $limit; ?>">
                                                <i class="fas fa-angle-left"></i>
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                        
                                        <?php
                                        $startPage = max(1, $page - 2);
                                        $endPage = min($totalPages, $page + 2);
                                        
                                        if ($startPage > 1): ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                        <?php endif;
                                        
                                        for ($i = $startPage; $i <= $endPage; $i++): ?>
                                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?status=<?php echo $status; ?>&page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>&limit=<?php echo $limit; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                        <?php endfor;
                                        
                                        if ($endPage < $totalPages): ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                        <?php endif; ?>
                                        
                                        <?php if ($page < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?status=<?php echo $status; ?>&page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>&limit=<?php echo $limit; ?>">
                                                <i class="fas fa-angle-right"></i>
                                            </a>
                                        </li>
                                        <li class="page-item">
                                            <a class="page-link" href="?status=<?php echo $status; ?>&page=<?php echo $totalPages; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>&limit=<?php echo $limit; ?>">
                                                <i class="fas fa-angle-double-right"></i>
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Review Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="reviewModalTitle">Review Audit Request</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="audit_id" id="reviewAuditId">
                    <input type="hidden" name="action" id="reviewAction">
                    
                    <div class="form-group">
                        <label for="admin_notes">Admin Notes:</label>
                        <textarea name="admin_notes" id="admin_notes" class="form-control" rows="3" 
                                  placeholder="Add notes about your decision (optional)"></textarea>
                    </div>
                    
                    <div id="reviewSummary"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn" id="reviewSubmitBtn">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" style="display: none;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Audit Details</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="auditDetailsContent">
                <!-- Content will be populated by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.modal {
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1050;
    width: 100%;
    height: 100%;
    overflow: hidden;
    outline: 0;
}

.modal.show {
    display: block !important;
}

.modal-dialog {
    position: relative;
    width: auto;
    margin: 0.5rem;
    pointer-events: none;
}

.modal-content {
    position: relative;
    display: flex;
    flex-direction: column;
    width: 100%;
    pointer-events: auto;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid rgba(0,0,0,.2);
    border-radius: 0.3rem;
    outline: 0;
    margin: 1.75rem auto;
    max-width: 500px;
}

.modal-lg .modal-content {
    max-width: 800px;
}

.modal-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    padding: 1rem 1rem;
    border-bottom: 1px solid #dee2e6;
    border-top-left-radius: calc(0.3rem - 1px);
    border-top-right-radius: calc(0.3rem - 1px);
}

.modal-title {
    margin-bottom: 0;
    line-height: 1.5;
    font-size: 1.25rem;
    font-weight: 500;
}

.modal-body {
    position: relative;
    flex: 1 1 auto;
    padding: 1rem;
}

.modal-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding: 0.75rem;
    border-top: 1px solid #dee2e6;
    border-bottom-right-radius: calc(0.3rem - 1px);
    border-bottom-left-radius: calc(0.3rem - 1px);
}

.modal-footer > * {
    margin: 0.25rem;
}

.close {
    float: right;
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1;
    color: #000;
    text-shadow: 0 1px 0 #fff;
    opacity: .5;
    background: transparent;
    border: 0;
    cursor: pointer;
}

.close:hover {
    opacity: .75;
}

.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1040;
    width: 100vw;
    height: 100vh;
    background-color: #000;
}

.modal-backdrop.show {
    opacity: 0.5;
}

body.modal-open {
    overflow: hidden;
}

.form-group {
    margin-bottom: 1rem;
}

.form-control {
    display: block;
    width: 100%;
    padding: 0.375rem 0.75rem;
    font-size: 1rem;
    font-weight: 400;
    line-height: 1.5;
    color: #495057;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
}

.form-control:focus {
    color: #495057;
    background-color: #fff;
    border-color: #80bdff;
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

.alert {
    position: relative;
    padding: 0.75rem 1.25rem;
    margin-bottom: 1rem;
    border: 1px solid transparent;
    border-radius: 0.25rem;
}

.alert-success {
    color: #155724;
    background-color: #d4edda;
    border-color: #c3e6cb;
}

.alert-warning {
    color: #856404;
    background-color: #fff3cd;
    border-color: #ffeaa7;
}

.alert-info {
    color: #0c5460;
    background-color: #d1ecf1;
    border-color: #bee5eb;
}

.alert-secondary {
    color: #383d41;
    background-color: #e2e3e5;
    border-color: #d6d8db;
}

.btn-success {
    color: #fff;
    background-color: #28a745;
    border-color: #28a745;
}

.btn-success:hover {
    background-color: #218838;
    border-color: #1e7e34;
}

.btn-danger {
    color: #fff;
    background-color: #dc3545;
    border-color: #dc3545;
}

.btn-danger:hover {
    background-color: #c82333;
    border-color: #bd2130;
}

.btn-secondary {
    color: #fff;
    background-color: #6c757d;
    border-color: #6c757d;
}

.btn-secondary:hover {
    background-color: #5a6268;
    border-color: #545b62;
}

.table-hover tbody tr:hover {
    background-color: rgba(0,0,0,.075);
}

.thead-light th {
    background-color: #e9ecef;
    border-color: #dee2e6;
    color: #495057;
    font-weight: 600;
    font-size: 0.875rem;
}

.reason-cell {
    max-width: 200px;
    word-wrap: break-word;
}

.btn-group-vertical .btn {
    border-radius: 0.25rem !important;
}

.btn-group-vertical .btn + .btn {
    margin-top: 0.25rem;
    margin-left: 0;
}

.font-weight-medium {
    font-weight: 500;
}

.badge {
    font-size: 0.75em;
    padding: 0.25em 0.4em;
}

.table td {
    vertical-align: middle;
    padding: 0.75rem;
}

.table th {
    border-top: none;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
}

.nav-pills .nav-link {
    border-radius: 0.375rem;
    font-weight: 500;
    transition: all 0.2s;
}

.nav-pills .nav-link.active {
    background-color: #007bff;
    color: white;
}

.nav-pills .nav-link:not(.active) {
    color: #6c757d;
    background-color: transparent;
}

.nav-pills .nav-link:not(.active):hover {
    background-color: #f8f9fa;
    color: #007bff;
}

.card {
    border: none;
    border-radius: 0.5rem;
}

.card-header {
    border-bottom: 1px solid #e9ecef;
    background-color: #fff;
}

.shadow-sm {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
}

.pagination-sm .page-link {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.pagination .page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
}

.form-control-sm {
    height: calc(1.5em + 0.5rem + 2px);
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.input-group-sm > .form-control,
.input-group-sm > .input-group-prepend > .input-group-text,
.input-group-sm > .input-group-append > .input-group-text,
.input-group-sm > .input-group-prepend > .btn,
.input-group-sm > .input-group-append > .btn {
    height: calc(1.5em + 0.5rem + 2px);
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.badge-light {
    background-color: #f8f9fa;
    color: #6c757d;
}

.text-info {
    color: #17a2b8 !important;
}

.font-weight-medium {
    font-weight: 500 !important;
}
</style>

<?php
$content = ob_get_clean();
include '../../includes/admin_layout.php';
?>

<script>
function reviewAudit(auditId, action) {
    document.getElementById('reviewAuditId').value = auditId;
    document.getElementById('reviewAction').value = action;
    
    const title = action === 'approved' ? 'Approve Audit Request' : 'Reject Audit Request';
    const btnClass = action === 'approved' ? 'btn-success' : 'btn-danger';
    const btnText = action === 'approved' ? 'Approve' : 'Reject';
    
    document.getElementById('reviewModalTitle').textContent = title;
    document.getElementById('reviewSubmitBtn').className = 'btn ' + btnClass;
    document.getElementById('reviewSubmitBtn').textContent = btnText;
    
    const summary = action === 'approved' 
        ? '<div class="alert alert-success">This will approve the quantity correction and update the delivery confirmation.</div>'
        : '<div class="alert alert-warning">This will reject the audit request. The original quantities will remain unchanged.</div>';
    
    document.getElementById('reviewSummary').innerHTML = summary;
    
    // Show modal using vanilla JavaScript
    const modal = document.getElementById('reviewModal');
    modal.style.display = 'block';
    modal.classList.add('show');
    document.body.classList.add('modal-open');
    
    // Add backdrop
    const backdrop = document.createElement('div');
    backdrop.className = 'modal-backdrop fade show';
    backdrop.id = 'modal-backdrop';
    document.body.appendChild(backdrop);
}

function viewAuditDetails(audit) {
    const content = `
        <div class="row">
            <div class="col-md-6">
                <h6>Audit Information</h6>
                <table class="table table-sm">
                    <tr><td><strong>Audit ID:</strong></td><td>#${audit.id}</td></tr>
                    <tr><td><strong>Date:</strong></td><td>${new Date(audit.created_at).toLocaleString()}</td></tr>
                    <tr><td><strong>Status:</strong></td><td><span class="badge badge-${audit.status === 'approved' ? 'success' : audit.status === 'rejected' ? 'danger' : 'warning'}">${audit.status.charAt(0).toUpperCase() + audit.status.slice(1)}</span></td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Material Information</h6>
                <table class="table table-sm">
                    <tr><td><strong>Item:</strong></td><td>${audit.item_name}</td></tr>
                    <tr><td><strong>Code:</strong></td><td>${audit.item_code}</td></tr>
                    <tr><td><strong>Original Qty:</strong></td><td>${audit.original_quantity} ${audit.unit}</td></tr>
                    <tr><td><strong>Corrected Qty:</strong></td><td>${audit.corrected_quantity} ${audit.unit}</td></tr>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <h6>Contractor Information</h6>
                <table class="table table-sm">
                    <tr><td><strong>Company:</strong></td><td>${audit.company_name}</td></tr>
                    <tr><td><strong>Contact:</strong></td><td>${audit.vendor_name}</td></tr>
                    <tr><td><strong>Site:</strong></td><td>${audit.site_id}</td></tr>
                    <tr><td><strong>Dispatch:</strong></td><td>${audit.dispatch_number}</td></tr>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <h6>Reason for Correction</h6>
                <div class="alert alert-info">${audit.reason}</div>
            </div>
        </div>
        ${audit.admin_notes ? `
        <div class="row">
            <div class="col-12">
                <h6>Admin Notes</h6>
                <div class="alert alert-secondary">${audit.admin_notes}</div>
                <small class="text-muted">Reviewed by ${audit.reviewer_name} on ${new Date(audit.reviewed_at).toLocaleString()}</small>
            </div>
        </div>
        ` : ''}
    `;
    
    document.getElementById('auditDetailsContent').innerHTML = content;
    
    // Show details modal using vanilla JavaScript
    const modal = document.getElementById('detailsModal');
    modal.style.display = 'block';
    modal.classList.add('show');
    document.body.classList.add('modal-open');
    
    // Add backdrop
    const backdrop = document.createElement('div');
    backdrop.className = 'modal-backdrop fade show';
    backdrop.id = 'details-modal-backdrop';
    document.body.appendChild(backdrop);
}

function showFullReason(reason) {
    alert(reason);
}

function toggleReason(button) {
    const reasonCell = button.closest('.reason-cell');
    const preview = reasonCell.querySelector('.reason-preview');
    const fullReason = button.getAttribute('data-full-reason');
    
    if (preview.style.display === 'none') {
        // Show preview, hide full
        preview.style.display = 'inline';
        preview.textContent = fullReason.substring(0, 60) + '...';
        button.innerHTML = '<i class="fas fa-expand-alt"></i>';
        button.title = 'Show full reason';
    } else {
        // Show full, hide preview
        preview.style.display = 'none';
        preview.textContent = fullReason;
        preview.style.display = 'inline';
        button.innerHTML = '<i class="fas fa-compress-alt"></i>';
        button.title = 'Show less';
    }
}

function performSearch() {
    const searchValue = document.getElementById('searchInput').value.trim();
    const currentStatus = '<?php echo $status; ?>';
    const currentLimit = '<?php echo $limit; ?>';
    
    if (searchValue) {
        window.location.href = `?status=${currentStatus}&search=${encodeURIComponent(searchValue)}&limit=${currentLimit}`;
    } else {
        window.location.href = `?status=${currentStatus}&limit=${currentLimit}`;
    }
}

function clearSearch() {
    const currentStatus = '<?php echo $status; ?>';
    const currentLimit = '<?php echo $limit; ?>';
    window.location.href = `?status=${currentStatus}&limit=${currentLimit}`;
}

function changeItemsPerPage(newLimit) {
    const currentStatus = '<?php echo $status; ?>';
    const currentSearch = '<?php echo htmlspecialchars($search); ?>';
    
    let url = `?status=${currentStatus}&limit=${newLimit}`;
    if (currentSearch) {
        url += `&search=${encodeURIComponent(currentSearch)}`;
    }
    
    window.location.href = url;
}

// Enter key search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
    }
});

// Close modal functionality
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.style.display = 'none';
    modal.classList.remove('show');
    document.body.classList.remove('modal-open');
    
    // Remove backdrop
    const backdrop = document.getElementById('modal-backdrop') || document.getElementById('details-modal-backdrop');
    if (backdrop) {
        backdrop.remove();
    }
}

// Add event listeners for close buttons
document.addEventListener('DOMContentLoaded', function() {
    // Close buttons
    document.querySelectorAll('[data-dismiss="modal"]').forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal) {
                closeModal(modal.id);
            }
        });
    });
    
    // Close on backdrop click
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-backdrop')) {
            const modals = document.querySelectorAll('.modal.show');
            modals.forEach(modal => {
                closeModal(modal.id);
            });
        }
    });
    
    // Close on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modals = document.querySelectorAll('.modal.show');
            modals.forEach(modal => {
                closeModal(modal.id);
            });
        }
    });
});
</script>