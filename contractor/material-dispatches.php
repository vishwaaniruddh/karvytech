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

<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Material Dispatches</h1>
        <p class="mt-2 text-gray-600">Track and confirm material deliveries for your sites</p>
    </div>
    <div class="flex items-center space-x-3">
        <div class="flex items-center space-x-2">
            <div class="h-3 w-3 bg-green-400 rounded-full animate-pulse"></div>
            <span class="text-sm text-gray-600 font-medium">Live Updates</span>
        </div>
        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            <?php echo count($dispatchedRequests); ?> Total Dispatches
        </span>
    </div>
</div>

<!-- Dispatch Status Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
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
    
    $statusCards = [
        'dispatched' => [
            'title' => 'Dispatched',
            'icon' => 'M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z',
            'bg' => 'bg-gradient-to-br from-orange-400 to-orange-500',
            'iconBg' => 'bg-orange-100',
            'iconColor' => 'text-orange-600',
            'textColor' => 'text-orange-600'
        ],
        'in_transit' => [
            'title' => 'In Transit',
            'icon' => 'M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z',
            'bg' => 'bg-gradient-to-br from-blue-400 to-blue-500',
            'iconBg' => 'bg-blue-100',
            'iconColor' => 'text-blue-600',
            'textColor' => 'text-blue-600'
        ],
        'delivered' => [
            'title' => 'Delivered',
            'icon' => 'M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z',
            'bg' => 'bg-gradient-to-br from-green-400 to-green-500',
            'iconBg' => 'bg-green-100',
            'iconColor' => 'text-green-600',
            'textColor' => 'text-green-600'
        ],
        'confirmed' => [
            'title' => 'Confirmed',
            'icon' => 'M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z',
            'bg' => 'bg-gradient-to-br from-purple-400 to-purple-500',
            'iconBg' => 'bg-purple-100',
            'iconColor' => 'text-purple-600',
            'textColor' => 'text-purple-600'
        ]
    ];
    ?>
    
    <?php foreach ($statusCards as $status => $config): ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 <?php echo $config['iconBg']; ?> rounded-xl flex items-center justify-center shadow-sm">
                            <svg class="w-6 h-6 <?php echo $config['iconColor']; ?>" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="<?php echo $config['icon']; ?>" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide"><?php echo $config['title']; ?></p>
                        <p class="text-3xl font-bold <?php echo $config['textColor']; ?>"><?php echo $statusCounts[$status]; ?></p>
                    </div>
                </div>
                <div class="hidden sm:block">
                    <div class="w-16 h-2 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-full <?php echo $config['bg']; ?> rounded-full transition-all duration-500" 
                             style="width: <?php echo count($dispatchedRequests) > 0 ? ($statusCounts[$status] / count($dispatchedRequests)) * 100 : 0; ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Dispatches List -->
<?php if (empty($dispatchedRequests)): ?>
<div class="card">
    <div class="card-body">
        <div class="text-center py-16">
            <div class="mx-auto h-24 w-24 bg-gray-100 rounded-full flex items-center justify-center mb-6">
                <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m13-8l-4 4m0 0l-4-4m4 4V3"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No Material Dispatches</h3>
            <p class="text-gray-500 max-w-md mx-auto leading-relaxed">
                No material dispatches found for your sites. Dispatches will appear here once the admin team sends materials to your locations.
            </p>
            <div class="mt-8">
                <div class="inline-flex items-center px-4 py-2 bg-blue-50 border border-blue-200 rounded-lg text-blue-700">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-sm font-medium">Check back later for dispatch updates</span>
                </div>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="space-y-6">
    <?php foreach ($dispatchedRequests as $request): ?>
    <?php
    $status = $request['dispatch_status'] ?? 'dispatched';
    $statusColors = [
        'dispatched' => ['bg' => 'bg-orange-50', 'border' => 'border-orange-200', 'text' => 'text-orange-800', 'badge' => 'bg-orange-100 text-orange-800'],
        'in_transit' => ['bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'text' => 'text-blue-800', 'badge' => 'bg-blue-100 text-blue-800'],
        'delivered' => ['bg' => 'bg-green-50', 'border' => 'border-green-200', 'text' => 'text-green-800', 'badge' => 'bg-green-100 text-green-800'],
        'confirmed' => ['bg' => 'bg-purple-50', 'border' => 'border-purple-200', 'text' => 'text-purple-800', 'badge' => 'bg-purple-100 text-purple-800']
    ];
    $statusLabels = [
        'dispatched' => 'Material Received',
        'in_transit' => 'In Transit',
        'delivered' => 'Delivered',
        'confirmed' => 'Confirmed'
    ];
    $statusConfig = $statusColors[$status] ?? $statusColors['dispatched'];
    $statusLabel = $statusLabels[$status] ?? ucfirst($status);
    ?>
    
    <!-- Dispatch Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200">
        <!-- Card Header -->
        <div class="<?php echo $statusConfig['bg']; ?> <?php echo $statusConfig['border']; ?> border-b px-6 py-4 rounded-t-xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 rounded-xl bg-white shadow-sm flex items-center justify-center">
                            <svg class="w-6 h-6 <?php echo $statusConfig['text']; ?>" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Material Request #<?php echo $request['id']; ?></h3>
                        <p class="text-sm <?php echo $statusConfig['text']; ?> font-medium">Dispatch #<?php echo $request['dispatch_number'] ?? 'N/A'; ?></p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?php echo $statusConfig['badge']; ?>">
                        <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                            <?php if ($status === 'dispatched'): ?>
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                            <?php elseif ($status === 'in_transit'): ?>
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            <?php elseif ($status === 'delivered'): ?>
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            <?php else: ?>
                                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            <?php endif; ?>
                        </svg>
                        <?php echo $statusLabel; ?>
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Card Body -->
        <div class="px-6 py-5">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Site Information -->
                <div class="space-y-3">
                    <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Site Information</h4>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($request['site_code']); ?></p>
                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($request['location']); ?></p>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <p class="text-sm text-gray-900">Requested: <?php echo date('d M Y', strtotime($request['request_date'])); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Dispatch Information -->
                <div class="space-y-3">
                    <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Dispatch Details</h4>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"></path>
                                <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1V8a1 1 0 00-1-1h-3z"></path>
                            </svg>
                            <div>
                                <p class="text-sm text-gray-900"><?php echo htmlspecialchars($request['courier_name'] ?? 'N/A'); ?></p>
                                <p class="text-xs text-gray-500">Courier Service</p>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <p class="text-sm text-gray-900"><?php echo htmlspecialchars($request['tracking_number'] ?? 'N/A'); ?></p>
                                <p class="text-xs text-gray-500">POD Number</p>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <p class="text-sm text-gray-900"><?php echo $request['dispatch_date'] ? date('d M Y', strtotime($request['dispatch_date'])) : 'N/A'; ?></p>
                                <p class="text-xs text-gray-500">Dispatch Date</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="space-y-3">
                    <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Actions</h4>
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-2">
                            <a href="view-dispatch.php?id=<?php echo $request['id']; ?>" 
                               class="inline-flex items-center justify-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                </svg>
                                Details
                            </a>
                            
                            <?php if ($request['installation_id']): ?>
                            <a href="../shared/view-installation.php?id=<?php echo $request['installation_id']; ?>" 
                               class="inline-flex items-center justify-center px-3 py-2 border border-blue-300 text-sm font-medium rounded-lg text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                               title="View Installation Details">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                </svg>
                            </a>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($status === 'dispatched' || $status === 'in_transit'): ?>
                        <a href="confirm-delivery.php?id=<?php echo $request['id']; ?>" 
                           class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 shadow-sm hover:shadow-md transition-all duration-200">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Confirm Receipt
                        </a>
                        <?php elseif ($status === 'confirmed'): ?>
                        <div class="w-full inline-flex items-center justify-center px-4 py-2 border border-green-200 text-sm font-medium rounded-lg text-green-700 bg-green-50">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Receipt Confirmed
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($request['request_notes']): ?>
                        <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="flex items-start">
                                <svg class="w-4 h-4 text-blue-400 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                                <div>
                                    <p class="text-xs font-medium text-blue-800">Request Notes</p>
                                    <p class="text-xs text-blue-700 mt-1"><?php echo htmlspecialchars($request['request_notes']); ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../includes/vendor_layout.php';
?>