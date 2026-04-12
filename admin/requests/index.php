<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/MaterialRequest.php';
require_once __DIR__ . '/../../models/Site.php';
require_once __DIR__ . '/../../models/Vendor.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$materialRequestModel = new MaterialRequest();
$siteModel = new Site();
$vendorModel = new Vendor();

// Handle pagination and filters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;

$filters = [
    'status' => $_GET['status'] ?? '',
    'vendor_id' => $_GET['vendor_id'] ?? '',
    'site_id' => $_GET['site_id'] ?? ''
];

// Get material requests with pagination
$requestsData = $materialRequestModel->getAllWithPagination($page, $limit, $filters);
$requests = $requestsData['requests'];
$totalPages = $requestsData['pages'];

// Get sites and vendors for filters
$sites = $siteModel->getAllSites();
$vendors = $vendorModel->getAllVendors();

// Get statistics
$stats = $materialRequestModel->getStats();

$title = 'Material Requests Hub';
ob_start();
?>

<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Material Requests Hub</h1>
        <p class="text-sm font-medium text-gray-500 mt-1">Operational oversight of vendor requisitions and site deployments</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="bulk_material_requests_upload.php" class="btn bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-bold text-xs py-2.5 shadow-sm inline-flex items-center">
            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
            Bulk Upload
        </a>
        <button onclick="exportRequests()" class="btn bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-bold text-xs py-2.5 shadow-sm inline-flex items-center">
            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Export Logs
        </button>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <?php
    $statItems = [
        ['label' => 'Total Volume', 'value' => $stats['total'], 'color' => 'blue', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
        ['label' => 'Awaiting Review', 'value' => $stats['pending'], 'color' => 'amber', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['label' => 'Approved Requests', 'value' => $stats['approved'], 'color' => 'emerald', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['label' => 'Active Dispatches', 'value' => $stats['dispatched'], 'color' => 'indigo', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4']
    ];
    foreach ($statItems as $item): ?>
    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div class="w-10 h-10 bg-<?php echo $item['color']; ?>-50 rounded-lg flex items-center justify-center text-<?php echo $item['color']; ?>-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo $item['icon']; ?>"/></svg>
            </div>
            <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider"><?php echo $item['label']; ?></span>
        </div>
        <div class="text-3xl font-bold text-gray-900"><?php echo number_format($item['value']); ?></div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Search and Filters -->
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Lifecycle Status</label>
            <select id="statusFilter" class="block w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm font-semibold focus:ring-blue-500 focus:bg-white outline-none">
                <option value="">All Status</option>
                <option value="draft" <?php echo $filters['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                <option value="pending" <?php echo $filters['status'] === 'pending' ? 'selected' : ''; ?>>Pending Review</option>
                <option value="approved" <?php echo $filters['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                <option value="dispatched" <?php echo $filters['status'] === 'dispatched' ? 'selected' : ''; ?>>Dispatched</option>
                <option value="completed" <?php echo $filters['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                <option value="rejected" <?php echo $filters['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
            </select>
        </div>
        <div>
            <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Assigned Vendor</label>
            <select id="vendorFilter" class="block w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm font-semibold focus:ring-blue-500 focus:bg-white outline-none">
                <option value="">All Vendors</option>
                <?php foreach ($vendors as $vendor): ?>
                    <option value="<?php echo $vendor['id']; ?>" <?php echo $filters['vendor_id'] == $vendor['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($vendor['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Site Location</label>
            <select id="siteFilter" class="block w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm font-semibold focus:ring-blue-500 focus:bg-white outline-none">
                <option value="">All Sites</option>
                <?php foreach ($sites as $site): ?>
                    <option value="<?php echo $site['id']; ?>" <?php echo $filters['site_id'] == $site['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($site['site_id']); ?> - <?php echo htmlspecialchars($site['site_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="flex items-end">
            <button onclick="applyFilters()" class="w-full py-2 bg-gray-900 text-white rounded-lg text-xs font-bold uppercase tracking-widest hover:bg-black transition-colors flex items-center justify-center gap-2 shadow-lg shadow-gray-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 10.414V17a1 1 0 01-.293.707l-2 2A1 1 0 018 19v-8.586L3.293 6.707A1 1 0 013 6V3z"/></svg>
                Apply Analysis
            </button>
        </div>
    </div>
</div>

<!-- Material Requests Table -->
<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50/50">
                <tr>
                    <th class="px-6 py-4 text-left w-10">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this.checked)" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    </th>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-left w-12">#</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-left">Actions</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-left">Requisition ID</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-left">Site Reconcilation</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-left">Vendor Details</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-right">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php if (empty($requests)): ?>
                <tr>
                    <td colspan="7" class="text-center py-20 text-gray-400 font-bold italic">No requisitions match the current criteria</td>
                </tr>
                <?php else: ?>
                    <?php 
                    $sno = ($page - 1) * $limit + 1;
                    foreach ($requests as $request): 
                    ?>
                    <tr class="hover:bg-gray-50/50 transition-colors group">
                        <td class="px-6 py-4">
                            <input type="checkbox" class="request-cb w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" value="<?php echo $request['id']; ?>" onchange="updateBulkActionState()">
                        </td>
                        <td class="px-6 py-4 text-xs font-bold text-gray-400"><?php echo $sno++; ?></td>
                         <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <button onclick="viewRequest(<?php echo $request['id']; ?>)" class="w-8 h-8 rounded-lg border border-gray-200 bg-white flex items-center justify-center text-gray-400 hover:text-blue-600 hover:border-blue-200 transition-all shadow-sm" title="View Details">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                                <?php if ($request['status'] === 'pending'): ?>
                                    <button onclick="approveRequest(<?php echo $request['id']; ?>)" class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-600 hover:text-white transition-all shadow-sm" title="Approve">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                    <button onclick="rejectRequest(<?php echo $request['id']; ?>)" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-600 hover:text-white transition-all shadow-sm" title="Reject">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                <?php endif; ?>
                                <?php if ($request['status'] === 'approved'): ?>
                                    <button onclick="createDispatch(<?php echo $request['id']; ?>)" class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center hover:bg-indigo-600 hover:text-white transition-all shadow-sm" title="Dispatch">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div>
                                <div class="text-sm font-bold text-gray-900">REQ#<?php echo $request['id']; ?></div>
                                <div class="text-[11px] font-medium text-gray-400 uppercase mt-0.5">REQ DT: <?php echo date('d M Y', strtotime($request['request_date'])); ?></div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="max-w-[180px]">
                                <div class="text-sm font-bold text-gray-900 truncate"><?php echo htmlspecialchars($request['site_code']); ?></div>
                                <div class="text-[11px] font-medium text-gray-400 truncate mt-0.5"><?php echo htmlspecialchars($request['location']); ?></div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-gray-900"><?php echo htmlspecialchars($request['vendor_company_name'] ?? $request['vendor_name']); ?></div>
                            <div class="text-[11px] font-medium text-gray-400 uppercase mt-0.5">Required: <?php echo $request['required_date'] ? date('d M Y', strtotime($request['required_date'])) : 'ASAP'; ?></div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <?php
                            $statusMap = [
                                'draft' => ['color' => 'gray', 'label' => 'Draft Plan'],
                                'pending' => ['color' => 'amber', 'label' => 'Pending Review'],
                                'approved' => ['color' => 'emerald', 'label' => 'Ready for Dispatch'],
                                'dispatched' => ['color' => 'indigo', 'label' => 'In Transit'],
                                'completed' => ['color' => 'purple', 'label' => 'Delivery Finalized'],
                                'rejected' => ['color' => 'rose', 'label' => 'Denied']
                            ];
                            $st = $statusMap[$request['status']] ?? ['color' => 'gray', 'label' => $request['status']];
                            ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-<?php echo $st['color']; ?>-50 text-<?php echo $st['color']; ?>-700 border border-<?php echo $st['color']; ?>-100">
                                <?php echo $st['label']; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php if ($totalPages > 1): ?>
    <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-100 flex items-center justify-between">
        <div class="text-[11px] font-bold text-gray-400 uppercase">Page <?php echo $page; ?> of <?php echo $totalPages; ?></div>
        <div class="flex gap-1">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&status=<?php echo urlencode($filters['status']); ?>&vendor_id=<?php echo urlencode($filters['vendor_id']); ?>&site_id=<?php echo urlencode($filters['site_id']); ?>" 
                   class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-bold transition-all <?php echo $i === $page ? 'bg-gray-900 text-white shadow-lg' : 'bg-white border border-gray-200 text-gray-500 hover:border-gray-900 hover:text-gray-900'; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function applyFilters() {
    const status = document.getElementById('statusFilter').value;
    const vendorId = document.getElementById('vendorFilter').value;
    const siteId = document.getElementById('siteFilter').value;
    const url = new URL(window.location);
    if (status) url.searchParams.set('status', status); else url.searchParams.delete('status');
    if (vendorId) url.searchParams.set('vendor_id', vendorId); else url.searchParams.delete('vendor_id');
    if (siteId) url.searchParams.set('site_id', siteId); else url.searchParams.delete('site_id');
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

function viewRequest(id) { window.open(`view-request.php?id=${id}`, '_blank'); }

function approveRequest(id) {
    Swal.fire({ title: 'Confirm Approval', text: "Authorize this material requisition?", icon: 'question', showCancelButton: true, confirmButtonColor: '#10b981' }).then((r) => {
        if (r.isConfirmed) updateRequestStatus(id, 'approved');
    });
}

function rejectRequest(id) {
    Swal.fire({ title: 'Deny Request', text: "Provide a reason for rejection", input: 'textarea', icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444' }).then((r) => {
        if (r.isConfirmed) updateRequestStatus(id, 'rejected', r.value);
    });
}

function updateRequestStatus(id, status, reason = '') {
    fetch('update-request-status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ request_id: id, status: status, reason: reason })
    }).then(res => res.json()).then(data => {
        if (data.success) {
            Swal.fire({ icon: 'success', title: 'Task Executed', timer: 1500, showConfirmButton: false });
            setTimeout(() => location.reload(), 1500);
        }
    });
}

function createDispatch(requestId) {
    window.location.href = `dispatch-material.php?request_id=${requestId}`;
}

function exportRequests() {
    const params = new URLSearchParams(window.location.search);
    window.open(`export-requests.php?${params.toString()}`, '_blank');
}

function toggleSelectAll(checked) {
    document.querySelectorAll('.request-cb').forEach(cb => cb.checked = checked);
    updateBulkActionState();
}

function updateBulkActionState() {
    const checkedCount = document.querySelectorAll('.request-cb:checked').length;
    const bulkBar = document.getElementById('bulkActionBar');
    const selectAll = document.getElementById('selectAll');
    const totalCount = document.querySelectorAll('.request-cb').length;
    
    if (checkedCount > 0) { bulkBar.classList.remove('hidden'); bulkBar.classList.add('flex'); document.getElementById('selectedCount').textContent = `${checkedCount} Items Selected`; }
    else { bulkBar.classList.add('hidden'); bulkBar.classList.remove('flex'); }
    
    if (selectAll) { 
        selectAll.checked = checkedCount > 0 && checkedCount === totalCount;
        selectAll.indeterminate = checkedCount > 0 && checkedCount < totalCount;
    }
}

function bulkUpdateStatus(status) {
    const selectedIds = Array.from(document.querySelectorAll('.request-cb:checked')).map(cb => cb.value);
    if (selectedIds.length === 0) return;
    
    Swal.fire({ title: 'Batch Execution', text: `Apply ${status} to ${selectedIds.length} requisitions?`, icon: 'warning', showCancelButton: true }).then((r) => {
        if (r.isConfirmed) {
            fetch('bulk-update-status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ request_ids: selectedIds, status: status })
            }).then(res => res.json()).then(data => {
                if (data.success) { Swal.fire('Complete', data.message, 'success'); setTimeout(() => location.reload(), 1500); }
            });
        }
    });
}
</script>

<!-- Bulk Action Bar -->
<div id="bulkActionBar" class="fixed bottom-12 left-1/2 -translate-x-1/2 hidden bg-gray-900 text-white px-8 py-4 rounded-2xl shadow-2xl items-center gap-8 z-50 border border-gray-800 animate-bounce-short">
    <div class="flex items-center gap-3">
        <div class="p-2 bg-blue-600 rounded-lg">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
        <div>
            <div id="selectedCount" class="text-xs font-black uppercase tracking-widest">0 Items Selected</div>
            <div class="text-[10px] font-bold text-gray-500 uppercase tracking-tighter">Batch Requisition Control</div>
        </div>
    </div>
    
    <div class="h-8 w-px bg-gray-800"></div>
    
    <div class="flex gap-2">
        <button onclick="bulkUpdateStatus('approved')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-[10px] font-black uppercase tracking-widest transition-all">Authorize Batch</button>
        <button onclick="bulkUpdateStatus('rejected')" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-[10px] font-black uppercase tracking-widest transition-all">Deny Batch</button>
    </div>
    
    <button onclick="toggleSelectAll(false)" class="p-2 hover:bg-white/10 rounded-lg transition-colors ml-4">
        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
</div>

<style>
@keyframes bounce-short { 0%, 100% { transform: translate(-50%, 0); } 50% { transform: translate(-50%, -8px); } }
.animate-bounce-short { animation: bounce-short 3s infinite ease-in-out; }
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>