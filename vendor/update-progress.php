<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/Site.php';
require_once __DIR__ . '/../models/SiteDelegation.php';

// Require vendor authentication
Auth::requireVendor();

$delegationId = $_GET['id'] ?? null;
if (!$delegationId) {
    header('Location: index.php');
    exit;
}

$vendorId = Auth::getVendorId();
$siteModel = new Site();
$delegationModel = new SiteDelegation();

// Get delegation details
$delegation = $delegationModel->find($delegationId);
if (!$delegation || $delegation['vendor_id'] != $vendorId) {
    header('Location: index.php');
    exit;
}

// Get site details
$site = $siteModel->findWithRelations($delegation['site_id']);
if (!$site) {
    header('Location: index.php');
    exit;
}

$title = 'Update Progress - ' . $site['site_id'];
ob_start();
?>

<!-- Header Section -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
        <div class="flex-1">
            <h1 class="text-3xl font-bold text-gray-900">Update Installation Progress</h1>
            <p class="mt-2 text-lg text-gray-600">Site: <span class="font-semibold text-blue-600"><?php echo htmlspecialchars($site['site_id']); ?></span></p>
            <p class="text-sm text-gray-500 mt-1">Update the current status and progress of your installation work</p>
        </div>
        <div class="mt-6 lg:mt-0 lg:ml-6">
            <a href="site-details.php?id=<?php echo $site['id']; ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                </svg>
                Back to Site Details
            </a>
        </div>
    </div>
</div>

<!-- Site Summary -->
<div class="professional-table bg-white mb-8">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Site Summary</h3>
        <p class="text-sm text-gray-500 mt-1">Basic information about this installation site</p>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Site ID</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo htmlspecialchars($site['site_id']); ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo htmlspecialchars($site['location']); ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo htmlspecialchars($site['city_name'] ?: 'N/A'); ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo htmlspecialchars($site['customer_name'] ?: 'N/A'); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Progress Update Form -->
<div class="professional-table bg-white">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Update Installation Progress</h3>
        <p class="text-sm text-gray-500 mt-1">Update the current status and progress of your installation work</p>
    </div>
    <div class="p-6">
        <form id="progressForm" action="process-progress.php" method="POST">
            <input type="hidden" name="site_id" value="<?php echo $site['id']; ?>">
            <input type="hidden" name="delegation_id" value="<?php echo $delegationId; ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Survey Status -->
                <div class="form-group">
                    <label class="form-label">Survey Status</label>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <input type="radio" id="survey_pending" name="survey_status" value="0" <?php echo !$site['survey_status'] ? 'checked' : ''; ?> class="mr-2">
                            <label for="survey_pending" class="text-sm">Pending</label>
                        </div>
                        <div class="flex items-center">
                            <input type="radio" id="survey_completed" name="survey_status" value="1" <?php echo $site['survey_status'] ? 'checked' : ''; ?> class="mr-2">
                            <label for="survey_completed" class="text-sm">Completed</label>
                        </div>
                    </div>
                </div>

                <!-- Survey Date -->
                <div class="form-group">
                    <label for="survey_date" class="form-label">Survey Completion Date</label>
                    <input type="datetime-local" id="survey_date" name="survey_submission_date" class="form-input" 
                           value="<?php echo $site['survey_submission_date'] ? date('Y-m-d\TH:i', strtotime($site['survey_submission_date'])) : ''; ?>">
                </div>

                <!-- Installation Status -->
                <div class="form-group">
                    <label class="form-label">Installation Status</label>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <input type="radio" id="install_pending" name="installation_status" value="0" <?php echo !$site['installation_status'] ? 'checked' : ''; ?> class="mr-2">
                            <label for="install_pending" class="text-sm">Pending</label>
                        </div>
                        <div class="flex items-center">
                            <input type="radio" id="install_completed" name="installation_status" value="1" <?php echo $site['installation_status'] ? 'checked' : ''; ?> class="mr-2">
                            <label for="install_completed" class="text-sm">Completed</label>
                        </div>
                    </div>
                </div>

                <!-- Installation Date -->
                <div class="form-group">
                    <label for="installation_date" class="form-label">Installation Completion Date</label>
                    <input type="datetime-local" id="installation_date" name="installation_date" class="form-input"
                           value="<?php echo $site['installation_date'] ? date('Y-m-d\TH:i', strtotime($site['installation_date'])) : ''; ?>">
                </div>

                <!-- Activity Status -->
                <div class="form-group">
                    <label for="activity_status" class="form-label">Activity Status</label>
                    <select id="activity_status" name="activity_status" class="form-select">
                        <option value="">Select Status</option>
                        <option value="Pending" <?php echo $site['activity_status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="In Progress" <?php echo $site['activity_status'] === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="Survey Completed" <?php echo $site['activity_status'] === 'Survey Completed' ? 'selected' : ''; ?>>Survey Completed</option>
                        <option value="Installation In Progress" <?php echo $site['activity_status'] === 'Installation In Progress' ? 'selected' : ''; ?>>Installation In Progress</option>
                        <option value="Installation Completed" <?php echo $site['activity_status'] === 'Installation Completed' ? 'selected' : ''; ?>>Installation Completed</option>
                        <option value="Testing" <?php echo $site['activity_status'] === 'Testing' ? 'selected' : ''; ?>>Testing</option>
                        <option value="Completed" <?php echo $site['activity_status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="On Hold" <?php echo $site['activity_status'] === 'On Hold' ? 'selected' : ''; ?>>On Hold</option>
                    </select>
                </div>

                <!-- Material Request -->
                <div class="form-group">
                    <label class="form-label">Material Request</label>
                    <div class="flex items-center">
                        <input type="checkbox" id="material_request" name="is_material_request_generated" value="1" 
                               <?php echo $site['is_material_request_generated'] ? 'checked' : ''; ?> class="mr-2">
                        <label for="material_request" class="text-sm">Material request generated</label>
                    </div>
                </div>
            </div>

            <!-- Progress Notes -->
            <div class="mt-6">
                <label for="progress_notes" class="form-label">Progress Notes</label>
                <textarea id="progress_notes" name="remarks" class="form-textarea" rows="4" 
                          placeholder="Add any notes about the installation progress, issues encountered, or next steps..."><?php echo htmlspecialchars($site['remarks'] ?: ''); ?></textarea>
            </div>

            <!-- Information Note -->
            <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-md">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-blue-400 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <h4 class="text-sm font-medium text-blue-800">Note about Delegation Completion</h4>
                        <p class="text-xs text-blue-600 mt-1">
                            Only administrators can mark delegations as complete. Update your progress here and the admin will review and close the delegation when appropriate.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="mt-6 flex justify-end space-x-2">
                <a href="site-details.php?id=<?php echo $site['id']; ?>" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    Update Progress
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Form submission
document.getElementById('progressForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner mr-2"></span>Updating...';
    
    fetch('process-progress.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            if (data.delegation_completed) {
                setTimeout(() => window.location.href = 'index.php', 2000);
            } else {
                setTimeout(() => window.location.href = `site-details.php?id=${<?php echo $site['id']; ?>}`, 1500);
            }
        } else {
            showAlert(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred. Please try again.', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

// Auto-enable date fields when status is completed
document.getElementById('survey_completed').addEventListener('change', function() {
    const dateField = document.getElementById('survey_date');
    if (this.checked && !dateField.value) {
        dateField.value = new Date().toISOString().slice(0, 16);
    }
});

document.getElementById('install_completed').addEventListener('change', function() {
    const dateField = document.getElementById('installation_date');
    if (this.checked && !dateField.value) {
        dateField.value = new Date().toISOString().slice(0, 16);
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../includes/vendor_layout.php';
?>