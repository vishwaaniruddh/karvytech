<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/SiteSurvey.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$surveyId = $_GET['id'] ?? null;
if (!$surveyId) {
    header('Location: index.php');
    exit;
}

$surveyModel = new SiteSurvey();
$survey = $surveyModel->findWithDetails($surveyId);

if (!$survey) {
    header('Location: index.php');
    exit;
}

$title = 'Survey Details - ' . ($survey['site_code'] ?? 'Survey #' . $surveyId);
ob_start();
?>

<!-- Header Section -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
        <div class="flex-1">
            <h1 class="text-3xl font-bold text-gray-900">Survey Details</h1>
            <p class="mt-2 text-lg text-gray-600">Survey ID: <span class="font-semibold text-blue-600">#<?php echo $survey['id']; ?></span></p>
            <p class="text-sm text-gray-500 mt-1">Detailed view of site feasibility survey</p>
        </div>
        <div class="mt-6 lg:mt-0 lg:ml-6">
            <a href="index.php" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                </svg>
                Back to Surveys
            </a>
        </div>
    </div>
</div>

<!-- Survey Information -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- Basic Information -->
    <div class="professional-table bg-white">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Basic Information</h3>
        </div>
        <div class="p-6">
            <dl class="space-y-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Survey ID</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?php echo $survey['id']; ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">ATM ID</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($survey['site_code'] ?? 'N/A'); ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Engineer/Vendor</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($survey['vendor_name'] ?? 'Unknown'); ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Survey Date</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?php echo date('M d, Y H:i', strtotime($survey['created_at'])); ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                    <dd class="mt-1">
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
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $statusClass; ?>">
                            <?php echo $statusText; ?>
                        </span>
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Site Information -->
    <div class="professional-table bg-white">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Site Information</h3>
        </div>
        <div class="p-6">
            <dl class="space-y-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Location</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($survey['location'] ?? 'N/A'); ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">City</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($survey['city_name'] ?? 'N/A'); ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">State</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($survey['state_name'] ?? 'N/A'); ?></dd>
                </div>
            </dl>
        </div>
    </div>
</div>

<!-- Survey Details -->
<div class="professional-table bg-white mb-8">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Survey Remarks</h3>
    </div>
    <div class="p-6">
        <div class="bg-gray-50 rounded-lg p-4">
            <p class="text-sm text-gray-900">
                <?php echo nl2br(htmlspecialchars($survey['technical_remarks'] ?? 'No remarks provided')); ?>
            </p>
        </div>
    </div>
</div>

<!-- Approval Section -->
<?php if ($survey['survey_status'] === 'pending'): ?>
<div class="professional-table bg-white">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Survey Approval</h3>
    </div>
    <div class="p-6">
        <div class="flex space-x-4">
            <button onclick="approveSurvey(<?php echo $survey['id']; ?>)" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
                Approve Survey
            </button>
            <button onclick="rejectSurvey(<?php echo $survey['id']; ?>)" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
                Reject Survey
            </button>
        </div>
    </div>
</div>
<?php elseif ($survey['approved_by']): ?>
<div class="professional-table bg-white">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Approval Details</h3>
    </div>
    <div class="p-6">
        <dl class="space-y-4">
            <div>
                <dt class="text-sm font-medium text-gray-500">Approved/Rejected By</dt>
                <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($survey['approved_by_name'] ?? 'Unknown'); ?></dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Date</dt>
                <dd class="mt-1 text-sm text-gray-900"><?php echo date('M d, Y H:i', strtotime($survey['approved_date'])); ?></dd>
            </div>
            <?php if ($survey['approval_remarks']): ?>
            <div>
                <dt class="text-sm font-medium text-gray-500">Remarks</dt>
                <dd class="mt-1 text-sm text-gray-900"><?php echo nl2br(htmlspecialchars($survey['approval_remarks'])); ?></dd>
            </div>
            <?php endif; ?>
        </dl>
    </div>
</div>
<?php endif; ?>

<script>
function approveSurvey(surveyId) {
    const remarks = prompt('Please enter approval remarks (optional):');
    if (remarks !== null) {
        updateSurveyStatus(surveyId, 'approve', remarks);
    }
}

function rejectSurvey(surveyId) {
    const remarks = prompt('Please enter rejection reason:');
    if (remarks !== null && remarks.trim() !== '') {
        updateSurveyStatus(surveyId, 'reject', remarks);
    } else if (remarks !== null) {
        alert('Rejection reason is required');
    }
}

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
            showAlert(data.message, 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred. Please try again.', 'error');
    });
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>