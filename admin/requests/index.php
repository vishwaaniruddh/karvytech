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

$title = 'Material Requests';
ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Material Requests</h1>
        <p class="mt-2 text-sm text-gray-700">Manage material requests from vendors</p>
    </div>
    <div class="relative inline-block">

        <a href="bulk_material_requests_upload.php" class="btn btn-secondary">
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg> Upload Material Requests In Bulk
        </a>




        <div id="bulkUploadMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border border-gray-200">
            <div class="py-1">
            </div>
        </div>
    </div>
    <div class="flex space-x-2">
        <button onclick="exportRequests()" class="btn btn-secondary">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
            Export
        </button>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Requests</dt>
                        <dd class="text-lg font-medium text-gray-900"><?php echo number_format($stats['total']); ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Pending</dt>
                        <dd class="text-lg font-medium text-gray-900"><?php echo number_format($stats['pending']); ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Approved</dt>
                        <dd class="text-lg font-medium text-gray-900"><?php echo number_format($stats['approved']); ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Dispatched</dt>
                        <dd class="text-lg font-medium text-gray-900"><?php echo number_format($stats['dispatched']); ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search and Filters -->
<div class="card mb-6">
    <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <select id="statusFilter" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">All Status</option>
                    <option value="draft" <?php echo $filters['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                    <option value="pending" <?php echo $filters['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="approved" <?php echo $filters['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                    <option value="dispatched" <?php echo $filters['status'] === 'dispatched' ? 'selected' : ''; ?>>Dispatched</option>
                    <option value="completed" <?php echo $filters['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="rejected" <?php echo $filters['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                </select>
            </div>
            <div>
                <select id="vendorFilter" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">All Vendors</option>
                    <?php foreach ($vendors as $vendor): ?>
                        <option value="<?php echo $vendor['id']; ?>" <?php echo $filters['vendor_id'] == $vendor['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($vendor['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <select id="siteFilter" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">All Sites</option>
                    <?php foreach ($sites as $site): ?>
                        <option value="<?php echo $site['id']; ?>" <?php echo $filters['site_id'] == $site['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($site['site_id']); ?> - <?php echo htmlspecialchars($site['site_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <button onclick="applyFilters()" class="btn btn-primary w-full">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 10.414V17a1 1 0 01-.293.707l-2 2A1 1 0 018 19v-8.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd"></path>
                    </svg>
                    Apply Filters
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Material Requests Table -->
<div class="card">
    <div class="card-body">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Actions</th>
                        <th>Request Details</th>
                        <th>Site</th>
                        <th>Vendor</th>
                        <th>Dates</th>
                        <th>Items</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($requests)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-8 text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-2.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 009.586 13H7"></path>
                            </svg>
                            <p class="mt-2">No material requests found</p>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($requests as $request): ?>
                        <tr>
                            
                            
                             <td>
                                <div class="flex items-center space-x-2">
                                    <button onclick="viewRequest(<?php echo $request['id']; ?>)" class="btn btn-sm btn-secondary" title="View Details">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                    <?php if ($request['status'] === 'pending'): ?>
                                        <button onclick="approveRequest(<?php echo $request['id']; ?>)" class="btn btn-sm btn-success" title="Approve Request">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                        </button>
                                        <button onclick="rejectRequest(<?php echo $request['id']; ?>)" class="btn btn-sm btn-danger" title="Reject Request">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                            </svg>
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($request['status'] === 'approved'): ?>
                                        <button onclick="createDispatch(<?php echo $request['id']; ?>)" class="btn btn-sm btn-primary" title="Create Dispatch">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                            
                            
                            
                            <td>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">Request #<?php echo $request['id']; ?></div>
                                    <?php if ($request['request_notes']): ?>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars(substr($request['request_notes'], 0, 50)); ?>...</div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="max-w-[150px]">
                                    <div class="text-sm font-medium text-gray-900 truncate"><?php echo htmlspecialchars($request['site_code']); ?></div>
                                    <div class="text-xs text-gray-500 truncate"><?php echo htmlspecialchars($request['location']); ?></div>
                                </div>
                            </td>
                            <td>
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($request['vendor_company_name'] ?? $request['vendor_name']); ?></div>
                                <?php if (!empty($request['vendor_name']) && $request['vendor_name'] !== $request['vendor_company_name']): ?>
                                    <div class="text-xs text-gray-500"><?php echo htmlspecialchars($request['vendor_name']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div>
                                    <div class="text-sm text-gray-900">Requested: <?php echo date('d M Y', strtotime($request['request_date'])); ?></div>
                                    <?php if ($request['required_date']): ?>
                                        <div class="text-sm text-gray-500">Required: <?php echo date('d M Y', strtotime($request['required_date'])); ?></div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php 
                                $items = json_decode($request['items'], true);
                                $itemCount = is_array($items) ? count($items) : 0;
                                
                                // Get priority from first item or request notes
                                $priority = null;
                                if (is_array($items) && !empty($items)) {
                                    $priority = $items[0]['priority'] ?? null;
                                }
                                
                                $priorityClasses = [
                                    'urgent' => 'bg-red-100 text-red-800',
                                    'high' => 'bg-orange-100 text-orange-800',
                                    'medium' => 'bg-yellow-100 text-yellow-800',
                                    'low' => 'bg-green-100 text-green-800'
                                ];
                                ?>
                                <div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?php echo $itemCount; ?> item<?php echo $itemCount != 1 ? 's' : ''; ?>
                                    </span>
                                    <?php if ($priority): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ml-1 <?php echo $priorityClasses[$priority] ?? 'bg-gray-100 text-gray-800'; ?>">
                                            <?php echo ucfirst($priority); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php
                                $statusClasses = [
                                    'draft' => 'bg-gray-100 text-gray-800',
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'approved' => 'bg-green-100 text-green-800',
                                    'dispatched' => 'bg-blue-100 text-blue-800',
                                    'completed' => 'bg-purple-100 text-purple-800',
                                    'rejected' => 'bg-red-100 text-red-800'
                                ];
                                $statusClass = $statusClasses[$request['status']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $statusClass; ?>">
                                    <?php echo ucfirst($request['status']); ?>
                                </span>
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
                    <a href="?page=<?php echo $page - 1; ?>&status=<?php echo urlencode($filters['status']); ?>&vendor_id=<?php echo urlencode($filters['vendor_id']); ?>&site_id=<?php echo urlencode($filters['site_id']); ?>" class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Previous</a>
                <?php endif; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&status=<?php echo urlencode($filters['status']); ?>&vendor_id=<?php echo urlencode($filters['vendor_id']); ?>&site_id=<?php echo urlencode($filters['site_id']); ?>" class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Next</a>
                <?php endif; ?>
            </div>
            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing <span class="font-medium"><?php echo (($page - 1) * $limit) + 1; ?></span> to 
                        <span class="font-medium"><?php echo min($page * $limit, $requestsData['total']); ?></span> of 
                        <span class="font-medium"><?php echo $requestsData['total']; ?></span> results
                    </p>
                </div>
                <div>
                    <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>&status=<?php echo urlencode($filters['status']); ?>&vendor_id=<?php echo urlencode($filters['vendor_id']); ?>&site_id=<?php echo urlencode($filters['site_id']); ?>" 
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

<script>
// Filter functionality
function applyFilters() {
    const status = document.getElementById('statusFilter').value;
    const vendorId = document.getElementById('vendorFilter').value;
    const siteId = document.getElementById('siteFilter').value;
    
    const url = new URL(window.location);
    
    if (status) url.searchParams.set('status', status);
    else url.searchParams.delete('status');
    
    if (vendorId) url.searchParams.set('vendor_id', vendorId);
    else url.searchParams.delete('vendor_id');
    
    if (siteId) url.searchParams.set('site_id', siteId);
    else url.searchParams.delete('site_id');
    
    url.searchParams.delete('page'); // Reset to first page
    
    window.location.href = url.toString();
}

// Request management functions
function viewRequest(requestId) {
    window.open(`view-request.php?id=${requestId}`, '_blank');
}

function approveRequest(requestId) {
    if (confirm('Are you sure you want to approve this material request?')) {
        updateRequestStatus(requestId, 'approved');
    }
}

function rejectRequest(requestId) {
    if (confirm('Are you sure you want to reject this material request?')) {
        updateRequestStatus(requestId, 'rejected');
    }
}

function updateRequestStatus(requestId, status) {
    fetch('update-request-status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            request_id: requestId,
            status: status
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(`Request ${status} successfully!`, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while updating the request.', 'error');
    });
}

function createDispatch(requestId) {
    window.location.href = `dispatch-material.php?request_id=${requestId}`;
}

function exportRequests() {
    const params = new URLSearchParams(window.location.search);
    window.open(`export-requests.php?${params.toString()}`, '_blank');
}

// Auto-apply filters when dropdowns change
document.getElementById('statusFilter').addEventListener('change', applyFilters);
document.getElementById('vendorFilter').addEventListener('change', applyFilters);
document.getElementById('siteFilter').addEventListener('change', applyFilters);

// Show all materials modal
function showAllMaterials(requestId, materials) {
    const modal = document.getElementById('materialsModal');
    const list = document.getElementById('materialsList');
    
    // Clear previous content
    list.innerHTML = '';
    
    // Add all materials to the list
    materials.forEach((material, index) => {
        const li = document.createElement('li');
        li.className = 'py-2 px-3 hover:bg-gray-50 rounded';
        li.innerHTML = `<span class="text-sm text-gray-700">${index + 1}. ${material}</span>`;
        list.appendChild(li);
    });
    
    // Show modal
    modal.classList.remove('hidden');
}

function closeMaterialsModal() {
    document.getElementById('materialsModal').classList.add('hidden');
}
</script>

<!-- Materials Modal -->
<div id="materialsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">All Materials</h3>
            <button onclick="closeMaterialsModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <div class="max-h-96 overflow-y-auto">
            <ul id="materialsList" class="divide-y divide-gray-200">
                <!-- Materials will be inserted here -->
            </ul>
        </div>
        <div class="mt-4 flex justify-end">
            <button onclick="closeMaterialsModal()" class="btn btn-secondary">Close</button>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>