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

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Material Dispatches</h1>
        <p class="mt-2 text-sm text-gray-700">Manage material dispatches to sites and vendors</p>
    </div>
    <div class="flex space-x-2">
        <a href="../" class="btn btn-secondary">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
            </svg>
            Back to Inventory
        </a>
        <button onclick="openModal('createDispatchModal')" class="btn btn-primary">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
            </svg>
            New Dispatch
        </button>
    </div>
</div>

<!-- Search and Filters -->
<div class="card mb-6">
    <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <input type="text" id="searchInput" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Search dispatches..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div>
                <select id="statusFilter" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">All Status</option>
                    <option value="prepared" <?php echo $status === 'prepared' ? 'selected' : ''; ?>>Prepared</option>
                    <option value="dispatched" <?php echo $status === 'dispatched' ? 'selected' : ''; ?>>Dispatched</option>
                    <option value="in_transit" <?php echo $status === 'in_transit' ? 'selected' : ''; ?>>In Transit</option>
                    <option value="delivered" <?php echo $status === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                    <option value="returned" <?php echo $status === 'returned' ? 'selected' : ''; ?>>Returned</option>
                </select>
            </div>
            <div>
                <select id="siteFilter" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">All Sites</option>
                    <?php foreach ($sites as $site): ?>
                        <option value="<?php echo $site['id']; ?>" <?php echo $siteId == $site['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($site['site_id']); ?> - <?php echo htmlspecialchars($site['site_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
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
<div class="card">
    <div class="card-body">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Actions</th>
                        <th>Dispatch Details</th>
                        <th>Destination</th>
                        <th>Contact Person</th>
                        <th>Date</th>
                        <th>Items/Value</th>
                        <th>Status</th>
                        <th>Tracking</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($dispatches)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-8 text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-2.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 009.586 13H7"></path>
                            </svg>
                            <p class="mt-2">No dispatches found</p>
                            <button onclick="openModal('createDispatchModal')" class="mt-2 btn btn-primary btn-sm">Create First Dispatch</button>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($dispatches as $dispatch): ?>
                        <tr>
                            
                             <td>
                                <div class="flex items-center space-x-2">
                                    <button onclick="viewDispatch(<?php echo $dispatch['id']; ?>)" class="btn btn-sm btn-secondary" title="View Details">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                    <?php if ($dispatch['dispatch_status'] !== 'delivered'): ?>
                                        <button onclick="updateDispatchStatus(<?php echo $dispatch['id']; ?>)" class="btn btn-sm btn-primary" title="Update Status">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                                            </svg>
                                        </button>
                                    <?php endif; ?>
                                    <button onclick="printDispatch(<?php echo $dispatch['id']; ?>)" class="btn btn-sm btn-secondary" title="Print Dispatch">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                            
                            <td>
                                <div>
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($dispatch['dispatch_number']); ?></div>
                                    <?php if ($dispatch['courier_name']): ?>
                                        <div class="text-sm text-gray-500">Courier: <?php echo htmlspecialchars($dispatch['courier_name']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <?php if ($dispatch['site_code']): ?>
                                        <div class="text-sm font-medium text-gray-900">Site: <?php echo htmlspecialchars($dispatch['site_code']); ?></div>
                                    <?php endif; ?>
                                    <?php if ($dispatch['vendor_company_name']): ?>
                                        <div class="text-xs font-medium text-gray-900">Vendor: <?php echo htmlspecialchars($dispatch['vendor_company_name']); ?></div>
                                    <?php endif; ?>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars(substr($dispatch['delivery_address'], 0, 50)); ?>...</div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($dispatch['contact_person_name']); ?></div>
                                    <?php if ($dispatch['contact_person_phone']): ?>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($dispatch['contact_person_phone']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="text-sm text-gray-900"><?php echo date('d M Y', strtotime($dispatch['dispatch_date'])); ?></div>
                                <?php if ($dispatch['expected_delivery_date']): ?>
                                    <div class="text-sm text-gray-500">Expected: <?php echo date('d M Y', strtotime($dispatch['expected_delivery_date'])); ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div>
                                    <div class="text-sm font-medium text-gray-900"><?php echo $dispatch['total_items']; ?> items</div>
                                    <div class="text-sm text-gray-500">₹<?php echo number_format($dispatch['total_value'], 2); ?></div>
                                </div>
                            </td>
                            <td>
                                <?php
                                $statusClasses = [
                                    'prepared' => 'bg-blue-100 text-blue-800',
                                    'dispatched' => 'bg-yellow-100 text-yellow-800',
                                    'in_transit' => 'bg-purple-100 text-purple-800',
                                    'delivered' => 'bg-green-100 text-green-800',
                                    'returned' => 'bg-red-100 text-red-800'
                                ];
                                $statusClass = $statusClasses[$dispatch['dispatch_status']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $statusClass; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $dispatch['dispatch_status'])); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($dispatch['tracking_number']): ?>
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($dispatch['tracking_number']); ?></div>
                                <?php else: ?>
                                    <span class="text-sm text-gray-500">No tracking</span>
                                <?php endif; ?>
                            </td>
                           
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($totalPages > 1): ?>
        <!-- Pagination -->
        <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 mt-4">
            <div class="flex flex-1 justify-between sm:hidden">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&site_id=<?php echo urlencode($siteId); ?>" class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Previous</a>
                <?php endif; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&site_id=<?php echo urlencode($siteId); ?>" class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Next</a>
                <?php endif; ?>
            </div>
            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing <span class="font-medium"><?php echo (($page - 1) * $limit) + 1; ?></span> to 
                        <span class="font-medium"><?php echo min($page * $limit, $dispatchesData['total']); ?></span> of 
                        <span class="font-medium"><?php echo $dispatchesData['total']; ?></span> results
                    </p>
                </div>
                <div>
                    <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&site_id=<?php echo urlencode($siteId); ?>" 
                               class="relative inline-flex items-center px-4 py-2 text-sm font-semibold <?php echo $i === $page ? 'bg-blue-600 text-white' : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50'; ?> focus:z-20 focus:outline-offset-0">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </nav>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
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