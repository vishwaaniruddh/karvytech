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

$title = 'Confirm Material Receipt - Request #' . $materialRequest['id'];
ob_start();
?>

<!-- Compact Header -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-4">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <a href="material-requests-list.php?view=<?php echo $materialRequest['id']; ?>" class="text-blue-600 hover:text-blue-800">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                </svg>
            </a>
            <h1 class="text-xl font-bold text-gray-900">Confirm Receipt - Request #<?php echo $materialRequest['id']; ?></h1>
        </div>
    </div>
</div>

<!-- Compact Info Banner -->
<div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
    <div class="flex items-start">
        <svg class="h-4 w-4 text-blue-400 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
        </svg>
        <div>
            <h3 class="text-sm font-medium text-blue-800">Confirm Material Receipt</h3>
            <p class="text-sm text-blue-700 mt-1">Verify all materials received and their condition. Upload delivery receipt and supporting documents.</p>
        </div>
    </div>
</div>

<!-- Compact Receipt Summary -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-4">
    <div class="px-4 py-3 border-b border-gray-200">
        <h3 class="text-sm font-semibold text-gray-900 flex items-center">
            <svg class="w-4 h-4 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
            </svg>
            Dispatch Summary
        </h3>
    </div>
    <div class="p-4">
        <table class="w-full text-sm">
            <tr class="border-b border-gray-100">
                <td class="py-1.5 text-gray-500 font-medium">Dispatch Number</td>
                <td class="py-1.5 text-gray-900 text-right"><?php echo htmlspecialchars($dispatchDetails['dispatch_number']); ?></td>
                <td class="py-1.5 text-gray-500 font-medium pl-4">Sent Date</td>
                <td class="py-1.5 text-gray-900 text-right"><?php echo date('d M Y', strtotime($dispatchDetails['dispatch_date'])); ?></td>
            </tr>
            <tr class="border-b border-gray-100">
                <td class="py-1.5 text-gray-500 font-medium">Courier</td>
                <td class="py-1.5 text-gray-900 text-right"><?php echo htmlspecialchars($dispatchDetails['courier_name'] ?? 'N/A'); ?></td>
                <td class="py-1.5 text-gray-500 font-medium pl-4">Tracking</td>
                <td class="py-1.5 text-gray-900 text-right"><?php echo htmlspecialchars($dispatchDetails['tracking_number'] ?? 'N/A'); ?></td>
            </tr>
            <tr>
                <td class="py-1.5 text-gray-500 font-medium">Site</td>
                <td class="py-1.5 text-gray-900 text-right" colspan="3"><?php echo htmlspecialchars($materialRequest['site_code']); ?> - <?php echo htmlspecialchars($materialRequest['location']); ?></td>
            </tr>
        </table>
    </div>
</div>

<!-- Delivery Confirmation Form -->
<form id="deliveryConfirmationForm" enctype="multipart/form-data">
    <input type="hidden" name="request_id" value="<?php echo $materialRequest['id']; ?>">
    <input type="hidden" name="dispatch_id" value="<?php echo $dispatchDetails['id']; ?>">
    
    <!-- Compact Receipt Details -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-4">
        <div class="px-4 py-3 border-b border-gray-200">
            <h3 class="text-sm font-semibold text-gray-900 flex items-center">
                <svg class="w-4 h-4 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                Receipt Details
            </h3>
        </div>
        <div class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="delivery_date" class="block text-sm font-medium text-gray-700 mb-1">Receipt Date *</label>
                    <input type="date" id="delivery_date" name="delivery_date" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required 
                           value="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div>
                    <label for="delivery_time" class="block text-sm font-medium text-gray-700 mb-1">Receipt Time *</label>
                    <input type="time" id="delivery_time" name="delivery_time" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required 
                           value="<?php echo date('H:i'); ?>">
                </div>
                
                <div>
                    <label for="received_by" class="block text-sm font-medium text-gray-700 mb-1">Received By *</label>
                    <input type="text" id="received_by" name="received_by" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required 
                           placeholder="Name of person who received">
                </div>
                
                <div>
                    <label for="received_by_phone" class="block text-sm font-medium text-gray-700 mb-1">Contact Phone</label>
                    <input type="text" id="received_by_phone" name="received_by_phone" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                           pattern="[6-9][0-9]{9}" maxlength="10" placeholder="10-digit mobile number">
                    <p class="mt-1 text-xs text-gray-500">10-digit number starting with 6-9</p>
                    <p id="phone_error" class="mt-1 text-xs text-red-600 hidden"></p>
                </div>
                
                <div class="md:col-span-2">
                    <label for="delivery_address" class="block text-sm font-medium text-gray-700 mb-1">Delivery Address *</label>
                    <textarea id="delivery_address" name="delivery_address" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required><?php echo htmlspecialchars($materialRequest['address'] ?? $dispatchDetails['delivery_address'] ?? ''); ?></textarea>
                </div>
                
                <div class="md:col-span-2">
                    <label for="delivery_notes" class="block text-sm font-medium text-gray-700 mb-1">Receipt Notes</label>
                    <textarea id="delivery_notes" name="delivery_notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                              placeholder="Any additional notes about the receipt..."></textarea>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Compact Material Verification -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-4">
        <div class="px-4 py-3 border-b border-gray-200">
            <h3 class="text-sm font-semibold text-gray-900 flex items-center">
                <svg class="w-4 h-4 mr-2 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                Material Verification
            </h3>
            <p class="text-xs text-gray-600 mt-1">Verify each material received and note any discrepancies</p>
        </div>
        <div class="p-4">
            <div class="space-y-3">
                <?php foreach ($requestedItems as $index => $item): ?>
                <?php $boqItem = $boqItems[$item['boq_item_id']] ?? null; ?>
                
                <!-- Compact Material Card -->
                <div class="bg-gray-50 rounded-lg border border-gray-200 p-3">
                    <input type="hidden" name="items[<?php echo $index; ?>][boq_item_id]" value="<?php echo $item['boq_item_id']; ?>">
                    
                    <div class="flex items-start space-x-3">
                        <!-- Material Icon & Info -->
                        <div class="flex-shrink-0">
                            <div class="h-10 w-10 rounded bg-blue-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        
                        <!-- Material Details & Form -->
                        <div class="flex-1">
                            <?php if ($boqItem): ?>
                                <h4 class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($boqItem['item_name']); ?></h4>
                                <p class="text-xs text-gray-500 mt-1">
                                    <span class="font-medium">Code:</span> <?php echo htmlspecialchars($boqItem['item_code']); ?>
                                    <span class="mx-2">•</span>
                                    <span class="font-medium">Unit:</span> <?php echo htmlspecialchars($boqItem['unit']); ?>
                                </p>
                            <?php else: ?>
                                <h4 class="text-sm font-semibold text-gray-900">Unknown Item</h4>
                                <p class="text-xs text-gray-500 mt-1">Item ID: <?php echo $item['boq_item_id']; ?></p>
                            <?php endif; ?>
                            
                            <!-- Compact Quantity & Condition Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mt-3">
                                <!-- Sent Quantity (Read-only) -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Sent</label>
                                    <div class="bg-white border border-gray-300 rounded px-2 py-1">
                                        <span class="text-sm font-semibold text-gray-900"><?php echo number_format($item['quantity']); ?></span>
                                        <span class="text-xs text-gray-500 ml-1"><?php echo htmlspecialchars($item['unit']); ?></span>
                                    </div>
                                </div>
                                
                                <!-- Received Quantity (Editable) -->
                                <div>
                                    <label for="received_qty_<?php echo $index; ?>" class="block text-xs font-medium text-gray-700 mb-1">
                                        Received *
                                    </label>
                                    <input type="number" 
                                           id="received_qty_<?php echo $index; ?>"
                                           name="items[<?php echo $index; ?>][received_quantity]" 
                                           class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                           step="0.01" 
                                           min="0" 
                                           max="<?php echo $item['quantity']; ?>" 
                                           value="<?php echo $item['quantity']; ?>" 
                                           required
                                           onchange="checkQuantityMatch(<?php echo $index; ?>, <?php echo $item['quantity']; ?>)">
                                </div>
                                
                                <!-- Condition -->
                                <div>
                                    <label for="condition_<?php echo $index; ?>" class="block text-xs font-medium text-gray-700 mb-1">
                                        Condition *
                                    </label>
                                    <select id="condition_<?php echo $index; ?>"
                                            name="items[<?php echo $index; ?>][condition]" 
                                            class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="toggleNotesRequired(<?php echo $index; ?>)">
                                        <option value="good">✓ Good</option>
                                        <option value="damaged">⚠ Damaged</option>
                                        <option value="partial">⊘ Partial</option>
                                        <option value="missing">✗ Missing</option>
                                    </select>
                                </div>
                                
                                <!-- Notes -->
                                <div>
                                    <label for="notes_<?php echo $index; ?>" class="block text-xs font-medium text-gray-700 mb-1">
                                        Notes <span id="notes_required_<?php echo $index; ?>" class="text-red-600 hidden">*</span>
                                    </label>
                                    <textarea id="notes_<?php echo $index; ?>"
                                              name="items[<?php echo $index; ?>][notes]" 
                                              class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                              rows="1" 
                                              placeholder="Notes if needed"></textarea>
                                </div>
                            </div>
                            
                            <!-- Quantity Mismatch Warning -->
                            <div id="qty_warning_<?php echo $index; ?>" class="hidden mt-2 bg-yellow-50 border border-yellow-200 rounded p-2">
                                <div class="flex">
                                    <svg class="h-4 w-4 text-yellow-400 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    <p class="text-xs text-yellow-800">Quantity mismatch detected. Please add notes explaining the discrepancy.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Compact Document Upload -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-4">
        <div class="px-4 py-3 border-b border-gray-200">
            <h3 class="text-sm font-semibold text-gray-900">Document Upload</h3>
        </div>
        <div class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="lr_copy" class="block text-sm font-medium text-gray-700 mb-1">LR Copy / Delivery Receipt *</label>
                    <input type="file" id="lr_copy" name="lr_copy" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required 
                           accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                    <p class="mt-1 text-xs text-gray-500">PDF, Image, or Document</p>
                </div>
                
                <div>
                    <label for="additional_documents" class="block text-sm font-medium text-gray-700 mb-1">Additional Documents</label>
                    <input type="file" id="additional_documents" name="additional_documents[]" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                           accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" multiple>
                    <p class="mt-1 text-xs text-gray-500">Optional additional files</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Compact Submit Section -->
    <div class="flex justify-end space-x-3">
        <a href="material-requests-list.php?view=<?php echo $materialRequest['id']; ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors">
            Cancel
        </a>
        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
            </svg>
            Confirm Receipt
        </button>
    </div>
</form>

<script>
// Phone number validation function
function validatePhone(phone) {
    if (!phone || phone.trim() === '') {
        return true; // Optional field
    }
    
    // Remove all non-numeric characters
    const clean = phone.replace(/[^0-9]/g, '');
    
    // Remove country code and leading 0
    const number = clean.replace(/^(91|0)/, '');
    
    // Check if 10 digits starting with 6-9
    return /^[6-9][0-9]{9}$/.test(number);
}

// Real-time phone validation
document.getElementById('received_by_phone').addEventListener('input', function(e) {
    const phone = e.target.value;
    const errorElement = document.getElementById('phone_error');
    
    // Remove non-numeric characters as user types
    e.target.value = phone.replace(/[^0-9]/g, '');
    
    if (phone.length > 0 && !validatePhone(phone)) {
        errorElement.textContent = 'Invalid phone number. Must be 10 digits starting with 6-9';
        errorElement.classList.remove('hidden');
        e.target.classList.add('border-red-500');
    } else {
        errorElement.classList.add('hidden');
        e.target.classList.remove('border-red-500');
    }
});

// Check if received quantity matches sent quantity
function checkQuantityMatch(index, sentQty) {
    const receivedQty = parseFloat(document.getElementById(`received_qty_${index}`).value);
    const warning = document.getElementById(`qty_warning_${index}`);
    const notesField = document.getElementById(`notes_${index}`);
    
    if (receivedQty !== sentQty) {
        warning.classList.remove('hidden');
        notesField.required = true;
        document.getElementById(`notes_required_${index}`).classList.remove('hidden');
    } else {
        warning.classList.add('hidden');
        // Check if condition also requires notes
        const condition = document.getElementById(`condition_${index}`).value;
        if (condition === 'good') {
            notesField.required = false;
            document.getElementById(`notes_required_${index}`).classList.add('hidden');
        }
    }
}

// Toggle notes required based on condition
function toggleNotesRequired(index) {
    const condition = document.getElementById(`condition_${index}`).value;
    const notesField = document.getElementById(`notes_${index}`);
    const notesRequired = document.getElementById(`notes_required_${index}`);
    
    if (condition !== 'good') {
        notesField.required = true;
        notesRequired.classList.remove('hidden');
    } else {
        // Check if quantity mismatch requires notes
        const sentQty = parseFloat(document.getElementById(`received_qty_${index}`).max);
        const receivedQty = parseFloat(document.getElementById(`received_qty_${index}`).value);
        
        if (receivedQty === sentQty) {
            notesField.required = false;
            notesRequired.classList.add('hidden');
        }
    }
}

// Form submission
document.getElementById('deliveryConfirmationForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validate phone if provided
    const phone = document.getElementById('received_by_phone').value;
    if (phone && !validatePhone(phone)) {
        showAlert('Please enter a valid 10-digit phone number starting with 6-9', 'error');
        document.getElementById('received_by_phone').focus();
        return;
    }
    
    const formData = new FormData(this);
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Confirming Receipt...';
    submitBtn.disabled = true;
    
    fetch('process-delivery-confirmation.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Receipt confirmed successfully!', 'success');
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
        showAlert('An error occurred while confirming receipt.', 'error');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../includes/vendor_layout.php';
?>