<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/MaterialRequest.php';
require_once __DIR__ . '/../models/Inventory.php';
require_once __DIR__ . '/../models/BoqItem.php';

// Require vendor authentication
Auth::requireRole(VENDOR_ROLE);

$currentUser = Auth::getCurrentUser();
$vendorId = $currentUser['vendor_id'];

$requestId = $_GET['id'] ?? null;

if (!$requestId) {
    header('Location: material-dispatches.php');
    exit;
}

$materialRequestModel = new MaterialRequest();
$inventoryModel = new Inventory();
$boqModel = new BoqItem();

// Get material request details
$materialRequest = $materialRequestModel->findWithDetails($requestId);

if (!$materialRequest || $materialRequest['vendor_id'] != $vendorId) {
    header('Location: material-dispatches.php');
    exit;
}

// Get dispatch details
$dispatchDetails = $inventoryModel->getDispatchByRequestId($requestId);

if (!$dispatchDetails || ($dispatchDetails['dispatch_status'] !== 'dispatched' && $dispatchDetails['dispatch_status'] !== 'in_transit')) {
    header('Location: view-dispatch.php?id=' . $requestId);
    exit;
}

// Parse requested items
$requestedItems = json_decode($materialRequest['items'], true) ?: [];

// Get BOQ item details for each requested item
$boqItems = [];
foreach ($requestedItems as $item) {
    if (!empty($item['boq_item_id'])) {
        $boqItem = $boqModel->find($item['boq_item_id']);
        if ($boqItem) {
            $boqItems[$item['boq_item_id']] = $boqItem;
        }
    }
}

$title = 'Confirm Delivery - Request #' . $materialRequest['id'];
ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Confirm Delivery</h1>
        <p class="mt-2 text-sm text-gray-700">Confirm receipt of materials for Request #<?php echo $materialRequest['id']; ?></p>
    </div>
    <div class="flex space-x-2">
        <a href="view-dispatch.php?id=<?php echo $materialRequest['id']; ?>" class="btn btn-secondary">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
            </svg>
            Back to Dispatch
        </a>
    </div>
</div>

<!-- Dispatch Summary -->
<div class="card mb-6">
    <div class="card-body">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Dispatch Summary</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="text-sm font-medium text-gray-500">Dispatch Number</label>
                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($dispatchDetails['dispatch_number']); ?></div>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-500">Dispatch Date</label>
                <div class="text-sm text-gray-900"><?php echo date('d M Y', strtotime($dispatchDetails['dispatch_date'])); ?></div>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-500">Courier</label>
                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($dispatchDetails['courier_name'] ?? 'N/A'); ?></div>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-500">POD / Tracking</label>
                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($dispatchDetails['tracking_number'] ?? 'N/A'); ?></div>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-500">Site</label>
                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($materialRequest['site_code']); ?> - <?php echo htmlspecialchars($materialRequest['location']); ?></div>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-500">Expected Delivery</label>
                <div class="text-sm text-gray-900"><?php echo $dispatchDetails['expected_delivery_date'] ? date('d M Y', strtotime($dispatchDetails['expected_delivery_date'])) : 'N/A'; ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Delivery Confirmation Form -->
<form id="deliveryConfirmationForm" enctype="multipart/form-data">
    <input type="hidden" name="request_id" value="<?php echo $materialRequest['id']; ?>">
    <input type="hidden" name="dispatch_id" value="<?php echo $dispatchDetails['id']; ?>">
    
    <!-- Delivery Details -->
    <div class="card mb-6">
        <div class="card-body">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Delivery Details</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label for="delivery_date" class="form-label">Actual Delivery Date *</label>
                    <input type="date" id="delivery_date" name="delivery_date" class="form-input" required 
                           value="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="delivery_time" class="form-label">Delivery Time *</label>
                    <input type="time" id="delivery_time" name="delivery_time" class="form-input" required 
                           value="<?php echo date('H:i'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="received_by" class="form-label">Received By *</label>
                    <input type="text" id="received_by" name="received_by" class="form-input" required 
                           placeholder="Name of person who received the materials">
                </div>
                
                <div class="form-group">
                    <label for="received_by_phone" class="form-label">Receiver Phone</label>
                    <input type="text" id="received_by_phone" name="received_by_phone" class="form-input" 
                           placeholder="Phone number of receiver">
                </div>
                
                <div class="form-group md:col-span-2">
                    <label for="delivery_address" class="form-label">Delivery Address *</label>
                    <textarea id="delivery_address" name="delivery_address" rows="3" class="form-input" required><?php echo htmlspecialchars($materialRequest['address']); ?></textarea>
                </div>
                
                <div class="form-group md:col-span-2">
                    <label for="delivery_notes" class="form-label">Delivery Notes</label>
                    <textarea id="delivery_notes" name="delivery_notes" rows="3" class="form-input" 
                              placeholder="Any additional notes about the delivery..."></textarea>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Material Verification -->
    <div class="card mb-6">
        <div class="card-body">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Material Verification</h3>
            
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Material Details</th>
                            <th>Dispatched Qty</th>
                            <th>Received Qty</th>
                            <th>Condition</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requestedItems as $index => $item): ?>
                        <?php $boqItem = $boqItems[$item['boq_item_id']] ?? null; ?>
                        <tr>
                            <td>
                                <input type="hidden" name="items[<?php echo $index; ?>][boq_item_id]" value="<?php echo $item['boq_item_id']; ?>">
                                <?php if ($boqItem): ?>
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                                <i class="<?php echo $boqItem['icon_class'] ?: 'fas fa-cube'; ?> text-blue-600"></i>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($boqItem['item_name']); ?></div>
                                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($boqItem['item_code']); ?></div>
                                            <div class="text-sm text-gray-500">Unit: <?php echo htmlspecialchars($boqItem['unit']); ?></div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="text-sm text-gray-500">Item not found (ID: <?php echo $item['boq_item_id']; ?>)</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="text-sm font-medium text-gray-900"><?php echo number_format($item['quantity']); ?></div>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($item['unit']); ?></div>
                            </td>
                            <td>
                                <input type="number" name="items[<?php echo $index; ?>][received_quantity]" 
                                       class="form-input w-24" step="0.01" min="0" max="<?php echo $item['quantity']; ?>" 
                                       value="<?php echo $item['quantity']; ?>" required>
                            </td>
                            <td>
                                <select name="items[<?php echo $index; ?>][condition]" class="form-input">
                                    <option value="good">Good</option>
                                    <option value="damaged">Damaged</option>
                                    <option value="partial">Partial</option>
                                    <option value="missing">Missing</option>
                                </select>
                            </td>
                            <td>
                                <textarea name="items[<?php echo $index; ?>][notes]" 
                                          class="form-input w-full" rows="2" 
                                          placeholder="Item notes..."></textarea>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Document Upload -->
    <div class="card mb-6">
        <div class="card-body">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Document Upload</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label for="lr_copy" class="form-label">LR Copy / Delivery Receipt *</label>
                    <input type="file" id="lr_copy" name="lr_copy" class="form-input" required 
                           accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                    <p class="mt-1 text-sm text-gray-500">Upload LR copy or delivery receipt (PDF, Image, or Document)</p>
                </div>
                
                <div class="form-group">
                    <label for="additional_documents" class="form-label">Additional Documents</label>
                    <input type="file" id="additional_documents" name="additional_documents[]" class="form-input" 
                           accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" multiple>
                    <p class="mt-1 text-sm text-gray-500">Upload any additional documents (Optional)</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Submit Button -->
    <div class="flex justify-end space-x-4">
        <a href="view-dispatch.php?id=<?php echo $materialRequest['id']; ?>" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
            </svg>
            Confirm Delivery
        </button>
    </div>
</form>

<script>
// Form submission
document.getElementById('deliveryConfirmationForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Confirming Delivery...';
    submitBtn.disabled = true;
    
    fetch('process-delivery-confirmation.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Delivery confirmed successfully!', 'success');
            setTimeout(() => {
                window.location.href = `view-dispatch.php?id=<?php echo $materialRequest['id']; ?>`;
            }, 1500);
        } else {
            showAlert('Error: ' + data.message, 'error');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while confirming delivery.', 'error');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../includes/vendor_layout.php';
?>