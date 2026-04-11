<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../models/Inventory.php';
require_once __DIR__ . '/../../../models/BoqItem.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$inventoryModel = new Inventory();
$boqModel = new BoqItem();

// Handle filters
$search = $_GET['search'] ?? '';
$boqItemId = $_GET['boq_item_id'] ?? null;
$location = $_GET['location'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 50;

// Get individual stock entries with pagination
$stockData = $inventoryModel->getIndividualStockEntries($boqItemId, $search, $location, $page, $limit);
$stockEntries = $stockData['entries'];
$totalPages = $stockData['pages'];
$totalEntries = $stockData['total'];

// Get BOQ items for filter
$boqItems = $boqModel->getActive();

$title = 'Individual Stock Entries';
ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Individual Stock Entries</h1>
        <p class="mt-2 text-sm text-gray-700">Manage individual inventory items with batch and serial tracking</p>
    </div>
    <div class="flex space-x-2">
        <a href="../" class="btn btn-secondary">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
            </svg>
            Back to Inventory
        </a>
        <button onclick="openModal('addStockEntryModal')" class="btn btn-primary">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
            </svg>
            Add Stock Entry
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
                    <input type="text" id="searchInput" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Search items, batch, serial..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div>
                <select id="boqItemFilter" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">All Items</option>
                    <?php foreach ($boqItems as $item): ?>
                        <option value="<?php echo $item['id']; ?>" <?php echo $boqItemId == $item['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($item['item_name']); ?> (<?php echo htmlspecialchars($item['item_code']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <select id="locationFilter" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">All Locations</option>
                    <option value="warehouse" <?php echo $location === 'warehouse' ? 'selected' : ''; ?>>Warehouse</option>
                    <option value="site" <?php echo $location === 'site' ? 'selected' : ''; ?>>Site</option>
                    <option value="vendor" <?php echo $location === 'vendor' ? 'selected' : ''; ?>>Vendor</option>
                    <option value="in_transit" <?php echo $location === 'in_transit' ? 'selected' : ''; ?>>In Transit</option>
                </select>
            </div>
            <div>
                <button onclick="exportStockEntries()" class="btn btn-secondary w-full">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                    Export
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Stock Entries Table -->
<div class="card">
    <div class="card-body">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Item Details</th>
                        <th>Batch/Serial</th>
                        <th>Stock</th>
                        <th>Location</th>
                        <th>Supplier</th>
                        <th>Quality</th>
                        <th>Dates</th>
                        <th>Value</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($stockEntries)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-8 text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-2.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 009.586 13H7"></path>
                            </svg>
                            <p class="mt-2">No stock entries found</p>
                            <button onclick="openModal('addStockEntryModal')" class="mt-2 btn btn-primary btn-sm">Add First Entry</button>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($stockEntries as $entry): ?>
                        <tr>
                            <td>
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                            <i class="<?php echo $entry['icon_class'] ?: 'fas fa-cube'; ?> text-blue-600"></i>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($entry['item_name']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($entry['item_code']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <?php if ($entry['batch_number']): ?>
                                        <div class="text-sm font-medium text-gray-900">Batch: <?php echo htmlspecialchars($entry['batch_number']); ?></div>
                                    <?php endif; ?>
                                    <?php if ($entry['serial_number']): ?>
                                        <div class="text-sm text-gray-500">Serial: <?php echo htmlspecialchars($entry['serial_number']); ?></div>
                                    <?php endif; ?>
                                    <?php if (!$entry['batch_number'] && !$entry['serial_number']): ?>
                                        <span class="text-sm text-gray-400">No batch/serial</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">1.00 <?php echo htmlspecialchars($entry['unit']); ?></div>
                                    <div class="text-sm text-gray-500">
                                        Status: 
                                        <?php
                                        $statusClasses = [
                                            'available' => 'text-green-600',
                                            'dispatched' => 'text-blue-600',
                                            'delivered' => 'text-purple-600',
                                            'damaged' => 'text-red-600'
                                        ];
                                        $statusClass = $statusClasses[$entry['item_status']] ?? 'text-gray-600';
                                        ?>
                                        <span class="<?php echo $statusClass; ?>"><?php echo ucfirst($entry['item_status']); ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?php echo ucfirst($entry['location_type']); ?>
                                    </span>
                                    <?php if ($entry['location_name']): ?>
                                        <div class="text-sm text-gray-500 mt-1"><?php echo htmlspecialchars($entry['location_name']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($entry['supplier_name'] ?: 'N/A'); ?></div>
                                <?php if ($entry['purchase_date']): ?>
                                    <div class="text-sm text-gray-500">Purchased: <?php echo date('d M Y', strtotime($entry['purchase_date'])); ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $qualityClasses = [
                                    'good' => 'bg-green-100 text-green-800',
                                    'damaged' => 'bg-red-100 text-red-800',
                                    'rejected' => 'bg-gray-100 text-gray-800'
                                ];
                                $qualityClass = $qualityClasses[$entry['quality_status']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $qualityClass; ?>">
                                    <?php echo ucfirst($entry['quality_status']); ?>
                                </span>
                            </td>
                            <td>
                                <div>
                                    <?php if ($entry['purchase_date']): ?>
                                        <div class="text-sm text-gray-900">Purchased: <?php echo date('d M Y', strtotime($entry['purchase_date'])); ?></div>
                                    <?php endif; ?>
                                    <?php if ($entry['expiry_date']): ?>
                                        <div class="text-sm text-gray-500">Expires: <?php echo date('d M Y', strtotime($entry['expiry_date'])); ?></div>
                                    <?php endif; ?>
                                    <?php if (!$entry['purchase_date'] && !$entry['expiry_date']): ?>
                                        <span class="text-sm text-gray-400">No dates</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">₹<?php echo number_format($entry['unit_cost'], 2); ?></div>
                                    <div class="text-sm text-gray-500">Total: ₹<?php echo number_format($entry['unit_cost'], 2); ?></div>
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center space-x-2">
                                    <button onclick="editStockEntry(<?php echo $entry['id']; ?>)" class="btn btn-sm btn-secondary" title="Edit Entry">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                                        </svg>
                                    </button>
                                    <button onclick="moveStockEntry(<?php echo $entry['id']; ?>)" class="btn btn-sm btn-primary" title="Move Location">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                    <?php if ($entry['quality_status'] === 'good'): ?>
                                        <button onclick="markDamaged(<?php echo $entry['id']; ?>)" class="btn btn-sm btn-danger" title="Mark as Damaged">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
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
        
        <?php if ($totalPages > 1): ?>
        <!-- Pagination -->
        <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 mt-4">
            <div class="flex flex-1 justify-between sm:hidden">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&boq_item_id=<?php echo urlencode($boqItemId); ?>&location=<?php echo urlencode($location); ?>" class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Previous</a>
                <?php endif; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&boq_item_id=<?php echo urlencode($boqItemId); ?>&location=<?php echo urlencode($location); ?>" class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Next</a>
                <?php endif; ?>
            </div>
            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing <span class="font-medium"><?php echo (($page - 1) * $limit) + 1; ?></span> to 
                        <span class="font-medium"><?php echo min($page * $limit, $totalEntries); ?></span> of 
                        <span class="font-medium"><?php echo number_format($totalEntries); ?></span> stock entries
                    </p>
                </div>
                <div>
                    <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&boq_item_id=<?php echo urlencode($boqItemId); ?>&location=<?php echo urlencode($location); ?>" 
                               class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                <span class="sr-only">Previous</span>
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        <?php endif; ?>
                        
                        <?php
                        $startPage = max(1, $page - 2);
                        $endPage = min($totalPages, $page + 2);
                        
                        for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&boq_item_id=<?php echo urlencode($boqItemId); ?>&location=<?php echo urlencode($location); ?>" 
                               class="relative inline-flex items-center px-4 py-2 text-sm font-semibold <?php echo $i === $page ? 'bg-blue-600 text-white' : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50'; ?> focus:z-20">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&boq_item_id=<?php echo urlencode($boqItemId); ?>&location=<?php echo urlencode($location); ?>" 
                               class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                <span class="sr-only">Next</span>
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        <?php endif; ?>
                    </nav>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Stock Entry Modal -->
<div id="addStockEntryModal" class="modal">
    <div class="modal-content max-w-4xl">
        <div class="modal-header">
            <h3 class="modal-title">Add Individual Stock Entry</h3>
            <button type="button" class="modal-close" onclick="closeModal('addStockEntryModal')">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <form id="addStockEntryForm">
            <div class="modal-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label for="boq_item_id" class="form-label">BOQ Item *</label>
                        <select id="boq_item_id" name="boq_item_id" class="form-select" required>
                            <option value="">Select Item</option>
                            <?php foreach ($boqItems as $item): ?>
                                <option value="<?php echo $item['id']; ?>">
                                    <?php echo htmlspecialchars($item['item_name']); ?> (<?php echo htmlspecialchars($item['item_code']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="quantity" class="form-label">Quantity *</label>
                        <input type="number" id="quantity" name="quantity" step="0.01" class="form-input" required value="1">
                        <div class="text-xs text-gray-500 mt-1">Note: Each entry represents individual items. For bulk entries, use the bulk stock entry feature.</div>
                    </div>
                    <div class="form-group">
                        <label for="unit_cost" class="form-label">Unit Cost *</label>
                        <input type="number" id="unit_cost" name="unit_cost" step="0.01" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="batch_number" class="form-label">Batch Number</label>
                        <input type="text" id="batch_number" name="batch_number" class="form-input">
                    </div>
                    <div class="form-group">
                        <label for="serial_number" class="form-label">Serial Number</label>
                        <input type="text" id="serial_number" name="serial_number" class="form-input" placeholder="Optional - leave empty for auto-generation">
                        <div class="text-xs text-gray-500 mt-1">
                            For multiple items: Use as base (e.g., "DEVICE123" becomes "DEVICE123-001", "DEVICE123-002", etc.)
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="location_type" class="form-label">Location Type *</label>
                        <select id="location_type" name="location_type" class="form-select" required>
                            <option value="warehouse">Warehouse</option>
                            <option value="site">Site</option>
                            <option value="vendor">Vendor</option>
                            <option value="in_transit">In Transit</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="location_name" class="form-label">Location Name</label>
                        <input type="text" id="location_name" name="location_name" class="form-input" placeholder="e.g., Main Warehouse, Site A">
                    </div>
                    <div class="form-group">
                        <label for="supplier_name" class="form-label">Supplier Name</label>
                        <input type="text" id="supplier_name" name="supplier_name" class="form-input">
                    </div>
                    <div class="form-group">
                        <label for="purchase_date" class="form-label">Purchase Date</label>
                        <input type="date" id="purchase_date" name="purchase_date" class="form-input">
                    </div>
                    <div class="form-group">
                        <label for="expiry_date" class="form-label">Expiry Date</label>
                        <input type="date" id="expiry_date" name="expiry_date" class="form-input">
                    </div>
                    <div class="form-group">
                        <label for="quality_status" class="form-label">Quality Status</label>
                        <select id="quality_status" name="quality_status" class="form-select">
                            <option value="good">Good</option>
                            <option value="damaged">Damaged</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="warranty_period" class="form-label">Warranty Period</label>
                        <input type="text" id="warranty_period" name="warranty_period" class="form-input" placeholder="e.g., 1 year, 6 months">
                    </div>
                    <div class="form-group md:col-span-2">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea id="notes" name="notes" rows="3" class="form-input" placeholder="Additional notes about this stock entry..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('addStockEntryModal')" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Stock Entry</button>
            </div>
        </form>
    </div>
</div>

<script>
// Search functionality
document.getElementById('searchInput').addEventListener('keyup', debounce(function() {
    applyFilters();
}, 500));

document.getElementById('boqItemFilter').addEventListener('change', applyFilters);
document.getElementById('locationFilter').addEventListener('change', applyFilters);

function applyFilters() {
    const searchTerm = document.getElementById('searchInput').value;
    const boqItemId = document.getElementById('boqItemFilter').value;
    const location = document.getElementById('locationFilter').value;
    
    const url = new URL(window.location);
    
    if (searchTerm) url.searchParams.set('search', searchTerm);
    else url.searchParams.delete('search');
    
    if (boqItemId) url.searchParams.set('boq_item_id', boqItemId);
    else url.searchParams.delete('boq_item_id');
    
    if (location) url.searchParams.set('location', location);
    else url.searchParams.delete('location');
    
    window.location.href = url.toString();
}

// Stock entry management functions
function editStockEntry(entryId) {
    // Implementation for editing stock entry
    console.log('Edit stock entry:', entryId);
}

function moveStockEntry(entryId) {
    // Implementation for moving stock entry location
    console.log('Move stock entry:', entryId);
}

function markDamaged(entryId) {
    if (confirm('Mark this stock entry as damaged?')) {
        fetch('update-stock-entry.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                entry_id: entryId,
                quality_status: 'damaged'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Stock entry marked as damaged', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred while updating the entry.', 'error');
        });
    }
}

function exportStockEntries() {
    const params = new URLSearchParams(window.location.search);
    window.open(`export-stock-entries.php?${params.toString()}`, '_blank');
}

// Quantity change handler to show serial number preview
document.getElementById('quantity').addEventListener('input', function() {
    updateSerialNumberPreview();
});

document.getElementById('serial_number').addEventListener('input', function() {
    updateSerialNumberPreview();
});

function updateSerialNumberPreview() {
    const quantity = parseInt(document.getElementById('quantity').value) || 1;
    const serialNumber = document.getElementById('serial_number').value.trim();
    const serialHelp = document.querySelector('#serial_number + .text-xs');
    
    if (quantity > 1) {
        if (serialNumber) {
            serialHelp.innerHTML = `For ${quantity} items: "${serialNumber}-001", "${serialNumber}-002", ... "${serialNumber}-${String(quantity).padStart(3, '0')}"`;
            serialHelp.className = 'text-xs text-blue-600 mt-1';
        } else {
            serialHelp.innerHTML = `For ${quantity} items: Auto-generated serials will be created (e.g., "ITM12_20241203123456-001", "ITM12_20241203123456-002", etc.)`;
            serialHelp.className = 'text-xs text-gray-500 mt-1';
        }
    } else {
        serialHelp.innerHTML = 'For single item: Use exact serial number or leave empty';
        serialHelp.className = 'text-xs text-gray-500 mt-1';
    }
}

// Form submission
document.getElementById('addStockEntryForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('add-stock-entry.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Stock entry added successfully!', 'success');
            closeModal('addStockEntryModal');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while adding the stock entry.', 'error');
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