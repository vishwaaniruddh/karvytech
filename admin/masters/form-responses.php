<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/DynamicSurvey.php';

$surveyId = $_GET['id'] ?? null;
if (!$surveyId) die("Form ID required");

$surveyModel = new DynamicSurvey();
$survey = $surveyModel->find($surveyId);
if (!$survey) die("Form not found");

$fields = $surveyModel->getFields($surveyId);
$db = Database::getInstance()->getConnection();

// Fetch responses
$stmt = $db->prepare("
    SELECT r.*, s.site_ticket_id as site_name 
    FROM dynamic_survey_responses r 
    LEFT JOIN sites s ON r.site_id = s.id 
    WHERE r.survey_id = ? 
    ORDER BY r.submission_date DESC
");
$stmt->execute([$surveyId]);
$responses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all values for these responses
$responseIds = array_column($responses, 'id');
$valuesMap = [];
if (!empty($responseIds)) {
    $placeholders = implode(',', array_fill(0, count($responseIds), '?'));
    $stmt = $db->prepare("SELECT * FROM dynamic_survey_response_values WHERE response_id IN ($placeholders)");
    $stmt->execute($responseIds);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $valuesMap[$row['response_id']][$row['field_id']] = $row;
    }
}

ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <div class="flex items-center text-sm text-gray-500 mb-2">
            <a href="form-maker.php" class="hover:text-blue-600">Form Maker</a>
            <svg class="w-4 h-4 mx-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
            <span>Responses</span>
        </div>
        <h1 class="text-2xl font-semibold text-gray-900"><?php echo htmlspecialchars($survey['title']); ?> - Responses</h1>
        <p class="text-sm text-gray-600">Viewing all submissions for this form</p>
    </div>
    <div class="flex space-x-3">
         <button onclick="window.print()" class="btn btn-secondary flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Print
        </button>
    </div>
</div>

<div class="card overflow-hidden">
    <div class="card-body p-0">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-100">Metadata</th>
                        <?php foreach ($fields as $field): ?>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-100 border-l border-gray-200"><?php echo htmlspecialchars($field['label']); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($responses)): ?>
                        <tr>
                            <td colspan="<?php echo count($fields) + 1; ?>" class="px-6 py-12 text-center text-gray-400 italic">No responses yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($responses as $resp): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap text-xs">
                                    <div class="font-bold text-gray-900">#<?php echo $resp['id']; ?></div>
                                    <div class="text-gray-500"><?php echo date('d M Y, H:i', strtotime($resp['submission_date'])); ?></div>
                                    <?php if ($resp['site_name']): ?>
                                        <div class="mt-1 text-blue-600 font-medium">Site: <?php echo htmlspecialchars($resp['site_name']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <?php foreach ($fields as $field): ?>
                                    <td class="px-4 py-3 text-sm border-l border-gray-100">
                                        <?php 
                                        $valObj = $valuesMap[$resp['id']][$field['id']] ?? null;
                                        if (!$valObj) {
                                            echo '<span class="text-gray-300">-</span>';
                                        } elseif ($field['field_type'] === 'file') {
                                            $path = $valObj['file_path'];
                                            if ($path) {
                                                // If multiple files (stored as comma separated or handle logic)
                                                // For now assuming single for display, or simple split
                                                $files = explode(',', $path);
                                                foreach ($files as $f) {
                                                    $f = trim($f);
                                                    $name = basename($f);
                                                    echo '<a href="../../'.htmlspecialchars($f).'" target="_blank" class="flex items-center text-blue-600 hover:underline mb-1">';
                                                    echo '<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>';
                                                    echo htmlspecialchars($name) . '</a>';
                                                }
                                            } else {
                                                echo '<span class="text-gray-400">No file</span>';
                                            }
                                        } else {
                                            $raw = $valObj['field_value'] ?? '';
                                            if ($field['field_type'] === 'datetime' || $field['field_type'] === 'datetime-local') {
                                                $raw = trim((string)$raw);
                                                // datetime-local posts in ISO format: YYYY-MM-DDTHH:MM
                                                $normalized = str_replace('T', ' ', $raw);
                                                $ts = strtotime($normalized);
                                                echo $ts ? htmlspecialchars(date('d M Y, H:i', $ts)) : nl2br(htmlspecialchars($raw));
                                            } else {
                                                echo nl2br(htmlspecialchars((string)$raw));
                                            }
                                        }
                                        ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
/* Custom styles for printing */
@media print {
    .btn, .sidebar, .header, .breadcrumb { display: none !important; }
    .card { border: none !important; box-shadow: none !important; }
    .data-table th { background-color: #f3f4f6 !important; -webkit-print-color-adjust: exact; }
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>
