<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/Inventory.php';
require_once __DIR__ . '/../../models/BoqItem.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$itemId = $_GET['item_id'] ?? null;
if (!$itemId) {
    header('Location: index.php');
    exit;
}

// Pagination parameters
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 20; // Items per page

$inventoryModel = new Inventory();
$boqModel = new BoqItem();

// Get BOQ item details
$boqItem = $boqModel->find($itemId);
if (!$boqItem) {
    header('Location: index.php');
    exit;
}

// Get stock summary (no pagination needed)
$stockSummary = $inventoryModel->getStockSummaryByItem($itemId);

// Get paginated stock details
$offset = ($page - 1) * $perPage;
$allStockDetails = $inventoryModel->getStockDetailsByItem($itemId);
$totalItems = count($allStockDetails);
$totalPages = ceil($totalItems / $perPage);
$stockDetails = array_slice($allStockDetails, $offset, $perPage);

$title = 'Stock Details - ' . $boqItem['item_name'];
ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Stock Details</h1>
        <p class="mt-2 text-sm text-gray-700"><?php echo htmlspecialchars($boqItem['item_name']); ?> (<?php echo htmlspecialchars($boqItem['item_code']); ?>)</p>
    </div>
    <div class="flex space-x-2">
        <a href="index.php" class="btn btn-secondary">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
            </svg>
            Back to Inventory
        </a>
        <a href="stock-entries/add-individual-stock.php?boq_item_id=<?php echo $itemId; ?>" class="btn btn-primary">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path>
            </svg>
            Add Stock
        </a>
    </div>
</div>

<!-- Item Information Card -->
<div class="card mb-6">
    <div class="card-header py-4">
        <div class="flex items-center justify-between">
            <h3 class="card-title">Item Information</h3>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                <?php echo htmlspecialchars($boqItem['category'] ?: 'Uncategorized'); ?>
            </span>
        </div>
    </div>
    <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Item Name</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo htmlspecialchars($boqItem['item_name']); ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Item Code</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded font-mono"><?php echo htmlspecialchars($boqItem['item_code']); ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo htmlspecialchars($boqItem['unit']); ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $boqItem['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                    <?php echo ucfirst($boqItem['status']); ?>
                </span>
            </div>
            <?php if ($boqItem['description']): ?>
            <div class="md:col-span-2 lg:col-span-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo htmlspecialchars($boqItem['description']); ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Stock Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 2L3 7v11a1 1 0 001 1h12a1 1 0 001-1V7l-7-5z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Stock</dt>
                        <dd class="text-lg font-medium text-gray-900"><?php echo number_format($stockSummary['total_stock'] ?? 0); ?> <?php echo htmlspecialchars($boqItem['unit']); ?></dd>
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
                        <dt class="text-sm font-medium text-gray-500 truncate">Available</dt>
                        <dd class="text-lg font-medium text-gray-900"><?php echo number_format($stockSummary['available_stock'] ?? 0); ?> <?php echo htmlspecialchars($boqItem['unit']); ?></dd>
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
                            <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Dispatched</dt>
                        <dd class="text-lg font-medium text-gray-900"><?php echo number_format($stockSummary['dispatched_stock'] ?? 0); ?> <?php echo htmlspecialchars($boqItem['unit']); ?></dd>
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
                            <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Value</dt>
                        <dd class="text-lg font-medium text-gray-900">₹<?php echo number_format($stockSummary['total_value'] ?? 0, 2); ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stock Items Table -->
<div class="card">
    <div class="card-header py-4">
        <div class="flex items-center justify-between">
            <h3 class="card-title">Stock Items</h3>
            <span class="text-sm text-gray-500">
                <?php echo $totalItems > 0 ? "Showing " . ($offset + 1) . "-" . min($offset + $perPage, $totalItems) . " of " . $totalItems . " items" : "No items"; ?>
            </span>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($stockDetails)): ?>
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-2.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 009.586 13H7"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No stock items found</h3>
                <p class="mt-1 text-sm text-gray-500">Add individual stock items to track inventory.</p>
                <div class="mt-6">
                    <a href="stock-entries/add-individual-stock.php?boq_item_id=<?php echo $itemId; ?>" class="btn btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path>
                        </svg>
                        Add First Stock Item
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="w-12">#</th>
                            <th>Serial Number</th>
                            <th>Status</th>
                            <th>Location</th>
                            <th>Unit Cost</th>
                            <th>Purchase Info</th>
                            <th>Quality</th>
                            <th>Warranty</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $serialNumber = $offset + 1;
                        foreach ($stockDetails as $stock): 
                            // Only show available and dispatched items
                            if (!in_array($stock['status'], ['available', 'dispatched'])) {
                                continue;
                            }
                        ?>
                        <tr class="<?php echo ($stock['activity_status'] === 'deleted') ? 'bg-red-50 border-l-4 border-red-500 opacity-70' : ''; ?>">
                            <td class="text-center">
                                <span class="text-sm font-medium text-gray-600"><?php echo $serialNumber++; ?></span>
                            </td>
                            <td>
                                <div>
                                    <?php if ($stock['serial_number']): ?>
                                        <div class="text-sm font-medium text-gray-900 font-mono"><?php echo htmlspecialchars($stock['serial_number']); ?></div>
                                    <?php else: ?>
                                        <div class="text-sm text-gray-500">No Serial</div>
                                    <?php endif; ?>
                                    <?php if ($stock['batch_number']): ?>
                                        <div class="text-xs text-gray-500 mt-1">Batch: <?php echo htmlspecialchars($stock['batch_number']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php
                                $statusClasses = [
                                    'available' => 'bg-green-100 text-green-800',
                                    'dispatched' => 'bg-blue-100 text-blue-800',
                                ];
                                $statusClass = $statusClasses[$stock['status']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $statusClass; ?>">
                                    <?php echo ucfirst($stock['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($stock['location_name'] ?: 'Main Warehouse'); ?></div>
                                <div class="text-xs text-gray-500 mt-1"><?php echo htmlspecialchars($stock['location_type'] ?: 'warehouse'); ?></div>
                            </td>
                            <td>
                                <div class="text-sm font-medium text-gray-900">₹<?php echo number_format($stock['unit_cost'], 2); ?></div>
                            </td>
                            <td>
                                <div>
                                    <?php if ($stock['supplier_name']): ?>
                                        <div class="text-sm text-gray-900"><?php echo htmlspecialchars($stock['supplier_name']); ?></div>
                                    <?php endif; ?>
                                    <?php if ($stock['purchase_date']): ?>
                                        <div class="text-xs text-gray-500 mt-1"><?php echo date('d M Y', strtotime($stock['purchase_date'])); ?></div>
                                    <?php endif; ?>
                                    <?php if ($stock['purchase_order_number']): ?>
                                        <div class="text-xs text-gray-500 mt-1">PO: <?php echo htmlspecialchars($stock['purchase_order_number']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php
                                $qualityClasses = [
                                    'good' => 'bg-green-100 text-green-800',
                                    'damaged' => 'bg-yellow-100 text-yellow-800',
                                    'rejected' => 'bg-red-100 text-red-800'
                                ];
                                $qualityClass = $qualityClasses[$stock['quality_status']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?php echo $qualityClass; ?>">
                                    <?php echo ucfirst($stock['quality_status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($stock['warranty_period']): ?>
                                    <div class="text-sm text-gray-900"><?php echo $stock['warranty_period']; ?> months</div>
                                    <?php if ($stock['purchase_date']): ?>
                                        <?php
                                        $warrantyEnd = date('d M Y', strtotime($stock['purchase_date'] . ' + ' . $stock['warranty_period'] . ' months'));
                                        $isExpired = strtotime($warrantyEnd) < time();
                                        ?>
                                        <div class="text-xs <?php echo $isExpired ? 'text-red-500' : 'text-gray-500'; ?> mt-1">
                                            Until: <?php echo $warrantyEnd; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="text-sm text-gray-500">No warranty</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="flex items-center space-x-2">
                                    <?php if ($stock['status'] === 'available'): ?>
                                        <button onclick="dispatchItem(<?php echo $stock['id']; ?>)" class="btn btn-sm btn-primary" title="Dispatch Item">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3z" clip-rule="evenodd"></path>
                                            </svg>
                                        </button>
                                    <?php endif; ?>
                                    <button onclick="editStockItem(<?php echo $stock['id']; ?>)" class="btn btn-sm btn-secondary" title="Edit Item">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                                        </svg>
                                    </button>
                                    <?php if ($stock['activity_status'] !== 'deleted'): ?>
                                        <button onclick="deleteStockItem(<?php echo $stock['id']; ?>)" class="btn btn-sm btn-secondary" title="Delete Item">
                                            Delete
                                        </button>
                                    <?php else: ?>
                                        <span class="text-xs text-red-600 italic">Deleted</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($totalPages > 1): ?>
            <!-- Pagination -->
            <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 mt-4">
                <div class="flex flex-1 justify-between sm:hidden">
                    <?php if ($page > 1): ?>
                        <a href="?item_id=<?php echo $itemId; ?>&page=<?php echo $page - 1; ?>" class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Previous</a>
                    <?php endif; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="?item_id=<?php echo $itemId; ?>&page=<?php echo $page + 1; ?>" class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Next</a>
                    <?php endif; ?>
                </div>
                <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing <span class="font-medium"><?php echo $offset + 1; ?></span> to <span class="font-medium"><?php echo min($offset + $perPage, $totalItems); ?></span> of <span class="font-medium"><?php echo $totalItems; ?></span> results
                        </p>
                    </div>
                    <div>
                        <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                            <?php if ($page > 1): ?>
                                <a href="?item_id=<?php echo $itemId; ?>&page=<?php echo $page - 1; ?>" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20">
                                    <span class="sr-only">Previous</span>
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                            <?php endif; ?>
                            
                            <?php
                            $startPage = max(1, $page - 2);
                            $endPage = min($totalPages, $page + 2);
                            
                            if ($startPage > 1): ?>
                                <a href="?item_id=<?php echo $itemId; ?>&page=1" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20">1</a>
                                <?php if ($startPage > 2): ?>
                                    <span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300">...</span>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                <a href="?item_id=<?php echo $itemId; ?>&page=<?php echo $i; ?>" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold <?php echo $i === $page ? 'z-10 bg-blue-600 text-white focus:z-20' : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50'; ?>"><?php echo $i; ?></a>
                            <?php endfor; ?>
                            
                            <?php if ($endPage < $totalPages): ?>
                                <?php if ($endPage < $totalPages - 1): ?>
                                    <span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300">...</span>
                                <?php endif; ?>
                                <a href="?item_id=<?php echo $itemId; ?>&page=<?php echo $totalPages; ?>" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20"><?php echo $totalPages; ?></a>
                            <?php endif; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <a href="?item_id=<?php echo $itemId; ?>&page=<?php echo $page + 1; ?>" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20">
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
        <?php endif; ?>
    </div>
</div>

<script>
function dispatchItem(stockId) {
    if (confirm('Are you sure you want to dispatch this item?')) {
        window.location.href = `dispatches/create-dispatch.php?stock_id=${stockId}`;
    }
}

function editStockItem(stockId) {
    window.location.href = `stock-entries/edit-stock.php?id=${stockId}`;
}

function deleteStockItem(stockId) {
    const remark = prompt("Please enter delete remark / reason:");

    if (remark === null || remark.trim() === "") {
        alert("Delete remark is mandatory.");
        return;
    }

    if (!confirm("Are you sure you want to delete this stock item?")) {
        return;
    }

    fetch('delete-stock-item.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'item_id=' + encodeURIComponent(stockId) +
              '&remark=' + encodeURIComponent(remark)
    })
    .then(res => {
        if (!res.ok) throw new Error('HTTP error');
        return res.json();
    })
    .then(data => {
        if (data.success) {
            alert('Stock item deleted successfully');
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(err => {
        console.error(err);
        alert('Server error');
    });
}

// Print functionality
function printStockDetails() {
    window.print();
}

// Add print styles
const printStyles = `
    <style>
        @media print {
            .btn, .card-header, nav, .no-print { display: none !important; }
            .card { border: 1px solid #ddd; margin-bottom: 20px; }
            .card-body { padding: 15px; }
            body { font-size: 12px; }
            .data-table { font-size: 11px; }
            .data-table th, .data-table td { padding: 8px 4px; }
        }
    </style>
`;

document.head.insertAdjacentHTML('beforeend', printStyles);
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>
