<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../models/Inventory.php';
require_once __DIR__ . '/../../../models/BoqItem.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$inventoryModel = new Inventory();
$boqModel = new BoqItem();

// Get all active BOQ items for dropdown
$boqItems = $boqModel->getAll(['status' => 'active']);

$title = 'Add Individual Stock Items';
ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Add Individual Stock Items</h1>
        <p class="mt-2 text-sm text-gray-700">Add individual items to inventory with unique tracking</p>
    </div>
    <div class="flex space-x-2">
        <a href="../index.php" class="btn btn-secondary">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
            </svg>
            Back to Inventory
        </a>
    </div>
</div>

<!-- Add Stock Form -->
<form id="stockForm">
    <!-- Item Selection -->
    <div class="card mb-6">
        <div class="card-body">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Item Selection</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label for="boq_item_id" class="form-label">BOQ Item *</label>
                    <select id="boq_item_id" name="boq_item_id" class="form-input" required onchange="updateItemDetails()">
                        <option value="">Select an item...</option>
                        <?php foreach ($boqItems as $item): ?>
                        <option value="<?php echo $item['id']; ?>" 
                                data-name="<?php echo htmlspecialchars($item['item_name']); ?>"
                                data-code="<?php echo htmlspecialchars($item['item_code']); ?>"
                                data-unit="<?php echo htmlspecialchars($item['unit']); ?>">
                            <?php echo htmlspecialchars($item['item_name']); ?> (<?php echo htmlspecialchars($item['item_code']); ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="quantity" class="form-label">Quantity to Add *</label>
                    <input type="number" id="quantity" name="quantity" class="form-input" 
                           min="1" value="1" required onchange="updateSerialNumberFields()">
                    <p class="text-xs text-gray-500 mt-1">Each item will be tracked individually</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Item Details -->
    <div class="card mb-6">
        <div class="card-body">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Item Details</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="form-group">
                    <label for="unit_cost" class="form-label">Unit Cost *</label>
                    <input type="number" id="unit_cost" name="unit_cost" class="form-input" 
                           step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="batch_number" class="form-label">Batch Number</label>
                    <input type="text" id="batch_number" name="batch_number" class="form-input" 
                           placeholder="e.g., BATCH2024001">
                </div>
                
                <div class="form-group">
                    <label for="supplier_name" class="form-label">Supplier Name</label>
                    <input type="text" id="supplier_name" name="supplier_name" class="form-input" 
                           placeholder="e.g., Tech Supplies Ltd">
                </div>
                
                <div class="form-group">
                    <label for="purchase_date" class="form-label">Purchase Date</label>
                    <input type="date" id="purchase_date" name="purchase_date" class="form-input" 
                           value="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="warranty_period" class="form-label">Warranty (Months)</label>
                    <input type="number" id="warranty_period" name="warranty_period" class="form-input" 
                           min="0" placeholder="e.g., 12">
                </div>
                
                <div class="form-group">
                    <label for="purchase_order_number" class="form-label">PO Number</label>
                    <input type="text" id="purchase_order_number" name="purchase_order_number" class="form-input" 
                           placeholder="e.g., PO2024001">
                </div>
            </div>
        </div>
    </div>
    
    <!-- Serial Numbers -->
    <div class="card mb-6">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Serial Numbers</h3>
                <div class="flex items-center space-x-2">
                    <label class="text-sm text-gray-600">Auto-generate:</label>
                    <input type="checkbox" id="auto_generate" onchange="toggleAutoGenerate()">
                </div>
            </div>
            
            <!-- Auto-generate options -->
            <div id="auto_generate_options" class="hidden mb-4 p-4 bg-blue-50 rounded-lg">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label for="serial_prefix" class="form-label">Serial Prefix</label>
                        <input type="text" id="serial_prefix" name="serial_prefix" class="form-input" 
                               placeholder="e.g., RACK, PC, CM">
                    </div>
                    <div class="form-group">
                        <label for="start_number" class="form-label">Start Number</label>
                        <input type="number" id="start_number" name="start_number" class="form-input" 
                               value="1" min="1">
                    </div>
                </div>
                <button type="button" onclick="generateSerialNumbers()" class="btn btn-secondary">
                    Generate Serial Numbers
                </button>
            </div>
            
            <!-- Manual serial number entry -->
            <div id="serial_numbers_container">
                <div class="serial-number-row grid grid-cols-12 gap-2 items-center mb-2">
                    <div class="col-span-1 text-sm font-medium text-gray-700">#1</div>
                    <div class="col-span-11">
                        <input type="text" name="serial_numbers[]" class="form-input" 
                               placeholder="Enter serial number (optional)">
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Additional Information -->
    <div class="card mb-6">
        <div class="card-body">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Additional Information</h3>
            
            <div class="form-group">
                <label for="notes" class="form-label">Notes</label>
                <textarea id="notes" name="notes" rows="3" class="form-input" 
                          placeholder="Any additional notes about these items..."></textarea>
            </div>
        </div>
    </div>
    
    <!-- Submit Button -->
    <div class="flex justify-end space-x-4">
        <a href="../index.php" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path>
            </svg>
            Add Stock Items
        </button>
    </div>
</form>

<script>
function updateItemDetails() {
    const select = document.getElementById('boq_item_id');
    const selectedOption = select.options[select.selectedIndex];
    
    if (selectedOption.value) {
        const itemName = selectedOption.dataset.name;
        const itemCode = selectedOption.dataset.code;
        
        // Auto-fill serial prefix with item code
        document.getElementById('serial_prefix').value = itemCode;
    }
}

function updateSerialNumberFields() {
    const quantity = parseInt(document.getElementById('quantity').value) || 1;
    const container = document.getElementById('serial_numbers_container');
    
    // Clear existing fields
    container.innerHTML = '';
    
    // Create new fields
    for (let i = 1; i <= quantity; i++) {
        const row = document.createElement('div');
        row.className = 'serial-number-row grid grid-cols-12 gap-2 items-center mb-2';
        row.innerHTML = `
            <div class="col-span-1 text-sm font-medium text-gray-700">#${i}</div>
            <div class="col-span-11">
                <input type="text" name="serial_numbers[]" class="form-input" 
                       placeholder="Enter serial number (optional)">
            </div>
        `;
        container.appendChild(row);
    }
}

function toggleAutoGenerate() {
    const checkbox = document.getElementById('auto_generate');
    const options = document.getElementById('auto_generate_options');
    
    if (checkbox.checked) {
        options.classList.remove('hidden');
    } else {
        options.classList.add('hidden');
    }
}

function generateSerialNumbers() {
    const prefix = document.getElementById('serial_prefix').value || '';
    const startNumber = parseInt(document.getElementById('start_number').value) || 1;
    const quantity = parseInt(document.getElementById('quantity').value) || 1;
    
    const serialInputs = document.querySelectorAll('input[name="serial_numbers[]"]');
    
    serialInputs.forEach((input, index) => {
        const number = startNumber + index;
        input.value = prefix + number.toString().padStart(3, '0');
    });
}

// Form submission
document.getElementById('stockForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Adding Items...';
    submitBtn.disabled = true;
    
    fetch('process-add-individual-stock.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text();
    })
    .then(text => {
        try {
            const data = JSON.parse(text);
            if (data.success) {
                showAlert(`Success: ${data.message}`, 'success');
                setTimeout(() => {
                    window.location.href = '../index.php';
                }, 1500);
            } else {
                showAlert('Error: ' + data.message, 'error');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        } catch (parseError) {
            console.error('JSON Parse Error:', parseError);
            console.error('Response text:', text);
            showAlert('Invalid response format. Check console for details.', 'error');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Fetch Error:', error);
        showAlert('Network error occurred while adding stock items.', 'error');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

function showAlert(message, type) {
    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = `fixed top-4 right-4 z-50 p-4 rounded-md shadow-lg ${
        type === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 
        'bg-red-100 text-red-800 border border-red-200'
    }`;
    alertDiv.innerHTML = `
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                ${type === 'success' ? 
                    '<path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>' :
                    '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>'
                }
            </svg>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Remove alert after 5 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

// Initialize
updateSerialNumberFields();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../includes/admin_layout.php';
?>
</content>