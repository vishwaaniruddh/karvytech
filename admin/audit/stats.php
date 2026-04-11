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

// If tables don't exist, show setup page
if (!$tablesExist) {
    $title = 'Audit System Setup Required';
    ob_start();
    ?>
    
    <div class="max-w-4xl mx-auto">
        <!-- Setup Required Message -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-yellow-100 mb-4">
                <svg class="h-8 w-8 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
            </div>
            
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Audit System Not Installed</h2>
            <p class="text-gray-600 mb-6 max-w-2xl mx-auto">
                The audit system tables have not been created yet. Click the button below to install the audit system and start tracking user activity.
            </p>
            
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 text-left max-w-2xl mx-auto">
                <h3 class="text-sm font-medium text-blue-900 mb-2">What will be installed?</h3>
                <ul class="text-sm text-blue-700 space-y-1">
                    <li>• <strong>user_audit_logs</strong> - Track all user activities and API calls</li>
                    <li>• <strong>user_audit_sessions</strong> - Monitor active user sessions</li>
                    <li>• <strong>user_audit_api_stats</strong> - API performance statistics</li>
                </ul>
            </div>
            
            <div class="flex justify-center gap-4">
                <a href="install.php" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"></path>
                    </svg>
                    Install Audit System
                </a>
                <a href="../reports/" class="inline-flex items-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Back to Reports
                </a>
            </div>
        </div>
    </div>
    
    <?php
    $content = ob_get_clean();
    include __DIR__ . '/../../includes/admin_layout.php';
    exit;
}

// Tables exist, continue with normal page
require_once __DIR__ . '/../../models/AuditLog.php';

$auditLog = new AuditLog();

// Get date range from filters or default to last 30 days
$dateFrom = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
$dateTo = $_GET['date_to'] ?? date('Y-m-d');

// Get comprehensive statistics
$stats = $auditLog->getStatistics($dateFrom, $dateTo);
$activeSessions = $auditLog->getActiveSessions();
$apiStats = $auditLog->getAPIStatistics();

// Calculate additional metrics
$dateRange = (strtotime($dateTo) - strtotime($dateFrom)) / 86400 + 1; // days
$avgLogsPerDay = $dateRange > 0 ? round($stats['total_logs'] / $dateRange, 2) : 0;

$title = 'Audit Statistics & Analytics';
ob_start();
?>

<!-- Header Section -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
        <div class="flex-1">
            <div class="flex items-center space-x-4 mb-2">
                <a href="../reports/" class="text-blue-600 hover:text-blue-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <h1 class="text-3xl font-bold text-gray-900">Audit Statistics & Analytics</h1>
            </div>
            <p class="text-lg text-gray-600">Comprehensive user access and activity analytics</p>
            <p class="text-sm text-gray-500 mt-1">Date Range: <?php echo date('M j, Y', strtotime($dateFrom)); ?> - <?php echo date('M j, Y', strtotime($dateTo)); ?></p>
        </div>
        <div class="mt-6 lg:mt-0 lg:ml-6">
            <div class="flex gap-3">
                <button onclick="window.location.href='index.php'" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                    </svg>
                    View Logs
                </button>
                <button onclick="window.location.href='export.php?date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>'" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                    Export
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Date Range Filter -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-4 items-end">
        <div class="flex-1 min-w-48">
            <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
            <input type="date" name="date_from" id="date_from" value="<?php echo $dateFrom; ?>" class="form-input w-full">
        </div>
        <div class="flex-1 min-w-48">
            <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
            <input type="date" name="date_to" id="date_to" value="<?php echo $dateTo; ?>" class="form-input w-full">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary">Apply</button>
            <a href="?" class="btn btn-secondary">Reset</a>
        </div>
    </form>
</div>

<!-- Key Metrics -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center justify-between mb-2">
            <div class="text-blue-100 text-sm font-medium">Total Logs</div>
            <svg class="w-8 h-8 text-blue-200" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
            </svg>
        </div>
        <div class="text-3xl font-bold mb-1"><?php echo number_format($stats['total_logs']); ?></div>
        <div class="text-blue-100 text-xs">Avg: <?php echo $avgLogsPerDay; ?> per day</div>
    </div>
    
    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center justify-between mb-2">
            <div class="text-green-100 text-sm font-medium">Unique Users</div>
            <svg class="w-8 h-8 text-green-200" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>
            </svg>
        </div>
        <div class="text-3xl font-bold mb-1"><?php echo number_format($stats['unique_users']); ?></div>
        <div class="text-green-100 text-xs"><?php echo count($activeSessions); ?> currently active</div>
    </div>
    
    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center justify-between mb-2">
            <div class="text-purple-100 text-sm font-medium">API Calls</div>
            <svg class="w-8 h-8 text-purple-200" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
        </div>
        <div class="text-3xl font-bold mb-1">
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
        </div>
        <div class="text-purple-100 text-xs"><?php echo count($apiStats); ?> unique endpoints</div>
    </div>
    
    <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center justify-between mb-2">
            <div class="text-yellow-100 text-sm font-medium">Date Range</div>
            <svg class="w-8 h-8 text-yellow-200" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
            </svg>
        </div>
        <div class="text-3xl font-bold mb-1"><?php echo round($dateRange); ?></div>
        <div class="text-yellow-100 text-xs">days analyzed</div>
    </div>
</div>

<!-- Action Type Distribution -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Activity by Action Type</h3>
        <div class="space-y-4">
            <?php 
            $actionColors = [
                'login' => ['bg' => 'bg-green-500', 'text' => 'text-green-600'],
                'logout' => ['bg' => 'bg-gray-500', 'text' => 'text-gray-600'],
                'page_access' => ['bg' => 'bg-blue-500', 'text' => 'text-blue-600'],
                'api_call' => ['bg' => 'bg-purple-500', 'text' => 'text-purple-600']
            ];
            
            foreach ($stats['by_action_type'] as $type): 
                $percentage = $stats['total_logs'] > 0 ? ($type['count'] / $stats['total_logs']) * 100 : 0;
                $color = $actionColors[$type['action_type']] ?? ['bg' => 'bg-gray-500', 'text' => 'text-gray-600'];
            ?>
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-gray-700 capitalize"><?php echo str_replace('_', ' ', $type['action_type']); ?></span>
                        <span class="text-sm font-semibold <?php echo $color['text']; ?>"><?php echo number_format($type['count']); ?> (<?php echo round($percentage, 1); ?>%)</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="<?php echo $color['bg']; ?> h-3 rounded-full transition-all duration-500" style="width: <?php echo $percentage; ?>%"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Status Code Distribution</h3>
        <div class="space-y-4">
            <?php 
            $statusColors = [
                '200' => ['bg' => 'bg-green-500', 'text' => 'text-green-600', 'label' => 'Success'],
                '400' => ['bg' => 'bg-yellow-500', 'text' => 'text-yellow-600', 'label' => 'Bad Request'],
                '401' => ['bg' => 'bg-orange-500', 'text' => 'text-orange-600', 'label' => 'Unauthorized'],
                '403' => ['bg' => 'bg-red-500', 'text' => 'text-red-600', 'label' => 'Forbidden'],
                '404' => ['bg' => 'bg-gray-500', 'text' => 'text-gray-600', 'label' => 'Not Found'],
                '500' => ['bg' => 'bg-red-700', 'text' => 'text-red-700', 'label' => 'Server Error']
            ];
            
            foreach ($stats['by_status_code'] as $status): 
                $percentage = $stats['total_logs'] > 0 ? ($status['count'] / $stats['total_logs']) * 100 : 0;
                $color = $statusColors[$status['status_code']] ?? ['bg' => 'bg-gray-500', 'text' => 'text-gray-600', 'label' => 'Other'];
            ?>
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-gray-700"><?php echo $status['status_code']; ?> - <?php echo $color['label']; ?></span>
                        <span class="text-sm font-semibold <?php echo $color['text']; ?>"><?php echo number_format($status['count']); ?> (<?php echo round($percentage, 1); ?>%)</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="<?php echo $color['bg']; ?> h-3 rounded-full transition-all duration-500" style="width: <?php echo $percentage; ?>%"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Top Endpoints -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Top 10 Most Accessed Endpoints</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Endpoint</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Access Count</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Percentage</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php 
                $rank = 1;
                foreach ($stats['top_endpoints'] as $endpoint): 
                    $percentage = $stats['total_logs'] > 0 ? ($endpoint['count'] / $stats['total_logs']) * 100 : 0;
                ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $rank++; ?></td>
                        <td class="px-6 py-4 text-sm text-gray-900 max-w-md truncate" title="<?php echo htmlspecialchars($endpoint['endpoint']); ?>">
                            <?php echo htmlspecialchars($endpoint['endpoint']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-gray-900">
                            <?php echo number_format($endpoint['count']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">
                            <?php echo round($percentage, 2); ?>%
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Top Users -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Top 10 Most Active Users</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Username</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Activity Count</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Percentage</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php 
                $rank = 1;
                foreach ($stats['top_users'] as $user): 
                    $percentage = $stats['total_logs'] > 0 ? ($user['count'] / $stats['total_logs']) * 100 : 0;
                ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $rank++; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            <?php echo htmlspecialchars($user['username']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-gray-900">
                            <?php echo number_format($user['count']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">
                            <?php echo round($percentage, 2); ?>%
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- API Statistics -->
<?php if (!empty($apiStats)): ?>
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">API Endpoint Statistics</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Endpoint</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Calls</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Success</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Errors</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Avg Time (s)</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Called</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($apiStats as $api): 
                    $successRate = $api['total_calls'] > 0 ? ($api['success_calls'] / $api['total_calls']) * 100 : 0;
                ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate" title="<?php echo htmlspecialchars($api['endpoint']); ?>">
                            <?php echo htmlspecialchars($api['endpoint']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                <?php echo $api['http_method']; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-gray-900">
                            <?php echo number_format($api['total_calls']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">
                                <?php echo number_format($api['success_calls']); ?> (<?php echo round($successRate, 1); ?>%)
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800">
                                <?php echo number_format($api['error_calls']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                            <?php echo number_format($api['avg_execution_time'], 4); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo $api['last_called'] ? date('M j, Y g:i A', strtotime($api['last_called'])) : 'N/A'; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Active Sessions -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Currently Active Sessions (<?php echo count($activeSessions); ?>)</h3>
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
                        $now = new DateTime();
                        $duration = $loginTime->diff($now);
                    ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($session['username']); ?>
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

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>
