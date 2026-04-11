<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../models/Inventory.php';
require_once __DIR__ . '/../../../models/BoqItem.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$inventoryModel = new Inventory();
$boqModel = new BoqItem();

// Handle pagination and filters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';

// Get inward receipts
$receiptsData = $inventoryModel->getInwardReceipts($page, $limit, $search, $status);
$receipts = $receiptsData['receipts'];
$totalPages = $receiptsData['pages'];

$title = 'Inward Receipts';
ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Inward Receipts</h1>
        <p class="mt-2 text-sm text-gray-700">Manage material receipts and stock inwards</p>
    </div>
    <div class="flex space-x-2">
        <a href="../" class="btn btn-secondary">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
            </svg>
            Back to Inventory
        </a>
        <button onclick="openModal('createInwardModal')" class="btn btn-primary">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
            </svg>
            New Inward Receipt
        </button>
    </div>
</div>

<!-- Search and Filters -->
<div class="card mb-6">
    <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <input type="text" id="searchInput" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Search receipts..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div>
                <select id="statusFilter" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">All Status</option>
                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="verified" <?php echo $status === 'verified' ? 'selected' : ''; ?>>Verified</option>
                    <option value="rejected" <?php echo $status === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Inward Receipts Table -->
<div class="card">
    <div class="card-body">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Receipt Details</th>
                        <th>Supplier</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Received By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($receipts)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-8 text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-2.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 009.586 13H7"></path>
                            </svg>
                            <p class="mt-2">No inward receipts found</p>
                            <button onclick="openModal('createInwardModal')" class="mt-2 btn btn-primary btn-sm">Create First Receipt</button>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($receipts as $receipt): ?>
                        <tr>
                            <td>
                                <div>
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($receipt['receipt_number']); ?></div>
                                    <?php if ($receipt['invoice_number']): ?>
                                        <div class="text-sm text-gray-500">Invoice: <?php echo htmlspecialchars($receipt['invoice_number']); ?></div>
                                    <?php endif; ?>
                                    <?php if ($receipt['purchase_order_number']): ?>
                                        <div class="text-sm text-gray-500">PO: <?php echo htmlspecialchars($receipt['purchase_order_number']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($receipt['supplier_name']); ?></div>
                                    <?php if ($receipt['supplier_contact']): ?>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($receipt['supplier_contact']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="text-sm text-gray-900"><?php echo date('d M Y', strtotime($receipt['receipt_date'])); ?></div>
                                <?php if ($receipt['invoice_date']): ?>
                                    <div class="text-sm text-gray-500">Inv: <?php echo date('d M Y', strtotime($receipt['invoice_date'])); ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <?php echo $receipt['item_count']; ?> items
                                </span>
                            </td>
                            <td>
                                <div class="text-sm font-medium text-gray-900">₹<?php echo number_format($receipt['total_amount'], 2); ?></div>
                            </td>
                            <td>
                                <?php
                                $statusClasses = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'verified' => 'bg-green-100 text-green-800',
                                    'rejected' => 'bg-red-100 text-red-800'
                                ];
                                $statusClass = $statusClasses[$receipt['status']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $statusClass; ?>">
                                    <?php echo ucfirst($receipt['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($receipt['received_by_name']); ?></div>
                                <div class="text-sm text-gray-500"><?php echo date('d M Y', strtotime($receipt['created_at'])); ?></div>
                            </td>
                            <td>
                                <div class="flex items-center space-x-2">
                                    <button onclick="viewReceipt(<?php echo $receipt['id']; ?>)" class="btn btn-sm btn-secondary" title="View Details">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                    <?php if ($receipt['status'] === 'pending'): ?>
                                        <button onclick="verifyReceipt(<?php echo $receipt['id']; ?>)" class="btn btn-sm btn-success" title="Verify Receipt">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                        </button>
                                    <?php endif; ?>
                                    <button onclick="printReceipt(<?php echo $receipt['id']; ?>)" class="btn btn-sm btn-secondary" title="Print Receipt">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
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
                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>" class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Previous</a>
                <?php endif; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>" class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Next</a>
                <?php endif; ?>
            </div>
            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing <span class="font-medium"><?php echo (($page - 1) * $limit) + 1; ?></span> to 
                        <span class="font-medium"><?php echo min($page * $limit, $receiptsData['total']); ?></span> of 
                        <span class="font-medium"><?php echo $receiptsData['total']; ?></span> results
                    </p>
                </div>
                <div>
                    <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>" 
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

<!-- Create Inward Receipt Modal -->
<div id="createInwardModal" class="modal">
    <div class="modal-content max-w-4xl">
        <div class="modal-header">
            <h3 class="modal-title">Create Inward Receipt</h3>
            <button type="button" class="modal-close" onclick="closeModal('createInwardModal')">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <form id="createInwardForm">
            <div class="modal-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="form-group">
                        <label for="receipt_number" class="form-label">Receipt Number *</label>
                        <input type="text" id="receipt_number" name="receipt_number" class="form-input" required readonly>
                    </div>
                    <div class="form-group">
                        <label for="receipt_date" class="form-label">Receipt Date *</label>
                        <input type="date" id="receipt_date" name="receipt_date" class="form-input" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="supplier_name" class="form-label">Supplier Name *</label>
                        <input type="text" id="supplier_name" name="supplier_name" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="supplier_contact" class="form-label">Supplier Contact</label>
                        <input type="text" id="supplier_contact" name="supplier_contact" class="form-input">
                    </div>
                    <div class="form-group">
                        <label for="purchase_order_number" class="form-label">Purchase Order Number</label>
                        <input type="text" id="purchase_order_number" name="purchase_order_number" class="form-input">
                    </div>
                    <div class="form-group">
                        <label for="invoice_number" class="form-label">Invoice Number</label>
                        <input type="text" id="invoice_number" name="invoice_number" class="form-input">
                    </div>
                    <div class="form-group">
                        <label for="invoice_date" class="form-label">Invoice Date</label>
                        <input type="date" id="invoice_date" name="invoice_date" class="form-input">
                    </div>
                    <div class="form-group">
                        <label for="total_amount" class="form-label">Total Amount</label>
                        <input type="number" id="total_amount" name="total_amount" step="0.01" class="form-input" readonly>
                    </div>
                </div>
                
                <div class="form-group mb-4">
                    <label for="remarks" class="form-label">Remarks</label>
                    <textarea id="remarks" name="remarks" rows="3" class="form-input"></textarea>
                </div>
                
                <!-- Items Section -->
                <div class="border-t pt-4">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-lg font-medium text-gray-900">Receipt Items</h4>
                        <button type="button" onclick="addReceiptItem()" class="btn btn-sm btn-primary">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                            </svg>
                            Add Item
                        </button>
                    </div>
                    
                    <div id="receiptItems">
                        <!-- Items will be added dynamically -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('createInwardModal')" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Receipt</button>
            </div>
        </form>
    </div>
</div>

<script>
// Search functionality
document.getElementById('searchInput').addEventListener('keyup', debounce(function() {
    applyFilters();
}, 500));

document.getElementById('statusFilter').addEventListener('change', applyFilters);

function applyFilters() {
    const searchTerm = document.getElementById('searchInput').value;
    const status = document.getElementById('statusFilter').value;
    
    const url = new URL(window.location);
    
    if (searchTerm) url.searchParams.set('search', searchTerm);
    else url.searchParams.delete('search');
    
    if (status) url.searchParams.set('status', status);
    else url.searchParams.delete('status');
    
    url.searchParams.delete('page'); // Reset to first page
    
    window.location.href = url.toString();
}

// Receipt management functions
function viewReceipt(receiptId) {
    window.open(`view-receipt.php?id=${receiptId}`, '_blank');
}

function verifyReceipt(receiptId) {
    if (confirm('Are you sure you want to verify this receipt?')) {
        fetch('verify-receipt.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ receipt_id: receiptId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Receipt verified successfully!', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred while verifying the receipt.', 'error');
        });
    }
}

function printReceipt(receiptId) {
    window.open(`print-receipt.php?id=${receiptId}`, '_blank');
}

// Generate receipt number when modal opens
document.addEventListener('DOMContentLoaded', function() {
    generateReceiptNumber();
});

function generateReceiptNumber() {
    fetch('generate-receipt-number.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('receipt_number').value = data.receipt_number;
            }
        })
        .catch(error => console.error('Error generating receipt number:', error));
}

// Receipt items management
let itemCounter = 0;

function addReceiptItem() {
    itemCounter++;
    const itemsContainer = document.getElementById('receiptItems');
    
    const itemDiv = document.createElement('div');
    itemDiv.className = 'receipt-item border rounded-lg p-4 mb-4';
    itemDiv.id = `item-${itemCounter}`;
    
    itemDiv.innerHTML = `
        <div class="flex justify-between items-center mb-3">
            <h5 class="text-sm font-medium text-gray-900">Item ${itemCounter}</h5>
            <button type="button" onclick="removeReceiptItem(${itemCounter})" class="text-red-600 hover:text-red-800">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="form-group">
                <label class="form-label">BOQ Item *</label>
                <select name="items[${itemCounter}][boq_item_id]" class="form-select boq-item-select" required onchange="updateItemDetails(this, ${itemCounter})">
                    <option value="">Select Item</option>
                    <?php
                    $boqItems = $boqModel->getAllItems();
                    foreach ($boqItems as $item):
                    ?>
                        <option value="<?php echo $item['id']; ?>" data-unit="<?php echo htmlspecialchars($item['unit']); ?>">
                            <?php echo htmlspecialchars($item['item_name']); ?> (<?php echo htmlspecialchars($item['item_code']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Quantity *</label>
                <input type="number" name="items[${itemCounter}][quantity_received]" step="0.01" class="form-input quantity-input" required onchange="calculateItemTotal(${itemCounter})">
                <small class="text-gray-500 unit-display"></small>
            </div>
            <div class="form-group">
                <label class="form-label">Unit Cost *</label>
                <input type="number" name="items[${itemCounter}][unit_cost]" step="0.01" class="form-input unit-cost-input" required onchange="calculateItemTotal(${itemCounter})">
            </div>
            <div class="form-group">
                <label class="form-label">Total Cost</label>
                <input type="number" name="items[${itemCounter}][total_cost]" step="0.01" class="form-input total-cost-input" readonly>
            </div>
            <div class="form-group">
                <label class="form-label">Batch Number</label>
                <input type="text" name="items[${itemCounter}][batch_number]" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Quality Status</label>
                <select name="items[${itemCounter}][quality_status]" class="form-select">
                    <option value="good">Good</option>
                    <option value="damaged">Damaged</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Expiry Date</label>
                <input type="date" name="items[${itemCounter}][expiry_date]" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Remarks</label>
                <input type="text" name="items[${itemCounter}][remarks]" class="form-input">
            </div>
        </div>
    `;
    
    itemsContainer.appendChild(itemDiv);
}

function removeReceiptItem(itemId) {
    const itemDiv = document.getElementById(`item-${itemId}`);
    if (itemDiv) {
        itemDiv.remove();
        calculateTotalAmount();
    }
}

function updateItemDetails(selectElement, itemId) {
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const unit = selectedOption.getAttribute('data-unit');
    const unitDisplay = document.querySelector(`#item-${itemId} .unit-display`);
    
    if (unitDisplay && unit) {
        unitDisplay.textContent = `Unit: ${unit}`;
    }
}

function calculateItemTotal(itemId) {
    const quantityInput = document.querySelector(`#item-${itemId} .quantity-input`);
    const unitCostInput = document.querySelector(`#item-${itemId} .unit-cost-input`);
    const totalCostInput = document.querySelector(`#item-${itemId} .total-cost-input`);
    
    const quantity = parseFloat(quantityInput.value) || 0;
    const unitCost = parseFloat(unitCostInput.value) || 0;
    const totalCost = quantity * unitCost;
    
    totalCostInput.value = totalCost.toFixed(2);
    calculateTotalAmount();
}

function calculateTotalAmount() {
    let totalAmount = 0;
    const totalCostInputs = document.querySelectorAll('.total-cost-input');
    
    totalCostInputs.forEach(input => {
        totalAmount += parseFloat(input.value) || 0;
    });
    
    document.getElementById('total_amount').value = totalAmount.toFixed(2);
}

// Form submission
document.getElementById('createInwardForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('create-inward.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Inward receipt created successfully!', 'success');
            closeModal('createInwardModal');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while creating the receipt.', 'error');
    });
});

// Add first item by default
addReceiptItem();

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