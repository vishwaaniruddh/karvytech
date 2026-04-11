<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/MaterialRequest.php';
require_once __DIR__ . '/../../models/BoqItem.php';
require_once __DIR__ . '/../../models/SiteSurvey.php';
require_once __DIR__ . '/../../models/Inventory.php';
require_once __DIR__ . '/../../models/Courier.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$requestId = $_GET['request_id'] ?? null;

if (!$requestId) {
    header('Location: index.php');
    exit;
}

$materialRequestModel = new MaterialRequest();
$boqModel = new BoqItem();
$surveyModel = new SiteSurvey();
$courierModel = new Courier();

// Get material request details
$materialRequest = $materialRequestModel->findWithDetails($requestId);

if (!$materialRequest || $materialRequest['status'] !== 'approved') {
    header('Location: index.php');
    exit;
}

// Get active couriers for dropdown
$activeCouriers = $courierModel->getActiveCouriers();

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

// Check stock availability for all requested items
$inventoryModel = new Inventory();
$stockAvailability = $inventoryModel->checkStockAvailabilityForItems($requestedItems);

// Check if this is an installation material request (no BOQ items)
$isInstallationRequest = empty($stockAvailability);
$hasBoqItems = false;
foreach ($requestedItems as $item) {
    if (!empty($item['boq_item_id'])) {
        $hasBoqItems = true;
        break;
    }
}

// Check if any items are out of stock
$hasStockIssues = false;
$outOfStockItems = [];
foreach ($stockAvailability as $boqItemId => $stock) {
    if (!$stock['is_sufficient']) {
        $hasStockIssues = true;
        $outOfStockItems[] = $stock;
    }
}

// Get survey details if available
$surveyDetails = null;
if ($materialRequest['survey_id']) {
    $surveyDetails = $surveyModel->find($materialRequest['survey_id']);
}

$title = 'Material Dispatch - Request #' . $materialRequest['id'];
ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Material Dispatch</h1>
        <p class="mt-2 text-sm text-gray-700">Create dispatch for Material Request #<?php echo $materialRequest['id']; ?></p>
    </div>
    <div class="flex space-x-2">
        <a href="view-request.php?id=<?php echo $materialRequest['id']; ?>" class="btn btn-secondary">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
            </svg>
            Back to Request
        </a>
    </div>
</div>

<!-- Information Cards Row -->
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
    <!-- Site Information -->
    <div class="card">
        <div class="card-body">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                </svg>
                Site Info
            </h3>
            <div class="space-y-2">
                <div>
                    <label class="text-xs font-medium text-gray-500">Site Code</label>
                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($materialRequest['site_code']); ?></div>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">Site Name</label>
                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($materialRequest['site_name'] ?? 'N/A'); ?></div>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">Location</label>
                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($materialRequest['location']); ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Vendor Information -->
    <div class="card">
        <div class="card-body">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                </svg>
                Vendor Info
            </h3>
            <div class="space-y-2">
                <div>
                    <label class="text-xs font-medium text-gray-500">Company Name</label>
                    <div class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($materialRequest['vendor_company_name'] ?? $materialRequest['vendor_name']); ?></div>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">Contact Person</label>
                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($materialRequest['contact_person'] ?? $materialRequest['vendor_name'] ?? 'Not specified'); ?></div>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">Phone</label>
                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($materialRequest['phone'] ?? 'Not specified'); ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Survey Status -->
    <div class="card">
        <div class="card-body">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" clip-rule="evenodd"></path>
                </svg>
                Survey Status
            </h3>
            <div class="space-y-2">
                <?php if ($surveyDetails): ?>
                <div>
                    <label class="text-xs font-medium text-gray-500">Status</label>
                    <div class="text-sm">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <?php echo ucfirst($surveyDetails['status'] ?? 'Unknown'); ?>
                        </span>
                    </div>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">Survey Date</label>
                    <div class="text-sm text-gray-900"><?php echo date('d M Y', strtotime($surveyDetails['submitted_date'])); ?></div>
                </div>
                <?php else: ?>
                <div>
                    <label class="text-xs font-medium text-gray-500">Status</label>
                    <div class="text-sm text-gray-500">No survey linked</div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Material Request Info -->
    <div class="card">
        <div class="card-body">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                </svg>
                Request Info
            </h3>
            <div class="space-y-2">
                <div>
                    <label class="text-xs font-medium text-gray-500">Request Date</label>
                    <div class="text-sm text-gray-900"><?php echo date('d M Y', strtotime($materialRequest['request_date'])); ?></div>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">Required Date</label>
                    <div class="text-sm text-gray-900"><?php echo $materialRequest['required_date'] ? date('d M Y', strtotime($materialRequest['required_date'])) : 'Not specified'; ?></div>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">Priority</label>
                    <div class="text-sm">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            <?php echo ucfirst($materialRequest['priority'] ?? 'Normal'); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($hasStockIssues): ?>
<!-- Stock Availability Warning -->
<div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
    <div class="flex">
        <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
        </div>
        <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">Stock Availability Issues</h3>
            <div class="mt-2 text-sm text-red-700">
                <p>The following items have insufficient stock for dispatch:</p>
                <ul class="list-disc list-inside mt-2 space-y-1">
                    <?php foreach ($outOfStockItems as $item): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($item['item_name']); ?></strong> 
                        (<?php echo htmlspecialchars($item['item_code']); ?>) - 
                        Requested: <?php echo $item['requested_quantity']; ?> <?php echo htmlspecialchars($item['unit']); ?>, 
                        Available: <?php echo $item['available_quantity']; ?> <?php echo htmlspecialchars($item['unit']); ?>
                        <?php if ($item['shortage'] > 0): ?>
                            <span class="text-red-600 font-medium">(Short by <?php echo $item['shortage']; ?> <?php echo htmlspecialchars($item['unit']); ?>)</span>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <div class="mt-3 p-3 bg-red-100 border border-red-300 rounded">
                    <p class="font-medium text-red-800">⚠️ Dispatch cannot proceed until stock is replenished or request quantities are adjusted.</p>
                    <div class="mt-2 flex space-x-3">
                        <a href="../inventory/index.php" class="text-red-700 underline hover:text-red-900">View Inventory</a>
                        <a href="../inventory/inwards/index.php" class="text-red-700 underline hover:text-red-900">Add Stock</a>
                        <a href="view-request.php?id=<?php echo $materialRequest['id']; ?>" class="text-red-700 underline hover:text-red-900">Back to Request</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Stock Availability Summary -->
<?php if ($hasBoqItems): ?>
<div class="card mb-6">
    <div class="card-body">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Stock Availability Summary</h3>
        
        <?php if (empty($stockAvailability)): ?>
        <div class="bg-gray-50 border border-gray-200 rounded-md p-4 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-2.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 009.586 13H7"></path>
            </svg>
            <p class="mt-2 text-sm text-gray-500">No stock information available</p>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($stockAvailability as $boqItemId => $stock): ?>
                    <tr class="<?php echo !$stock['is_sufficient'] ? 'bg-red-50' : 'bg-green-50'; ?>">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8">
                                    <div class="h-8 w-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                        <i class="fas fa-cube text-blue-600 text-sm"></i>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($stock['item_name']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($stock['item_code']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo $stock['requested_quantity']; ?> <?php echo htmlspecialchars($stock['unit']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo $stock['available_quantity']; ?> <?php echo htmlspecialchars($stock['unit']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($stock['is_sufficient']): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    In Stock
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                    Out of Stock
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="../inventory/index.php?search=<?php echo urlencode($stock['item_code']); ?>" 
                               class="text-blue-600 hover:text-blue-900">View Details</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php else: ?>
<!-- Installation Material Request - No Stock Tracking -->
<div class="card mb-6">
    <div class="card-body">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Material Request Information</h3>
        <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Installation Material Request</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <p>This is a material request from an ongoing installation. Stock availability tracking is not applicable for installation materials as they are managed separately at the site level.</p>
                        <p class="mt-2">You can proceed with dispatch based on the contractor's request and site requirements.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Dispatch Form -->
<form id="dispatchForm">
    <input type="hidden" name="material_request_id" value="<?php echo $materialRequest['id']; ?>">
    
    <!-- Dispatch Information -->
    <div class="card mb-6">
        <div class="card-body">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Dispatch Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="form-group">
                    <label for="contact_person_name" class="form-label">Contact Person Name *</label>
                    <input type="text" id="contact_person_name" name="contact_person_name" class="form-input" required 
                           value="<?php echo htmlspecialchars($materialRequest['contact_person'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="contact_person_phone" class="form-label">Contact Person Phone *</label>
                    <input type="text" id="contact_person_phone" name="contact_person_phone" class="form-input" required
                           pattern="[6-9][0-9]{9}" 
                           maxlength="10"
                           placeholder="10-digit mobile number"
                           value="<?php echo htmlspecialchars($materialRequest['phone'] ?? ''); ?>">
                    <p class="mt-1 text-sm text-gray-500">Enter 10-digit mobile number starting with 6-9</p>
                    <p id="phone_error" class="mt-1 text-sm text-red-600 hidden"></p>
                </div>
                
                <div class="form-group">
                    <label for="courier_name" class="form-label">Courier Name *</label>
                    <select id="courier_name" name="courier_name" class="form-input" required>
                        <option value="">Select Courier</option>
                        <?php foreach ($activeCouriers as $courier): ?>
                            <option value="<?php echo htmlspecialchars($courier['courier_name']); ?>">
                                <?php echo htmlspecialchars($courier['courier_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="mt-1 text-sm text-gray-500">Select courier service for dispatch</p>
                </div>
                
                <div class="form-group">
                    <label for="pod_number" class="form-label">POD / Tracking Number</label>
                    <input type="text" id="pod_number" name="pod_number" class="form-input" 
                           placeholder="Enter POD or tracking number">
                </div>
                
                <div class="form-group">
                    <label for="dispatch_date" class="form-label">Dispatch Date *</label>
                    <input type="date" id="dispatch_date" name="dispatch_date" class="form-input" required 
                           value="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="expected_delivery_date" class="form-label">Expected Delivery Date</label>
                    <input type="date" id="expected_delivery_date" name="expected_delivery_date" class="form-input"
                           value="<?php echo $materialRequest['required_date'] ?: ''; ?>">
                </div>
                
                <div class="form-group md:col-span-2 lg:col-span-3">
                    <label for="delivery_address" class="form-label">Delivery Address *</label>
                    <textarea id="delivery_address" name="delivery_address" rows="3" class="form-input" required
                              placeholder="Enter delivery address..."><?php echo htmlspecialchars($materialRequest['address'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group md:col-span-2 lg:col-span-3">
                    <label for="dispatch_remarks" class="form-label">Dispatch Remarks</label>
                    <textarea id="dispatch_remarks" name="dispatch_remarks" rows="3" class="form-input" 
                              placeholder="Any special instructions or remarks for dispatch..."><?php echo htmlspecialchars($materialRequest['request_notes'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Material Items -->
    <div class="card mb-6">
        <div class="card-body">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Material Items</h3>
            
            <?php foreach ($requestedItems as $index => $item): ?>
            <?php 
                // Check if this is a BOQ-based request or installation material request
                $isBoqRequest = isset($item['boq_item_id']);
                $boqItemId = $item['boq_item_id'] ?? null;
                $boqItem = $boqItemId ? ($boqItems[$boqItemId] ?? null) : null;
                
                // Get item details from appropriate source
                $itemName = $boqItem['item_name'] ?? $item['material_name'] ?? 'Unknown Item';
                $itemCode = $boqItem['item_code'] ?? 'N/A';
                $itemUnit = $boqItem['unit'] ?? $item['unit'] ?? 'units';
                $itemIcon = $boqItem['icon_class'] ?? 'fas fa-tools';
                
                $stockInfo = $boqItemId ? ($stockAvailability[$boqItemId] ?? null) : null;
                $isOutOfStock = $stockInfo && !$stockInfo['is_sufficient'];
                $needsSerial = isset($boqItem['need_serial_number']) && $boqItem['need_serial_number'] == 1;
            ?>
            
            <!-- Material Item Header (Cumulative Total) -->
            <div class="border border-gray-200 rounded-lg mb-4 <?php echo $isOutOfStock ? 'border-red-300 bg-red-50' : ''; ?>">
                <div class="<?php echo $isOutOfStock ? 'bg-red-100' : 'bg-gray-50'; ?> px-4 py-3 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <div class="h-10 w-10 rounded-lg <?php echo $isOutOfStock ? 'bg-red-100' : 'bg-blue-100'; ?> flex items-center justify-center">
                                    <i class="<?php echo $itemIcon; ?> <?php echo $isOutOfStock ? 'text-red-600' : 'text-blue-600'; ?>"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-lg font-medium text-gray-900">
                                    <?php echo htmlspecialchars($itemName); ?>
                                    <?php if ($isOutOfStock): ?>
                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Out of Stock
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-sm text-gray-500">
                                    <?php echo htmlspecialchars($itemCode); ?> • Unit: <?php echo htmlspecialchars($itemUnit); ?>
                                    <?php if ($needsSerial): ?>
                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                            <i class="fas fa-barcode mr-1"></i> Serial Required
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($stockInfo): ?>
                                <div class="text-sm <?php echo $isOutOfStock ? 'text-red-600' : 'text-green-600'; ?> mt-1">
                                    Available: <?php echo $stockInfo['available_quantity']; ?> <?php echo htmlspecialchars($stockInfo['unit']); ?>
                                    <?php if ($isOutOfStock): ?>
                                        • Short by: <?php echo $stockInfo['shortage']; ?> <?php echo htmlspecialchars($stockInfo['unit']); ?>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-semibold <?php echo $isOutOfStock ? 'text-red-600' : 'text-blue-600'; ?>">Requires <?php echo number_format($item['quantity']); ?> items</div>
                            <div class="text-sm text-gray-500">Total Quantity</div>
                        </div>
                    </div>
                </div>
                
                <!-- Dispatch Configuration -->
                <div class="px-4 py-3 bg-white">
                    <?php if ($isOutOfStock): ?>
                    <div class="bg-red-50 border border-red-200 rounded-md p-3 mb-4">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <div class="text-sm text-red-700">
                                <strong>Cannot dispatch this item:</strong> Insufficient stock available. 
                                Please add stock to inventory or reduce the requested quantity.
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="form-label">Dispatch Quantity *</label>
                            <input type="number" name="items[<?php echo $index; ?>][dispatch_quantity]" 
                                   class="form-input <?php echo $isOutOfStock ? 'bg-gray-100 cursor-not-allowed' : ''; ?>" 
                                   step="0.01" min="0" 
                                   max="<?php echo $stockInfo ? min($item['quantity'], $stockInfo['available_quantity']) : $item['quantity']; ?>" 
                                   value="<?php echo $stockInfo ? min($item['quantity'], $stockInfo['available_quantity']) : $item['quantity']; ?>" 
                                   <?php echo $isOutOfStock ? 'disabled readonly' : 'required'; ?>
                                   onchange="updateIndividualRows(<?php echo $index; ?>)">
                            <input type="hidden" name="items[<?php echo $index; ?>][boq_item_id]" value="<?php echo $boqItemId ?? ''; ?>">
                            <input type="hidden" name="items[<?php echo $index; ?>][material_name]" value="<?php echo htmlspecialchars($itemName); ?>">
                            <?php if ($stockInfo): ?>
                            <div class="text-xs text-gray-500 mt-1">
                                Max available: <?php echo $stockInfo['available_quantity']; ?> <?php echo htmlspecialchars($stockInfo['unit']); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label class="form-label">Batch Number (Optional)</label>
                            <input type="text" name="items[<?php echo $index; ?>][batch_number]" 
                                   class="form-input <?php echo $isOutOfStock ? 'bg-gray-100 cursor-not-allowed' : ''; ?>" 
                                   placeholder="Enter batch number"
                                   <?php echo $isOutOfStock ? 'disabled readonly' : ''; ?>>
                        </div>
                        <div>
                            <label class="form-label">Dispatch Notes</label>
                            <textarea name="items[<?php echo $index; ?>][dispatch_notes]" 
                                      class="form-input <?php echo $isOutOfStock ? 'bg-gray-100 cursor-not-allowed' : ''; ?>" 
                                      rows="1" 
                                      placeholder="Notes..."
                                      <?php echo $isOutOfStock ? 'disabled readonly' : ''; ?>><?php echo htmlspecialchars($item['notes'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    
                    <!-- Individual Items Section (Only for Serialized Products) -->
                    <?php if ($needsSerial): ?>
                    <div class="border-t border-gray-100 pt-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-md font-medium text-gray-900">
                                <i class="fas fa-barcode text-purple-600 mr-2"></i>Individual Serial Numbers
                            </h4>
                            <div class="text-sm text-gray-500">
                                <span id="individual_count_<?php echo $index; ?>"><?php echo intval($item['quantity']); ?></span> items to dispatch
                            </div>
                        </div>
                        
                        <div id="individual_items_<?php echo $index; ?>" class="space-y-2" data-needs-serial="true">
                            <!-- Individual items will be generated by JavaScript -->
                        </div>
                    </div>
                    <?php else: ?>
                    <input type="hidden" id="individual_items_<?php echo $index; ?>" data-needs-serial="false">
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Submit Button -->
    <div class="flex justify-end space-x-4">
        <a href="view-request.php?id=<?php echo $materialRequest['id']; ?>" class="btn btn-secondary">Cancel</a>
        <?php if ($hasStockIssues): ?>
        <button type="button" class="btn btn-secondary cursor-not-allowed" disabled>
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"></path>
            </svg>
            Cannot Dispatch - Stock Issues
        </button>
        <?php else: ?>
        <button type="submit" class="btn btn-primary">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            Dispatch Continue
        </button>
        <?php endif; ?>
    </div>
</form>

<script>
let individualCounters = {};

// Initialize individual items on page load
document.addEventListener('DOMContentLoaded', function() {
    <?php foreach ($requestedItems as $index => $item): ?>
    updateIndividualRows(<?php echo $index; ?>);
    <?php endforeach; ?>
});

function updateIndividualRows(itemIndex) {
    const dispatchQty = parseInt(document.querySelector(`input[name="items[${itemIndex}][dispatch_quantity]"]`).value) || 0;
    const container = document.getElementById(`individual_items_${itemIndex}`);
    const countDisplay = document.getElementById(`individual_count_${itemIndex}`);
    
    // Check if this item needs serial numbers
    const needsSerial = container && container.getAttribute('data-needs-serial') === 'true';
    
    if (!needsSerial) {
        // For non-serialized items, just update the count display if it exists
        if (countDisplay) {
            countDisplay.textContent = dispatchQty;
        }
        return;
    }
    
    // Update count display
    if (countDisplay) {
        countDisplay.textContent = dispatchQty;
    }
    
    // Clear existing items
    container.innerHTML = '';
    
    // Add header row if items exist
    if (dispatchQty > 0) {
        const headerDiv = document.createElement('div');
        headerDiv.className = 'grid grid-cols-12 gap-2 items-center py-2 bg-gray-50 border-b border-gray-200 text-sm font-medium text-gray-700';
        headerDiv.innerHTML = `
            <div class="col-span-1">#</div>
            <div class="col-span-6">Serial Number *</div>
            <div class="col-span-4">Batch Number</div>
            <div class="col-span-1 text-center">Status</div>
        `;
        
        container.appendChild(headerDiv);
    }
    
    // Create individual item rows for serialized products
    for (let i = 0; i < dispatchQty; i++) {
        const itemDiv = document.createElement('div');
        itemDiv.className = 'grid grid-cols-12 gap-2 items-center py-2 border-b border-gray-100 last:border-b-0';
        itemDiv.innerHTML = `
            <div class="col-span-1 text-sm text-gray-500 font-medium">#${i + 1}</div>
            <div class="col-span-6">
                <input type="text" name="items[${itemIndex}][individual][${i}][serial_number]" 
                       class="form-input w-full" placeholder="Enter serial number" required
                       style="font-size: 13px; padding: 6px 10px;">
            </div>
            <div class="col-span-4">
                <input type="text" name="items[${itemIndex}][individual][${i}][batch_number]" 
                       class="form-input w-full" placeholder="Batch (optional)" 
                       style="font-size: 13px; padding: 6px 10px;">
            </div>
            <div class="col-span-1 text-center">
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    <i class="fas fa-check"></i>
                </span>
            </div>
        `;
        
        container.appendChild(itemDiv);
    }
}

// Form submission
// Phone number validation function
function validatePhone(phone) {
    // Remove all non-numeric characters
    const clean = phone.replace(/[^0-9]/g, '');
    
    // Remove country code and leading 0
    const number = clean.replace(/^(91|0)/, '');
    
    // Check if 10 digits starting with 6-9
    return /^[6-9][0-9]{9}$/.test(number);
}

// Real-time phone validation
document.getElementById('contact_person_phone').addEventListener('input', function(e) {
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

document.getElementById('dispatchForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validate phone before submission
    const phone = document.getElementById('contact_person_phone').value;
    if (!validatePhone(phone)) {
        showAlert('Please enter a valid 10-digit phone number starting with 6-9', 'error');
        document.getElementById('contact_person_phone').focus();
        return;
    }
    
    const formData = new FormData(this);
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Processing Dispatch...';
    submitBtn.disabled = true;
    
    fetch('process-material-dispatch.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Material dispatch processed successfully!', 'success');
            setTimeout(() => {
                window.location.href = `view-request.php?id=<?php echo $materialRequest['id']; ?>`;
            }, 1500);
        } else {
            showAlert('Error: ' + data.message, 'error');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while processing the dispatch.', 'error');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>