<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/SiteSurvey.php';

// Require vendor authentication
Auth::requireVendor();

$vendorId = Auth::getVendorId();
$surveyModel = new SiteSurvey();

// Get all surveys for this vendor
$surveys = $surveyModel->getVendorSurveys($vendorId);


// Categorize surveys by status
$pendingSurveys = array_filter($surveys, fn($s) => $s['survey_status'] === 'pending');
$approvedSurveys = array_filter($surveys, fn($s) => $s['survey_status'] === 'approved');
$rejectedSurveys = array_filter($surveys, fn($s) => $s['survey_status'] === 'rejected');
$completedSurveys = array_filter($surveys, fn($s) => $s['survey_status'] === 'completed');

$title = 'My Surveys';
ob_start();
?>

<!-- Enhanced Header -->
<div class="bg-gradient-to-r from-green-600 to-green-700 rounded-xl shadow-lg p-6 mb-8">
    <div class="flex justify-between items-center">
        <div class="text-white">
            <h1 class="text-3xl font-bold">Site Surveys</h1>
            <p class="mt-2 text-green-100">Professional Survey Management Dashboard</p>
            <p class="text-sm text-green-200 mt-1">View and manage all your submitted site surveys</p>
        </div>
        <div class="flex items-center space-x-4">
            <!-- Stats Cards -->
            <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-4 text-center min-w-24">
                <div class="text-2xl font-bold text-white"><?php echo count($pendingSurveys); ?></div>
                <div class="text-xs text-green-100 uppercase tracking-wide">Pending</div>
            </div>
            <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-4 text-center min-w-24">
                <div class="text-2xl font-bold text-white"><?php echo count($approvedSurveys); ?></div>
                <div class="text-xs text-green-100 uppercase tracking-wide">Approved</div>
            </div>
            <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-4 text-center min-w-24">
                <div class="text-2xl font-bold text-white"><?php echo count($completedSurveys); ?></div>
                <div class="text-xs text-green-100 uppercase tracking-wide">Completed</div>
            </div>
            <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-4 text-center min-w-24">
                <div class="text-2xl font-bold text-white"><?php echo count($surveys); ?></div>
                <div class="text-xs text-green-100 uppercase tracking-wide">Total</div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Filter Tabs -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6 overflow-hidden">
    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
            <svg class="w-5 h-5 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd"></path>
            </svg>
            Filter Surveys
        </h3>
    </div>
    <div class="p-6">
        <nav class="flex space-x-1 bg-gray-100 rounded-lg p-1">
            <button onclick="filterSurveys('all')" id="tab-all" class="tab-button active flex-1 px-4 py-2 text-sm font-medium rounded-md transition-all duration-200">
                <div class="flex items-center justify-center space-x-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" clip-rule="evenodd"></path>
                    </svg>
                    <span>All Surveys</span>
                    <span class="bg-blue-100 text-blue-800 text-xs px-2 py-0.5 rounded-full"><?php echo count($surveys); ?></span>
                </div>
            </button>
            <button onclick="filterSurveys('pending')" id="tab-pending" class="tab-button flex-1 px-4 py-2 text-sm font-medium rounded-md transition-all duration-200">
                <div class="flex items-center justify-center space-x-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                    </svg>
                    <span>Pending</span>
                    <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-0.5 rounded-full"><?php echo count($pendingSurveys); ?></span>
                </div>
            </button>
            <button onclick="filterSurveys('approved')" id="tab-approved" class="tab-button flex-1 px-4 py-2 text-sm font-medium rounded-md transition-all duration-200">
                <div class="flex items-center justify-center space-x-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <span>Approved</span>
                    <span class="bg-green-100 text-green-800 text-xs px-2 py-0.5 rounded-full"><?php echo count($approvedSurveys); ?></span>
                </div>
            </button>
            <button onclick="filterSurveys('completed')" id="tab-completed" class="tab-button flex-1 px-4 py-2 text-sm font-medium rounded-md transition-all duration-200">
                <div class="flex items-center justify-center space-x-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span>Completed</span>
                    <span class="bg-blue-100 text-blue-800 text-xs px-2 py-0.5 rounded-full"><?php echo count($completedSurveys); ?></span>
                </div>
            </button>
        </nav>
    </div>
</div>

<!-- Surveys Table -->
<div class="professional-table bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">Survey History</h3>
            <p class="text-sm text-gray-500 mt-1">Track and manage your site survey submissions</p>
        </div>
    </div>
    <div class="p-6">
        <?php if (empty($surveys)): ?>
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No surveys submitted</h3>
                <p class="mt-1 text-sm text-gray-500">You haven't submitted any site surveys yet.</p>
                <div class="mt-6">
                    <a href="sites/" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm0 2h12v8H4V6z" clip-rule="evenodd"></path>
                        </svg>
                        View Sites
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="surveysTable">
                    <thead class="table-header">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Survey Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Site Information</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submission Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($surveys as $survey): ?>
                        <tr class="survey-row hover:bg-gray-50 transition-colors" data-status="<?php echo $survey['survey_status']; ?>">
                            <!-- Survey Details -->
                            <td class="px-6 py-4">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 h-12 w-12">
                                        <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center shadow-lg">
                                            <svg class="h-6 w-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4 flex-1 min-w-0">
                                        <div class="flex items-center space-x-2 mb-1">
                                            <h4 class="text-sm font-semibold text-gray-900">Survey #<?php echo $survey['id']; ?></h4>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Survey
                                            </span>
                                        </div>
                                        <?php if (!empty($survey['technical_remarks'])): ?>
                                            <div class="text-sm text-gray-600 break-words max-w-xs" title="<?php echo htmlspecialchars($survey['technical_remarks']); ?>">
                                                <?php 
                                                $remarks = $survey['technical_remarks'];
                                                if (strlen($remarks) > 60) {
                                                    echo htmlspecialchars(substr($remarks, 0, 60)) . '...';
                                                } else {
                                                    echo htmlspecialchars($remarks);
                                                }
                                                ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-sm text-gray-400">No remarks provided</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>

                            <!-- Site Information -->
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="h-8 w-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                            <svg class="h-4 w-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($survey['site_code'] ?? $survey['site_id']); ?></div>
                                        <div class="text-xs text-gray-500 break-words max-w-48" title="<?php echo htmlspecialchars($survey['location'] ?? ''); ?>">
                                            <?php 
                                            $location = $survey['location'] ?? '';
                                            if (strlen($location) > 40) {
                                                echo htmlspecialchars(substr($location, 0, 40)) . '...';
                                            } else {
                                                echo htmlspecialchars($location);
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4">
                                <div class="flex flex-col space-y-2">
                                    <?php
                                    $statusConfig = [
                                        'approved' => [
                                            'class' => 'bg-gradient-to-r from-green-100 to-green-200 text-green-800 border border-green-300',
                                            'text' => 'Approved',
                                            'icon' => '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>',
                                            'dot' => 'bg-green-400'
                                        ],
                                        'rejected' => [
                                            'class' => 'bg-gradient-to-r from-red-100 to-red-200 text-red-800 border border-red-300',
                                            'text' => 'Rejected',
                                            'icon' => '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>',
                                            'dot' => 'bg-red-400'
                                        ],
                                        'completed' => [
                                            'class' => 'bg-gradient-to-r from-blue-100 to-blue-200 text-blue-800 border border-blue-300',
                                            'text' => 'Completed',
                                            'icon' => '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>',
                                            'dot' => 'bg-blue-400'
                                        ],
                                        'pending' => [
                                            'class' => 'bg-gradient-to-r from-yellow-100 to-yellow-200 text-yellow-800 border border-yellow-300',
                                            'text' => 'Pending',
                                            'icon' => '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path></svg>',
                                            'dot' => 'bg-yellow-400'
                                        ]
                                    ];
                                    
                                    $status = $survey['survey_status'] ?? 'pending';
                                    $config = $statusConfig[$status] ?? $statusConfig['pending'];
                                    ?>
                                    <div class="flex items-center">
                                        <div class="h-2 w-2 <?php echo $config['dot']; ?> rounded-full mr-2"></div>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold <?php echo $config['class']; ?>">
                                            <?php echo $config['icon'] . $config['text']; ?>
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Submission Date -->
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">
                                    <?php echo $survey['created_at'] ? date('M d, Y', strtotime($survey['created_at'])) : 'N/A'; ?>
                                </div>
                                <div class="text-xs text-gray-500 flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                    </svg>
                                    <?php echo $survey['created_at'] ? date('H:i A', strtotime($survey['created_at'])) : ''; ?>
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <!-- View Survey Button -->
                                    <a href="../shared/view-survey.php?id=<?php echo $survey['id']; ?>" 
                                       class="group relative inline-flex items-center justify-center p-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200" 
                                       title="View Survey">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    </a>
                                    
                                    <?php if (in_array($survey['survey_status'], ['completed', 'approved'])): ?>
                                        <!-- Material Request Button -->
                                        <a href="material-request.php?site_id=<?php echo $survey['site_id']; ?>&survey_id=<?php echo $survey['id']; ?>" 
                                           class="group relative inline-flex items-center justify-center px-4 py-2 border border-green-300 text-sm font-medium rounded-lg text-green-700 bg-green-50 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200" 
                                           title="Generate Material Request">
                                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zM8 6a2 2 0 114 0v1H8V6zM6 9a1 1 0 012 0v1a1 1 0 11-2 0V9zm8 0a1 1 0 012 0v1a1 1 0 11-2 0V9z" clip-rule="evenodd"></path>
                                            </svg>
                                            Materials
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
</div>

<script>
// Tab filtering
function filterSurveys(status) {
    const rows = document.querySelectorAll('.survey-row');
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

// Add CSS for tabs
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
    
    /* Table hover effects */
    .survey-row:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
    
    /* Badge animations */
    .survey-row:hover .inline-flex {
        transform: scale(1.02);
    }
`;
document.head.appendChild(style);
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../includes/vendor_layout.php';
?>