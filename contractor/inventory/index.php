<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../models/Inventory.php';

// Require vendor authentication
Auth::requireVendor();

$vendorId = Auth::getVendorId();

// Handle pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';

try {
    $inventoryModel = new Inventory();
    
    // Get paginated received materials for this contractor
    $materialsData = $inventoryModel->getContractorReceivedMaterialsPaginated($vendorId, $page, $limit, $search, $status);
    $receivedMaterials = $materialsData['materials'];
    $totalPages = $materialsData['pages'];
    $totalRecords = $materialsData['total'];
    
    // Get summary statistics
    $totalDispatches = $inventoryModel->getContractorDispatchCount($vendorId);
    $totalItems = $inventoryModel->getContractorTotalItems($vendorId);
    $pendingConfirmations = $inventoryModel->getContractorPendingConfirmations($vendorId);
    
    // Get database connection for additional queries
    require_once __DIR__ . '/../../config/database.php';
    $db = Database::getInstance()->getConnection();
    
    // Get accepted count (confirmed dispatches)
    $acceptedSql = "SELECT COUNT(*) FROM inventory_dispatches WHERE vendor_id = ? AND dispatch_status = 'confirmed'";
    $acceptedStmt = $db->prepare($acceptedSql);
    $acceptedStmt->execute([$vendorId]);
    $acceptedCount = $acceptedStmt->fetchColumn();
    
    // Get distinct material summary
    $materialSql = "SELECT 
                        bi.item_name,
                        COUNT(DISTINCT id.id) as dispatch_count,
                        COUNT(idi.id) as total_quantity,
                        bi.unit
                    FROM inventory_dispatches id
                    JOIN inventory_dispatch_items idi ON id.id = idi.dispatch_id
                    JOIN boq_items bi ON idi.boq_item_id = bi.id
                    WHERE id.vendor_id = ?
                    GROUP BY bi.id, bi.item_name, bi.unit
                    ORDER BY bi.item_name";
    $materialStmt = $db->prepare($materialSql);
    $materialStmt->execute([$vendorId]);
    $materialSummary = $materialStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get all dispatches for the contractor
    $dispatchSql = "SELECT 
                        id.*,
                        s.site_id as site_code,
                        s.location as site_location
                    FROM inventory_dispatches id
                    LEFT JOIN sites s ON id.site_id = s.id
                    WHERE id.vendor_id = ?
                    ORDER BY id.dispatch_date DESC, id.id DESC";
    $dispatchStmt = $db->prepare($dispatchSql);
    $dispatchStmt->execute([$vendorId]);
    $allDispatches = $dispatchStmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    error_log("Error in contractor inventory: " . $e->getMessage());
    $receivedMaterials = [];
    $totalPages = 1;
    $totalRecords = 0;
    $totalDispatches = 0;
    $totalItems = 0;
    $pendingConfirmations = 0;
    $acceptedCount = 0;
    $materialSummary = [];
    $allDispatches = [];
}

$title = 'Material Received from Admin';
ob_start();
?>

<style>
input, textarea, select {
    border: 1px solid #d1d5db !important;
}
</style>

<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Material Received from Admin</h1>
            <p class="mt-1 text-sm text-gray-600">Track all materials received from Karvy Admin</p>
        </div>
        <div class="flex space-x-3">
            <a href="../dashboard.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                </svg>
                Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a2 2 0 002 2h8a2 2 0 002-2V3a2 2 0 012 2v6h-3a2 2 0 00-2 2v4H6a2 2 0 01-2-2V5zm8 8a2 2 0 012-2h3v4a2 2 0 01-2 2v-1a2 2 0 00-2-2h-1v-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Receipts</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo $totalDispatches; ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Accepted</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo $acceptedCount; ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Pending</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo $pendingConfirmations; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Material Summary -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Material Summary</h3>
            <p class="text-sm text-gray-600">Distinct materials received with counts</p>
        </div>
        <div class="p-6">
            <?php if (!empty($materialSummary)): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($materialSummary as $material): ?>
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <h4 class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($material['item_name']); ?></h4>
                                <p class="text-xs text-gray-500 mt-1">
                                    <?php echo $material['dispatch_count']; ?> dispatch<?php echo $material['dispatch_count'] > 1 ? 'es' : ''; ?>
                                </p>
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-bold text-blue-600"><?php echo number_format($material['total_quantity']); ?></div>
                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($material['unit']); ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m13-8V4a1 1 0 00-1-1H7a1 1 0 00-1 1v1m8 0V4a1 1 0 00-1-1H9a1 1 0 00-1 1v1"></path>
                    </svg>
                    <p class="mt-2 text-sm text-gray-600">No materials received yet</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- All Dispatches -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">All Material Receipts</h3>
            <p class="text-sm text-gray-600">Complete list of materials received from Karvy Admin</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dispatch Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Site</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dispatch Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($allDispatches)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m13-8V4a1 1 0 00-1-1H7a1 1 0 00-1 1v1m8 0V4a1 1 0 00-1-1H9a1 1 0 00-1 1v1"></path>
                            </svg>
                            <p class="text-lg font-medium">No material receipts found</p>
                            <p class="text-sm">Materials dispatched from admin will appear here</p>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php 
                        $serialNumber = 1;
                        foreach ($allDispatches as $dispatch): 
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo $serialNumber++; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($dispatch['dispatch_number']); ?></div>
                                <div class="text-xs text-gray-500">ID: <?php echo $dispatch['id']; ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($dispatch['site_code'] ?? 'N/A'); ?></div>
                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($dispatch['site_location'] ?? 'N/A'); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <span class="font-medium">View Details</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo $dispatch['dispatch_date'] ? date('d M Y', strtotime($dispatch['dispatch_date'])) : 'N/A'; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php 
                                $status = $dispatch['dispatch_status'] ?? 'dispatched';
                                $statusConfig = [
                                    'dispatched' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'label' => 'Dispatched'],
                                    'in_transit' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'label' => 'In Transit'],
                                    'delivered' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-800', 'label' => 'Delivered'],
                                    'confirmed' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'label' => 'Confirmed'],
                                    'partially_delivered' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'label' => 'Partial']
                                ];
                                $config = $statusConfig[$status] ?? $statusConfig['dispatched'];
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $config['bg']; ?> <?php echo $config['text']; ?>">
                                    <?php echo $config['label']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="../material-received-from-admin.php?id=<?php echo $dispatch['id']; ?>" 
                                       class="text-blue-600 hover:text-blue-900" title="View Details">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    </a>
                                   
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/vendor_layout.php';
?>