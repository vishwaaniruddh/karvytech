<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../models/Inventory.php';
require_once __DIR__ . '/../../../models/BoqItem.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$inventoryModel = new Inventory();
$boqModel = new BoqItem();

// Get stock entry ID
$stockId = $_GET['id'] ?? null;

if (!$stockId) {
    header('Location: index.php');
    exit;
}

// Get stock entry details
$sql = "SELECT ist.*, bi.item_name, bi.item_code, bi.unit, bi.category, bi.icon_class
        FROM inventory_stock ist
        JOIN boq_items bi ON CAST(ist.boq_item_id AS CHAR) = CAST(bi.id AS CHAR)
        WHERE ist.id = ?";

$db = Database::getInstance()->getConnection();
$stmt = $db->prepare($sql);
$stmt->execute([$stockId]);
$stockEntry = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$stockEntry) {
    header('Location: index.php');
    exit;
}

// Get BOQ items for dropdown
$boqItems = $boqModel->getActive();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $updateData = [
            'unit_cost' => $_POST['unit_cost'],
            'batch_number' => $_POST['batch_number'] ?? null,
            'serial_number' => $_POST['serial_number'] ?? null,
            'location_type' => $_POST['location_type'],
            'location_id' => $_POST['location_id'] ?? null,
            'location_name' => $_POST['location_name'] ?? null,
            'purchase_date' => $_POST['purchase_date'] ?? null,
            'expiry_date' => $_POST['expiry_date'] ?? null,
            'supplier_name' => $_POST['supplier_name'] ?? null,
            'quality_status' => $_POST['quality_status'],
            'warranty_period' => $_POST['warranty_period'] ?? null,
            'item_status' => $_POST['item_status'],
            'notes' => $_POST['notes'] ?? null
        ];
        
        $result = $inventoryModel->updateIndividualStockEntry($stockId, $updateData);
        
        if ($result) {
            $_SESSION['success_message'] = 'Stock entry updated successfully';
            header('Location: index.php');
            exit;
        } else {
            $error = 'Failed to update stock entry';
        }
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

$title = 'Edit Stock Entry';
ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Edit Stock Entry</h1>
        <p class="mt-2 text-sm text-gray-700">Update individual inventory item details</p>
    </div>
    <div class="flex space-x-2">
        <a href="index.php" class="btn btn-secondary">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
            </svg>
            Back to Stock Entries
        </a>
    </div>
</div>

<?php if (isset($error)): ?>
<div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
    <div class="flex">
        <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
        </div>
        <div class="ml-3">
            <p class="text-sm text-red-700"><?php echo htmlspecialchars($error); ?></p>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Stock Entry Details Card -->
<div class="card mb-6">
    <div class="card-body">
        <div class="flex items-center mb-6 pb-6 border-b border-gray-200">
            <div class="flex-shrink-0 h-16 w-16">
                <div class="h-16 w-16 rounded-lg bg-blue-100 flex items-center justify-center">
                    <i class="<?php echo $stockEntry['icon_class'] ?: 'fas fa-cube'; ?> text-blue-600 text-2xl"></i>
                </div>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($stockEntry['item_name']); ?></h3>
                <p class="text-sm text-gray-500"><?php echo htmlspecialchars($stockEntry['item_code']); ?> • <?php echo htmlspecialchars($stockEntry['category']); ?></p>
                <p class="text-sm text-gray-500 mt-1">Stock ID: #<?php echo $stockEntry['id']; ?></p>
            </div>
        </div>

        <form method="POST" action="">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Basic Information -->
                <div class="form-group">
                    <label for="unit_cost" class="form-label">Unit Cost *</label>
                    <input type="number" id="unit_cost" name="unit_cost" step="0.01" class="form-input" required 
                           value="<?php echo htmlspecialchars($stockEntry['unit_cost']); ?>">
                </div>

                <div class="form-group">
                    <label for="batch_number" class="form-label">Batch Number</label>
                    <input type="text" id="batch_number" name="batch_number" class="form-input" 
                           value="<?php echo htmlspecialchars($stockEntry['batch_number'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="serial_number" class="form-label">Serial Number</label>
                    <input type="text" id="serial_number" name="serial_number" class="form-input" 
                           value="<?php echo htmlspecialchars($stockEntry['serial_number'] ?? ''); ?>">
                </div>

                <!-- Location Information -->
                <div class="form-group">
                    <label for="location_type" class="form-label">Location Type *</label>
                    <select id="location_type" name="location_type" class="form-select" required>
                        <option value="warehouse" <?php echo $stockEntry['location_type'] === 'warehouse' ? 'selected' : ''; ?>>Warehouse</option>
                        <option value="site" <?php echo $stockEntry['location_type'] === 'site' ? 'selected' : ''; ?>>Site</option>
                        <option value="vendor" <?php echo $stockEntry['location_type'] === 'vendor' ? 'selected' : ''; ?>>Vendor</option>
                        <option value="in_transit" <?php echo $stockEntry['location_type'] === 'in_transit' ? 'selected' : ''; ?>>In Transit</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="location_name" class="form-label">Location Name</label>
                    <input type="text" id="location_name" name="location_name" class="form-input" 
                           value="<?php echo htmlspecialchars($stockEntry['location_name'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="location_id" class="form-label">Location ID</label>
                    <input type="text" id="location_id" name="location_id" class="form-input" 
                           value="<?php echo htmlspecialchars($stockEntry['location_id'] ?? ''); ?>">
                </div>

                <!-- Dates -->
                <div class="form-group">
                    <label for="purchase_date" class="form-label">Purchase Date</label>
                    <input type="date" id="purchase_date" name="purchase_date" class="form-input" 
                           value="<?php echo $stockEntry['purchase_date'] ? date('Y-m-d', strtotime($stockEntry['purchase_date'])) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="expiry_date" class="form-label">Expiry Date</label>
                    <input type="date" id="expiry_date" name="expiry_date" class="form-input" 
                           value="<?php echo $stockEntry['expiry_date'] ? date('Y-m-d', strtotime($stockEntry['expiry_date'])) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="warranty_period" class="form-label">Warranty Period (months)</label>
                    <input type="number" id="warranty_period" name="warranty_period" class="form-input" 
                           value="<?php echo htmlspecialchars($stockEntry['warranty_period'] ?? ''); ?>">
                </div>

                <!-- Supplier Information -->
                <div class="form-group">
                    <label for="supplier_name" class="form-label">Supplier Name</label>
                    <input type="text" id="supplier_name" name="supplier_name" class="form-input" 
                           value="<?php echo htmlspecialchars($stockEntry['supplier_name'] ?? ''); ?>">
                </div>

                <!-- Status -->
                <div class="form-group">
                    <label for="item_status" class="form-label">Item Status *</label>
                    <select id="item_status" name="item_status" class="form-select" required>
                        <option value="available" <?php echo $stockEntry['item_status'] === 'available' ? 'selected' : ''; ?>>Available</option>
                        <option value="dispatched" <?php echo $stockEntry['item_status'] === 'dispatched' ? 'selected' : ''; ?>>Dispatched</option>
                        <option value="delivered" <?php echo $stockEntry['item_status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                        <option value="damaged" <?php echo $stockEntry['item_status'] === 'damaged' ? 'selected' : ''; ?>>Damaged</option>
                        <option value="returned" <?php echo $stockEntry['item_status'] === 'returned' ? 'selected' : ''; ?>>Returned</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="quality_status" class="form-label">Quality Status *</label>
                    <select id="quality_status" name="quality_status" class="form-select" required>
                        <option value="good" <?php echo $stockEntry['quality_status'] === 'good' ? 'selected' : ''; ?>>Good</option>
                        <option value="damaged" <?php echo $stockEntry['quality_status'] === 'damaged' ? 'selected' : ''; ?>>Damaged</option>
                        <option value="rejected" <?php echo $stockEntry['quality_status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                </div>

                <!-- Notes -->
                <div class="form-group md:col-span-2 lg:col-span-3">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea id="notes" name="notes" rows="3" class="form-input"><?php echo htmlspecialchars($stockEntry['notes'] ?? ''); ?></textarea>
                </div>
            </div>

            <div class="flex justify-end space-x-4 mt-6 pt-6 border-t border-gray-200">
                <a href="index.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    Update Stock Entry
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Audit Trail -->
<div class="card">
    <div class="card-body">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Audit Trail</h3>
        <div class="space-y-3">
            <div class="flex items-center text-sm">
                <span class="text-gray-500 w-32">Created:</span>
                <span class="text-gray-900"><?php echo date('d M Y H:i', strtotime($stockEntry['created_at'])); ?></span>
            </div>
            <?php if ($stockEntry['updated_at']): ?>
            <div class="flex items-center text-sm">
                <span class="text-gray-500 w-32">Last Updated:</span>
                <span class="text-gray-900"><?php echo date('d M Y H:i', strtotime($stockEntry['updated_at'])); ?></span>
            </div>
            <?php endif; ?>
            <?php if ($stockEntry['dispatched_at']): ?>
            <div class="flex items-center text-sm">
                <span class="text-gray-500 w-32">Dispatched:</span>
                <span class="text-gray-900"><?php echo date('d M Y H:i', strtotime($stockEntry['dispatched_at'])); ?></span>
            </div>
            <?php endif; ?>
            <?php if ($stockEntry['delivered_at']): ?>
            <div class="flex items-center text-sm">
                <span class="text-gray-500 w-32">Delivered:</span>
                <span class="text-gray-900"><?php echo date('d M Y H:i', strtotime($stockEntry['delivered_at'])); ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../includes/admin_layout.php';
?>
