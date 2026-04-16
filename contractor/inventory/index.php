<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../models/Inventory.php';

// Require vendor authentication
Auth::requireVendor();

$vendorId = Auth::getVendorId();
$inventoryModel = new Inventory();

// Get summary statistics (Server-side for initial load)
$totalDispatches = $inventoryModel->getContractorDispatchCount($vendorId);
$totalItems = $inventoryModel->getContractorTotalItems($vendorId);
$pendingConfirmations = $inventoryModel->getContractorPendingConfirmations($vendorId);

// Get accepted count
require_once __DIR__ . '/../../config/database.php';
$db = Database::getInstance()->getConnection();
$acceptedCount = $db->query("SELECT COUNT(*) FROM inventory_dispatches WHERE vendor_id = $vendorId AND dispatch_status = 'confirmed'")->fetchColumn();

$title = 'Material Received from Admin';
ob_start();
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Material Receipts</h1>
            <p class="mt-1 text-sm text-gray-500 font-medium">Manage and audit all materials dispatched to your organization from Karvy Admin.</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="exportToExcel()" class="inline-flex items-center px-4 py-2 bg-emerald-50 text-emerald-700 text-sm font-bold rounded-xl border border-emerald-100 hover:bg-emerald-100 transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Export Excel
            </button>
            <a href="../dashboard.php" class="inline-flex items-center px-4 py-2 bg-white text-gray-700 text-sm font-bold rounded-xl border border-gray-200 hover:bg-gray-50 transition-all duration-200 shadow-sm">
                Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m13-8V4a1 1 0 00-1-1H7a1 1 0 00-1 1v1m8 0V4a1 1 0 00-1-1H9a1 1 0 00-1 1v1"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Total Receipts</p>
                    <p class="text-2xl font-black text-gray-900 mt-0.5"><?php echo $totalDispatches; ?></p>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-green-50 text-green-600 rounded-2xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Confirmed</p>
                    <p class="text-2xl font-black text-gray-900 mt-0.5"><?php echo $acceptedCount; ?></p>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Pending Action</p>
                    <p class="text-2xl font-black text-gray-900 mt-0.5"><?php echo $pendingConfirmations; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm p-6 mb-10">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] mb-2">Search Dispatch</label>
                <div class="relative">
                    <input type="text" id="searchInput" placeholder="Dispatch # or Site ID..." class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 transition-all font-semibold">
                    <svg class="w-4 h-4 absolute left-3.5 top-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] mb-2">Workflow Status</label>
                <select id="statusFilter" class="w-full px-4 py-2.5 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 transition-all font-semibold">
                    <option value="">All Statuses</option>
                    <option value="dispatched">Dispatched</option>
                    <option value="in_transit">In Transit</option>
                    <option value="delivered">Delivered</option>
                    <option value="confirmed">Confirmed</option>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] mb-2">Date From</label>
                <input type="date" id="dateFrom" class="w-full px-4 py-2.5 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 transition-all font-semibold">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] mb-2">Date To</label>
                <input type="date" id="dateTo" class="w-full px-4 py-2.5 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 transition-all font-semibold">
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden mb-8">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50">
                        <th class="px-6 py-4 text-[10px] font-extrabold text-gray-400 uppercase tracking-[0.2em]">#</th>
                        <th class="px-6 py-4 text-[10px] font-extrabold text-gray-400 uppercase tracking-[0.2em]">Dispatch Details</th>
                        <th class="px-6 py-4 text-[10px] font-extrabold text-gray-400 uppercase tracking-[0.2em]">Deployment Site</th>
                        <th class="px-6 py-4 text-[10px] font-extrabold text-gray-400 uppercase tracking-[0.2em]">Logistics Info</th>
                        <th class="px-6 py-4 text-[10px] font-extrabold text-gray-400 uppercase tracking-[0.2em]">Status</th>
                        <th class="px-6 py-4 text-[10px] font-extrabold text-gray-400 uppercase tracking-[0.2em] text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="materialsTableBody" class="divide-y divide-gray-50">
                    <!-- Loaded via JS -->
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-8 py-6 bg-gray-50/50 flex flex-col md:flex-row items-center justify-between gap-4 border-t border-gray-100">
            <div class="text-xs font-bold text-gray-400 uppercase tracking-widest">
                Showing <span id="showingStart">0</span> - <span id="showingEnd">0</span> of <span id="totalResults">0</span> Receipts
            </div>
            <div class="flex items-center gap-2" id="paginationControls">
                <!-- Loaded via JS -->
            </div>
        </div>
    </div>
</div>

<template id="materialRowTemplate">
    <tr class="group hover:bg-blue-50/30 transition-all duration-200">
        <td class="px-6 py-5 text-xs font-black text-gray-300">#SERIAL#</td>
        <td class="px-6 py-5">
            <div class="flex flex-col">
                <span class="text-sm font-black text-gray-900 uppercase tracking-tight">#DISPATCH_NUMBER#</span>
                <span class="text-[10px] font-bold text-gray-400 mt-0.5 uppercase tracking-widest">#DATE#</span>
            </div>
        </td>
        <td class="px-6 py-5">
            <div class="flex flex-col">
                <span class="text-sm font-bold text-gray-800 uppercase tracking-tight">#SITE_CODE#</span>
                <span class="text-[10px] font-bold text-gray-400 truncate max-w-[200px] uppercase tracking-widest">#LOCATION#</span>
            </div>
        </td>
        <td class="px-6 py-5">
             <div class="flex flex-col gap-1">
                 <div class="flex items-center gap-2">
                     <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Courier:</span>
                     <span class="text-[10px] font-bold text-gray-700">#COURIER#</span>
                 </div>
                 <div class="flex items-center gap-2">
                     <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Tracking/LR:</span>
                     <span class="text-[10px] font-bold text-gray-700">#TRACKING#</span>
                 </div>
             </div>
        </td>
        <td class="px-6 py-5">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest #STATUS_BG# #STATUS_TEXT# border #STATUS_BORDER#">
                #STATUS_LABEL#
            </span>
        </td>
        <td class="px-6 py-5">
            <div class="flex items-center justify-center gap-2">
                <a href="../material-received-from-admin.php?id=#ID#" class="p-2 text-blue-500 hover:bg-blue-50 rounded-lg transition-colors border border-transparent hover:border-blue-100" title="View Details">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                </a>
            </div>
        </td>
    </tr>
</template>

<script>
let currentPage = 1;
const limit = 10;

function fetchMaterials(page = 1) {
    currentPage = page;
    const search = document.getElementById('searchInput').value;
    const status = document.getElementById('statusFilter').value;
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    
    const url = `api/get-received-materials.php?page=${page}&limit=${limit}&search=${encodeURIComponent(search)}&status=${status}&date_from=${dateFrom}&date_to=${dateTo}`;
    
    // Show skeleton or loading state
    const tbody = document.getElementById('materialsTableBody');
    tbody.innerHTML = '<tr><td colspan="6" class="py-20 text-center"><div class="flex flex-col items-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mb-4"></div><span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Syncing Receipts...</span></div></td></tr>';

    fetch(url)
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                renderTable(res.data, res.pagination);
                renderPagination(res.pagination);
            }
        });
}

function renderTable(data, pagination) {
    const tbody = document.getElementById('materialsTableBody');
    const template = document.getElementById('materialRowTemplate').innerHTML;
    
    if (data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="py-20 text-center text-gray-400 font-bold uppercase tracking-widest text-xs">No matching receipts found in archives</td></tr>';
        return;
    }

    let html = '';
    const statusMap = {
        'dispatched': {bg: 'bg-orange-50', text: 'text-orange-600', border: 'border-orange-100', label: 'Dispatched'},
        'in_transit': {bg: 'bg-blue-50', text: 'text-blue-600', border: 'border-blue-100', label: 'In Transit'},
        'delivered': {bg: 'bg-indigo-50', text: 'text-indigo-600', border: 'border-indigo-100', label: 'Delivered'},
        'confirmed': {bg: 'bg-green-50', text: 'text-green-600', border: 'border-green-100', label: 'Confirmed'},
    };

    data.forEach((row, index) => {
        const serial = ((pagination.page - 1) * pagination.limit) + index + 1;
        const status = statusMap[row.dispatch_status] || statusMap['dispatched'];
        
        let rowHtml = template
            .replace('#SERIAL#', serial.toString().padStart(2, '0'))
            .replace('#DISPATCH_NUMBER#', row.dispatch_number)
            .replace('#DATE#', row.dispatch_date ? new Date(row.dispatch_date).toLocaleDateString('en-GB', {day: '2-digit', month: 'short', year: 'numeric'}) : 'N/A')
            .replace('#SITE_CODE#', row.site_code || 'GNR-MASTER')
            .replace('#LOCATION#', row.site_location || 'General Warehouse')
            .replace('#COURIER#', row.courier_name || 'Direct Delivery')
            .replace('#TRACKING#', row.tracking_number || 'N/A')
            .replace('#STATUS_BG#', status.bg)
            .replace('#STATUS_TEXT#', status.text)
            .replace('#STATUS_BORDER#', status.border)
            .replace('#STATUS_LABEL#', status.label)
            .replace('#ID#', row.id);
            
        html += rowHtml;
    });

    tbody.innerHTML = html;
    
    document.getElementById('showingStart').innerText = ((pagination.page - 1) * pagination.limit) + 1;
    document.getElementById('showingEnd').innerText = Math.min(pagination.page * pagination.limit, pagination.total);
    document.getElementById('totalResults').innerText = pagination.total;
}

function renderPagination(pagination) {
    const container = document.getElementById('paginationControls');
    let html = '';
    
    const prevDisabled = pagination.page === 1 ? 'opacity-30 pointer-events-none' : '';
    html += `<button onclick="fetchMaterials(${pagination.page - 1})" class="p-2 bg-white border border-gray-100 rounded-lg shadow-sm hover:bg-gray-50 transition-colors ${prevDisabled}"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg></button>`;
    
    for (let i = 1; i <= pagination.pages; i++) {
        const active = i === pagination.page ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-500 border-gray-100 hover:bg-gray-50';
        html += `<button onclick="fetchMaterials(${i})" class="w-8 h-8 text-[10px] font-bold rounded-lg border transition-all duration-200 ${active}">${i}</button>`;
    }
    
    const nextDisabled = pagination.page === pagination.pages ? 'opacity-30 pointer-events-none' : '';
    html += `<button onclick="fetchMaterials(${pagination.page + 1})" class="p-2 bg-white border border-gray-100 rounded-lg shadow-sm hover:bg-gray-50 transition-colors ${nextDisabled}"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg></button>`;
    
    container.innerHTML = html;
}

function exportToExcel() {
    const search = document.getElementById('searchInput').value;
    const status = document.getElementById('statusFilter').value;
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    
    const url = `api/export-received-materials.php?search=${encodeURIComponent(search)}&status=${status}&date_from=${dateFrom}&date_to=${dateTo}`;
    window.location.href = url;
}

// Event Listeners
document.getElementById('searchInput').addEventListener('input', () => fetchMaterials(1));
document.getElementById('statusFilter').addEventListener('change', () => fetchMaterials(1));
document.getElementById('dateFrom').addEventListener('change', () => fetchMaterials(1));
document.getElementById('dateTo').addEventListener('change', () => fetchMaterials(1));

// Initial Load
fetchMaterials(1);
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/vendor_layout.php';
?>