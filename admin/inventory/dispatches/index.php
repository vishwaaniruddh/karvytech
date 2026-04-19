<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../models/Site.php';
require_once __DIR__ . '/../../../models/Vendor.php';
require_once __DIR__ . '/../../../models/MaterialRequest.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$siteModel = new Site();
$vendorModel = new Vendor();
$mrModel = new MaterialRequest();

$sites = $siteModel->getAllSites();
$vendors = $vendorModel->getAllVendors();

// Pre-fill logic if request_id is present
$requestId = $_GET['request_id'] ?? null;
$prefillData = null;
if ($requestId) {
    $prefillData = $mrModel->find($requestId);
}

$title = 'Material Dispatches';
ob_start();
?>

<!-- Header Section -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Material Dispatches</h1>
        <p class="text-[13px] font-medium text-gray-500 mt-1 uppercase tracking-wide">Logistics & Supply Chain Manifest</p>
    </div>
    <div class="flex items-center gap-3">
        <button onclick="exportDispatches()" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 rounded-xl text-xs font-bold uppercase tracking-wider transition-all shadow-sm flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Export
        </button>
        <button onclick="openCreateModal()" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold uppercase tracking-wider transition-all shadow-lg shadow-blue-200 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Create Dispatch
        </button>
        <a href="../" class="p-2.5 bg-white border border-gray-200 text-gray-400 hover:text-gray-900 hover:border-gray-900 rounded-xl transition-all shadow-sm" title="Back to Inventory">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
        </a>
    </div>
</div>

<!-- Stats Grid -->
<div id="statsGrid" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <!-- Stats dynamically injected here -->
    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm animate-pulse h-32"></div>
    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm animate-pulse h-32"></div>
    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm animate-pulse h-32"></div>
    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm animate-pulse h-32"></div>
</div>

<!-- Refined Filters -->
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 mb-8">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-5">
        <div class="md:col-span-4 relative">
            <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Search Dispatch / Courier</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" id="searchInput" class="block w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-transparent rounded-xl text-sm font-semibold focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition-all" placeholder="Dispatch #, tracking, etc..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
            </div>
        </div>
        <div class="md:col-span-2">
            <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Req ID</label>
            <input type="text" id="requestIdFilter" class="block w-full px-4 py-2.5 bg-gray-50 border border-transparent rounded-xl text-sm font-semibold focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition-all" placeholder="Request #" value="<?php echo htmlspecialchars($_GET['request_id'] ?? ''); ?>">
        </div>
        <div class="md:col-span-2">
            <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Transit Status</label>
            <select id="statusFilter" class="block w-full px-4 py-2.5 bg-gray-50 border border-transparent rounded-xl text-sm font-semibold focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition-all appearance-none cursor-pointer">
                <option value="">All Statuses</option>
                <option value="prepared">Prepared</option>
                <option value="dispatched">Dispatched</option>
                <option value="in_transit">In Transit</option>
                <option value="delivered">Delivered</option>
                <option value="returned">Returned</option>
            </select>
        </div>
        <div class="md:col-span-3">
            <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Destination Site</label>
            <select id="siteFilter" class="block w-full px-4 py-2.5 bg-gray-50 border border-transparent rounded-xl text-sm font-semibold focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition-all appearance-none cursor-pointer">
                <option value="">All Sites</option>
                <?php foreach ($sites as $site): ?>
                    <option value="<?php echo $site['id']; ?>"><?php echo htmlspecialchars($site['site_id']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="md:col-span-1 flex items-end">
            <button onclick="fetchDispatches(1)" class="w-full py-2.5 bg-gray-900 text-white rounded-xl flex items-center justify-center hover:bg-black transition-all shadow-md">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
            </button>
        </div>
    </div>
</div>

<!-- Dispatches Table Content -->
<div class="v-table-wrap mb-8 relative">
    <div id="loadingOverlay" class="v-table-loading">
        <div class="spinner"></div>
    </div>
    <div style="overflow-x:auto;">
        <table class="v-table">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th style="width: 140px;">Actions</th>
                    <th>Manifest ID</th>
                    <th>Request</th>
                    <th>Destination Context</th>
                    <th>Timeline</th>
                    <th style="text-align: right;">Value</th>
                    <th style="text-align: right;">Status</th>
                </tr>
            </thead>
            <tbody id="dispatchesTableBody">
                <!-- Data injected by JS -->
            </tbody>
        </table>
    </div>
    <div id="pagination" class="v-pag hidden">
        <!-- Pagination injected by JS -->
    </div>
</div>

<!-- Create Dispatch Modal -->
<div id="createDispatchModal" class="modal">
    <div class="modal-content max-w-4xl rounded-2xl shadow-2xl overflow-hidden border-0">
        <div class="modal-header-fixed bg-gray-900 text-white rounded-t-2xl">
            <div class="flex flex-col">
                <h3 class="modal-title !text-white">Create Dispatch Manifest</h3>
                <p class="text-[10px] opacity-60 font-medium uppercase tracking-widest mt-0.5">Inventory Outbound Logic</p>
            </div>
            <button type="button" class="modal-close !text-white hover:text-gray-300" onclick="closeModal('createDispatchModal')">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <form id="createDispatchForm">
            <div class="modal-body p-6">
                <!-- Basic Info -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="form-group">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Manifest #</label>
                        <input type="text" id="dispatch_number" name="dispatch_number" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-xl text-sm font-bold text-gray-900 outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-not-allowed" required readonly>
                    </div>
                    <div class="form-group">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Dispatch Date</label>
                        <input type="date" name="dispatch_date" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500 transition-all" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Linked Request ID</label>
                        <input type="number" id="material_request_id" name="material_request_id" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500 transition-all" value="<?php echo $requestId ?: ''; ?>" placeholder="Optional Request #">
                    </div>
                </div>

                <!-- Destination -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="form-group">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Target Site</label>
                        <select id="site_id" name="site_id" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-bold outline-none focus:ring-2 focus:ring-blue-500 transition-all" required onchange="updatePrefillAddress()">
                            <option value="">Select Operational Hub</option>
                            <?php foreach ($sites as $site): ?>
                                <option value="<?php echo $site['id']; ?>" data-address="<?php echo htmlspecialchars($site['address'] ?: ''); ?>" <?php echo ($prefillData && $prefillData['site_id'] == $site['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($site['site_id']); ?> - <?php echo htmlspecialchars($site['site_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Assigned Vendor</label>
                        <select name="vendor_id" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-bold outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                            <option value="">Select Vendor Context</option>
                            <?php foreach ($vendors as $vendor): ?>
                                <option value="<?php echo $vendor['id']; ?>" <?php echo ($prefillData && $prefillData['vendor_id'] == $vendor['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($vendor['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="form-group">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Contact Name</label>
                        <input type="text" name="contact_person_name" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500 transition-all" required placeholder="Person receiving materials">
                    </div>
                    <div class="form-group">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Contact Phone</label>
                        <input type="text" name="contact_person_phone" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500 transition-all" placeholder="Receiving contact number">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Delivery Milestone Address</label>
                        <textarea id="delivery_address" name="delivery_address" rows="2" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500 transition-all" required><?php echo $prefillData ? htmlspecialchars($prefillData['location'] ?? '') : ''; ?></textarea>
                    </div>
                </div>

                <!-- Logistics -->
                <div class="bg-gray-50 rounded-2xl p-5 mb-8 border border-gray-100 grid grid-cols-1 md:grid-cols-4 gap-4">
                     <div class="form-group">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Courier</label>
                        <input type="text" name="courier_name" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-semibold outline-none">
                    </div>
                    <div class="form-group">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Tracking #</label>
                        <input type="text" name="tracking_number" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-semibold outline-none">
                    </div>
                    <div class="form-group">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Status</label>
                        <select name="dispatch_status" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-bold outline-none">
                            <option value="prepared">Prepared</option>
                            <option value="dispatched">Dispatched</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Expected ETA</label>
                        <input type="date" name="expected_delivery_date" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-semibold">
                    </div>
                </div>

                <!-- Manifest Items -->
                <div class="border-t border-gray-100 pt-6">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Manifest Payload</h4>
                            <p class="text-[10px] text-gray-400 font-medium tracking-wider">Itemized shipment contents</p>
                        </div>
                        <button type="button" onclick="addDispatchItem()" class="px-4 py-2 bg-gray-900 text-white rounded-xl text-[11px] font-bold uppercase tracking-wider hover:bg-black transition-all flex items-center gap-2">
                             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                             Add Item
                        </button>
                    </div>
                    <div id="dispatchItems" class="space-y-4">
                        <!-- Items dynamically added -->
                    </div>
                </div>
            </div>
            <div class="modal-footer-fixed bg-gray-50 border-t border-gray-100 p-6 flex justify-end gap-3">
                <button type="button" onclick="closeModal('createDispatchModal')" class="px-6 py-2.5 border border-gray-200 text-gray-500 font-bold text-[11px] uppercase tracking-widest rounded-xl hover:bg-gray-100 transition-all">Cancel</button>
                <button type="submit" class="px-10 py-2.5 bg-blue-600 text-white font-bold text-[11px] uppercase tracking-widest rounded-xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-100">Confirm Dispatch</button>
            </div>
        </form>
    </div>
</div>

<!-- Update Status Modal -->
<div id="updateStatusModal" class="modal">
    <div class="modal-content max-w-lg rounded-2xl shadow-2xl border-0 overflow-hidden">
        <div class="modal-header-fixed bg-emerald-600 text-white rounded-t-2xl">
            <div class="flex flex-col">
                <h3 class="modal-title !text-white">Route Milestone Update</h3>
                <p class="text-[10px] opacity-70 font-medium uppercase tracking-widest mt-0.5" id="updateTargetLabel">---</p>
            </div>
            <button type="button" class="modal-close !text-white" onclick="closeModal('updateStatusModal')">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
            </button>
        </div>
        <form id="updateStatusForm">
            <div class="modal-body p-6">
                <input type="hidden" id="updateDispatchId" name="dispatch_id">
                <div class="space-y-5">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Transit Lifecycle Stage</label>
                        <select id="newStatus" name="new_status" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-bold outline-none focus:ring-2 focus:ring-emerald-500 transition-all" required onchange="toggleStatusFields(this.value)">
                            <option value="prepared">Prepared</option>
                            <option value="dispatched">Dispatched</option>
                            <option value="in_transit">In Transit</option>
                            <option value="delivered">Delivered</option>
                            <option value="returned">Returned</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Tracking Manifest #</label>
                        <input type="text" id="trackingNumber" name="tracking_number" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-semibold outline-none">
                    </div>
                    <div id="deliveryDateField" style="display: none;">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Proof of Delivery Date</label>
                        <input type="date" id="actualDeliveryDate" name="actual_delivery_date" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-semibold">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Internal Remarks</label>
                        <textarea name="status_remarks" rows="3" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-semibold outline-none" placeholder="Operational notes for this update..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer p-6 border-t border-gray-50 flex justify-end gap-3">
                 <button type="button" onclick="closeModal('updateStatusModal')" class="px-6 py-2.5 text-gray-400 font-bold text-[11px] uppercase tracking-widest">Cancel</button>
                 <button type="submit" class="px-10 py-2.5 bg-emerald-600 text-white font-bold text-[11px] uppercase tracking-widest rounded-xl hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-100">Update Shipment</button>
            </div>
        </form>
    </div>
</div>

<script>
let currentPage = 1;
const statusMap = {
    'prepared': { color: 'blue', label: 'Awaiting Pickup' },
    'dispatched': { color: 'amber', label: 'Sent to Courier' },
    'in_transit': { color: 'indigo', label: 'In Transit' },
    'delivered': { color: 'emerald', label: 'Delivered' },
    'returned': { color: 'rose', label: 'Returned' }
};

document.addEventListener('DOMContentLoaded', () => {
    fetchDispatches(1);
    
    // Auto-search logic
    ['searchInput', 'requestIdFilter'].forEach(id => {
        document.getElementById(id).addEventListener('keyup', debounce(() => fetchDispatches(1), 500));
    });
    
    ['statusFilter', 'siteFilter'].forEach(id => {
        document.getElementById(id).addEventListener('change', () => fetchDispatches(1));
    });
});

async function fetchDispatches(page = 1) {
    currentPage = page;
    const body = document.getElementById('dispatchesTableBody');
    const search = document.getElementById('searchInput').value;
    const requestId = document.getElementById('requestIdFilter').value;
    const status = document.getElementById('statusFilter').value;
    const siteId = document.getElementById('siteFilter').value;

    body.innerHTML = `<tr><td colspan="8" class="text-center py-20"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900 mx-auto"></div></td></tr>`;

    try {
        const response = await fetch(`api/get-dispatches.php?page=${page}&search=${encodeURIComponent(search)}&request_id=${requestId}&status=${status}&site_id=${siteId}`);
        const result = await response.json();

        if (result.success) {
            renderTable(result.data, result.pagination);
            renderStats(result.stats);
        } else {
            body.innerHTML = `<tr><td colspan="8" class="text-center py-20 text-red-500 font-bold">${result.message}</td></tr>`;
        }
    } catch (e) {
        body.innerHTML = `<tr><td colspan="8" class="text-center py-20 text-red-500 font-bold">Failed to connect to manifest server</td></tr>`;
    }
}

function renderTable(data, pagination) {
    const body = document.getElementById('dispatchesTableBody');
    if (data.length === 0) {
        body.innerHTML = `<tr><td colspan="8" style="padding: 48px; text-align: center; color: #94a3b8; font-weight: 500;">No outbound manifests match your parameters.</td></tr>`;
        renderPagination(pagination);
        return;
    }

    const pillMap = {
        'prepared': { c: 'v-pill-warning', l: 'Awaiting Pickup' },
        'dispatched': { c: 'v-pill-active', l: 'Sent to Courier' },
        'in_transit': { c: 'v-pill-active', l: 'In Transit' },
        'delivered': { c: 'v-pill-active', l: 'Delivered' },
        'returned': { c: 'v-pill-critical', l: 'Returned' }
    };

    body.innerHTML = data.map((d, i) => {
        const sno = (pagination.page - 1) * pagination.limit + i + 1;
        const st = pillMap[d.dispatch_status] || { c: '', l: d.dispatch_status };
        const date = new Date(d.dispatch_date);
        
        return `
            <tr>
                <td><span class="v-row-num">${sno}</span></td>
                <td>
                    <div style="display:flex; align-items:center; gap:6px;">
                        <button onclick="viewDispatch(${d.id})" class="v-act-btn v-view" data-tip="View Manifest">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                        ${d.dispatch_status !== 'delivered' ? `
                            <button onclick="updateDispatchStatus(${d.id})" style="color:#059669;background:#ecfdf5;border-radius:6px;width:28px;height:28px;display:flex;align-items:center;justify-content:center;transition:all 0.2s;" onmouseover="this.style.background='#10b981';this.style.color='#fff';" onmouseout="this.style.background='#ecfdf5';this.style.color='#059669';" title="Update Milestone">
                                <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </button>
                        ` : ''}
                        <button onclick="viewChallan(${d.id})" style="color:#4f46e5;background:#e0e7ff;border-radius:6px;width:28px;height:28px;display:flex;align-items:center;justify-content:center;transition:all 0.2s;" onmouseover="this.style.background='#6366f1';this.style.color='#fff';" onmouseout="this.style.background='#e0e7ff';this.style.color='#4f46e5';" title="Print Delivery Challan">
                            <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </button>
                    </div>
                </td>
                <td>
                    <div class="v-name" style="font-size:14px;">${d.dispatch_number}</div>
                    <div class="v-code" style="margin-top:4px;">${d.courier_name || 'Hand Carry/Internal'}</div>
                    ${d.tracking_number ? `
                        <div style="margin-top:4px;display:inline-flex;align-items:center;padding:2px 6px;background:#eff6ff;color:#2563eb;font-size:10px;font-weight:700;border-radius:4px;letter-spacing:0.04em;">
                            TRACK: ${d.tracking_number}
                        </div>
                    ` : ''}
                </td>
                <td>
                    ${d.material_request_id ? `
                        <div style="font-size:11px;font-weight:800;color:#64748b;background:#f1f5f9;display:inline-flex;padding:4px 8px;border-radius:6px;">REQ-${String(d.material_request_id).padStart(6, '0')}</div>
                    ` : `<span style="font-size:11px;font-weight:600;color:#cbd5e1;font-style:italic;">N/A</span>`}
                </td>
                <td>
                    <div style="font-weight:600; color:#334155; font-size:13px; max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${d.site_code}</div>
                    ${d.vendor_company_name ? `<div style="font-size:10px; font-weight:700; color:#6366f1; text-transform:uppercase; margin-top:2px;">${d.vendor_company_name}</div>` : ''}
                    <div style="font-size:11px; font-weight:600; color:#94a3b8; max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; margin-top:2px;">${d.delivery_address || ''}</div>
                </td>
                <td>
                    <div style="font-weight:600; color:#0f172a; font-size:13px;">${date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })}</div>
                    <div style="font-size:10px; font-weight:700; color:#64748b; margin-top:2px;">${date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}</div>
                </td>
                <td style="text-align: right;">
                    <div style="font-weight:600; color:#334155; font-size:13px;">${d.total_items} Item(s)</div>
                    <div style="font-size:11px; font-weight:700; color:#059669; margin-top:2px;">₹${parseFloat(d.total_value).toLocaleString('en-IN', { minimumFractionDigits: 2 })}</div>
                </td>
                <td style="text-align: right;">
                    <span class="v-pill ${st.c}" style="${!st.c ? 'background:#f1f5f9;color:#64748b;' : ''}">
                        ${st.l}
                    </span>
                </td>
            </tr>
        `;
    }).join('');

    renderPagination(pagination);
}

function renderStats(stats) {
    const grid = document.getElementById('statsGrid');
    const items = [
        { label: 'Total Manifests', val: stats.total, class: 'card-slate', ringColor: '#60a5fa', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' },
        { label: 'In Transit', val: stats.in_transit, class: 'card-cyan', ringColor: '#22d3ee', icon: 'M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0' },
        { label: 'Delivered', val: stats.delivered, class: 'card-green', ringColor: '#34d399', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
        { label: 'Awaiting Pickup', val: stats.pending, class: 'card-amber', ringColor: '#fbbf24', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' }
    ];

    grid.innerHTML = items.map(s => `
        <div class="stat-card ${s.class}">
            <div style="display:flex; align-items:flex-start; justify-content:space-between;">
                <div>
                    <div class="stat-value">${new Intl.NumberFormat().format(s.val)}</div>
                    <div class="stat-label">${s.label}</div>
                </div>
                <div class="stat-icon-ring">
                    <svg fill="none" stroke="${s.ringColor}" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="${s.icon}"/></svg>
                </div>
            </div>
            <div style="margin-top:16px; display:flex; align-items:center; gap:6px;">
                <div style="width:24px; height:3px; border-radius:2px; background:rgba(255,255,255,0.3);"></div>
                <span style="font-size:10px; font-weight:600; color:rgba(255,255,255,0.4);">Milestones</span>
            </div>
        </div>
    `).join('');
}

function renderPagination(p) {
    const el = document.getElementById('pagination');
    if (p.pages <= 1) {
        el.classList.add('hidden');
        return;
    }

    el.classList.remove('hidden');
    let buttons = '';
    for (let i = 1; i <= p.pages; i++) {
        if (i === p.page) {
            buttons += `<button class="v-pag-btn active">${i}</button>`;
        } else {
            buttons += `<button onclick="fetchDispatches(${i})" class="v-pag-btn">${i}</button>`;
        }
    }

    el.innerHTML = `
        <div class="v-pag-info">
            Showing Page <strong>${p.page}</strong> of <strong>${p.pages}</strong>
        </div>
        <div class="v-pag-nav">${buttons}</div>
    `;
}

// Modal Handlers
function openCreateModal() {
    generateDispatchNumber();
    openModal('createDispatchModal');
}

function generateDispatchNumber() {
    fetch('generate-dispatch-number.php')
        .then(r => r.json())
        .then(d => { if(d.success) document.getElementById('dispatch_number').value = d.dispatch_number; });
}

function updatePrefillAddress() {
    const select = document.getElementById('site_id');
    const opt = select.options[select.selectedIndex];
    const addr = opt.getAttribute('data-address');
    if(addr) document.getElementById('delivery_address').value = addr;
}

// Dispatch Items Logic
let itemIndex = 0;
function addDispatchItem() {
    itemIndex++;
    const container = document.getElementById('dispatchItems');
    const div = document.createElement('div');
    div.className = 'p-5 bg-white border border-gray-100 rounded-2xl shadow-sm relative group';
    div.id = `item-${itemIndex}`;
    div.innerHTML = `
        <button type="button" onclick="removeItem(${itemIndex})" class="absolute top-4 right-4 text-gray-300 hover:text-rose-500 transition-colors">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
        </button>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Inventory Item</label>
                <select name="items[${itemIndex}][boq_item_id]" class="w-full px-4 py-2 bg-gray-50 border-0 rounded-xl text-xs font-bold" required onchange="loadItemData(this, ${itemIndex})">
                    <option value="">Select Material...</option>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Qty Dispatched</label>
                <input type="number" name="items[${itemIndex}][quantity_dispatched]" step="0.01" class="w-full px-4 py-2 bg-gray-50 border-0 rounded-xl text-xs font-bold" required onchange="calcTotal(${itemIndex})">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Unit Cost (₹)</label>
                <input type="number" name="items[${itemIndex}][unit_cost]" step="0.01" class="w-full px-4 py-2 bg-gray-50 border-0 rounded-xl text-xs font-bold" required onchange="calcTotal(${itemIndex})">
            </div>
        </div>
        <div class="mt-4 flex gap-4">
             <div class="flex-1">
                <input type="text" name="items[${itemIndex}][remarks]" placeholder="Item specific notes..." class="w-full px-4 py-2 bg-gray-50 border-0 rounded-xl text-[11px] font-medium">
            </div>
             <div class="w-32 text-right">
                <div class="text-[9px] font-black text-gray-300 uppercase tracking-widest mb-1">Total</div>
                <div class="text-xs font-bold text-gray-900" id="item-total-${itemIndex}">₹ 0.00</div>
            </div>
        </div>
    `;
    container.appendChild(div);
    populateItems(div.querySelector('select'));
}

function removeItem(id) { document.getElementById(`item-${id}`).remove(); }

function populateItems(select) {
    fetch('get-available-items.php').then(r => r.json()).then(d => {
        if(d.success) d.items.forEach(i => {
            const opt = document.createElement('option');
            opt.value = i.boq_item_id;
            opt.textContent = `${i.item_name} (Code: ${i.item_code}) [Avail: ${i.available_stock}]`;
            opt.dataset.cost = i.unit_cost;
            select.appendChild(opt);
        });
    });
}

function loadItemData(select, id) {
    const cost = select.options[select.selectedIndex].dataset.cost;
    const parent = document.getElementById(`item-${id}`);
    if(cost) parent.querySelector('input[name*="[unit_cost]"]').value = cost;
    calcTotal(id);
}

function calcTotal(id) {
    const parent = document.getElementById(`item-${id}`);
    const qty = parent.querySelector('input[name*="[quantity_dispatched]"]').value || 0;
    const cost = parent.querySelector('input[name*="[unit_cost]"]').value || 0;
    document.getElementById(`item-total-${id}`).textContent = `₹ ${(qty * cost).toFixed(2)}`;
}

// Action Functions
function viewDispatch(id) { window.open(`view-dispatch.php?id=${id}`, '_blank'); }
function viewChallan(id) { window.open(`view-delivery-challan.php?id=${id}`, '_blank'); }

async function updateDispatchStatus(id) {
    const response = await fetch(`get-dispatch-details.php?id=${id}`);
    const data = await response.json();
    if(data.success) {
        const d = data.dispatch;
        document.getElementById('updateDispatchId').value = d.id;
        document.getElementById('updateTargetLabel').textContent = `MANIFEST: ${d.dispatch_number}`;
        document.getElementById('newStatus').value = d.dispatch_status;
        document.getElementById('trackingNumber').value = d.tracking_number || '';
        toggleStatusFields(d.dispatch_status);
        openModal('updateStatusModal');
    }
}

function toggleStatusFields(s) {
    document.getElementById('deliveryDateField').style.display = (s === 'delivered') ? 'block' : 'none';
}

async function exportDispatches() {
    const btn = document.querySelector('button[onclick="exportDispatches()"]');
    const originalContent = btn.innerHTML;

    // Show loading state
    btn.disabled = true;
    btn.classList.add('opacity-75', 'cursor-not-allowed');
    btn.innerHTML = `
        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-gray-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Exporting...
    `;

    if (window.Swal) {
        Swal.fire({
            title: 'Generating Report',
            text: 'Preparing your advanced dispatch manifest...',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => { Swal.showLoading(); }
        });
    }

    const search = document.getElementById('searchInput').value;
    const requestId = document.getElementById('requestIdFilter').value;
    const status = document.getElementById('statusFilter').value;
    const siteId = document.getElementById('siteFilter').value;
    
    const params = new URLSearchParams({
        search,
        request_id: requestId,
        status,
        site_id: siteId
    });

    try {
        const response = await fetch(`export-dispatches.php?${params.toString()}`);
        if (!response.ok) throw new Error('Export service unavailable');

        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        
        const timestamp = new Date().toISOString().slice(0, 19).replace(/[:T]/g, '-');
        const filename = `Dispatch_Manifest_Advanced_${timestamp}.xlsx`;

        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        a.remove();

        if (window.Swal) {
            Swal.fire({ 
                icon: 'success', 
                title: 'Export Complete', 
                text: 'Your manifest has been downloaded.', 
                timer: 2000, 
                showConfirmButton: false 
            });
        }
    } catch (error) {
        console.error('Export failed:', error);
        if (window.Swal) {
            Swal.fire('Export Error', 'Failed to generate the Excel file. Please try again.', 'error');
        } else {
            alert('Export failed. Please try again.');
        }
    } finally {
        btn.disabled = false;
        btn.classList.remove('opacity-75', 'cursor-not-allowed');
        btn.innerHTML = originalContent;
    }
}

// Form Submissions
document.getElementById('createDispatchForm').onsubmit = async (e) => {
    e.preventDefault();
    const res = await fetch('create-dispatch.php', { method: 'POST', body: new FormData(e.target) });
    const data = await res.json();
    if(data.success) {
        showToast('Manifest Created Successfully', 'success');
        closeModal('createDispatchModal');
        fetchDispatches(1);
    } else showToast(data.message, 'error');
};

document.getElementById('updateStatusForm').onsubmit = async (e) => {
    e.preventDefault();
    const res = await fetch('update-dispatch-status.php', { method: 'POST', body: new FormData(e.target) });
    const data = await res.json();
    if(data.success) {
        showToast('Shipment Milestone Updated', 'success');
        closeModal('updateStatusModal');
        fetchDispatches(currentPage);
    } else showToast(data.message, 'error');
};

function debounce(fn, delay) {
    let timeoutID;
    return (...args) => {
        if (timeoutID) clearTimeout(timeoutID);
        timeoutID = setTimeout(() => fn(...args), delay);
    };
}
</script>

<style>
/* ── Premium Settings CSS ── */
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

.v-act{display:flex;align-items:center;gap:5px;justify-content:flex-start}
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
</style>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../../includes/admin_layout.php';
?>