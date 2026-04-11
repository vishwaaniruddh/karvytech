<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/SuperadminRequest.php';

// Require superadmin authentication
Auth::requireRole('superadmin');

header('Content-Type: application/json');

$id = $_GET['id'] ?? 0;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Invalid request ID']);
    exit;
}

$requestModel = new SuperadminRequest();
$request = $requestModel->find($id);

if (!$request) {
    echo json_encode(['success' => false, 'message' => 'Request not found']);
    exit;
}

$priorityColors = [
    'urgent' => 'bg-red-100 text-red-800 border-red-200',
    'high' => 'bg-orange-100 text-orange-800 border-orange-200',
    'medium' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
    'low' => 'bg-gray-100 text-gray-800 border-gray-200'
];

$statusColors = [
    'pending' => 'bg-yellow-100 text-yellow-800',
    'approved' => 'bg-green-100 text-green-800',
    'rejected' => 'bg-red-100 text-red-800'
];

$requestData = json_decode($request['request_data'], true);

ob_start();
?>
<div class="space-y-4">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="text-xs text-gray-500 uppercase font-bold">Request Type</label>
            <p class="text-sm font-medium text-gray-900"><?php echo ucwords(str_replace('_', ' ', $request['request_type'])); ?></p>
        </div>
        <div>
            <label class="text-xs text-gray-500 uppercase font-bold">Priority</label>
            <div>
                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-semibold border <?php echo $priorityColors[$request['priority']]; ?>">
                    <?php echo ucfirst($request['priority']); ?>
                </span>
            </div>
        </div>
        <div>
            <label class="text-xs text-gray-500 uppercase font-bold">Status</label>
            <div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $statusColors[$request['status']]; ?>">
                    <?php echo ucfirst($request['status']); ?>
                </span>
            </div>
        </div>
        <div>
            <label class="text-xs text-gray-500 uppercase font-bold">Created Date</label>
            <p class="text-sm text-gray-900"><?php echo date('M d, Y h:i A', strtotime($request['created_at'])); ?></p>
        </div>
    </div>
    
    <div class="border-t border-gray-100 pt-4">
        <label class="text-xs text-gray-500 uppercase font-bold">Request Title</label>
        <p class="text-sm font-medium text-gray-900 mt-1"><?php echo htmlspecialchars($request['request_title']); ?></p>
    </div>
    
    <div>
        <label class="text-xs text-gray-500 uppercase font-bold">Description</label>
        <p class="text-sm text-gray-700 mt-1"><?php echo nl2br(htmlspecialchars($request['request_description'])); ?></p>
    </div>
    
    <div class="border-t border-gray-100 pt-4">
        <label class="text-xs text-gray-500 uppercase font-bold">Requested By</label>
        <div class="mt-1">
            <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($request['requested_by_name']); ?></p>
            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($request['requested_by_role']); ?> • <?php echo htmlspecialchars($request['requested_by_email']); ?></p>
        </div>
    </div>
    
    <?php if ($requestData): ?>
    <div class="border-t border-gray-100 pt-4">
        <label class="text-xs text-gray-500 uppercase font-bold">
            <?php echo $request['request_type'] === 'site_deletion' ? 'Site Details' : 'Request Data'; ?>
        </label>
        <div class="mt-2 bg-gray-50 rounded-lg p-3 border border-gray-200">
            <?php if ($request['request_type'] === 'site_deletion'): ?>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-xs text-gray-600">Site Code:</span>
                        <span class="text-xs font-semibold text-gray-900"><?php echo htmlspecialchars($requestData['site_code'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-xs text-gray-600">Location:</span>
                        <span class="text-xs font-semibold text-gray-900"><?php echo htmlspecialchars($requestData['location'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-xs text-gray-600">Customer:</span>
                        <span class="text-xs font-semibold text-gray-900"><?php echo htmlspecialchars($requestData['customer'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-xs text-gray-600">Site ID:</span>
                        <span class="text-xs font-medium text-gray-700">#<?php echo htmlspecialchars($requestData['site_id'] ?? 'N/A'); ?></span>
                    </div>
                </div>
            <?php else: ?>
                <pre class="text-xs text-gray-700 whitespace-pre-wrap"><?php echo json_encode($requestData, JSON_PRETTY_PRINT); ?></pre>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if ($request['status'] !== 'pending'): ?>
    <div class="border-t border-gray-100 pt-4">
        <label class="text-xs text-gray-500 uppercase font-bold">Review Details</label>
        <div class="mt-2 bg-<?php echo $request['status'] === 'approved' ? 'green' : 'red'; ?>-50 rounded-lg p-3 border border-<?php echo $request['status'] === 'approved' ? 'green' : 'red'; ?>-200">
            <p class="text-sm font-medium text-gray-900">Reviewed by: <?php echo htmlspecialchars($request['reviewed_by_name']); ?></p>
            <p class="text-xs text-gray-500">On: <?php echo date('M d, Y h:i A', strtotime($request['reviewed_at'])); ?></p>
            <?php if ($request['remarks']): ?>
                <div class="mt-2">
                    <p class="text-xs text-gray-500 uppercase font-bold">Remarks:</p>
                    <p class="text-sm text-gray-700 mt-1"><?php echo nl2br(htmlspecialchars($request['remarks'])); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php
$html = ob_get_clean();
echo json_encode(['success' => true, 'html' => $html]);
?>
