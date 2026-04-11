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

// Get dispatched material requests for this vendor
$dispatchedRequests = $materialRequestModel->getDispatchedRequestsForVendor($vendorId);

$title = 'Material Dispatches';
ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Material Dispatches</h1>
        <p class="mt-2 text-sm text-gray-700">Track and confirm material deliveries</p>
    </div>
    <div class="flex items-center space-x-2">
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
            <?php echo count($dispatchedRequests); ?> Dispatches
        </span>
    </div>
</div>

<!-- Dispatch Status Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <?php
    $statusCounts = [
        'dispatched' => 0,
        'in_transit' => 0,
        'delivered' => 0,
        'confirmed' => 0
    ];
    
    foreach ($dispatchedRequests as $request) {
        $status = $request['dispatch_status'] ?? 'dispatched';
        if (isset($statusCounts[$status])) {
            $statusCounts[$status]++;
        }
    }
    ?>
    
    <div class="card">
        <div class="card-body">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Dispatched</p>
                    <p class="text-2xl font-semibold text-gray-900"><?php echo $statusCounts['dispatched']; ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">In Transit</p>
                    <p class="text-2xl font-semibold text-gray-900"><?php echo $statusCounts['in_transit']; ?></p>
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
</div>

<!-- Dispatches List -->
<div class="card">
    <div class="card-body">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Dispatches</h3>
        
        <?php if (empty($dispatchedRequests)): ?>
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m13-8l-4 4m0 0l-4-4m4 4V3"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No dispatches</h3>
            <p class="mt-1 text-sm text-gray-500">No material dispatches found for your sites.</p>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Request Details</th>
                        <th>Site Information</th>
                        <th>Dispatch Info</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dispatchedRequests as $request): ?>
                    <tr>
                        <td>
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">Request #<?php echo $request['id']; ?></div>
                                    <div class="text-sm text-gray-500">Dispatch #<?php echo $request['dispatch_number'] ?? 'N/A'; ?></div>
                                    <div class="text-sm text-gray-500"><?php echo date('d M Y', strtotime($request['request_date'])); ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($request['site_code']); ?></div>
                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($request['location']); ?></div>
                        </td>
                        <td>
                            <div class="text-sm text-gray-900">
                                <div><strong>Courier:</strong> <?php echo htmlspecialchars($request['courier_name'] ?? 'N/A'); ?></div>
                                <div><strong>POD:</strong> <?php echo htmlspecialchars($request['tracking_number'] ?? 'N/A'); ?></div>
                                <div><strong>Dispatch Date:</strong> <?php echo $request['dispatch_date'] ? date('d M Y', strtotime($request['dispatch_date'])) : 'N/A'; ?></div>
                            </div>
                        </td>
                        <td>
                            <?php
                            $status = $request['dispatch_status'] ?? 'dispatched';
                            $statusColors = [
                                'dispatched' => 'bg-orange-100 text-orange-800',
                                'in_transit' => 'bg-blue-100 text-blue-800',
                                'delivered' => 'bg-green-100 text-green-800',
                                'confirmed' => 'bg-purple-100 text-purple-800'
                            ];
                            $colorClass = $statusColors[$status] ?? 'bg-gray-100 text-gray-800';
                            ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $colorClass; ?>">
                                <?php echo ucfirst($status); ?>
                            </span>
                        </td>
                        <td>
                            <div class="flex space-x-2">
                                <a href="view-dispatch.php?id=<?php echo $request['id']; ?>" class="btn btn-sm btn-secondary">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    View
                                </a>
                                <?php if ($status === 'dispatched' || $status === 'in_transit'): ?>
                                <a href="confirm-delivery.php?id=<?php echo $request['id']; ?>" class="btn btn-sm btn-primary">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Confirm Delivery
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../includes/vendor_layout.php';
?>