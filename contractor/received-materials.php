<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../models/Inventory.php';

// Require vendor authentication
Auth::requireVendor();

$vendorId = Auth::getVendorId();

try {
    $inventoryModel = new Inventory();
    
    // Get all received materials for this contractor
    $receivedMaterials = $inventoryModel->getContractorReceivedMaterials($vendorId);
    
    // Get summary statistics
    $totalDispatches = $inventoryModel->getContractorDispatchCount($vendorId);
    $totalItems = $inventoryModel->getContractorTotalItems($vendorId);
    $pendingConfirmations = $inventoryModel->getContractorPendingConfirmations($vendorId);
    
} catch (Exception $e) {
    error_log("Error in received-materials.php: " . $e->getMessage());
    $receivedMaterials = [];
    $totalDispatches = 0;
    $totalItems = 0;
    $pendingConfirmations = 0;
}

$title = 'Received Materials - Inventory Management';
ob_start();
?>

<style>
input, textarea, select {
    border: 1px solid #d1d5db !important;
}
</style>

<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Received Materials</h1>
            <p class="mt-1 text-sm text-gray-600">Manage and track all materials received from dispatches</p>
        </div>
        <div class="flex space-x-3">
            <button onclick="exportInventory()" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
                Export
            </button>
            <a href="dashboard.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                </svg>
                Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a2 2 0 002 2h8a2 2 0 002-2V3a2 2 0 012 2v6h-3a2 2 0 00-2 2v4H6a2 2 0 01-2-2V5zm8 8a2 2 0 012-2h3v4a2 2 0 01-2 2v-1a2 2 0 00-2-2h-1v-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Dispatches</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo $totalDispatches; ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Items</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo $totalItems; ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Pending Confirmations</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo $pendingConfirmations; ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Material Types</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo count(array_unique(array_column($receivedMaterials, 'item_name'))); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Filter & Search</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search Materials</label>
                    <input type="text" id="searchInput" placeholder="Search by item name, dispatch number..." 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select id="statusFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                        <option value="">All Status</option>
                        <option value="delivered">Delivered</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="pending">Pending Confirmation</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                    <input type="date" id="dateFrom" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">To</label>
                    <input type="date" id="dateTo" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                </div>
            </div>
            <div class="mt-4 flex space-x-3">
                <button onclick="applyFilters()" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700">
                    Apply Filters
                </button>
                <button onclick="clearFilters()" class="px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">
                    Clear
                </button>
            </div>
        </div>
    </div>

    <!-- Materials Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Received Materials Inventory</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="materialsTable">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dispatch</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Details</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Serial Numbers</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Condition</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Received Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($receivedMaterials)): ?>
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m13-8V4a1 1 0 00-1-1H7a1 1 0 00-1 1v1m8 0V4a1 1 0 00-1-1H9a1 1 0 00-1 1v1"></path>
                            </svg>
                            <p class="text-lg font-medium">No materials received yet</p>
                            <p class="text-sm">Materials from dispatches will appear here once received</p>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($receivedMaterials as $material): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($material['dispatch_number']); ?></div>
                                <div class="text-xs text-gray-500"><?php echo date('d M Y', strtotime($material['dispatch_date'])); ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($material['item_name']); ?></div>
                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($material['item_code'] ?? 'N/A'); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <span class="font-medium"><?php echo $material['received_quantity']; ?></span>
                                    <span class="text-gray-500">/ <?php echo $material['sent_quantity']; ?></span>
                                    <span class="text-xs text-gray-400"><?php echo $material['unit']; ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <?php if (!empty($material['serial_numbers'])): ?>
                                    <?php 
                                    $serialNumbers = htmlspecialchars($material['serial_numbers']);
                                    $maxLength = 30;
                                    if (strlen($serialNumbers) > $maxLength): 
                                    ?>
                                        <span class="text-sm text-gray-900"><?php echo substr($serialNumbers, 0, $maxLength); ?>...</span>
                                        <button type="button" 
                                                onclick="showSerialModal('<?php echo addslashes($serialNumbers); ?>', '<?php echo addslashes($material['item_name']); ?>')"
                                                class="ml-2 text-blue-600 hover:text-blue-800"
                                                title="View all serial numbers">
                                            <svg class="w-4 h-4 inline" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                            </svg>
                                        </button>
                                    <?php else: ?>
                                        <span class="text-sm text-gray-900"><?php echo $serialNumbers; ?></span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-sm text-gray-400">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php 
                                $condition = $material['received_condition'] ?? $material['item_condition'] ?? 'good';
                                $conditionClass = $condition === 'good' ? 'bg-green-100 text-green-800' : 
                                                ($condition === 'damaged' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800');
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $conditionClass; ?>">
                                    <?php echo ucfirst($condition); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php 
                                $status = $material['confirmation_date'] ? 'confirmed' : 
                                         ($material['delivery_date'] ? 'delivered' : 'pending');
                                $statusClass = $status === 'confirmed' ? 'bg-green-100 text-green-800' : 
                                              ($status === 'delivered' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800');
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $statusClass; ?>">
                                    <?php echo ucfirst($status); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo $material['delivery_date'] ? date('d M Y', strtotime($material['delivery_date'])) : 'Pending'; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <?php if (!$material['confirmation_date']): ?>
                                    <button onclick="confirmReceipt(<?php echo $material['dispatch_id']; ?>, '<?php echo addslashes($material['item_name']); ?>')" 
                                            class="text-green-600 hover:text-green-900" title="Confirm Receipt">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                    <?php endif; ?>
                                    <a href="../admin/inventory/dispatches/view-dispatch.php?id=<?php echo $material['dispatch_id']; ?>" 
                                       class="text-blue-600 hover:text-blue-900" title="View Dispatch">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    </a>
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

<!-- Serial Numbers Modal (reuse from dispatch view) -->
<div id="serialModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900" id="serialModalTitle">Serial Numbers</h3>
                <button type="button" onclick="closeSerialModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
            
            <div class="bg-gray-50 border border-gray-200 rounded-md p-4 max-h-96 overflow-y-auto">
                <table class="w-full text-sm" id="serialTable">
                    <thead class="bg-gray-100 sticky top-0">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Serial Number</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200" id="serialModalContent">
                        <!-- Serial numbers will be displayed here -->
                    </tbody>
                </table>
            </div>
            
            <div class="flex justify-end mt-4">
                <button type="button" onclick="copySerialNumbers()" 
                        class="mr-3 inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z"></path>
                        <path d="M6 3a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2 3 3 0 01-3 3H9a3 3 0 01-3-3z"></path>
                    </svg>
                    Copy
                </button>
                <button type="button" onclick="closeSerialModal()" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentSerialNumbers = '';

// Serial Modal Functions (reused from dispatch view)
function showSerialModal(serialNumbers, itemName) {
    currentSerialNumbers = serialNumbers;
    
    const serialArray = serialNumbers.split(/[,;\n\r]+/)
        .map(s => s.trim())
        .filter(s => s.length > 0);
    
    document.getElementById('serialModalTitle').textContent = 'Serial Numbers - ' + itemName + ' (' + serialArray.length + ' items)';
    
    let tableHTML = '';
    serialArray.forEach((serial, index) => {
        tableHTML += `
            <tr class="${index % 2 === 0 ? 'bg-white' : 'bg-gray-50'}">
                <td class="px-3 py-2 text-gray-900 font-medium">${index + 1}</td>
                <td class="px-3 py-2 text-gray-900 font-mono">${serial}</td>
            </tr>
        `;
    });
    
    document.getElementById('serialModalContent').innerHTML = tableHTML;
    document.getElementById('serialModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeSerialModal() {
    document.getElementById('serialModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function copySerialNumbers() {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(currentSerialNumbers).then(function() {
            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            button.innerHTML = '<svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>Copied!';
            setTimeout(() => {
                button.innerHTML = originalText;
            }, 2000);
        });
    }
}

// Filter and Search Functions
function applyFilters() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    
    const rows = document.querySelectorAll('#materialsTable tbody tr');
    
    rows.forEach(row => {
        if (row.cells.length === 1) return; // Skip empty state row
        
        const itemName = row.cells[1].textContent.toLowerCase();
        const dispatchNumber = row.cells[0].textContent.toLowerCase();
        const status = row.cells[5].textContent.toLowerCase().trim();
        const receivedDate = row.cells[6].textContent.trim();
        
        let show = true;
        
        // Search filter
        if (searchTerm && !itemName.includes(searchTerm) && !dispatchNumber.includes(searchTerm)) {
            show = false;
        }
        
        // Status filter
        if (statusFilter && !status.includes(statusFilter)) {
            show = false;
        }
        
        // Date filters
        if (dateFrom && receivedDate !== 'Pending') {
            const rowDate = new Date(receivedDate);
            const filterDate = new Date(dateFrom);
            if (rowDate < filterDate) show = false;
        }
        
        if (dateTo && receivedDate !== 'Pending') {
            const rowDate = new Date(receivedDate);
            const filterDate = new Date(dateTo);
            if (rowDate > filterDate) show = false;
        }
        
        row.style.display = show ? '' : 'none';
    });
}

function clearFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('dateFrom').value = '';
    document.getElementById('dateTo').value = '';
    applyFilters();
}

// Confirm Receipt Function
function confirmReceipt(dispatchId, itemName) {
    if (confirm(`Confirm receipt of ${itemName}?`)) {
        // Implementation for confirming receipt
        fetch('process-confirm-receipt.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                dispatch_id: dispatchId,
                action: 'confirm_receipt'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while confirming receipt');
        });
    }
}

// Export Function
function exportInventory() {
    window.location.href = 'export-inventory.php';
}

// Real-time search
document.getElementById('searchInput').addEventListener('input', applyFilters);
document.getElementById('statusFilter').addEventListener('change', applyFilters);
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../includes/vendor_layout.php';
?>