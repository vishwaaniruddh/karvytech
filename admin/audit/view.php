<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/AuditLog.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$logId = $_GET['id'] ?? null;
if (!$logId) {
    header('Location: index.php');
    exit;
}

$auditLog = new AuditLog();
$logs = $auditLog->getAllLogs(['limit' => 1]);
$log = null;

// Find the specific log
$allLogs = $auditLog->getAllLogs([]);
foreach ($allLogs as $l) {
    if ($l['id'] == $logId) {
        $log = $l;
        break;
    }
}

if (!$log) {
    header('Location: index.php?error=Log not found');
    exit;
}

$title = 'Audit Log Details - #' . $log['id'];
ob_start();
?>

<!-- Header Section -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
    <div class="flex items-center space-x-4 mb-2">
        <a href="index.php" class="text-blue-600 hover:text-blue-800">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Audit Log Details</h1>
    </div>
    <p class="text-lg text-gray-600">Log ID: #<?php echo $log['id']; ?></p>
</div>

<!-- Log Details -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- Basic Information -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
        <div class="space-y-3">
            <div class="flex justify-between">
                <span class="text-gray-500">User ID:</span>
                <span class="font-medium"><?php echo $log['user_id'] ?? 'N/A'; ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Username:</span>
                <span class="font-medium"><?php echo htmlspecialchars($log['username'] ?? 'N/A'); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">User Role:</span>
                <span class="font-medium"><?php echo htmlspecialchars($log['user_role'] ?? 'N/A'); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Action Type:</span>
                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                    <?php 
                    switch($log['action_type']) {
                        case 'login': echo 'bg-green-100 text-green-800'; break;
                        case 'logout': echo 'bg-gray-100 text-gray-800'; break;
                        case 'api_call': echo 'bg-purple-100 text-purple-800'; break;
                        case 'page_access': echo 'bg-blue-100 text-blue-800'; break;
                        default: echo 'bg-gray-100 text-gray-800';
                    }
                    ?>">
                    <?php echo ucfirst(str_replace('_', ' ', $log['action_type'])); ?>
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Timestamp:</span>
                <span class="font-medium"><?php echo date('M j, Y g:i:s A', strtotime($log['created_at'])); ?></span>
            </div>
        </div>
    </div>
    
    <!-- Request Information -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Request Information</h3>
        <div class="space-y-3">
            <div class="flex justify-between">
                <span class="text-gray-500">HTTP Method:</span>
                <span class="font-medium"><?php echo $log['http_method'] ?? 'N/A'; ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Status Code:</span>
                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                    <?php 
                    $code = $log['status_code'];
                    if ($code >= 200 && $code < 300) echo 'bg-green-100 text-green-800';
                    elseif ($code >= 300 && $code < 400) echo 'bg-blue-100 text-blue-800';
                    elseif ($code >= 400 && $code < 500) echo 'bg-yellow-100 text-yellow-800';
                    else echo 'bg-red-100 text-red-800';
                    ?>">
                    <?php echo $log['status_code']; ?>
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">IP Address:</span>
                <span class="font-medium"><?php echo htmlspecialchars($log['ip_address'] ?? 'N/A'); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Execution Time:</span>
                <span class="font-medium"><?php echo $log['execution_time'] ? $log['execution_time'] . 's' : 'N/A'; ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Session ID:</span>
                <span class="font-medium text-xs"><?php echo htmlspecialchars(substr($log['session_id'] ?? 'N/A', 0, 20)) . '...'; ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Endpoint -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Endpoint</h3>
    <div class="bg-gray-50 rounded p-3 font-mono text-sm break-all">
        <?php echo htmlspecialchars($log['endpoint']); ?>
    </div>
</div>

<!-- User Agent -->
<?php if ($log['user_agent']): ?>
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">User Agent</h3>
    <div class="bg-gray-50 rounded p-3 text-sm break-all">
        <?php echo htmlspecialchars($log['user_agent']); ?>
    </div>
</div>
<?php endif; ?>

<!-- Request Data -->
<?php if ($log['request_data']): ?>
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Request Data</h3>
    <pre class="bg-gray-50 rounded p-4 text-sm overflow-x-auto"><code><?php echo htmlspecialchars(json_encode(json_decode($log['request_data']), JSON_PRETTY_PRINT)); ?></code></pre>
</div>
<?php endif; ?>

<!-- Response Data -->
<?php if ($log['response_data']): ?>
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Response Data</h3>
    <pre class="bg-gray-50 rounded p-4 text-sm overflow-x-auto"><code><?php echo htmlspecialchars(json_encode(json_decode($log['response_data']), JSON_PRETTY_PRINT)); ?></code></pre>
</div>
<?php endif; ?>

<!-- Error Message -->
<?php if ($log['error_message']): ?>
<div class="bg-white rounded-lg shadow-sm border border-red-200 p-6 mb-8">
    <h3 class="text-lg font-semibold text-red-900 mb-4">Error Message</h3>
    <div class="bg-red-50 rounded p-3 text-sm text-red-800">
        <?php echo htmlspecialchars($log['error_message']); ?>
    </div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>
