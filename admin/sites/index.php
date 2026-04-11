<?php
require_once __DIR__ . '/../../controllers/SitesController.php';
require_once __DIR__ . '/../../includes/rbac_helper.php';

$controller = new SitesController();
$data = $controller->index();



$title = 'Sites Management';
ob_start();

// var_dump($data);
?>

<style>
    th,td{
        white-space:nowrap;
    }
</style>

<!-- Stats Overview -->
<div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3 mb-6">
    <!-- Total Sites -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-2 mb-2">
            <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="text-2xl font-bold text-gray-900"><?php echo number_format($data['stats']['total_sites'] ?? 0); ?></div>
        </div>
        <div class="text-[10px] text-gray-500 uppercase font-semibold tracking-wide">Total Sites</div>
    </div>

    <!-- Delegation Active -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-2 mb-2">
            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>
                </svg>
            </div>
            <div class="text-2xl font-bold text-gray-900"><?php echo number_format($data['stats']['delegation_active'] ?? 0); ?></div>
        </div>
        <div class="text-[10px] text-gray-500 uppercase font-semibold tracking-wide">Delegated</div>
    </div>

    <!-- Delegation Pending -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-2 mb-2">
            <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="text-2xl font-bold text-gray-900"><?php echo number_format($data['stats']['delegation_pending'] ?? 0); ?></div>
        </div>
        <div class="text-[10px] text-gray-500 uppercase font-semibold tracking-wide">Pending Delegation</div>
    </div>

    <!-- Survey Approved -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-2 mb-2">
            <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="text-2xl font-bold text-gray-900"><?php echo number_format($data['stats']['survey_approved'] ?? 0); ?></div>
        </div>
        <div class="text-[10px] text-gray-500 uppercase font-semibold tracking-wide">Survey Approved</div>
    </div>

    <!-- Survey Pending -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-2 mb-2">
            <div class="w-10 h-10 rounded-lg bg-yellow-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="text-2xl font-bold text-gray-900"><?php echo number_format($data['stats']['survey_pending'] ?? 0); ?></div>
        </div>
        <div class="text-[10px] text-gray-500 uppercase font-semibold tracking-wide">Survey Pending</div>
    </div>

    <!-- Survey Rejected -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-2 mb-2">
            <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="text-2xl font-bold text-gray-900"><?php echo number_format($data['stats']['survey_rejected'] ?? 0); ?></div>
        </div>
        <div class="text-[10px] text-gray-500 uppercase font-semibold tracking-wide">Survey Rejected</div>
    </div>

    <!-- Installation Done -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-2 mb-2">
            <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="text-2xl font-bold text-gray-900"><?php echo number_format($data['stats']['installation_done'] ?? 0); ?></div>
        </div>
        <div class="text-[10px] text-gray-500 uppercase font-semibold tracking-wide">Installation Done</div>
    </div>
</div>

<!-- Secondary Stats Row -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 px-4 py-3 mb-6">
    <div class="flex items-center gap-2">
        <div class="w-2 h-2 rounded-full bg-gray-400"></div>
        <span class="text-xs text-gray-600">Installation Pending:</span>
        <span class="text-sm font-bold text-gray-900"><?php echo number_format($data['stats']['installation_pending'] ?? 0); ?></span>
    </div>
</div>

<!-- Customer-wise Sites Count -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wide flex items-center">
            <svg class="w-4 h-4 mr-1.5 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>
            </svg>
            Customer-wise Sites
        </h3>
    </div>
    <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-2">
        <?php
        if (!empty($data['stats']['customer_counts'])) {
            foreach ($data['stats']['customer_counts'] as $customer):
        ?>
        <div class="bg-gray-50 rounded-md p-2.5 border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-all group">
            <div class="text-lg font-bold text-gray-900 group-hover:text-blue-600"><?php echo $customer['site_count']; ?></div>
            <div class="text-[9px] text-gray-500 font-medium truncate mt-0.5" title="<?php echo htmlspecialchars($customer['customer_name']); ?>">
                <?php echo htmlspecialchars($customer['customer_name']); ?>
            </div>
        </div>
        <?php 
            endforeach;
        } else {
            echo '<p class="text-gray-500 col-span-full text-center py-3 text-xs">No customer data available</p>';
        }
        ?>
    </div>
</div>

<div class="flex justify-between items-center mb-6">
    <div>
        <p class="mt-2 text-sm text-gray-700">Manage installation sites and track progress</p>
    </div>
    <div class="flex space-x-2">
        <a href="export-sites.php<?php echo !empty($data['search']) ? '?search=' . urlencode($data['search']) : ''; ?>" 
           class="btn btn-success">
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
            Export Sites
        </a>
        
        <div class="relative inline-block">
            <a href="bulk_upload.php" class="btn btn-secondary">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                </svg> Upload Sites In Bulk
            </a>
            
            <a href="vendor_assign_bulk_upload.php" class="btn btn-secondary">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                </svg> Upload Vendor Assigned Bulk 
            </a>

            <div id="bulkUploadMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border border-gray-200">
                <div class="py-1">
                </div>
            </div>
        </div>
        <button onclick="openModal('createSiteModal')" class="btn btn-primary" title="Add New Site">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
            </svg>
        </button>
    </div>
</div>

<!-- Search and Filters -->
<div class="card mb-6">
    <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
            <div class="lg:col-span-2">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <input type="text" id="searchInput" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Search sites..." value="<?php echo htmlspecialchars($data['search']); ?>">
                </div>
            </div>
            <div>
                <select id="cityFilter" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">All Cities</option>
                    <?php foreach ($data['filter_options']['cities'] as $city): ?>
                        <option value="<?php echo htmlspecialchars($city); ?>" <?php echo $data['filters']['city'] === $city ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($city); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <select id="stateFilter" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">All States</option>
                    <?php foreach ($data['filter_options']['states'] as $state): ?>
                        <option value="<?php echo htmlspecialchars($state); ?>" <?php echo $data['filters']['state'] === $state ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($state); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <select id="statusFilter" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">All Status</option>
                    <?php foreach ($data['filter_options']['activity_statuses'] as $status): ?>
                        <option value="<?php echo htmlspecialchars($status); ?>" <?php echo $data['filters']['activity_status'] === $status ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($status); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <select id="surveyStatusFilter" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">All Survey Status</option>
                    <option value="pending" <?php echo ($data['filters']['survey_status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="submitted" <?php echo ($data['filters']['survey_status'] ?? '') === 'submitted' ? 'selected' : ''; ?>>Submitted</option>
                    <option value="approved" <?php echo ($data['filters']['survey_status'] ?? '') === 'approved' ? 'selected' : ''; ?>>Approved</option>
                    <option value="rejected" <?php echo ($data['filters']['survey_status'] ?? '') === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Sites Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200" id="sitesTable">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">#</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Site Details</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Site Ticket ID</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Location</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Customer/Contact</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Vendor</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Progress</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php 
                    $serial_number = (($data['pagination']['current_page'] - 1) * $data['pagination']['limit']) + 1;
                    foreach ($data['sites'] as $site):

                        // echo '<pre>';
                        // var_dump($site);
                        // echo '</pre>';

                    ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-3 py-2 whitespace-nowrap text-xs font-medium text-gray-600"><?php echo $serial_number++; ?></td>
                            <td class="px-3 py-2 whitespace-nowrap">
                                <div class="flex items-center space-x-2">
                                    <button onclick="viewSite(<?php echo $site['id']; ?>)" class="btn btn-sm btn-secondary" title="View">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>

                                    <?php if (can('sites', 'edit')): ?>
                                    <button onclick="editSite(<?php echo $site['id']; ?>)" class="btn btn-sm btn-primary" title="Edit">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                                        </svg>
                                    </button>
                                    <?php endif; ?>
                                    <?php if ($site['has_survey_submitted']): ?>
                                        <button onclick="viewSiteSurvey(<?php echo $site['survey_id']; ?>)" class="btn btn-sm btn-success" title="View Site Survey">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd"></path>
                                            </svg>
                                        </button>
                                    <?php else: ?>
                                        <button onclick="delegateSite(<?php echo $site['id']; ?>)" class="btn btn-sm btn-info" title="Delegate Site">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"></path>
                                            </svg>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if (can('sites', 'delete')): ?>
                                    <button onclick="deleteSite(<?php echo $site['id']; ?>)" class="btn btn-sm btn-danger" title="Delete">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"></path>
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 012 0v4a1 1 0 11-2 0V7zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V7a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                    <?php endif; ?>
                                    
                                    <?php if ($site['delegation_status'] === 'active' && !$site['has_survey_submitted']): ?>
                                    <button onclick="conductSurvey(<?php echo $site['id']; ?>)" 
                                            class="group relative inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 shadow-lg hover:shadow-xl transition-all duration-200" 
                                            title="Conduct Site Survey">
                                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" clip-rule="evenodd"></path>
                                        </svg>
                                        Survey
                                    </button>
                                    <?php elseif ($site['delegation_status'] === 'active' && $site['has_survey_submitted']): ?>
                                    <button onclick="conductSurvey(<?php echo $site['id']; ?>)" 
                                            class="group relative inline-flex items-center justify-center px-4 py-2 border border-green-600 text-sm font-medium rounded-lg text-green-700 bg-green-50 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200" 
                                            title="Update Site Survey">
                                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" clip-rule="evenodd"></path>
                                        </svg>
                                        Update Survey
                                    </button>
                                    <?php endif; ?>
                                    
                                    <?php if (in_array($site['actual_survey_status'], ['completed', 'approved'])): ?>
                                        <!-- Material Request Button -->
                                        <button onclick="conductMaterials(<?php echo $site['id']; ?>,<?php echo $site['survey_id']; ?>)" 
                                        
                                           class="group relative inline-flex items-center justify-center px-4 py-2 border border-green-300 text-sm font-medium rounded-lg text-green-700 bg-green-50 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200" 
                                           title="Generate Material Request">
                                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zM8 6a2 2 0 114 0v1H8V6zM6 9a1 1 0 012 0v1a1 1 0 11-2 0V9zm8 0a1 1 0 012 0v1a1 1 0 11-2 0V9z" clip-rule="evenodd"></path>
                                            </svg>
                                            Materials
                                        </button>    
                                        <!--</a>-->
                                    <?php endif; ?>
                                    
                                </div>
                            </td>
                            <td class="px-3 py-2">
                                <div>
                                    <div class="text-xs font-semibold text-gray-900"><?php echo htmlspecialchars($site['site_id'] ?? ''); ?></div>
                                    <?php if ($site['store_id']): ?>
                                        <div class="text-[10px] text-gray-500">Store: <?php echo htmlspecialchars($site['store_id'] ?? ''); ?></div>
                                    <?php endif; ?>
                                    <?php if ($site['po_number']): ?>
                                        <div class="text-[10px] text-gray-500">PO: <?php echo htmlspecialchars($site['po_number'] ?? ''); ?></div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-3 py-2">
                                <div class="text-xs font-semibold text-gray-900"><?php echo htmlspecialchars($site['site_ticket_id'] ?? ''); ?></div>
                            </td>
                            <td class="px-3 py-2">
                                <div class="text-xs text-gray-900"><?php echo htmlspecialchars($site['city'] ?? ''); ?>, <?php echo htmlspecialchars($site['state'] ?? ''); ?></div>
                                <div class="text-[10px] text-gray-500"><?php echo htmlspecialchars($site['country'] ?? ''); ?></div>
                                <?php if ($site['zone']): ?>
                                    <div class="text-[10px] text-gray-500">Zone: <?php echo htmlspecialchars($site['zone']); ?></div>
                                <?php endif; ?>
                                <?php if ($site['pincode']): ?>
                                    <div class="text-[10px] text-gray-500">PIN: <?php echo htmlspecialchars($site['pincode']); ?></div>
                                <?php endif; ?>
                                <?php if ($site['branch']): ?>
                                    <div class="text-[10px] text-gray-500">Branch: <?php echo htmlspecialchars($site['branch'] ?? ''); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-3 py-2">
                                <?php if ($site['customer']): ?>
                                    <div class="text-xs text-gray-900 font-medium"><?php echo htmlspecialchars($site['customer'] ?? ''); ?></div>
                                <?php endif; ?>
                                <?php if ($site['contact_person_name']): ?>
                                    <div class="text-[10px] text-gray-600 mt-1">
                                        <strong>Contact:</strong> <?php echo htmlspecialchars($site['contact_person_name']); ?>
                                    </div>
                                    <?php if ($site['contact_person_number']): ?>
                                        <div class="text-[10px] text-gray-500">
                                            📞 <?php echo htmlspecialchars($site['contact_person_number']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($site['contact_person_email']): ?>
                                        <div class="text-[10px] text-gray-500">
                                            ✉️ <?php echo htmlspecialchars($site['contact_person_email']); ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td class="px-3 py-2">
                                <?php if ($site['delegation_status'] === 'active' && $site['delegated_vendor_name']): ?>
                                    <!-- Delegated Vendor -->
                                    <div class="flex items-center space-x-2">
                                        <span class="badge badge-warning">Delegated</span>
                                        <span class="text-sm text-orange-600"><?php echo htmlspecialchars($site['delegated_vendor_name'] ?? ''); ?></span>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        Since: <?php echo date('M d, Y', strtotime($site['delegation_date'])); ?>
                                    </div>
                                <?php elseif ($site['survey_vendor_name']): ?>
                                    <!-- Survey Vendor -->
                                    <div class="flex items-center space-x-2">
                                        <span class="badge badge-info">Survey By</span>
                                        <span class="text-sm text-blue-600"><?php echo htmlspecialchars($site['survey_vendor_name']); ?></span>
                                    </div>
                                <?php elseif ($site['vendor']): ?>
                                    <!-- Regular Vendor -->
                                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($site['vendor']); ?></div>
                                <?php else: ?>
                                    <!-- No Vendor -->
                                    <span class="text-sm text-gray-400">No vendor assigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($site['activity_status']): ?>
                                    <span class="badge badge-info"><?php echo htmlspecialchars($site['activity_status'] ?? ''); ?></span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">No Status</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="space-y-1">
                                    <div class="flex items-center">
                                        <span class="text-xs text-gray-500 w-16">Survey:</span>
                                        <?php if ($site['has_survey_submitted']): ?>
                                            <?php if ($site['actual_survey_status'] === 'approved'): ?>
                                                <div class="flex items-center space-x-1">
                                                    <span class="badge badge-success">Approved</span>
                                                    <?php 
                                                        $viewUrl = ($site['survey_type'] === 'dynamic') ? "../../shared/view-survey2.php" : "../../shared/view-survey.php";
                                                    ?>
                                                    <a href="<?php echo $viewUrl; ?>?id=<?php echo $site['survey_id']; ?>" 
                                                       class="inline-flex items-center justify-center w-6 h-6 text-green-600 hover:text-green-800 hover:bg-green-50 rounded transition-colors" 
                                                       title="View Survey">
                                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                                        </svg>
                                                    </a>
                                                </div>
                                            <?php elseif ($site['actual_survey_status'] === 'rejected'): ?>
                                                <span class="badge badge-danger">Rejected</span>
                                            <?php else: ?>
                                                <div class="flex items-center space-x-1">
                                                    <span class="badge badge-info">Submitted</span>
                                                    <?php 
                                                        $viewUrl = ($site['survey_type'] === 'dynamic') ? "../../shared/view-survey2.php" : "../../shared/view-survey.php";
                                                    ?>
                                                    <a href="<?php echo $viewUrl; ?>?id=<?php echo $site['survey_id']; ?>" 
                                                       class="inline-flex items-center justify-center w-6 h-6 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded transition-colors" 
                                                       title="View Survey">
                                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                                        </svg>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Pending</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex items-center">
                                        
                                        <span class="text-xs text-gray-500 w-16">Install:</span>
                                        <div class="flex items-center space-x-2">
                                            <span class="badge <?php echo $site['installation_status'] ? 'badge-success' : 'badge-warning'; ?>">
                                                <?php echo $site['installation_status'] ? 'Done' : 'Pending'; ?>
                                            </span>
                                            <?php if ($site['installation_status'] && $site['installation_id']): ?>
                                                <a href="../installations/view.php?id=<?php echo $site['installation_id']; ?>" 
                                                   class="inline-flex items-center justify-center w-6 h-6 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded transition-colors" 
                                                   title="View Installation Details">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                           
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($data['pagination']['total_pages'] > 1): ?>
            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 rounded-b-lg">
                <div class="flex-1 flex justify-between sm:hidden">
                    <!-- Mobile Pagination -->
                    <?php if ($data['pagination']['current_page'] > 1): ?>
                        <a href="?page=<?php echo $data['pagination']['current_page'] - 1; ?><?php echo !empty($data['search']) ? '&search=' . urlencode($data['search']) : ''; ?>" 
                           class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Previous
                        </a>
                    <?php endif; ?>
                    <?php if ($data['pagination']['current_page'] < $data['pagination']['total_pages']): ?>
                        <a href="?page=<?php echo $data['pagination']['current_page'] + 1; ?><?php echo !empty($data['search']) ? '&search=' . urlencode($data['search']) : ''; ?>" 
                           class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Next
                        </a>
                    <?php endif; ?>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing
                            <span class="font-medium"><?php echo (($data['pagination']['current_page'] - 1) * $data['pagination']['limit']) + 1; ?></span>
                            to
                            <span class="font-medium"><?php echo min($data['pagination']['current_page'] * $data['pagination']['limit'], $data['pagination']['total_records']); ?></span>
                            of
                            <span class="font-medium"><?php echo $data['pagination']['total_records']; ?></span>
                            results
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            <?php
                            $current = $data['pagination']['current_page'];
                            $total = $data['pagination']['total_pages'];
                            $search_param = !empty($data['search']) ? '&search=' . urlencode($data['search']) : '';
                            
                            // First button
                            if ($current > 1): ?>
                                <a href="?page=1<?php echo $search_param; ?>" 
                                   class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">First</span>
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M15.707 15.707a1 1 0 01-1.414 0l-5-5a1 1 0 010-1.414l5-5a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 010 1.414zm-6 0a1 1 0 01-1.414 0l-5-5a1 1 0 010-1.414l5-5a1 1 0 011.414 1.414L5.414 10l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
                                    </svg>
                                </a>
                            <?php else: ?>
                                <span class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-300 cursor-not-allowed">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M15.707 15.707a1 1 0 01-1.414 0l-5-5a1 1 0 010-1.414l5-5a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 010 1.414zm-6 0a1 1 0 01-1.414 0l-5-5a1 1 0 010-1.414l5-5a1 1 0 011.414 1.414L5.414 10l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
                                    </svg>
                                </span>
                            <?php endif;
                            
                            // Previous button
                            if ($current > 1): ?>
                                <a href="?page=<?php echo $current - 1; ?><?php echo $search_param; ?>" 
                                   class="relative inline-flex items-center px-2 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">Previous</span>
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </a>
                            <?php else: ?>
                                <span class="relative inline-flex items-center px-2 py-2 border border-gray-300 bg-gray-100 text-sm font-medium text-gray-300 cursor-not-allowed">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </span>
                            <?php endif;
                            
                            // Page numbers with smart ellipsis
                            $range = 2; // Show 2 pages on each side of current
                            
                            // Always show first page
                            if ($current > $range + 2) {
                                echo '<a href="?page=1' . $search_param . '" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">1</a>';
                                if ($current > $range + 3) {
                                    echo '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>';
                                }
                            }
                            
                            // Show pages around current
                            for ($i = max(1, $current - $range); $i <= min($total, $current + $range); $i++) {
                                if ($i == $current) {
                                    echo '<span class="z-10 bg-blue-50 border-blue-500 text-blue-600 relative inline-flex items-center px-4 py-2 border text-sm font-medium">' . $i . '</span>';
                                } else {
                                    echo '<a href="?page=' . $i . $search_param . '" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">' . $i . '</a>';
                                }
                            }
                            
                            // Always show last page
                            if ($current < $total - $range - 1) {
                                if ($current < $total - $range - 2) {
                                    echo '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>';
                                }
                                echo '<a href="?page=' . $total . $search_param . '" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">' . $total . '</a>';
                            }
                            
                            // Next button
                            if ($current < $total): ?>
                                <a href="?page=<?php echo $current + 1; ?><?php echo $search_param; ?>" 
                                   class="relative inline-flex items-center px-2 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">Next</span>
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </a>
                            <?php else: ?>
                                <span class="relative inline-flex items-center px-2 py-2 border border-gray-300 bg-gray-100 text-sm font-medium text-gray-300 cursor-not-allowed">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </span>
                            <?php endif;
                            
                            // Last button
                            if ($current < $total): ?>
                                <a href="?page=<?php echo $total; ?><?php echo $search_param; ?>" 
                                   class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">Last</span>
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10.293 15.707a1 1 0 010-1.414L14.586 10l-4.293-4.293a1 1 0 111.414-1.414l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                        <path fill-rule="evenodd" d="M4.293 15.707a1 1 0 010-1.414L8.586 10 4.293 5.707a1 1 0 011.414-1.414l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </a>
                            <?php else: ?>
                                <span class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-300 cursor-not-allowed">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10.293 15.707a1 1 0 010-1.414L14.586 10l-4.293-4.293a1 1 0 111.414-1.414l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                        <path fill-rule="evenodd" d="M4.293 15.707a1 1 0 010-1.414L8.586 10 4.293 5.707a1 1 0 011.414-1.414l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </span>
                            <?php endif; ?>
                        </nav>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Create Site Modal -->
<div id="createSiteModal" class="modal">
    <div class="modal-content-large">
        <div class="modal-header-fixed">
            <h3 class="modal-title">Add New Site</h3>
            <button type="button" class="modal-close" onclick="closeModal('createSiteModal')">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <form id="createSiteForm" action="create.php" method="POST">
            <div class="modal-body-scrollable">
                <!-- Basic Information Section -->
                <div class="form-section">
                    <h4 class="form-section-title">Basic Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="site_id" class="form-label">Site ID *</label>
                            <input type="text" id="site_id" name="site_id" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label for="store_id" class="form-label">Store ID</label>
                            <input type="text" id="store_id" name="store_id" class="form-input">
                        </div>
                        <div class="form-group md:col-span-2">
                            <label class="form-label">Location & Pincode *</label>
                            <div class="flex gap-2">
                                <input type="text" id="location" name="location" class="form-input" style="width: 70%;" placeholder="Enter location address" required>
                                <input type="text" id="pincode" name="pincode" class="form-input" style="width: 30%;" placeholder="Pincode" maxlength="6" pattern="[0-9]{6}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Location Details Section -->
                <div class="form-section">
                    <h4 class="form-section-title">Location Details</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <div class="form-group">
                            <label for="country_id" class="form-label">Country *</label>
                            <select id="country_id" name="country_id" class="form-select" required onchange="loadStatesForSite(this.value)">
                                <option value="">Select Country</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="state_id" class="form-label">State *</label>
                            <select id="state_id" name="state_id" class="form-select" required onchange="loadCitiesForSite(this.value)">
                                <option value="">Select State</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="city_id" class="form-label">City *</label>
                            <select id="city_id" name="city_id" class="form-select" required>
                                <option value="">Select City</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="zone" class="form-label">Zone</label>
                            <input type="text" id="zone" name="zone" class="form-input" placeholder="e.g. West Zone">
                        </div>
                        <div class="form-group">
                            <label for="branch" class="form-label">Branch</label>
                            <input type="text" id="branch" name="branch" class="form-input">
                        </div>
                    </div>
                </div>

                <!-- Purchase Order Section -->
                <div class="form-section">
                    <h4 class="form-section-title">Purchase Order Details</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="po_number" class="form-label">PO Number</label>
                            <input type="text" id="po_number" name="po_number" class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="po_date" class="form-label">PO Date</label>
                            <input type="date" id="po_date" name="po_date" class="form-input">
                        </div>
                    </div>
                </div>

                <!-- Client Information Section -->
                <div class="form-section">
                    <h4 class="form-section-title">Client Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="customer_id" class="form-label">Customer</label>
                            <select id="customer_id" name="customer_id" class="form-select">
                                <option value="">Select Customer</option>
                            </select>
                        </div>

                    </div>
                </div>

                <!-- Contact Information  -->
                <div class="form-section">
                    <h4 class="form-section-title">Contact Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="form-group">
                            <label for="contact_person_name" class="form-label">Contact Person Name</label>
                            <input type="text" name="contact_person_name" id="contact_person_name" class="form-input" placeholder="Enter contact person name">
                        </div>
                        <div class="form-group">
                            <label for="contact_person_number" class="form-label">Contact Person Number</label>
                            <input type="tel" name="contact_person_number" id="contact_person_number" class="form-input" placeholder="+91-9876543210">
                        </div>
                        <div class="form-group">
                            <label for="contact_person_email" class="form-label">Contact Person Email</label>
                            <input type="email" name="contact_person_email" id="contact_person_email" class="form-input" placeholder="contact@example.com">
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer-fixed">
                <button type="button" onclick="closeModal('createSiteModal')" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Site</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Site Modal -->
<div id="editSiteModal" class="modal">
    <div class="modal-content-large">
        <div class="modal-header-fixed">
            <h3 class="modal-title">Edit Site</h3>
            <button type="button" class="modal-close" onclick="closeModal('editSiteModal')">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <form id="editSiteForm" method="POST">
            <div class="modal-body-scrollable">
                <!-- Same structure as create form but with edit_ prefixed IDs -->
                <!-- Basic Information Section -->
                <div class="form-section">
                    <h4 class="form-section-title">Basic Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="edit_site_id" class="form-label">Site ID *</label>
                            <input type="text" id="edit_site_id" name="site_id" class="form-input" required>
                        </div>
                        
                         <div class="form-group">
                            <label for="edit_site_ticket_id" class="form-label">Site Ticket ID *</label>
                            <input type="text" id="edit_site_ticket_id" name="site_ticket_id" class="form-input"
                                readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_store_id" class="form-label">Store ID</label>
                            <input type="text" id="edit_store_id" name="store_id" class="form-input">
                        </div>
                        <div class="form-group md:col-span-2">
                            <label class="form-label">Location & Pincode *</label>
                            <div class="flex gap-2">
                                <input type="text" id="edit_location" name="location" class="form-input" style="width: 70%;" placeholder="Enter location address" required>
                                <input type="text" id="edit_pincode" name="pincode" class="form-input" style="width: 30%;" placeholder="Pincode" maxlength="6" pattern="[0-9]{6}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Location Details Section -->
                <div class="form-section">
                    <h4 class="form-section-title">Location Details</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <div class="form-group">
                            <label for="edit_country_id" class="form-label">Country *</label>
                            <select id="edit_country_id" name="country_id" class="form-select" required onchange="loadStatesForSite(this.value, 'edit_state_id')">
                                <option value="">Select Country</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_state_id" class="form-label">State *</label>
                            <select id="edit_state_id" name="state_id" class="form-select" required onchange="loadCitiesForSite(this.value, 'edit_city_id')">
                                <option value="">Select State</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_city_id" class="form-label">City *</label>
                            <select id="edit_city_id" name="city_id" class="form-select" required>
                                <option value="">Select City</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_zone" class="form-label">Zone</label>
                            <input type="text" id="edit_zone" name="zone" class="form-input" placeholder="e.g. West Zone">
                        </div>
                        <div class="form-group">
                            <label for="edit_branch" class="form-label">Branch</label>
                            <input type="text" id="edit_branch" name="branch" class="form-input">
                        </div>
                    </div>
                </div>

                <!-- Purchase Order Section -->
                <div class="form-section">
                    <h4 class="form-section-title">Purchase Order Details</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="edit_po_number" class="form-label">PO Number</label>
                            <input type="text" id="edit_po_number" name="po_number" class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="edit_po_date" class="form-label">PO Date</label>
                            <input type="date" id="edit_po_date" name="po_date" class="form-input">
                        </div>
                    </div>
                </div>

                <!-- Client Information Section -->
                <div class="form-section">
                    <h4 class="form-section-title">Client Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="form-group">
                            <label for="edit_customer_id" class="form-label">Customer</label>
                            <select id="edit_customer_id" name="customer_id" class="form-select">
                                <option value="">Select Customer</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_vendor" class="form-label">Vendor</label>
                            <input type="text" id="edit_vendor" name="vendor" class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="edit_delegated_vendor" class="form-label">Delegated Vendor</label>
                            <input type="text" id="edit_delegated_vendor" name="delegated_vendor" class="form-input">
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="form-section">
                    <h4 class="form-section-title">Contact Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="form-group">
                            <label for="edit_contact_person_name" class="form-label">Contact Person Name</label>
                            <input type="text" name="contact_person_name" id="edit_contact_person_name" class="form-input" placeholder="Enter contact person name">
                        </div>
                        <div class="form-group">
                            <label for="edit_contact_person_number" class="form-label">Contact Person Number</label>
                            <input type="tel" name="contact_person_number" id="edit_contact_person_number" class="form-input" placeholder="+91-9876543210">
                        </div>
                        <div class="form-group">
                            <label for="edit_contact_person_email" class="form-label">Contact Person Email</label>
                            <input type="email" name="contact_person_email" id="edit_contact_person_email" class="form-input" placeholder="contact@example.com">
                        </div>
                    </div>
                </div>


                <!-- Status Information Section -->
                <div class="form-section">
                    <h4 class="form-section-title">Status Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="edit_activity_status" class="form-label">Activity Status</label>
                            <select id="edit_activity_status" name="activity_status" class="form-select">
                                <option value="">Select Status</option>
                                <option value="Pending">Pending</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Completed">Completed</option>
                                <option value="On Hold">On Hold</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Progress Flags</label>
                            <div class="space-y-2">
                                <div class="flex items-center">
                                    <input type="checkbox" id="edit_is_delegate" name="is_delegate" class="mr-2">
                                    <label for="edit_is_delegate" class="text-sm">Is Delegated</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" id="edit_survey_status" name="survey_status" class="mr-2">
                                    <label for="edit_survey_status" class="text-sm">Survey Completed</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" id="edit_installation_status" name="installation_status" class="mr-2">
                                    <label for="edit_installation_status" class="text-sm">Installation Done</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" id="edit_is_material_request_generated" name="is_material_request_generated" class="mr-2">
                                    <label for="edit_is_material_request_generated" class="text-sm">Material Request Generated</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Information Section -->
                <div class="form-section">
                    <h4 class="form-section-title">Additional Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="edit_survey_submission_date" class="form-label">Survey Submission Date</label>
                            <input type="datetime-local" id="edit_survey_submission_date" name="survey_submission_date" class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="edit_installation_date" class="form-label">Installation Date</label>
                            <input type="datetime-local" id="edit_installation_date" name="installation_date" class="form-input">
                        </div>
                        <div class="form-group md:col-span-2">
                            <label for="edit_remarks" class="form-label">Remarks</label>
                            <textarea id="edit_remarks" name="remarks" class="form-textarea" rows="3"></textarea>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer-fixed">
                <button type="button" onclick="closeModal('editSiteModal')" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Site</button>
            </div>
        </form>
    </div>
</div>

<!-- View Site Modal -->
<div id="viewSiteModal" class="modal">
    <div class="modal-content-large">
        <div class="modal-header-fixed">
            <h3 class="modal-title">Site Details</h3>
            <button type="button" class="modal-close" onclick="closeModal('viewSiteModal')">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <div class="modal-body-scrollable">
            <!-- Basic Information Section -->
            <div class="form-section">
                <h4 class="form-section-title">Basic Information</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Site ID</label>
                        <p id="view_site_id" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Site Ticket ID</label>
                        <p id="view_site_ticket_id" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Store ID</label>
                        <p id="view_store_id" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                        <p id="view_location" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pincode</label>
                        <p id="view_pincode" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></p>
                    </div>
                </div>
            </div>

            <!-- Location Details Section -->
            <div class="form-section">
                <h4 class="form-section-title">Location Details</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                        <p id="view_city" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                        <p id="view_state" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                        <p id="view_country" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Zone</label>
                        <p id="view_zone" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                        <p id="view_branch" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></p>
                    </div>
                </div>
            </div>

            <!-- Purchase Order Section -->
            <div class="form-section">
                <h4 class="form-section-title">Purchase Order Details</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">PO Number</label>
                        <p id="view_po_number" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">PO Date</label>
                        <p id="view_po_date" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></p>
                    </div>
                </div>
            </div>

            <!-- Client Information Section -->
            <div class="form-section">
                <h4 class="form-section-title">Client Information</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                        <p id="view_customer" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Vendor</label>
                        <p id="view_vendor" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Delegated Vendor</label>
                        <p id="view_delegated_vendor" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></p>
                    </div>
                </div>
            </div>

            <!-- Contact Information Section -->
            <div class="form-section">
                <h4 class="form-section-title">Contact Information</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contact Person Name</label>
                        <p id="view_contact_person_name" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contact Person Number</label>
                        <p id="view_contact_person_number" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contact Person Email</label>
                        <p id="view_contact_person_email" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></p>
                    </div>
                </div>
            </div>


            <!-- Status Information Section -->
            <div class="form-section">
                <h4 class="form-section-title">Status Information</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Activity Status</label>
                        <p id="view_activity_status" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Progress Flags</label>
                        <div id="view_progress_flags" class="text-sm text-gray-900 bg-gray-50 p-2 rounded space-y-1"></div>
                    </div>
                </div>
            </div>

            <!-- Additional Information Section -->
            <div class="form-section">
                <h4 class="form-section-title">Additional Information</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Survey Submission Date</label>
                        <p id="view_survey_submission_date" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Installation Date</label>
                        <p id="view_installation_date" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Created By</label>
                        <p id="view_created_by" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Updated By</label>
                        <p id="view_updated_by" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Created At</label>
                        <p id="view_created_at" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Updated At</label>
                        <p id="view_updated_at" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Remarks</label>
                        <p id="view_remarks" class="text-sm text-gray-900 bg-gray-50 p-2 rounded min-h-[60px]"></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer-fixed">
            <button type="button" onclick="closeModal('viewSiteModal')" class="btn btn-secondary">Close</button>
            <button type="button" onclick="editSiteFromView()" class="btn btn-primary">Edit Site</button>
        </div>
    </div>
</div>


<script>
    
    const BASE_URL = '<?php echo BASE_URL; ?>';
    
    console.log('Sites page script loaded');

    // Test if basic JavaScript is working
    console.log('JavaScript execution test: OK');

    // Load dropdown data when page loads
    document.addEventListener('DOMContentLoaded', function() {
        loadCountriesForSite();
        loadCustomersForSite();
        loadBanksForSite();
    });

    // Load countries for site form
    async function loadCountriesForSite(targetSelectId = 'country_id') {
        try {
            const response = await fetch('../../api/masters.php?path=countries');
            const data = await response.json();

            if (data.success) {
                const select = document.getElementById(targetSelectId);
                if (select) {
                    select.innerHTML = '<option value="">Select Country</option>';
                    data.data.records.forEach(country => {
                        select.innerHTML += `<option value="${country.id}">${country.name}</option>`;
                    });
                }
            }
        } catch (error) {
            console.error('Error loading countries:', error);
        }
    }

    // Load states for site form
    async function loadStatesForSite(countryId, targetSelectId = 'state_id') {
        const stateSelect = document.getElementById(targetSelectId);
        const citySelectId = targetSelectId.replace('state_id', 'city_id');
        const citySelect = document.getElementById(citySelectId);

        if (!stateSelect) return;

        // Clear existing options
        stateSelect.innerHTML = '<option value="">Loading...</option>';
        if (citySelect) {
            citySelect.innerHTML = '<option value="">Select City</option>';
        }

        if (!countryId) {
            stateSelect.innerHTML = '<option value="">Select State</option>';
            return;
        }

        try {
            const response = await fetch(`../../api/states.php?action=getByCountry&country_id=${countryId}`);
            const data = await response.json();

            if (data.success) {
                stateSelect.innerHTML = '<option value="">Select State</option>';
                data.data.forEach(state => {
                    stateSelect.innerHTML += `<option value="${state.id}">${state.name}</option>`;
                });
            } else {
                stateSelect.innerHTML = '<option value="">Error loading states</option>';
            }
        } catch (error) {
            stateSelect.innerHTML = '<option value="">Error loading states</option>';
            console.error('Error loading states:', error);
        }
    }

    // Load cities for site form
    async function loadCitiesForSite(stateId, targetSelectId = 'city_id') {
        const citySelect = document.getElementById(targetSelectId);

        if (!citySelect) return;

        // Clear existing options
        citySelect.innerHTML = '<option value="">Loading...</option>';

        if (!stateId) {
            citySelect.innerHTML = '<option value="">Select City</option>';
            return;
        }

        try {
            const response = await fetch(`../../api/cities.php?action=getByState&state_id=${stateId}`);
            const data = await response.json();

            if (data.success) {
                citySelect.innerHTML = '<option value="">Select City</option>';
                data.data.forEach(city => {
                    citySelect.innerHTML += `<option value="${city.id}">${city.name}</option>`;
                });
            } else {
                citySelect.innerHTML = '<option value="">Error loading cities</option>';
            }
        } catch (error) {
            citySelect.innerHTML = '<option value="">Error loading cities</option>';
            console.error('Error loading cities:', error);
        }
    }

    // Load customers for site form
    async function loadCustomersForSite(targetSelectId = 'customer_id') {
        try {
            const response = await fetch('../../api/masters.php?path=customers');
            const data = await response.json();

            if (data.success) {
                const select = document.getElementById(targetSelectId);
                if (select) {
                    select.innerHTML = '<option value="">Select Customer</option>';
                    data.data.records.forEach(customer => {
                        select.innerHTML += `<option value="${customer.id}">${customer.name}</option>`;
                    });
                }
            }
        } catch (error) {
            console.error('Error loading customers:', error);
        }
    }

    // Load banks for site form
    async function loadBanksForSite(targetSelectId = 'bank_id') {
        try {
            const response = await fetch('../../api/masters.php?path=banks');
            const data = await response.json();

            if (data.success) {
                const select = document.getElementById(targetSelectId);
                if (select) {
                    select.innerHTML = '<option value="">Select Bank</option>';
                    data.data.records.forEach(bank => {
                        select.innerHTML += `<option value="${bank.id}">${bank.name}</option>`;
                    });
                }
            }
        } catch (error) {
            console.error('Error loading banks:', error);
        }
    }

    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
        // Search functionality
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('keyup', debounce(function() {
                applyFilters();
            }, 500));
        }

        // Filter functionality
        const cityFilter = document.getElementById('cityFilter');
        const stateFilter = document.getElementById('stateFilter');
        const statusFilter = document.getElementById('statusFilter');
        const surveyStatusFilter = document.getElementById('surveyStatusFilter');

        if (cityFilter) cityFilter.addEventListener('change', applyFilters);
        if (stateFilter) stateFilter.addEventListener('change', applyFilters);
        if (statusFilter) statusFilter.addEventListener('change', applyFilters);
        if (surveyStatusFilter) surveyStatusFilter.addEventListener('change', applyFilters);
    });

    function applyFilters() {
        try {
            const searchInput = document.getElementById('searchInput');
            const cityFilter = document.getElementById('cityFilter');
            const stateFilter = document.getElementById('stateFilter');
            const statusFilter = document.getElementById('statusFilter');
            const surveyStatusFilter = document.getElementById('surveyStatusFilter');

            const searchTerm = searchInput ? searchInput.value : '';
            const city = cityFilter ? cityFilter.value : '';
            const state = stateFilter ? stateFilter.value : '';
            const status = statusFilter ? statusFilter.value : '';
            const surveyStatus = surveyStatusFilter ? surveyStatusFilter.value : '';

            console.log('Applying filters:', {
                searchTerm,
                city,
                state,
                status,
                surveyStatus
            });

            const url = new URL(window.location);

            if (searchTerm) url.searchParams.set('search', searchTerm);
            else url.searchParams.delete('search');

            if (city) url.searchParams.set('city', city);
            else url.searchParams.delete('city');

            if (state) url.searchParams.set('state', state);
            else url.searchParams.delete('state');

            if (status) url.searchParams.set('activity_status', status);
            else url.searchParams.delete('activity_status');

            if (surveyStatus) url.searchParams.set('survey_status', surveyStatus);
            else url.searchParams.delete('survey_status');

            url.searchParams.delete('page');

            console.log('Redirecting to:', url.toString());
            window.location.href = url.toString();
        } catch (error) {
            console.error('Error in applyFilters:', error);
        }
    }

    // Create site form submission
    document.getElementById('createSiteForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate contact person fields
        const contactNumber = document.getElementById('contact_person_number').value.trim();
        const contactEmail = document.getElementById('contact_person_email').value.trim();
        
        let validationErrors = [];
        
        // Validate phone number if provided
        if (contactNumber) {
            if (!validatePhoneNumberClient(contactNumber)) {
                validationErrors.push('Contact Person Number: Invalid phone number format. Must be 10 digits starting with 6-9');
            }
        }
        
        // Validate email if provided
        if (contactEmail) {
            if (!validateEmailClient(contactEmail)) {
                validationErrors.push('Contact Person Email: Invalid email address format');
            }
        }
        
        // Show validation errors if any
        if (validationErrors.length > 0) {
            showAlert(validationErrors.join('\n'), 'error');
            return;
        }
        
        submitForm('createSiteForm', function(data) {
            closeModal('createSiteModal');
            showAlert('Site created successfully!', 'success');
            setTimeout(() => location.reload(), 1500);
        });
    });

    // Site management functions
    function viewSite(id) {
        fetch(`view.php?id=${id}`)
            .then(response => response.json())
            .then(data => { debugger;
                if (data.success) {
                    const site = data.site;
                    populateViewModal(site);
                    openModal('viewSiteModal');
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Failed to load site data', 'error');
            });
    }

    function populateViewModal(site) {
        document.getElementById('view_site_id').textContent = site.site_id || 'N/A';
         document.getElementById('view_site_ticket_id').textContent = site.site_ticket_id || 'N/A';
        document.getElementById('view_store_id').textContent = site.store_id || 'N/A';
        document.getElementById('view_location').textContent = site.location || 'N/A';
        document.getElementById('view_city').textContent = site.city_name || site.city || 'N/A';
        document.getElementById('view_state').textContent = site.state_name || site.state || 'N/A';
        document.getElementById('view_country').textContent = site.country_name || site.country || 'N/A';
        document.getElementById('view_branch').textContent = site.branch || 'N/A';
        document.getElementById('view_po_number').textContent = site.po_number || 'N/A';
        document.getElementById('view_po_date').textContent = site.po_date || 'N/A';
        document.getElementById('view_customer').textContent = site.customer_name || site.customer || 'N/A';
       // document.getElementById('view_bank').textContent = site.bank_name || site.bank || 'N/A';
        document.getElementById('view_vendor').textContent = site.vendor || 'N/A';
        document.getElementById('view_delegated_vendor').textContent = site.delegated_vendor || 'N/A';
        document.getElementById('view_activity_status').textContent = site.activity_status || 'N/A';
        document.getElementById('view_survey_submission_date').textContent = site.survey_submission_date ? formatDateTime(site.survey_submission_date) : 'N/A';
        document.getElementById('view_installation_date').textContent = site.installation_date ? formatDateTime(site.installation_date) : 'N/A';
        document.getElementById('view_created_by').textContent = site.created_by || 'N/A';
        document.getElementById('view_updated_by').textContent = site.updated_by || 'N/A';
        document.getElementById('view_created_at').textContent = formatDateTime(site.created_at);
        document.getElementById('view_updated_at').textContent = formatDateTime(site.updated_at);
        document.getElementById('view_remarks').textContent = site.remarks || 'No remarks';

        // Progress flags
        const progressFlags = [];
        if (site.is_delegate == 1) progressFlags.push('✓ Delegated');
        if (site.survey_status == 1) progressFlags.push('✓ Survey Completed');
        if (site.installation_status == 1) progressFlags.push('✓ Installation Done');
        if (site.is_material_request_generated == 1) progressFlags.push('✓ Material Request Generated');

        document.getElementById('view_progress_flags').innerHTML = progressFlags.length > 0 ?
            progressFlags.map(flag => `<div>${flag}</div>`).join('') :
            '<div class="text-gray-500">No progress flags set</div>';

        // Store site ID for edit function
        window.currentViewingSiteId = site.id;
    }

    function editSite(id) {
        fetch(`edit.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const site = data.site;
                    populateEditModal(site);
                    document.getElementById('editSiteForm').action = `edit.php?id=${id}`;
                    openModal('editSiteModal');
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Failed to load site data', 'error');
            });
    }

    async function populateEditModal(site) {
        document.getElementById('edit_site_id').value = site.site_id || '';
        document.getElementById('edit_site_ticket_id').value = site.site_ticket_id || '';
        document.getElementById('edit_store_id').value = site.store_id || '';
        document.getElementById('edit_location').value = site.location || '';
        document.getElementById('edit_branch').value = site.branch || '';
        document.getElementById('edit_po_number').value = site.po_number || '';
        document.getElementById('edit_po_date').value = site.po_date || '';
        document.getElementById('edit_activity_status').value = site.activity_status || '';
        document.getElementById('edit_survey_submission_date').value = site.survey_submission_date ? site.survey_submission_date.replace(' ', 'T') : '';
        document.getElementById('edit_installation_date').value = site.installation_date ? site.installation_date.replace(' ', 'T') : '';
        document.getElementById('edit_remarks').value = site.remarks || '';

        // Load dropdowns for edit form
        await loadCountriesForSite('edit_country_id');
        await loadCustomersForSite('edit_customer_id');
        await loadBanksForSite('edit_bank_id');

        // Set selected values after loading dropdowns
        setTimeout(() => {
            if (site.country_id) {
                document.getElementById('edit_country_id').value = site.country_id;
                loadStatesForSite(site.country_id, 'edit_state_id').then(() => {
                    if (site.state_id) {
                        document.getElementById('edit_state_id').value = site.state_id;
                        loadCitiesForSite(site.state_id, 'edit_city_id').then(() => {
                            if (site.city_id) {
                                document.getElementById('edit_city_id').value = site.city_id;
                            }
                        });
                    }
                });
            }

            if (site.customer_id) {
                document.getElementById('edit_customer_id').value = site.customer_id;
            }

            if (site.bank_id) {
                document.getElementById('edit_bank_id').value = site.bank_id;
            }
        }, 500);

        // Set vendor fields
        document.getElementById('edit_vendor').value = site.vendor || '';
        document.getElementById('edit_delegated_vendor').value = site.delegated_vendor || '';

        // Checkboxes
        document.getElementById('edit_is_delegate').checked = site.is_delegate == 1;
        document.getElementById('edit_survey_status').checked = site.survey_status == 1;
        document.getElementById('edit_installation_status').checked = site.installation_status == 1;
        document.getElementById('edit_is_material_request_generated').checked = site.is_material_request_generated == 1;
    }

    function editSiteFromView() {
        if (window.currentViewingSiteId) {
            closeModal('viewSiteModal');
            editSite(window.currentViewingSiteId);
        }
    }

    function viewSurvey(surveyId) {
        // Redirect to the unified survey view page
        window.open(`../../shared/view-survey.php?id=${surveyId}`, '_blank');
    }

    function viewSiteSurvey(surveyId) {
        // Redirect to the unified survey view page
        window.open(`../../shared/view-survey.php?id=${surveyId}`, '_blank');
    }

    function deleteSite(id) {
        // All users (including superadmin) create deletion requests
        showConfirmDialog(
            'Request Site Deletion',
            'This will create a deletion request for superadmin approval. The site will remain active until the request is approved.',
            function() {
                fetch(`delete.php?id=${id}`, {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showAlert(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Failed to submit deletion request', 'error');
                });
            }
        );
    }

    function showConfirmDialog(title, message, onConfirm, onCancel) {
        // Create overlay
        const overlay = document.createElement('div');
        overlay.className = 'fixed inset-0 bg-black bg-opacity-50 z-[9998] flex items-center justify-center';
        overlay.style.animation = 'fadeIn 0.2s ease-out';
        
        // Create dialog
        const dialog = document.createElement('div');
        dialog.className = 'bg-white rounded-lg shadow-xl max-w-md w-full mx-4 transform transition-all';
        dialog.style.animation = 'slideIn 0.3s ease-out';
        dialog.innerHTML = `
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">${title}</h3>
                    </div>
                </div>
                <p class="text-sm text-gray-600 mb-6">${message}</p>
                <div class="flex justify-end space-x-3">
                    <button id="cancelBtn" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        Cancel
                    </button>
                    <button id="confirmBtn" class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                        Delete
                    </button>
                </div>
            </div>
        `;
        
        overlay.appendChild(dialog);
        document.body.appendChild(overlay);
        
        // Add animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            @keyframes slideIn {
                from { transform: scale(0.95) translateY(-10px); opacity: 0; }
                to { transform: scale(1) translateY(0); opacity: 1; }
            }
        `;
        document.head.appendChild(style);
        
        // Handle buttons
        const confirmBtn = dialog.querySelector('#confirmBtn');
        const cancelBtn = dialog.querySelector('#cancelBtn');
        
        const closeDialog = () => {
            overlay.style.animation = 'fadeIn 0.2s ease-out reverse';
            setTimeout(() => {
                document.body.removeChild(overlay);
                document.head.removeChild(style);
            }, 200);
        };
        
        confirmBtn.addEventListener('click', () => {
            closeDialog();
            if (onConfirm) onConfirm();
        });
        
        cancelBtn.addEventListener('click', () => {
            closeDialog();
            if (onCancel) onCancel();
        });
        
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                closeDialog();
                if (onCancel) onCancel();
            }
        });
    }

    // Utility function to format dates
    function formatDateTime(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
    }

    // Clear form when modal is closed
    function clearCreateForm() {
        document.getElementById('createSiteForm').reset();

        // Reset dropdowns to default state
        document.getElementById('country_id').innerHTML = '<option value="">Select Country</option>';
        document.getElementById('state_id').innerHTML = '<option value="">Select State</option>';
        document.getElementById('city_id').innerHTML = '<option value="">Select City</option>';
        document.getElementById('customer_id').innerHTML = '<option value="">Select Customer</option>';
        document.getElementById('bank_id').innerHTML = '<option value="">Select Bank</option>';

        // Reload dropdown data
        loadCountriesForSite();
        loadCustomersForSite();
        loadBanksForSite();
    }

    // File upload handling - wrapped in DOMContentLoaded to ensure elements exist
    document.addEventListener('DOMContentLoaded', function() {

        const fileInput = document.getElementById('excel_file');
        if (!fileInput) {
            console.error('File input not found');
        } else {

            // Simple file validation
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const allowedTypes = ['xlsx', 'xls', 'csv'];
                    const fileExtension = file.name.split('.').pop().toLowerCase();

                    if (!allowedTypes.includes(fileExtension)) {
                        alert('Please select a valid Excel (.xlsx, .xls) or CSV (.csv) file.');
                        this.value = ''; // Clear the input
                        return;
                    }

                    // Check file size (max 10MB)
                    if (file.size > 10 * 1024 * 1024) {
                        alert('File size too large. Maximum size is 10MB.');
                        this.value = ''; // Clear the input
                        return;
                    }

                    console.log('File selected:', file.name);
                }
            });

        } // End else block for fileInput check

        // Bulk upload form submission
        const bulkUploadForm = document.getElementById('bulkUploadForm');
        if (!bulkUploadForm) {
            console.error('Bulk upload form not found');
        } else {

            bulkUploadForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);

                // Check if file is selected
                if (!fileInput.files.length) {
                    alert('Please select a file to upload');
                    return;
                }

                const uploadBtn = document.getElementById('upload-btn');
                const progressDiv = document.getElementById('upload-progress');
                const progressBar = document.getElementById('progress-bar');
                const progressText = document.getElementById('progress-text');
                const resultsDiv = document.getElementById('upload-results');

                // Show progress
                uploadBtn.disabled = true;
                uploadBtn.textContent = 'Uploading...';
                progressDiv.classList.remove('hidden');
                resultsDiv.classList.add('hidden');

                // Simulate progress (since we can't track real progress easily)
                let progress = 0;
                const progressInterval = setInterval(() => {
                    progress += Math.random() * 15;
                    if (progress > 90) progress = 90;
                    progressBar.style.width = progress + '%';
                    progressText.textContent = `Processing... ${Math.round(progress)}%`;
                }, 200);

                fetch('bulk_upload.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        clearInterval(progressInterval);
                        progressBar.style.width = '100%';
                        progressText.textContent = 'Complete!';

                        setTimeout(() => {
                            progressDiv.classList.add('hidden');
                            resultsDiv.classList.remove('hidden');

                            if (data.success) {
                                document.getElementById('success-results').classList.remove('hidden');
                                document.getElementById('error-results').classList.add('hidden');
                                document.getElementById('success-message').textContent = data.message;

                                setTimeout(() => {
                                    closeModal('bulkUploadModal');
                                    location.reload();
                                }, 2000);
                            } else {
                                document.getElementById('success-results').classList.add('hidden');
                                document.getElementById('error-results').classList.remove('hidden');

                                let errorHtml = '<ul class="list-disc list-inside">';
                                if (data.errors && Array.isArray(data.errors)) {
                                    data.errors.forEach(error => {
                                        errorHtml += `<li>${error}</li>`;
                                    });
                                } else {
                                    errorHtml += `<li>${data.message || 'Unknown error occurred'}</li>`;
                                }
                                errorHtml += '</ul>';

                                document.getElementById('error-list').innerHTML = errorHtml;
                            }

                            uploadBtn.disabled = false;
                            uploadBtn.textContent = 'Upload Sites';
                        }, 500);
                    })
                    .catch(error => {
                        clearInterval(progressInterval);
                        progressDiv.classList.add('hidden');
                        resultsDiv.classList.remove('hidden');

                        document.getElementById('success-results').classList.add('hidden');
                        document.getElementById('error-results').classList.remove('hidden');
                        document.getElementById('error-list').innerHTML = `<p>Network error: ${error.message}</p>`;

                        uploadBtn.disabled = false;
                        uploadBtn.textContent = 'Upload Sites';
                    });
            }); // End addEventListener for bulkUploadForm
        } // End else block for bulkUploadForm check

    }); // End bulk upload DOMContentLoaded wrapper


    // Edit site form submission - moved outside DOMContentLoaded to ensure it's available
    document.addEventListener('DOMContentLoaded', function() {
        const editSiteForm = document.getElementById('editSiteForm');
        if (editSiteForm) {
            editSiteForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Validate contact person fields
                const contactNumber = document.getElementById('edit_contact_person_number').value.trim();
                const contactEmail = document.getElementById('edit_contact_person_email').value.trim();
                
                let validationErrors = [];
                
                // Validate phone number if provided
                if (contactNumber) {
                    if (!validatePhoneNumberClient(contactNumber)) {
                        validationErrors.push('Contact Person Number: Invalid phone number format. Must be 10 digits starting with 6-9');
                    }
                }
                
                // Validate email if provided
                if (contactEmail) {
                    if (!validateEmailClient(contactEmail)) {
                        validationErrors.push('Contact Person Email: Invalid email address format');
                    }
                }
                
                // Show validation errors if any
                if (validationErrors.length > 0) {
                    showAlert(validationErrors.join('\n'), 'error');
                    return;
                }
                
                submitForm('editSiteForm', function(data) {
                    closeModal('editSiteModal');
                    showAlert('Site updated successfully!', 'success');
                    setTimeout(() => location.reload(), 1500);
                });
            });
        }
    });

    // Delegation functions
    function delegateSite(siteId) {
        window.location.href = `delegate.php?id=${siteId}`;
    }

    function viewDelegation(siteId) {
        window.location.href = `delegate.php?id=${siteId}`;
    }

    // Download template function
    function downloadTemplate() {
        window.open('download_template.php', '_blank');
    }
    
    function conductSurvey(delegationId) {
        window.location.href = `${BASE_URL}/admin/site-survey2.php?delegation_id=${delegationId}`;
    }

    function conductMaterials(siteId,surveyId) { debugger;
        var url = `${BASE_URL}/admin/material-request.php?site_id=${siteId}&survey_id=${surveyId}`;
        //alert(url);
        window.location.href = `${BASE_URL}/admin/material-request.php?site_id=${siteId}&survey_id=${surveyId}`;
    }

    // Override modal close to clear forms
    const originalCloseModal = window.closeModal;
    window.closeModal = function(modalId) {
        if (modalId === 'createSiteModal') {
            clearCreateForm();
        } else if (modalId === 'bulkUploadModal') {
            // Reset bulk upload form
            document.getElementById('bulkUploadForm').reset();
            document.getElementById('upload-progress').classList.add('hidden');
            document.getElementById('upload-results').classList.add('hidden');
            document.getElementById('success-results').classList.add('hidden');
            document.getElementById('error-results').classList.add('hidden');
        }
        originalCloseModal(modalId);
    };

    // Functions are now properly defined

    // Bulk upload dropdown menu functions
    function toggleBulkUploadMenu() {
        const menu = document.getElementById('bulkUploadMenu');
        menu.classList.toggle('hidden');
    }

    function closeBulkUploadMenu() {
        const menu = document.getElementById('bulkUploadMenu');
        menu.classList.add('hidden');
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const menu = document.getElementById('bulkUploadMenu');
        const button = event.target.closest('.relative');

        if (!button || !button.contains(event.target)) {
            menu.classList.add('hidden');
        }
    });

    // Client-side validation functions
    function validatePhoneNumberClient(phone) {
        if (!phone || phone.trim() === '') {
            return true; // Optional field
        }
        
        // Remove all spaces, hyphens, and parentheses
        const cleanPhone = phone.replace(/[\s\-\(\)]/g, '');
        
        // Remove +91 country code if present
        const withoutCountryCode = cleanPhone.replace(/^\+91/, '');
        
        // Remove leading 0 if present
        const finalPhone = withoutCountryCode.replace(/^0/, '');
        
        // Check if it's exactly 10 digits starting with 6-9
        return /^[6-9][0-9]{9}$/.test(finalPhone);
    }

    function validateEmailClient(email) {
        if (!email || email.trim() === '') {
            return true; // Optional field
        }
        
        // Basic email validation regex
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email) && email.length <= 255;
    }

    // Add real-time validation feedback
    document.addEventListener('DOMContentLoaded', function() {
        // Phone number validation feedback
        const phoneInputs = ['contact_person_number', 'edit_contact_person_number'];
        phoneInputs.forEach(inputId => {
            const input = document.getElementById(inputId);
            if (input) {
                input.addEventListener('blur', function() {
                    const value = this.value.trim();
                    if (value && !validatePhoneNumberClient(value)) {
                        this.classList.add('border-red-500');
                        this.classList.remove('border-gray-300');
                        showFieldError(this, 'Invalid phone number format. Must be 10 digits starting with 6-9');
                    } else {
                        this.classList.remove('border-red-500');
                        this.classList.add('border-gray-300');
                        hideFieldError(this);
                    }
                });
            }
        });

        // Email validation feedback
        const emailInputs = ['contact_person_email', 'edit_contact_person_email'];
        emailInputs.forEach(inputId => {
            const input = document.getElementById(inputId);
            if (input) {
                input.addEventListener('blur', function() {
                    const value = this.value.trim();
                    if (value && !validateEmailClient(value)) {
                        this.classList.add('border-red-500');
                        this.classList.remove('border-gray-300');
                        showFieldError(this, 'Invalid email address format');
                    } else {
                        this.classList.remove('border-red-500');
                        this.classList.add('border-gray-300');
                        hideFieldError(this);
                    }
                });
            }
        });
    });

    function showFieldError(input, message) {
        // Remove existing error message
        hideFieldError(input);
        
        // Create error message element
        const errorDiv = document.createElement('div');
        errorDiv.className = 'text-red-500 text-xs mt-1 field-error';
        errorDiv.textContent = message;
        
        // Insert after the input
        input.parentNode.insertBefore(errorDiv, input.nextSibling);
    }

    function hideFieldError(input) {
        const errorDiv = input.parentNode.querySelector('.field-error');
        if (errorDiv) {
            errorDiv.remove();
        }
    }
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>