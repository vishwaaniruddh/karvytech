<?php
require_once '../config/auth.php';
// constants.php is already included by auth.php
require_once '../config/database.php';
require_once '../models/Site.php';
require_once '../models/Vendor.php';
require_once '../models/SiteSurvey.php';
require_once '../models/Inventory.php';
require_once '../models/Installation.php';
require_once '../models/MaterialRequest.php';

// Require internal administrative authentication
if (!Auth::isInternal()) {
    header('Location: ' . url('/shared/403.php?role=internal'));
    exit();
}

$title = 'Admin Dashboard';
$currentUser = Auth::getCurrentUser();

// Initialize models
$siteModel = new Site();
$vendorModel = new Vendor();
$surveyModel = new SiteSurvey();
$inventoryModel = new Inventory();
$installationModel = new Installation();

// Initialize stats with full defaults to handle unauthorized access gracefully
$totalSites = 0; 
$siteStats = ['delegated' => 0, 'surveyed' => 0, 'installed' => 0, 'pending' => 0];
$vendorStats = ['total' => 0, 'active' => 0, 'inactive' => 0];
$surveyStats = ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];
$installationStats = ['total' => 0, 'assigned' => 0, 'in_progress' => 0, 'completed' => 0, 'overdue' => 0];
$inventoryStats = ['total_items' => 0, 'total_quantity' => 0, 'low_stock_items' => 0];
$requestStats = ['total' => 0, 'pending' => 0, 'approved' => 0, 'dispatched' => 0, 'delivered' => 0];
$recentActivities = [];

// Get comprehensive dashboard statistics
try {
    $db = Database::getInstance()->getConnection();
    
    // Sites Statistics
    if (Auth::hasModuleAccess('sites')) {
        $stmt = $db->query("SELECT COUNT(*) as total FROM sites");
        $totalSites = $stmt->fetch()['total'];
        
        $stmt = $db->query("SELECT 
            SUM(CASE WHEN is_delegate = 1 THEN 1 ELSE 0 END) as delegated,
            SUM(CASE WHEN survey_status = 1 THEN 1 ELSE 0 END) as surveyed,
            SUM(CASE WHEN installation_status = 1 THEN 1 ELSE 0 END) as installed,
            SUM(CASE WHEN is_delegate = 0 THEN 1 ELSE 0 END) as pending
            FROM sites");
        $siteStats = $stmt->fetch();
    }
    
    // Vendors Statistics
    if (Auth::hasModuleAccess('users')) { // Vendors are managed under users/vendors
        $stmt = $db->query("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive
            FROM vendors");
        $vendorStats = $stmt->fetch();
    }
    
    // Survey Statistics
    if (Auth::hasModuleAccess('surveys')) {
        $stmt = $db->query("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN survey_status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN survey_status = 'approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN survey_status = 'rejected' THEN 1 ELSE 0 END) as rejected
            FROM site_surveys");
        $surveyStats = $stmt->fetch();
    }
    
    // Installation Statistics
    if (Auth::hasModuleAccess('installations')) {
        $installationStats = $installationModel->getInstallationStats();
    }
    
    // Inventory Statistics
    if (Auth::hasModuleAccess('inventory')) {
        $stmt = $db->query("SELECT COUNT(DISTINCT item_name) as total_items,
         COALESCE(SUM(total_stock), 0) as total_quantity, 
         SUM(CASE WHEN total_stock < 10 THEN 1 ELSE 0 END) as low_stock_items
          FROM inventory_summary");
        $inventoryStats = $stmt->fetch() ?: $inventoryStats;
    }
    
    // Material Requests Statistics
    if (Auth::hasModuleAccess('materials')) {
        $stmt = $db->query("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'dispatched' THEN 1 ELSE 0 END) as dispatched
            FROM material_requests");
        $requestStats = $stmt->fetch() ?: $requestStats;
    }
    
    // Recent Activities
    $stmt = $db->query("SELECT 
        'survey' as type, 
        CONCAT('Survey submitted for site ', COALESCE(s.site_id, 'Unknown')) as activity,
        ss.created_at as activity_date
        FROM site_surveys ss 
        LEFT JOIN sites s ON ss.site_id = s.id 
        WHERE ss.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        UNION ALL
        SELECT 
        'installation' as type,
        CONCAT('Installation delegated for site ', COALESCE(s.site_id, 'Unknown')) as activity,
        id.delegation_date as activity_date
        FROM installation_delegations id
        LEFT JOIN sites s ON id.site_id = s.id
        WHERE id.delegation_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        UNION ALL
        SELECT 
        'request' as type,
        'Material request created' as activity,
        mr.created_at as activity_date
        FROM material_requests mr
        WHERE mr.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ORDER BY activity_date DESC LIMIT 10");
    $recentActivities = $stmt->fetchAll() ?: [];
    
    // Monthly trends (last 6 months)
    $stmt = $db->query("SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as count
        FROM sites 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month");
    $monthlyTrends = $stmt->fetchAll() ?: [];
    
    // Top performing vendors
    $stmt = $db->query("SELECT 
        v.name,
        COUNT(ss.id) as surveys_completed,
        COUNT(id.id) as installations_completed
        FROM vendors v
        LEFT JOIN site_surveys ss ON v.id = ss.vendor_id AND ss.survey_status = 'approved'
        LEFT JOIN installation_delegations id ON v.id = id.vendor_id AND id.status = 'completed'
        WHERE v.status = 'active'
        GROUP BY v.id, v.name
        ORDER BY (COUNT(ss.id) + COUNT(id.id)) DESC
        LIMIT 5");
    $topVendors = $stmt->fetchAll() ?: [];
    
} catch (Exception $e) {
    // Default values in case of error
    $totalSites = 0;
    $siteStats = ['delegated' => 0, 'surveyed' => 0, 'installed' => 0, 'pending' => 0];
    $vendorStats = ['total' => 0, 'active' => 0, 'inactive' => 0];
    $surveyStats = ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];
    $installationStats = ['total' => 0, 'assigned' => 0, 'in_progress' => 0, 'completed' => 0, 'overdue' => 0];
    $inventoryStats = ['total_items' => 0, 'total_quantity' => 0, 'low_stock_items' => 0];
    $requestStats = ['total' => 0, 'pending' => 0, 'approved' => 0, 'dispatched' => 0, 'delivered' => 0];
    $recentActivities = [];
    $monthlyTrends = [];
    $topVendors = [];
}
ob_start();
?>

<!-- Dashboard Header -->
<!-- <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-lg shadow-lg p-6 mb-8 text-white">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
        <div class="flex-1">
            <h1 class="text-3xl font-bold">Welcome back, <?php echo htmlspecialchars($currentUser['username']); ?>!</h1>
            <p class="mt-2 text-blue-100">Here's what's happening with your site installation management system today.</p>
            <p class="text-sm text-blue-200 mt-1"><?php echo date('l, F j, Y'); ?></p>
        </div>
        <div class="mt-6 lg:mt-0 lg:ml-6">
            <div class="flex space-x-3">
                <button onclick="refreshDashboard()" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-4 py-2 rounded-lg transition-all duration-200">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Refresh
                </button>
            </div>
        </div>
    </div>
</div> -->

<!-- Key Metrics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Sites Card -->
    <?php if (Auth::hasModuleAccess('sites')): ?>
    <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100 hover:shadow-xl transition-shadow duration-300">
        <div class="p-5">
            <div class="text-sm font-medium text-gray-500 mb-3">Total Sites</div>
            <div class="flex items-center justify-between">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg flex-shrink-0">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-gray-900"><?php echo ($totalSites); ?></div>
                    <div class="text-sm text-gray-600 mt-1">
                        <span class="text-green-600">+<?php echo $siteStats['delegated'] ?? 0; ?></span> delegated
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Active Vendors Card -->
    <?php if (Auth::hasModuleAccess('users')): ?>
    <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100 hover:shadow-xl transition-shadow duration-300">
        <div class="p-5">
            <div class="text-sm font-medium text-gray-500 mb-3">Active Vendors</div>
            <div class="flex items-center justify-between">
                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg flex-shrink-0">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-gray-900"><?php echo number_format($vendorStats['active'] ?? 0); ?></div>
                    <div class="text-sm text-gray-600 mt-1">
                        <span class="text-gray-500"><?php echo $vendorStats['inactive'] ?? 0; ?> inactive</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Pending Surveys Card -->
    <?php if (Auth::hasModuleAccess('surveys')): ?>
    <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100 hover:shadow-xl transition-shadow duration-300">
        <div class="p-5">
            <div class="text-sm font-medium text-gray-500 mb-3">Pending Surveys</div>
            <div class="flex items-center justify-between">
                <div class="w-12 h-12 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl flex items-center justify-center shadow-lg flex-shrink-0">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-gray-900"><?php echo number_format($surveyStats['pending'] ?? 0); ?></div>
                    <div class="text-sm text-gray-600 mt-1">
                        <span class="text-green-600"><?php echo $surveyStats['approved'] ?? 0; ?> approved</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Active Installations Card -->
    <?php if (Auth::hasModuleAccess('installations')): ?>
    <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100 hover:shadow-xl transition-shadow duration-300">
        <div class="p-5">
            <div class="text-sm font-medium text-gray-500 mb-3">Active Installation</div>
            <div class="flex items-center justify-between">
                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg flex-shrink-0">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-gray-900"><?php echo number_format($installationStats['in_progress'] ?? 0); ?></div>
                    <div class="text-sm text-gray-600 mt-1">
                        <span class="text-red-600"><?php echo $installationStats['overdue'] ?? 0; ?> overdue</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Secondary Metrics Row -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Inventory Status -->
    <div class="bg-white shadow-lg rounded-xl border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Inventory Status</h3>
            <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
            </div>
        </div>
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Total Items</span>
                <span class="font-semibold text-gray-900"><?php echo number_format($inventoryStats['total_items'] ?? 0); ?></span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Total Quantity</span>
                <span class="font-semibold text-gray-900"><?php echo number_format($inventoryStats['total_quantity'] ?? 0); ?></span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Low Stock Items</span>
                <span class="font-semibold text-red-600"><?php echo number_format($inventoryStats['low_stock_items'] ?? 0); ?></span>
            </div>
        </div>
    </div>

    <!-- Material Requests -->
    <div class="bg-white shadow-lg rounded-xl border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Material Requests</h3>
            <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
        </div>
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Total Requests</span>
                <span class="font-semibold text-gray-900"><?php echo number_format($requestStats['total'] ?? 0); ?></span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Pending</span>
                <span class="font-semibold text-yellow-600"><?php echo number_format($requestStats['pending'] ?? 0); ?></span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Dispatched</span>
                <span class="font-semibold text-green-600"><?php echo number_format($requestStats['dispatched'] ?? 0); ?></span>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white shadow-lg rounded-xl border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
            </div>
        </div>
        <div class="space-y-4">
            <?php if (Auth::hasModuleAccess('sites')): ?>
            <a href="<?php echo BASE_URL; ?>/admin/sites/index.php" class="block w-full bg-blue-50 hover:bg-blue-100 text-blue-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add New Site
            </a>
            <?php endif; ?>

            <?php if (Auth::hasModuleAccess('users')): ?>
            <a href="<?php echo BASE_URL; ?>/admin/vendors/" class="block w-full bg-green-50 hover:bg-green-100 text-green-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                </svg>
                Manage Vendors
            </a>
            <?php endif; ?>

            <?php if (Auth::hasModuleAccess('inventory')): ?>
            <a href="<?php echo BASE_URL; ?>/admin/inventory/" class="block w-full bg-purple-50 hover:bg-purple-100 text-purple-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                View Inventory
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Charts and Analytics Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- Site Status Distribution -->
    <div class="bg-white shadow-lg rounded-xl border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Site Status Distribution</h3>
            <div class="text-sm text-gray-500">Total: <?php echo $totalSites; ?> sites</div>
        </div>
        <div class="space-y-4">
            <?php
            $statusData = [
                ['label' => 'Pending Assignment', 'count' => $siteStats['pending'], 'color' => 'bg-gray-500', 'bg' => 'bg-gray-100'],
                ['label' => 'Delegated to Vendor', 'count' => $siteStats['delegated'], 'color' => 'bg-blue-500', 'bg' => 'bg-blue-100'],
                ['label' => 'Survey Completed', 'count' => $siteStats['surveyed'], 'color' => 'bg-yellow-500', 'bg' => 'bg-yellow-100'],
                ['label' => 'Installation Complete', 'count' => $siteStats['installed'], 'color' => 'bg-green-500', 'bg' => 'bg-green-100']
            ];
            
            foreach ($statusData as $status):
                $percentage = $totalSites > 0 ? ($status['count'] / $totalSites) * 100 : 0;
            ?>
            <div class="flex items-center justify-between">
                <div class="flex items-center flex-1">
                    <div class="w-3 h-3 <?php echo $status['color']; ?> rounded-full mr-3"></div>
                    <span class="text-sm text-gray-700 flex-1"><?php echo $status['label']; ?></span>
                    <span class="text-sm font-semibold text-gray-900 mr-4"><?php echo $status['count']; ?></span>
                </div>
                <div class="w-24 <?php echo $status['bg']; ?> rounded-full h-2">
                    <div class="<?php echo $status['color']; ?> h-2 rounded-full" style="width: <?php echo $percentage; ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Installation Progress -->
    <div class="bg-white shadow-lg rounded-xl border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Installation Progress</h3>
            <div class="text-sm text-gray-500">Total: <?php echo $installationStats['total']; ?> installations</div>
        </div>
        <div class="space-y-4">
            <?php
            $installationData = [
                ['label' => 'Assigned', 'count' => $installationStats['assigned'] ?? 0, 'color' => 'bg-blue-500', 'bg' => 'bg-blue-100'],
                ['label' => 'In Progress', 'count' => $installationStats['in_progress'] ?? 0, 'color' => 'bg-yellow-500', 'bg' => 'bg-yellow-100'],
                ['label' => 'Completed', 'count' => $installationStats['completed'] ?? 0, 'color' => 'bg-green-500', 'bg' => 'bg-green-100'],
                ['label' => 'Overdue', 'count' => $installationStats['overdue'] ?? 0, 'color' => 'bg-red-500', 'bg' => 'bg-red-100']
            ];
            
            $totalInstallations = $installationStats['total'] ?? 1;
            foreach ($installationData as $installation):
                $percentage = $totalInstallations > 0 ? ($installation['count'] / $totalInstallations) * 100 : 0;
            ?>
            <div class="flex items-center justify-between">
                <div class="flex items-center flex-1">
                    <div class="w-3 h-3 <?php echo $installation['color']; ?> rounded-full mr-3"></div>
                    <span class="text-sm text-gray-700 flex-1"><?php echo $installation['label']; ?></span>
                    <span class="text-sm font-semibold text-gray-900 mr-4"><?php echo $installation['count']; ?></span>
                </div>
                <div class="w-24 <?php echo $installation['bg']; ?> rounded-full h-2">
                    <div class="<?php echo $installation['color']; ?> h-2 rounded-full" style="width: <?php echo $percentage; ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Bottom Row: Recent Activities and Top Vendors -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Recent Activities -->
    <div class="bg-white shadow-lg rounded-xl border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Recent Activities</h3>
            <span class="text-sm text-gray-500">Last 7 days</span>
        </div>
        <div class="space-y-4">
            <?php if (empty($recentActivities)): ?>
                <div class="text-center py-8 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p>No recent activities</p>
                </div>
            <?php else: ?>
                <?php foreach (array_slice($recentActivities, 0, 8) as $activity): ?>
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <?php
                        $iconClass = 'w-2 h-2 rounded-full mt-2';
                        switch($activity['type']) {
                            case 'survey':
                                $iconClass .= ' bg-yellow-500';
                                break;
                            case 'installation':
                                $iconClass .= ' bg-purple-500';
                                break;
                            case 'request':
                                $iconClass .= ' bg-blue-500';
                                break;
                            default:
                                $iconClass .= ' bg-gray-500';
                        }
                        ?>
                        <div class="<?php echo $iconClass; ?>"></div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-900"><?php echo htmlspecialchars($activity['activity']); ?></p>
                        <p class="text-xs text-gray-500"><?php echo date('M j, Y g:i A', strtotime($activity['activity_date'])); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Top Performing Vendors -->
    <div class="bg-white shadow-lg rounded-xl border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Top Performing Vendors</h3>
            <a href="<?php echo BASE_URL; ?>/admin/vendors/" class="text-sm text-blue-600 hover:text-blue-800">View All</a>
        </div>
        <div class="space-y-4">
            <?php if (empty($topVendors)): ?>
                <div class="text-center py-8 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <p>No vendor performance data</p>
                </div>
            <?php else: ?>
                <?php foreach ($topVendors as $index => $vendor): ?>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-bold text-sm">
                            <?php echo $index + 1; ?>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($vendor['name']); ?></p>
                            <p class="text-xs text-gray-500">
                                <?php echo $vendor['surveys_completed']; ?> surveys, <?php echo $vendor['installations_completed']; ?> installations
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-semibold text-gray-900">
                            <?php echo $vendor['surveys_completed'] + $vendor['installations_completed']; ?>
                        </div>
                        <div class="text-xs text-gray-500">Total</div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Auto-refresh dashboard every 5 minutes
setInterval(refreshDashboard, 300000);

function refreshDashboard() {
    location.reload();
}

// Add some interactivity
document.addEventListener('DOMContentLoaded', function() {
    // Add hover effects to cards
    const cards = document.querySelectorAll('.hover\\:shadow-xl');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
</script>
<?php
$content = ob_get_clean();
include '../includes/admin_layout.php';
?>