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
<div class="v-table-wrap mb-8 relative">
    <div id="loadingOverlay" class="v-table-loading">
        <div class="spinner"></div>
    </div>
    
    <div style="overflow-x:auto;">
        <table class="v-table">
            <thead>
                <tr>
                    <th style="width: 40px;">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this.checked)" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    </th>
                    <th style="width: 50px;">#</th>
                    <th style="width: 150px;">Actions</th>
                    <th>Requisition ID</th>
                    <th>Site Reconciliation</th>
                    <th>Vendor Details</th>
                    <th style="text-align: right;">Status</th>
                </tr>
            </thead>
            <tbody id="requestsTableBody">
                <tr>
                    <td colspan="7" style="padding: 40px; text-align: center; color: #94a3b8; font-weight: 500;">Initializing requisitions...</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div id="paginationContainer" class="v-pag hidden">
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
        tbody.innerHTML = `<tr><td colspan="7" style="padding: 48px; text-align: center; color: #94a3b8; font-weight: 500;">No requisitions match the current criteria.</td></tr>`;
        return;
    }
    
    const statusMap = {
        'draft': { c: '', l: 'Draft Plan' },
        'pending': { c: 'v-pill-warning', l: 'Pending Review' },
        'approved': { c: 'v-pill-active', l: 'Ready for Dispatch' },
        'dispatched': { c: 'v-pill-active', l: 'In Transit' },
        'completed': { c: 'v-pill-active', l: 'Delivery Finalized' },
        'rejected': { c: 'v-pill-critical', l: 'Denied' }
    };
    
    const sNoStart = (currentPage - 1) * limit + 1;
    
    tbody.innerHTML = requests.map((req, index) => {
        const st = statusMap[req.status] || { c: '', l: req.status };
        return `
            <tr>
                <td>
                    <input type="checkbox" class="request-cb w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" value="${req.id}" onchange="updateBulkActionState()">
                </td>
                <td><span class="v-row-num">${sNoStart + index}</span></td>
                <td>
                    <div style="display:flex; align-items:center; gap:6px;">
                        <button onclick="viewRequest(${req.id})" class="v-act-btn v-view" data-tip="View Details">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                        ${req.status === 'pending' ? `
                            <button onclick="approveRequest(${req.id})" style="color:#059669;background:#ecfdf5;border-radius:6px;width:28px;height:28px;display:flex;align-items:center;justify-content:center;transition:all 0.2s;" onmouseover="this.style.background='#10b981';this.style.color='#fff';" onmouseout="this.style.background='#ecfdf5';this.style.color='#059669';" title="Approve">
                                <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </button>
                            <button onclick="rejectRequest(${req.id})" style="color:#dc2626;background:#fef2f2;border-radius:6px;width:28px;height:28px;display:flex;align-items:center;justify-content:center;transition:all 0.2s;" onmouseover="this.style.background='#ef4444';this.style.color='#fff';" onmouseout="this.style.background='#fef2f2';this.style.color='#dc2626';" title="Reject">
                                <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        ` : ''}
                        ${req.status === 'approved' ? `
                            <button onclick="createDispatch(${req.id})" style="color:#4f46e5;background:#e0e7ff;border-radius:6px;width:28px;height:28px;display:flex;align-items:center;justify-content:center;transition:all 0.2s;" onmouseover="this.style.background='#6366f1';this.style.color='#fff';" onmouseout="this.style.background='#e0e7ff';this.style.color='#4f46e5';" title="Dispatch">
                                <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </button>
                        ` : ''}
                    </div>
                </td>
                <td>
                    <div class="v-name" style="font-size:14px;">REQ#${req.id}</div>
                    <div class="v-code" style="margin-top:4px;">REQ DT: ${formatDate(req.request_date)}</div>
                </td>
                <td>
                    <div style="font-weight:600; color:#334155; font-size:13px; max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${req.site_code || 'N/A'}</div>
                    <div style="font-size:11px; font-weight:600; color:#94a3b8; max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; margin-top:2px;">${req.location || 'N/A'}</div>
                </td>
                <td>
                    <div style="font-weight:600; color:#0f172a; font-size:13px;">${req.vendor_company_name || req.vendor_name || 'In-house'}</div>
                    <div style="font-size:10px; font-weight:700; color:#64748b; text-transform:uppercase; margin-top:2px;">Req: ${req.required_date ? formatDate(req.required_date) : 'ASAP'}</div>
                </td>
                <td style="text-align: right;">
                    <span class="v-pill ${st.c}" style="${!st.c ? 'background:#f1f5f9;color:#64748b;' : ''}">
                        ${st.l}
                    </span>
                </td>
            </tr>
        `;
    }).join('');
}

function renderStats(stats) {
    const statsContainer = document.getElementById('statsContainer');
    const statItems = [
        { label: 'Total Volume', value: stats.total, class: 'card-slate', ringColor: '#60a5fa', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' },
        { label: 'Awaiting Review', value: stats.pending, class: 'card-amber', ringColor: '#fbbf24', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' },
        { label: 'Approved Requests', value: stats.approved, class: 'card-green', ringColor: '#34d399', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
        { label: 'Active Dispatches', value: stats.dispatched, class: 'card-cyan', ringColor: '#22d3ee', icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4' }
    ];
    
    statsContainer.innerHTML = statItems.map(item => `
        <div class="stat-card ${item.class}">
            <div style="display:flex; align-items:flex-start; justify-content:space-between;">
                <div>
                    <div class="stat-value">${new Intl.NumberFormat().format(item.value)}</div>
                    <div class="stat-label">${item.label}</div>
                </div>
                <div class="stat-icon-ring">
                    <svg fill="none" stroke="${item.ringColor}" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="${item.icon}"/></svg>
                </div>
            </div>
            <div style="margin-top:16px; display:flex; align-items:center; gap:6px;">
                <div style="width:24px; height:3px; border-radius:2px; background:rgba(255,255,255,0.3);"></div>
                <span style="font-size:10px; font-weight:600; color:rgba(255,255,255,0.4);">Analysis</span>
            </div>
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
    let html = `
        <div class="v-pag-info">
            Showing Page <strong>${pagination.page}</strong> of <strong>${pagination.pages}</strong>
        </div>
        <div class="v-pag-nav">
    `;
    
    for (let i = 1; i <= pagination.pages; i++) {
        if (i === pagination.page) {
            html += `<button class="v-pag-btn active">${i}</button>`;
        } else {
            html += `<button onclick="changePage(${i})" class="v-pag-btn">${i}</button>`;
        }
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

/* ── Premium Request Styles ── */
.stat-card {
    position: relative;
    border-radius: 20px;
    padding: 24px 28px;
    color: #ffffff;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 140px;
}
.stat-card::before {
    content: '';
    position: absolute;
    top: -50px;
    right: -50px;
    width: 150px;
    height: 150px;
    border-radius: 50%;
    opacity: 0.08;
    transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}
.stat-card:hover { transform: translateY(-4px); }
.stat-card:hover::before { opacity: 0.14; transform: scale(1.2); }

.stat-card.card-slate { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); box-shadow: 0 8px 32px rgba(15, 23, 42, 0.25); }
.stat-card.card-slate::before { background: #3b82f6; }

.stat-card.card-cyan { background: linear-gradient(135deg, #164e63 0%, #0891b2 100%); box-shadow: 0 8px 32px rgba(8, 145, 178, 0.25); }
.stat-card.card-cyan::before { background: #22d3ee; }

.stat-card.card-amber { background: linear-gradient(135deg, #78350f 0%, #92400e 100%); box-shadow: 0 8px 32px rgba(146, 64, 14, 0.2); }
.stat-card.card-amber::before { background: #fbbf24; }

.stat-card.card-green { background: linear-gradient(135deg, #064e3b 0%, #065f46 100%); box-shadow: 0 8px 32px rgba(6, 95, 70, 0.2); }
.stat-card.card-green::before { background: #34d399; }

.stat-card.card-purple { background: linear-gradient(135deg, #2e1065 0%, #4c1d95 100%); box-shadow: 0 8px 32px rgba(76, 29, 149, 0.2); }
.stat-card.card-purple::before { background: #a78bfa; }

.stat-card.card-rose { background: linear-gradient(135deg, #881337 0%, #be123c 100%); box-shadow: 0 8px 32px rgba(190, 18, 60, 0.2); }
.stat-card.card-rose::before { background: #fb7185; }

.stat-value {
    font-size: 2.5rem;
    font-weight: 900;
    line-height: 1;
    color: #ffffff;
    font-variant-numeric: tabular-nums;
    letter-spacing: -0.03em;
}
.stat-label {
    font-size: 0.65rem;
    font-weight: 800;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.6);
    margin-top: 8px;
}
.stat-icon-ring {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(8px);
    transition: all 0.3s ease;
}
.stat-card:hover .stat-icon-ring {
    background: rgba(255, 255, 255, 0.14);
    transform: scale(1.08);
}
.stat-icon-ring svg { width: 22px; height: 22px; }

/* Table settings */
.v-table-wrap{background:#fff;border:1px solid #f1f5f9;border-radius:16px;overflow:hidden;position:relative;min-height:300px}
.v-table-loading{position:absolute;inset:0;background:rgba(255,255,255,.85);z-index:10;display:none;align-items:center;justify-content:center}
.v-table-loading.show{display:flex}
.v-table-loading .spinner{width:32px;height:32px;border:3px solid #e2e8f0;border-top-color:#6366f1;border-radius:50%;animation:spin .6s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
.v-table{width:100%;border-collapse:separate;border-spacing:0}
.v-table thead{background:linear-gradient(135deg,#f8fafc,#f1f5f9)}
.v-table th{padding:12px 16px;text-align:left;font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#64748b;border-bottom:1px solid #e2e8f0;white-space:nowrap}
.v-table td{padding:14px 16px;font-size:13px;color:#334155;border-bottom:1px solid #f8fafc;vertical-align:middle}
.v-table tbody tr{transition:all .15s ease}
.v-table tbody tr:hover{background:#fafbff}

.v-row-num{width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;background:#f1f5f9;border-radius:8px;font-size:11px;font-weight:700;color:#94a3b8}

.v-avatar{width:36px;height:36px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;flex-shrink:0;transition:transform .2s ease}
.v-table tbody tr:hover .v-avatar{transform:scale(1.1)}
.v-avatar-blue{background:#eff6ff;color:#3b82f6}

.v-name{font-weight:600;color:#0f172a;cursor:pointer;transition:color .15s}
.v-table tbody tr:hover .v-name{color:#4f46e5}
.v-code{font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.02em;margin-top:1px}

.v-pill{display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:100px;font-size:10px;font-weight:700;letter-spacing:.04em;text-transform:uppercase}
.v-pill-active{background:#ecfdf5;color:#059669}
.v-pill-warning{background:#fffbeb;color:#d97706}
.v-pill-critical{background:#fef2f2;color:#dc2626}

.v-act{display:flex;align-items:center;gap:5px;justify-content:flex-end}
.v-act-btn{width:30px;height:30px;display:inline-flex;align-items:center;justify-content:center;border-radius:9px;border:1px solid transparent;cursor:pointer;transition:all .2s ease;position:relative;background:transparent;padding:0}
.v-act-btn svg{width:14px;height:14px}
.v-act-btn.v-view{color:#94a3b8}
.v-act-btn.v-view:hover{background:#eff6ff;color:#3b82f6;border-color:#bfdbfe}
.v-act-btn[data-tip]:hover::after{content:attr(data-tip);position:absolute;bottom:calc(100% + 6px);left:50%;transform:translateX(-50%);padding:4px 8px;background:#0f172a;color:#fff;font-size:10px;font-weight:600;border-radius:6px;white-space:nowrap;z-index:10;pointer-events:none;animation:tipFade .15s ease}
.v-act-btn[data-tip]:hover::before{content:'';position:absolute;bottom:calc(100% + 2px);left:50%;transform:translateX(-50%);border:4px solid transparent;border-top-color:#0f172a;z-index:10}

.v-pag{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-top:1px solid #f1f5f9;flex-wrap:wrap;gap:12px;background:#fff;}
.v-pag-info{font-size:12px;font-weight:500;color:#64748b}
.v-pag-info strong{font-weight:700;color:#0f172a}
.v-pag-nav{display:flex;align-items:center;gap:4px}
.v-pag-btn{min-width:32px;height:32px;display:inline-flex;align-items:center;justify-content:center;border-radius:8px;border:1px solid #e2e8f0;background:#fff;font-size:12px;font-weight:600;color:#475569;cursor:pointer;transition:all .2s ease;text-decoration:none;padding:0 6px}
.v-pag-btn:hover{background:#f8fafc;border-color:#c7d2fe;color:#4f46e5}
.v-pag-btn.active{background:linear-gradient(135deg,#4f46e5,#6366f1);color:#fff;border-color:transparent;box-shadow:0 2px 6px rgba(99,102,241,.3)}
.v-pag-btn.disabled{opacity:.4;cursor:not-allowed;pointer-events:none}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>