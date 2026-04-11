<?php 
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/Site.php';
require_once __DIR__ . '/../../models/SiteDelegation.php';

// Require vendor authentication and permission
// Auth::requireVendorPermission('view_sites');

$vendorId = Auth::getVendorId();
$siteModel = new Site();
$delegationModel = new SiteDelegation();

// Get vendor's delegated sites
$delegatedSites = $delegationModel->getVendorDelegations($vendorId, 'active');
$completedSites = $delegationModel->getVendorDelegations($vendorId, 'completed');

// Combine and format for display
$sites = [];
foreach ($delegatedSites as $site) {
    $site['delegation_status'] = 'active';
    $sites[] = $site;
}
foreach ($completedSites as $site) {
    $site['delegation_status'] = 'completed';
    $sites[] = $site;
}

$title = 'My Sites';
ob_start();
?>

<!-- Enhanced Header -->
<div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl shadow-lg p-6 mb-8">
    <div class="flex justify-between items-center">
        <div class="text-white">
            <h1 class="text-3xl font-bold">My Sites</h1>
            <p class="mt-2 text-blue-100">Professional Site Management Dashboard</p>
            <p class="text-sm text-blue-200 mt-1">Manage your delegated installation projects efficiently</p>
        </div>
        <div class="flex items-center space-x-4">
            <!-- Stats Cards -->
            <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-4 text-center min-w-24">
                <div class="text-2xl font-bold text-white"><?php echo count($delegatedSites); ?></div>
                <div class="text-xs text-blue-100 uppercase tracking-wide">Active</div>
            </div>
            <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-4 text-center min-w-24">
                <div class="text-2xl font-bold text-white"><?php echo count($completedSites); ?></div>
                <div class="text-xs text-blue-100 uppercase tracking-wide">Completed</div>
            </div>
            <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-4 text-center min-w-24">
                <div class="text-2xl font-bold text-white"><?php echo count($sites); ?></div>
                <div class="text-xs text-blue-100 uppercase tracking-wide">Total</div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Filter Tabs -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6 overflow-hidden">
    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
            <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd"></path>
            </svg>
            Filter Sites
        </h3>
    </div>
    <div class="p-6">
        <nav class="flex space-x-1 bg-gray-100 rounded-lg p-1">
            <button onclick="filterSites('all')" id="tab-all" class="tab-button active flex-1 px-4 py-2 text-sm font-medium rounded-md transition-all duration-200">
                <div class="flex items-center justify-center space-x-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" clip-rule="evenodd"></path>
                    </svg>
                    <span>All Sites</span>
                    <span class="bg-blue-100 text-blue-800 text-xs px-2 py-0.5 rounded-full"><?php echo count($sites); ?></span>
                </div>
            </button>
            <button onclick="filterSites('active')" id="tab-active" class="tab-button flex-1 px-4 py-2 text-sm font-medium rounded-md transition-all duration-200">
                <div class="flex items-center justify-center space-x-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                    </svg>
                    <span>Active</span>
                    <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-0.5 rounded-full"><?php echo count($delegatedSites); ?></span>
                </div>
            </button>
            <button onclick="filterSites('completed')" id="tab-completed" class="tab-button flex-1 px-4 py-2 text-sm font-medium rounded-md transition-all duration-200">
                <div class="flex items-center justify-center space-x-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <span>Completed</span>
                    <span class="bg-green-100 text-green-800 text-xs px-2 py-0.5 rounded-full"><?php echo count($completedSites); ?></span>
                </div>
            </button>
        </nav>
    </div>
</div>

<!-- Sites Table -->
<div class="professional-table bg-white">
    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">Site Assignments</h3>
            <p class="text-sm text-gray-500 mt-1">Manage your delegated installation projects</p>
        </div>
    </div>
    <div class="p-6">
        <?php if (empty($sites)): ?>
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h3M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No sites assigned</h3>
                <p class="mt-1 text-sm text-gray-500">You don't have any sites assigned to you yet.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="sitesTable">
                    <thead class="table-header">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Site Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($sites as $site): ?>
                        <tr class="site-row hover:bg-gray-50 transition-colors" data-status="<?php echo $site['delegation_status']; ?>">
                            <td class="px-6 py-4">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 h-12 w-12">
                                        <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-lg">
                                            <svg class="h-6 w-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm0 2h12v8H4V6z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4 flex-1 min-w-0">
                                        <div class="flex items-center space-x-2 mb-1">
                                            <h4 class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($site['site_id']); ?></h4>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Site
                                            </span>
                                        </div>
                                        <div class="text-sm text-gray-600 break-words max-w-xs" title="<?php echo htmlspecialchars($site['location']); ?>">
                                            <?php 
                                            $location = $site['location'];
                                            if (strlen($location) > 60) {
                                                echo htmlspecialchars(substr($location, 0, 60)) . '...';
                                            } else {
                                                echo htmlspecialchars($location);
                                            }
                                            ?>
                                        </div>
                                        <?php if (!empty($site['notes'])): ?>
                                            <div class="text-xs text-amber-600 mt-2 flex items-center bg-amber-50 px-2 py-1 rounded-md">
                                                <svg class="w-3 h-3 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                                </svg>
                                                <span class="truncate">Special instructions</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="h-8 w-8 rounded-lg bg-green-100 flex items-center justify-center">
                                            <svg class="h-4 w-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($site['city'] . ', ' . $site['state']); ?></div>
                                        <div class="text-xs text-gray-500 flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4A1 1 0 0116 13H6a3 3 0 01-3-3V6z" clip-rule="evenodd"></path>
                                            </svg>
                                            <?php echo htmlspecialchars($site['country']); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="h-10 w-10 rounded-lg bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center">
                                            <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-3 flex-1 min-w-0">
                                        <div class="text-sm font-medium text-gray-900 truncate" title="<?php echo htmlspecialchars($site['customer'] ?? 'N/A'); ?>">
                                            <?php echo htmlspecialchars($site['customer'] ?? 'N/A'); ?>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            Customer
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col space-y-2">
                                    <div class="flex items-center">
                                        <?php if ($site['delegation_status'] === 'active'): ?>
                                            <div class="h-2 w-2 bg-yellow-400 rounded-full mr-2 animate-pulse"></div>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-yellow-100 to-yellow-200 text-yellow-800 border border-yellow-300">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                </svg>
                                                Active
                                            </span>
                                        <?php else: ?>
                                            <div class="h-2 w-2 bg-green-400 rounded-full mr-2"></div>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-green-100 to-green-200 text-green-800 border border-green-300">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                Completed
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-xs text-gray-500 flex items-center">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                        </svg>
                                        Since <?php echo date('M d, Y', strtotime($site['delegation_date'])); ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-3">
                                    <!-- Survey Progress -->
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            <?php if ($site['survey_status'] ?? false): ?>
                                                <div class="h-6 w-6 rounded-full bg-green-500 flex items-center justify-center">
                                                    <svg class="h-3 w-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                </div>
                                            <?php else: ?>
                                                <div class="h-6 w-6 rounded-full bg-yellow-400 flex items-center justify-center">
                                                    <svg class="h-3 w-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                    </svg>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-xs font-medium text-gray-900">Survey</div>
                                            <div class="text-xs">
                                                <?php 
                                                $surveyStatus = $site['survey_status'] ?? 'pending';
                                                $statusColors = [
                                                    'pending' => 'text-yellow-600',
                                                    'submitted' => 'text-blue-600', 
                                                    'approved' => 'text-green-600',
                                                    'rejected' => 'text-red-600'
                                                ];
                                                $colorClass = $statusColors[$surveyStatus] ?? 'text-gray-500';
                                                ?>
                                                <span class="<?php echo $colorClass; ?> font-medium">
                                                    <?php echo ucfirst($surveyStatus); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Installation Progress -->
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            <?php if ($site['installation_status'] ?? false): ?>
                                                <div class="h-6 w-6 rounded-full bg-green-500 flex items-center justify-center">
                                                    <svg class="h-3 w-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                </div>
                                            <?php else: ?>
                                                <div class="h-6 w-6 rounded-full bg-gray-300 flex items-center justify-center">
                                                    <svg class="h-3 w-3 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M9.504 1.132a1 1 0 01.992 0l1.75 1a1 1 0 11-.992 1.736L10 3.152l-1.254.716a1 1 0 11-.992-1.736l1.75-1zM5.618 4.504a1 1 0 01-.372 1.364L5.016 6l.23.132a1 1 0 11-.992 1.736L3 7.723V8a1 1 0 01-2 0V6a.996.996 0 01.52-.878l1.734-.99a1 1 0 011.364.372zm8.764 0a1 1 0 011.364-.372l1.734.99A.996.996 0 0118 6v2a1 1 0 11-2 0v-.277l-1.254.145a1 1 0 11-.992-1.736L14.984 6l-.23-.132a1 1 0 01-.372-1.364zm-7 4a1 1 0 011.364-.372L10 8.848l1.254-.716a1 1 0 11.992 1.736L11 10.723V12a1 1 0 11-2 0v-1.277l-1.246-.855a1 1 0 01-.372-1.364zM3 11a1 1 0 011 1v1.277l1.246.855a1 1 0 11-.992 1.736l-1.75-1A1 1 0 012 14v-2a1 1 0 011-1zm14 0a1 1 0 011 1v2a1 1 0 01-.504.868l-1.75 1a1 1 0 11-.992-1.736L16 13.277V12a1 1 0 011-1zm-9.618 5.504a1 1 0 011.364-.372l.254.145V16a1 1 0 112 0v.277l.254-.145a1 1 0 11.992 1.736l-1.75 1a.996.996 0 01-.992 0l-1.75-1a1 1 0 01-.372-1.364z" clip-rule="evenodd"></path>
                                                    </svg>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-xs font-medium text-gray-900">Installation</div>
                                            <div class="text-xs text-gray-500">
                                                <?php echo ($site['installation_status'] ?? false) ? 'Completed' : 'Pending'; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <!-- View Details Button -->
                                    <button onclick="viewSiteDetails(<?php echo $site['id']; ?>, '<?php echo htmlspecialchars($site['site_id']); ?>')" 
                                            class="group relative inline-flex items-center justify-center p-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200" 
                                            title="View Details">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                    
                                    <?php if ($site['delegation_status'] === 'active' || $site['delegation_status'] === 'completed'): ?>

                                            <!-- Survey Button -->
                                            <button onclick="conductSurvey(<?php echo $site['id']; ?>)" 
                                                    class="group relative inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 shadow-lg hover:shadow-xl transition-all duration-200" 
                                                    title="Conduct Site Survey">
                                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" clip-rule="evenodd"></path>
                                                </svg>
                                                Survey
                                            </button>

                                            <!-- Material Request Button -->
                                            <!-- <button onclick="generateMaterialRequest(<?php echo $site['id']; ?>)" 
                                                    class="group relative inline-flex items-center justify-center px-4 py-2 border border-blue-300 text-sm font-medium rounded-lg text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200" 
                                                    title="Generate Material Request">
                                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zM8 6a2 2 0 114 0v1H8V6zM6 9a1 1 0 012 0v1a1 1 0 11-2 0V9zm8 0a1 1 0 012 0v1a1 1 0 11-2 0V9z" clip-rule="evenodd"></path>
                                                </svg>
                                                Materials
                                            </button> -->
                                        

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

<!-- Site Details Modal -->
<div id="siteDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-4 border-b">
                <h3 class="text-lg font-semibold text-gray-900" id="modalSiteId">Site Details</h3>
                <button onclick="closeSiteModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Modal Content -->
            <div id="modalContent" class="mt-4">
                <div class="flex justify-center items-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    <span class="ml-2 text-gray-600">Loading site details...</span>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="flex justify-end pt-4 border-t mt-6 space-x-2">
                <button onclick="closeSiteModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors">
                    Close
                </button>
                <button id="modalActionButton" onclick="" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors hidden">
                    Take Action
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Tab filtering
function filterSites(status) {
    const rows = document.querySelectorAll('.site-row');
    const tabs = document.querySelectorAll('.tab-button');
    
    // Update tab styles
    tabs.forEach(tab => tab.classList.remove('active'));
    document.getElementById(`tab-${status}`).classList.add('active');
    
    // Filter rows
    rows.forEach(row => {
        if (status === 'all') {
            row.style.display = '';
        } else {
            const rowStatus = row.getAttribute('data-status');
            row.style.display = rowStatus === status ? '' : 'none';
        }
    });
}

// Navigation functions
const BASE_URL = '<?php echo BASE_URL; ?>';

function viewSiteDetails(id, siteId) {
    // Show modal and load site details
    const modal = document.getElementById('siteDetailsModal');
    const modalContent = document.getElementById('modalContent');
    const modalSiteId = document.getElementById('modalSiteId');
    
    // Show modal
    modal.classList.remove('hidden');
    modalSiteId.textContent = `Site Details - ${siteId}`;
    
    // Show loading state
    modalContent.innerHTML = `
        <div class="flex justify-center items-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <span class="ml-2 text-gray-600">Loading site details...</span>
        </div>
    `;
    
    // Fetch site details using the database ID
    fetch(`get-site-details.php?id=${encodeURIComponent(id)}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(text => {
            try {
                const data = JSON.parse(text);
                console.log('Site data received:', data);
                if (data.success) {
                    console.log('Layout files count:', data.site.layout_files ? data.site.layout_files.length : 0);
                    displaySiteDetails(data.site);
                } else {
                    modalContent.innerHTML = `
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 mx-auto text-red-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-red-600">${data.message || 'Failed to load site details'}</p>
                        </div>
                    `;
                }
            } catch (parseError) {
                console.error('JSON Parse Error:', parseError);
                console.error('Response text:', text);
                modalContent.innerHTML = `
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 mx-auto text-red-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-red-600">Invalid response format</p>
                        <p class="text-xs text-gray-500 mt-2">Check console for details</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            modalContent.innerHTML = `
                <div class="text-center py-8">
                    <svg class="w-12 h-12 mx-auto text-red-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-red-600">Network error loading site details</p>
                </div>
            `;
        });
}

// Helper functions for survey status
function getSurveyStatusClass(status) {
    const statusClasses = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'submitted': 'bg-blue-100 text-blue-800',
        'approved': 'bg-green-100 text-green-800',
        'rejected': 'bg-red-100 text-red-800'
    };
    return statusClasses[status] || 'bg-gray-100 text-gray-800';
}

function getSurveyStatusText(status) {
    if (!status) return 'Pending';
    return status.charAt(0).toUpperCase() + status.slice(1);
}

function displaySiteDetails(site) {
    const modalContent = document.getElementById('modalContent');
    const actionButton = document.getElementById('modalActionButton');
    
    modalContent.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Basic Information -->
            <div class="space-y-4">
                <h4 class="font-semibold text-gray-900 border-b pb-2">Basic Information</h4>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Site ID</label>
                        <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded">${site.site_id || 'N/A'}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Store ID</label>
                        <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded">${site.store_id || 'N/A'}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Location</label>
                        <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded">${site.location || 'N/A'}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Branch</label>
                        <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded">${site.branch || 'N/A'}</p>
                    </div>
                </div>
            </div>
            
            <!-- Location Details -->
            <div class="space-y-4">
                <h4 class="font-semibold text-gray-900 border-b pb-2">Location Details</h4>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">City</label>
                        <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded">${site.city || 'N/A'}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">State</label>
                        <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded">${site.state || 'N/A'}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Country</label>
                        <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded">${site.country || 'N/A'}</p>
                    </div>
                </div>
            </div>
            
            <!-- Client Information -->
            <div class="space-y-4">
                <h4 class="font-semibold text-gray-900 border-b pb-2">Client Information</h4>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Customer</label>
                        <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded">${site.customer || 'N/A'}</p>
                    </div>
                </div>
            </div>
            
            <!-- Progress Status -->
            <div class="space-y-4">
                <h4 class="font-semibold text-gray-900 border-b pb-2">Progress Status</h4>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Survey Status</label>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getSurveyStatusClass(site.survey_status)}">
                            ${getSurveyStatusText(site.survey_status)}
                        </span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Installation Status</label>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${site.installation_status ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}">
                            ${site.installation_status ? 'Completed' : 'Pending'}
                        </span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Delegation Date</label>
                        <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded">${site.delegation_date ? new Date(site.delegation_date).toLocaleDateString() : 'N/A'}</p>
                    </div>
                </div>
            </div>
        </div>
        
        ${site.notes ? `
        <div class="mt-6 pt-4 border-t">
            <h4 class="font-semibold text-gray-900 mb-2">Special Instructions</h4>
            <div class="text-sm text-gray-900 bg-blue-50 p-3 rounded border-l-4 border-blue-400">
                ${site.notes}
            </div>
        </div>
        ` : ''}
        
        ${site.remarks ? `
        <div class="mt-4">
            <h4 class="font-semibold text-gray-900 mb-2">Remarks</h4>
            <div class="text-sm text-gray-900 bg-gray-50 p-3 rounded">
                ${site.remarks}
            </div>
        </div>
        ` : ''}
        
        ${site.layout_files && site.layout_files.length > 0 ? `
        <div class="mt-6 pt-4 border-t">
            <h4 class="font-semibold text-gray-900 mb-3">Layout Files</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                ${site.layout_files.map(layout => {
                    const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(layout.file_type.toLowerCase());
                    const fileSize = formatFileSize(layout.file_size);
                    const uploadDate = new Date(layout.uploaded_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
                    
                    return `
                        <div class="border rounded-lg p-4 bg-white hover:shadow-md transition-shadow">
                            <div class="flex items-start space-x-3">
                                ${isImage ? `
                                    <img src="../../admin/sites/${layout.file_path}" 
                                         alt="Layout" 
                                         class="h-20 w-20 object-cover rounded cursor-pointer"
                                         onclick="window.open('../../admin/sites/${layout.file_path}', '_blank')">
                                ` : `
                                    <div class="h-20 w-20 flex items-center justify-center bg-gray-100 rounded">
                                        ${getFileIcon(layout.file_type)}
                                    </div>
                                `}
                                
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate" title="${layout.original_filename}">
                                        ${layout.original_filename}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">${fileSize}</p>
                                    <p class="text-xs text-gray-500">${uploadDate}</p>
                                    ${layout.remarks ? `
                                        <p class="text-xs text-gray-600 mt-2 italic">"${layout.remarks}"</p>
                                    ` : ''}
                                    <a href="../../admin/sites/${layout.file_path}" 
                                       download 
                                       class="inline-flex items-center text-xs text-blue-600 hover:text-blue-800 mt-2">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                        </svg>
                                        Download
                                    </a>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('')}
            </div>
        </div>
        ` : ''}
    `;
    
    // Show action button if needed
    if (site.delegation_status === 'active' && !site.survey_status) {
        actionButton.classList.remove('hidden');
        actionButton.textContent = 'Conduct Survey';
        actionButton.onclick = () => conductSurvey(site.delegation_id);
    } else {
        actionButton.classList.add('hidden');
    }
}

// Helper function to format file size
function formatFileSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(2) + ' KB';
    return (bytes / 1048576).toFixed(2) + ' MB';
}

// Helper function to get file icon
function getFileIcon(fileType) {
    const type = fileType.toLowerCase();
    if (type === 'pdf') {
        return '<svg class="w-10 h-10 text-red-600" fill="currentColor" viewBox="0 0 20 20"><path d="M4 18h12V6h-4V2H4v16zm-2 1V0h12l4 4v16H2v-1z"></path></svg>';
    } else if (type === 'xls' || type === 'xlsx') {
        return '<svg class="w-10 h-10 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path d="M4 2h12l4 4v12H4V2zm1 1v14h10V7h-4V3H5z"></path></svg>';
    } else if (type === 'doc' || type === 'docx') {
        return '<svg class="w-10 h-10 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path d="M4 2h12l4 4v12H4V2zm1 1v14h10V7h-4V3H5z"></path></svg>';
    }
    return '<svg class="w-10 h-10 text-gray-600" fill="currentColor" viewBox="0 0 20 20"><path d="M4 2h12l4 4v12H4V2zm1 1v14h10V7h-4V3H5z"></path></svg>';
}

function closeSiteModal() {
    document.getElementById('siteDetailsModal').classList.add('hidden');
}



function conductSurvey(delegationId) {
    window.location.href = `${BASE_URL}/vendor/site-survey.php?delegation_id=${delegationId}`;
}

function generateMaterialRequest(siteId) {
    window.location.href = `${BASE_URL}/vendor/material-request.php?site_id=${siteId}`;
}

// Add CSS for tabs and modal
const style = document.createElement('style');
style.textContent = `
    .tab-button {
        color: #6b7280;
        background-color: transparent;
        border: none;
        cursor: pointer;
    }
    .tab-button:hover {
        color: #374151;
        background-color: rgba(255, 255, 255, 0.5);
    }
    .tab-button.active {
        color: #1f2937;
        background-color: white;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
    }
    
    /* Modal animations */
    #siteDetailsModal {
        animation: fadeIn 0.3s ease-out;
    }
    
    #siteDetailsModal > div {
        animation: slideIn 0.3s ease-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    @keyframes slideIn {
        from { transform: translateY(-20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    
    /* Badge styles */
    .badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .badge-warning {
        background-color: #fef3c7;
        color: #92400e;
    }
    
    .badge-success {
        background-color: #d1fae5;
        color: #065f46;
    }
`;
document.head.appendChild(style);

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('siteDetailsModal');
    if (event.target === modal) {
        closeSiteModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modal = document.getElementById('siteDetailsModal');
        if (!modal.classList.contains('hidden')) {
            closeSiteModal();
        }
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/vendor_layout.php';
?>