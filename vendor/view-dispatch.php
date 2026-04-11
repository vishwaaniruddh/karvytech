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
    header('Location: material-dispatches.php');
    exit;
}

$materialRequestModel = new MaterialRequest();
$inventoryModel = new Inventory();
$boqModel = new BoqItem();

// Get material request details
$materialRequest = $materialRequestModel->findWithDetails($requestId);

if (!$materialRequest || $materialRequest['vendor_id'] != $vendorId) {
    header('Location: material-dispatches.php');
    exit;
}

// Get dispatch details
$dispatchDetails = $inventoryModel->getDispatchByRequestId($requestId);

// Get dispatch items with individual records
$dispatchItems = $inventoryModel->getDispatchItems($dispatchDetails['id'] ?? 0);

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

$title = 'Dispatch Details - Request #' . $materialRequest['id'];
ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Dispatch Details</h1>
        <p class="mt-2 text-sm text-gray-700">Material Request #<?php echo $materialRequest['id']; ?> - Dispatch #<?php echo $dispatchDetails['dispatch_number'] ?? 'N/A'; ?></p>
    </div>
    <div class="flex space-x-2">
        <a href="material-dispatches.php" class="btn btn-secondary">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
            </svg>
            Back to Dispatches
        </a>
        <?php if (($dispatchDetails['dispatch_status'] ?? 'dispatched') === 'dispatched' || ($dispatchDetails['dispatch_status'] ?? 'dispatched') === 'in_transit'): ?>
        <a href="confirm-delivery.php?id=<?php echo $materialRequest['id']; ?>" class="btn btn-primary">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
            </svg>
            Confirm Delivery
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Status Banner -->
<div class="mb-6">
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
    <div class="rounded-md <?php echo $config['bg']; ?> <?php echo $config['border']; ?> border p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 <?php echo $config['icon']; ?>" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium <?php echo $config['text']; ?>">
                    Dispatch Status: <?php echo ucfirst($status); ?>
                </h3>
                <div class="mt-2 text-sm <?php echo $config['text']; ?>">
                    <p>
                        <?php if ($status === 'dispatched'): ?>
                            Material has been dispatched from admin. Please confirm delivery once received.
                        <?php elseif ($status === 'in_transit'): ?>
                            Material is in transit. Please confirm delivery once received.
                        <?php elseif ($status === 'delivered'): ?>
                            Material has been delivered. Waiting for admin confirmation.
                        <?php elseif ($status === 'confirmed'): ?>
                            Delivery has been confirmed by admin. Process complete.
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Information Cards -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Site Information -->
    <div class="card">
        <div class="card-body">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                </svg>
                Site Information
            </h3>
            <div class="space-y-3">
                <div>
                    <label class="text-sm font-medium text-gray-500">Site Code</label>
                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($materialRequest['site_code']); ?></div>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-500">Location</label>
                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($materialRequest['location']); ?></div>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Address</label>
                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($materialRequest['address']); ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Dispatch Information -->
    <div class="card">
        <div class="card-body">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                Dispatch Information
            </h3>
            <div class="space-y-3">
                <div>
                    <label class="text-sm font-medium text-gray-500">Dispatch Number</label>
                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($dispatchDetails['dispatch_number'] ?? 'N/A'); ?></div>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Dispatch Date</label>
                    <div class="text-sm text-gray-900"><?php echo $dispatchDetails['dispatch_date'] ? date('d M Y', strtotime($dispatchDetails['dispatch_date'])) : 'N/A'; ?></div>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Courier Name</label>
                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($dispatchDetails['courier_name'] ?? 'N/A'); ?></div>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">POD / Tracking Number</label>
                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($dispatchDetails['tracking_number'] ?? 'N/A'); ?></div>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Expected Delivery</label>
                    <div class="text-sm text-gray-900"><?php echo $dispatchDetails['expected_delivery_date'] ? date('d M Y', strtotime($dispatchDetails['expected_delivery_date'])) : 'N/A'; ?></div>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Contact Person</label>
                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($dispatchDetails['contact_person_name'] ?? 'N/A'); ?></div>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Contact Phone</label>
                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($dispatchDetails['contact_person_phone'] ?? 'N/A'); ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Material Items -->
<div class="card mb-6">
    <div class="card-body">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Dispatched Materials</h3>
        
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Material Details</th>
                        <th>Requested Qty</th>
                        <th>Dispatched Qty</th>
                        <th>Individual Records</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($dispatchItems)): ?>
                        <?php foreach ($dispatchItems as $item): ?>
                        <tr>
                            <td>
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                            <i class="<?php echo $item['icon_class'] ?: 'fas fa-cube'; ?> text-blue-600"></i>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($item['item_name'] ?? 'Unknown Item'); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($item['item_code'] ?? 'N/A'); ?></div>
                                        <div class="text-sm text-gray-500">Unit: <?php echo htmlspecialchars($item['unit'] ?? 'N/A'); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="text-sm font-medium text-gray-900"><?php echo number_format($item['quantity_dispatched']); ?></div>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($item['unit'] ?? 'Units'); ?></div>
                            </td>
                            <td>
                                <div class="text-sm font-medium text-green-600"><?php echo number_format($item['quantity_dispatched']); ?></div>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($item['unit'] ?? 'Units'); ?></div>
                            </td>
                            <td>
                                <?php
                                $individualRecords = json_decode($item['individual_records'] ?? '[]', true);
                                if (!empty($individualRecords)):
                                ?>
                                <div class="space-y-1">
                                    <div class="text-xs font-medium text-gray-700 mb-2">Individual Items:</div>
                                    <?php foreach ($individualRecords as $record): ?>
                                    <div class="bg-gray-50 px-2 py-1 rounded text-xs">
                                        <span class="font-medium">SN:</span> <?php echo htmlspecialchars($record['serial_number'] ?? 'N/A'); ?>
                                        <?php if (!empty($record['batch_number'])): ?>
                                        <br><span class="font-medium">Batch:</span> <?php echo htmlspecialchars($record['batch_number']); ?>
                                        <?php endif; ?>
                                        <br><span class="font-medium">Qty:</span> <?php echo $record['quantity'] ?? 1; ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php elseif (!empty($item['batch_number'])): ?>
                                <div class="text-sm text-gray-900">
                                    <span class="font-medium">Batch:</span> <?php echo htmlspecialchars($item['batch_number']); ?>
                                </div>
                                <?php else: ?>
                                <div class="text-sm text-gray-500">Cumulative</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($item['remarks'] ?? 'No notes'); ?></div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php foreach ($requestedItems as $item): ?>
                        <?php $boqItem = $boqItems[$item['boq_item_id']] ?? null; ?>
                        <tr>
                            <td>
                                <?php if ($boqItem): ?>
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                                <i class="<?php echo $boqItem['icon_class'] ?: 'fas fa-cube'; ?> text-blue-600"></i>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($boqItem['item_name']); ?></div>
                                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($boqItem['item_code']); ?></div>
                                            <div class="text-sm text-gray-500">Unit: <?php echo htmlspecialchars($boqItem['unit']); ?></div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="text-sm text-gray-500">Item not found (ID: <?php echo $item['boq_item_id']; ?>)</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="text-sm font-medium text-gray-900"><?php echo number_format($item['quantity']); ?></div>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($item['unit']); ?></div>
                            </td>
                            <td>
                                <div class="text-sm font-medium text-green-600"><?php echo number_format($item['quantity']); ?></div>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($item['unit']); ?></div>
                            </td>
                            <td>
                                <div class="text-sm text-gray-500">No dispatch details</div>
                            </td>
                            <td>
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($item['notes'] ?? 'No notes'); ?></div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Dispatch Remarks -->
<?php if (!empty($dispatchDetails['delivery_remarks'])): ?>
<div class="card">
    <div class="card-body">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Dispatch Remarks</h3>
        <div class="bg-gray-50 rounded-lg p-4">
            <p class="text-sm text-gray-700"><?php echo nl2br(htmlspecialchars($dispatchDetails['delivery_remarks'])); ?></p>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../includes/vendor_layout.php';
?>