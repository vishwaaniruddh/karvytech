<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/SiteSurvey.php';

// Require vendor authentication
Auth::requireVendor();

$vendorId = Auth::getVendorId();
$db = Database::getInstance()->getConnection();

// 1. Get legacy surveys
$surveyModel = new SiteSurvey();
$legacySurveys = $surveyModel->getVendorSurveys($vendorId);

// 2. Get dynamic surveys
$stmt = $db->prepare("
    SELECT dsr.id, dsr.site_id as site_db_id, dsr.survey_form_id, dsr.survey_status, 
           dsr.submitted_date as created_at, s.site_id as site_code, s.location,
           ds.title as survey_title, 'dynamic' as source
    FROM dynamic_survey_responses dsr
    LEFT JOIN sites s ON dsr.site_id = s.id
    LEFT JOIN dynamic_surveys ds ON dsr.survey_form_id = ds.id
    WHERE dsr.surveyor_id = ?
    ORDER BY dsr.submitted_date DESC
");
$stmt->execute([$_SESSION['user_id']]);
$dynamicSurveys = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Map legacy surveys to a consistent format
$formattedLegacy = array_map(function($s) {
    return [
        'id' => $s['id'],
        'site_code' => $s['site_code'],
        'location' => $s['location'],
        'survey_status' => $s['survey_status'],
        'created_at' => $s['created_at'],
        'survey_title' => 'Legacy Survey',
        'source' => 'legacy',
        'site_id' => $s['site_id']
    ];
}, $legacySurveys);

// Map dynamic surveys
$formattedDynamic = array_map(function($s) {
    return [
        'id' => $s['id'],
        'site_code' => $s['site_code'],
        'location' => $s['location'],
        'survey_status' => $s['survey_status'],
        'created_at' => $s['created_at'],
        'survey_title' => $s['survey_title'] ?? 'Dynamic Survey',
        'source' => 'dynamic',
        'site_id' => $s['site_db_id']
    ];
}, $dynamicSurveys);

// Combine all surveys
$allSurveys = array_merge($formattedDynamic, $formattedLegacy);

// Sort by date descending
usort($allSurveys, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

$title = 'My Surveys';
ob_start();
?>

<!-- Enhanced Header -->
<div class="bg-gradient-to-r from-green-600 to-green-700 rounded-xl shadow-lg p-6 mb-8">
    <div class="flex justify-between items-center">
        <div class="text-white">
            <h1 class="text-3xl font-bold">Site Surveys</h1>
            <p class="mt-2 text-green-100">Modern Site Assessment Dashboard</p>
            <p class="text-sm text-green-200 mt-1">Manage all your submitted surveys in one place</p>
        </div>
        <div class="flex items-center space-x-4">
            <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-4 text-center min-w-24">
                <div class="text-2xl font-bold text-white"><?php echo count(array_filter($allSurveys, fn($s) => $s['survey_status'] === 'submitted' || $s['survey_status'] === 'pending')); ?></div>
                <div class="text-xs text-green-100 uppercase tracking-wide">Pending</div>
            </div>
            <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-4 text-center min-w-24">
                <div class="text-2xl font-bold text-white"><?php echo count(array_filter($allSurveys, fn($s) => $s['survey_status'] === 'approved')); ?></div>
                <div class="text-xs text-green-100 uppercase tracking-wide">Approved</div>
            </div>
            <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-4 text-center min-w-24">
                <div class="text-2xl font-bold text-white"><?php echo count($allSurveys); ?></div>
                <div class="text-xs text-green-100 uppercase tracking-wide">Total</div>
            </div>
        </div>
    </div>
</div>

<!-- Surveys Table -->
<div class="professional-table bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">Survey History</h3>
            <p class="text-sm text-gray-500 mt-1">Unified view of legacy and dynamic surveys</p>
        </div>
    </div>
    <div class="p-6">
        <?php if (empty($allSurveys)): ?>
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No surveys found</h3>
                <p class="mt-1 text-sm text-gray-500">You haven't submitted any site surveys yet.</p>
                <div class="mt-6">
                    <a href="sites/" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 transition-colors">
                        View Sites
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="table-header bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Survey Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Site Information</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted On</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($allSurveys as $survey): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <?php if ($survey['source'] === 'dynamic'): ?>
                                            <div class="h-10 w-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                                <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z"></path>
                                                    <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd"></path>
                                                </svg>
                                            </div>
                                        <?php else: ?>
                                            <div class="h-10 w-10 rounded-lg bg-gray-100 flex items-center justify-center">
                                                <svg class="h-5 w-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm0 2h12v8H4V6z" clip-rule="evenodd"></path>
                                                </svg>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($survey['survey_title']); ?></div>
                                        <div class="text-xs text-gray-500"><?php echo $survey['source'] === 'dynamic' ? 'New Dynamic Form' : 'Legacy Form'; ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($survey['site_code']); ?></div>
                                <div class="text-xs text-gray-500 truncate max-w-xs" title="<?php echo htmlspecialchars($survey['location']); ?>"><?php echo htmlspecialchars($survey['location']); ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <?php
                                $status = $survey['survey_status'];
                                $badgeClass = match($status) {
                                    'approved' => 'bg-green-100 text-green-800',
                                    'rejected' => 'bg-red-100 text-red-800',
                                    'submitted', 'pending' => 'bg-yellow-100 text-yellow-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                                ?>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $badgeClass; ?>">
                                    <?php echo ucfirst($status); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?php echo date('M d, Y H:i', strtotime($survey['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <?php if ($survey['source'] === 'dynamic'): ?>
                                    <a href="../shared/view-survey2.php?id=<?php echo $survey['id']; ?>" class="inline-flex items-center px-3 py-1.5 border border-blue-600 text-xs font-medium rounded text-blue-600 hover:bg-blue-600 hover:text-white transition-colors">
                                        View Survey
                                    </a>
                                <?php else: ?>
                                    <a href="../shared/view-survey.php?id=<?php echo $survey['id']; ?>" class="inline-flex items-center px-3 py-1.5 border border-gray-600 text-xs font-medium rounded text-gray-600 hover:bg-gray-600 hover:text-white transition-colors">
                                        View (Old)
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../includes/vendor_layout.php';
?>