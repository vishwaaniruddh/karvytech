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
    header('Location: material-received.php');
    exit;
}

$materialRequestModel = new MaterialRequest();
$inventoryModel = new Inventory();
$boqModel = new BoqItem();

// Get material request details
$materialRequest = $materialRequestModel->findWithDetails($requestId);

if (!$materialRequest || $materialRequest['vendor_id'] != $vendorId) {
    header('Location: material-received.php');
    exit;
}

// Get dispatch details
$dispatchDetails = $inventoryModel->getDispatchByRequestId($requestId);

// var_dump($dispatchDetails);
// Get dispatch items with individual records
$dispatchItems = [];
if (!empty($dispatchDetails['id'])) {
    $dispatchItems = $inventoryModel->getDispatchItemsSummary($dispatchDetails['id']);
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

$title = 'Material Receipt Details - Request #' . $materialRequest['id'];
ob_start();
?>

<!-- Compact Header -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 mb-3">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2">
            <a href="material-requests-list.php" class="text-blue-600 hover:text-blue-800">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                </svg>
            </a>
            <h1 class="text-lg font-semibold text-gray-900">Receipt Details - Request #<?php echo $materialRequest['id']; ?></h1>
        </div>
        
        <?php if (($dispatchDetails['dispatch_status'] ?? 'dispatched') === 'dispatched' || ($dispatchDetails['dispatch_status'] ?? 'dispatched') === 'in_transit'): ?>
        <a href="confirm-delivery.php?id=<?php echo $materialRequest['id']; ?>" class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-white bg-green-600 hover:bg-green-700 transition-colors">
            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
            </svg>
            Confirm Delivery
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Compact Status Banner -->
<div class="mb-3">
    <?php
    $status = $dispatchDetails['dispatch_status'] ?? 'dispatched';
    $statusConfig = [
        'dispatched' => ['bg' => 'bg-orange-50', 'border' => 'border-orange-200', 'text' => 'text-orange-800', 'icon' => 'text-orange-400'],
        'in_transit' => ['bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'text' => 'text-blue-800', 'icon' => 'text-blue-400'],
        'delivered' => ['bg' => 'bg-green-50', 'border' => 'border-green-200', 'text' => 'text-green-800', 'icon' => 'text-green-400'],
        'confirmed' => ['bg' => 'bg-purple-50', 'border' => 'border-purple-200', 'text' => 'text-purple-800', 'icon' => 'text-purple-400']
    ];
    $config = $statusConfig[$status] ?? $statusConfig['dispatched'];
    ?>
    <div class="rounded-lg <?php echo $config['bg']; ?> <?php echo $config['border']; ?> border p-2">
        <div class="flex items-start">
            <svg class="h-3 w-3 <?php echo $config['icon']; ?> mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
            <div>
                <h3 class="text-xs font-medium <?php echo $config['text']; ?>">
                    Status: <?php 
                    $statusLabels = [
                        'dispatched' => 'Material Sent - Pending Acceptance',
                        'in_transit' => 'In Transit',
                        'delivered' => 'Delivered',
                        'confirmed' => 'Receipt Confirmed'
                    ];
                    echo $statusLabels[$status] ?? ucfirst($status);
                    ?>
                </h3>
                <p class="text-xs <?php echo $config['text']; ?> mt-0.5">
                    <?php if ($status === 'dispatched'): ?>
                        Material sent from Karvy. Please confirm receipt once received.
                    <?php elseif ($status === 'in_transit'): ?>
                        Material is on the way. Please confirm receipt once received.
                    <?php elseif ($status === 'delivered'): ?>
                        Material delivered. Waiting for confirmation.
                    <?php elseif ($status === 'confirmed'): ?>
                        Receipt confirmed. Process complete.
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Information Cards -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-3 mb-4">
    <!-- Site Information -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3">
        <h3 class="text-sm font-semibold text-gray-900 mb-2 flex items-center">
            <svg class="w-4 h-4 mr-1 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
            </svg>
            Site Information
        </h3>
        <div class="space-y-2">
            <div class="flex justify-between">
                <span class="text-xs font-medium text-gray-500">Site Code</span>
                <span class="text-xs text-gray-900"><?php echo htmlspecialchars($materialRequest['site_code']); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-xs font-medium text-gray-500">Location</span>
                <span class="text-xs text-gray-900"><?php echo htmlspecialchars($materialRequest['location']); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-xs font-medium text-gray-500">Address</span>
                <span class="text-xs text-gray-900"><?php echo htmlspecialchars($materialRequest['address'] ?? $dispatchDetails['delivery_address'] ?? 'N/A'); ?></span>
            </div>
        </div>
    </div>
    
    <!-- Delivery Information -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3">
        <h3 class="text-sm font-semibold text-gray-900 mb-2 flex items-center">
            <svg class="w-4 h-4 mr-1 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            Delivery Information
        </h3>
        <div class="space-y-2">
            <div class="flex justify-between">
                <span class="text-xs font-medium text-gray-500">Receipt Number</span>
                <span class="text-xs text-gray-900"><?php echo htmlspecialchars($dispatchDetails['dispatch_number'] ?? 'N/A'); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-xs font-medium text-gray-500">Sent Date</span>
                <span class="text-xs text-gray-900"><?php echo $dispatchDetails['dispatch_date'] ? date('d M Y', strtotime($dispatchDetails['dispatch_date'])) : 'N/A'; ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-xs font-medium text-gray-500">Courier Name</span>
                <span class="text-xs text-gray-900"><?php echo htmlspecialchars($dispatchDetails['courier_name'] ?? 'N/A'); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-xs font-medium text-gray-500">POD / Tracking</span>
                <span class="text-xs text-gray-900"><?php echo htmlspecialchars($dispatchDetails['tracking_number'] ?? 'N/A'); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-xs font-medium text-gray-500">Expected Delivery</span>
                <span class="text-xs text-gray-900"><?php echo $dispatchDetails['expected_delivery_date'] ? date('d M Y', strtotime($dispatchDetails['expected_delivery_date'])) : 'N/A'; ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-xs font-medium text-gray-500">Contact Person</span>
                <span class="text-xs text-gray-900"><?php echo htmlspecialchars($dispatchDetails['contact_person_name'] ?? 'N/A'); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-xs font-medium text-gray-500">Contact Phone</span>
                <span class="text-xs text-gray-900"><?php echo htmlspecialchars($dispatchDetails['contact_person_phone'] ?? 'N/A'); ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Material Items -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 mb-4">
    <div class="p-0">
        <h3 class="text-sm font-semibold text-gray-900 mb-3 flex items-center">
            <svg class="w-4 h-4 mr-1 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM13 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2h-2z"></path>
            </svg>
            Received Materials
        </h3>
        
        <div class="space-y-2">
            <?php if (!empty($dispatchItems)): ?>
                <?php foreach ($dispatchItems as $item): ?>
                <div class="bg-white border border-gray-200 rounded-lg p-3 hover:shadow-sm transition-shadow">
                    <div class="flex items-center justify-between">
                        <!-- Material Info -->
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <div class="h-10 w-10 rounded-lg bg-blue-600 flex items-center justify-center">
                                    <i class="<?php echo $item['icon_class'] ?: 'fas fa-cube'; ?> text-white text-sm"></i>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($item['item_name'] ?? 'Unknown Item'); ?></h4>
                                <p class="text-xs text-gray-500">
                                    <?php echo htmlspecialchars($item['item_code'] ?? 'N/A'); ?> • <?php echo htmlspecialchars($item['unit'] ?? 'N/A'); ?>
                                </p>
                            </div>
                        </div>
                        
                            <!-- Quantity Display -->
                            <div class="text-right">
                                <div class="text-lg font-bold text-green-700"><?php echo number_format($item['quantity_dispatched'] ?? 0); ?></div>
                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($item['unit'] ?? 'Units'); ?></div>
                            </div>
                    </div>
                    
                    <!-- Additional Details (Collapsible) -->
                    <?php
                    $serialNumbers = !empty($item['serial_numbers']) ? explode(',', $item['serial_numbers']) : [];
                    $batchNumber = $item['batch_number'] ?? null;
                    $hasDetails = !empty($serialNumbers) || !empty($batchNumber) || !empty($item['remarks']);
                    ?>
                    
                    <?php if ($hasDetails): ?>
                    <div class="mt-2 pt-2 border-t border-gray-100">
                        <button type="button" 
                                onclick="toggleDetails('item-<?php echo $item['boq_item_id']; ?>')" 
                                class="text-xs text-blue-600 hover:text-blue-800 flex items-center">
                            <svg class="w-3 h-3 mr-1 transform transition-transform" id="icon-item-<?php echo $item['boq_item_id']; ?>" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                            Show Details
                        </button>
                        
                        <div id="details-item-<?php echo $item['boq_item_id']; ?>" class="hidden mt-2 space-y-2">
                            <?php if (!empty($serialNumbers)): ?>
                            <div>
                                <label class="text-xs font-medium text-gray-600">Serial Numbers:</label>
                                <div class="flex flex-wrap gap-1 mt-1">
                                    <?php foreach ($serialNumbers as $sn): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-blue-100 text-blue-800">
                                        <?php echo htmlspecialchars(trim($sn)); ?>
                                    </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php elseif (!empty($batchNumber)): ?>
                            <div>
                                <label class="text-xs font-medium text-gray-600">Batch Number:</label>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-800 ml-2">
                                    <?php echo htmlspecialchars($batchNumber); ?>
                                </span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($item['remarks'])): ?>
                            <div>
                                <label class="text-xs font-medium text-gray-600">Notes:</label>
                                <p class="text-xs text-gray-700 mt-1"><?php echo htmlspecialchars($item['remarks']); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <?php foreach ($requestedItems as $item): ?>
                <?php $boqItem = $boqItems[$item['boq_item_id']] ?? null; ?>
                <div class="bg-white border border-gray-200 rounded-lg p-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <?php if ($boqItem): ?>
                                <div class="h-10 w-10 rounded-lg bg-blue-600 flex items-center justify-center">
                                    <i class="<?php echo $boqItem['icon_class'] ?: 'fas fa-cube'; ?> text-white text-sm"></i>
                                </div>
                                <?php else: ?>
                                <div class="h-10 w-10 rounded-lg bg-gray-400 flex items-center justify-center">
                                    <i class="fas fa-question text-white text-sm"></i>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <?php if ($boqItem): ?>
                                <h4 class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($boqItem['item_name']); ?></h4>
                                <p class="text-xs text-gray-500">
                                    <?php echo htmlspecialchars($boqItem['item_code']); ?> • <?php echo htmlspecialchars($boqItem['unit']); ?>
                                </p>
                                <?php else: ?>
                                <div class="text-sm text-gray-500">Item not found (ID: <?php echo $item['boq_item_id']; ?>)</div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="text-right">
                            <div class="text-lg font-bold text-green-700"><?php echo number_format($item['quantity']); ?></div>
                            <div class="text-xs text-gray-500"><?php echo htmlspecialchars($item['unit']); ?></div>
                        </div>
                    </div>
                    
                    <?php if (!empty($item['notes'])): ?>
                    <div class="mt-2 pt-2 border-t border-gray-100">
                        <p class="text-xs text-gray-700"><?php echo htmlspecialchars($item['notes']); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Confirmation Details (if confirmed) -->
<?php if ($status === 'confirmed' && !empty($dispatchDetails['delivery_remarks'])): ?>
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 mb-4">
    <h3 class="text-sm font-semibold text-gray-900 mb-3 flex items-center">
        <svg class="w-4 h-4 mr-1 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
        </svg>
        Receipt Confirmation Details
    </h3>
    <div class="bg-purple-50 rounded-lg p-3 border border-purple-100">
        <?php
        // Parse delivery remarks to extract structured information
        $remarks = $dispatchDetails['delivery_remarks'];
        $remarksParts = explode(' | ', $remarks);
        ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <?php foreach ($remarksParts as $part): ?>
                <?php if (strpos($part, ':') !== false): ?>
                    <?php list($label, $value) = explode(':', $part, 2); ?>
                    <div>
                        <label class="text-xs font-medium text-purple-700"><?php echo htmlspecialchars(trim($label)); ?></label>
                        <div class="text-xs text-purple-900 mt-1"><?php echo htmlspecialchars(trim($value)); ?></div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Uploaded Documents -->
<?php if (!empty($dispatchDetails['lr_copy_path']) || !empty($dispatchDetails['additional_documents'])): ?>
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 mb-4">
    <h3 class="text-sm font-semibold text-gray-900 mb-3 flex items-center">
        <svg class="w-4 h-4 mr-1 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
        </svg>
        Uploaded Documents
    </h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
        <!-- LR Copy / Delivery Receipt -->
        <?php if (!empty($dispatchDetails['lr_copy_path'])): ?>
        <div class="bg-blue-50 rounded-lg p-3 border border-blue-200 hover:shadow-sm transition-shadow">
            <div class="flex items-center space-x-2">
                <div class="flex-shrink-0">
                    <div class="h-8 w-8 rounded bg-blue-600 flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="text-xs font-medium text-blue-900">LR Copy / Delivery Receipt</h4>
                    <p class="text-xs text-blue-700">Official delivery document</p>
                </div>
                <a href="../<?php echo htmlspecialchars($dispatchDetails['lr_copy_path']); ?>" 
                   target="_blank" 
                   class="inline-flex items-center px-2 py-1 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition-colors">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z"></path>
                        <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z"></path>
                    </svg>
                    View
                </a>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Additional Documents -->
        <?php 
        if (!empty($dispatchDetails['additional_documents'])):
            // Check if it's already an array or needs to be decoded
            if (is_array($dispatchDetails['additional_documents'])) {
                $additionalDocs = $dispatchDetails['additional_documents'];
            } else {
                $additionalDocs = json_decode($dispatchDetails['additional_documents'], true);
            }
            
            if (is_array($additionalDocs)):
                foreach ($additionalDocs as $index => $docPath):
                    $fileName = basename($docPath);
                    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    
                    // Determine icon and color based on file type
                    $iconConfig = [
                        'pdf' => ['icon' => 'fas fa-file-pdf', 'color' => 'red'],
                        'doc' => ['icon' => 'fas fa-file-word', 'color' => 'blue'],
                        'docx' => ['icon' => 'fas fa-file-word', 'color' => 'blue'],
                        'jpg' => ['icon' => 'fas fa-file-image', 'color' => 'green'],
                        'jpeg' => ['icon' => 'fas fa-file-image', 'color' => 'green'],
                        'png' => ['icon' => 'fas fa-file-image', 'color' => 'green'],
                        'gif' => ['icon' => 'fas fa-file-image', 'color' => 'green'],
                        'webp' => ['icon' => 'fas fa-file-image', 'color' => 'green'],
                    ];
                    
                    $config = $iconConfig[$fileExt] ?? ['icon' => 'fas fa-file', 'color' => 'gray'];
                    $colorClass = $config['color'];
                    $isImage = in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
        ?>
        <div class="bg-<?php echo $colorClass; ?>-50 rounded-lg p-3 border border-<?php echo $colorClass; ?>-200 hover:shadow-sm transition-shadow">
            <div class="flex items-center space-x-2">
                <div class="flex-shrink-0">
                    <div class="h-8 w-8 rounded bg-<?php echo $colorClass; ?>-600 flex items-center justify-center">
                        <i class="<?php echo $config['icon']; ?> text-white text-sm"></i>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="text-xs font-medium text-<?php echo $colorClass; ?>-900 truncate">Document <?php echo $index + 1; ?></h4>
                    <p class="text-xs text-<?php echo $colorClass; ?>-700 truncate" title="<?php echo htmlspecialchars($fileName); ?>">
                        <?php echo htmlspecialchars($fileName); ?>
                    </p>
                </div>
                <div class="flex space-x-1">
                    <?php if ($isImage): ?>
                    <button type="button" 
                            onclick="previewImage('../<?php echo htmlspecialchars($docPath); ?>', '<?php echo htmlspecialchars($fileName); ?>')"
                            class="inline-flex items-center px-2 py-1 bg-<?php echo $colorClass; ?>-600 text-white text-xs font-medium rounded hover:bg-<?php echo $colorClass; ?>-700 transition-colors"
                            title="Preview Image">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                    <?php endif; ?>
                    <a href="../<?php echo htmlspecialchars($docPath); ?>" 
                       target="_blank" 
                       class="inline-flex items-center px-2 py-1 bg-<?php echo $colorClass; ?>-600 text-white text-xs font-medium rounded hover:bg-<?php echo $colorClass; ?>-700 transition-colors">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z"></path>
                            <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
        <?php 
                endforeach;
            endif;
        endif;
        ?>
    </div>
    
    <?php if (empty($dispatchDetails['lr_copy_path']) && empty($dispatchDetails['additional_documents'])): ?>
    <div class="text-center py-4">
        <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <p class="mt-2 text-xs text-gray-500">No documents uploaded yet</p>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Partial Delivery Management -->
<?php if (($dispatchDetails['dispatch_status'] ?? '') === 'partially_delivered'): ?>
<div class="card mb-6">
    <div class="card-body">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
            Partial Delivery - Missing Items
        </h3>
        
        <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 mb-4">
            <div class="flex">
                <svg class="h-5 w-5 text-orange-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <h4 class="text-sm font-medium text-orange-800">Partial Delivery Detected</h4>
                    <p class="text-sm text-orange-700 mt-1">
                        You have confirmed receiving only part of the dispatched materials. You can request the missing items below.
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Missing Items Analysis -->
        <?php
        // Get delivery confirmation details to compare sent vs received
        $deliveryConfirmation = $inventoryModel->getDeliveryConfirmationDetails($dispatchDetails['id']);
        $itemConfirmations = [];
        if ($deliveryConfirmation && !empty($deliveryConfirmation['item_confirmations'])) {
            // Check if it's already an array or needs to be decoded
            if (is_array($deliveryConfirmation['item_confirmations'])) {
                $itemConfirmations = $deliveryConfirmation['item_confirmations'];
            } else {
                $itemConfirmations = json_decode($deliveryConfirmation['item_confirmations'], true) ?: [];
            }
        }
        
        $missingItems = [];
        foreach ($dispatchItems as $dispatchItem) {
            $sentQty = $dispatchItem['quantity_dispatched'];
            $receivedQty = 0;
            
            // Find received quantity for this item
            foreach ($itemConfirmations as $confirmation) {
                if ($confirmation['boq_item_id'] == $dispatchItem['boq_item_id']) {
                    $receivedQty = $confirmation['received_quantity'];
                    break;
                }
            }
            
            $missingQty = $sentQty - $receivedQty;
            if ($missingQty > 0) {
                $missingItems[] = [
                    'boq_item_id' => $dispatchItem['boq_item_id'],
                    'item_name' => $dispatchItem['item_name'],
                    'item_code' => $dispatchItem['item_code'],
                    'unit' => $dispatchItem['unit'],
                    'sent_qty' => $sentQty,
                    'received_qty' => $receivedQty,
                    'missing_qty' => $missingQty
                ];
            }
        }
        ?>
        
        <?php if (!empty($missingItems)): ?>
        <div class="space-y-4">
            <h4 class="text-md font-medium text-gray-900">Missing Items Summary</h4>
            
            <div class="grid grid-cols-1 gap-3">
                <?php foreach ($missingItems as $item): ?>
                <div class="bg-white border border-red-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <h5 class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($item['item_name']); ?></h5>
                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($item['item_code']); ?></p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="text-right">
                                <div class="text-sm text-red-600 font-medium">
                                    Missing: <?php echo number_format($item['missing_qty']); ?> <?php echo htmlspecialchars($item['unit']); ?>
                                </div>
                                <div class="text-xs text-gray-500">
                                    Sent: <?php echo number_format($item['sent_qty']); ?> | 
                                    Received: <?php echo number_format($item['received_qty']); ?>
                                </div>
                            </div>
                            
                            <!-- Audit Button for Received Quantity -->
                            <button type="button" 
                                    onclick="openReceivedQuantityAuditModal(<?php echo htmlspecialchars(json_encode($item)); ?>)" 
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded hover:bg-blue-100 transition-colors"
                                    title="Audit/Correct Received Quantity">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                                </svg>
                                Audit
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Request Missing Items Button -->
            <div class="flex justify-end pt-4 border-t border-gray-200">
                <button type="button" onclick="requestMissingItems()" class="btn btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                    </svg>
                    Request Missing Items
                </button>
            </div>
        </div>
        <?php else: ?>
        <div class="text-center py-4">
            <svg class="mx-auto h-8 w-8 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
            </svg>
            <p class="text-sm text-gray-600 mt-2">No missing items detected. All items have been accounted for.</p>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Delivery Remarks -->
<?php if (!empty($dispatchDetails['dispatch_remarks']) && $status !== 'confirmed'): ?>
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 mb-4">
    <h3 class="text-sm font-semibold text-gray-900 mb-3">Delivery Remarks</h3>
    <div class="bg-gray-50 rounded-lg p-3">
        <p class="text-xs text-gray-700"><?php echo nl2br(htmlspecialchars($dispatchDetails['dispatch_remarks'])); ?></p>
    </div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../includes/vendor_layout.php';
?>

<!-- Audit Modal -->
<div id="auditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Audit Quantity</h3>
                <button type="button" onclick="closeAuditModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div id="auditContent">
                <!-- Content will be populated by JavaScript -->
            </div>
            
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="closeAuditModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors">
                    Cancel
                </button>
                <button type="button" onclick="submitAudit()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                    Update Quantity
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Image Preview Modal -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-10 mx-auto p-5 w-full max-w-4xl">
        <div class="bg-white rounded-lg shadow-lg">
            <div class="flex items-center justify-between p-4 border-b">
                <h3 id="imageTitle" class="text-lg font-medium text-gray-900">Image Preview</h3>
                <button type="button" onclick="closeImageModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-4">
                <img id="previewImage" src="" alt="Preview" class="max-w-full h-auto mx-auto rounded-lg">
            </div>
        </div>
    </div>
</div>

<script>
let currentAuditItem = null;

function toggleDetails(itemId) {
    const details = document.getElementById('details-' + itemId);
    const icon = document.getElementById('icon-' + itemId);
    
    if (details.classList.contains('hidden')) {
        details.classList.remove('hidden');
        icon.style.transform = 'rotate(180deg)';
    } else {
        details.classList.add('hidden');
        icon.style.transform = 'rotate(0deg)';
    }
}

function openReceivedQuantityAuditModal(item) {
    currentAuditItem = {
        boq_item_id: item.boq_item_id,
        item_name: item.item_name,
        item_code: item.item_code,
        unit: item.unit,
        quantity_dispatched: item.sent_qty, // Use sent quantity as the original dispatched amount
        current_received: item.received_qty // Current confirmed received quantity
    };
    
    const content = `
        <div class="space-y-4">
            <div class="bg-gray-50 rounded-lg p-3">
                <h4 class="font-medium text-gray-900">${item.item_name}</h4>
                <p class="text-sm text-gray-600">${item.item_code} • ${item.unit}</p>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sent Quantity</label>
                    <div class="text-lg font-bold text-blue-600">${item.sent_qty} ${item.unit}</div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Currently Confirmed</label>
                    <div class="text-lg font-bold text-green-600">${item.received_qty} ${item.unit}</div>
                </div>
            </div>
            
            <div>
                <label for="auditQuantity" class="block text-sm font-medium text-gray-700 mb-2">
                    Correct Received Quantity <span class="text-red-500">*</span>
                </label>
                <input type="number" 
                       id="auditQuantity" 
                       value="${item.received_qty}"
                       min="0" 
                       max="${item.sent_qty}"
                       step="1"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <p class="text-xs text-gray-500 mt-1">Maximum: ${item.sent_qty} ${item.unit} (cannot exceed sent quantity)</p>
            </div>
            
            <div>
                <label for="auditReason" class="block text-sm font-medium text-gray-700 mb-2">
                    Reason for Correction <span class="text-red-500">*</span>
                </label>
                <textarea id="auditReason" 
                          rows="3" 
                          placeholder="Please explain why you need to correct the received quantity..."
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
            </div>
            
            <div class="bg-yellow-50 border border-yellow-200 rounded-md p-3">
                <div class="flex">
                    <svg class="h-5 w-5 text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <h4 class="text-sm font-medium text-yellow-800">Important</h4>
                        <p class="text-sm text-yellow-700 mt-1">
                            This will update your confirmed received quantity and recalculate missing items. The admin will be notified of this correction.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('auditContent').innerHTML = content;
    document.getElementById('auditModal').classList.remove('hidden');
}

function openAuditModal(item) {
    // This function is kept for backward compatibility but not used in this context
    openReceivedQuantityAuditModal(item);
}

function closeAuditModal() {
    document.getElementById('auditModal').classList.add('hidden');
    currentAuditItem = null;
}

function submitAudit() {
    if (!currentAuditItem) return;
    
    const quantity = document.getElementById('auditQuantity').value;
    const reason = document.getElementById('auditReason').value;
    
    if (!quantity || !reason.trim()) {
        alert('Please fill in all required fields.');
        return;
    }
    
    const originalQuantity = currentAuditItem.current_received || currentAuditItem.quantity_dispatched;
    const maxQuantity = currentAuditItem.quantity_dispatched;
    
    if (parseFloat(quantity) > parseFloat(maxQuantity)) {
        alert(`Received quantity cannot exceed sent quantity (${maxQuantity} ${currentAuditItem.unit}).`);
        return;
    }
    
    if (parseFloat(quantity) === parseFloat(originalQuantity)) {
        alert('The quantity is the same as the current confirmed quantity. No changes needed.');
        return;
    }
    
    const confirmMessage = `Are you sure you want to update the received quantity from ${originalQuantity} to ${quantity} ${currentAuditItem.unit}?\n\nReason: ${reason}\n\nThis will recalculate missing items and notify the admin.`;
    
    if (!confirm(confirmMessage)) {
        return;
    }
    
    // Submit audit request
    const auditData = {
        dispatch_id: <?php echo $dispatchDetails['id']; ?>,
        boq_item_id: currentAuditItem.boq_item_id,
        original_quantity: originalQuantity,
        corrected_quantity: quantity,
        reason: reason,
        request_id: <?php echo $materialRequest['id']; ?>,
        audit_type: 'received_quantity'
    };
    
    fetch('submit-quantity-audit.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(auditData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Received quantity audit submitted successfully!\n\nAudit ID: ' + data.audit_id + '\n\nThe missing items will be recalculated and the admin has been notified.');
            closeAuditModal();
            location.reload();
        } else {
            alert('Error submitting audit: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while submitting the audit.');
    });
}

function previewImage(imagePath, fileName) {
    document.getElementById('imageTitle').textContent = fileName;
    document.getElementById('previewImage').src = imagePath;
    document.getElementById('imageModal').classList.remove('hidden');
}

function closeImageModal() {
    document.getElementById('imageModal').classList.add('hidden');
}

// Close modals when clicking outside
document.getElementById('auditModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeAuditModal();
    }
});

document.getElementById('imageModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeImageModal();
    }
});

function requestMissingItems() {
    const missingItems = <?php echo json_encode($missingItems ?? []); ?>;
    
    if (missingItems.length === 0) {
        alert('No missing items to request.');
        return;
    }
    
    // Prepare request data
    const requestData = {
        original_request_id: <?php echo $materialRequest['id']; ?>,
        original_dispatch_id: <?php echo $dispatchDetails['id']; ?>,
        site_id: <?php echo $materialRequest['site_id']; ?>,
        missing_items: missingItems.map(item => ({
            boq_item_id: item.boq_item_id,
            quantity: item.missing_qty,
            unit: item.unit,
            reason: 'Missing from partial delivery - Dispatch #<?php echo $dispatchDetails['dispatch_number']; ?>'
        })),
        request_type: 'missing_items_followup',
        priority: 'high',
        notes: 'Follow-up request for missing items from partial delivery'
    };
    
    // Show confirmation
    const itemsList = missingItems.map(item => 
        `• ${item.item_name}: ${item.missing_qty} ${item.unit}`
    ).join('\n');
    
    const confirmMessage = `Request missing items?\n\nMissing Items:\n${itemsList}\n\nThis will create a new material request for the missing items.`;
    
    if (!confirm(confirmMessage)) {
        return;
    }
    
    // Submit request
    fetch('submit-missing-items-request.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Missing items request submitted successfully!\n\nRequest ID: ' + data.request_id);
            // Optionally reload the page or redirect
            location.reload();
        } else {
            alert('Error submitting request: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while submitting the request.');
    });
}
</script>