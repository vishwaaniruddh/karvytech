<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/MaterialRequest.php';
require_once __DIR__ . '/../../models/BoqItem.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$requestId = $_GET['id'] ?? null;

if (!$requestId) {
    header('Location: index.php');
    exit;
}

$materialRequestModel = new MaterialRequest();
$boqModel = new BoqItem();

// Get request details
$request = $materialRequestModel->findWithDetails($requestId);

if (!$request) {
    header('Location: index.php');
    exit;
}

// Parse items
$items = json_decode($request['items'], true) ?: [];

// Get BOQ item details for each item
$boqItems = [];
foreach ($items as $item) {
    if (!empty($item['boq_item_id'])) {
        $boqItem = $boqModel->find($item['boq_item_id']);
        if ($boqItem) {
            $boqItems[$item['boq_item_id']] = $boqItem;
        }
    }
}

$title = 'Material Request #' . $request['id'];
ob_start();
?>

<style>
.edit-mode input.form-input {
    padding: 0.375rem 0.5rem;
    font-size: 0.875rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    width: 100%;
}
.edit-mode input.form-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}
tr[data-removed="true"] {
    opacity: 0.5;
    text-decoration: line-through;
}
.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}
</style>


<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Material Request #<?php echo $request['id']; ?></h1>
        <p class="mt-2 text-sm text-gray-700">View material request details</p>
    </div>
    <div class="flex space-x-2">
        <a href="index.php" class="btn btn-secondary">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
            </svg>
            Back to Requests
        </a>
        <?php if ($request['status'] === 'pending'): ?>
            <button onclick="approveRequest(<?php echo $request['id']; ?>)" class="btn btn-success">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
                Approve Request
            </button>
            <button onclick="rejectRequest(<?php echo $request['id']; ?>)" class="btn btn-danger">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
                Reject Request
            </button>
        <?php endif; ?>
        <?php if (in_array($request['status'], ['pending', 'approved'])): ?>
            <button onclick="toggleEditMode()" id="editItemsBtn" class="btn btn-warning">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                </svg>
                Edit Items
            </button>
        <?php endif; ?>
        <?php if ($request['status'] === 'approved'): ?>
            <a href="dispatch-material.php?request_id=<?php echo $request['id']; ?>" class="btn btn-primary">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                Create Dispatch
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Request Details -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Main Details -->
    <div class="lg:col-span-2">
        <div class="card">
            <div class="card-body">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Request Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Site</label>
                        <div class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($request['site_code']); ?></div>
                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($request['location']); ?></div>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500">Vendor</label>
                        <div class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($request['vendor_name']); ?></div>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500">Request Date</label>
                        <div class="mt-1 text-sm text-gray-900"><?php echo date('d M Y', strtotime($request['request_date'])); ?></div>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500">Required Date</label>
                        <div class="mt-1 text-sm text-gray-900">
                            <?php echo $request['required_date'] ? date('d M Y', strtotime($request['required_date'])) : 'Not specified'; ?>
                        </div>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500">Status</label>
                        <div class="mt-1">
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
                        </div>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500">Created</label>
                        <div class="mt-1 text-sm text-gray-900"><?php echo date('d M Y H:i', strtotime($request['created_date'])); ?></div>
                    </div>
                </div>
                
                <?php if ($request['request_notes']): ?>
                <div class="mt-4">
                    <label class="text-sm font-medium text-gray-500">Notes</label>
                    <div class="mt-1 text-sm text-gray-900"><?php echo nl2br(htmlspecialchars($request['request_notes'])); ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Status Timeline -->
    <div>
        <div class="card">
            <div class="card-body">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Status Timeline</h3>
                
                <div class="flow-root">
                    <ul class="-mb-8">
                        <li>
                            <div class="relative pb-8">
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                                            </svg>
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1 pt-1.5">
                                        <div>
                                            <p class="text-sm text-gray-500">Request created</p>
                                            <p class="text-sm text-gray-900"><?php echo date('d M Y H:i', strtotime($request['created_date'])); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        
                        <?php if ($request['processed_date']): ?>
                        <li>
                            <div class="relative pb-8">
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full <?php echo $request['status'] === 'approved' ? 'bg-green-500' : 'bg-red-500'; ?> flex items-center justify-center ring-8 ring-white">
                                            <?php if ($request['status'] === 'approved'): ?>
                                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                            <?php else: ?>
                                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                </svg>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1 pt-1.5">
                                        <div>
                                            <p class="text-sm text-gray-500">Request <?php echo $request['status']; ?></p>
                                            <p class="text-sm text-gray-900"><?php echo date('d M Y H:i', strtotime($request['processed_date'])); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Requested Items -->
<div class="card">
    <div class="card-body">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Requested Items</h3>
            <div id="editModeActions" class="hidden space-x-2">
                <button onclick="saveItemChanges()" class="btn btn-success btn-sm">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    Save Changes
                </button>
                <button onclick="cancelEditMode()" class="btn btn-secondary btn-sm">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                    Cancel
                </button>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="data-table" id="itemsTable">
                <thead>
                    <tr>
                        <th>Item Details</th>
                        <th>Item Code</th>
                        <th>Quantity</th>
                        <th>Unit</th>
                        <th>Notes</th>
                        <th class="edit-mode-column hidden">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-gray-500">No items found</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($items as $index => $item): ?>
                        <?php 
                            // Check if this is a BOQ-based request or installation material request
                            $isBoqRequest = isset($item['boq_item_id']);
                            $itemName = $isBoqRequest 
                                ? (isset($boqItems[$item['boq_item_id']]) ? $boqItems[$item['boq_item_id']]['item_name'] : 'Item not found')
                                : ($item['material_name'] ?? 'Unknown Material');
                            $itemCode = $item['item_code'] ?? 'N/A';
                            $quantity = $item['quantity'] ?? 0;
                            $unit = $item['unit'] ?? 'units';
                            $notes = $item['notes'] ?? $item['reason'] ?? '';
                            $currentStock = $item['current_stock'] ?? null;
                        ?>
                        <tr data-index="<?php echo $index; ?>" data-boq-id="<?php echo $item['boq_item_id'] ?? ''; ?>">
                            <td>
                                <?php if ($isBoqRequest && isset($boqItems[$item['boq_item_id']])): ?>
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                                <i class="<?php echo $boqItems[$item['boq_item_id']]['icon_class'] ?: 'fas fa-cube'; ?> text-blue-600"></i>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($boqItems[$item['boq_item_id']]['item_name']); ?></div>
                                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($boqItems[$item['boq_item_id']]['description'] ?? ''); ?></div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-lg bg-orange-100 flex items-center justify-center">
                                                <i class="fas fa-tools text-orange-600"></i>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($itemName); ?></div>
                                            <?php if ($currentStock !== null): ?>
                                                <div class="text-sm text-gray-500">Current Stock: <?php echo $currentStock; ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="text-sm text-gray-900 view-mode"><?php echo htmlspecialchars($itemCode); ?></div>
                                <input type="text" class="form-input text-sm edit-mode hidden" value="<?php echo htmlspecialchars($itemCode); ?>" data-field="item_code">
                            </td>
                            <td>
                                <div class="text-sm font-medium text-gray-900 view-mode"><?php echo number_format($quantity); ?></div>
                                <input type="number" class="form-input text-sm edit-mode hidden" value="<?php echo $quantity; ?>" data-field="quantity" min="1" step="1">
                            </td>
                            <td>
                                <div class="text-sm text-gray-900 view-mode"><?php echo htmlspecialchars($unit); ?></div>
                                <input type="text" class="form-input text-sm edit-mode hidden" value="<?php echo htmlspecialchars($unit); ?>" data-field="unit">
                            </td>
                            <td>
                                <div class="text-sm text-gray-900 view-mode"><?php echo htmlspecialchars($notes); ?></div>
                                <input type="text" class="form-input text-sm edit-mode hidden" value="<?php echo htmlspecialchars($notes); ?>" data-field="notes">
                            </td>
                            <td class="edit-mode-column hidden">
                                <button onclick="removeItem(<?php echo $index; ?>)" class="text-red-600 hover:text-red-900" title="Remove item">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
let isEditMode = false;
let originalItems = [];

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

function toggleEditMode() {
    isEditMode = !isEditMode;
    
    if (isEditMode) {
        // Store original values
        originalItems = [];
        document.querySelectorAll('#itemsTable tbody tr').forEach(row => {
            const index = row.dataset.index;
            if (index !== undefined) {
                const item = {
                    boq_item_id: row.dataset.boqId,
                    item_code: row.querySelector('[data-field="item_code"]').value,
                    quantity: row.querySelector('[data-field="quantity"]').value,
                    unit: row.querySelector('[data-field="unit"]').value,
                    notes: row.querySelector('[data-field="notes"]').value
                };
                originalItems.push(item);
            }
        });
        
        // Show edit mode
        document.querySelectorAll('.view-mode').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.edit-mode').forEach(el => el.classList.remove('hidden'));
        document.querySelectorAll('.edit-mode-column').forEach(el => el.classList.remove('hidden'));
        document.getElementById('editModeActions').classList.remove('hidden');
        document.getElementById('editItemsBtn').classList.add('hidden');
    } else {
        cancelEditMode();
    }
}

function cancelEditMode() {
    isEditMode = false;
    
    // Hide edit mode
    document.querySelectorAll('.view-mode').forEach(el => el.classList.remove('hidden'));
    document.querySelectorAll('.edit-mode').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.edit-mode-column').forEach(el => el.classList.add('hidden'));
    document.getElementById('editModeActions').classList.add('hidden');
    document.getElementById('editItemsBtn').classList.remove('hidden');
    
    // Restore original values
    if (originalItems.length > 0) {
        document.querySelectorAll('#itemsTable tbody tr').forEach((row, index) => {
            if (originalItems[index]) {
                row.querySelector('[data-field="item_code"]').value = originalItems[index].item_code;
                row.querySelector('[data-field="quantity"]').value = originalItems[index].quantity;
                row.querySelector('[data-field="unit"]').value = originalItems[index].unit;
                row.querySelector('[data-field="notes"]').value = originalItems[index].notes;
            }
        });
    }
}

function removeItem(index) {
    if (confirm('Are you sure you want to remove this item?')) {
        const row = document.querySelector(`#itemsTable tbody tr[data-index="${index}"]`);
        if (row) {
            row.style.opacity = '0.5';
            row.dataset.removed = 'true';
        }
    }
}

function saveItemChanges() {
    const items = [];
    let hasError = false;
    
    document.querySelectorAll('#itemsTable tbody tr').forEach(row => {
        const index = row.dataset.index;
        if (index !== undefined && row.dataset.removed !== 'true') {
            const quantity = parseFloat(row.querySelector('[data-field="quantity"]').value);
            
            if (!quantity || quantity <= 0) {
                hasError = true;
                showAlert('Please enter valid quantities for all items', 'error');
                return;
            }
            
            const item = {
                boq_item_id: row.dataset.boqId,
                item_code: row.querySelector('[data-field="item_code"]').value,
                quantity: quantity,
                unit: row.querySelector('[data-field="unit"]').value,
                notes: row.querySelector('[data-field="notes"]').value
            };
            items.push(item);
        }
    });
    
    if (hasError) return;
    
    if (items.length === 0) {
        showAlert('Cannot save request with no items', 'error');
        return;
    }
    
    // Send update request
    fetch('update-request-items.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            request_id: <?php echo $request['id']; ?>,
            items: items
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Items updated successfully!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while updating items.', 'error');
    });
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>