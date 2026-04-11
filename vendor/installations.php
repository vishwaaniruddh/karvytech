<?php
require_once __DIR__ . '/../config/auth.php';
// constants.php is already included by auth.php
require_once __DIR__ . '/../models/Installation.php';

// Require vendor authentication
Auth::requireVendor();

$vendorId = Auth::getVendorId();
$installationModel = new Installation();

// Get all installations assigned to this vendor
$installations = $installationModel->getVendorInstallations($vendorId);
$stats = $installationModel->getVendorInstallationStats($vendorId);

$title = 'My Installations';
ob_start();
?>

<!-- Enhanced Header -->
<div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl shadow-lg p-6 mb-8">
    <div class="flex justify-between items-center">
        <div class="text-white">
            <h1 class="text-3xl font-bold">My Installations</h1>
            <p class="mt-2 text-blue-100">Professional Installation Management Dashboard</p>
            <p class="text-sm text-blue-200 mt-1">Track and manage your assigned installation projects</p>
        </div>
        <div class="flex items-center space-x-4">
            <!-- Quick Stats -->
            <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-4 text-center min-w-24">
                <div class="text-2xl font-bold text-white"><?php echo $stats['total'] ?? 0; ?></div>
                <div class="text-xs text-blue-100 uppercase tracking-wide">Total</div>
            </div>
            <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-4 text-center min-w-24">
                <div class="text-2xl font-bold text-white"><?php echo $stats['in_progress'] ?? 0; ?></div>
                <div class="text-xs text-blue-100 uppercase tracking-wide">Active</div>
            </div>
            <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-4 text-center min-w-24">
                <div class="text-2xl font-bold text-white"><?php echo $stats['completed'] ?? 0; ?></div>
                <div class="text-xs text-blue-100 uppercase tracking-wide">Done</div>
            </div>
            <!-- Refresh Button -->
            <button onclick="refreshInstallations()" class="bg-white bg-opacity-20 backdrop-blur-sm hover:bg-opacity-30 text-white px-4 py-2 rounded-lg transition-all duration-200 flex items-center space-x-2">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
                </svg>
                <span>Refresh</span>
            </button>
        </div>
    </div>
</div>

<!-- Enhanced Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <!-- Total Assigned Card -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Assigned</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo $stats['total'] ?? 0; ?></p>
                        </div>
                    </div>
                </div>
                <div class="text-blue-500">
                    <svg class="w-8 h-8 opacity-20" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-blue-50 px-6 py-3">
            <div class="text-xs text-blue-600 font-medium">All Projects</div>
        </div>
    </div>

    <!-- In Progress Card -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600">In Progress</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo $stats['in_progress'] ?? 0; ?></p>
                        </div>
                    </div>
                </div>
                <div class="text-yellow-500">
                    <svg class="w-8 h-8 opacity-20" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-yellow-50 px-6 py-3">
            <div class="text-xs text-yellow-600 font-medium">Active Work</div>
        </div>
    </div>

    <!-- Completed Card -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600">Completed</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo $stats['completed'] ?? 0; ?></p>
                        </div>
                    </div>
                </div>
                <div class="text-green-500">
                    <svg class="w-8 h-8 opacity-20" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-green-50 px-6 py-3">
            <div class="text-xs text-green-600 font-medium">Finished</div>
        </div>
    </div>

    <!-- Overdue Card -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-red-600 rounded-xl flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600">Overdue</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo $stats['overdue'] ?? 0; ?></p>
                        </div>
                    </div>
                </div>
                <div class="text-red-500">
                    <svg class="w-8 h-8 opacity-20" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-red-50 px-6 py-3">
            <div class="text-xs text-red-600 font-medium">Needs Attention</div>
        </div>
    </div>
</div>

<!-- Enhanced Installations Table -->
<div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
    <div class="px-6 py-5 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-xl font-bold text-gray-900 flex items-center">
                    <svg class="w-6 h-6 mr-3 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" clip-rule="evenodd"></path>
                    </svg>
                    Installation Tasks
                </h3>
                <p class="mt-2 text-sm text-gray-600">
                    Your assigned installation projects and their current status
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    <?php echo count($installations); ?> Projects
                </span>
            </div>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        Installation Details
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        Site Information
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        Timeline
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($installations)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center justify-center space-y-4">
                                <div class="w-20 h-20 bg-gradient-to-br from-blue-100 to-blue-200 rounded-full flex items-center justify-center">
                                    <svg class="w-10 h-10 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                </div>
                                <div class="text-center">
                                    <h3 class="text-lg font-semibold text-gray-900">No installations assigned</h3>
                                    <p class="mt-2 text-sm text-gray-600 max-w-md">You don't have any installation tasks assigned yet. New assignments will appear here when they become available.</p>
                                </div>
                                <div class="flex items-center space-x-2 text-xs text-gray-500">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span>Check back later for new assignments</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($installations as $installation): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 h-12 w-12">
                                        <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-lg">
                                            <svg class="h-6 w-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4 flex-1 min-w-0">
                                        <div class="flex items-center space-x-2 mb-1">
                                            <h4 class="text-sm font-semibold text-gray-900">Installation #<?php echo $installation['id']; ?></h4>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <?php echo ucfirst($installation['installation_type']); ?>
                                            </span>
                                        </div>
                                        <div class="flex items-center space-x-2 mt-2">
                                            <span class="text-xs text-gray-500">Priority:</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                                <?php 
                                                switch($installation['priority']) {
                                                    case 'urgent': echo 'bg-gradient-to-r from-red-100 to-red-200 text-red-800 border border-red-300'; break;
                                                    case 'high': echo 'bg-gradient-to-r from-orange-100 to-orange-200 text-orange-800 border border-orange-300'; break;
                                                    case 'medium': echo 'bg-gradient-to-r from-yellow-100 to-yellow-200 text-yellow-800 border border-yellow-300'; break;
                                                    default: echo 'bg-gradient-to-r from-gray-100 to-gray-200 text-gray-800 border border-gray-300'; break;
                                                }
                                                ?>">
                                                <?php 
                                                switch($installation['priority']) {
                                                    case 'urgent': echo '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>'; break;
                                                    case 'high': echo '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>'; break;
                                                    case 'medium': echo '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path></svg>'; break;
                                                    default: echo '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 6.414 6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>'; break;
                                                }
                                                echo ucfirst($installation['priority']);
                                                ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="h-10 w-10 rounded-lg bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center shadow-md">
                                            <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-3 flex-1 min-w-0">
                                        <div class="text-sm font-semibold text-gray-900 flex items-center">
                                            <svg class="w-4 h-4 mr-1 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                                            </svg>
                                            <?php echo htmlspecialchars($installation['site_code'] ?? 'N/A'); ?>
                                        </div>
                                        <div class="text-sm text-gray-600 break-words max-w-xs" title="<?php echo htmlspecialchars($installation['location'] ?? 'Address not available'); ?>">
                                            <?php 
                                            $location = $installation['location'] ?? 'Address not available';
                                            if (strlen($location) > 50) {
                                                echo htmlspecialchars(substr($location, 0, 50)) . '...';
                                            } else {
                                                echo htmlspecialchars($location);
                                            }
                                            ?>
                                        </div>
                                        <div class="text-xs text-blue-600 flex items-center mt-1">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                            </svg>
                                            <?php echo htmlspecialchars($installation['city_name'] . ', ' . $installation['state_name']); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-3">
                                    <!-- Expected Timeline -->
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <div class="flex items-center mb-2">
                                            <svg class="w-4 h-4 mr-2 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                            </svg>
                                            <span class="text-xs font-semibold text-purple-700 uppercase tracking-wide">Expected</span>
                                        </div>
                                        <div class="text-xs text-gray-600 space-y-1">
                                            <div class="flex items-center">
                                                <span class="w-12 text-gray-500">Start:</span>
                                                <span class="font-medium"><?php echo $installation['expected_start_date'] ? date('M j, Y', strtotime($installation['expected_start_date'])) : 'Not set'; ?></span>
                                            </div>
                                            <div class="flex items-center">
                                                <span class="w-12 text-gray-500">End:</span>
                                                <span class="font-medium"><?php echo $installation['expected_completion_date'] ? date('M j, Y', strtotime($installation['expected_completion_date'])) : 'Not set'; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <?php if ($installation['actual_start_date']): ?>
                                        <!-- Actual Start -->
                                        <div class="bg-blue-50 rounded-lg p-3">
                                            <div class="flex items-center mb-2">
                                                <svg class="w-4 h-4 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                </svg>
                                                <span class="text-xs font-semibold text-blue-700 uppercase tracking-wide">Actual Start</span>
                                            </div>
                                            <div class="text-xs text-blue-700 font-medium">
                                                <?php echo date('M j, Y H:i', strtotime($installation['actual_start_date'])); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <?php
                                    $status = $installation['status'];
                                    switch($status) {
                                        case 'completed':
                                            echo '<div class="h-2 w-2 bg-green-400 rounded-full mr-2"></div>';
                                            echo '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-green-100 to-green-200 text-green-800 border border-green-300">';
                                            echo '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>';
                                            echo 'Completed</span>';
                                            break;
                                        case 'in_progress':
                                            echo '<div class="h-2 w-2 bg-blue-400 rounded-full mr-2 animate-pulse"></div>';
                                            echo '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-blue-100 to-blue-200 text-blue-800 border border-blue-300">';
                                            echo '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path></svg>';
                                            echo 'In Progress</span>';
                                            break;
                                        case 'on_hold':
                                            echo '<div class="h-2 w-2 bg-yellow-400 rounded-full mr-2"></div>';
                                            echo '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-yellow-100 to-yellow-200 text-yellow-800 border border-yellow-300">';
                                            echo '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>';
                                            echo 'On Hold</span>';
                                            break;
                                        case 'cancelled':
                                            echo '<div class="h-2 w-2 bg-red-400 rounded-full mr-2"></div>';
                                            echo '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-red-100 to-red-200 text-red-800 border border-red-300">';
                                            echo '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>';
                                            echo 'Cancelled</span>';
                                            break;
                                        case 'acknowledged':
                                            echo '<div class="h-2 w-2 bg-purple-400 rounded-full mr-2"></div>';
                                            echo '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-purple-100 to-purple-200 text-purple-800 border border-purple-300">';
                                            echo '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>';
                                            echo 'Acknowledged</span>';
                                            break;
                                        case 'assigned':
                                            echo '<div class="h-2 w-2 bg-indigo-400 rounded-full mr-2 animate-pulse"></div>';
                                            echo '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-indigo-100 to-indigo-200 text-indigo-800 border border-indigo-300">';
                                            echo '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>';
                                            echo 'Assigned</span>';
                                            break;
                                        default:
                                            echo '<div class="h-2 w-2 bg-gray-400 rounded-full mr-2"></div>';
                                            echo '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 border border-gray-300">';
                                            echo ucfirst(str_replace('_', ' ', $status)) . '</span>';
                                    }
                                    ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <!-- View Installation Button -->
                                    <button onclick="viewInstallation(<?php echo $installation['id']; ?>)" 
                                            class="group relative inline-flex items-center justify-center p-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200" 
                                            title="View Details">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                    
                                    <?php if ($installation['status'] === 'assigned'): ?>
                                        <!-- Acknowledge Button -->
                                        <button onclick="acknowledgeInstallation(<?php echo $installation['id']; ?>)" 
                                                class="group relative inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-lg hover:shadow-xl transition-all duration-200" 
                                                title="Acknowledge Assignment">
                                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            Acknowledge
                                        </button>
                                    <?php elseif (in_array($installation['status'], ['acknowledged', 'in_progress'])): ?>
                                        <!-- Manage Installation Button -->
                                        <button onclick="manageInstallation(<?php echo $installation['id']; ?>)" 
                                                class="group relative inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 shadow-lg hover:shadow-xl transition-all duration-200" 
                                                title="Manage Installation">
                                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"></path>
                                            </svg>
                                            Manage
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function refreshInstallations() {
    location.reload();
}

function viewInstallation(installationId) {
    window.location.href = 'manage-installation.php?id=' + installationId;
}

function acknowledgeInstallation(installationId) {
    if (confirm('Are you sure you want to acknowledge this installation assignment?')) {
        fetch('process-installation-action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'acknowledge',
                installation_id: installationId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Installation acknowledged successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing the request.');
        });
    }
}

function manageInstallation(installationId) {
    window.location.href = 'manage-installation.php?id=' + installationId;
}
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../includes/vendor_layout.php';
?>