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

<div class="audit-management-container">
    <!-- Filter Tabs -->
    <div class="filter-tabs">
        <?php
        $pendingCount = $db->prepare("SELECT COUNT(*) FROM quantity_audit WHERE status = 'pending'");
        $pendingCount->execute();
        $pendingTotal = $pendingCount->fetchColumn();
        
        $approvedCount = $db->prepare("SELECT COUNT(*) FROM quantity_audit WHERE status = 'approved'");
        $approvedCount->execute();
        $approvedTotal = $approvedCount->fetchColumn();
        
        $rejectedCount = $db->prepare("SELECT COUNT(*) FROM quantity_audit WHERE status = 'rejected'");
        $rejectedCount->execute();
        $rejectedTotal = $rejectedCount->fetchColumn();
        
        $allCount = $db->prepare("SELECT COUNT(*) FROM quantity_audit");
        $allCount->execute();
        $allTotal = $allCount->fetchColumn();
        ?>
        
        <a href="?status=pending<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>&limit=<?php echo $limit; ?>" 
           class="filter-tab <?php echo $status === 'pending' ? 'active' : ''; ?>">
            <i class="fas fa-clock"></i>
            Pending
            <span class="count"><?php echo $pendingTotal; ?></span>
        </a>
        
        <a href="?status=approved<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>&limit=<?php echo $limit; ?>" 
           class="filter-tab <?php echo $status === 'approved' ? 'active' : ''; ?>">
            <i class="fas fa-check-circle"></i>
            Approved
            <span class="count"><?php echo $approvedTotal; ?></span>
        </a>
        
        <a href="?status=rejected<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>&limit=<?php echo $limit; ?>" 
           class="filter-tab <?php echo $status === 'rejected' ? 'active' : ''; ?>">
            <i class="fas fa-times-circle"></i>
            Rejected
            <span class="count"><?php echo $rejectedTotal; ?></span>
        </a>
        
        <a href="?status=all<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>&limit=<?php echo $limit; ?>" 
           class="filter-tab <?php echo $status === 'all' ? 'active' : ''; ?>">
            <i class="fas fa-list-alt"></i>
            All Records
            <span class="count"><?php echo $allTotal; ?></span>
        </a>
    </div>

    <!-- Search and Controls -->
    <div class="controls-section">
        <div class="search-container">
            <div class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input" 
                       placeholder="Search by contractor, item, site..." 
                       value="<?php echo htmlspecialchars($search); ?>" 
                       id="searchInput">
                <?php if (!empty($search)): ?>
                <button class="clear-search" onclick="clearSearch()" title="Clear search">
                    <i class="fas fa-times"></i>
                </button>
                <?php endif; ?>
            </div>
        </div>
        <div class="items-per-page">
            <label>Show:</label>
            <select onchange="changeItemsPerPage(this.value)">
                <option value="10" <?php echo $limit == 10 ? 'selected' : ''; ?>>10</option>
                <option value="20" <?php echo $limit == 20 ? 'selected' : ''; ?>>20</option>
                <option value="50" <?php echo $limit == 50 ? 'selected' : ''; ?>>50</option>
                <option value="100" <?php echo $limit == 100 ? 'selected' : ''; ?>>100</option>
            </select>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content-card">
        <?php if (isset($message)): ?>
        <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?>">
            <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <?php if (empty($audits)): ?>
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <h3>No audit requests found</h3>
            <p>
                <?php if (!empty($search)): ?>
                    No audit requests match your search criteria.
                    <br><button class="btn-link" onclick="clearSearch()">Clear Search</button>
                <?php else: ?>
                    No quantity audit requests match the current filter.
                <?php endif; ?>
            </p>
        </div>
        <?php else: ?>
        
        <!-- Results Info -->
        <div class="results-info">
            <span>Showing <?php echo count($audits); ?> of <?php echo number_format($totalRecords); ?> audit requests</span>
            <?php if (!empty($search)): ?>
            <span class="search-info">for "<strong><?php echo htmlspecialchars($search); ?></strong>"</span>
            <?php endif; ?>
        </div>
        
        <!-- Data Table -->
        <div class="table-container">
            <table class="audit-table">
                <thead>
                    <tr>
                        <th width="80">#</th>
                        <th width="120">Date</th>
                        <th width="200">Contractor</th>
                        <th width="250">Material</th>
                        <th width="150">Quantities</th>
                        <th width="200">Reason</th>
                        <th width="100">Status</th>
                        <th width="140">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $serialNumber = $offset + 1;
                    foreach ($audits as $audit): 
                    ?>
                    <tr>
                        <td class="text-center">
                            <span class="audit-id">#<?php echo $audit['id']; ?></span>
                        </td>
                        <td>
                            <div class="date-info">
                                <div class="date"><?php echo date('M d, Y', strtotime($audit['created_at'])); ?></div>
                                <div class="time"><?php echo date('H:i', strtotime($audit['created_at'])); ?></div>
                            </div>
                        </td>
                        <td>
                            <div class="contractor-info">
                                <div class="company"><?php echo htmlspecialchars($audit['company_name']); ?></div>
                                <div class="contact"><?php echo htmlspecialchars($audit['vendor_name']); ?></div>
                                <div class="site"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($audit['site_id']); ?></div>
                            </div>
                        </td>
                        <td>
                            <div class="material-info">
                                <div class="item-name"><?php echo htmlspecialchars($audit['item_name']); ?></div>
                                <div class="item-code"><?php echo htmlspecialchars($audit['item_code']); ?></div>
                                <div class="dispatch"><i class="fas fa-truck"></i> <?php echo htmlspecialchars($audit['dispatch_number']); ?></div>
                            </div>
                        </td>
                        <td>
                            <div class="quantities">
                                <div class="quantity-row">
                                    <span class="label">Original:</span>
                                    <span class="value original"><?php echo number_format($audit['original_quantity']); ?></span>
                                </div>
                                <div class="quantity-row">
                                    <span class="label">Corrected:</span>
                                    <span class="value corrected"><?php echo number_format($audit['corrected_quantity']); ?></span>
                                </div>
                                <div class="unit"><?php echo htmlspecialchars($audit['unit']); ?></div>
                            </div>
                        </td>
                        <td>
                            <div class="reason-cell">
                                <?php if (strlen($audit['reason']) > 80): ?>
                                <span class="reason-preview"><?php echo htmlspecialchars(substr($audit['reason'], 0, 80)); ?>...</span>
                                <button class="expand-btn" onclick="toggleReason(this)" data-full-reason="<?php echo htmlspecialchars($audit['reason']); ?>">
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
                                'pending' => 'status-pending',
                                'approved' => 'status-approved',
                                'rejected' => 'status-rejected'
                            ];
                            $statusClass = $statusClasses[$audit['status']] ?? 'status-default';
                            ?>
                            <div class="status-info">
                                <span class="status-badge <?php echo $statusClass; ?>">
                                    <?php echo ucfirst($audit['status']); ?>
                                </span>
                                <?php if ($audit['reviewed_at']): ?>
                                <div class="reviewer-info">
                                    <div class="reviewer"><?php echo htmlspecialchars($audit['reviewer_name']); ?></div>
                                    <div class="review-date"><?php echo date('M d, H:i', strtotime($audit['reviewed_at'])); ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <?php if ($audit['status'] === 'pending'): ?>
                                <button class="action-btn approve-btn" onclick="reviewAudit(<?php echo $audit['id']; ?>, 'approved')" title="Approve">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button class="action-btn reject-btn" onclick="reviewAudit(<?php echo $audit['id']; ?>, 'rejected')" title="Reject">
                                    <i class="fas fa-times"></i>
                                </button>
                                <?php else: ?>
                                <button class="action-btn details-btn" onclick="viewAuditDetails(<?php echo htmlspecialchars(json_encode($audit)); ?>)" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination-container">
            <div class="pagination-info">
                Page <?php echo $page; ?> of <?php echo $totalPages; ?>
            </div>
            <div class="pagination">
                <?php if ($page > 1): ?>
                <a href="?status=<?php echo $status; ?>&page=1<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>&limit=<?php echo $limit; ?>" class="page-btn">
                    <i class="fas fa-angle-double-left"></i>
                </a>
                <a href="?status=<?php echo $status; ?>&page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>&limit=<?php echo $limit; ?>" class="page-btn">
                    <i class="fas fa-angle-left"></i>
                </a>
                <?php endif; ?>
                
                <?php
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $page + 2);
                
                if ($startPage > 1): ?>
                <span class="page-dots">...</span>
                <?php endif;
                
                for ($i = $startPage; $i <= $endPage; $i++): ?>
                <a href="?status=<?php echo $status; ?>&page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>&limit=<?php echo $limit; ?>" 
                   class="page-btn <?php echo $i === $page ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
                <?php endfor;
                
                if ($endPage < $totalPages): ?>
                <span class="page-dots">...</span>
                <?php endif; ?>
                
                <?php if ($page < $totalPages): ?>
                <a href="?status=<?php echo $status; ?>&page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>&limit=<?php echo $limit; ?>" class="page-btn">
                    <i class="fas fa-angle-right"></i>
                </a>
                <a href="?status=<?php echo $status; ?>&page=<?php echo $totalPages; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>&limit=<?php echo $limit; ?>" class="page-btn">
                    <i class="fas fa-angle-double-right"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php endif; ?>
    </div>
</div>

<!-- Review Modal -->
<div class="modal-overlay" id="reviewModal">
    <div class="modal-container">
        <div class="modal-header">
            <h3 id="reviewModalTitle">Review Audit Request</h3>
            <button class="modal-close" onclick="closeModal('reviewModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="audit_id" id="reviewAuditId">
                <input type="hidden" name="action" id="reviewAction">
                
                <div class="form-group">
                    <label for="admin_notes">Admin Notes:</label>
                    <textarea name="admin_notes" id="admin_notes" rows="4" 
                              placeholder="Add notes about your decision (optional)"></textarea>
                </div>
                
                <div id="reviewSummary"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('reviewModal')">Cancel</button>
                <button type="submit" class="btn-primary" id="reviewSubmitBtn">Submit</button>
            </div>
        </form>
    </div>
</div>

<!-- Details Modal -->
<div class="modal-overlay" id="detailsModal">
    <div class="modal-container large">
        <div class="modal-header">
            <h3>Audit Details</h3>
            <button class="modal-close" onclick="closeModal('detailsModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body" id="auditDetailsContent">
            <!-- Content will be populated by JavaScript -->
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeModal('detailsModal')">Close</button>
        </div>
    </div>
</div>

<style>
/* Professional Tabular Design */
.audit-management-container {
    padding: 0;
    background: #f8fafc;
    font-size: 18px; /* Increased base font size */
}

/* Filter Tabs */
.filter-tabs {
    display: flex;
    background: white;
    border-radius: 8px;
    margin-bottom: 1rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.filter-tab {
    flex: 1;
    padding: 1rem 1.5rem;
    text-decoration: none;
    color: #64748b;
    background: white;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    font-weight: 500;
    font-size: 18px;
    transition: all 0.3s ease;
    position: relative;
    white-space: nowrap;
}

.filter-tab:hover {
    background: #f1f5f9;
    color: #334155;
}

.filter-tab.active {
    background: #3b82f6;
    color: white;
}

.filter-tab .count {
    background: rgba(0, 0, 0, 0.1);
    padding: 0.3rem 0.5rem;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
}

.filter-tab.active .count {
    background: rgba(255, 255, 255, 0.2);
}

/* Controls Section */
.controls-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    gap: 1rem;
}

.search-container {
    flex: 1;
    max-width: 300px;
}

.search-box {
    position: relative;
    display: flex;
    align-items: center;
}

.search-input {
    width: 100%;
    padding: 0.8rem 1rem 0.8rem 2.5rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    background: white;
    color: #374151;
    font-size: 17px;
    transition: all 0.3s ease;
}

.search-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
}

.search-icon {
    position: absolute;
    left: 1rem;
    color: #9ca3af;
    z-index: 2;
    font-size: 18px;
}

.clear-search {
    position: absolute;
    right: 0.4rem;
    background: #ef4444;
    border: none;
    border-radius: 3px;
    width: 1.2rem;
    height: 1.2rem;
    color: white;
    cursor: pointer;
    font-size: 12px;
    transition: all 0.3s ease;
}

.clear-search:hover {
    background: #dc2626;
}

.items-per-page {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 16px;
}

.items-per-page label {
    color: #64748b;
    font-weight: 500;
    font-size: 16px;
}

.items-per-page select {
    padding: 0.3rem;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    background: white;
    color: #374151;
    font-size: 16px;
}

/* Content Card */
.content-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

/* Alert */
.alert {
    padding: 0.6rem 1rem;
    margin: 1rem;
    border-radius: 6px;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-weight: 500;
    font-size: 18px;
}

.alert.alert-success {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.alert.alert-danger {
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 2.5rem 1.5rem;
}

.empty-icon {
    font-size: 2.5rem;
    color: #d1d5db;
    margin-bottom: 0.8rem;
}

.empty-state h3 {
    color: #374151;
    margin-bottom: 0.4rem;
    font-size: 12px;
}

.empty-state p {
    color: #6b7280;
    font-size: 16px;
}

.btn-link {
    background: none;
    border: none;
    color: #3b82f6;
    text-decoration: underline;
    cursor: pointer;
    font-size: 16px;
}

/* Results Info */
.results-info {
    padding: 0.6rem 1rem;
    background: #f8fafc;
    border-bottom: 1px solid #e5e7eb;
    color: #64748b;
    font-size: 16px;
}

.search-info {
    margin-left: 0.4rem;
}

/* Table */
.table-container {
    overflow-x: auto;
}

.audit-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 16px;
}

.audit-table th {
    background: #f8fafc;
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: #374151;
    border-bottom: 2px solid #e5e7eb;
    font-size: 15px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    white-space: nowrap;
}

.audit-table td {
    padding: 1rem;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: top;
    white-space: nowrap;
    font-size: 16px;
}

.audit-table tr:hover {
    background: #f9fafb;
}

/* Table Cell Content */
.audit-id {
    font-weight: 600;
    color: #3b82f6;
    font-size: 14px;
}

.date-info .date {
    font-weight: 500;
    color: #374151;
    font-size: 13px;
    display: block;
}

.date-info .time {
    font-size: 12px;
    color: #6b7280;
    display: block;
}

.contractor-info .company {
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.15rem;
    font-size: 14px;
    display: block;
}

.contractor-info .contact {
    font-size: 13px;
    color: #6b7280;
    margin-bottom: 0.15rem;
    display: block;
}

.contractor-info .site {
    font-size: 13px;
    color: #3b82f6;
    display: flex;
    align-items: center;
    gap: 0.2rem;
}

.material-info .item-name {
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.15rem;
    font-size: 14px;
    display: block;
}

.material-info .item-code {
    font-size: 13px;
    color: #6b7280;
    margin-bottom: 0.15rem;
    display: block;
}

.material-info .dispatch {
    font-size: 13px;
    color: #3b82f6;
    display: flex;
    align-items: center;
    gap: 0.2rem;
}

.quantities {
    font-size: 14px;
}

.quantity-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.15rem;
}

.quantity-row .label {
    color: #6b7280;
    font-size: 13px;
}

.quantity-row .value {
    font-weight: 600;
    padding: 0.15rem 0.3rem;
    border-radius: 3px;
    font-size: 13px;
}

.value.original {
    background: #fef2f2;
    color: #dc2626;
}

.value.corrected {
    background: #dcfce7;
    color: #16a34a;
}

.unit {
    text-align: center;
    font-size: 12px;
    color: #6b7280;
    margin-top: 0.15rem;
    display: block;
}

.reason-cell {
    max-width: 150px;
    word-wrap: break-word;
    line-height: 1.2;
    font-size: 14px;
}

.expand-btn {
    background: none;
    border: none;
    color: #3b82f6;
    cursor: pointer;
    margin-left: 0.3rem;
    padding: 0.15rem;
    border-radius: 3px;
    transition: background 0.3s ease;
    font-size: 13px;
}

.expand-btn:hover {
    background: #f3f4f6;
}

.status-info {
    text-align: center;
}

.status-badge {
    padding: 0.3rem 0.5rem;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    display: inline-block;
}

.status-pending {
    background: #fef3c7;
    color: #d97706;
}

.status-approved {
    background: #dcfce7;
    color: #16a34a;
}

.status-rejected {
    background: #fef2f2;
    color: #dc2626;
}

.reviewer-info {
    margin-top: 0.3rem;
    font-size: 11px;
    color: #6b7280;
    text-align: center;
}

.reviewer-info .reviewer {
    font-weight: 500;
    display: block;
}

.reviewer-info .review-date {
    display: block;
}

.action-buttons {
    display: flex;
    gap: 0.3rem;
    justify-content: center;
}

.action-btn {
    width: 2rem;
    height: 2rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    font-size: 16px;
}

.approve-btn {
    background: #16a34a;
    color: white;
}

.approve-btn:hover {
    background: #15803d;
    transform: translateY(-1px);
}

.reject-btn {
    background: #dc2626;
    color: white;
}

.reject-btn:hover {
    background: #b91c1c;
    transform: translateY(-1px);
}

.details-btn {
    background: #3b82f6;
    color: white;
}

.details-btn:hover {
    background: #2563eb;
    transform: translateY(-1px);
}

/* Pagination */
.pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.8rem 1rem;
    background: #f8fafc;
    border-top: 1px solid #e5e7eb;
}

.pagination-info {
    color: #64748b;
    font-weight: 500;
    font-size: 16px;
}

.pagination {
    display: flex;
    gap: 0.2rem;
}

.page-btn {
    padding: 0.3rem 0.5rem;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    color: #374151;
    text-decoration: none;
    transition: all 0.3s ease;
    min-width: 2rem;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
}

.page-btn:hover {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
}

.page-btn.active {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
}

.page-dots {
    padding: 0.3rem;
    color: #9ca3af;
    font-size: 15px;
}

/* Modal Styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    backdrop-filter: blur(4px);
}

.modal-overlay.show {
    display: flex;
}

.modal-container {
    background: white;
    border-radius: 12px;
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 25px rgba(0, 0, 0, 0.1);
}

.modal-container.large {
    max-width: 800px;
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    color: #374151;
    font-weight: 600;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.2rem;
    color: #6b7280;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.modal-close:hover {
    background: #f3f4f6;
    color: #374151;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: #374151;
    font-weight: 500;
}

.form-group textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    resize: vertical;
    font-family: inherit;
    transition: border-color 0.3s ease;
}

.form-group textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.btn-primary {
    background: #3b82f6;
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 6px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: #2563eb;
}

.btn-secondary {
    background: #f3f4f6;
    color: #374151;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 6px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-secondary:hover {
    background: #e5e7eb;
}

/* Responsive Design */
@media (max-width: 768px) {
    .filter-tabs {
        flex-direction: column;
    }
    
    .controls-section {
        flex-direction: column;
        gap: 1rem;
    }
    
    .search-container {
        max-width: 100%;
    }
    
    .table-container {
        font-size: 0.85rem;
    }
    
    .audit-table th,
    .audit-table td {
        padding: 0.75rem 0.5rem;
    }
    
    .pagination-container {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .action-buttons {
        flex-direction: column;
    }
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
    const btnText = action === 'approved' ? 'Approve' : 'Reject';
    
    document.getElementById('reviewModalTitle').textContent = title;
    document.getElementById('reviewSubmitBtn').textContent = btnText;
    
    const summary = action === 'approved' 
        ? '<div class="alert alert-success">This will approve the quantity correction and update the delivery confirmation.</div>'
        : '<div class="alert alert-danger">This will reject the audit request. The original quantities will remain unchanged.</div>';
    
    document.getElementById('reviewSummary').innerHTML = summary;
    
    showModal('reviewModal');
}

function viewAuditDetails(audit) {
    const content = `
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
            <div>
                <h4 style="color: #374151; margin-bottom: 1rem;">Audit Information</h4>
                <div style="background: #f8fafc; padding: 1rem; border-radius: 8px;">
                    <p><strong>Audit ID:</strong> #${audit.id}</p>
                    <p><strong>Date:</strong> ${new Date(audit.created_at).toLocaleString()}</p>
                    <p><strong>Status:</strong> <span class="status-badge status-${audit.status}">${audit.status.charAt(0).toUpperCase() + audit.status.slice(1)}</span></p>
                </div>
            </div>
            <div>
                <h4 style="color: #374151; margin-bottom: 1rem;">Material Information</h4>
                <div style="background: #f8fafc; padding: 1rem; border-radius: 8px;">
                    <p><strong>Item:</strong> ${audit.item_name}</p>
                    <p><strong>Code:</strong> ${audit.item_code}</p>
                    <p><strong>Original Qty:</strong> ${audit.original_quantity} ${audit.unit}</p>
                    <p><strong>Corrected Qty:</strong> ${audit.corrected_quantity} ${audit.unit}</p>
                </div>
            </div>
        </div>
        <div style="margin-bottom: 2rem;">
            <h4 style="color: #374151; margin-bottom: 1rem;">Contractor Information</h4>
            <div style="background: #f8fafc; padding: 1rem; border-radius: 8px;">
                <p><strong>Company:</strong> ${audit.company_name}</p>
                <p><strong>Contact:</strong> ${audit.vendor_name}</p>
                <p><strong>Site:</strong> ${audit.site_id}</p>
                <p><strong>Dispatch:</strong> ${audit.dispatch_number}</p>
            </div>
        </div>
        <div style="margin-bottom: 2rem;">
            <h4 style="color: #374151; margin-bottom: 1rem;">Reason for Correction</h4>
            <div style="background: #f0f9ff; padding: 1rem; border-radius: 8px; border-left: 4px solid #3b82f6;">
                ${audit.reason}
            </div>
        </div>
        ${audit.admin_notes ? `
        <div>
            <h4 style="color: #374151; margin-bottom: 1rem;">Admin Notes</h4>
            <div style="background: #dcfce7; padding: 1rem; border-radius: 8px; border-left: 4px solid #16a34a;">
                ${audit.admin_notes}
                <p style="margin-top: 1rem; font-size: 0.9rem; color: #166534;">
                    <strong>Reviewed by:</strong> ${audit.reviewer_name} on ${new Date(audit.reviewed_at).toLocaleString()}
                </p>
            </div>
        </div>
        ` : ''}
    `;
    
    document.getElementById('auditDetailsContent').innerHTML = content;
    showModal('detailsModal');
}

function toggleReason(button) {
    const reasonCell = button.closest('.reason-cell');
    const preview = reasonCell.querySelector('.reason-preview');
    const fullReason = button.getAttribute('data-full-reason');
    
    if (preview.style.display === 'none') {
        preview.style.display = 'inline';
        preview.textContent = fullReason.substring(0, 80) + '...';
        button.innerHTML = '<i class="fas fa-expand-alt"></i>';
        button.title = 'Show full reason';
    } else {
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

function showModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.remove('show');
    document.body.style.overflow = 'auto';
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    // Search on Enter key
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
    }
    
    // Close modal on backdrop click
    document.querySelectorAll('.modal-overlay').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(this.id);
            }
        });
    });
    
    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.show').forEach(modal => {
                closeModal(modal.id);
            });
        }
    });
});
</script>