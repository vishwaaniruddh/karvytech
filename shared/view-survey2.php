<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';

// Check if user is authenticated
if (!Auth::isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$responseId = $_GET['id'] ?? null;
if (!$responseId) {
    header('Location: ../admin/surveys/index2.php');
    exit;
}

$currentUser = Auth::getCurrentUser();
$isAdmin = Auth::isAdmin();

$db = Database::getInstance()->getConnection();

// Fetch survey response with all related data
$stmt = $db->prepare("
    SELECT sr.*, 
           ds.title as survey_title, 
           ds.description as survey_description,
           s.site_id, s.store_id, s.site_ticket_id,
           CONCAT_WS(' ', u.first_name, u.last_name) as surveyor_name,
           c.name as customer_name,
           approved_u.username as approved_by_name
    FROM dynamic_survey_responses sr
    LEFT JOIN dynamic_surveys ds ON sr.survey_form_id = ds.id
    LEFT JOIN sites s ON sr.site_id = s.id
    LEFT JOIN users u ON sr.surveyor_id = u.id
    LEFT JOIN customers c ON s.customer_id = c.id
    LEFT JOIN users approved_u ON sr.approved_by = approved_u.id
    WHERE sr.id = ?
");
$stmt->execute([$responseId]);
$response = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$response) {
    header('Location: ../admin/surveys/index2.php');
    exit;
}

// Decode JSON data
$formData = json_decode($response['form_data'], true) ?? [];
$siteMasterData = json_decode($response['site_master_data'], true) ?? [];

// Get form structure from database tables
$formStructure = ['sections' => []];

// Fetch main sections (parent_section_id IS NULL)
$stmt = $db->prepare("SELECT * FROM dynamic_survey_sections WHERE survey_id = ? AND parent_section_id IS NULL ORDER BY sort_order ASC");
$stmt->execute([$response['survey_form_id']]);
$sections = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($sections as $sectionIndex => $sectionData) {
    $section = $sectionData;
    // Get fields for this section
    $stmt = $db->prepare("SELECT * FROM dynamic_survey_fields WHERE section_id = ? ORDER BY sort_order ASC");
    $stmt->execute([$section['id']]);
    $section['fields'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get subsections
    $stmt = $db->prepare("SELECT * FROM dynamic_survey_sections WHERE parent_section_id = ? ORDER BY sort_order ASC");
    $stmt->execute([$section['id']]);
    $subsections = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $section['subsections'] = [];
    foreach ($subsections as $subIndex => $subsectionData) {
        $subsection = $subsectionData;
        // Get fields for subsection
        $stmt = $db->prepare("SELECT * FROM dynamic_survey_fields WHERE section_id = ? ORDER BY sort_order ASC");
        $stmt->execute([$subsection['id']]);
        $subsection['fields'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $section['subsections'][] = $subsection;
    }
    $sections[$sectionIndex] = $section;
}

$formStructure['sections'] = $sections;

$title = 'Survey Response - ' . ($response['site_id'] ?? 'Unknown Site');

// Determine layout
if (Auth::isVendor()) {
    $layoutPath = __DIR__ . '/../includes/vendor_layout.php';
    $backUrl = '../contractor/surveys.php';
} else {
    $layoutPath = __DIR__ . '/../includes/admin_layout.php';
    $backUrl = '../admin/surveys/index.php';
}

ob_start();
?>

<!-- Header Section -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
        <div class="flex-1">
            <h1 class="text-3xl font-bold text-gray-900">Survey Response Details</h1>
            <p class="mt-2 text-lg text-gray-600">Site: <span
                    class="font-semibold text-blue-600"><?php echo htmlspecialchars($response['site_id'] ?? 'Unknown'); ?></span>
            </p>
            <p class="text-sm text-gray-500 mt-1">Submitted on
                <?php echo date('M d, Y H:i', strtotime($response['submitted_date'])); ?>
            </p>
        </div>
        <div class="mt-6 lg:mt-0 lg:ml-6">
            <div class="flex space-x-3">
                <?php
                $statusClasses = [
                    'pending' => 'bg-yellow-100 text-yellow-800',
                    'submitted' => 'bg-blue-100 text-blue-800',
                    'approved' => 'bg-green-100 text-green-800',
                    'rejected' => 'bg-red-100 text-red-800'
                ];
                $statusClass = $statusClasses[$response['survey_status']] ?? 'bg-gray-100 text-gray-800';
                ?>
                <span
                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?php echo $statusClass; ?>">
                    <?php echo ucfirst($response['survey_status']); ?>
                </span>

                <?php 
                $canApprove = Auth::hasPermission('surveys', 'approve');
                $canReject = Auth::hasPermission('surveys', 'reject');
                ?>
                <?php if (($canApprove || $canReject) && $response['survey_status'] === 'submitted'): ?>
                    <?php if ($canApprove): ?>
                        <button onclick="approveSurvey(<?php echo $responseId; ?>)"
                            class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            Approve
                        </button>
                    <?php endif; ?>

                    <?php if ($canReject): ?>
                        <button onclick="rejectSurvey(<?php echo $responseId; ?>)"
                            class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            Reject
                        </button>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($response['survey_status'] !== 'approved'): ?>
                    <a href="edit-survey2.php?id=<?php echo $responseId; ?>"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z">
                            </path>
                        </svg>
                        Edit Survey
                    </a>
                <?php endif; ?>

                <a href="<?php echo $backUrl; ?>"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
                            clip-rule="evenodd"></path>
                    </svg>
                    Back
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Site Master Data -->
<div class="professional-table bg-white mb-8">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Site Information</h3>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <?php foreach ($siteMasterData as $key => $value): ?>
                <?php if (!empty($value)): ?>
                    <div>
                        <label
                            class="block text-xs font-medium text-gray-500 mb-1"><?php echo ucwords(str_replace('_', ' ', $key)); ?></label>
                        <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo htmlspecialchars($value); ?></p>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Survey Form Data -->
<div class="professional-table bg-white mb-8">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($response['survey_title']); ?></h3>
        <?php if ($response['survey_description']): ?>
            <p class="text-sm text-gray-500 mt-1"><?php echo htmlspecialchars($response['survey_description']); ?></p>
        <?php endif; ?>
    </div>

    <div class="p-6">
        <!-- Dynamic Sections (Read-only version of edit page) -->
        <?php foreach ($formStructure['sections'] as $section): ?>
            <div class="form-section mb-8">
                <h4 class="text-lg font-semibold text-gray-800 mb-4"><?php echo htmlspecialchars($section['title']); ?></h4>
                <?php if (!empty($section['description'])): ?>
                    <p class="text-sm text-gray-500 mb-4"><?php echo htmlspecialchars($section['description']); ?></p>
                <?php endif; ?>

                <!-- Main Section Fields -->
                <?php if (!empty($section['fields'])): ?>
                    <div class="flex flex-wrap gap-6 mb-6">
                        <?php foreach ($section['fields'] as $field): ?>
                            <?php
                            $fieldValue = $formData[$field['id']] ?? null;
                            $widthClass = 'w-full';
                            if ($field['field_width'] === 'half')
                                $widthClass = 'w-full md:w-[calc(50%-0.75rem)]';
                            elseif ($field['field_width'] === 'third')
                                $widthClass = 'w-full md:w-[calc(33.333%-1rem)]';
                            elseif ($field['field_width'] === 'quarter')
                                $widthClass = 'w-full md:w-[calc(25%-1.125rem)]';
                            ?>
                            <div class="<?php echo $widthClass; ?> form-group">
                                <label class="form-label"><?php echo htmlspecialchars($field['label']); ?></label>

                                <?php if ($field['field_type'] === 'file'): ?>
                                    <!-- File Display -->
                                    <?php
                                    $files = [];
                                    if ($fieldValue) {
                                        if (is_array($fieldValue)) {
                                            // Check if it's a single file (associative array with file_path) or list of files
                                            if (isset($fieldValue['file_path'])) {
                                                $files = [$fieldValue];
                                            } else {
                                                $files = $fieldValue;
                                            }
                                        } else {
                                            // Handle case where it might be a JSON string that hasn't been decoded (extra safety)
                                            $decoded = json_decode($fieldValue, true);
                                            if ($decoded) {
                                                $files = isset($decoded['file_path']) ? [$decoded] : $decoded;
                                            }
                                        }
                                    }
                                    ?>
                                    <?php if (!empty($files)): ?>
                                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                            <?php foreach ($files as $file): ?>
                                                <?php if (is_array($file) && isset($file['file_path'])): ?>
                                                    <?php
                                                    $filePath = $file['file_path'];
                                                    $isImage = in_array(strtolower(pathinfo($filePath, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                                    ?>
                                                    <?php if ($isImage): ?>
                                                        <a href="../<?php echo htmlspecialchars($filePath); ?>" target="_blank" class="block">
                                                            <img src="../<?php echo htmlspecialchars($filePath); ?>"
                                                                alt="<?php echo htmlspecialchars($field['label']); ?>"
                                                                class="w-full h-24 object-cover rounded border">
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="../<?php echo htmlspecialchars($filePath); ?>" target="_blank"
                                                            class="flex flex-col items-center justify-center h-24 bg-gray-100 rounded border hover:bg-gray-200">
                                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                                                </path>
                                                            </svg>
                                                            <span
                                                                class="text-xs text-gray-600 mt-1 px-1 text-center truncate w-full"><?php echo htmlspecialchars($file['original_name'] ?? 'File'); ?></span>
                                                        </a>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-sm text-gray-500 italic">No files uploaded</p>
                                    <?php endif; ?>
                                <?php elseif ($field['field_type'] === 'textarea'): ?>
                                    <div class="bg-gray-50 p-3 rounded text-sm text-gray-900 whitespace-pre-wrap border">
                                        <?php echo htmlspecialchars($fieldValue ?? ''); ?>
                                    </div>
                                <?php elseif ($field['field_type'] === 'checkbox'): ?>
                                    <div class="bg-gray-50 p-2 rounded text-sm text-gray-900 border">
                                        <?php
                                        if ($fieldValue) {
                                            $values = is_array($fieldValue) ? $fieldValue : explode(',', $fieldValue);
                                            echo htmlspecialchars(implode(', ', $values));
                                        } else {
                                            echo '<span class="text-gray-500 italic">None selected</span>';
                                        }
                                        ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded border">
                                        <?php echo htmlspecialchars($fieldValue ?? 'N/A'); ?>
                                    </p>
                                <?php endif; ?>

                                <?php if (!empty($field['help_text'])): ?>
                                    <p class="text-xs text-gray-500 mt-1"><?php echo htmlspecialchars($field['help_text']); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Subsections -->
                <?php if (!empty($section['subsections'])): ?>
                    <div class="space-y-6">
                        <?php foreach ($section['subsections'] as $subsection): ?>
                            <div class="bg-purple-50/50 rounded-xl p-6 border-2 border-purple-200">
                                <div class="mb-4">
                                    <h5 class="text-md font-bold text-purple-900 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                            </path>
                                        </svg>
                                        <?php echo htmlspecialchars($subsection['title']); ?>
                                    </h5>
                                    <?php if (!empty($subsection['description'])): ?>
                                        <p class="text-sm text-purple-700 mt-1">
                                            <?php echo htmlspecialchars($subsection['description']); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <div class="flex flex-wrap gap-6">
                                    <?php foreach ($subsection['fields'] as $field): ?>
                                        <?php
                                        $fieldValue = $formData[$field['id']] ?? null;
                                        $widthClass = 'w-full';
                                        if ($field['field_width'] === 'half')
                                            $widthClass = 'w-full md:w-[calc(50%-0.75rem)]';
                                        elseif ($field['field_width'] === 'third')
                                            $widthClass = 'w-full md:w-[calc(33.333%-1rem)]';
                                        elseif ($field['field_width'] === 'quarter')
                                            $widthClass = 'w-full md:w-[calc(25%-1.125rem)]';
                                        ?>
                                        <div class="<?php echo $widthClass; ?> form-group">
                                            <label class="form-label"><?php echo htmlspecialchars($field['label']); ?></label>

                                            <?php if ($field['field_type'] === 'file'): ?>
                                                <!-- File Display -->
                                                <?php
                                                $files = [];
                                                if ($fieldValue) {
                                                    if (is_array($fieldValue)) {
                                                        if (isset($fieldValue['file_path'])) {
                                                            $files = [$fieldValue];
                                                        } else {
                                                            $files = $fieldValue;
                                                        }
                                                    } else {
                                                        $decoded = json_decode($fieldValue, true);
                                                        if ($decoded) {
                                                            $files = isset($decoded['file_path']) ? [$decoded] : $decoded;
                                                        }
                                                    }
                                                }
                                                ?>
                                                <?php if (!empty($files)): ?>
                                                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                                        <?php foreach ($files as $file): ?>
                                                            <?php if (is_array($file) && isset($file['file_path'])): ?>
                                                                <?php
                                                                $filePath = $file['file_path'];
                                                                $isImage = in_array(strtolower(pathinfo($filePath, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                                                ?>
                                                                <?php if ($isImage): ?>
                                                                    <a href="../<?php echo htmlspecialchars($filePath); ?>" target="_blank" class="block">
                                                                        <img src="../<?php echo htmlspecialchars($filePath); ?>"
                                                                            alt="<?php echo htmlspecialchars($field['label']); ?>"
                                                                            class="w-full h-24 object-cover rounded border">
                                                                    </a>
                                                                <?php else: ?>
                                                                    <a href="../<?php echo htmlspecialchars($filePath); ?>" target="_blank"
                                                                        class="flex flex-col items-center justify-center h-24 bg-gray-100 rounded border hover:bg-gray-200">
                                                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor"
                                                                            viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                                                            </path>
                                                                        </svg>
                                                                        <span
                                                                            class="text-xs text-gray-600 mt-1 px-1 text-center truncate w-full"><?php echo htmlspecialchars($file['original_name'] ?? 'File'); ?></span>
                                                                    </a>
                                                                <?php endif; ?>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <p class="text-sm text-gray-500 italic">No files uploaded</p>
                                                <?php endif; ?>
                                            <?php elseif ($field['field_type'] === 'textarea'): ?>
                                                <div class="bg-white p-3 rounded text-sm text-gray-900 whitespace-pre-wrap border">
                                                    <?php echo htmlspecialchars($fieldValue ?? ''); ?>
                                                </div>
                                            <?php elseif ($field['field_type'] === 'checkbox'): ?>
                                                <div class="bg-white p-2 rounded text-sm text-gray-900 border">
                                                    <?php
                                                    if ($fieldValue) {
                                                        $values = is_array($fieldValue) ? $fieldValue : explode(',', $fieldValue);
                                                        echo htmlspecialchars(implode(', ', $values));
                                                    } else {
                                                        echo '<span class="text-gray-500 italic">None selected</span>';
                                                    }
                                                    ?>
                                                </div>
                                            <?php else: ?>
                                                <p class="text-sm text-gray-900 bg-white p-2 rounded border">
                                                    <?php echo htmlspecialchars($fieldValue ?? 'N/A'); ?>
                                                </p>
                                            <?php endif; ?>

                                            <?php if (!empty($field['help_text'])): ?>
                                                <p class="text-xs text-gray-500 mt-1"><?php echo htmlspecialchars($field['help_text']); ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Approval Status (Permission Check) -->
<?php if ((Auth::hasPermission('surveys', 'approve') || Auth::hasPermission('surveys', 'reject')) && ($response['survey_status'] === 'approved' || $response['survey_status'] === 'rejected')): ?>
    <div class="professional-table bg-white mt-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Approval Status</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <p
                        class="text-sm text-gray-900 bg-gray-50 p-2 rounded font-semibold <?php echo $response['survey_status'] === 'approved' ? 'text-green-700' : 'text-red-700'; ?>">
                        <?php echo ucfirst($response['survey_status']); ?>
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Processed By</label>
                    <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded">
                        <?php echo htmlspecialchars($response['approved_by_name'] ?? 'System/N/A'); ?>
                    </p>
                </div>
                <?php if (!empty($response['approved_date'])): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                        <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded">
                            <?php echo date('M d, Y H:i', strtotime($response['approved_date'])); ?>
                        </p>
                    </div>
                <?php endif; ?>
                <?php if (!empty($response['approval_remarks'])): ?>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Remarks</label>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-900 whitespace-pre-wrap">
                                <?php echo htmlspecialchars($response['approval_remarks']); ?>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if (Auth::hasPermission('surveys', 'approve') || Auth::hasPermission('surveys', 'reject')): ?>
    <!-- Admin Survey Action Modal -->
    <div id="surveyActionModal"
        class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-5xl w-full max-h-[90vh] overflow-hidden">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <h3 id="actionModalTitle" class="text-xl font-semibold text-gray-900"></h3>
                    <button type="button" onclick="closeSurveyActionModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>
            </div>

            <form id="surveyActionForm">
                <div class="flex flex-col md:flex-row">
                    <!-- Left Side: Disclaimer -->
                    <div
                        class="w-full md:w-1/2 p-6 bg-blue-50 border-r border-gray-200 overflow-y-auto max-h-[calc(90vh-180px)]">
                        <div class="flex items-center mb-4">
                            <svg class="w-6 h-6 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <h4 class="text-lg font-semibold text-blue-900">Important Disclaimer</h4>
                        </div>

                        <div class="space-y-4 text-sm text-gray-700">
                            <div class="bg-white p-4 rounded-lg border border-blue-200">
                                <h5 class="font-semibold text-gray-900 mb-2">Survey Verification</h5>
                                <p>By approving or rejecting this survey, you confirm that you have reviewed all submitted
                                    information, verified site details, and examined all photographs.</p>
                            </div>
                            <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-300">
                                <p class="text-sm font-medium text-yellow-900">This action cannot be undone. Please ensure
                                    you have reviewed all details carefully.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side: Remarks Form -->
                    <div class="w-full md:w-1/2 p-6 overflow-y-auto max-h-[calc(90vh-180px)]">
                        <input type="hidden" id="actionResponseId" name="response_id" value="<?php echo $responseId; ?>">
                        <input type="hidden" id="actionType" name="action">

                        <div class="mb-6">
                            <label for="actionRemarks" class="block text-sm font-semibold text-gray-900 mb-2">
                                Remarks / Feedback <span class="text-red-500">*</span>
                            </label>
                            <textarea id="actionRemarks" name="remarks" rows="10" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Enter your remarks here..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
                    <button type="button" onclick="closeSurveyActionModal()"
                        class="px-5 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" id="actionSubmitBtn"
                        class="px-5 py-2 text-sm font-medium text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2"></button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function approveSurvey(id) {
            document.getElementById('actionModalTitle').textContent = 'Approve Survey Response';
            document.getElementById('actionType').value = 'approve';
            document.getElementById('actionSubmitBtn').textContent = 'Confirm Approval';
            document.getElementById('actionSubmitBtn').className = 'px-5 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700';
            document.getElementById('surveyActionModal').classList.remove('hidden');
        }

        function rejectSurvey(id) {
            document.getElementById('actionModalTitle').textContent = 'Reject Survey Response';
            document.getElementById('actionType').value = 'reject';
            document.getElementById('actionSubmitBtn').textContent = 'Confirm Rejection';
            document.getElementById('actionSubmitBtn').className = 'px-5 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700';
            document.getElementById('surveyActionModal').classList.remove('hidden');
        }

        function closeSurveyActionModal() {
            document.getElementById('surveyActionModal').classList.add('hidden');
            document.getElementById('surveyActionForm').reset();
        }

        document.getElementById('surveyActionForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('../admin/surveys/process-survey-action-dynamic.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while processing the request.');
                });
        });
    </script>
<?php endif; ?>

<?php
$content = ob_get_clean();
require_once $layoutPath;
?>