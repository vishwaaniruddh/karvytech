<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/SiteSurvey.php';
require_once __DIR__ . '/../../models/Installation.php';
require_once __DIR__ . '/../../models/Vendor.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$surveyModel = new SiteSurvey();
$installationModel = new Installation();
$vendorModel = new Vendor();

// Pagination settings
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = isset($_GET['per_page']) ? max(10, min(100, intval($_GET['per_page']))) : 25;
$offset = ($page - 1) * $perPage;

// Get all surveys with installation status
$allSurveys = $surveyModel->getAllSurveys();
$totalSurveys = count($allSurveys);
$totalPages = ceil($totalSurveys / $perPage);

// Paginate surveys
$surveys = array_slice($allSurveys, $offset, $perPage);

$activeVendors = $vendorModel->getActiveVendors();

$title = 'Site Surveys Management';
ob_start();
?>

<!-- Header Section -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
        <div class="flex-1">
            <h1 class="text-3xl font-bold text-gray-900">Site Surveys Management</h1>
            <p class="mt-2 text-lg text-gray-600">Review and approve vendor site feasibility surveys</p>
            <p class="text-sm text-gray-500 mt-1">Manage all submitted site surveys from vendors</p>
        </div>
        <div class="mt-6 lg:mt-0 lg:ml-6">
            <div class="flex flex-col items-end gap-3">
                <div class="flex space-x-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                        <?php echo count(array_filter($allSurveys, fn($s) => $s['survey_status'] === 'pending')); ?> Pending
                    </span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        <?php echo count(array_filter($allSurveys, fn($s) => $s['survey_status'] === 'approved')); ?> Approved
                    </span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                        <?php echo count(array_filter($allSurveys, fn($s) => $s['survey_status'] === 'rejected')); ?> Rejected
                    </span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        <?php echo count(array_filter($allSurveys, fn($s) => ($s['installation_status'] ?? 'not_delegated') === 'delegated')); ?> Delegated
                    </span>
                </div>
                <button onclick="exportSurveys()" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                    Export to Excel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Filters Section -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
    <div class="flex flex-wrap items-end gap-4">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">Survey Status</label>
            <select id="filterSurveyStatus" class="form-select w-full">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
        
        <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">Installation Status</label>
            <select id="filterInstallationStatus" class="form-select w-full">
                <option value="">All Statuses</option>
                <option value="not_delegated">Not Delegated</option>
                <option value="delegated">Delegated</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
            </select>
        </div>
        
        <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
            <input type="date" id="filterDateFrom" class="form-input w-full">
        </div>
        
        <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
            <input type="date" id="filterDateTo" class="form-input w-full">
        </div>
        
        <div class="flex gap-2">
            <button onclick="applyFilters()" class="btn btn-primary">
                <svg class="w-4 h-4 mr-2 inline" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd"></path>
                </svg>
                Filter
            </button>
            <button onclick="clearFilters()" class="btn btn-secondary">
                <svg class="w-4 h-4 mr-2 inline" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
                Clear
            </button>
        </div>
    </div>
    
    <!-- Color Legend -->
    <div class="mt-4 pt-4 border-t border-gray-200">
        <p class="text-sm font-medium text-gray-700 mb-2">Undelegated Duration:</p>
        <div class="flex flex-wrap gap-3 text-xs">
            <div class="flex items-center">
                <div class="w-4 h-4 bg-green-100 border border-green-300 rounded mr-2"></div>
                <span class="text-gray-600">0-3 days</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 bg-yellow-100 border border-yellow-300 rounded mr-2"></div>
                <span class="text-gray-600">4-7 days</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 bg-orange-100 border border-orange-300 rounded mr-2"></div>
                <span class="text-gray-600">8-14 days</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 bg-red-100 border border-red-300 rounded mr-2"></div>
                <span class="text-gray-600">15+ days</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 bg-gray-100 border border-gray-300 rounded mr-2"></div>
                <span class="text-gray-600">Delegated/Not Approved</span>
            </div>
        </div>
    </div>
</div>

<!-- Surveys Table -->
<div class="professional-table bg-white">
    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">All Site Surveys</h3>
            <p class="text-sm text-gray-500 mt-1">Showing <?php echo count($surveys); ?> of <?php echo $totalSurveys; ?> surveys (Page <?php echo $page; ?> of <?php echo $totalPages; ?>)</p>
        </div>
        <div class="flex items-center gap-2">
            <label class="text-sm text-gray-600">Per page:</label>
            <select id="perPageSelect" onchange="changePerPage(this.value)" class="form-select text-sm">
                <option value="10" <?php echo $perPage == 10 ? 'selected' : ''; ?>>10</option>
                <option value="25" <?php echo $perPage == 25 ? 'selected' : ''; ?>>25</option>
                <option value="50" <?php echo $perPage == 50 ? 'selected' : ''; ?>>50</option>
                <option value="100" <?php echo $perPage == 100 ? 'selected' : ''; ?>>100</option>
            </select>
        </div>
    </div>
    <div class="p-6">
        <?php if (empty($surveys)): ?>
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No surveys found</h3>
                <p class="mt-1 text-sm text-gray-500">No site surveys have been submitted yet.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="table-header">
                        <tr>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Site Code</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Vendor</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Survey Status</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Installation Status</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Survey Date</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($surveys as $survey): 
                            // Calculate days since approval for undelegated sites
                            $rowClass = '';
                            if ($survey['survey_status'] === 'approved' && ($survey['installation_status'] ?? 'not_delegated') === 'not_delegated') {
                                $approvalDate = new DateTime($survey['updated_at'] ?? $survey['created_at']);
                                $now = new DateTime();
                                $daysSinceApproval = $now->diff($approvalDate)->days;
                                
                                if ($daysSinceApproval <= 3) {
                                    $rowClass = 'bg-green-50 hover:bg-green-100';
                                } elseif ($daysSinceApproval <= 7) {
                                    $rowClass = 'bg-yellow-50 hover:bg-yellow-100';
                                } elseif ($daysSinceApproval <= 14) {
                                    $rowClass = 'bg-orange-50 hover:bg-orange-100';
                                } else {
                                    $rowClass = 'bg-red-50 hover:bg-red-100';
                                }
                            } else {
                                $rowClass = 'hover:bg-gray-50';
                            }
                        ?>
                        <tr class="<?php echo $rowClass; ?> transition-colors" 
                            id="row_<?php echo $survey['id']; ?>"
                            data-survey-status="<?php echo $survey['survey_status']; ?>"
                            data-installation-status="<?php echo $survey['installation_status'] ?? 'not_delegated'; ?>"
                            data-created-date="<?php echo date('Y-m-d', strtotime($survey['created_at'])); ?>">
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                <?php echo $survey['id']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                <div class="font-medium"><?php echo htmlspecialchars($survey['site_code'] ?? 'N/A'); ?></div>
                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars(substr($survey['location'] ?? '', 0, 30)); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                <?php echo htmlspecialchars($survey['vendor_name'] ?? 'Unknown'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <?php
                                $statusClass = '';
                                $statusText = '';
                                switch($survey['survey_status']) {
                                    case 'approved':
                                        $statusClass = 'bg-green-100 text-green-800';
                                        $statusText = 'Approved';
                                        break;
                                    case 'rejected':
                                        $statusClass = 'bg-red-100 text-red-800';
                                        $statusText = 'Rejected';
                                        break;
                                    default:
                                        $statusClass = 'bg-yellow-100 text-yellow-800';
                                        $statusText = 'Pending';
                                }
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $statusClass; ?>" id="status_<?php echo $survey['id']; ?>">
                                    <?php echo $statusText; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <?php
                                $installationStatus = $survey['installation_status'] ?? 'not_delegated';
                                $installationClass = '';
                                $installationText = '';
                                switch($installationStatus) {
                                    case 'delegated':
                                        $installationClass = 'bg-blue-100 text-blue-800';
                                        $installationText = 'Delegated';
                                        break;
                                    case 'in_progress':
                                        $installationClass = 'bg-purple-100 text-purple-800';
                                        $installationText = 'In Progress';
                                        break;
                                    case 'completed':
                                        $installationClass = 'bg-green-100 text-green-800';
                                        $installationText = 'Completed';
                                        break;
                                    default:
                                        $installationClass = 'bg-gray-100 text-gray-800';
                                        $installationText = 'Not Delegated';
                                }
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $installationClass; ?>" id="installation_status_<?php echo $survey['id']; ?>">
                                    <?php echo $installationText; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                <?php echo date('Y-m-d H:i', strtotime($survey['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="../../shared/view-survey.php?id=<?php echo $survey['id']; ?>" class="text-blue-600 hover:text-blue-900 text-xs">View</a>
                                    
                                    <?php if ($survey['survey_status'] === 'pending'): ?>
                                        <select class="form-control form-control-sm action-dropdown" data-id="<?php echo $survey['id']; ?>" style="width:auto; display:inline-block; font-size: 11px;">
                                            <option value="">Survey Action</option>
                                            <option value="approve">Approve</option>
                                            <option value="reject">Reject</option>
                                        </select>
                                    <?php endif; ?>
                                    
                                    <?php if ($survey['survey_status'] === 'approved' && ($survey['installation_status'] ?? 'not_delegated') === 'not_delegated'): ?>
                                        <button onclick="delegateForInstallation(<?php echo $survey['id']; ?>)" class="text-green-600 hover:text-green-900 text-xs">
                                            Delegate Installation
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if (($survey['installation_status'] ?? 'not_delegated') === 'delegated'): ?>
                                        <a href="../installations/view.php?survey_id=<?php echo $survey['id']; ?>" class="text-purple-600 hover:text-purple-900 text-xs">
                                            View Installation
                                        </a>
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
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
        <div class="flex-1 flex justify-between sm:hidden">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>&per_page=<?php echo $perPage; ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Previous
                </a>
            <?php endif; ?>
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>&per_page=<?php echo $perPage; ?>" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Next
                </a>
            <?php endif; ?>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700">
                    Showing <span class="font-medium"><?php echo $offset + 1; ?></span> to <span class="font-medium"><?php echo min($offset + $perPage, $totalSurveys); ?></span> of <span class="font-medium"><?php echo $totalSurveys; ?></span> results
                </p>
            </div>
            <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&per_page=<?php echo $perPage; ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <span class="sr-only">Previous</span>
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </a>
                    <?php endif; ?>
                    
                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    
                    if ($startPage > 1): ?>
                        <a href="?page=1&per_page=<?php echo $perPage; ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">1</a>
                        <?php if ($startPage > 2): ?>
                            <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&per_page=<?php echo $perPage; ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 <?php echo $i == $page ? 'bg-blue-50 border-blue-500 text-blue-600 z-10' : 'bg-white text-gray-700 hover:bg-gray-50'; ?> text-sm font-medium">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?>
                            <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>
                        <?php endif; ?>
                        <a href="?page=<?php echo $totalPages; ?>&per_page=<?php echo $perPage; ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"><?php echo $totalPages; ?></a>
                    <?php endif; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&per_page=<?php echo $perPage; ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <span class="sr-only">Next</span>
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Installation Delegation Modal -->
<div id="installationDelegationModal" class="modal">
    <div class="modal-content max-w-2xl">
        <div class="modal-header">
            <h3 class="modal-title">Delegate for Installation</h3>
            <button type="button" class="modal-close" onclick="closeModal('installationDelegationModal')">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <form id="installationDelegationForm">
            <input type="hidden" id="delegation_survey_id" name="survey_id">
            <div class="modal-body">
                <div id="surveyInfo" class="mb-4 p-3 bg-gray-50 rounded"></div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label for="vendor_id" class="form-label">Select Vendor *</label>
                        <select id="vendor_id" name="vendor_id" class="form-select" required>
                            <option value="">Choose Vendor</option>
                            <?php foreach ($activeVendors as $vendor): ?>
                                <option value="<?php echo $vendor['id']; ?>">
                                    <?php echo htmlspecialchars($vendor['company_name'] ?: $vendor['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="priority" class="form-label">Priority</label>
                        <select id="priority" name="priority" class="form-select">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="expected_start_date" class="form-label">Expected Start Date</label>
                        <input type="date" id="expected_start_date" name="expected_start_date" class="form-input" min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="expected_completion_date" class="form-label">Expected Completion Date</label>
                        <input type="date" id="expected_completion_date" name="expected_completion_date" class="form-input" min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="installation_type" class="form-label">Installation Type</label>
                        <select id="installation_type" name="installation_type" class="form-select">
                            <option value="standard">Standard Installation</option>
                            <option value="complex">Complex Installation</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="upgrade">Upgrade</option>
                        </select>
                    </div>
                    
                    <div class="form-group md:col-span-2">
                        <label for="special_instructions" class="form-label">Special Instructions</label>
                        <textarea id="special_instructions" name="special_instructions" rows="3" class="form-input" placeholder="Any special instructions for the installation team..."></textarea>
                    </div>
                    
                    <div class="form-group md:col-span-2">
                        <label for="delegation_notes" class="form-label">Notes</label>
                        <textarea id="delegation_notes" name="notes" rows="2" class="form-input" placeholder="Additional notes or comments..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('installationDelegationModal')" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Delegate Installation</button>
            </div>
        </form>
    </div>
</div>

<script>
// Pagination
function changePerPage(perPage) {
    window.location.href = `?page=1&per_page=${perPage}`;
}

// Export functionality
function exportSurveys() {
    const surveyStatus = document.getElementById('filterSurveyStatus').value;
    const installationStatus = document.getElementById('filterInstallationStatus').value;
    const dateFrom = document.getElementById('filterDateFrom').value;
    const dateTo = document.getElementById('filterDateTo').value;
    
    const params = new URLSearchParams();
    if (surveyStatus) params.append('survey_status', surveyStatus);
    if (installationStatus) params.append('installation_status', installationStatus);
    if (dateFrom) params.append('date_from', dateFrom);
    if (dateTo) params.append('date_to', dateTo);
    
    window.location.href = `export-surveys.php?${params.toString()}`;
}

// Filter functionality
function applyFilters() {
    const surveyStatus = document.getElementById('filterSurveyStatus').value;
    const installationStatus = document.getElementById('filterInstallationStatus').value;
    const dateFrom = document.getElementById('filterDateFrom').value;
    const dateTo = document.getElementById('filterDateTo').value;
    
    const rows = document.querySelectorAll('tbody tr[data-survey-status]');
    let visibleCount = 0;
    
    rows.forEach(row => {
        let show = true;
        
        // Filter by survey status
        if (surveyStatus && row.dataset.surveyStatus !== surveyStatus) {
            show = false;
        }
        
        // Filter by installation status
        if (installationStatus && row.dataset.installationStatus !== installationStatus) {
            show = false;
        }
        
        // Filter by date range
        const rowDate = row.dataset.createdDate;
        if (dateFrom && rowDate < dateFrom) {
            show = false;
        }
        if (dateTo && rowDate > dateTo) {
            show = false;
        }
        
        row.style.display = show ? '' : 'none';
        if (show) visibleCount++;
    });
    
    // Show message if no results
    const tbody = document.querySelector('tbody');
    let noResultsRow = document.getElementById('noResultsRow');
    
    if (visibleCount === 0) {
        if (!noResultsRow) {
            noResultsRow = document.createElement('tr');
            noResultsRow.id = 'noResultsRow';
            noResultsRow.innerHTML = `
                <td colspan="7" class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No results found</h3>
                    <p class="mt-1 text-sm text-gray-500">Try adjusting your filters</p>
                </td>
            `;
            tbody.appendChild(noResultsRow);
        }
    } else if (noResultsRow) {
        noResultsRow.remove();
    }
    
    showAlert(`Showing ${visibleCount} of ${rows.length} surveys`, 'success');
}

function clearFilters() {
    document.getElementById('filterSurveyStatus').value = '';
    document.getElementById('filterInstallationStatus').value = '';
    document.getElementById('filterDateFrom').value = '';
    document.getElementById('filterDateTo').value = '';
    
    const rows = document.querySelectorAll('tbody tr[data-survey-status]');
    rows.forEach(row => {
        row.style.display = '';
    });
    
    const noResultsRow = document.getElementById('noResultsRow');
    if (noResultsRow) {
        noResultsRow.remove();
    }
    
    showAlert('Filters cleared', 'success');
}

// Handle approval/rejection and installation delegation
document.addEventListener('DOMContentLoaded', function() {
    const dropdowns = document.querySelectorAll('.action-dropdown');
    
    dropdowns.forEach(dropdown => {
        dropdown.addEventListener('change', function() {
            const surveyId = this.dataset.id;
            const action = this.value;
            
            if (action && surveyId) {
                const remarks = prompt(`Please enter remarks for ${action}ing this survey:`);
                if (remarks !== null) {
                    updateSurveyStatus(surveyId, action, remarks);
                }
                this.value = ''; // Reset dropdown
            }
        });
    });
});

function updateSurveyStatus(surveyId, action, remarks) {
    const formData = new FormData();
    formData.append('survey_id', surveyId);
    formData.append('action', action);
    formData.append('remarks', remarks);
    
    fetch('process-survey-action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update status badge
            const statusBadge = document.getElementById(`status_${surveyId}`);
            if (statusBadge) {
                if (action === 'approve') {
                    statusBadge.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800';
                    statusBadge.textContent = 'Approved';
                } else if (action === 'reject') {
                    statusBadge.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800';
                    statusBadge.textContent = 'Rejected';
                }
            }
            
            // Remove dropdown and add delegation button if approved
            const actionsCell = document.querySelector(`[data-id="${surveyId}"]`).closest('td');
            if (action === 'approve') {
                const delegateBtn = document.createElement('button');
                delegateBtn.onclick = () => delegateForInstallation(surveyId);
                delegateBtn.className = 'text-green-600 hover:text-green-900 text-xs ml-2';
                delegateBtn.textContent = 'Delegate Installation';
                actionsCell.appendChild(delegateBtn);
            }
            
            // Remove dropdown
            const dropdown = document.querySelector(`[data-id="${surveyId}"]`);
            if (dropdown) {
                dropdown.style.display = 'none';
            }
            
            showAlert(data.message, 'success');
        } else {
            showAlert(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred. Please try again.', 'error');
    });
}

function delegateForInstallation(surveyId) {
    // Fetch survey details
    fetch(`get-survey-details.php?id=${surveyId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const survey = data.survey;
                
                // Populate survey info
                document.getElementById('delegation_survey_id').value = surveyId;
                document.getElementById('surveyInfo').innerHTML = `
                    <h5 class="font-medium text-gray-900">Survey Details</h5>
                    <div class="grid grid-cols-2 gap-4 mt-2 text-sm">
                        <div><span class="text-gray-500">Site:</span> ${survey.site_code}</div>
                        <div><span class="text-gray-500">Location:</span> ${survey.location || 'N/A'}</div>
                        <div><span class="text-gray-500">Survey Vendor:</span> ${survey.vendor_name}</div>
                        <div><span class="text-gray-500">Survey Date:</span> ${new Date(survey.created_at).toLocaleDateString()}</div>
                    </div>
                `;
                
                openModal('installationDelegationModal');
            } else {
                showAlert(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Failed to load survey details', 'error');
        });
}

// Installation delegation form submission
document.getElementById('installationDelegationForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Processing...';
    
    fetch('process-installation-delegation.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            closeModal('installationDelegationModal');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while delegating installation.', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>