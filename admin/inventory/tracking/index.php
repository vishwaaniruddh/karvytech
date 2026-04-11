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

// Handle filters
$search = $_GET['search'] ?? '';
$siteId = $_GET['site_id'] ?? null;
$vendorId = $_GET['vendor_id'] ?? null;
$status = $_GET['status'] ?? '';

// Get material tracking data
$trackingData = $inventoryModel->getMaterialTracking($search, $siteId, $vendorId, $status);

// Get sites and vendors for filters
$sites = $siteModel->getAllSites();
$vendors = $vendorModel->getAllVendors();

$title = 'Material Tracking';
ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Material Tracking</h1>
        <p class="mt-2 text-sm text-gray-700">Track material location and movement across sites</p>
    </div>
    <div class="flex space-x-2">
        <a href="../" class="btn btn-secondary">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
            </svg>
            Back to Inventory
        </a>
        <button onclick="exportTrackingData()" class="btn btn-secondary">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
            Export Data
        </button>
    </div>
</div>

<!-- Search and Filters -->
<div class="card mb-6">
    <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <input type="text" id="searchInput" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Search materials..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
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
            <div>
                <select id="vendorFilter" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">All Vendors</option>
                    <?php foreach ($vendors as $vendor): ?>
                        <option value="<?php echo $vendor['id']; ?>" <?php echo $vendorId == $vendor['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($vendor['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <select id="statusFilter" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">All Status</option>
                    <option value="available" <?php echo $status === 'available' ? 'selected' : ''; ?>>Available</option>
                    <option value="reserved" <?php echo $status === 'reserved' ? 'selected' : ''; ?>>Reserved</option>
                    <option value="dispatched" <?php echo $status === 'dispatched' ? 'selected' : ''; ?>>Dispatched</option>
                    <option value="installed" <?php echo $status === 'installed' ? 'selected' : ''; ?>>Installed</option>
                    <option value="damaged" <?php echo $status === 'damaged' ? 'selected' : ''; ?>>Damaged</option>
                    <option value="returned" <?php echo $status === 'returned' ? 'selected' : ''; ?>>Returned</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Material Tracking Table -->
<div class="card">
    <div class="card-body">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Material Details</th>
                        <th>Batch/Serial</th>
                        <th>Quantity</th>
                        <th>Current Location</th>
                        <th>Site/Vendor</th>
                        <th>Status</th>
                        <th>Last Movement</th>
                        <th>Dispatch Info</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($trackingData)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-8 text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <p class="mt-2">No material tracking data found</p>
                            <p class="text-sm text-gray-400">Materials will appear here once they are dispatched</p>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($trackingData as $item): ?>
                        <tr>
                            <td>
                                <div>
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($item['item_name']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($item['item_code']); ?></div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <?php if ($item['batch_number']): ?>
                                        <div class="text-sm text-gray-900">Batch: <?php echo htmlspecialchars($item['batch_number']); ?></div>
                                    <?php endif; ?>
                                    <?php if ($item['serial_number']): ?>
                                        <div class="text-sm text-gray-500">Serial: <?php echo htmlspecialchars($item['serial_number']); ?></div>
                                    <?php endif; ?>
                                    <?php if (!$item['batch_number'] && !$item['serial_number']): ?>
                                        <span class="text-sm text-gray-400">No batch/serial</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="text-sm text-gray-900"><?php echo number_format($item['quantity'], 2); ?> <?php echo htmlspecialchars($item['unit']); ?></div>
                            </td>
                            <td>
                                <?php
                                $locationClasses = [
                                    'warehouse' => 'bg-blue-100 text-blue-800',
                                    'in_transit' => 'bg-yellow-100 text-yellow-800',
                                    'site' => 'bg-green-100 text-green-800',
                                    'vendor' => 'bg-purple-100 text-purple-800',
                                    'returned' => 'bg-gray-100 text-gray-800',
                                    'damaged' => 'bg-red-100 text-red-800'
                                ];
                                $locationClass = $locationClasses[$item['current_location_type']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $locationClass; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $item['current_location_type'])); ?>
                                </span>
                                <?php if ($item['current_location_name']): ?>
                                    <div class="text-sm text-gray-500 mt-1"><?php echo htmlspecialchars($item['current_location_name']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div>
                                    <?php if ($item['site_code']): ?>
                                        <div class="text-sm font-medium text-gray-900">Site: <?php echo htmlspecialchars($item['site_code']); ?></div>
                                    <?php endif; ?>
                                    <?php if ($item['vendor_name']): ?>
                                        <div class="text-sm font-medium text-gray-900">Vendor: <?php echo htmlspecialchars($item['vendor_name']); ?></div>
                                    <?php endif; ?>
                                    <?php if (!$item['site_code'] && !$item['vendor_name']): ?>
                                        <span class="text-sm text-gray-400">Not assigned</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php
                                $statusClasses = [
                                    'available' => 'bg-green-100 text-green-800',
                                    'reserved' => 'bg-yellow-100 text-yellow-800',
                                    'dispatched' => 'bg-blue-100 text-blue-800',
                                    'installed' => 'bg-purple-100 text-purple-800',
                                    'damaged' => 'bg-red-100 text-red-800',
                                    'returned' => 'bg-gray-100 text-gray-800'
                                ];
                                $statusClass = $statusClasses[$item['status']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $statusClass; ?>">
                                    <?php echo ucfirst($item['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="text-sm text-gray-900"><?php echo date('d M Y', strtotime($item['last_movement_date'])); ?></div>
                                <div class="text-sm text-gray-500"><?php echo date('H:i', strtotime($item['last_movement_date'])); ?></div>
                            </td>
                            <td>
                                <?php if ($item['dispatch_number']): ?>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($item['dispatch_number']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo date('d M Y', strtotime($item['dispatch_date'])); ?></div>
                                    </div>
                                <?php else: ?>
                                    <span class="text-sm text-gray-400">No dispatch</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="flex items-center space-x-2">
                                    <button onclick="viewTrackingHistory(<?php echo $item['id']; ?>)" class="btn btn-sm btn-secondary" title="View History">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                    <button onclick="updateLocation(<?php echo $item['id']; ?>)" class="btn btn-sm btn-primary" title="Update Location">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                    <?php if ($item['status'] === 'dispatched'): ?>
                                        <button onclick="markAsDelivered(<?php echo $item['id']; ?>)" class="btn btn-sm btn-success" title="Mark as Delivered">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Update Location Modal -->
<div id="updateLocationModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Update Material Location</h3>
            <button type="button" class="modal-close" onclick="closeModal('updateLocationModal')">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <form id="updateLocationForm">
            <input type="hidden" id="tracking_id" name="tracking_id">
            <div class="modal-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label for="location_type" class="form-label">Location Type *</label>
                        <select id="location_type" name="location_type" class="form-select" required onchange="updateLocationOptions()">
                            <option value="">Select Location Type</option>
                            <option value="warehouse">Warehouse</option>
                            <option value="in_transit">In Transit</option>
                            <option value="site">Site</option>
                            <option value="vendor">Vendor</option>
                            <option value="returned">Returned</option>
                            <option value="damaged">Damaged</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="location_id" class="form-label">Specific Location</label>
                        <select id="location_id" name="location_id" class="form-select">
                            <option value="">Select Location</option>
                        </select>
                    </div>
                    <div class="form-group md:col-span-2">
                        <label for="movement_remarks" class="form-label">Movement Remarks</label>
                        <textarea id="movement_remarks" name="movement_remarks" rows="3" class="form-input" placeholder="Enter remarks about this location update..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('updateLocationModal')" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Location</button>
            </div>
        </form>
    </div>
</div>

<!-- Tracking History Modal -->
<div id="trackingHistoryModal" class="modal">
    <div class="modal-content max-w-4xl">
        <div class="modal-header">
            <h3 class="modal-title">Material Tracking History</h3>
            <button type="button" class="modal-close" onclick="closeModal('trackingHistoryModal')">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <div id="trackingHistoryContent">
                <!-- History content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
// Search functionality
document.getElementById('searchInput').addEventListener('keyup', debounce(function() {
    applyFilters();
}, 500));

document.getElementById('siteFilter').addEventListener('change', applyFilters);
document.getElementById('vendorFilter').addEventListener('change', applyFilters);
document.getElementById('statusFilter').addEventListener('change', applyFilters);

function applyFilters() {
    const searchTerm = document.getElementById('searchInput').value;
    const siteId = document.getElementById('siteFilter').value;
    const vendorId = document.getElementById('vendorFilter').value;
    const status = document.getElementById('statusFilter').value;
    
    const url = new URL(window.location);
    
    if (searchTerm) url.searchParams.set('search', searchTerm);
    else url.searchParams.delete('search');
    
    if (siteId) url.searchParams.set('site_id', siteId);
    else url.searchParams.delete('site_id');
    
    if (vendorId) url.searchParams.set('vendor_id', vendorId);
    else url.searchParams.delete('vendor_id');
    
    if (status) url.searchParams.set('status', status);
    else url.searchParams.delete('status');
    
    window.location.href = url.toString();
}

// Tracking functions
function viewTrackingHistory(trackingId) {
    fetch(`get-tracking-history.php?id=${trackingId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('trackingHistoryContent').innerHTML = data.html;
                openModal('trackingHistoryModal');
            } else {
                showAlert('Error loading tracking history: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred while loading tracking history.', 'error');
        });
}

function updateLocation(trackingId) {
    document.getElementById('tracking_id').value = trackingId;
    openModal('updateLocationModal');
}

function updateLocationOptions() {
    const locationType = document.getElementById('location_type').value;
    const locationSelect = document.getElementById('location_id');
    
    // Clear existing options
    locationSelect.innerHTML = '<option value="">Select Location</option>';
    
    if (locationType === 'site') {
        // Load sites
        <?php foreach ($sites as $site): ?>
            locationSelect.innerHTML += '<option value="<?php echo $site['id']; ?>"><?php echo htmlspecialchars($site['site_id']); ?> - <?php echo htmlspecialchars($site['site_name']); ?></option>';
        <?php endforeach; ?>
    } else if (locationType === 'vendor') {
        // Load vendors
        <?php foreach ($vendors as $vendor): ?>
            locationSelect.innerHTML += '<option value="<?php echo $vendor['id']; ?>"><?php echo htmlspecialchars($vendor['name']); ?></option>';
        <?php endforeach; ?>
    }
}

function markAsDelivered(trackingId) {
    if (confirm('Mark this material as delivered?')) {
        fetch('update-material-status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                tracking_id: trackingId,
                status: 'installed',
                location_type: 'site'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Material marked as delivered successfully!', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred while updating material status.', 'error');
        });
    }
}

function exportTrackingData() {
    const params = new URLSearchParams(window.location.search);
    window.open(`export-tracking.php?${params.toString()}`, '_blank');
}

// Form submission
document.getElementById('updateLocationForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('update-material-location.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Material location updated successfully!', 'success');
            closeModal('updateLocationModal');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while updating location.', 'error');
    });
});

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
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../includes/admin_layout.php';
?>