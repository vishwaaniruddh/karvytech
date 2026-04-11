<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

// Check if audit tables exist with correct structure
$db = Database::getInstance()->getConnection();
$tablesExist = false;
try {
    // Check if table exists
    $stmt = $db->query("SHOW TABLES LIKE 'user_audit_logs'");
    if ($stmt->rowCount() > 0) {
        // Check if the table has the correct columns
        $stmt = $db->query("SHOW COLUMNS FROM user_audit_logs LIKE 'action_type'");
        $tablesExist = $stmt->rowCount() > 0;
    }
} catch (Exception $e) {
    $tablesExist = false;
}

// If tables don't exist, redirect to install page
if (!$tablesExist) {
    header('Location: install.php');
    exit;
}

// Tables exist, continue with normal page
require_once __DIR__ . '/../../models/AuditLog.php';

$auditLog = new AuditLog();

// Get filter parameters
$page = (int)($_GET['page'] ?? 1);
$limit = (int)($_GET['limit'] ?? 10);
$filters = [
    'username' => $_GET['username'] ?? '',
    'action_type' => $_GET['action_type'] ?? '',
    'status_code' => $_GET['status_code'] ?? '',
    'endpoint' => $_GET['endpoint'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? ''
];

// Get paginated logs
$result = $auditLog->getAllLogsWithPagination($page, $limit, $filters);
$logs = $result['records'];

// Get statistics
$stats = $auditLog->getStatistics($filters['date_from'], $filters['date_to']);

// Get active sessions
$activeSessions = $auditLog->getActiveSessions();

$title = 'User Access Audit';
ob_start();
?>

<!-- Header Section -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
        <div class="flex-1">
            <h1 class="text-3xl font-bold text-gray-900">User Access Audit</h1>
            <p class="mt-2 text-lg text-gray-600">Monitor user activity, API calls, and system access</p>
        </div>
        <div class="mt-6 lg:mt-0 lg:ml-6">
            <div class="flex gap-3">
                <button onclick="exportLogs()" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                    Export
                </button>
                <button onclick="location.reload()" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
                    </svg>
                    Refresh
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Logs</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['total_logs']); ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Unique Users</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['unique_users']); ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
                <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Active Sessions</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo count($activeSessions); ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">API Calls</p>
                <p class="text-2xl font-bold text-gray-900">
                    <?php 
                    $apiCalls = 0;
                    foreach ($stats['by_action_type'] as $type) {
                        if ($type['action_type'] === 'api_call') {
                            $apiCalls = $type['count'];
                            break;
                        }
                    }
                    echo number_format($apiCalls);
                    ?>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Filters Section -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-4 items-end">
        <div class="flex-1 min-w-48">
            <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
            <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($filters['username']); ?>" class="form-input w-full" placeholder="Search username">
        </div>
        <div class="flex-1 min-w-48">
            <label for="action_type" class="block text-sm font-medium text-gray-700 mb-1">Action Type</label>
            <select name="action_type" id="action_type" class="form-select w-full">
                <option value="">All Actions</option>
                <option value="login" <?php echo $filters['action_type'] === 'login' ? 'selected' : ''; ?>>Login</option>
                <option value="logout" <?php echo $filters['action_type'] === 'logout' ? 'selected' : ''; ?>>Logout</option>
                <option value="page_access" <?php echo $filters['action_type'] === 'page_access' ? 'selected' : ''; ?>>Page Access</option>
                <option value="api_call" <?php echo $filters['action_type'] === 'api_call' ? 'selected' : ''; ?>>API Call</option>
                <option value="create_boq" <?php echo $filters['action_type'] === 'create_boq' ? 'selected' : ''; ?>>Create BOQ</option>
                <option value="update_boq" <?php echo $filters['action_type'] === 'update_boq' ? 'selected' : ''; ?>>Update BOQ</option>
                <option value="delete_boq" <?php echo $filters['action_type'] === 'delete_boq' ? 'selected' : ''; ?>>Delete BOQ</option>
            </select>
        </div>
        <div class="flex-1 min-w-48">
            <label for="status_code" class="block text-sm font-medium text-gray-700 mb-1">Status Code</label>
            <select name="status_code" id="status_code" class="form-select w-full">
                <option value="">All Status</option>
                <option value="200" <?php echo $filters['status_code'] === '200' ? 'selected' : ''; ?>>200 - Success</option>
                <option value="400" <?php echo $filters['status_code'] === '400' ? 'selected' : ''; ?>>400 - Bad Request</option>
                <option value="401" <?php echo $filters['status_code'] === '401' ? 'selected' : ''; ?>>401 - Unauthorized</option>
                <option value="403" <?php echo $filters['status_code'] === '403' ? 'selected' : ''; ?>>403 - Forbidden</option>
                <option value="404" <?php echo $filters['status_code'] === '404' ? 'selected' : ''; ?>>404 - Not Found</option>
                <option value="500" <?php echo $filters['status_code'] === '500' ? 'selected' : ''; ?>>500 - Server Error</option>
            </select>
        </div>
        <div class="flex-1 min-w-48">
            <label for="endpoint" class="block text-sm font-medium text-gray-700 mb-1">Endpoint</label>
            <input type="text" name="endpoint" id="endpoint" value="<?php echo htmlspecialchars($filters['endpoint']); ?>" class="form-input w-full" placeholder="Search endpoint">
        </div>
        <div class="flex-1 min-w-48">
            <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
            <input type="date" name="date_from" id="date_from" value="<?php echo $filters['date_from']; ?>" class="form-input w-full">
        </div>
        <div class="flex-1 min-w-48">
            <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
            <input type="date" name="date_to" id="date_to" value="<?php echo $filters['date_to']; ?>" class="form-input w-full">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="?" class="btn btn-secondary">Clear</a>
        </div>
    </form>
</div>

<!-- Audit Logs Table -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-8">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">Audit Logs</h3>
            <p class="text-sm text-gray-500 mt-1">
                Showing <?php echo number_format((($result['page'] - 1) * $result['limit']) + 1); ?> to 
                <?php echo number_format(min($result['page'] * $result['limit'], $result['total'])); ?> of 
                <?php echo number_format($result['total']); ?> logs
            </p>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Endpoint</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP Address</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                            No audit logs found
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $log['id']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars(ucfirst($log['username'] ?? 'N/A')); ?></div>
                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($log['user_role'] ?? ''); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                                    <?php 
                                    switch($log['action_type']) {
                                        case 'login': echo 'bg-green-100 text-green-800'; break;
                                        case 'logout': echo 'bg-gray-100 text-gray-800'; break;
                                        case 'api_call': echo 'bg-purple-100 text-purple-800'; break;
                                        case 'page_access': echo 'bg-blue-100 text-blue-800'; break;
                                        case 'create_boq': echo 'bg-indigo-100 text-indigo-800'; break;
                                        case 'update_boq': echo 'bg-orange-100 text-orange-800'; break;
                                        case 'delete_boq': echo 'bg-red-100 text-red-800'; break;
                                        default: echo 'bg-gray-100 text-gray-800';
                                    }
                                    ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $log['action_type'])); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate" title="<?php echo htmlspecialchars($log['endpoint']); ?>">
                                <?php echo htmlspecialchars($log['endpoint']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $log['http_method']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
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
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($log['ip_address'] ?? 'N/A'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('M j, Y g:i A', strtotime($log['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <button onclick="viewDetails(<?php echo $log['id']; ?>)" class="text-blue-600 hover:text-blue-800">
                                    View
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($result['pages'] > 1): ?>
    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
        <div class="flex-1 flex justify-between sm:hidden">
            <a href="?page=<?php echo max(1, $result['page'] - 1); ?>&<?php echo http_build_query($filters); ?>&limit=<?php echo $limit; ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Previous</a>
            <a href="?page=<?php echo min($result['pages'], $result['page'] + 1); ?>&<?php echo http_build_query($filters); ?>&limit=<?php echo $limit; ?>" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Next</a>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700">
                    Showing <span class="font-medium"><?php echo (($result['page'] - 1) * $result['limit']) + 1; ?></span> to 
                    <span class="font-medium"><?php echo min($result['page'] * $result['limit'], $result['total']); ?></span> of 
                    <span class="font-medium"><?php echo $result['total']; ?></span> results
                </p>
            </div>
            <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                    <?php if ($result['page'] > 1): ?>
                        <a href="?page=<?php echo $result['page'] - 1; ?>&<?php echo http_build_query($filters); ?>&limit=<?php echo $limit; ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <span class="sr-only">Previous</span>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    <?php endif; ?>

                    <?php
                    $startPage = max(1, $result['page'] - 2);
                    $endPage = min($result['pages'], $result['page'] + 2);
                    
                    if ($startPage > 1): ?>
                        <a href="?page=1&<?php echo http_build_query($filters); ?>&limit=<?php echo $limit; ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">1</a>
                        <?php if ($startPage > 2): ?>
                            <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&<?php echo http_build_query($filters); ?>&limit=<?php echo $limit; ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium <?php echo $i === $result['page'] ? 'bg-blue-50 text-blue-600 border-blue-500 z-10' : 'text-gray-700 hover:bg-gray-50'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($endPage < $result['pages']): ?>
                        <?php if ($endPage < $result['pages'] - 1): ?>
                            <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>
                        <?php endif; ?>
                        <a href="?page=<?php echo $result['pages']; ?>&<?php echo http_build_query($filters); ?>&limit=<?php echo $limit; ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"><?php echo $result['pages']; ?></a>
                    <?php endif; ?>

                    <?php if ($result['page'] < $result['pages']): ?>
                        <a href="?page=<?php echo $result['page'] + 1; ?>&<?php echo http_build_query($filters); ?>&limit=<?php echo $limit; ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <span class="sr-only">Next</span>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Active Sessions -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Active Sessions</h3>
        <p class="text-sm text-gray-500 mt-1"><?php echo count($activeSessions); ?> active sessions</p>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Login Time</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Activity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP Address</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($activeSessions)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            No active sessions
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($activeSessions as $session): 
                        $loginTime = new DateTime($session['login_time']);
                        $lastActivity = new DateTime($session['last_activity']);
                        $now = new DateTime();
                        $duration = $loginTime->diff($now);
                    ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars(ucfirst($session['username'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('M j, Y g:i A', strtotime($session['login_time'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('M j, Y g:i A', strtotime($session['last_activity'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($session['ip_address']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php 
                                if ($duration->days > 0) echo $duration->days . 'd ';
                                echo $duration->h . 'h ' . $duration->i . 'm';
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function viewDetails(logId) {
    window.location.href = 'view.php?id=' + logId;
}

function exportLogs() {
    const params = new URLSearchParams(window.location.search);
    window.location.href = 'export.php?' + params.toString();
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>
