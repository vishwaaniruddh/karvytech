<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/Site.php';
require_once __DIR__ . '/../../models/Vendor.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$siteModel = new Site();
$vendorModel = new Vendor();

// Get sites and vendors for filters
$sites = $siteModel->getAllSites();
$vendors = $vendorModel->getAllVendors();

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
<div id="statsContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <!-- Skeleton Loaders -->
    <?php for($i=0; $i<4; $i++): ?>
    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm animate-pulse">
        <div class="flex items-center justify-between mb-4">
            <div class="w-10 h-10 bg-gray-100 rounded-lg"></div>
            <div class="w-20 h-3 bg-gray-100 rounded"></div>
        </div>
        <div class="w-16 h-8 bg-gray-100 rounded"></div>
    </div>
    <?php endfor; ?>
</div>

<!-- Search and Filters -->
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-6">
    <form id="filterForm" class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Lifecycle Status</label>
            <select name="status" id="statusFilter" class="block w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm font-semibold focus:ring-blue-500 focus:bg-white outline-none">
                <option value="">All Status</option>
                <option value="draft">Draft</option>
                <option value="pending">Pending Review</option>
                <option value="approved">Approved</option>
                <option value="dispatched">Dispatched</option>
                <option value="completed">Completed</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
        <div>
            <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Assigned Vendor</label>
            <select name="vendor_id" id="vendorFilter" class="block w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm font-semibold focus:ring-blue-500 focus:bg-white outline-none">
                <option value="">All Vendors</option>
                <?php foreach ($vendors as $vendor): ?>
                    <option value="<?php echo $vendor['id']; ?>"><?php echo htmlspecialchars($vendor['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Site Location</label>
            <select name="site_id" id="siteFilter" class="block w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm font-semibold focus:ring-blue-500 focus:bg-white outline-none">
                <option value="">All Sites</option>
                <?php foreach ($sites as $site): ?>
                    <option value="<?php echo $site['id']; ?>"><?php echo htmlspecialchars($site['site_id']); ?> - <?php echo htmlspecialchars($site['site_name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Requisition ID</label>
            <input type="text" name="request_id" id="requestIdFilter" placeholder="Search REQ#" class="block w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm font-semibold focus:ring-blue-500 focus:bg-white outline-none">
        </div>
        <div class="flex items-end">
            <button type="button" onclick="loadRequests()" class="w-full py-2 bg-gray-900 text-white rounded-lg text-xs font-bold uppercase tracking-widest hover:bg-black transition-colors flex items-center justify-center gap-2 shadow-lg shadow-gray-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 10.414V17a1 1 0 01-.293.707l-2 2A1 1 0 018 19v-8.586L3.293 6.707A1 1 0 013 6V3z"/></svg>
                Apply Analysis
            </button>
        </div>
    </form>
</div>

<!-- Material Requests Table -->
<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden relative">
    <div id="loadingOverlay" class="absolute inset-0 bg-white/60 z-10 flex items-center justify-center hidden">
        <div class="animate-spin rounded-full h-8 w-8 border-4 border-blue-600 border-t-transparent"></div>
    </div>
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
            <tbody id="requestsTableBody" class="divide-y divide-gray-50 italic text-gray-400">
                <tr>
                    <td colspan="7" class="text-center py-20">Initializing requisitions...</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div id="paginationContainer" class="px-6 py-4 bg-gray-50/50 border-t border-gray-100 flex items-center justify-between hidden">
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let currentPage = 1;
const limit = 20;

document.addEventListener('DOMContentLoaded', () => {
    loadRequests();
    
    // Auto-reload on filter change
    document.querySelectorAll('#filterForm select').forEach(select => {
        select.addEventListener('change', () => { currentPage = 1; loadRequests(); });
    });
    
    let searchTimeout;
    document.getElementById('requestIdFilter').addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => { currentPage = 1; loadRequests(); }, 500);
    });
});

async function loadRequests() {
    const overlay = document.getElementById('loadingOverlay');
    overlay.classList.remove('hidden');
    
    const formData = new FormData(document.getElementById('filterForm'));
    const params = new URLSearchParams(formData);
    params.set('page', currentPage);
    params.set('limit', limit);
    
    try {
        const response = await fetch(`api/get-requests.php?${params.toString()}`);
        const result = await response.json();
        
        if (result.success) {
            renderTable(result.requests);
            renderStats(result.stats);
            renderPagination(result.pagination);
        } else {
            Swal.fire('Error', result.message, 'error');
        }
    } catch (error) {
        console.error('Fetch error:', error);
    } finally {
        overlay.classList.add('hidden');
    }
}

function renderTable(requests) {
    const tbody = document.getElementById('requestsTableBody');
    if (!requests || requests.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-20 text-gray-400 font-bold italic">No requisitions match the current criteria</td></tr>`;
        return;
    }
    
    const statusMap = {
        'draft': { color: 'gray', label: 'Draft Plan' },
        'pending': { color: 'amber', label: 'Pending Review' },
        'approved': { color: 'emerald', label: 'Ready for Dispatch' },
        'dispatched': { color: 'indigo', label: 'In Transit' },
        'completed': { color: 'purple', label: 'Delivery Finalized' },
        'rejected': { color: 'rose', label: 'Denied' }
    };
    
    const sNoStart = (currentPage - 1) * limit + 1;
    
    tbody.innerHTML = requests.map((req, index) => {
        const st = statusMap[req.status] || { color: 'gray', label: req.status };
        return `
            <tr class="hover:bg-gray-50/50 transition-colors group italic-none">
                <td class="px-6 py-4">
                    <input type="checkbox" class="request-cb w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" value="${req.id}" onchange="updateBulkActionState()">
                </td>
                <td class="px-6 py-4 text-xs font-bold text-gray-400">${sNoStart + index}</td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                        <button onclick="viewRequest(${req.id})" class="w-8 h-8 rounded-lg border border-gray-200 bg-white flex items-center justify-center text-gray-400 hover:text-blue-600 hover:border-blue-200 transition-all shadow-sm" title="View Details">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                        ${req.status === 'pending' ? `
                            <button onclick="approveRequest(${req.id})" class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-600 hover:text-white transition-all shadow-sm" title="Approve">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </button>
                            <button onclick="rejectRequest(${req.id})" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-600 hover:text-white transition-all shadow-sm" title="Reject">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        ` : ''}
                        ${req.status === 'approved' ? `
                            <button onclick="createDispatch(${req.id})" class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center hover:bg-indigo-600 hover:text-white transition-all shadow-sm" title="Dispatch">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </button>
                        ` : ''}
                    </div>
                </td>
                <td class="px-6 py-4">
                    <div>
                        <div class="text-sm font-bold text-gray-900">REQ#${req.id}</div>
                        <div class="text-[11px] font-medium text-gray-400 uppercase mt-0.5">REQ DT: ${formatDate(req.request_date)}</div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <div class="max-w-[180px]">
                        <div class="text-sm font-bold text-gray-900 truncate">${req.site_code || 'N/A'}</div>
                        <div class="text-[11px] font-medium text-gray-400 truncate mt-0.5">${req.location || 'N/A'}</div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm font-bold text-gray-900">${req.vendor_company_name || req.vendor_name || 'In-house'}</div>
                    <div class="text-[11px] font-medium text-gray-400 uppercase mt-0.5">Required: ${req.required_date ? formatDate(req.required_date) : 'ASAP'}</div>
                </td>
                <td class="px-6 py-4 text-right">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-${st.color}-50 text-${st.color}-700 border border-${st.color}-100">
                        ${st.label}
                    </span>
                </td>
            </tr>
        `;
    }).join('');
    
    // Remove italic from table body when data is loaded
    tbody.classList.remove('italic', 'text-gray-400');
}

function renderStats(stats) {
    const statsContainer = document.getElementById('statsContainer');
    const statItems = [
        { label: 'Total Volume', value: stats.total, color: 'blue', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' },
        { label: 'Awaiting Review', value: stats.pending, color: 'amber', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' },
        { label: 'Approved Requests', value: stats.approved, color: 'emerald', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
        { label: 'Active Dispatches', value: stats.dispatched, color: 'indigo', icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4' }
    ];
    
    statsContainer.innerHTML = statItems.map(item => `
        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 bg-${item.color}-50 rounded-lg flex items-center justify-center text-${item.color}-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${item.icon}"/></svg>
                </div>
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">${item.label}</span>
            </div>
            <div class="text-3xl font-bold text-gray-900">${new Intl.NumberFormat().format(item.value)}</div>
        </div>
    `).join('');
}

function renderPagination(pagination) {
    const container = document.getElementById('paginationContainer');
    if (pagination.pages <= 1) {
        container.classList.add('hidden');
        return;
    }
    
    container.classList.remove('hidden');
    let html = `<div class="text-[11px] font-bold text-gray-400 uppercase">Page ${pagination.page} of ${pagination.pages}</div>`;
    html += `<div class="flex gap-1">`;
    
    for (let i = 1; i <= pagination.pages; i++) {
        html += `
            <button onclick="changePage(${i})" 
               class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-bold transition-all ${i === pagination.page ? 'bg-gray-900 text-white shadow-lg' : 'bg-white border border-gray-200 text-gray-500 hover:border-gray-900 hover:text-gray-900'}">
                ${i}
            </button>
        `;
    }
    html += `</div>`;
    container.innerHTML = html;
}

function changePage(page) {
    currentPage = page;
    loadRequests();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function formatDate(dateStr) {
    if (!dateStr) return 'N/A';
    return new Date(dateStr).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
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
            loadRequests();
        }
    });
}

function createDispatch(requestId) {
    window.location.href = `dispatch-material.php?request_id=${requestId}`;
}

function exportRequests() {
    const formData = new FormData(document.getElementById('filterForm'));
    const params = new URLSearchParams(formData);
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
                if (data.success) { Swal.fire('Complete', data.message, 'success'); loadRequests(); updateBulkActionState(); }
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
.italic-none { font-style: normal !important; }
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>