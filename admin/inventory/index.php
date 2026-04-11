<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/Inventory.php';
require_once __DIR__ . '/../../models/BoqItem.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$inventoryModel = new Inventory();
$boqModel = new BoqItem();

// Include Warehouse model
require_once __DIR__ . '/../../models/Warehouse.php';
$warehouseModel = new Warehouse();

// Get inventory statistics
$stats = $inventoryModel->getInventoryStats();

// Handle filters
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$warehouseId = $_GET['warehouse_id'] ?? '';
$lowStock = isset($_GET['low_stock']);

// Get stock overview
$stockItems = $inventoryModel->getStockOverview($search, $category, $lowStock, $warehouseId);
//echo '<pre>';print_r($stockItems);echo '</pre>';die;
$categories = $boqModel->getCategories();
$warehouses = $warehouseModel->getAll('', 'active');

$title = 'Inventory Management';
ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Inventory Management</h1>
        <p class="mt-2 text-sm text-gray-700">Manage stock levels, inwards, dispatches and material tracking</p>
    </div>
    <div class="flex space-x-2">
        <a href="inwards/" class="btn btn-secondary">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
            Inward Receipts
        </a>
        <a href="dispatches/" class="btn btn-secondary">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            Dispatches
        </a>
        <a href="tracking/" class="btn btn-secondary">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
            </svg>
            Material Tracking
        </a>
        <a href="reconciliation/" class="btn btn-secondary">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"></path>
            </svg>
            Reconciliation
        </a>
        <a href="stock-entries/add-individual-stock.php" class="btn btn-primary">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path>
            </svg>
            Add Individual Items
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6 mb-8">
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 2L3 7v11a1 1 0 001 1h12a1 1 0 001-1V7l-7-5zM9 9a1 1 0 012 0v4a1 1 0 11-2 0V9z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Items</dt>
                        <dd class="text-lg font-medium text-gray-900"><?php echo number_format($stats['total_items']); ?></dd>
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
                            <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Value</dt>
                        <dd class="text-lg font-medium text-gray-900">₹<?php echo number_format($stats['total_value'], 2); ?></dd>
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
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Dispatched Items</dt>
                        <dd class="text-lg font-medium text-gray-900"><?php echo number_format($stats['dispatched_quantity'] ?? 0); ?></dd>
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
                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Individual Entries</dt>
                        <dd class="text-lg font-medium text-gray-900"><?php echo number_format($stats['total_entries'] ?? 0); ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-indigo-500 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Pending Dispatches</dt>
                        <dd class="text-lg font-medium text-gray-900"><?php echo number_format($stats['pending_dispatches']); ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search and Filters -->
<div class="card mb-6">
    <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="lg:col-span-2">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <input type="text" id="searchInput" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Search items..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div>
                <select id="warehouseFilter" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">All Warehouses</option>
                    <?php foreach ($warehouses as $wh): ?>
                        <option value="<?php echo $wh['id']; ?>" <?php echo $warehouseId == $wh['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($wh['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <select id="categoryFilter" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="flex items-center">
                    <input type="checkbox" id="lowStockFilter" class="form-checkbox" <?php echo $lowStock ? 'checked' : ''; ?>>
                    <span class="ml-2 text-sm text-gray-700">Show Low Stock Only</span>
                </label>
            </div>
        </div>
    </div>
</div>

<!-- Stock Overview Table -->
<div class="card">
    <div class="card-body">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Stock Overview</h3>
            <a href="stock-entries/" class="btn btn-secondary btn-sm">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" clip-rule="evenodd"></path>
                </svg>
                Individual Entries
            </a>
            <!--<button onclick="openModal('updateStockModal')" class="btn btn-primary btn-sm">-->
            <!--    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">-->
            <!--        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>-->
            <!--    </svg>-->
            <!--    Update Unit Cost-->
            <!--</button>-->
        </div>
        
        <div class="overflow-x-auto">
            <table class="data-table" id="stockTable">
                <thead>
                    <tr>
                        <th>Actions</th>
                        <th>Item Details</th>
                        <th>Category</th>
                        <th>Warehouse</th>
                        <th>Total Stock</th>
                        <th>Available</th>
                        <th>Dispatched</th>
                        <th>Stock Status</th>
                        <th>Unit Cost</th>
                        <th>Total Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stockItems as $item): ?>
                    <tr>
                        
                        
                        <td>
                            <div class="flex items-center space-x-2">
                                <button onclick="viewStockDetails(<?php echo $item['boq_item_id']; ?>)" class="btn btn-sm btn-secondary" title="View Details">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                                <button onclick="updateStockLevels(<?php echo $item['boq_item_id']; ?>,<?php echo number_format($item['available_stock'] ?? 0); ?>)" class="btn btn-sm btn-primary" title="Update Levels">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                        
                        
                        
                        <td>
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                        <!-- <i class="<?php echo $item['icon_class'] ?: 'fas fa-cube'; ?> text-blue-600"></i> -->
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($item['item_name']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($item['item_code']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                <?php echo htmlspecialchars($item['category'] ?: 'Uncategorized'); ?>
                            </span>
                        </td>
                        <td>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm text-gray-900">
                                    <?php 
                                    $warehouseName = isset($item['warehouse_name']) ? $item['warehouse_name'] : 'All Warehouses';
                                    echo htmlspecialchars($warehouseName); 
                                    ?>
                                </span>
                           
                        <td>
                            <div class="text-sm text-gray-900"><?php echo number_format($item['total_stock'] ?? 0); ?> <?php echo htmlspecialchars($item['unit']); ?></div>
                        </td>
                        <td>
                            <div class="text-sm text-gray-900"><?php echo number_format($item['available_stock'] ?? 0); ?> <?php echo htmlspecialchars($item['unit']); ?></div>
                        </td>
                        <td>
                            <div class="text-sm text-gray-900"><?php echo number_format($item['dispatched_stock'] ?? 0); ?> <?php echo htmlspecialchars($item['unit']); ?></div>
                        </td>
                        <td>
                            <?php
                            // Calculate stock status based on available stock
                            $availableStock = $item['available_stock'] ?? 0;
                            $totalStock = $item['total_stock'] ?? 0;
                            
                            if ($totalStock == 0) {
                                $stockStatus = 'empty';
                                $statusClass = 'bg-red-100 text-red-800';
                            } elseif ($availableStock == 0) {
                                $stockStatus = 'out of stock';
                                $statusClass = 'bg-orange-100 text-orange-800';
                            } elseif ($availableStock < ($totalStock * 0.2)) {
                                $stockStatus = 'low';
                                $statusClass = 'bg-yellow-100 text-yellow-800';
                            } else {
                                $stockStatus = 'normal';
                                $statusClass = 'bg-green-100 text-green-800';
                            }
                            ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $statusClass; ?>">
                                <?php echo ucfirst($stockStatus); ?>
                            </span>
                        </td>
                        <td>
                            <div class="text-sm text-gray-900">₹<?php echo number_format($item['avg_unit_cost'] ?? 0, 2); ?></div>
                        </td>
                        <td>
                            <div class="text-sm text-gray-900">₹<?php echo number_format($item['total_value'], 2); ?></div>
                        </td>
                        
                        
                        
                        
                        
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Update Unit Cost Modal -->
<!--<div id="updateStockModal" class="modal">-->
<!--    <div class="modal-content">-->
<!--        <div class="modal-header">-->
<!--            <h3 class="modal-title">Update Unit Cost</h3>-->
<!--            <button type="button" class="modal-close" onclick="closeModal('updateStockModal')">-->
<!--                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">-->
<!--                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>-->
<!--                </svg>-->
<!--            </button>-->
<!--        </div>-->
<!--        <form id="updateStockForm">-->
<!--            <div class="modal-body">-->
<!--                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">-->
<!--                    <div class="form-group">-->
<!--                        <label for="stock_item" class="form-label">Select Item *</label>-->
<!--                        <select id="stock_item" name="boq_item_id" class="form-select" required>-->
<!--                            <option value="">Select Item</option>-->
<!--                            <?php foreach ($stockItems as $item): ?>-->
<!--                                <option value="<?php echo $item['boq_item_id']; ?>">-->
<!--                                    <?php echo htmlspecialchars($item['item_name']); ?> (<?php echo htmlspecialchars($item['item_code']); ?>)-->
<!--                                </option>-->
<!--                            <?php endforeach; ?>-->
<!--                        </select>-->
<!--                    </div>-->
<!--                    <div class="form-group">-->
<!--                        <label for="unit_cost" class="form-label">Unit Cost *</label>-->
<!--                        <input type="number" id="unit_cost" name="unit_cost" step="0.01" class="form-input" required>-->
<!--                    </div>-->
<!--                    <div class="form-group">-->
<!--                        <label for="stock_quantity" class="form-label">Stock Quantity *</label>-->
<!--                         <input type="number" id="stock_quantity" name="stock_quantity" class="form-input" max="1" min="1" step="1" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>-->
<!--                    </div>-->
<!--                    <div class="form-group">-->
<!--                        <label for="notes" class="form-label">Notes</label>-->
<!--                        <textarea id="notes" name="notes" rows="3" class="form-input" -->
<!--                                  placeholder="Any notes about this unit cost update..."></textarea>-->
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->
<!--            <div class="modal-footer">-->
<!--                <button type="button" onclick="closeModal('updateStockModal')" class="btn btn-secondary">Cancel</button>-->
<!--                <button type="submit" class="btn btn-primary">Update Unit Cost</button>-->
<!--            </div>-->
<!--        </form>-->
<!--    </div>-->
<!--</div>-->

<script>
// Search functionality
document.getElementById('searchInput').addEventListener('keyup', debounce(function() {
    applyFilters();
}, 500));

// Filter functionality
document.getElementById('warehouseFilter').addEventListener('change', applyFilters);
document.getElementById('categoryFilter').addEventListener('change', applyFilters);
document.getElementById('lowStockFilter').addEventListener('change', applyFilters);

function applyFilters() {
    const searchTerm = document.getElementById('searchInput').value;
    const warehouseId = document.getElementById('warehouseFilter').value;
    const category = document.getElementById('categoryFilter').value;
    const lowStock = document.getElementById('lowStockFilter').checked;
    
    const url = new URL(window.location);
    
    if (searchTerm) url.searchParams.set('search', searchTerm);
    else url.searchParams.delete('search');
    
    if (warehouseId) url.searchParams.set('warehouse_id', warehouseId);
    else url.searchParams.delete('warehouse_id');
    
    if (category) url.searchParams.set('category', category);
    else url.searchParams.delete('category');
    
    if (lowStock) url.searchParams.set('low_stock', '1');
    else url.searchParams.delete('low_stock');
    
    window.location.href = url.toString();
}

// Stock management functions
function viewStockDetails(boqItemId) {
    window.open(`stock-details.php?item_id=${boqItemId}`, '_blank');
}

function updateStockLevels(boqItemId,stockQty) {
    document.getElementById('stock_item').value = boqItemId;
    document.getElementById('stock_quantity').value = stockQty;
    document.getElementById('stock_quantity').max = stockQty;
    openModal('updateStockModal');
}

document.getElementById('stock_quantity').addEventListener("input", () => {
  if (document.getElementById('stock_quantity').value !== "" && Number(document.getElementById('stock_quantity').value) > Number(document.getElementById('stock_quantity').max)) {
    document.getElementById('stock_quantity').value = document.getElementById('stock_quantity').max;
  }
});

// Update stock form submission
document.getElementById('updateStockForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('update-stock-levels.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Stock levels updated successfully!', 'success');
            closeModal('updateStockModal');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while updating stock levels.', 'error');
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
include __DIR__ . '/../../includes/admin_layout.php';
?>