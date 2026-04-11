<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/SiteSurvey.php';
require_once __DIR__ . '/../models/SiteDelegation.php';

// Require vendor authentication
Auth::requireVendor();

$surveyId = $_GET['id'] ?? null;
if (!$surveyId) {
    header('Location: surveys.php');
    exit;
}

$vendorId = Auth::getVendorId();
$surveyModel = new SiteSurvey();

// Get survey details with site information
$survey = $surveyModel->findWithDetails($surveyId);

// Check if survey exists
if (!$survey) {
    header('Location: surveys.php');
    exit;
}

// For vendor access, check if the survey belongs to a site delegated to this vendor
// The survey.vendor_id is actually the user_id, so we need to check delegation
$delegationModel = new SiteDelegation();
$delegations = $delegationModel->getVendorDelegations($vendorId);
$hasAccess = false;

foreach ($delegations as $delegation) {
    if ($delegation['site_id'] == $survey['site_id']) {
        $hasAccess = true;
        break;
    }
}

if (!$hasAccess) {
    header('Location: surveys.php');
    exit;
}

$title = 'Survey Details - ' . ($survey['site_code'] ?? $survey['site_id'] ?? 'Unknown Site');
ob_start();
?>

<!-- Header Section -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
        <div class="flex-1">
            <h1 class="text-3xl font-bold text-gray-900">Survey Details</h1>
            <p class="mt-2 text-lg text-gray-600">Site: <span class="font-semibold text-blue-600"><?php echo htmlspecialchars($survey['site_code'] ?? $survey['site_id'] ?? 'Unknown'); ?></span></p>
            <p class="text-sm text-gray-500 mt-1">Detailed view of site feasibility assessment</p>
        </div>
        <div class="mt-6 lg:mt-0 lg:ml-6">
            <div class="flex space-x-3">
                <?php
                $statusClasses = [
                    'pending' => 'bg-yellow-100 text-yellow-800',
                    'submitted' => 'bg-blue-100 text-blue-800',
                    'completed' => 'bg-blue-100 text-blue-800',
                    'approved' => 'bg-green-100 text-green-800',
                    'rejected' => 'bg-red-100 text-red-800'
                ];
                $statusClass = $statusClasses[$survey['survey_status']] ?? 'bg-gray-100 text-gray-800';
                ?>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?php echo $statusClass; ?>">
                    <?php echo ucfirst($survey['survey_status']); ?>
                </span>
                <a href="surveys.php" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                    </svg>
                    Back to Surveys
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Site Information -->
<div class="professional-table bg-white mb-8">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Site Information</h3>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Site ID</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-3 rounded-md"><?php echo htmlspecialchars($survey['site_code'] ?? $survey['site_id'] ?? 'N/A'); ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-3 rounded-md"><?php echo htmlspecialchars($survey['location'] ?? 'N/A'); ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-3 rounded-md"><?php echo htmlspecialchars($survey['city_name'] ?: 'N/A'); ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Survey Date</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-3 rounded-md">
                    <?php echo $survey['survey_date'] ? date('M d, Y H:i', strtotime($survey['survey_date'])) : 'N/A'; ?>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Site Assessment -->
<div class="professional-table bg-white mb-8">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Site Assessment</h3>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Site Accessibility</label>
                <div class="flex items-center">
                    <?php
                    $accessibilityColors = ['good' => 'green', 'moderate' => 'yellow', 'poor' => 'red'];
                    $color = $accessibilityColors[$survey['site_accessibility'] ?? ''] ?? 'gray';
                    ?>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-<?php echo $color; ?>-100 text-<?php echo $color; ?>-800">
                        <?php echo ucfirst($survey['site_accessibility'] ?: 'Not specified'); ?>
                    </span>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Power Availability</label>
                <div class="flex items-center">
                    <?php
                    $powerColors = ['available' => 'green', 'partial' => 'yellow', 'unavailable' => 'red'];
                    $color = $powerColors[$survey['power_availability'] ?? ''] ?? 'gray';
                    ?>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-<?php echo $color; ?>-100 text-<?php echo $color; ?>-800">
                        <?php echo ucfirst($survey['power_availability'] ?: 'Not specified'); ?>
                    </span>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Network Connectivity</label>
                <div class="flex items-center">
                    <?php
                    $networkColors = ['excellent' => 'green', 'good' => 'green', 'poor' => 'yellow', 'none' => 'red'];
                    $color = $networkColors[$survey['network_connectivity'] ?? ''] ?? 'gray';
                    ?>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-<?php echo $color; ?>-100 text-<?php echo $color; ?>-800">
                        <?php echo ucfirst($survey['network_connectivity'] ?: 'Not specified'); ?>
                    </span>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Space Adequacy</label>
                <div class="flex items-center">
                    <?php
                    $spaceColors = ['adequate' => 'green', 'tight' => 'yellow', 'inadequate' => 'red'];
                    $color = $spaceColors[$survey['space_adequacy'] ?? ''] ?? 'gray';
                    ?>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-<?php echo $color; ?>-100 text-<?php echo $color; ?>-800">
                        <?php echo ucfirst($survey['space_adequacy'] ?: 'Not specified'); ?>
                    </span>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Security Level</label>
                <div class="flex items-center">
                    <?php
                    $securityColors = ['high' => 'green', 'medium' => 'yellow', 'low' => 'red'];
                    $color = $securityColors[$survey['security_level'] ?? ''] ?? 'gray';
                    ?>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-<?php echo $color; ?>-100 text-<?php echo $color; ?>-800">
                        <?php echo ucfirst($survey['security_level'] ?? 'Not specified'); ?>
                    </span>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Estimated Completion</label>
                <div class="flex items-center">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        <?php echo $survey['estimated_completion_days'] ? $survey['estimated_completion_days'] . ' days' : 'Not specified'; ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Work Requirements -->
<div class="professional-table bg-white mb-8">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Work Requirements</h3>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2 <?php echo ($survey['electrical_work_required'] ?? false) ? 'text-green-500' : 'text-gray-400'; ?>" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-sm <?php echo ($survey['electrical_work_required'] ?? false) ? 'text-gray-900 font-medium' : 'text-gray-500'; ?>">
                    Electrical work required
                </span>
            </div>
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2 <?php echo ($survey['civil_work_required'] ?? false) ? 'text-green-500' : 'text-gray-400'; ?>" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-sm <?php echo ($survey['civil_work_required'] ?? false) ? 'text-gray-900 font-medium' : 'text-gray-500'; ?>">
                    Civil work required
                </span>
            </div>
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2 <?php echo ($survey['network_work_required'] ?? false) ? 'text-green-500' : 'text-gray-400'; ?>" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-sm <?php echo ($survey['network_work_required'] ?? false) ? 'text-gray-900 font-medium' : 'text-gray-500'; ?>">
                    Network work required
                </span>
            </div>
        </div>
        
        <?php if (!empty($survey['additional_equipment_needed'])): ?>
        <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Additional Equipment Needed</label>
            <div class="bg-gray-50 p-4 rounded-md">
                <p class="text-sm text-gray-900"><?php echo nl2br(htmlspecialchars($survey['additional_equipment_needed'] ?? '')); ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Survey Findings -->
<div class="professional-table bg-white mb-8">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Survey Findings</h3>
    </div>
    <div class="p-6 space-y-6">
        <?php if ($survey['technical_remarks']): ?>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Technical Remarks</label>
            <div class="bg-gray-50 p-4 rounded-md">
                <p class="text-sm text-gray-900"><?php echo nl2br(htmlspecialchars($survey['technical_remarks'])); ?></p>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($survey['challenges_identified']): ?>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Challenges Identified</label>
            <div class="bg-yellow-50 p-4 rounded-md border border-yellow-200">
                <p class="text-sm text-gray-900"><?php echo nl2br(htmlspecialchars($survey['challenges_identified'])); ?></p>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($survey['recommendations']): ?>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Recommendations</label>
            <div class="bg-blue-50 p-4 rounded-md border border-blue-200">
                <p class="text-sm text-gray-900"><?php echo nl2br(htmlspecialchars($survey['recommendations'])); ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Site Photos and Attachments -->
<?php 
// Collect all photo fields
$photoFields = [
    'floor_height_photos' => 'Floor Height Photos',
    'ceiling_photos' => 'Ceiling Photos', 
    'analytic_photos' => 'Analytic Camera Photos',
    'existing_poe_photos' => 'Existing POE Rack Photos',
    'space_new_rack_photos' => 'Server Room Photos',
    'new_poe_photos' => 'New POE Rack Photos',
    'rrl_photos' => 'RRL Material Photos',
    'kptl_photos' => 'KPTL Material Photos',
    'site_photos' => 'General Site Photos'
];

$hasPhotos = false;
foreach ($photoFields as $field => $label) {
    if (!empty($survey[$field])) {
        $hasPhotos = true;
        break;
    }
}
?>

<?php if ($hasPhotos): ?>
    <div class="professional-table bg-white mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Survey Photos & Attachments</h3>
            <p class="text-sm text-gray-500 mt-1">Visual documentation from the site survey</p>
        </div>
        <div class="p-6">
            <?php foreach ($photoFields as $field => $label): ?>
                <?php if (!empty($survey[$field])): ?>
                    <?php $photos = json_decode($survey[$field], true); ?>
                    <?php if (!empty($photos) && is_array($photos)): ?>
                        <div class="mb-8">
                            <h4 class="text-md font-medium text-gray-900 mb-4"><?php echo $label; ?></h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <?php foreach ($photos as $photo): ?>
                                <div class="relative group">
                                    <img src="<?php echo BASE_URL . '/' . htmlspecialchars($photo); ?>" alt="<?php echo $label; ?>" class="w-full h-48 object-cover rounded-lg shadow-sm border border-gray-200 group-hover:shadow-md transition-shadow">
                                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all rounded-lg flex items-center justify-center">
                                        <button onclick="openImageModal('<?php echo BASE_URL . '/' . htmlspecialchars($photo); ?>', '<?php echo $label; ?>')" class="opacity-0 group-hover:opacity-100 bg-white text-gray-700 px-3 py-1 rounded-md text-sm font-medium transition-opacity">
                                            View Full Size
                                        </button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Approval Status -->
<?php if ($survey['survey_status'] === 'approved' || $survey['survey_status'] === 'rejected'): ?>
<div class="professional-table bg-white">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Approval Status</h3>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?php echo $statusClass; ?>">
                    <?php echo ucfirst($survey['survey_status']); ?>
                </span>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Reviewed By</label>
                <p class="text-sm text-gray-900"><?php echo htmlspecialchars($survey['approved_by_name'] ?: 'N/A'); ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Review Date</label>
                <p class="text-sm text-gray-900">
                    <?php echo $survey['approved_date'] ? date('M d, Y H:i', strtotime($survey['approved_date'])) : 'N/A'; ?>
                </p>
            </div>
            <?php if ($survey['approval_remarks']): ?>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Review Comments</label>
                <div class="bg-gray-50 p-4 rounded-md">
                    <p class="text-sm text-gray-900"><?php echo nl2br(htmlspecialchars($survey['approval_remarks'])); ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Photo Modal -->
<div id="photoModal" class="fixed inset-0 bg-black bg-opacity-75 hidden z-50 flex items-center justify-center p-4">
    <div class="relative max-w-4xl max-h-full">
        <button onclick="closeImageModal()" class="absolute top-4 right-4 text-white hover:text-gray-300 z-10">
            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
        </button>
        <img id="modalImage" src="" alt="" class="max-w-full max-h-full object-contain rounded-lg">
        <div id="modalCaption" class="absolute bottom-4 left-4 right-4 text-white text-center bg-black bg-opacity-50 rounded px-4 py-2"></div>
    </div>
</div>

<script>
function openImageModal(src, caption) {
    document.getElementById('photoModal').classList.remove('hidden');
    document.getElementById('modalImage').src = src;
    document.getElementById('modalCaption').textContent = caption;
    document.body.style.overflow = 'hidden';
}

function closeImageModal() {
    document.getElementById('photoModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Close modal when clicking outside the image
document.getElementById('photoModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeImageModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeImageModal();
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../includes/vendor_layout.php';
?>