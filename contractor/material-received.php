<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/MaterialRequest.php';
require_once __DIR__ . '/../models/Inventory.php';
require_once __DIR__ . '/../models/BoqItem.php';

// Require vendor authentication
Auth::requireRole(VENDOR_ROLE);

$currentUser = Auth::getCurrentUser();
$vendorId = $currentUser['vendor_id'];

$materialRequestModel = new MaterialRequest();
$inventoryModel = new Inventory();
$boqModel = new BoqItem();

// Get received materials for this vendor (delivered dispatches)
$receivedMaterials = $inventoryModel->getReceivedMaterialsForVendor($vendorId);

// Group materials by dispatch
$dispatchGroups = [];
foreach ($receivedMaterials as $material) {
    $dispatchId = $material['id'];
    if (!isset($dispatchGroups[$dispatchId])) {
        $dispatchGroups[$dispatchId] = [
            'dispatch_info' => $material,
            'items' => []
        ];
    }
}

// Get items for each dispatch
foreach ($dispatchGroups as $dispatchId => &$group) {
    $group['items'] = $inventoryModel->getDispatchItemsSummary($dispatchId);
    
    // Get request info if available
    $requestId = $group['dispatch_info']['material_request_id'] ?? null;
    if ($requestId) {
        $group['request_info'] = $materialRequestModel->findWithDetails($requestId);
    }
}

// Calculate statistics
$stats = [
    'total_dispatches' => count($dispatchGroups),
    'pending' => 0,
    'delivered' => 0,
    'confirmed' => 0,
    'total_items' => 0
];

foreach ($dispatchGroups as $group) {
    $status = $group['dispatch_info']['dispatch_status'] ?? 'delivered';
    if ($status === 'dispatched') $stats['pending']++;
    elseif ($status === 'delivered') $stats['delivered']++;
    elseif ($status === 'confirmed') $stats['confirmed']++;
    $stats['total_items'] += count($group['items']);
}

$title = 'Material Received';
ob_start();
?>

<div class="mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Materials Received</h1>
    <p class="mt-2 text-sm text-gray-700">Track and manage materials sent to you from Karvy</p>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Pending Acceptance</p>
                <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo $stats['pending']; ?></p>
            </div>
            <div class="bg-yellow-100 rounded-full p-3">
                <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                </svg>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Delivered</p>
                <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo $stats['delivered']; ?></p>
            </div>
            <div class="bg-green-100 rounded-full p-3">
                <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"></path>
                    <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z"></path>
                </svg>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-purple-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Confirmed</p>
                <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo $stats['confirmed']; ?></p>
            </div>
            <div class="bg-purple-100 rounded-full p-3">
                <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Receipts</p>
                <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo $stats['total_items']; ?></p>
            </div>
            <div class="bg-blue-100 rounded-full p-3">
                <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Dispatch Cards -->
<?php if (empty($dispatchGroups)): ?>
<div class="bg-white rounded-lg shadow-sm p-12 text-center">
    <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-2.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 009.586 13H7"></path>
    </svg>
    <h3 class="mt-4 text-lg font-medium text-gray-900">No Materials Received</h3>
    <p class="mt-2 text-sm text-gray-500">You haven't received any materials from Karvy yet.</p>
</div>
<?php else: ?>
<div class="grid grid-cols-1 gap-6">
    <?php foreach ($dispatchGroups as $dispatchId => $group): 
        $dispatch = $group['dispatch_info'];
        $items = $group['items'];
        $status = $dispatch['dispatch_status'] ?? 'delivered';
        
        // Status styling
        $statusConfig = [
            'dispatched' => ['bg' => 'bg-yellow-50', 'border' => 'border-yellow-200', 'badge' => 'bg-yellow-100 text-yellow-800', 'label' => 'Pending Acceptance'],
            'delivered' => ['bg' => 'bg-green-50', 'border' => 'border-green-200', 'badge' => 'bg-green-100 text-green-800', 'label' => 'Delivered'],
            'confirmed' => ['bg' => 'bg-purple-50', 'border' => 'border-purple-200', 'badge' => 'bg-purple-100 text-purple-800', 'label' => 'Confirmed']
        ];
        $config = $statusConfig[$status] ?? $statusConfig['delivered'];
    ?>
    
    <!-- Dispatch Card -->
    <div class="bg-white rounded-lg shadow-sm border <?php echo $config['border']; ?> overflow-hidden">
        <!-- Card Header -->
        <div class="<?php echo $config['bg']; ?> px-6 py-4 border-b <?php echo $config['border']; ?>">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 rounded-lg bg-white shadow-sm flex items-center justify-center">
                            <svg class="w-6 h-6 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"></path>
                                <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z"></path>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            Receipt #<?php echo htmlspecialchars($dispatch['dispatch_number']); ?>
                        </h3>
                        <?php if (!empty($group['request_info'])): ?>
                        <div class="text-xs text-gray-600 mt-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-blue-100 text-blue-800">
                                Request #<?php echo $group['request_info']['id']; ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        <div class="flex items-center space-x-3 mt-1 text-sm text-gray-600">
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                </svg>
                                <?php echo htmlspecialchars($dispatch['site_code'] ?? 'N/A'); ?>
                            </span>
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                </svg>
                                Sent: <?php echo $dispatch['dispatch_date'] ? date('d M Y', strtotime($dispatch['dispatch_date'])) : 'N/A'; ?>
                            </span>
                            <?php if ($dispatch['courier_name']): ?>
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"></path>
                                    <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z"></path>
                                </svg>
                                <?php echo htmlspecialchars($dispatch['courier_name']); ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?php echo $config['badge']; ?>">
                        <?php echo $config['label']; ?>
                    </span>
                    <div class="text-sm text-gray-600 mt-2">
                        <?php echo count($items); ?> item<?php echo count($items) != 1 ? 's' : ''; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Card Body - Items Grid -->
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php if (empty($items)): ?>
                <div class="col-span-full text-center py-8 text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-2.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 009.586 13H7"></path>
                    </svg>
                    <p class="mt-2 text-sm">No items in this dispatch</p>
                </div>
                <?php else: ?>
                <?php foreach ($items as $item): ?>
                <!-- Item Card -->
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 hover:shadow-md transition-shadow">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="h-10 w-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                <i class="<?php echo $item['icon_class'] ?? 'fas fa-cube'; ?> text-blue-600"></i>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                <?php echo htmlspecialchars($item['item_name'] ?? 'Unknown Item'); ?>
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                <?php echo htmlspecialchars($item['item_code'] ?? 'N/A'); ?>
                            </p>
                            <div class="mt-2 flex items-center justify-between">
                                <span class="text-lg font-bold text-gray-900">
                                    <?php echo number_format($item['quantity_dispatched'] ?? 0); ?>
                                </span>
                                <span class="text-xs text-gray-500">
                                    <?php echo htmlspecialchars($item['unit'] ?? 'units'); ?>
                                </span>
                            </div>
                            <?php if (!empty($item['batch_number'])): ?>
                            <div class="mt-2 text-xs text-gray-600">
                                <span class="font-medium">Batch:</span> <?php echo htmlspecialchars($item['batch_number']); ?>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($item['serial_numbers'])): ?>
                            <div class="mt-1 text-xs text-gray-600">
                                <span class="font-medium">Serial:</span> <?php echo htmlspecialchars(substr($item['serial_numbers'], 0, 30)); ?><?php echo strlen($item['serial_numbers']) > 30 ? '...' : ''; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Delivery Info -->
            <?php if ($dispatch['delivery_address'] || $dispatch['tracking_number']): ?>
            <div class="mt-6 pt-6 border-t border-gray-200">
                <h4 class="text-sm font-medium text-gray-900 mb-3">Delivery Information</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <?php if ($dispatch['delivery_address']): ?>
                    <div>
                        <span class="font-medium text-gray-700">Address:</span>
                        <p class="text-gray-600 mt-1"><?php echo nl2br(htmlspecialchars($dispatch['delivery_address'])); ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if ($dispatch['tracking_number']): ?>
                    <div>
                        <span class="font-medium text-gray-700">Tracking Number:</span>
                        <p class="text-gray-600 mt-1 font-mono"><?php echo htmlspecialchars($dispatch['tracking_number']); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Actions -->
            <?php if ($status === 'dispatched'): ?>
            <div class="mt-6 pt-6 border-t border-gray-200 flex justify-end space-x-3">
                <button onclick="acceptDispatch(<?php echo $dispatchId; ?>)" class="btn btn-success">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    Confirm Receipt
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<script>
function acceptDispatch(dispatchId) {
    if (!confirm('Confirm that you have received all materials in this shipment?\n\nThis will automatically update the status to "Delivered" in Karvy\'s system.')) {
        return;
    }
    
    fetch('process-material-acceptance.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            dispatch_id: dispatchId,
            action: 'accept_all'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Receipt confirmed successfully!\n\nStatus has been updated to "Delivered" in Karvy\'s system.');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while confirming receipt.');
    });
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../includes/vendor_layout.php';
?>
