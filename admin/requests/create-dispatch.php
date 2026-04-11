<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/MaterialRequest.php';
require_once __DIR__ . '/../../models/BoqItem.php';
require_once __DIR__ . '/../../models/Site.php';
require_once __DIR__ . '/../../models/SiteSurvey.php';
require_once __DIR__ . '/../../models/Inventory.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$requestId = $_GET['id'] ?? null;

if (!$requestId) {
    header('Location: index.php');
    exit;
}

$materialRequestModel = new MaterialRequest();
$boqModel = new BoqItem();
$siteModel = new Site();
$surveyModel = new SiteSurvey();
$inventoryModel = new Inventory();

// Get material request details
$request = $materialRequestModel->findWithDetails($requestId);

if (!$request || $request['status'] !== 'approved') {
    header('Location: index.php');
    exit;
}

// Get site details
$site = $siteModel->findWithRelations($request['site_id']);

// Get survey details if available
$survey = null;
if ($request['survey_id']) {
    $survey = $surveyModel->find($request['survey_id']);
}

// Parse requested items
$requestedItems = json_decode($request['items'], true) ?: [];

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

$title = 'Create Dispatch - Request #' . $request['id'];
ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Create Dispatch</h1>
        <p class="mt-2 text-sm text-gray-700">Create material dispatch for Request #<?php echo $request['id']; ?></p>
    </div>
    <div class="flex space-x-2">
        <a href="view-request.php?id=<?php echo $request['id']; ?>" class="btn btn-secondary">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
            </svg>
            Back to Request
        </a>
    </div>
</div>

<!-- Request Information -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Site Information -->
    <div class="card">
        <div class="card-body">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Site Information</h3>
            <div class="space-y-3">
                <div>
                    <label class="text-sm font-medium text-gray-500">Site ID</label>
                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($site['site_id']); ?></div>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Location</label>
                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($site['location']); ?></div>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Address</label>
                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($site['address'] ?? $site['location']); ?></div>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">City, State</label>
                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($site['city_name'] . ', ' . $site['state_name']); ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Vendor Information -->
    <div class="card">
        <div class="card-body">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Vendor Information</h3>
            <div class="space-y-3">
                <div>
                    <label class="text-sm font-medium text-gray-500">Vendor Name</label>
                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($request['vendor_name']); ?></div>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Request Date</label>
                    <div class="text-sm text-gray-900"><?php echo date('d M Y', strtotime($request['request_date'])); ?></div>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Required Date</label>
                    <div class="text-sm text-gray-900"><?php echo $request['required_date'] ? date('d M Y', strtotime($request['required_date'])) : 'Not specified'; ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Survey Status -->
    <div class="card">
        <div class="card-body">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Survey Status</h3>
            <div class="space-y-3">
                <?php if ($survey): ?>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Survey Status</label>
                        <div class="text-sm text-gray-900">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <?php echo ucfirst($survey['survey_status']); ?>
                            </span>
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Submitted Date</label>
                        <div class="text-sm text-gray-900"><?php echo date('d M Y', strtotime($survey['submitted_date'])); ?></div>
                    </div>
                <?php else: ?>
                    <div class="text-sm text-gray-500">No survey information available</div>
                <?php endif; ?>
                
                <div>
                    <label class="text-sm font-medium text-gray-500">Request Notes</label>
                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($request['request_notes'] ?: 'No notes provided'); ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Dispatch Form -->
<form id="createDispatchForm">
    <input type="hidden" name="material_request_id" value="<?php echo $request['id']; ?>">
    <input type="hidden" name="site_id" value="<?php echo $request['site_id']; ?>">
    <input type="hidden" name="vendor_id" value="<?php echo $request['vendor_id']; ?>">
    
    <!-- Dispatch Details -->
    <div class="card mb-6">
        <div class="card-body">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Dispatch Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label for="dispatch_number" class="form-label">Dispatch Number *</label>
                    <input type="text" id="dispatch_number" name="dispatch_number" class="form-input" required readonly>
                </div>
                <div class="form-group">
                    <label for="dispatch_date" class="form-label">Dispatch Date *</label>
                    <input type="date" id="dispatch_date" name="dispatch_date" class="form-input" required value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="form-group">
                    <label for="contact_person_name" class="form-label">Contact Person Name *</label>
                    <input type="text" id="contact_person_name" name="contact_person_name" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="contact_person_phone" class="form-label">Contact Person Phone *</label>
                    <input type="text" id="contact_person_phone" name="contact_person_phone" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="courier_name" class="form-label">Courier Name</label>
                    <input type="text" id="courier_name" name="courier_name" class="form-input">
                </div>
                <div class="form-group">
                    <label for="tracking_number" class="form-label">POD/Tracking Number</label>
                    <input type="text" id="tracking_number" name="tracking_number" class="form-input">
                </div>
                <div class="form-group md:col-span-2">
                    <label for="delivery_address" class="form-label">Delivery Address *</label>
                    <textarea id="delivery_address" name="delivery_address" rows="3" class="form-input" required><?php echo htmlspecialchars($site['address'] ?? $site['location']); ?></textarea>
                </div>
                <div class="form-group md:col-span-2">
                    <label for="delivery_remarks" class="form-label">Dispatch Remarks</label>
                    <textarea id="delivery_remarks" name="delivery_remarks" rows="3" class="form-input" placeholder="Any special instructions or remarks..."></textarea>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Requested Materials -->
    <div class="card mb-6">
        <div class="card-body">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Requested Materials</h3>
            
            <div class="space-y-4">
                <?php foreach ($requestedItems as $index => $item): ?>
                    <?php $boqItem = $boqItems[$item['boq_item_id']] ?? null; ?>
                    <div class="border rounded-lg p-4 bg-gray-50">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                        <i class="<?php echo $boqItem['icon_class'] ?? 'fas fa-cube'; ?> text-blue-600"></i>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($boqItem['item_name'] ?? 'Unknown Item'); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($item['item_code']); ?></div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-medium text-gray-900">Requested: <?php echo number_format($item['quantity']); ?> <?php echo htmlspecialchars($item['unit']); ?></div>
                                <?php if ($item['notes']): ?>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($item['notes']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Dispatch Details for this item -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mt-4 pt-4 border-t border-gray-200">
                            <input type="hidden" name="items[<?php echo $index; ?>][boq_item_id]" value="<?php echo $item['boq_item_id']; ?>">
                            
                            <div class="form-group">
                                <label class="form-label">Dispatch Quantity *</label>
                                <input type="number" name="items[<?php echo $index; ?>][quantity_dispatched]" 
                                       step="0.01" class="form-input" required 
                                       value="<?php echo $item['quantity']; ?>"
                                       max="<?php echo $item['quantity']; ?>"
                                       onchange="calculateItemTotal(<?php echo $index; ?>)">
                                <small class="text-gray-500">Max: <?php echo $item['quantity']; ?> <?php echo htmlspecialchars($item['unit']); ?></small>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Unit Cost *</label>
                                <input type="number" name="items[<?php echo $index; ?>][unit_cost]" 
                                       step="0.01" class="form-input unit-cost-input" required
                                       onchange="calculateItemTotal(<?php echo $index; ?>)">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Total Cost</label>
                                <input type="number" name="items[<?php echo $index; ?>][total_cost]" 
                                       step="0.01" class="form-input total-cost-input" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Item Condition</label>
                                <select name="items[<?php echo $index; ?>][item_condition]" class="form-select">
                                    <option value="new">New</option>
                                    <option value="used">Used</option>
                                    <option value="refurbished">Refurbished</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Batch Number</label>
                                <input type="text" name="items[<?php echo $index; ?>][batch_number]" class="form-input">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Serial Numbers</label>
                                <input type="text" name="items[<?php echo $index; ?>][serial_numbers]" 
                                       class="form-input" placeholder="Comma separated if multiple">
                                <small class="text-gray-500">Required if item needs serial tracking</small>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Warranty Period</label>
                                <input type="text" name="items[<?php echo $index; ?>][warranty_period]" 
                                       class="form-input" placeholder="e.g., 1 year, 6 months">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Item Remarks</label>
                                <input type="text" name="items[<?php echo $index; ?>][remarks]" 
                                       class="form-input" value="<?php echo htmlspecialchars($item['notes']); ?>">
                            </div>
                        </div>
                        
                        <!-- Individual/Cumulative Toggle -->
                        <?php if ($item['quantity'] > 1): ?>
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="flex items-center space-x-4">
                                <label class="flex items-center">
                                    <input type="radio" name="items[<?php echo $index; ?>][entry_type]" value="cumulative" 
                                           class="form-radio" checked onchange="toggleEntryType(<?php echo $index; ?>)">
                                    <span class="ml-2 text-sm text-gray-700">Cumulative Entry (<?php echo $item['quantity']; ?> items as one)</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="items[<?php echo $index; ?>][entry_type]" value="individual" 
                                           class="form-radio" onchange="toggleEntryType(<?php echo $index; ?>)">
                                    <span class="ml-2 text-sm text-gray-700">Individual Entries (separate tracking)</span>
                                </label>
                            </div>
                            
                            <div id="individual-entries-<?php echo $index; ?>" class="hidden mt-4">
                                <div class="bg-blue-50 p-3 rounded-md">
                                    <p class="text-sm text-blue-700">Individual entries will be created for serial number tracking. You can add serial numbers after dispatch creation.</p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Submit Section -->
    <div class="card">
        <div class="card-body">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Dispatch Summary</h3>
                    <p class="text-sm text-gray-500">Total items: <?php echo count($requestedItems); ?></p>
                    <p class="text-sm text-gray-500">Total value: <span id="totalValue">₹0.00</span></p>
                </div>
                <div class="flex space-x-3">
                    <a href="view-request.php?id=<?php echo $request['id']; ?>" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Create Dispatch
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
// Generate dispatch number on page load
document.addEventListener('DOMContentLoaded', function() {
    generateDispatchNumber();
    calculateTotalValue();
});

function generateDispatchNumber() {
    fetch('../inventory/dispatches/generate-dispatch-number.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('dispatch_number').value = data.dispatch_number;
            }
        })
        .catch(error => console.error('Error generating dispatch number:', error));
}

function calculateItemTotal(itemIndex) {
    const quantityInput = document.querySelector(`input[name="items[${itemIndex}][quantity_dispatched]"]`);
    const unitCostInput = document.querySelector(`input[name="items[${itemIndex}][unit_cost]"]`);
    const totalCostInput = document.querySelector(`input[name="items[${itemIndex}][total_cost]"]`);
    
    const quantity = parseFloat(quantityInput.value) || 0;
    const unitCost = parseFloat(unitCostInput.value) || 0;
    const totalCost = quantity * unitCost;
    
    totalCostInput.value = totalCost.toFixed(2);
    calculateTotalValue();
}

function calculateTotalValue() {
    let totalValue = 0;
    const totalCostInputs = document.querySelectorAll('.total-cost-input');
    
    totalCostInputs.forEach(input => {
        totalValue += parseFloat(input.value) || 0;
    });
    
    document.getElementById('totalValue').textContent = '₹' + totalValue.toFixed(2);
}

function toggleEntryType(itemIndex) {
    const individualDiv = document.getElementById(`individual-entries-${itemIndex}`);
    const entryType = document.querySelector(`input[name="items[${itemIndex}][entry_type]"]:checked`).value;
    
    if (entryType === 'individual') {
        individualDiv.classList.remove('hidden');
    } else {
        individualDiv.classList.add('hidden');
    }
}

// Form submission
document.getElementById('createDispatchForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('process-dispatch.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Dispatch created successfully!', 'success');
            setTimeout(() => {
                window.location.href = 'view-request.php?id=<?php echo $request['id']; ?>';
            }, 1500);
        } else {
            showAlert('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while creating the dispatch.', 'error');
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>