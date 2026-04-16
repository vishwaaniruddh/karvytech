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
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden min-h-[400px]">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50/50">
                <tr>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-left w-12">#</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-left">Actions</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-left">Manifest ID</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-left">Request</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-left">Destination Context</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-left">Timeline</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-left text-right">Value</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-right">Status</th>
                </tr>
            </thead>
            <tbody id="dispatchesTableBody" class="divide-y divide-gray-50">
                <!-- Data injected by JS -->
            </tbody>
        </table>
    </div>
    <div id="pagination" class="px-6 py-4 bg-gray-50/50 border-t border-gray-100 flex items-center justify-between">
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
        body.innerHTML = `<tr><td colspan="8" class="text-center py-24 text-gray-400 font-bold italic">No outbound manifests match your parameters</td></tr>`;
        renderPagination(pagination);
        return;
    }

    body.innerHTML = data.map((d, i) => {
        const sno = (pagination.page - 1) * pagination.limit + i + 1;
        const st = statusMap[d.dispatch_status] || { color: 'gray', label: d.dispatch_status };
        const date = new Date(d.dispatch_date);
        
        return `
            <tr class="hover:bg-gray-50/50 transition-colors group">
                <td class="px-6 py-4 text-xs font-bold text-gray-300 whitespace-nowrap">#${sno}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center gap-1.5">
                        <button onclick="viewDispatch(${d.id})" class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-white border border-transparent hover:border-blue-100 transition-all shadow-sm" title="View Manifest">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                        ${d.dispatch_status !== 'delivered' ? `
                            <button onclick="updateDispatchStatus(${d.id})" class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-600 hover:text-white transition-all shadow-sm" title="Update Route Milestone">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </button>
                        ` : ''}
                        <button onclick="viewChallan(${d.id})" class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400 hover:text-indigo-600 hover:bg-white border border-transparent hover:border-indigo-100 transition-all shadow-sm" title="Print Delivery Challan">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </button>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div>
                        <div class="text-sm font-bold text-gray-900">${d.dispatch_number}</div>
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">${d.courier_name || 'Hand Carry/Internal'}</div>
                        ${d.tracking_number ? `
                            <div class="mt-1 flex items-center gap-1.5">
                                <span class="text-[9px] font-black text-blue-600 tracking-tighter uppercase font-mono bg-blue-50 px-1.5 rounded">${d.tracking_number}</span>
                            </div>
                        ` : ''}
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    ${d.material_request_id ? `
                        <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-gray-100 text-gray-600 border border-gray-200">
                            <span class="text-[10px] font-bold uppercase tracking-tighter">REQ-${String(d.material_request_id).padStart(6, '0')}</span>
                        </div>
                    ` : '<span class="text-xs text-gray-300 font-bold italic">N/A</span>'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="max-w-[200px]">
                        <div class="text-sm font-bold text-gray-900">${d.site_code}</div>
                        ${d.vendor_company_name ? `<div class="text-[10px] font-bold text-indigo-500 uppercase mt-0.5 truncate tracking-wide">${d.vendor_company_name}</div>` : ''}
                        <div class="text-[10px] text-gray-400 leading-tight mt-1 line-clamp-1 italic">${d.delivery_address}</div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-bold text-gray-900">${date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })}</div>
                    <div class="text-[10px] font-medium text-gray-400">${date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}</div>
                </td>
                <td class="px-6 py-4 text-right whitespace-nowrap">
                    <div class="text-sm font-bold text-gray-900">${d.total_items} Item(s)</div>
                    <div class="text-[11px] font-bold text-emerald-600 mt-0.5">₹${parseFloat(d.total_value).toLocaleString('en-IN', { minimumFractionDigits: 2 })}</div>
                </td>
                <td class="px-6 py-4 text-right whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-1 rounded text-[10px] font-black uppercase tracking-widest bg-${st.color}-50 text-${st.color}-700 border border-${st.color}-100">
                        ${st.label}
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
        { label: 'Total Manisfests', val: stats.total, color: 'blue', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' },
        { label: 'In Transit', val: stats.in_transit, color: 'indigo', icon: 'M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0' },
        { label: 'Delivered', val: stats.delivered, color: 'emerald', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
        { label: 'Awaiting Pickup', val: stats.pending, color: 'amber', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' }
    ];

    grid.innerHTML = items.map(s => `
        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm transition-all hover:shadow-lg group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 bg-${s.color}-50 rounded-xl flex items-center justify-center text-${s.color}-600 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${s.icon}"/></svg>
                </div>
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">${s.label}</span>
            </div>
            <div class="text-3xl font-bold text-gray-900 tracking-tight">${Number(s.val).toLocaleString()}</div>
        </div>
    `).join('');
}

function renderPagination(p) {
    const el = document.getElementById('pagination');
    if (p.pages <= 1) {
        el.innerHTML = '';
        return;
    }

    let buttons = '';
    for (let i = 1; i <= p.pages; i++) {
        buttons += `
            <button onclick="fetchDispatches(${i})" class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-bold transition-all ${i === p.page ? 'bg-gray-900 text-white shadow-lg' : 'bg-white border border-gray-200 text-gray-400 hover:border-gray-900 hover:text-gray-900'}">${i}</button>
        `;
    }

    el.innerHTML = `
        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Page ${p.page} of ${p.pages}</div>
        <div class="flex gap-1.5">${buttons}</div>
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

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../includes/admin_layout.php';
?>