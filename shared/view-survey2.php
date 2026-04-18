<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';

// Check if user is authenticated
if (!Auth::isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

// Check if user has permission to view surveys
Auth::requirePermission('surveys', 'view');

$responseId = $_GET['id'] ?? null;
$revisionId = $_GET['rev_id'] ?? null;
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
           s.id as sites_table_id,
           s.site_id, s.store_id, s.site_ticket_id,
           s.city, s.state, s.country, s.zone, s.customer, s.vendor,
           s.location, s.pincode, s.branch,
           s.contact_person_name, s.contact_person_number, s.contact_person_email,
           COALESCE(NULLIF(TRIM(CONCAT_WS(' ', u.first_name, u.last_name)), ''), u.username) as surveyor_name,
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

// Fetch Revision History
$stmt = $db->prepare("
    SELECT rev.*, u.username as updated_by_name 
    FROM dynamic_survey_revisions rev
    LEFT JOIN users u ON rev.updated_by = u.id
    WHERE rev.response_id = ?
    ORDER BY rev.revision_number DESC
");
$stmt->execute([$responseId]);
$revisions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// If viewing a specific revision, override response data
$currentRevision = null;
if ($revisionId) {
    foreach ($revisions as $rev) {
        if ($rev['id'] == $revisionId) {
            $currentRevision = $rev;
            $response['form_data'] = $rev['form_data'];
            $response['site_master_data'] = $rev['site_master_data'];
            $response['submitted_date'] = $rev['updated_at'];
            $response['surveyor_name'] = $rev['updated_by_name'];
            break;
        }
    }
}

// Decode JSON data
$formData = json_decode($response['form_data'], true) ?? [];

// Site information is already fetched from the main query join
$siteMasterData = [];
if (!empty($response['site_id'])) {
    $siteMasterData = [
        'site_id' => $response['site_id'] ?? '',
        'store_id' => $response['store_id'] ?? '',
        'site_ticket_id' => $response['site_ticket_id'] ?? '',
        'location' => $response['location'] ?? '',
        'pincode' => $response['pincode'] ?? '',
        'branch' => $response['branch'] ?? '',
        'contact_person_name' => $response['contact_person_name'] ?? '',
        'contact_person_number' => $response['contact_person_number'] ?? '',
        'contact_person_email' => $response['contact_person_email'] ?? '',
        'city' => $response['city'] ?? '',
        'state' => $response['state'] ?? '',
        'country' => $response['country'] ?? '',
        'zone' => $response['zone'] ?? '',
        'customer' => $response['customer'] ?? '',
        'vendor' => $response['vendor'] ?? ''
    ];
    
    // Remove empty values
    $siteMasterData = array_filter($siteMasterData, function($value) {
        return !empty($value);
    });
}

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

// Helper function to get repeat count for a section
function getRepeatCount($section, $sections, $formData) {
    $sectionTitle = strtolower(trim($section['title'] ?? ''));
    
    // Special handling for "Floor Wise Camera Details"
    if ($sectionTitle === 'floor wise camera details') {
        // Find "General Information" section
        foreach ($sections as $s) {
            if (strtolower(trim($s['title'] ?? '')) === 'general information') {
                // Find "No of Floors" field
                foreach ($s['fields'] as $field) {
                    if (strtolower(trim($field['label'] ?? '')) === 'no of floors') {
                        $value = $formData[$field['id']] ?? 0;
                        $count = intval($value);
                        return max(0, $count);
                    }
                }
            }
        }
        return 0;
    }
    
    // For other repeatable sections
    if ($section['is_repeatable'] && $section['repeat_source_field_id']) {
        $value = $formData[$section['repeat_source_field_id']] ?? 0;
        $count = intval($value);
        return max(0, $count);
    }
    
    return 1; // Default: show once
}

// Helper function to get field key
function getFieldKey($fieldId, $repeatIndex, $section) {
    $isRepeatable = $section['is_repeatable'] || stripos($section['title'] ?? '', 'floor wise') !== false;
    if (!$isRepeatable) {
        return $fieldId;
    }
    return $fieldId . '_' . $repeatIndex;
}

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

<?php if ($revisionId): ?>
    <!-- Historical View Banner -->
    <div class="bg-amber-50 border-l-4 border-amber-400 p-4 mb-8 rounded-r-lg shadow-sm">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-amber-700">
                        <span class="font-bold">Historical Mode:</span> You are viewing 
                        <span class="font-bold underline">Revision #<?php echo $currentRevision['revision_number']; ?></span> 
                        submitted on <?php echo date('M d, Y h:i A', strtotime($currentRevision['updated_at'])); ?>.
                    </p>
                </div>
            </div>
            <a href="view-survey2.php?id=<?php echo $responseId; ?>" 
               class="ml-4 px-3 py-1 bg-amber-100 text-amber-800 text-xs font-semibold rounded hover:bg-amber-200 transition-colors">
               Back to Latest
            </a>
        </div>
    </div>
<?php endif; ?>

<!-- Header Section -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
        <div class="flex-1">
            <h1 class="text-3xl font-bold text-gray-900">Survey Response Details</h1>
            <p class="mt-2 text-lg text-gray-600">Site: <span
                    class="font-semibold text-blue-600"><?php echo htmlspecialchars($response['site_id'] ?? 'Unknown'); ?></span>
            </p>
            <p class="text-sm text-gray-500 mt-1">Submitted by 
                <span class="font-medium text-gray-900"><?php echo htmlspecialchars($response['surveyor_name'] ?? 'Unknown'); ?></span>
                on <?php echo date('M d, Y h:i A', strtotime($response['submitted_date'])); ?>
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

                <?php if ($response['survey_status'] !== 'approved' && Auth::hasPermission('surveys', 'edit')): ?>
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
        <?php if (empty($siteMasterData)): ?>
            <p class="text-sm text-gray-500 italic">No site information available</p>
        <?php else: ?>
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
        <?php endif; ?>
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
            <?php 
            $repeatCount = getRepeatCount($section, $formStructure['sections'], $formData);
            $isRepeatable = $section['is_repeatable'] || stripos($section['title'] ?? '', 'floor wise') !== false;
            ?>
            <?php for ($rIndex = 1; $rIndex <= $repeatCount; $rIndex++): ?>
            <div class="form-section mb-8 <?php echo $isRepeatable ? 'border-l-4 border-blue-500 pl-6' : ''; ?>">
                <h4 class="text-lg font-semibold text-gray-800 mb-4">
                    <?php echo htmlspecialchars($section['title']); ?>
                    <?php if ($isRepeatable && $repeatCount > 1): ?>
                        <span class="text-blue-500 ml-2">( #<?php echo $rIndex; ?> )</span>
                    <?php endif; ?>
                </h4>
                <?php if (!empty($section['description'])): ?>
                    <p class="text-sm text-gray-500 mb-4"><?php echo htmlspecialchars($section['description']); ?></p>
                <?php endif; ?>

                <!-- Main Section Fields -->
                <?php if (!empty($section['fields'])): ?>
                    <div class="flex flex-wrap gap-6 mb-6">
                        <?php foreach ($section['fields'] as $field): ?>
                            <?php
                            $fieldKey = getFieldKey($field['id'], $rIndex, $section);
                            $fieldValue = $formData[$fieldKey] ?? null;
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
                                        $fieldKey = getFieldKey($field['id'], $rIndex, $section);
                                        $fieldValue = $formData[$fieldKey] ?? null;
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
            <?php endfor; // End repeat loop ?>
            
            <!-- Cumulative Summary for Repeatable Sections -->
            <!-- Cumulative Summary for Repeatable Sections -->
            <?php if ($isRepeatable && $repeatCount > 0): ?>
                <?php
                $hardwareGrandTotal = 0;
                $summaryCards = [];
                
                // Collect and calculate for all fields
                $allFieldsForSum = $section['fields'];
                if (!empty($section['subsections'])) {
                    foreach ($section['subsections'] as $sub) {
                        $allFieldsForSum = array_merge($allFieldsForSum, $sub['fields']);
                    }
                }

                foreach ($allFieldsForSum as $field) {
                    if ($field['field_type'] === 'number') {
                        $sum = 0;
                        for ($i = 1; $i <= $repeatCount; $i++) {
                            $fieldKey = getFieldKey($field['id'], $i, $section);
                            $sum += floatval($formData[$fieldKey] ?? 0);
                        }
                        
                        $summaryCards[] = ['label' => $field['label'], 'sum' => $sum];
                        $lbl = strtolower($field['label']);
                        if (strpos($lbl, 'slp camera') !== false || strpos($lbl, 'analytical camera') !== false || strpos($lbl, 'blind spot') !== false) {
                            $hardwareGrandTotal += $sum;
                        }
                    }
                }
                ?>
                <div class="mb-8 p-8 bg-blue-50/50 rounded-2xl border-2 border-blue-100 shadow-sm">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h4 class="text-sm font-black text-blue-900 uppercase tracking-[0.2em]">Deployment Summary</h4>
                            <p class="text-xs text-blue-600 mt-1 font-medium italic">Hardware consolidation across <?php echo $repeatCount; ?> floor(s)</p>
                        </div>
                        <div class="bg-blue-600 text-white px-6 py-3 rounded-xl shadow-lg flex items-center gap-4">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-widest opacity-80 leading-none">Overall Hardware Total</p>
                                <p class="text-xs font-medium opacity-70">(SLP + Analytical + Blind Spot)</p>
                            </div>
                            <span class="text-3xl font-black tabular-nums border-l border-white/20 pl-4"><?php echo number_format($hardwareGrandTotal); ?></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                        <?php foreach ($summaryCards as $card): ?>
                            <div class="bg-white p-5 rounded-xl shadow-sm border border-blue-100">
                                <p class="text-[10px] font-black text-blue-500 uppercase tracking-widest mb-1">Total <?php echo htmlspecialchars($card['label']); ?></p>
                                <div class="flex items-baseline justify-between">
                                    <p class="text-2xl font-bold text-blue-900"><?php echo number_format($card['sum']); ?></p>
                                    <span class="text-[10px] font-bold text-gray-400 uppercase">Qty</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
        <?php endforeach; // End section loop ?>
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

<?php if (!empty($revisions)): ?>
    <!-- Revision History Section -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mt-8 mb-8">
        <div class="flex items-center justify-between mb-6 border-b pb-4">
            <div>
                <h3 class="text-xl font-bold text-gray-900">Revision History</h3>
                <p class="text-sm text-gray-500 mt-1">Audit log of all changes made to this survey.</p>
            </div>
            <div class="bg-blue-50 text-blue-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider">
                <?php echo count($revisions); ?> Revisions
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Rev #</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Updated By</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Date & Time</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Summary of Changes</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($revisions as $rev): ?>
                        <tr class="<?php echo ($revisionId == $rev['id']) ? 'bg-blue-50/50' : ''; ?> hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-sm font-bold text-gray-900">
                                #<?php echo $rev['revision_number']; ?>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                <div class="flex items-center">
                                    <div class="h-8 w-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center mr-2 text-xs font-bold">
                                        <?php echo strtoupper(substr($rev['updated_by_name'] ?? 'U', 0, 1)); ?>
                                    </div>
                                    <?php echo htmlspecialchars($rev['updated_by_name'] ?? 'Unknown'); ?>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                <?php echo date('M d, Y h:i A', strtotime($rev['updated_at'])); ?>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 italic">
                                <?php echo htmlspecialchars($rev['change_summary'] ?? 'No summary provided'); ?>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <?php if ($revisionId == $rev['id']): ?>
                                    <span class="text-xs font-bold text-blue-600 bg-blue-100 px-2 py-1 rounded">Currently Viewing</span>
                                <?php else: ?>
                                    <a href="view-survey2.php?id=<?php echo $responseId; ?>&rev_id=<?php echo $rev['id']; ?>" 
                                       class="inline-flex items-center text-indigo-600 hover:text-indigo-900 font-medium text-sm transition-colors group">
                                        View Version
                                        <svg class="w-4 h-4 ml-1 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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

        document.getElementById('surveyActionForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            const action = document.getElementById('actionType').value;
            const actionLabel = action === 'approve' ? 'Approve' : 'Reject';
            
            const confirmed = await showConfirm(
                `${actionLabel} Survey`,
                `Are you sure you want to ${action} this survey response? This action cannot be undone.`,
                { 
                    confirmText: `Yes, ${actionLabel}`,
                    confirmType: action === 'approve' ? 'success' : 'danger'
                }
            );

            if (!confirmed) return;

            const formData = new FormData(this);
            const submitBtn = document.getElementById('actionSubmitBtn');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Processing...';

            fetch('../admin/surveys/process-survey-action-dynamic.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showToast(data.message, 'error');
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred while processing the request.', 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                });
        });
    </script>
<?php endif; ?>

<?php
$content = ob_get_clean();
require_once $layoutPath;
?>