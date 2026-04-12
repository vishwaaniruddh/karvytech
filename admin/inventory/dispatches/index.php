<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../models/Inventory.php';
require_once __DIR__ . '/../../../models/Site.php';
require_once __DIR__ . '/../../../models/Vendor.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$inventoryModel = new Inventory();
$siteModel = new Site();
$vendorModel = new Vendor();

// Handle pagination and filters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$siteId = $_GET['site_id'] ?? null;

// Handle material request pre-fills
$requestId = $_GET['request_id'] ?? null;
$materialRequest = null;
$requestItems = [];

if ($requestId) {
    // Assuming you have a way to get material request details
    // For now, initialize them to avoid errors
    // If you have a method like $inventoryModel->getMaterialRequest($requestId), call it here
}

// Get dispatches
$dispatchesData = $inventoryModel->getDispatches($page, $limit, $search, $status, $siteId);
$dispatches = $dispatchesData['dispatches'];
$totalPages = $dispatchesData['pages'];

// Get sites and vendors for filters
$sites = $siteModel->getAllSites();
$vendors = $vendorModel->getAllVendors();

$title = 'Material Dispatches';
ob_start();
?>

<?php
// Get dispatch stats for the header
$dispatchStats = [
    ['label' => 'Total Dispatches', 'count' => $dispatchesData['total'], 'color' => 'blue', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
    ['label' => 'In Transit', 'count' => count(array_filter($dispatches, fn($d) => in_array($d['dispatch_status'], ['dispatched', 'in_transit']))), 'color' => 'indigo', 'icon' => 'M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0'],
    ['label' => 'Delivered', 'count' => count(array_filter($dispatches, fn($d) => $d['dispatch_status'] === 'delivered')), 'color' => 'emerald', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
    ['label' => 'Pending Action', 'count' => count(array_filter($dispatches, fn($d) => $d['dispatch_status'] === 'prepared')), 'color' => 'amber', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z']
];
?>

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
        <button onclick="openModal('createDispatchModal')" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold uppercase tracking-wider transition-all shadow-lg shadow-blue-200 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Create Dispatch
        </button>
        <a href="../" class="p-2.5 bg-white border border-gray-200 text-gray-400 hover:text-gray-900 hover:border-gray-900 rounded-xl transition-all shadow-sm" title="Back to Inventory">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
        </a>
    </div>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <?php foreach ($dispatchStats as $stat): ?>
    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow group">
        <div class="flex items-center justify-between mb-4">
            <div class="w-10 h-10 bg-<?php echo $stat['color']; ?>-50 rounded-xl flex items-center justify-center text-<?php echo $stat['color']; ?>-600 group-hover:scale-110 transition-transform">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo $stat['icon']; ?>"/></svg>
            </div>
            <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider"><?php echo $stat['label']; ?></span>
        </div>
        <div class="text-3xl font-bold text-gray-900"><?php echo number_format($stat['count']); ?></div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Refined Filters -->
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 mb-8">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-5">
        <div class="md:col-span-5 relative">
            <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Search Dispatch / Courier</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" id="searchInput" class="block w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-transparent rounded-xl text-sm font-semibold focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition-all" placeholder="Enter dispatch number, contact or tracking..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
        </div>
        <div class="md:col-span-3">
            <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Transit Status</label>
            <select id="statusFilter" class="block w-full px-4 py-2.5 bg-gray-50 border border-transparent rounded-xl text-sm font-semibold focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition-all appearance-none cursor-pointer">
                <option value="">All Lifecycle Stages</option>
                <option value="prepared" <?php echo $status === 'prepared' ? 'selected' : ''; ?>>Prepared</option>
                <option value="dispatched" <?php echo $status === 'dispatched' ? 'selected' : ''; ?>>Dispatched</option>
                <option value="in_transit" <?php echo $status === 'in_transit' ? 'selected' : ''; ?>>In Transit</option>
                <option value="delivered" <?php echo $status === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                <option value="returned" <?php echo $status === 'returned' ? 'selected' : ''; ?>>Returned</option>
            </select>
        </div>
        <div class="md:col-span-3">
            <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Destination Site</label>
            <select id="siteFilter" class="block w-full px-4 py-2.5 bg-gray-50 border border-transparent rounded-xl text-sm font-semibold focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition-all appearance-none cursor-pointer">
                <option value="">All Operational Sites</option>
                <?php foreach ($sites as $site): ?>
                    <option value="<?php echo $site['id']; ?>" <?php echo $siteId == $site['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($site['site_id']); ?> - <?php echo htmlspecialchars($site['site_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="md:col-span-1 flex items-end">
            <button onclick="applyFilters()" class="w-full py-2.5 bg-gray-900 text-white rounded-xl flex items-center justify-center hover:bg-black transition-all shadow-md shadow-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
            </button>
        </div>
    </div>
</div>

<!-- Inline script to ensure functions are available -->
<script>
// Define critical functions inline to ensure they're available immediately
if (typeof window.viewDispatch === 'undefined') {
    window.viewDispatch = function(dispatchId) {
        window.open('view-dispatch.php?id=' + dispatchId, '_blank');
    };
}

if (typeof window.updateDispatchStatus === 'undefined') {
    window.updateDispatchStatus = function(dispatchId) {
        alert('Update status functionality will be available shortly. Dispatch ID: ' + dispatchId);
    };
}

if (typeof window.printDispatch === 'undefined') {
    window.printDispatch = function(dispatchId) {
        window.open('print-dispatch.php?id=' + dispatchId, '_blank');
    };
}

// Log that functions are available
console.log('Dispatch functions loaded:', {
    viewDispatch: typeof window.viewDispatch,
    updateDispatchStatus: typeof window.updateDispatchStatus,
    printDispatch: typeof window.printDispatch
});
</script>

<!-- Dispatches Table -->
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50/50">
                <tr>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-left w-12">#</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-left">Actions</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-left">Dispatch Profile</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-left">Destination Context</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-left">Logistics Contact</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-left">Timeline</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-left">Manifest Value</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-right">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php if (empty($dispatches)): ?>
                <tr>
                    <td colspan="8" class="text-center py-20 text-gray-400 font-bold italic">No active dispatch manifests found</td>
                </tr>
                <?php else: ?>
                    <?php 
                    $sno = ($page - 1) * $limit + 1;
                    foreach ($dispatches as $dispatch): 
                    ?>
                    <tr class="hover:bg-gray-50/50 transition-colors group">
                         <td class="px-6 py-4 text-xs font-bold text-gray-400"><?php echo $sno++; ?></td>
                         <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <button onclick="viewDispatch(<?php echo $dispatch['id']; ?>)" class="w-8 h-8 rounded-lg border border-gray-200 bg-white flex items-center justify-center text-gray-400 hover:text-blue-600 hover:border-blue-200 transition-all shadow-sm" title="View Details">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                                <?php if ($dispatch['dispatch_status'] !== 'delivered'): ?>
                                    <button onclick="updateDispatchStatus(<?php echo $dispatch['id']; ?>)" class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-600 hover:text-white transition-all shadow-sm" title="Update Status">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                <?php endif; ?>
                                <button onclick="printDispatch(<?php echo $dispatch['id']; ?>)" class="w-8 h-8 rounded-lg bg-gray-50 text-gray-500 flex items-center justify-center hover:bg-gray-900 hover:text-white transition-all shadow-sm" title="Print Labels">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                </button>
                            </div>
                        </td>
                        
                        <td class="px-6 py-4">
                            <div>
                                <div class="text-sm font-bold text-gray-900"><?php echo htmlspecialchars($dispatch['dispatch_number']); ?></div>
                                <div class="text-[11px] font-medium text-gray-400 uppercase mt-0.5"><?php echo $dispatch['courier_name'] ?: 'Internal Transit'; ?></div>
                                <?php if ($dispatch['tracking_number']): ?>
                                    <div class="mt-1 flex items-center gap-1.5">
                                        <span class="w-1 h-1 rounded-full bg-blue-500"></span>
                                        <span class="text-[10px] font-bold text-blue-600 tracking-tight font-mono uppercase"><?php echo htmlspecialchars($dispatch['tracking_number']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <div class="max-w-[200px]">
                                <?php if ($dispatch['site_code']): ?>
                                    <div class="text-sm font-bold text-gray-900"><?php echo htmlspecialchars($dispatch['site_code']); ?></div>
                                <?php endif; ?>
                                <?php if ($dispatch['vendor_company_name']): ?>
                                    <div class="text-[11px] font-bold text-indigo-600 uppercase mb-0.5">VENDOR: <?php echo htmlspecialchars($dispatch['vendor_company_name']); ?></div>
                                <?php endif; ?>
                                <div class="text-[11px] leading-relaxed text-gray-500 font-medium line-clamp-1 italic"><?php echo htmlspecialchars($dispatch['delivery_address']); ?></div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-gray-900"><?php echo htmlspecialchars($dispatch['contact_person_name']); ?></div>
                            <div class="text-[11px] font-medium text-gray-400 mt-0.5"><?php echo htmlspecialchars($dispatch['contact_person_phone'] ?: '--'); ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-gray-900"><?php echo date('d M Y', strtotime($dispatch['dispatch_date'])); ?></div>
                            <div class="text-[10px] font-medium text-gray-400"><?php echo date('h:i A', strtotime($dispatch['dispatch_date'])); ?></div>
                            <?php if ($dispatch['expected_delivery_date']): ?>
                                <div class="text-[10px] font-bold text-rose-500 mt-1 uppercase tracking-tight">ETA: <?php echo date('d M Y', strtotime($dispatch['expected_delivery_date'])); ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-gray-900"><?php echo $dispatch['total_items']; ?> <span class="text-gray-400 font-medium">Items</span></div>
                            <div class="text-[11px] font-bold text-emerald-600 mt-0.5">₹<?php echo number_format($dispatch['total_value'], 2); ?></div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <?php
                            $statusMap = [
                                'prepared' => ['color' => 'blue', 'label' => 'Awaiting Pickup'],
                                'dispatched' => ['color' => 'amber', 'label' => 'Sent to Courier'],
                                'in_transit' => ['color' => 'indigo', 'label' => 'On the Way'],
                                'delivered' => ['color' => 'emerald', 'label' => 'Delivery Success'],
                                'returned' => ['color' => 'rose', 'label' => 'Return Inbound']
                            ];
                            $st = $statusMap[$dispatch['dispatch_status']] ?? ['color' => 'gray', 'label' => $dispatch['dispatch_status']];
                            ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-<?php echo $st['color']; ?>-50 text-<?php echo $st['color']; ?>-700 border border-<?php echo $st['color']; ?>-100 whitespace-nowrap">
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
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&site_id=<?php echo urlencode($siteId); ?>" 
                   class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-bold transition-all <?php echo $i === $page ? 'bg-gray-900 text-white shadow-lg' : 'bg-white border border-gray-200 text-gray-500 hover:border-gray-900 hover:text-gray-900'; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Create Dispatch Modal -->
<div id="createDispatchModal" class="modal">
    <div class="modal-content max-w-4xl">
        <div class="modal-header">
            <h3 class="modal-title">Create Material Dispatch</h3>
            <button type="button" class="modal-close" onclick="closeModal('createDispatchModal')">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <form id="createDispatchForm">
            <div class="modal-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="form-group">
                        <label for="dispatch_number" class="form-label">Dispatch Number *</label>
                        <input type="text" id="dispatch_number" name="dispatch_number" class="form-input" required readonly>
                    </div>
                    <div class="form-group">
                        <label for="dispatch_date" class="form-label">Dispatch Date *</label>
                        <input type="date" id="dispatch_date" name="dispatch_date" class="form-input" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="material_request_id" class="form-label">Material Request</label>
                        <input type="hidden" id="material_request_id" name="material_request_id" value="<?php echo $requestId ?: ''; ?>">
                        <?php if ($materialRequest): ?>
                            <div class="p-3 bg-blue-50 rounded-md">
                                <div class="text-sm font-medium text-blue-900">Request #<?php echo $materialRequest['id']; ?></div>
                                <div class="text-sm text-blue-700"><?php echo htmlspecialchars($materialRequest['request_notes'] ?: 'No notes'); ?></div>
                            </div>
                        <?php else: ?>
                            <div class="text-sm text-gray-500">No material request selected</div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="site_id" class="form-label">Site</label>
                        <select id="site_id" name="site_id" class="form-select" onchange="updateDeliveryAddress()">
                            <option value="">Select Site</option>
                            <?php foreach ($sites as $site): ?>
                                <option value="<?php echo $site['id']; ?>" 
                                        data-address="<?php echo htmlspecialchars($site['address']); ?>"
                                        <?php echo ($materialRequest && $materialRequest['site_id'] == $site['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($site['site_id']); ?> - <?php echo htmlspecialchars($site['site_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="vendor_id" class="form-label">Vendor</label>
                        <select id="vendor_id" name="vendor_id" class="form-select">
                            <option value="">Select Vendor</option>
                            <?php foreach ($vendors as $vendor): ?>
                                <option value="<?php echo $vendor['id']; ?>"
                                        <?php echo ($materialRequest && $materialRequest['vendor_id'] == $vendor['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($vendor['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="contact_person_name" class="form-label">Contact Person *</label>
                        <input type="text" id="contact_person_name" name="contact_person_name" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="contact_person_phone" class="form-label">Contact Phone</label>
                        <input type="text" id="contact_person_phone" name="contact_person_phone" class="form-input">
                    </div>
                    <div class="form-group md:col-span-2">
                        <label for="delivery_address" class="form-label">Delivery Address *</label>
                        <textarea id="delivery_address" name="delivery_address" rows="3" class="form-input" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="courier_name" class="form-label">Courier Name</label>
                        <input type="text" id="courier_name" name="courier_name" class="form-input">
                    </div>
                    <div class="form-group">
                        <label for="tracking_number" class="form-label">Tracking Number</label>
                        <input type="text" id="tracking_number" name="tracking_number" class="form-input">
                    </div>
                    <div class="form-group">
                        <label for="expected_delivery_date" class="form-label">Expected Delivery Date</label>
                        <input type="date" id="expected_delivery_date" name="expected_delivery_date" class="form-input">
                    </div>
                    <div class="form-group">
                        <label for="delivery_remarks" class="form-label">Delivery Remarks</label>
                        <input type="text" id="delivery_remarks" name="delivery_remarks" class="form-input">
                    </div>
                </div>
                
                <!-- Items Section -->
                <div class="border-t pt-4">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-lg font-medium text-gray-900">Dispatch Items</h4>
                        <button type="button" onclick="addDispatchItem()" class="btn btn-sm btn-primary">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                            </svg>
                            Add Item
                        </button>
                    </div>
                    
                    <div id="dispatchItems">
                        <!-- Items will be added dynamically -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('createDispatchModal')" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Dispatch</button>
            </div>
        </form>
    </div>
</div>

<!-- Update Status Modal -->
<div id="updateStatusModal" class="modal">
    <div class="modal-content max-w-lg">
        <div class="modal-header">
            <h3 class="modal-title">Update Dispatch Status</h3>
            <button type="button" class="modal-close" onclick="closeModal('updateStatusModal')">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <form id="updateStatusForm">
            <div class="modal-body">
                <input type="hidden" id="updateDispatchId" name="dispatch_id">
                
                <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                    <div class="text-sm text-gray-600">Dispatch Number:</div>
                    <div class="font-medium text-gray-900" id="currentDispatchNumber"></div>
                    <div class="text-sm text-gray-600 mt-2">Current Status:</div>
                    <div class="font-medium text-gray-900" id="currentStatus"></div>
                </div>
                
                <div class="grid grid-cols-1 gap-4">
                    <div class="form-group">
                        <label for="newStatus" class="form-label">New Status *</label>
                        <select id="newStatus" name="new_status" class="form-select" required onchange="toggleStatusFields(this.value)">
                            <option value="prepared">Prepared</option>
                            <option value="dispatched">Dispatched</option>
                            <option value="in_transit">In Transit</option>
                            <option value="delivered">Delivered</option>
                            <option value="returned">Returned</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="trackingNumber" class="form-label">Tracking Number</label>
                        <input type="text" id="trackingNumber" name="tracking_number" class="form-input" placeholder="Enter tracking number">
                    </div>
                    
                    <div id="deliveryDateField" class="form-group" style="display: none;">
                        <label for="actualDeliveryDate" class="form-label">Actual Delivery Date</label>
                        <input type="date" id="actualDeliveryDate" name="actual_delivery_date" class="form-input">
                    </div>
                    
                    <div class="form-group">
                        <label for="statusRemarks" class="form-label">Remarks</label>
                        <textarea id="statusRemarks" name="status_remarks" rows="3" class="form-input" placeholder="Enter any remarks about the status update..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('updateStatusModal')" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Status</button>
            </div>
        </form>
    </div>
</div>

<script>
// Define functions immediately to ensure they're available for onclick handlers
(function() {
    'use strict';
    
    // Dispatch management functions - Define these first and make them global
window.viewDispatch = function(dispatchId) {
    console.log('viewDispatch called with ID:', dispatchId);
    window.open(`view-dispatch.php?id=${dispatchId}`, '_blank');
};

window.printDispatch = function(dispatchId) {
    console.log('printDispatch called with ID:', dispatchId);
    window.open(`print-dispatch.php?id=${dispatchId}`, '_blank');
};

window.updateDispatchStatus = function(dispatchId) {
    console.log('updateDispatchStatus called with ID:', dispatchId);
    // Load current dispatch details
    fetch(`get-dispatch-details.php?id=${dispatchId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                openUpdateStatusModal(data.dispatch);
            } else {
                showAlert('Error loading dispatch details: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error loading dispatch details', 'error');
        });
};

window.openUpdateStatusModal = function(dispatch) {
    // Populate the modal with current dispatch data
    document.getElementById('updateDispatchId').value = dispatch.id;
    document.getElementById('currentDispatchNumber').textContent = dispatch.dispatch_number;
    document.getElementById('currentStatus').textContent = dispatch.dispatch_status.replace('_', ' ').toUpperCase();
    document.getElementById('newStatus').value = dispatch.dispatch_status;
    document.getElementById('actualDeliveryDate').value = '';
    document.getElementById('statusRemarks').value = dispatch.delivery_remarks || '';
    
    // Show appropriate fields based on current status
    toggleStatusFields(dispatch.dispatch_status);
    
    // Open the modal
    openModal('updateStatusModal');
};

window.toggleStatusFields = function(currentStatus) {
    const deliveryDateField = document.getElementById('deliveryDateField');
    const trackingField = document.getElementById('trackingField');
    
    // Show delivery date field only for delivered status
    if (currentStatus === 'delivered') {
        deliveryDateField.style.display = 'block';
    } else {
        deliveryDateField.style.display = 'none';
    }
};

// Search functionality
function applyFilters() {
    const searchTerm = document.getElementById('searchInput').value;
    const status = document.getElementById('statusFilter').value;
    const siteId = document.getElementById('siteFilter').value;
    
    const url = new URL(window.location);
    
    if (searchTerm) url.searchParams.set('search', searchTerm);
    else url.searchParams.delete('search');
    
    if (status) url.searchParams.set('status', status);
    else url.searchParams.delete('status');
    
    if (siteId) url.searchParams.set('site_id', siteId);
    else url.searchParams.delete('site_id');
    
    url.searchParams.delete('page'); // Reset to first page
    
    window.location.href = url.toString();
}

function exportDispatches() {
    const params = new URLSearchParams(window.location.search);
    window.location.href = `export-dispatches.php?${params.toString()}`;
}

// Initialize page functionality
document.addEventListener('DOMContentLoaded', function() {
    // Verify functions are loaded
    console.log('Functions loaded:', {
        viewDispatch: typeof viewDispatch,
        updateDispatchStatus: typeof updateDispatchStatus,
        printDispatch: typeof printDispatch
    });
    
    // Generate dispatch number when modal opens
    generateDispatchNumber();
    
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const siteFilter = document.getElementById('siteFilter');
    
    if (searchInput) {
        searchInput.addEventListener('keyup', debounce(function() {
            applyFilters();
        }, 500));
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', applyFilters);
    }
    
    if (siteFilter) {
        siteFilter.addEventListener('change', applyFilters);
    }
});

function generateDispatchNumber() {
    fetch('generate-dispatch-number.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('dispatch_number').value = data.dispatch_number;
            }
        })
        .catch(error => console.error('Error generating dispatch number:', error));
}

function updateDeliveryAddress() {
    const siteSelect = document.getElementById('site_id');
    const selectedOption = siteSelect.options[siteSelect.selectedIndex];
    const address = selectedOption.getAttribute('data-address');
    
    if (address) {
        document.getElementById('delivery_address').value = address;
    }
}

// Dispatch items management
let dispatchItemCounter = 0;

function addDispatchItem() {
    dispatchItemCounter++;
    const itemsContainer = document.getElementById('dispatchItems');
    
    const itemDiv = document.createElement('div');
    itemDiv.className = 'dispatch-item border rounded-lg p-4 mb-4';
    itemDiv.id = `dispatch-item-${dispatchItemCounter}`;
    
    itemDiv.innerHTML = `
        <div class="flex justify-between items-center mb-3">
            <h5 class="text-sm font-medium text-gray-900">Item ${dispatchItemCounter}</h5>
            <button type="button" onclick="removeDispatchItem(${dispatchItemCounter})" class="text-red-600 hover:text-red-800">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="form-group">
                <label class="form-label">BOQ Item *</label>
                <select name="items[${dispatchItemCounter}][boq_item_id]" class="form-select boq-item-select" required onchange="updateDispatchItemDetails(this, ${dispatchItemCounter})">
                    <option value="">Select Item</option>
                    <!-- Items will be loaded via AJAX -->
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Quantity *</label>
                <input type="number" name="items[${dispatchItemCounter}][quantity_dispatched]" step="0.01" class="form-input quantity-input" required onchange="calculateDispatchItemTotal(${dispatchItemCounter})">
                <small class="text-gray-500 unit-display"></small>
            </div>
            <div class="form-group">
                <label class="form-label">Unit Cost *</label>
                <input type="number" name="items[${dispatchItemCounter}][unit_cost]" step="0.01" class="form-input unit-cost-input" required onchange="calculateDispatchItemTotal(${dispatchItemCounter})">
            </div>
            <div class="form-group">
                <label class="form-label">Total Cost</label>
                <input type="number" name="items[${dispatchItemCounter}][total_cost]" step="0.01" class="form-input total-cost-input" readonly>
            </div>
            <div class="form-group">
                <label class="form-label">Batch Number</label>
                <input type="text" name="items[${dispatchItemCounter}][batch_number]" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Item Condition</label>
                <select name="items[${dispatchItemCounter}][item_condition]" class="form-select">
                    <option value="new">New</option>
                    <option value="used">Used</option>
                    <option value="refurbished">Refurbished</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Warranty Period</label>
                <input type="text" name="items[${dispatchItemCounter}][warranty_period]" class="form-input" placeholder="e.g., 1 year">
            </div>
            <div class="form-group">
                <label class="form-label">Remarks</label>
                <input type="text" name="items[${dispatchItemCounter}][remarks]" class="form-input">
            </div>
        </div>
    `;
    
    itemsContainer.appendChild(itemDiv);
    loadAvailableItems(dispatchItemCounter);
}

function removeDispatchItem(itemId) {
    const itemDiv = document.getElementById(`dispatch-item-${itemId}`);
    if (itemDiv) {
        itemDiv.remove();
        calculateDispatchTotalValue();
    }
}

function loadAvailableItems(itemId) {
    fetch('get-available-items.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.querySelector(`#dispatch-item-${itemId} .boq-item-select`);
                data.items.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.boq_item_id;
                    option.textContent = `${item.item_name} (${item.item_code}) - Available: ${item.available_stock} ${item.unit}`;
                    option.setAttribute('data-unit', item.unit);
                    option.setAttribute('data-available', item.available_stock);
                    option.setAttribute('data-cost', item.unit_cost);
                    select.appendChild(option);
                });
            }
        })
        .catch(error => console.error('Error loading items:', error));
}

function updateDispatchItemDetails(selectElement, itemId) {
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const unit = selectedOption.getAttribute('data-unit');
    const available = selectedOption.getAttribute('data-available');
    const cost = selectedOption.getAttribute('data-cost');
    
    const unitDisplay = document.querySelector(`#dispatch-item-${itemId} .unit-display`);
    const unitCostInput = document.querySelector(`#dispatch-item-${itemId} .unit-cost-input`);
    const quantityInput = document.querySelector(`#dispatch-item-${itemId} .quantity-input`);
    
    if (unitDisplay && unit) {
        unitDisplay.textContent = `Unit: ${unit} (Available: ${available})`;
    }
    
    if (unitCostInput && cost) {
        unitCostInput.value = cost;
    }
    
    if (quantityInput && available) {
        quantityInput.setAttribute('max', available);
    }
    
    calculateDispatchItemTotal(itemId);
}

function calculateDispatchItemTotal(itemId) {
    const quantityInput = document.querySelector(`#dispatch-item-${itemId} .quantity-input`);
    const unitCostInput = document.querySelector(`#dispatch-item-${itemId} .unit-cost-input`);
    const totalCostInput = document.querySelector(`#dispatch-item-${itemId} .total-cost-input`);
    
    const quantity = parseFloat(quantityInput.value) || 0;
    const unitCost = parseFloat(unitCostInput.value) || 0;
    const totalCost = quantity * unitCost;
    
    totalCostInput.value = totalCost.toFixed(2);
    calculateDispatchTotalValue();
}

function calculateDispatchTotalValue() {
    let totalValue = 0;
    const totalCostInputs = document.querySelectorAll('#dispatchItems .total-cost-input');
    
    totalCostInputs.forEach(input => {
        totalValue += parseFloat(input.value) || 0;
    });
    
    // Update display if needed
    console.log('Total dispatch value:', totalValue);
}

// Update Status Form submission
document.getElementById('updateStatusForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('update-dispatch-status.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Dispatch status updated successfully!', 'success');
            closeModal('updateStatusModal');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while updating the status.', 'error');
    });
});

// Form submission
document.getElementById('createDispatchForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('create-dispatch.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Dispatch created successfully!', 'success');
            closeModal('createDispatchModal');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while creating the dispatch.', 'error');
    });
});

// Pre-fill items from material request if available
<?php if ($materialRequest && !empty($requestItems)): ?>
// Pre-populate items from material request
document.addEventListener('DOMContentLoaded', function() {
    // Wait a bit for the page to fully load
    setTimeout(function() {
        <?php foreach ($requestItems as $index => $item): ?>
        addDispatchItem();
        const itemContainer = document.getElementById(`dispatch-item-${dispatchItemCounter}`);
        if (itemContainer) {
            const boqSelect = itemContainer.querySelector('.boq-item-select');
            const quantityInput = itemContainer.querySelector('.quantity-input');
            const remarksInput = itemContainer.querySelector('input[name*="[remarks]"]');
            
            // Wait for the BOQ items to load via AJAX
            setTimeout(function() {
                if (boqSelect) boqSelect.value = '<?php echo $item['boq_item_id']; ?>';
                if (quantityInput) quantityInput.value = '<?php echo $item['quantity']; ?>';
                if (remarksInput) remarksInput.value = '<?php echo htmlspecialchars($item['notes']); ?>';
                
                // Trigger change event to update item details
                if (boqSelect) {
                    boqSelect.dispatchEvent(new Event('change'));
                }
            }, 500);
        }
        <?php endforeach; ?>
    }, 100);
});

// Auto-open modal if coming from material request
<?php if ($requestId): ?>
document.addEventListener('DOMContentLoaded', function() {
    openModal('createDispatchModal');
    
    // Pre-fill delivery address if site is selected
    const siteSelect = document.getElementById('site_id');
    if (siteSelect && siteSelect.value) {
        updateDeliveryAddress();
    }
});
<?php endif; ?>
<?php else: ?>
// Add first item by default if no material request
addDispatchItem();
<?php endif; ?>

// Utility function for debouncing
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Modal utility functions - Make them global
window.openModal = function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
        modal.style.alignItems = 'flex-start';
        modal.style.justifyContent = 'center';
        document.body.style.overflow = 'hidden';
    }
};

window.closeModal = function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
};

// Alert function - Make it global
window.showAlert = function(message, type = 'info') {
    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} fixed top-4 right-4 z-50 max-w-sm p-4 rounded-lg shadow-lg`;
    
    const alertClasses = {
        'success': 'bg-green-100 border-green-500 text-green-700',
        'error': 'bg-red-100 border-red-500 text-red-700',
        'warning': 'bg-yellow-100 border-yellow-500 text-yellow-700',
        'info': 'bg-blue-100 border-blue-500 text-blue-700'
    };
    
    alertDiv.className += ' ' + (alertClasses[type] || alertClasses['info']);
    alertDiv.innerHTML = `
        <div class="flex items-center">
            <span class="flex-1">${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-lg font-bold">&times;</button>
        </div>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentElement) {
            alertDiv.remove();
        }
    }, 5000);
};

})(); // End of IIFE
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../includes/admin_layout.php';
?>