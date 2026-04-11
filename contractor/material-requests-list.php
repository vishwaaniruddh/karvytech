<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/MaterialRequest.php';
require_once __DIR__ . '/../models/Site.php';
require_once __DIR__ . '/../models/Inventory.php';
require_once __DIR__ . '/../models/BoqItem.php';

// Require vendor authentication
Auth::requireVendor();

$vendorId = Auth::getVendorId();

// Check if we're viewing a specific request
$viewRequestId = $_GET['view'] ?? null;

if ($viewRequestId) {
    // Show individual request view
    $materialRequestModel = new MaterialRequest();
    $inventoryModel = new Inventory();
    $boqModel = new BoqItem();
    
    $request = $materialRequestModel->findWithDetails($viewRequestId);
    
    // Verify the request belongs to this vendor
    if (!$request || $request['vendor_id'] != $vendorId) {
        header('Location: material-requests-list.php?error=not_found');
        exit;
    }
    
    // Get dispatch information for this request
    $dispatchDetails = $inventoryModel->getDispatchByRequestId($viewRequestId);
    $dispatchItems = [];
    $deliveryConfirmation = null;
    
    if ($dispatchDetails) {
        $dispatchItems = $inventoryModel->getDispatchItemsSummary($dispatchDetails['id']);
        $deliveryConfirmation = $inventoryModel->getDeliveryConfirmationDetails($dispatchDetails['id']);
    }
    
    $title = 'Material Request #' . $request['id'];
    ob_start();
    ?>
    
    <!-- Compact Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <a href="material-requests-list.php" class="text-blue-600 hover:text-blue-800">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                    </svg>
                </a>
                <h1 class="text-xl font-bold text-gray-900">Request #<?php echo $request['id']; ?></h1>
                <?php
                $statusColors = [
                    'draft' => 'bg-gray-100 text-gray-800',
                    'pending' => 'bg-yellow-100 text-yellow-800',
                    'approved' => 'bg-green-100 text-green-800',
                    'dispatched' => 'bg-blue-100 text-blue-800',
                    'delivered' => 'bg-purple-100 text-purple-800',
                    'completed' => 'bg-emerald-100 text-emerald-800',
                    'rejected' => 'bg-red-100 text-red-800',
                    'partially_fulfilled' => 'bg-orange-100 text-orange-800'
                ];
                $statusColor = $statusColors[$request['status']] ?? 'bg-gray-100 text-gray-800';
                ?>
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium <?php echo $statusColor; ?>">
                    <?php echo ucfirst(str_replace('_', ' ', $request['status'])); ?>
                </span>
            </div>
            

            <?php 
            // Debug: Check button conditions
            $showButton = false;
            $debugInfo = [];
            
            if ($dispatchDetails) {
                $debugInfo[] = "Dispatch exists: YES";
                $debugInfo[] = "Dispatch status: " . ($dispatchDetails['dispatch_status'] ?? 'null');
                $debugInfo[] = "Status in array: " . (in_array($dispatchDetails['dispatch_status'], ['dispatched', 'in_transit']) ? 'YES' : 'NO');
            } else {
                $debugInfo[] = "Dispatch exists: NO";
            }
            
            if ($deliveryConfirmation) {
                $debugInfo[] = "Delivery confirmation exists: YES";
            } else {
                $debugInfo[] = "Delivery confirmation exists: NO";
            }
            
            $showButton = $dispatchDetails && in_array($dispatchDetails['dispatch_status'], ['dispatched', 'in_transit']) && !$deliveryConfirmation;
            $debugInfo[] = "Show button: " . ($showButton ? 'YES' : 'NO');
            
            // Always show button for now (for testing)
            ?>
            
            <!-- Debug info (remove after testing) -->
            <!-- <?php echo implode(' | ', $debugInfo); ?> -->
            
            <!-- Always show button for testing -->
            <a href="confirm-delivery.php?id=<?php echo $request['id']; ?>" 
               class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 transition-colors">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                Confirm Delivery
            </a>
            
            <?php if ($showButton): ?>
            <!-- Conditional button (should also show if conditions are met) -->
            <a href="confirm-delivery.php?id=<?php echo $request['id']; ?>" 
               class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition-colors ml-2">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
                Conditional
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Compact Info Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
        <!-- Request Info Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-4 py-3 border-b border-gray-200">
                <h3 class="text-sm font-semibold text-gray-900">Request Details</h3>
            </div>
            <div class="p-4">
                <table class="w-full text-sm">
                    <tr class="border-b border-gray-100">
                        <td class="py-1.5 text-gray-500 font-medium">Site Code</td>
                        <td class="py-1.5 text-gray-900 text-right"><?php echo htmlspecialchars($request['site_code'] ?? 'N/A'); ?></td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-1.5 text-gray-500 font-medium">Location</td>
                        <td class="py-1.5 text-gray-900 text-right"><?php echo htmlspecialchars($request['location'] ?? 'N/A'); ?></td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-1.5 text-gray-500 font-medium">Priority</td>
                        <td class="py-1.5 text-gray-900 text-right"><?php echo ucfirst($request['priority'] ?? 'normal'); ?></td>
                    </tr>
                    <tr>
                        <td class="py-1.5 text-gray-500 font-medium">Required</td>
                        <td class="py-1.5 text-gray-900 text-right"><?php echo date('d M Y', strtotime($request['required_date'])); ?></td>
                    </tr>
                </table>
                <?php if ($request['request_notes']): ?>
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <p class="text-xs text-gray-500 mb-1">Notes</p>
                    <p class="text-sm text-gray-900"><?php echo nl2br(htmlspecialchars($request['request_notes'])); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Status Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-4 py-3 border-b border-gray-200">
                <h3 class="text-sm font-semibold text-gray-900">Status Timeline</h3>
            </div>
            <div class="p-4 space-y-3">
                <div class="flex items-center space-x-3">
                    <div class="w-6 h-6 rounded-full bg-blue-500 flex items-center justify-center">
                        <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">Created</p>
                        <p class="text-xs text-gray-500"><?php echo date('M d, Y H:i', strtotime($request['created_at'])); ?></p>
                    </div>
                </div>
                
                <?php if ($request['status'] !== 'pending' && $request['status'] !== 'draft'): ?>
                <div class="flex items-center space-x-3">
                    <div class="w-6 h-6 rounded-full <?php echo $request['status'] === 'rejected' ? 'bg-red-500' : 'bg-green-500'; ?> flex items-center justify-center">
                        <?php if ($request['status'] === 'rejected'): ?>
                            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        <?php else: ?>
                            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">
                            <?php echo $request['status'] === 'rejected' ? 'Rejected' : 'Approved'; ?>
                        </p>
                        <p class="text-xs text-gray-500"><?php echo $request['updated_at'] ? date('M d, Y H:i', strtotime($request['updated_at'])) : 'N/A'; ?></p>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (in_array($request['status'], ['dispatched', 'delivered', 'completed'])): ?>
                <div class="flex items-center space-x-3">
                    <div class="w-6 h-6 rounded-full bg-purple-500 flex items-center justify-center">
                        <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">Dispatched</p>
                        <p class="text-xs text-gray-500">Materials sent</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Dispatch Info Card -->
        <?php if ($dispatchDetails): ?>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-4 py-3 border-b border-gray-200">
                <h3 class="text-sm font-semibold text-gray-900">Dispatch Info</h3>
            </div>
            <div class="p-4">
                <table class="w-full text-sm">
                    <tr class="border-b border-gray-100">
                        <td class="py-1.5 text-gray-500 font-medium">Number</td>
                        <td class="py-1.5 text-gray-900 text-right"><?php echo htmlspecialchars($dispatchDetails['dispatch_number']); ?></td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-1.5 text-gray-500 font-medium">Date</td>
                        <td class="py-1.5 text-gray-900 text-right"><?php echo date('d M Y', strtotime($dispatchDetails['dispatch_date'])); ?></td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-1.5 text-gray-500 font-medium">Courier</td>
                        <td class="py-1.5 text-gray-900 text-right"><?php echo htmlspecialchars($dispatchDetails['courier_name'] ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <td class="py-1.5 text-gray-500 font-medium">Status</td>
                        <td class="py-1.5 text-right">
                            <?php 
                            $dispatchStatusColors = [
                                'prepared' => 'bg-yellow-100 text-yellow-800',
                                'dispatched' => 'bg-blue-100 text-blue-800',
                                'in_transit' => 'bg-purple-100 text-purple-800',
                                'delivered' => 'bg-green-100 text-green-800',
                                'confirmed' => 'bg-emerald-100 text-emerald-800',
                                'partially_delivered' => 'bg-orange-100 text-orange-800'
                            ];
                            $dispatchStatusColor = $dispatchStatusColors[$dispatchDetails['dispatch_status']] ?? 'bg-gray-100 text-gray-800';
                            ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?php echo $dispatchStatusColor; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $dispatchDetails['dispatch_status'])); ?>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Compact Material Items Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-4">
        <div class="px-4 py-3 border-b border-gray-200">
            <h3 class="text-sm font-semibold text-gray-900">Material Items</h3>
        </div>
        <div class="overflow-x-auto">
            <?php 
            $items = json_decode($request['items'], true) ?: [];
            if (!empty($items)): 
            ?>
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Requested</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Dispatched</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Received</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($items as $item): 
                        // Get actual BOQ item details
                        $boqItemId = $item['boq_item_id'] ?? null;
                        $boqItemDetails = null;
                        if ($boqItemId) {
                            $boqItemDetails = $boqModel->find($boqItemId);
                        }
                        
                        // Find matching dispatch item if exists
                        $dispatchedQty = 0;
                        $receivedQty = 0;
                        $itemStatus = 'pending';
                        
                        if (!empty($dispatchItems)) {
                            foreach ($dispatchItems as $dispatchItem) {
                                if ($dispatchItem['boq_item_id'] == $boqItemId) {
                                    $dispatchedQty = $dispatchItem['quantity_dispatched'];
                                    break;
                                }
                            }
                        }
                        
                        // Check delivery confirmation for received quantity
                        if ($deliveryConfirmation && !empty($deliveryConfirmation['item_confirmations'])) {
                            foreach ($deliveryConfirmation['item_confirmations'] as $confirmation) {
                                if ($confirmation['boq_item_id'] == $boqItemId) {
                                    $receivedQty = $confirmation['received_quantity'] ?? 0;
                                    break;
                                }
                            }
                        }
                        
                        // Determine status
                        $requestedQty = $item['quantity'] ?? 0;
                        if ($receivedQty > 0) {
                            if ($receivedQty >= $requestedQty) {
                                $itemStatus = 'completed';
                            } else {
                                $itemStatus = 'partially_received';
                            }
                        } elseif ($dispatchedQty > 0) {
                            $itemStatus = 'dispatched';
                        }
                        
                        $statusColors = [
                            'pending' => 'bg-gray-100 text-gray-800',
                            'dispatched' => 'bg-blue-100 text-blue-800',
                            'partially_received' => 'bg-orange-100 text-orange-800',
                            'completed' => 'bg-green-100 text-green-800'
                        ];
                        $statusColor = $statusColors[$itemStatus] ?? 'bg-gray-100 text-gray-800';
                    ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded bg-blue-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">
                                        <?php echo htmlspecialchars($boqItemDetails['item_name'] ?? $item['item_name'] ?? 'Unknown Item'); ?>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <?php echo htmlspecialchars($boqItemDetails['item_code'] ?? $item['item_code'] ?? 'N/A'); ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="font-medium text-gray-900"><?php echo number_format($requestedQty); ?></div>
                            <div class="text-xs text-gray-500"><?php echo htmlspecialchars($boqItemDetails['unit'] ?? $item['unit'] ?? 'Nos'); ?></div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="font-medium <?php echo $dispatchedQty > 0 ? 'text-blue-900' : 'text-gray-400'; ?>">
                                <?php echo number_format($dispatchedQty); ?>
                            </div>
                            <div class="text-xs <?php echo $dispatchedQty > 0 ? 'text-blue-600' : 'text-gray-400'; ?>">
                                <?php echo $dispatchedQty > 0 ? 'Sent' : 'Pending'; ?>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="font-medium <?php echo $receivedQty > 0 ? 'text-green-900' : 'text-gray-400'; ?>">
                                <?php echo number_format($receivedQty); ?>
                            </div>
                            <div class="text-xs <?php echo $receivedQty > 0 ? 'text-green-600' : 'text-gray-400'; ?>">
                                <?php echo $receivedQty > 0 ? 'Confirmed' : 'Pending'; ?>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?php echo $statusColor; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $itemStatus)); ?>
                            </span>
                            <?php if ($itemStatus === 'partially_received' && $requestedQty > $receivedQty): ?>
                            <div class="text-xs text-orange-600 mt-1">
                                Missing: <?php echo number_format($requestedQty - $receivedQty); ?>
                            </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="text-center py-8">
                <svg class="mx-auto h-8 w-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                <p class="text-sm text-gray-500">No items found</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($deliveryConfirmation): ?>
    <!-- Compact Delivery Confirmation -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-4">
        <div class="px-4 py-3 border-b border-gray-200">
            <h3 class="text-sm font-semibold text-gray-900">Delivery Confirmation</h3>
        </div>
        <div class="p-4">
            <table class="w-full text-sm">
                <tr class="border-b border-gray-100">
                    <td class="py-1.5 text-gray-500 font-medium">Delivery Date</td>
                    <td class="py-1.5 text-gray-900 text-right">
                        <?php echo $deliveryConfirmation['delivery_date'] ? date('d M Y', strtotime($deliveryConfirmation['delivery_date'])) : 'Not specified'; ?>
                    </td>
                    <td class="py-1.5 text-gray-500 font-medium pl-4">Received By</td>
                    <td class="py-1.5 text-gray-900 text-right"><?php echo htmlspecialchars($deliveryConfirmation['received_by'] ?? 'Not specified'); ?></td>
                </tr>
                <tr class="border-b border-gray-100">
                    <td class="py-1.5 text-gray-500 font-medium">Delivery Time</td>
                    <td class="py-1.5 text-gray-900 text-right"><?php echo htmlspecialchars($deliveryConfirmation['delivery_time'] ?? 'Not specified'); ?></td>
                    <td class="py-1.5 text-gray-500 font-medium pl-4">Contact Phone</td>
                    <td class="py-1.5 text-gray-900 text-right"><?php echo htmlspecialchars($deliveryConfirmation['received_by_phone'] ?? 'Not provided'); ?></td>
                </tr>
                <tr>
                    <td class="py-1.5 text-gray-500 font-medium">Confirmation Date</td>
                    <td class="py-1.5 text-gray-900 text-right" colspan="3">
                        <?php echo $deliveryConfirmation['confirmation_date'] ? date('d M Y H:i', strtotime($deliveryConfirmation['confirmation_date'])) : 'Not specified'; ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <?php 
    // Check for partial delivery scenario - only if there's a delivery confirmation with partial quantities
    $hasPartialDelivery = false;
    $missingItems = [];
    
    if ($dispatchDetails && $deliveryConfirmation && !empty($deliveryConfirmation['item_confirmations']) && !empty($items)) {
        foreach ($items as $item) {
            $boqItemId = $item['boq_item_id'] ?? null;
            if (!$boqItemId) continue;
            
            $requestedQty = $item['quantity'] ?? 0;
            $dispatchedQty = 0;
            $receivedQty = 0;
            
            // Find dispatched quantity
            if (!empty($dispatchItems)) {
                foreach ($dispatchItems as $dispatchItem) {
                    if ($dispatchItem['boq_item_id'] == $boqItemId) {
                        $dispatchedQty = $dispatchItem['quantity_dispatched'];
                        break;
                    }
                }
            }
            
            // Find received quantity from delivery confirmation
            foreach ($deliveryConfirmation['item_confirmations'] as $confirmation) {
                if ($confirmation['boq_item_id'] == $boqItemId) {
                    $receivedQty = $confirmation['received_quantity'] ?? 0;
                    break;
                }
            }
            
            // Check if there's a shortage (only if delivery was confirmed and received less than dispatched)
            if ($dispatchedQty > 0 && $receivedQty < $dispatchedQty) {
                $hasPartialDelivery = true;
                $boqItemDetails = $boqModel->find($boqItemId);
                $missingItems[] = [
                    'boq_item_id' => $boqItemId,
                    'item_name' => $boqItemDetails['item_name'] ?? $item['item_name'] ?? 'Unknown Item',
                    'item_code' => $boqItemDetails['item_code'] ?? $item['item_code'] ?? 'N/A',
                    'unit' => $boqItemDetails['unit'] ?? $item['unit'] ?? 'Nos',
                    'requested' => $requestedQty,
                    'dispatched' => $dispatchedQty,
                    'received' => $receivedQty,
                    'missing' => $dispatchedQty - $receivedQty
                ];
            }
        }
    }
    ?>

    <?php if ($hasPartialDelivery && !empty($missingItems)): ?>
    <!-- Partial Delivery Management -->
    <div class="bg-orange-50 border border-orange-200 rounded-lg shadow-sm mb-4">
        <div class="px-4 py-3 border-b border-orange-200 bg-orange-100">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-orange-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <h3 class="text-sm font-semibold text-orange-900">Partial Delivery Detected</h3>
            </div>
            <p class="text-xs text-orange-700 mt-1">Some items were received in lesser quantity than dispatched.</p>
        </div>
        <div class="p-4">
            <div class="overflow-x-auto mb-4">
                <table class="w-full text-sm">
                    <thead class="bg-orange-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-orange-700 uppercase">Item</th>
                            <th class="px-3 py-2 text-center text-xs font-medium text-orange-700 uppercase">Dispatched</th>
                            <th class="px-3 py-2 text-center text-xs font-medium text-orange-700 uppercase">Received</th>
                            <th class="px-3 py-2 text-center text-xs font-medium text-orange-700 uppercase">Missing</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-orange-100">
                        <?php foreach ($missingItems as $missingItem): ?>
                        <tr>
                            <td class="px-3 py-2">
                                <div class="font-medium text-gray-900"><?php echo htmlspecialchars($missingItem['item_name']); ?></div>
                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($missingItem['item_code']); ?></div>
                            </td>
                            <td class="px-3 py-2 text-center text-gray-900">
                                <?php echo number_format($missingItem['dispatched']); ?> <?php echo htmlspecialchars($missingItem['unit']); ?>
                            </td>
                            <td class="px-3 py-2 text-center text-gray-900">
                                <?php echo number_format($missingItem['received']); ?> <?php echo htmlspecialchars($missingItem['unit']); ?>
                            </td>
                            <td class="px-3 py-2 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <?php echo number_format($missingItem['missing']); ?> <?php echo htmlspecialchars($missingItem['unit']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <?php
    $content = ob_get_clean();
    include __DIR__ . '/../includes/vendor_layout.php';
    exit;
}

// Get all material requests for this vendor (original list view)
$materialRequestModel = new MaterialRequest();
$materialRequests = $materialRequestModel->getVendorRequests($vendorId);

$title = 'My Material Requests';
ob_start();
?>

<!-- Header Section -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
        <div class="flex-1">
            <h1 class="text-3xl font-bold text-gray-900">My Material Requests</h1>
            <p class="mt-2 text-lg text-gray-600">View and track your submitted material requests</p>
        </div>
        <div class="mt-6 lg:mt-0 lg:ml-6" style="display:none;">
            <a href="material-request.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                </svg>
                New Request
            </a>
        </div>
    </div>
</div>

<!-- Material Requests Table -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="p-6">
        <?php if (empty($materialRequests)): ?>
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Material Requests</h3>
                <p class="text-gray-500 mb-4">You haven't submitted any material requests yet.</p>
                <a href="material-request.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    Create Your First Request
                </a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Site</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($materialRequests as $request): ?>
                            <tr class="hover:bg-gray-50 cursor-pointer" onclick="viewRequest(<?php echo $request['id']; ?>)">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            Request #<?php echo htmlspecialchars($request['id']); ?>
                                        </div>
                                        <?php if ($request['request_notes']): ?>
                                            <div class="text-sm text-gray-500 max-w-xs truncate" title="<?php echo htmlspecialchars($request['request_notes']); ?>">
                                                <?php echo htmlspecialchars($request['request_notes']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($request['site_id'] ?? 'N/A'); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo htmlspecialchars($request['location'] ?? 'Location not specified'); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <?php echo $request['total_items'] ?? 0; ?> items
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        Total Qty: <?php echo $request['total_quantity'] ?? 0; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $statusColors = [
                                        'draft' => 'bg-gray-100 text-gray-800',
                                        'submitted' => 'bg-blue-100 text-blue-800',
                                        'approved' => 'bg-green-100 text-green-800',
                                        'rejected' => 'bg-red-100 text-red-800',
                                        'partially_fulfilled' => 'bg-yellow-100 text-yellow-800',
                                        'fulfilled' => 'bg-green-100 text-green-800'
                                    ];
                                    $statusColor = $statusColors[$request['status']] ?? 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $statusColor; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $request['status'])); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div>Requested: <?php echo date('M d, Y', strtotime($request['request_date'])); ?></div>
                                    <div>Required: <?php echo date('M d, Y', strtotime($request['required_date'])); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="event.stopPropagation(); viewRequest(<?php echo $request['id']; ?>)" 
                                            class="text-blue-600 hover:text-blue-900 mr-3">
                                        View
                                    </button>
                                    <?php if ($request['status'] === 'draft'): ?>
                                        <a href="material-request.php?site_id=<?php echo $request['site_id']; ?>&edit=<?php echo $request['id']; ?>" 
                                           class="text-green-600 hover:text-green-900" onclick="event.stopPropagation();">
                                            Edit
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function viewRequest(requestId) {
    window.location.href = `material-requests-list.php?view=${requestId}`;
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../includes/vendor_layout.php';
?>