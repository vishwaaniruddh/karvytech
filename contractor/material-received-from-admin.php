<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/Inventory.php';

// Require contractor authentication
Auth::requireRole(VENDOR_ROLE);

$currentUser = Auth::getCurrentUser();
$vendorId = $currentUser['vendor_id'];

$dispatchId = $_GET['id'] ?? null;

if (!$dispatchId) {
    header('Location: inventory/index.php');
    exit;
}

$inventoryModel = new Inventory();

// Get dispatch details
$dispatch = $inventoryModel->getDispatchById($dispatchId);

if (!$dispatch || $dispatch['vendor_id'] != $vendorId) {
    header('Location: inventory/index.php');
    exit;
}

// Get dispatch items with details
$dispatchItems = $inventoryModel->getDispatchItemsSummary($dispatchId);

$title = 'Material Receipt Details - ' . $dispatch['dispatch_number'];
ob_start();
?>

<!-- Compact Header -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 mb-3">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2">
            <a href="inventory/index.php" class="text-blue-600 hover:text-blue-800">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                </svg>
            </a>
            <h1 class="text-lg font-semibold text-gray-900">Receipt Details - <?php echo htmlspecialchars($dispatch['dispatch_number']); ?></h1>
        </div>
        
        <?php if (($dispatch['dispatch_status'] ?? 'dispatched') === 'dispatched' || ($dispatch['dispatch_status'] ?? 'dispatched') === 'in_transit'): ?>
        <a href="confirm-delivery.php?dispatch_id=<?php echo $dispatch['id']; ?>" class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-white bg-green-600 hover:bg-green-700 transition-colors">
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
    $status = $dispatch['dispatch_status'] ?? 'dispatched';
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
                <span class="text-xs text-gray-900"><?php echo htmlspecialchars($dispatch['site_code'] ?? 'N/A'); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-xs font-medium text-gray-500">Location</span>
                <span class="text-xs text-gray-900"><?php echo htmlspecialchars($dispatch['location'] ?? 'N/A'); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-xs font-medium text-gray-500">Address</span>
                <span class="text-xs text-gray-900"><?php echo htmlspecialchars($dispatch['delivery_address'] ?? 'N/A'); ?></span>
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
                <span class="text-xs font-medium text-gray-500">Dispatch Number</span>
                <span class="text-xs text-gray-900"><?php echo htmlspecialchars($dispatch['dispatch_number']); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-xs font-medium text-gray-500">Dispatch Date</span>
                <span class="text-xs text-gray-900"><?php echo $dispatch['dispatch_date'] ? date('d M Y', strtotime($dispatch['dispatch_date'])) : 'N/A'; ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-xs font-medium text-gray-500">Courier Name</span>
                <span class="text-xs text-gray-900"><?php echo htmlspecialchars($dispatch['courier_name'] ?? 'N/A'); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-xs font-medium text-gray-500">Tracking Number</span>
                <span class="text-xs text-gray-900"><?php echo htmlspecialchars($dispatch['tracking_number'] ?? 'N/A'); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-xs font-medium text-gray-500">Expected Delivery</span>
                <span class="text-xs text-gray-900"><?php echo $dispatch['expected_delivery_date'] ? date('d M Y', strtotime($dispatch['expected_delivery_date'])) : 'N/A'; ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-xs font-medium text-gray-500">Contact Person</span>
                <span class="text-xs text-gray-900"><?php echo htmlspecialchars($dispatch['contact_person_name'] ?? 'N/A'); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-xs font-medium text-gray-500">Contact Phone</span>
                <span class="text-xs text-gray-900"><?php echo htmlspecialchars($dispatch['contact_person_phone'] ?? 'N/A'); ?></span>
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
            Dispatched Materials
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
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m13-8V4a1 1 0 00-1-1H7a1 1 0 00-1 1v1m8 0V4a1 1 0 00-1-1H9a1 1 0 00-1 1v1"></path>
                    </svg>
                    <p class="mt-2 text-sm text-gray-600">No items found in this dispatch</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Confirmation Details (if confirmed) -->
<?php if ($status === 'confirmed' && !empty($dispatch['delivery_remarks'])): ?>
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 mb-4">
    <h3 class="text-sm font-semibold text-gray-900 mb-3 flex items-center">
        <svg class="w-4 h-4 mr-1 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
        </svg>
        Receipt Confirmation Details
    </h3>
    <div class="bg-purple-50 rounded-lg p-3 border border-purple-100">
        <div class="text-sm text-purple-900">
            <?php echo nl2br(htmlspecialchars($dispatch['delivery_remarks'])); ?>
        </div>
        <?php if ($dispatch['delivery_date']): ?>
        <div class="mt-2 text-xs text-purple-700">
            Confirmed on: <?php echo date('d M Y H:i', strtotime($dispatch['delivery_date'])); ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Uploaded Documents -->
<?php if (!empty($dispatch['lr_copy_path']) || !empty($dispatch['additional_documents'])): ?>
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 mb-4">
    <h3 class="text-sm font-semibold text-gray-900 mb-3 flex items-center">
        <svg class="w-4 h-4 mr-1 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
        </svg>
        Uploaded Documents
    </h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
        <!-- LR Copy / Delivery Receipt -->
        <?php if (!empty($dispatch['lr_copy_path'])): ?>
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
                <a href="../<?php echo htmlspecialchars($dispatch['lr_copy_path']); ?>" 
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
        if (!empty($dispatch['additional_documents'])):
            $additionalDocs = is_array($dispatch['additional_documents']) ? 
                             $dispatch['additional_documents'] : 
                             json_decode($dispatch['additional_documents'], true);
            
            if (is_array($additionalDocs)):
                foreach ($additionalDocs as $index => $docPath):
                    $fileName = basename($docPath);
                    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    
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
    
    <?php if (empty($dispatch['lr_copy_path']) && empty($dispatch['additional_documents'])): ?>
    <div class="text-center py-4">
        <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <p class="mt-2 text-xs text-gray-500">No documents uploaded yet</p>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Delivery Remarks -->
<?php if (!empty($dispatch['dispatch_remarks']) && $status !== 'confirmed'): ?>
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 mb-4">
    <h3 class="text-sm font-semibold text-gray-900 mb-3">Delivery Remarks</h3>
    <div class="bg-gray-50 rounded-lg p-3">
        <p class="text-xs text-gray-700"><?php echo nl2br(htmlspecialchars($dispatch['dispatch_remarks'])); ?></p>
    </div>
</div>
<?php endif; ?>

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

function previewImage(imagePath, fileName) {
    document.getElementById('imageTitle').textContent = fileName;
    document.getElementById('previewImage').src = imagePath;
    document.getElementById('imageModal').classList.remove('hidden');
}

function closeImageModal() {
    document.getElementById('imageModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('imageModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeImageModal();
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../includes/vendor_layout.php';
?>