<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/MaterialRequest.php';
require_once __DIR__ . '/../models/Site.php';

// Require vendor authentication
Auth::requireVendor();

$vendorId = Auth::getVendorId();

// Get all material requests for this vendor
$materialRequestModel = new MaterialRequest();
$materialRequests = $materialRequestModel->getVendorRequests($vendorId);

$title = 'My Material Requests';
ob_start();
?>

<!-- Header Section -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
        <div class="flex-1">
            <h1 class="text-3xl font-bold text-gray-900">My Material Requests</h1>
            <p class="mt-2 text-lg text-gray-600">View and track your submitted material requests</p>
        </div>
        <div class="mt-6 lg:mt-0 lg:ml-6">
            <a href="material-request.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                </svg>
                New Request
            </a>
        </div>
    </div>
</div>

<!-- Material Requests Table -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="p-6">
        <?php if (empty($materialRequests)): ?>
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Material Requests</h3>
                <p class="text-gray-500 mb-4">You haven't submitted any material requests yet.</p>
                <a href="material-request.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    Create Your First Request
                </a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Site</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($materialRequests as $request): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            Request #<?php echo htmlspecialchars($request['id']); ?>
                                        </div>
                                        <?php if ($request['request_notes']): ?>
                                            <div class="text-sm text-gray-500 max-w-xs truncate" title="<?php echo htmlspecialchars($request['request_notes']); ?>">
                                                <?php echo htmlspecialchars($request['request_notes']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($request['site_id'] ?? 'N/A'); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo htmlspecialchars($request['location'] ?? 'Location not specified'); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <?php echo $request['total_items'] ?? 0; ?> items
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        Total Qty: <?php echo $request['total_quantity'] ?? 0; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $statusColors = [
                                        'draft' => 'bg-gray-100 text-gray-800',
                                        'submitted' => 'bg-blue-100 text-blue-800',
                                        'approved' => 'bg-green-100 text-green-800',
                                        'rejected' => 'bg-red-100 text-red-800',
                                        'partially_fulfilled' => 'bg-yellow-100 text-yellow-800',
                                        'fulfilled' => 'bg-green-100 text-green-800'
                                    ];
                                    $statusColor = $statusColors[$request['status']] ?? 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $statusColor; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $request['status'])); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div>Requested: <?php echo date('M d, Y', strtotime($request['request_date'])); ?></div>
                                    <div>Required: <?php echo date('M d, Y', strtotime($request['required_date'])); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="viewRequest(<?php echo $request['id']; ?>)" 
                                            class="text-blue-600 hover:text-blue-900 mr-3">
                                        View
                                    </button>
                                    <?php if ($request['status'] === 'draft'): ?>
                                        <a href="material-request.php?site_id=<?php echo $request['site_id']; ?>&edit=<?php echo $request['id']; ?>" 
                                           class="text-green-600 hover:text-green-900">
                                            Edit
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- View Request Modal -->
<div id="viewRequestModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 xl:w-1/2 shadow-lg rounded-md bg-white max-h-[80vh] overflow-hidden flex flex-col">
        <div class="flex items-center justify-between pb-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Material Request Details</h3>
            <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeModal('viewRequestModal')">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto mt-4" id="requestDetailsContent">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>

<script>
function viewRequest(requestId) {
    // Show loading state
    document.getElementById('requestDetailsContent').innerHTML = `
        <div class="flex flex-col items-center justify-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-4"></div>
            <span class="text-gray-600 font-medium">Loading request details...</span>
            <span class="text-gray-400 text-sm mt-1">Please wait</span>
        </div>
    `;
    
    // Open modal
    document.getElementById('viewRequestModal').classList.remove('hidden');
    
    // Fetch request details
    fetch(`get-request-details.php?id=${requestId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                displayRequestDetails(data.request);
            } else {
                console.error('API Error:', data);
                document.getElementById('requestDetailsContent').innerHTML = `
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-red-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="text-red-600 mb-2 font-medium">Error loading request details</div>
                        <div class="text-gray-500">${data.message || 'Unknown error occurred'}</div>
                        ${data.debug ? `<div class="text-xs text-gray-400 mt-2">Debug: ${JSON.stringify(data.debug)}</div>` : ''}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            document.getElementById('requestDetailsContent').innerHTML = `
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-red-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="text-red-600 mb-2 font-medium">Network error loading request details</div>
                    <div class="text-gray-500">Please check your connection and try again</div>
                    <div class="text-xs text-gray-400 mt-2">Error: ${error.message}</div>
                </div>
            `;
        });
}

function displayRequestDetails(request) {
    const statusColors = {
        'draft': 'bg-gray-100 text-gray-800',
        'submitted': 'bg-blue-100 text-blue-800',
        'approved': 'bg-green-100 text-green-800',
        'rejected': 'bg-red-100 text-red-800',
        'partially_fulfilled': 'bg-yellow-100 text-yellow-800',
        'fulfilled': 'bg-green-100 text-green-800'
    };
    
    const statusColor = statusColors[request.status] || 'bg-gray-100 text-gray-800';
    
    let itemsHtml = '';
    if (request.items && request.items.length > 0) {
        itemsHtml = request.items.map(item => `
            <tr class="hover:bg-gray-50">
                <td class="py-3 px-4 text-sm text-gray-900">${item.item_name || 'N/A'}</td>
                <td class="py-3 px-4 text-sm text-gray-600">${item.item_code || 'N/A'}</td>
                <td class="py-3 px-4 text-sm text-center font-medium text-gray-900">${item.quantity}</td>
                <td class="py-3 px-4 text-sm text-gray-600">${item.unit || 'N/A'}</td>
                <td class="py-3 px-4 text-sm text-gray-600">${item.notes || '-'}</td>
            </tr>
        `).join('');
    } else {
        itemsHtml = '<tr><td colspan="5" class="py-8 text-center text-gray-500">No items found</td></tr>';
    }
    
    document.getElementById('requestDetailsContent').innerHTML = `
        <!-- Request Information -->
        <div class="bg-gray-50 rounded-lg p-6 mb-6">
            <h4 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">Request Information</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Request ID</label>
                    <p class="text-sm text-gray-900 bg-white p-3 rounded border">#${request.id}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${statusColor}">
                        ${request.status.charAt(0).toUpperCase() + request.status.slice(1).replace('_', ' ')}
                    </span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Site</label>
                    <p class="text-sm text-gray-900 bg-white p-3 rounded border">${request.site_code || request.site_id || 'N/A'}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                    <p class="text-sm text-gray-900 bg-white p-3 rounded border">${request.location || 'N/A'}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Request Date</label>
                    <p class="text-sm text-gray-900 bg-white p-3 rounded border">${new Date(request.request_date).toLocaleDateString()}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Required Date</label>
                    <p class="text-sm text-gray-900 bg-white p-3 rounded border">${new Date(request.required_date).toLocaleDateString()}</p>
                </div>
                ${request.request_notes ? `
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <p class="text-sm text-gray-900 bg-white p-3 rounded border">${request.request_notes}</p>
                </div>
                ` : ''}
            </div>
        </div>
        
        <!-- Material Items -->
        <div class="bg-gray-50 rounded-lg p-6">
            <h4 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">Material Items</h4>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white rounded-lg border border-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Name</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                            <th class="py-3 px-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        ${itemsHtml}
                    </tbody>
                </table>
            </div>
        </div>
    `;
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.add('hidden');
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('viewRequestModal');
    if (event.target === modal) {
        closeModal('viewRequestModal');
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modal = document.getElementById('viewRequestModal');
        if (!modal.classList.contains('hidden')) {
            closeModal('viewRequestModal');
        }
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../includes/vendor_layout.php';
?>