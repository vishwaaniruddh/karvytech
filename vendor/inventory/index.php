<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/Inventory.php';
require_once __DIR__ . '/../../models/MaterialRequest.php';
require_once __DIR__ . '/../../models/BoqItem.php';

// Require vendor authentication
Auth::requireRole(VENDOR_ROLE);

$currentUser = Auth::getCurrentUser();
$vendorId = $currentUser['vendor_id'];

$inventoryModel = new Inventory();
$materialRequestModel = new MaterialRequest();
$boqModel = new BoqItem();

// Get vendor's inventory summary
$receivedMaterials = $inventoryModel->getReceivedMaterialsForVendor($vendorId);
$materialRequests = $materialRequestModel->getVendorRequests($vendorId);

// Calculate inventory statistics
$stats = [
    'total_requests' => count($materialRequests),
    'pending_acceptance' => 0,
    'delivered_materials' => 0,
    'confirmed_materials' => 0,
    'total_items' => 0,
    'unique_materials' => []
];

foreach ($receivedMaterials as $material) {
    $status = $material['dispatch_status'] ?? 'delivered';
    if ($status === 'dispatched') $stats['pending_acceptance']++;
    if ($status === 'delivered') $stats['delivered_materials']++;
    if ($status === 'confirmed') $stats['confirmed_materials']++;
    
    // Count items
    $dispatchItems = $inventoryModel->getDispatchItems($material['id']);
    $stats['total_items'] += count($dispatchItems);
    
    // Track unique materials
    foreach ($dispatchItems as $item) {
        $stats['unique_materials'][$item['boq_item_id']] = $item['item_name'];
    }
}

$title = 'Vendor Inventory';
ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Inventory Overview</h1>
        <p class="mt-2 text-sm text-gray-700">Complete inventory and materials management for your projects</p>
    </div>
    <div class="flex items-center space-x-2">
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
            <?php echo count($stats['unique_materials']); ?> Material Types
        </span>
    </div>
</div>

<!-- Inventory Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Requests -->
    <div class="card">
        <div class="card-body">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Requests</p>
                    <p class="text-2xl font-semibold text-gray-900"><?php echo $stats['total_requests']; ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Pending Acceptance -->
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
                    <p class="text-sm font-medium text-gray-500">Pending Acceptance</p>
                    <p class="text-2xl font-semibold text-gray-900"><?php echo $stats['pending_acceptance']; ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Delivered Materials -->
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
                    <p class="text-2xl font-semibold text-gray-900"><?php echo $stats['delivered_materials']; ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Total Items -->
    <div class="card">
        <div class="card-body">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Items</p>
                    <p class="text-2xl font-semibold text-gray-900"><?php echo $stats['total_items']; ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Material Requests -->
    <div class="card">
        <div class="card-body">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Material Requests</h3>
                    <p class="text-sm text-gray-500 mt-1">Create and manage material requests</p>
                </div>
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <a href="../material-requests-list.php" class="btn btn-primary w-full">
                    View All Requests
                </a>
            </div>
        </div>
    </div>
    
    <!-- Material Received -->
    <div class="card">
        <div class="card-body">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Material Received</h3>
                    <p class="text-sm text-gray-500 mt-1">Track received materials and deliveries</p>
                </div>
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <a href="../material-received.php" class="btn btn-success w-full">
                    View Received Materials
                </a>
            </div>
        </div>
    </div>
    
    <!-- Material Dispatches -->
    <div class="card">
        <div class="card-body">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Material Dispatches</h3>
                    <p class="text-sm text-gray-500 mt-1">View dispatch history and tracking</p>
                </div>
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <a href="../material-dispatches.php" class="btn btn-secondary w-full">
                    View Dispatches
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Material Requests -->
    <div class="card">
        <div class="card-body">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Material Requests</h3>
            
            <?php if (empty($materialRequests)): ?>
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No requests yet</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating your first material request.</p>
                <div class="mt-6">
                    <a href="../material-request.php" class="btn btn-primary">
                        Create Request
                    </a>
                </div>
            </div>
            <?php else: ?>
            <div class="space-y-4">
                <?php 
                $recentRequests = array_slice($materialRequests, 0, 5);
                foreach ($recentRequests as $request): 
                ?>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-8 w-8">
                            <div class="h-8 w-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3">
                            <div class="text-sm font-medium text-gray-900">Request #<?php echo $request['id']; ?></div>
                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($request['site_code'] ?? 'N/A'); ?> • <?php echo date('M j, Y', strtotime($request['request_date'])); ?></div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <?php
                        $statusColors = [
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'approved' => 'bg-blue-100 text-blue-800',
                            'dispatched' => 'bg-purple-100 text-purple-800',
                            'delivered' => 'bg-green-100 text-green-800',
                            'rejected' => 'bg-red-100 text-red-800'
                        ];
                        $colorClass = $statusColors[$request['status']] ?? 'bg-gray-100 text-gray-800';
                        ?>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium <?php echo $colorClass; ?>">
                            <?php echo ucfirst($request['status']); ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="mt-4 text-center">
                <a href="../material-requests-list.php" class="text-sm text-blue-600 hover:text-blue-800">
                    View all requests →
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Recent Received Materials -->
    <div class="card">
        <div class="card-body">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Received Materials</h3>
            
            <?php if (empty($receivedMaterials)): ?>
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m13-8l-4 4m0 0l-4-4m4 4V3"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No materials received</h3>
                <p class="mt-1 text-sm text-gray-500">Materials dispatched by admin will appear here.</p>
            </div>
            <?php else: ?>
            <div class="space-y-4">
                <?php 
                $recentMaterials = array_slice($receivedMaterials, 0, 5);
                foreach ($recentMaterials as $material): 
                ?>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-8 w-8">
                            <div class="h-8 w-8 rounded-lg bg-green-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3">
                            <div class="text-sm font-medium text-gray-900">Request #<?php echo $material['material_request_id']; ?></div>
                            <div class="text-sm text-gray-500"><?php echo $material['dispatch_number']; ?> • <?php echo date('M j, Y', strtotime($material['dispatch_date'])); ?></div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <?php
                        $status = $material['dispatch_status'] ?? 'delivered';
                        $statusColors = [
                            'dispatched' => 'bg-yellow-100 text-yellow-800',
                            'delivered' => 'bg-green-100 text-green-800',
                            'confirmed' => 'bg-purple-100 text-purple-800'
                        ];
                        $statusLabels = [
                            'dispatched' => 'Pending',
                            'delivered' => 'Delivered',
                            'confirmed' => 'Confirmed'
                        ];
                        $colorClass = $statusColors[$status] ?? 'bg-gray-100 text-gray-800';
                        $statusLabel = $statusLabels[$status] ?? ucfirst($status);
                        ?>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium <?php echo $colorClass; ?>">
                            <?php echo $statusLabel; ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="mt-4 text-center">
                <a href="../material-received.php" class="text-sm text-green-600 hover:text-green-800">
                    View all materials →
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/vendor_layout.php';
?>
</content>