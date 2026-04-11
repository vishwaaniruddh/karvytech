<?php
require_once '../config/auth.php';
// constants.php is already included by auth.php
require_once '../models/Site.php';
require_once '../models/SiteDelegation.php';
require_once '../models/SiteSurvey.php';

// Require vendor authentication
Auth::requireVendor();

$vendorId = Auth::getVendorId();
$currentUser = Auth::getCurrentUser();

// Initialize models
$siteModel = new Site();
$delegationModel = new SiteDelegation();
$surveyModel = new SiteSurvey();

// Get vendor statistics
try {
    // Get delegated sites
    $activeDelegations = $delegationModel->getVendorDelegations($vendorId, 'active');
    $completedDelegations = $delegationModel->getVendorDelegations($vendorId, 'completed');
    
    // Get survey statistics
    $pendingSurveys = count(array_filter($activeDelegations, function($site) {
        return !($site['survey_status'] ?? false);
    }));
    
    $completedSurveys = count(array_filter($activeDelegations, function($site) {
        return ($site['survey_status'] ?? false);
    }));
    
    // Get installation statistics
    $pendingInstallations = count(array_filter($activeDelegations, function($site) {
        return !($site['installation_status'] ?? false);
    }));
    
    $completedInstallations = count(array_filter($activeDelegations, function($site) {
        return ($site['installation_status'] ?? false);
    }));
    
    // Recent activities (last 5 delegations)
    $recentActivities = array_slice($activeDelegations, 0, 5);
    
} catch (Exception $e) {
    // Default values in case of error
    $activeDelegations = [];
    $completedDelegations = [];
    $pendingSurveys = 0;
    $completedSurveys = 0;
    $pendingInstallations = 0;
    $completedInstallations = 0;
    $recentActivities = [];
}

$title = 'Vendor Dashboard';
ob_start();
?>

<!-- Dashboard Header -->
<div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-lg shadow-lg p-6 mb-8 text-white">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
        <div class="flex-1">
            <h1 class="text-3xl font-bold">Welcome back, <?php echo htmlspecialchars($currentUser['username']); ?>!</h1>
            <p class="mt-2 text-blue-100">Here's your site installation management overview.</p>
            <p class="text-sm text-blue-200 mt-1"><?php echo date('l, F j, Y'); ?></p>
        </div>
        <div class="mt-6 lg:mt-0 lg:ml-6">
            <div class="flex space-x-3">
                <a href="<?php echo BASE_URL; ?>/vendor/sites/" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-4 py-2 rounded-lg transition-all duration-200">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h3M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    View All Sites
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Key Metrics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Active Sites Card -->
    <div class="stats-card p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg stats-icon">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h3M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Active Sites</dt>
                    <dd class="text-2xl font-bold text-gray-900"><?php echo count($activeDelegations); ?></dd>
                    <dd class="text-sm text-gray-600 mt-1">
                        <span class="text-green-600"><?php echo count($completedDelegations); ?></span> completed
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <!-- Pending Surveys Card -->
    <div class="stats-card p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl flex items-center justify-center shadow-lg stats-icon">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                </div>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Pending Surveys</dt>
                    <dd class="text-2xl font-bold text-gray-900"><?php echo $pendingSurveys; ?></dd>
                    <dd class="text-sm text-gray-600 mt-1">
                        <span class="text-green-600"><?php echo $completedSurveys; ?></span> completed
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <!-- Pending Installations Card -->
    <div class="stats-card p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg stats-icon">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Pending Installations</dt>
                    <dd class="text-2xl font-bold text-gray-900"><?php echo $pendingInstallations; ?></dd>
                    <dd class="text-sm text-gray-600 mt-1">
                        <span class="text-green-600"><?php echo $completedInstallations; ?></span> completed
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <!-- Total Progress Card -->
    <div class="stats-card p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg stats-icon">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Projects</dt>
                    <dd class="text-2xl font-bold text-gray-900"><?php echo count($activeDelegations) + count($completedDelegations); ?></dd>
                    <dd class="text-sm text-gray-600 mt-1">
                        All time assignments
                    </dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activities and Quick Actions -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Recent Site Assignments -->
    <div class="professional-table">
        <div class="px-6 py-4 border-b border-gray-200 table-header">
            <h3 class="text-lg font-semibold text-gray-900">Recent Site Assignments</h3>
            <p class="text-sm text-gray-500 mt-1">Your latest delegated sites</p>
        </div>
        <div class="p-6">
            <?php if (empty($recentActivities)): ?>
                <div class="text-center py-8 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h3M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    <p>No recent site assignments</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($recentActivities as $site): ?>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm0 2h12v8H4V6z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($site['site_id']); ?></p>
                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($site['location']); ?></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-gray-500">
                                <?php echo date('M d', strtotime($site['delegation_date'])); ?>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?php echo ($site['survey_status'] ?? false) ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                <?php echo ($site['survey_status'] ?? false) ? 'Survey Done' : 'Survey Pending'; ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-4 text-center">
                    <a href="<?php echo BASE_URL; ?>/vendor/sites/" class="text-sm text-blue-600 hover:text-blue-800">View All Sites →</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="professional-table">
        <div class="px-6 py-4 border-b border-gray-200 table-header">
            <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
            <p class="text-sm text-gray-500 mt-1">Common tasks and shortcuts</p>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                <a href="<?php echo BASE_URL; ?>/vendor/sites/" class="block w-full bg-blue-50 hover:bg-blue-100 text-blue-700 px-4 py-3 rounded-lg text-sm font-medium transition-colors duration-200">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h3M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <div>
                            <div class="font-medium">View My Sites</div>
                            <div class="text-xs text-blue-600">Manage delegated installations</div>
                        </div>
                    </div>
                </a>
                
                <a href="<?php echo BASE_URL; ?>/vendor/surveys.php" class="block w-full bg-green-50 hover:bg-green-100 text-green-700 px-4 py-3 rounded-lg text-sm font-medium transition-colors duration-200">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                        <div>
                            <div class="font-medium">Site Surveys</div>
                            <div class="text-xs text-green-600">Conduct and manage surveys</div>
                        </div>
                    </div>
                </a>
                
                <a href="<?php echo BASE_URL; ?>/vendor/installations.php" class="block w-full bg-purple-50 hover:bg-purple-100 text-purple-700 px-4 py-3 rounded-lg text-sm font-medium transition-colors duration-200">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <div>
                            <div class="font-medium">Installations</div>
                            <div class="text-xs text-purple-600">Track installation progress</div>
                        </div>
                    </div>
                </a>
                
                <a href="<?php echo BASE_URL; ?>/vendor/material-requests-list.php" class="block w-full bg-orange-50 hover:bg-orange-100 text-orange-700 px-4 py-3 rounded-lg text-sm font-medium transition-colors duration-200">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <div>
                            <div class="font-medium">Material Requests</div>
                            <div class="text-xs text-orange-600">Manage material requirements</div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include '../includes/vendor_layout.php';
?>