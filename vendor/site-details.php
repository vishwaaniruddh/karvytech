<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/Site.php';
require_once __DIR__ . '/../models/SiteDelegation.php';

// Require vendor authentication
Auth::requireVendor();

$siteId = $_GET['id'] ?? null;
if (!$siteId) {
    header('Location: index.php');
    exit;
}

$vendorId = Auth::getVendorId();
$siteModel = new Site();
$delegationModel = new SiteDelegation();

// Get site details
$site = $siteModel->findWithRelations($siteId);
if (!$site) {
    header('Location: index.php');
    exit;
}

// Get survey status if exists
require_once __DIR__ . '/../models/SiteSurvey.php';
$surveyModel = new SiteSurvey();
$survey = $surveyModel->findBySiteAndVendor($siteId, $vendorId);
if ($survey) {
    $site['actual_survey_status'] = $survey['survey_status'];
    $site['survey_submitted_date'] = $survey['submitted_date'];
}

// Verify this site is delegated to current vendor
$delegation = $delegationModel->getActiveDelegation($siteId);
if (!$delegation || $delegation['vendor_id'] != $vendorId) {
    header('Location: index.php');
    exit;
}

$title = 'Site Details - ' . $site['site_id'];
ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Site Details</h1>
        <p class="mt-2 text-sm text-gray-700">Installation site information and progress</p>
    </div>
    <div class="flex space-x-2">
        <a href="<?php echo BASE_URL; ?>/vendor/sites/" class="btn btn-secondary">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
            </svg>
            Back to Dashboard
        </a>
        <button onclick="updateProgress(<?php echo $delegation['id']; ?>)" class="btn btn-primary">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
            </svg>
            Update Progress
        </button>
    </div>
</div>

<!-- Delegation Status Card -->
<div class="card delegation-card mb-6">
    <div class="card-header">
        <h3 class="card-title">Delegation Information</h3>
        <span class="badge badge-warning">Active</span>
    </div>
    <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Delegated Date</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo date('M d, Y H:i', strtotime($delegation['delegation_date'])); ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Delegated By</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo htmlspecialchars($delegation['delegated_by_name']); ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <span class="badge badge-warning">Active</span>
            </div>
            <?php if ($delegation['notes']): ?>
            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Special Instructions</label>
                <div class="delegation-info">
                    <?php echo htmlspecialchars($delegation['notes']); ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Site Information -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Basic Information -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Basic Information</h3>
        </div>
        <div class="card-body">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Site ID</label>
                    <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded font-mono"><?php echo htmlspecialchars($site['site_id']); ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Store ID</label>
                    <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo htmlspecialchars($site['store_id'] ?: 'N/A'); ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                    <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo htmlspecialchars($site['location']); ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                    <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo htmlspecialchars($site['branch'] ?: 'N/A'); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Location Details -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Location Details</h3>
        </div>
        <div class="card-body">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                    <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo htmlspecialchars($site['city_name'] ?: 'N/A'); ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                    <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo htmlspecialchars($site['state_name'] ?: 'N/A'); ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                    <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo htmlspecialchars($site['country_name'] ?: 'N/A'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Client Information -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Client Information</h3>
        </div>
        <div class="card-body">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                    <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo htmlspecialchars($site['customer_name'] ?: 'N/A'); ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bank</label>
                    <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo htmlspecialchars($site['bank_name'] ?: 'N/A'); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Purchase Order -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Purchase Order</h3>
        </div>
        <div class="card-body">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">PO Number</label>
                    <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo htmlspecialchars($site['po_number'] ?: 'N/A'); ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">PO Date</label>
                    <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo $site['po_date'] ? date('M d, Y', strtotime($site['po_date'])) : 'N/A'; ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Installation Progress -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Installation Progress</h3>
    </div>
    <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Survey Status</label>
                <div class="flex items-center space-x-2">
                    <?php 
                    $actualStatus = $site['actual_survey_status'] ?? 'pending';
                    $statusClasses = [
                        'pending' => 'badge-warning',
                        'submitted' => 'badge-info', 
                        'approved' => 'badge-success',
                        'rejected' => 'badge-danger'
                    ];
                    $badgeClass = $statusClasses[$actualStatus] ?? 'badge-secondary';
                    ?>
                    <span class="badge <?php echo $badgeClass; ?>">
                        <?php echo ucfirst($actualStatus); ?>
                    </span>
                    <?php if ($site['survey_submitted_date']): ?>
                        <span class="text-xs text-gray-500">on <?php echo date('M d, Y', strtotime($site['survey_submitted_date'])); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Installation Status</label>
                <div class="flex items-center space-x-2">
                    <span class="badge <?php echo $site['installation_status'] ? 'badge-success' : 'badge-warning'; ?>">
                        <?php echo $site['installation_status'] ? 'Completed' : 'Pending'; ?>
                    </span>
                    <?php if ($site['installation_date']): ?>
                        <span class="text-xs text-gray-500">on <?php echo date('M d, Y', strtotime($site['installation_date'])); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Activity Status</label>
                <span class="badge badge-info"><?php echo htmlspecialchars($site['activity_status'] ?: 'No Status'); ?></span>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Material Request</label>
                <span class="badge <?php echo $site['is_material_request_generated'] ? 'badge-success' : 'badge-secondary'; ?>">
                    <?php echo $site['is_material_request_generated'] ? 'Generated' : 'Not Generated'; ?>
                </span>
            </div>
        </div>
        
        <?php if ($site['remarks']): ?>
        <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Remarks</label>
            <div class="text-sm text-gray-900 bg-gray-50 p-3 rounded">
                <?php echo htmlspecialchars($site['remarks']); ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function updateProgress(delegationId) {
    window.location.href = `<?php echo BASE_URL; ?>/vendor/update-progress.php?id=${delegationId}`;
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../includes/vendor_layout.php';
?>