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

// Group materials by material request ID
$materialsByRequest = [];
foreach ($receivedMaterials as $material) {
    $requestId = $material['material_request_id'] ?? 'no_request';
    if (!isset($materialsByRequest[$requestId])) {
        $materialsByRequest[$requestId] = [
            'request_info' => null,
            'dispatches' => []
        ];
    }
    
    // Get request info if not already set
    if (!$materialsByRequest[$requestId]['request_info'] && $requestId !== 'no_request') {
        $requestInfo = $materialRequestModel->findWithDetails($requestId);
        $materialsByRequest[$requestId]['request_info'] = $requestInfo;
    }
    
    $materialsByRequest[$requestId]['dispatches'][] = $material;
}

$title = 'Material Received';
ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Material Received</h1>
        <p class="mt-2 text-sm text-gray-700">Track and manage received materials from admin</p>
    </div>
    <div class="flex items-center space-x-2">
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
            <?php echo count($materialsByRequest); ?> Request(s)
        </span>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
            <?php echo count($receivedMaterials); ?> Dispatch(es)
        </span>
    </div>
</div>

<!-- Received Status Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <?php
    $statusCounts = [
        'pending' => 0,
        'delivered' => 0,
        'confirmed' => 0,
        'total_items' => 0
    ];
    
    foreach ($materialsByRequest as $requestId => $requestData) {
        foreach ($requestData['dispatches'] as $material) {
            $status = $material['dispatch_status'] ?? 'delivered';
            if ($status === 'dispatched') $statusCounts['pending']++;
            if ($status === 'delivered') $statusCounts['delivered']++;
            if ($status === 'confirmed') $statusCounts['confirmed']++;
            
            // Count items in stock
            $items = json_decode($material['item_confirmations'] ?? '[]', true);
            $statusCounts['total_items'] += count($items);
        }
    }
    ?>
    
    <div class="card">
        <div class="card-body">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Acceptance Pending</p>
                    <p class="text-2xl font-semibold text-gray-900"><?php echo $statusCounts['pending']; ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Delivered</p>
                    <p class="text-2xl font-semibold text-gray-900"><?php echo $statusCounts['delivered']; ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Confirmed</p>
                    <p class="text-2xl font-semibold text-gray-900"><?php echo $statusCounts['confirmed']; ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Confirmed</p>
                    <p class="text-2xl font-semibold text-gray-900"><?php echo $statusCounts['confirmed']; ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 2L3 7v11a1 1 0 001 1h12a1 1 0 001-1V7l-7-5zM8 15a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Items</p>
                    <p class="text-2xl font-semibold text-gray-900"><?php echo $statusCounts['total_items']; ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Received Materials List -->
<div class="card">
    <div class="card-body">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Material Requests & Dispatches</h3>
        
        <?php if (empty($materialsByRequest)): ?>
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m13-8l-4 4m0 0l-4-4m4 4V3"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No materials received</h3>
            <p class="mt-1 text-sm text-gray-500">No materials have been received from admin yet.</p>
        </div>
        <?php else: ?>
        <div class="space-y-8">
            <?php foreach ($materialsByRequest as $requestId => $requestData): ?>
            
            <!-- Material Request Group -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <!-- Request Header -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-200 rounded-t-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-12 w-12">
                                <div class="h-12 w-12 rounded-lg bg-blue-100 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    <?php if ($requestId === 'no_request'): ?>
                                        Direct Dispatches (No Request)
                                    <?php else: ?>
                                        Material Request #<?php echo $requestId; ?>
                                    <?php endif; ?>
                                </h3>
                                <?php if ($requestData['request_info']): ?>
                                <div class="text-sm text-gray-600 mt-1">
                                    <span class="font-medium">Site:</span> <?php echo htmlspecialchars($requestData['request_info']['site_code'] ?? 'N/A'); ?>
                                    <span class="mx-2">•</span>
                                    <span class="font-medium">Requested:</span> <?php echo date('d M Y', strtotime($requestData['request_info']['request_date'])); ?>
                                    <?php if ($requestData['request_info']['request_notes']): ?>
                                    <span class="mx-2">•</span>
                                    <span class="font-medium">Notes:</span> <?php echo htmlspecialchars(substr($requestData['request_info']['request_notes'], 0, 50)); ?><?php echo strlen($requestData['request_info']['request_notes']) > 50 ? '...' : ''; ?>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-gray-500">
                                <?php echo count($requestData['dispatches']); ?> dispatch(es)
                            </div>
                            <?php
                            // Calculate overall status for this request
                            $allConfirmed = true;
                            $anyPending = false;
                            foreach ($requestData['dispatches'] as $dispatch) {
                                if ($dispatch['dispatch_status'] === 'dispatched') {
                                    $anyPending = true;
                                    $allConfirmed = false;
                                } elseif ($dispatch['dispatch_status'] !== 'confirmed') {
                                    $allConfirmed = false;
                                }
                            }
                            
                            if ($allConfirmed): ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    All Confirmed
                                </span>
                            <?php elseif ($anyPending): ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                    </svg>
                                    Pending Acceptance
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                    Delivered
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Dispatches in this Request -->
                <div class="divide-y divide-gray-200">
                    <?php foreach ($requestData['dispatches'] as $material): ?>
                    <!-- Individual Dispatch -->
                    <div class="p-6">
                        <!-- Dispatch Header -->
                        <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8">
                                    <div class="h-8 w-8 rounded-lg bg-green-100 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">Dispatch #<?php echo $material['dispatch_number']; ?></div>
                                    <div class="text-xs text-gray-500">Received: <?php echo $material['delivery_date'] ? date('d M Y', strtotime($material['delivery_date'])) : 'N/A'; ?></div>
                                </div>
                            </div>
                            <div class="text-right">
                                <?php
                                $status = $material['dispatch_status'] ?? 'delivered';
                                $statusColors = [
                                    'dispatched' => 'bg-yellow-100 text-yellow-800',
                                    'delivered' => 'bg-green-100 text-green-800',
                                    'confirmed' => 'bg-purple-100 text-purple-800'
                                ];
                                $statusLabels = [
                                    'dispatched' => 'Acceptance Pending',
                                    'delivered' => 'Delivered',
                                    'confirmed' => 'Confirmed'
                                ];
                                $colorClass = $statusColors[$status] ?? 'bg-gray-100 text-gray-800';
                                $statusLabel = $statusLabels[$status] ?? ucfirst($status);
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $colorClass; ?>">
                                    <?php echo $statusLabel; ?>
                                </span>
                                <div class="text-xs text-gray-500 mt-1">Site: <?php echo htmlspecialchars($material['site_code'] ?? 'N/A'); ?></div>
                            </div>
                        </div>
                        
                        <!-- Material Items for this Dispatch -->
                        <div class="mt-4">
                    <?php if ($material['dispatch_status'] === 'dispatched'): ?>
                    <h4 class="text-md font-medium text-gray-900 mb-3">Request Summary</h4>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-blue-800">
                                    Request #<?php echo $material['material_request_id']; ?> contains 
                                    <?php 
                                    $dispatchItems = $inventoryModel->getDispatchItems($material['id']);
                                    $totalItems = count($dispatchItems);
                                    $totalQuantity = 0;
                                    foreach ($dispatchItems as $dispatchItem) {
                                        $totalQuantity += $dispatchItem['quantity_dispatched'] ?? 1;
                                    }
                                    
                                    // Count accepted items
                                    $existingConfirmations = json_decode($material['item_confirmations'] ?? '[]', true);
                                    $acceptedItemTypes = count($existingConfirmations);
                                    $uniqueBoqItems = array_unique(array_column($dispatchItems, 'boq_item_id'));
                                    $totalItemTypes = count($uniqueBoqItems);
                                    
                                    echo $totalItems . ' different materials with total quantity of ' . number_format($totalQuantity) . ' units';
                                    ?>
                                </p>
                                <?php if ($acceptedItemTypes > 0): ?>
                                <div class="mt-2">
                                    <div class="flex items-center justify-between text-xs text-blue-700">
                                        <span>Individual Acceptance Progress</span>
                                        <span><?php echo $acceptedItemTypes; ?>/<?php echo $totalItemTypes; ?> item types accepted</span>
                                    </div>
                                    <div class="w-full bg-blue-200 rounded-full h-2 mt-1">
                                        <div class="bg-blue-600 h-2 rounded-full" style="width: <?php echo ($acceptedItemTypes / $totalItemTypes) * 100; ?>%"></div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <p class="text-xs text-blue-600 mt-1">
                                    <?php if ($acceptedItemTypes == $totalItemTypes): ?>
                                        All items individually accepted! Click "Accept Request" to finalize.
                                    <?php else: ?>
                                        Accept items individually or click "Accept Request" to confirm receipt of all materials
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <h4 class="text-md font-medium text-gray-900 mb-3">Material Details</h4>
                    <?php else: ?>
                    <h4 class="text-md font-medium text-gray-900 mb-3">Received Items</h4>
                    <?php endif; ?>
                    
                    <?php
                    $itemConfirmations = json_decode($material['item_confirmations'] ?? '[]', true);
                    $showDispatchItems = ($material['dispatch_status'] === 'dispatched' && empty($itemConfirmations));
                    
                    if (!empty($itemConfirmations) || $showDispatchItems):
                        // For dispatched materials without confirmations, get dispatch items
                        if ($showDispatchItems) {
                            $dispatchItems = $inventoryModel->getDispatchItems($material['id']);
                        }
                    ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Material</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <?php echo $showDispatchItems ? 'Dispatched Qty' : 'Received Qty'; ?>
                                    </th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <?php echo $showDispatchItems ? 'Status' : 'Condition'; ?>
                                    </th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Individual Records</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                                    <?php if ($showDispatchItems): ?>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php 
                                $itemsToShow = $showDispatchItems ? $dispatchItems : $itemConfirmations;
                                foreach ($itemsToShow as $item): 
                                    $boqItem = $boqModel->find($item['boq_item_id']); 
                                ?>
                                <tr>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <?php if ($boqItem): ?>
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-8 w-8">
                                                    <div class="h-8 w-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                                        <i class="<?php echo $boqItem['icon_class'] ?: 'fas fa-cube'; ?> text-blue-600 text-xs"></i>
                                                    </div>
                                                </div>
                                                <div class="ml-3">
                                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($boqItem['item_name']); ?></div>
                                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($boqItem['item_code']); ?></div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-sm text-gray-500">Item not found</div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php 
                                            $quantity = $showDispatchItems ? ($item['quantity_dispatched'] ?? 1) : ($item['received_quantity'] ?? 0);
                                            echo number_format($quantity); 
                                            ?>
                                        </div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($boqItem['unit'] ?? 'Units'); ?></div>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <?php if ($showDispatchItems): ?>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Awaiting Acceptance
                                            </span>
                                        <?php else: ?>
                                            <?php
                                            $condition = $item['condition'] ?? 'good';
                                            $conditionColors = [
                                                'good' => 'bg-green-100 text-green-800',
                                                'damaged' => 'bg-red-100 text-red-800',
                                                'partial' => 'bg-yellow-100 text-yellow-800',
                                                'missing' => 'bg-gray-100 text-gray-800'
                                            ];
                                            $conditionClass = $conditionColors[$condition] ?? 'bg-gray-100 text-gray-800';
                                            ?>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium <?php echo $conditionClass; ?>">
                                                <?php echo ucfirst($condition); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-3 py-2">
                                        <?php
                                        // Check if there are individual records
                                        $individualRecords = null;
                                        if ($showDispatchItems) {
                                            $individualRecords = json_decode($item['serial_numbers'] ?? '[]', true);
                                        } else {
                                            $individualRecords = json_decode($material['individual_records'] ?? '[]', true);
                                        }
                                        
                                        if (!empty($individualRecords)):
                                        ?>
                                        <div class="text-xs space-y-1">
                                            <?php foreach ($individualRecords as $record): ?>
                                            <div class="bg-gray-50 px-2 py-1 rounded">
                                                <span class="font-medium">SN:</span> <?php echo htmlspecialchars($record['serial_number'] ?? 'N/A'); ?>
                                                <span class="ml-2 font-medium">Qty:</span> <?php echo $record['quantity'] ?? 1; ?>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php else: ?>
                                        <div class="text-sm text-gray-500">Cumulative</div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-3 py-2">
                                        <div class="text-sm text-gray-900">
                                            <?php 
                                            $notes = $showDispatchItems ? ($item['remarks'] ?? 'No notes') : ($item['notes'] ?? 'No notes');
                                            echo htmlspecialchars($notes); 
                                            ?>
                                        </div>
                                    </td>
                                    <?php if ($showDispatchItems): ?>
                                    <td class="px-3 py-2">
                                        <?php
                                        // Check if this item has been individually accepted
                                        $existingConfirmations = json_decode($material['item_confirmations'] ?? '[]', true);
                                        $isAccepted = false;
                                        foreach ($existingConfirmations as $confirmation) {
                                            if ($confirmation['boq_item_id'] == $item['boq_item_id']) {
                                                $isAccepted = true;
                                                break;
                                            }
                                        }
                                        
                                        if ($isAccepted): ?>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                Accepted
                                            </span>
                                        <?php else: ?>
                                            <button onclick="acceptIndividualItem(<?php echo $material['id']; ?>, <?php echo $item['boq_item_id']; ?>, '<?php echo htmlspecialchars($boqItem['item_name'] ?? 'Unknown'); ?>')" 
                                                    class="inline-flex items-center px-2 py-1 border border-transparent text-xs leading-4 font-medium rounded text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                Accept Item
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                    <div class="text-sm text-gray-500">No item details available</div>
                    <?php endif; ?>
                </div>
                
                        <!-- Dispatch-level Actions -->
                        <?php if ($material['dispatch_status'] === 'dispatched'): ?>
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-600">
                                    <strong>Dispatch #<?php echo $material['dispatch_number']; ?></strong> is ready for acceptance
                                </div>
                                <div class="flex space-x-2">
                                    <button onclick="acceptRequest(<?php echo $material['id']; ?>, <?php echo $material['material_request_id']; ?>)" 
                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        Accept Dispatch
                                    </button>
                                    <button onclick="viewRequestDetails(<?php echo $material['material_request_id']; ?>)" 
                                            class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path>
                                        </svg>
                                        View Details
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Delivery Information -->
                        <?php if (!empty($material['delivery_notes']) || !empty($material['lr_copy_path'])): ?>
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">Delivery Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <?php if (!empty($material['delivery_notes'])): ?>
                                <div>
                                    <label class="text-xs font-medium text-gray-500">Delivery Notes</label>
                                    <div class="text-sm text-gray-900"><?php echo nl2br(htmlspecialchars($material['delivery_notes'])); ?></div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($material['lr_copy_path'])): ?>
                                <div>
                                    <label class="text-xs font-medium text-gray-500">LR Copy</label>
                                    <div class="text-sm">
                                        <a href="<?php echo BASE_URL . '/' . $material['lr_copy_path']; ?>" target="_blank" class="text-blue-600 hover:text-blue-800">
                                            <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                            </svg>
                                            View LR Copy
                                        </a>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Request-level Summary Actions -->
                <?php 
                $hasAnyPending = false;
                foreach ($requestData['dispatches'] as $dispatch) {
                    if ($dispatch['dispatch_status'] === 'dispatched') {
                        $hasAnyPending = true;
                        break;
                    }
                }
                ?>
                <?php if ($hasAnyPending && $requestId !== 'no_request'): ?>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            <strong>Material Request #<?php echo $requestId; ?></strong> - Accept all pending dispatches at once
                        </div>
                        <button onclick="acceptAllDispatches(<?php echo $requestId; ?>)" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Accept All Dispatches
                        </button>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function acceptIndividualItem(dispatchId, boqItemId, itemName) {
    if (!confirm('Are you sure you want to accept "' + itemName + '"? This will confirm receipt of this specific item.')) {
        return;
    }
    
    // Show loading state
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<svg class="animate-spin h-3 w-3 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
    button.disabled = true;
    
    // Create form data
    const formData = new FormData();
    formData.append('dispatch_id', dispatchId);
    formData.append('boq_item_id', boqItemId);
    formData.append('action', 'accept_individual_item');
    
    fetch('process-request-acceptance.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('"' + itemName + '" accepted successfully!', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert('Error: ' + data.message, 'error');
            button.innerHTML = originalText;
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while accepting the item.', 'error');
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function acceptRequest(dispatchId, requestId) {
    if (!confirm('Are you sure you want to accept Request #' + requestId + '? This will confirm receipt of all materials in this request.')) {
        return;
    }
    
    // Show loading state
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Processing...';
    button.disabled = true;
    
    // Create form data
    const formData = new FormData();
    formData.append('dispatch_id', dispatchId);
    formData.append('request_id', requestId);
    formData.append('action', 'accept_request');
    
    fetch('process-request-acceptance.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Request #' + requestId + ' accepted successfully!', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert('Error: ' + data.message, 'error');
            button.innerHTML = originalText;
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while accepting the request.', 'error');
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function acceptAllDispatches(requestId) {
    if (!confirm('Are you sure you want to accept ALL pending dispatches for Request #' + requestId + '? This will confirm receipt of all materials in all pending dispatches for this request.')) {
        return;
    }
    
    // Show loading state
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Processing...';
    button.disabled = true;
    
    // Create form data
    const formData = new FormData();
    formData.append('request_id', requestId);
    formData.append('action', 'accept_all_dispatches');
    
    fetch('process-request-acceptance.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('All dispatches for Request #' + requestId + ' accepted successfully!', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert('Error: ' + data.message, 'error');
            button.innerHTML = originalText;
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while accepting the dispatches.', 'error');
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function viewRequestDetails(requestId) {
    // Open request details in a new tab or modal
    window.open('material-requests-list.php?request_id=' + requestId, '_blank');
}

function showAlert(message, type) {
    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = `fixed top-4 right-4 z-50 p-4 rounded-md shadow-lg max-w-md ${
        type === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 
        'bg-red-100 text-red-800 border border-red-200'
    }`;
    alertDiv.innerHTML = `
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                ${type === 'success' ? 
                    '<path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>' :
                    '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>'
                }
            </svg>
            <span class="flex-1">${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-lg font-bold hover:opacity-75">&times;</button>
        </div>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Remove alert after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentElement) {
            alertDiv.remove();
        }
    }, 5000);
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../includes/vendor_layout.php';
?>