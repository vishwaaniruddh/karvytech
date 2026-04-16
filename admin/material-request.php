<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/Site.php';
require_once __DIR__ . '/../models/SiteSurvey.php';
require_once __DIR__ . '/../models/BoqItem.php';
require_once __DIR__ . '/../models/BoqMaster.php';
require_once __DIR__ . '/../models/SiteDelegation.php';

// Require vendor authentication
//Auth::requireVendor();

//$vendorId = Auth::getVendorId();
$siteId = $_GET['site_id'] ?? null;
$surveyId = $_GET['survey_id'] ?? null;

$delegationModel = new SiteDelegation();
$vendorId = $delegationModel->findSiteVendorId($siteId);

// If no site_id provided, show site selection page
if (!$siteId) {
    require_once __DIR__ . '/../models/VendorPermission.php';
    $permissionModel = new VendorPermission();
    $vendorSites = $permissionModel->getVendorSites($vendorId);
    
    $title = 'Material Request - Select Site';
    ob_start();
    ?>
    
    <!-- Header Section -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-gray-900">Material Request</h1>
                <p class="mt-2 text-lg text-gray-600">Select a site to create a material request</p>
            </div>
            <div class="mt-6 lg:mt-0 lg:ml-6">
                <a href="sites/index.php" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                    </svg>
                    Back to Sites
                </a>
            </div>
        </div>
    </div>

    <!-- Site Selection -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Select Site for Material Request</h3>
        
        <?php if (empty($vendorSites)): ?>
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H9m0 0H5m0 0h2M7 7h10M7 11h4m6 0h4M7 15h10"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Sites Available</h3>
                <p class="text-gray-500">You don't have access to any sites yet. Please contact your administrator.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($vendorSites as $site): ?>
                    <div class="border border-gray-200 rounded-lg p-6 hover:border-blue-300 hover:shadow-md transition-all duration-200">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h4 class="text-lg font-semibold text-gray-900 mb-2"><?php echo htmlspecialchars($site['site_id']); ?></h4>
                                <p class="text-sm text-gray-600 mb-3"><?php echo htmlspecialchars($site['location'] ?? 'Location not specified'); ?></p>
                                
                                <div class="flex items-center text-xs text-gray-500 mb-4">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <?php echo htmlspecialchars($site['city_name'] ?? 'City not specified'); ?>
                                </div>
                                
                                <a href="material-request.php?site_id=<?php echo $site['id']; ?>" 
                                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    Create Request
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <?php
    $content = ob_get_clean();
    include __DIR__ . '/../includes/admin_layout.php';
    exit;
}

// Get site details with relationships
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("
    SELECT s.*, c.name as customer_name, ct.name as city_name, st.name as state_name
    FROM sites s
    LEFT JOIN customers c ON s.customer_id = c.id
    LEFT JOIN cities ct ON s.city_id = ct.id
    LEFT JOIN states st ON s.state_id = st.id
    WHERE s.id = ?
");
$stmt->execute([$siteId]);
$site = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$site) {
    header('Location: sites/index.php');
    exit;
}

// Get survey details - check both legacy and dynamic surveys
$surveyModel = new SiteSurvey();
$survey = $surveyModel->findBySiteAndVendor($siteId, $vendorId);

// If no legacy survey found, check for dynamic surveys
if (!$survey) {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("
        SELECT dsr.*, 'dynamic' as survey_type, dsr.survey_status, dsr.submitted_date, 
               v.name as surveyor_name
        FROM dynamic_survey_responses dsr
        LEFT JOIN vendors v ON dsr.surveyor_id = v.id
        WHERE dsr.site_id = ?
        AND dsr.survey_status = 'approved'
        ORDER BY dsr.submitted_date DESC
        LIMIT 1
    ");
    $stmt->execute([$siteId]);
    $dynamicSurvey = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($dynamicSurvey) {
        $survey = $dynamicSurvey;
        $survey['survey_type'] = 'dynamic';
    }
}

// echo '$siteId' . $siteId ; 
// echo '$vendorId' . $vendorId;
// var_dump($survey);
// return ; 
// If a specific survey_id was provided, verify it matches
if ($surveyId && $survey && $survey['id'] != $surveyId) {
    // The provided survey_id doesn't match the site/vendor survey
    $survey = null;
}

// Get BOQ items for material selection
$boqModel = new BoqItem();
$boqItems = $boqModel->getActive();

// Get BOQ Masters for this customer
$boqMasterModel = new BoqMaster();
$availableBoqs = $boqMasterModel->getActiveByCustomerId($site['customer_id']);

// If it's a dynamic survey, fetch field mappings for better data display in the Intelligence Card
if (isset($survey['survey_type']) && $survey['survey_type'] === 'dynamic') {
    $stmt = $db->prepare("
        SELECT id, label, section_id 
        FROM dynamic_survey_fields 
        WHERE section_id IN (SELECT id FROM dynamic_survey_sections WHERE survey_id = ?)
    ");
    $stmt->execute([$survey['survey_form_id']]);
    $fieldDefs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $formData = json_decode($survey['form_data'], true) ?? [];
    $surveyDataMapped = [];
    foreach ($fieldDefs as $f) {
        $val = $formData[$f['id']] ?? null;
        if ($val !== null) {
            $surveyDataMapped[strtolower(trim($f['label']))] = $val;
        }
    }
}

// Check for existing material requests for this site
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("
    SELECT mr.*, 
           JSON_LENGTH(mr.items) as item_count,
           CASE 
               WHEN mr.status = 'pending' THEN 'warning'
               WHEN mr.status = 'approved' THEN 'success'
               WHEN mr.status = 'rejected' THEN 'danger'
               WHEN mr.status = 'dispatched' THEN 'info'
               WHEN mr.status = 'completed' THEN 'success'
               ELSE 'secondary'
           END as status_color
    FROM material_requests mr
    WHERE mr.site_id = ?
    ORDER BY mr.created_at DESC
");
$stmt->execute([$siteId]);
$existingRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

$title = 'Material Request - ' . ($site['site_id'] ?? 'Site #' . $siteId);
ob_start();
?>

<!-- Header Section -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
        <div class="flex-1">
            <h1 class="text-3xl font-bold text-gray-900">Material Request</h1>
            <p class="mt-2 text-lg text-gray-600">Site: <span class="font-semibold text-blue-600"><?php echo htmlspecialchars($site['site_id'] ?? 'Unknown'); ?></span></p>
            <p class="text-sm text-gray-500 mt-1">Request materials needed for installation</p>
        </div>
        <div class="mt-6 lg:mt-0 lg:ml-6">
            <a href="sites/index.php" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                </svg>
                Back to Sites
            </a>
        </div>
    </div>
</div>

<!-- Info Grid: Site & Survey Intelligence -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- Site Information Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Site Logistics</h3>
            <span class="px-3 py-1 bg-blue-50 text-blue-600 text-[10px] font-bold rounded-full border border-blue-100 uppercase tracking-widest">Master Data</span>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-2 gap-y-6 gap-x-4">
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Site Identity</label>
                    <p class="text-sm font-bold text-gray-900"><?php echo htmlspecialchars($site['site_id'] ?? 'N/A'); ?></p>
                    <p class="text-[10px] font-semibold text-gray-400 mt-1 uppercase"><?php echo htmlspecialchars($site['store_id'] ?? ''); ?></p>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Client & Brand</label>
                    <p class="text-sm font-bold text-gray-800"><?php echo htmlspecialchars($site['customer_name'] ?? 'N/A'); ?></p>
                </div>
                <div class="col-span-2">
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Geo-Location</label>
                    <p class="text-sm font-bold text-gray-900"><?php echo htmlspecialchars($site['location'] ?? 'N/A'); ?></p>
                    <p class="text-[10px] font-semibold text-gray-500 mt-1 uppercase"><?php echo htmlspecialchars(($site['city_name'] ?? '') . ', ' . ($site['state_name'] ?? '')); ?></p>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Operational Branch</label>
                    <p class="text-sm font-bold text-gray-800 tracking-tight"><?php echo htmlspecialchars($site['branch'] ?? 'N/A'); ?></p>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Current Status</label>
                    <span class="inline-flex px-2 py-0.5 bg-green-50 text-green-700 text-[10px] font-bold rounded-md border border-green-100 uppercase tracking-tight">Active Project</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Survey Intelligence Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Survey Intelligence</h3>
            <?php if ($survey): ?>
                <span class="px-3 py-1 bg-green-50 text-green-600 text-[10px] font-bold rounded-full border border-green-100 uppercase tracking-widest">Survey Verified</span>
            <?php else: ?>
                <span class="px-3 py-1 bg-amber-50 text-amber-600 text-[10px] font-bold rounded-full border border-amber-100 uppercase tracking-widest">Action Required</span>
            <?php endif; ?>
        </div>
        <div class="p-6">
            <?php if ($survey): ?>
                <div class="grid grid-cols-2 gap-y-6 gap-x-4">
                    <div class="col-span-2 flex items-center justify-between p-3 bg-gray-50 border border-gray-100 rounded-xl mb-2">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Survey Status</p>
                            <p class="text-sm font-bold text-gray-900 mt-1">Assessment Completed</p>
                            <p class="text-[9px] font-bold text-blue-500 uppercase tracking-wider mt-1 opacity-80"><?php echo htmlspecialchars($survey['surveyor_name'] ?? 'Vendor Assigned'); ?></p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Submission Date</p>
                            <p class="text-xs font-bold text-gray-700 mt-1"><?php echo date('d M Y, h:i A', strtotime($survey['submitted_date'])); ?></p>
                        </div>
                    </div>
                    
                    
                    <div class="col-span-2 mt-4 pt-4 border-t border-gray-50 flex items-center justify-between">
                        <button type="button" onclick="toggleSurveyDetails('<?php echo $siteId; ?>', '<?php echo $survey['survey_type'] ?? 'legacy'; ?>')" class="inline-flex items-center text-xs font-bold text-blue-600 hover:text-blue-800 transition-colors uppercase tracking-widest group">
                            <span id="toggleText">Expand Detailed Technical Manifest</span>
                            <svg id="toggleIcon" class="w-4 h-4 ml-2 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        <a href="<?php echo (isset($survey['survey_type']) && $survey['survey_type'] === 'dynamic') ? '../shared/view-survey2.php?id=' . $survey['id'] : '../shared/view-survey.php?id=' . $survey['id']; ?>" target="_blank" class="inline-flex items-center text-[10px] font-bold text-gray-400 hover:text-blue-600 transition-colors uppercase tracking-widest group">
                            Open in New Tab
                            <svg class="w-3.5 h-3.5 ml-1.5 opacity-60 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="flex flex-col items-center justify-center py-10 opacity-60">
                    <div class="w-16 h-16 bg-amber-50 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <p class="text-sm font-bold text-gray-800 uppercase tracking-widest">No Intelligence Data</p>
                    <p class="text-xs font-semibold text-gray-500 mt-2 text-center">Material requisition is blocked until a site<br>survey manifest is verified.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Survey Expansion (Full Width) -->
<div id="surveyDetailsContainer" class="mb-8 hidden animate-fadeIn overflow-hidden">
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100/80 p-8" id="surveyDetailsContent">
        <div class="flex items-center justify-center py-12">
            <svg class="animate-spin h-6 w-6 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="ml-4 text-xs font-bold text-gray-500 uppercase tracking-[0.2em]">Hydrating Manifest Intelligence...</span>
        </div>
    </div>
</div>

<!-- Existing Material Requests Warning -->
<?php if (!empty($existingRequests)): ?>
<div class="bg-amber-50 border border-amber-200 rounded-lg p-6 mb-8">
    <div class="flex items-start">
        <div class="flex-shrink-0">
            <svg class="w-6 h-6 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
        </div>
        <div class="ml-3 flex-1">
            <h3 class="text-lg font-semibold text-amber-800 mb-2">
                <svg class="w-5 h-5 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
                Existing Material Requests Found
            </h3>
            <p class="text-amber-700 mb-4">
                This site already has <?php echo count($existingRequests); ?> material request(s). 
                Please review existing requests before creating a new one to avoid duplicates.
            </p>
            
            <div class="space-y-3">
                <?php foreach ($existingRequests as $request): ?>
                <div class="bg-white border border-amber-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3">
                                <h4 class="font-semibold text-gray-900">Request #<?php echo $request['id']; ?></h4>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    <?php 
                                    $status = $request['status'] ?: 'pending';
                                    echo $status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                              ($status === 'approved' ? 'bg-green-100 text-green-800' : 
                                              ($status === 'rejected' ? 'bg-red-100 text-red-800' : 
                                              ($status === 'dispatched' ? 'bg-blue-100 text-blue-800' : 
                                              ($status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800')))); ?>">
                                    <?php echo ucfirst($status); ?>
                                </span>
                            </div>
                            <div class="mt-2 text-sm text-gray-600">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <span class="font-medium">Requested:</span> 
                                        <?php echo date('j M Y', strtotime($request['request_date'])); ?>
                                    </div>
                                    <div>
                                        <span class="font-medium">Required:</span> 
                                        <?php echo date('j M Y', strtotime($request['required_date'])); ?>
                                    </div>
                                    <div>
                                        <span class="font-medium">Items:</span> 
                                        <?php echo $request['item_count']; ?> item(s)
                                    </div>
                                </div>
                                <?php if ($request['request_notes']): ?>
                                <div class="mt-2">
                                    <span class="font-medium">Notes:</span> 
                                    <?php echo htmlspecialchars($request['request_notes']); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="ml-4">
                            <a href="requests/view-request.php?id=<?php echo $request['id']; ?>" 
                               class="inline-flex items-center px-3 py-2 border border-amber-300 text-sm font-medium rounded-md text-amber-700 bg-white hover:bg-amber-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                </svg>
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="mt-4 p-4 bg-amber-100 rounded-lg">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-amber-600 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="text-sm text-amber-800">
                        <p class="font-medium mb-1">Important Guidelines:</p>
                        <ul class="list-disc list-inside space-y-1">
                            <li>Review existing requests to avoid duplicate orders</li>
                            <li>Consider modifying existing pending requests instead of creating new ones</li>
                            <li>Multiple requests may cause inventory confusion and delays</li>
                            <li>Contact inventory team if you need to modify approved requests</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 flex items-center justify-between">
                <div class="text-sm text-amber-700">
                    <strong>Still need to create a new request?</strong> You can proceed below, but please ensure it's necessary.
                </div>
                <button onclick="toggleNewRequestForm()" id="toggleFormBtn" 
                        class="inline-flex items-center px-4 py-2 border border-amber-600 text-sm font-medium rounded-md text-amber-700 bg-white hover:bg-amber-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                    </svg>
                    Create New Request Anyway
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Material Request Form -->
<div id="materialRequestFormContainer" <?php echo !empty($existingRequests) ? 'style="display: none;"' : ''; ?>>
<form id="materialRequestForm" action="process-material-request.php" method="POST">
    <input type="hidden" name="site_id" value="<?php echo $siteId; ?>">
    <input type="hidden" name="survey_id" value="<?php echo $surveyId ?? ''; ?>">
    <input type="hidden" name="vendor_id" value="<?php echo $vendorId ?? ''; ?>">
    
    <!-- Request Details -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Request Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="request_date" class="block text-sm font-medium text-gray-700 mb-2">Request Date *</label>
                <input type="date" id="request_date" name="request_date" value="<?php echo date('Y-m-d'); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            <div>
                <label for="required_date" class="block text-sm font-medium text-gray-700 mb-2">Required Date *</label>
                <input type="date" id="required_date" name="required_date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            <div class="md:col-span-2">
                <label for="request_notes" class="block text-sm font-medium text-gray-700 mb-2">Request Notes</label>
                <textarea id="request_notes" name="request_notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Any special instructions or notes for this material request..."></textarea>
            </div>
        </div>
    </div>

    <!-- Material Items -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Material Items</h3>
            <div class="flex space-x-2">
                <div class="relative inline-block text-left">
                    <select id="boqSelector" onchange="loadBoqItems(this.value)" class="inline-flex justify-center w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <option value="">Load from BOQ Master...</option>
                        <?php foreach ($availableBoqs as $b): ?>
                            <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['boq_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="button" onclick="addMaterialItem()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                    </svg>
                    Add Item
                </button>
            </div>
        </div>
        
        <div id="materialItemsContainer">
            <!-- Material items will be added here dynamically -->
        </div>
        
        <div id="noItemsMessage" class="text-center py-8 text-gray-500">
            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
            <p>No material items added yet. Click "Add Item" to start building your request.</p>
        </div>
    </div>

    <!-- Survey-Based Recommendations (if survey exists) -->
    <?php if ($survey): ?>
    <div class="bg-blue-50 rounded-lg border border-blue-200 p-6 mb-8" style="display:none;">
        <h3 class="text-lg font-semibold text-blue-900 mb-4">Survey-Based Recommendations</h3>
        <p class="text-sm text-blue-700 mb-4">Based on your site survey, here are some recommended materials:</p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php if ($survey['total_cameras']): ?>
            <div class="bg-white p-4 rounded-lg border border-blue-200">
                <h4 class="font-medium text-blue-900">Cameras</h4>
                <p class="text-sm text-blue-700">Total cameras needed: <?php echo $survey['total_cameras']; ?></p>
                <p class="text-sm text-blue-700">Analytic cameras: <?php echo $survey['analytic_cameras'] ?? 'N/A'; ?></p>
                <button type="button" onclick="addRecommendedCameras()" class="mt-2 text-xs text-blue-600 hover:text-blue-800">Add to Request</button>
            </div>
            <?php endif; ?>
            
            <?php if ($survey['new_poe_rack'] === 'Yes'): ?>
            <div class="bg-white p-4 rounded-lg border border-blue-200">
                <h4 class="font-medium text-blue-900">POE Equipment</h4>
                <p class="text-sm text-blue-700">New POE rack required</p>
                <button type="button" onclick="addRecommendedPOE()" class="mt-2 text-xs text-blue-600 hover:text-blue-800">Add to Request</button>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Submit Section -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex justify-between items-center">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Submit Request</h3>
                <p class="text-sm text-gray-500 mt-1">Review your material request before submitting</p>
            </div>
            <div class="flex space-x-3">
                <button type="button" onclick="saveDraft()" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Save Draft
                </button>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    Submit Request
                </button>
            </div>
        </div>
    </div>
</form>
</div> <!-- End materialRequestFormContainer -->

<script>
let surveyLoaded = false;

function toggleSurveyDetails(siteId, type) {
    const container = document.getElementById('surveyDetailsContainer');
    const content = document.getElementById('surveyDetailsContent');
    const text = document.getElementById('toggleText');
    const icon = document.getElementById('toggleIcon');
    
    if (container.classList.contains('hidden')) {
        container.classList.remove('hidden');
        text.innerText = 'Collapse Technical Manifest';
        icon.classList.add('rotate-180');
        
        if (!surveyLoaded) {
            fetch(`api/get-survey-details-html.php?site_id=${siteId}&type=${type}`)
                .then(response => response.text())
                .then(html => {
                    content.innerHTML = html;
                    surveyLoaded = true;
                })
                .catch(error => {
                    content.innerHTML = '<p class="text-xs text-red-500 font-bold uppercase tracking-widest">Error fetching manifest intelligence.</p>';
                    console.error('Error:', error);
                });
        }
    } else {
        container.classList.add('hidden');
        text.innerText = 'Expand Detailed Technical Manifest';
        icon.classList.remove('rotate-180');
    }
}

let itemCounter = 0;
const boqItems = <?php echo json_encode($boqItems); ?>;

// Set minimum required date to tomorrow
document.getElementById('required_date').min = new Date(Date.now() + 86400000).toISOString().split('T')[0];

function toggleNewRequestForm() {
    const container = document.getElementById('materialRequestFormContainer');
    const toggleBtn = document.getElementById('toggleFormBtn');
    
    if (container.style.display === 'none') {
        container.style.display = 'block';
        toggleBtn.innerHTML = `
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
            Hide New Request Form
        `;
        // Scroll to form
        container.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else {
        container.style.display = 'none';
        toggleBtn.innerHTML = `
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
            </svg>
            Create New Request Anyway
        `;
    }
}

function addMaterialItem() {
    itemCounter++;
    const container = document.getElementById('materialItemsContainer');
    const noItemsMessage = document.getElementById('noItemsMessage');
    
    const itemHtml = `
        <div class="material-item border border-gray-200 rounded-lg p-4 mb-4" id="item_${itemCounter}">
            <div class="flex justify-between items-start mb-4">
                <h4 class="text-md font-medium text-gray-900">Material Item #${itemCounter}</h4>
                <button type="button" onclick="removeMaterialItem(${itemCounter})" class="text-red-600 hover:text-red-800">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">BOQ Item *</label>
                    <select name="items[${itemCounter}][boq_item_id]" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required onchange="updateItemDetails(${itemCounter}, this.value)">
                        <option value="">Select Item</option>
                        ${boqItems.map(item => `<option value="${item.id}" data-code="${item.item_code}" data-unit="${item.unit}">${item.item_name}</option>`).join('')}
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Item Code</label>
                    <input type="text" name="items[${itemCounter}][item_code]" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50" readonly>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quantity *</label>
                    <input type="number" name="items[${itemCounter}][quantity]" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
                    <input type="text" name="items[${itemCounter}][unit]" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50" readonly>
                </div>
                <div class="md:col-span-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <input type="text" name="items[${itemCounter}][notes]" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Any specific requirements or notes for this item...">
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', itemHtml);
    noItemsMessage.style.display = 'none';
    return itemCounter;
}

function loadBoqItems(boqId) {
    if (!boqId) return;
    
    window.showConfirmToast('Do you want to clear current items before loading BOQ items?', {
        confirmText: 'Clear & Load',
        cancelText: 'Keep & Append',
        onConfirm: () => {
            document.getElementById('materialItemsContainer').innerHTML = '';
            document.getElementById('noItemsMessage').style.display = 'block';
            executeBoqLoad(boqId);
        },
        onCancel: () => {
            executeBoqLoad(boqId);
        }
    });
}

function executeBoqLoad(boqId) {
    const selector = document.getElementById('boqSelector');
    selector.disabled = true;
    
    fetch(`boq/get-boq-details.php?id=${boqId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => { throw err; });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            data.items.forEach(item => {
                const id = addMaterialItem();
                const itemDiv = document.getElementById(`item_${id}`);
                const select = itemDiv.querySelector(`select[name*="[boq_item_id]"]`);
                select.value = item.boq_item_id;
                
                // Trigger detail update
                updateItemDetails(id, item.boq_item_id);
                
                // Set quantity and other fields
                setTimeout(() => {
                    const qtyInput = itemDiv.querySelector(`input[name*="[quantity]"]`);
                    if (qtyInput) qtyInput.value = item.quantity;
                    
                    if (item.notes) {
                        const noteInput = itemDiv.querySelector(`input[name*="[notes]"]`);
                        if (noteInput) noteInput.value = item.notes;
                    }
                }, 100);
            });
            showToast(`Successfully loaded ${data.items.length} items from BOQ`, 'success');
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast(error.message || 'Error loading BOQ details', 'error');
    })
    .finally(() => {
        selector.disabled = false;
        selector.value = '';
    });
}

function removeMaterialItem(itemId) {
    const item = document.getElementById(`item_${itemId}`);
    if (item) {
        item.remove();
        
        // Show no items message if no items left
        const container = document.getElementById('materialItemsContainer');
        if (container.children.length === 0) {
            document.getElementById('noItemsMessage').style.display = 'block';
        }
    }
}

function updateItemDetails(itemId, boqItemId) {
    const item = boqItems.find(item => item.id == boqItemId);
    if (item) {
        const itemContainer = document.getElementById(`item_${itemId}`);
        itemContainer.querySelector('input[name*="[item_code]"]').value = item.item_code || '';
        itemContainer.querySelector('input[name*="[unit]"]').value = item.unit || '';
    }
}

function addRecommendedCameras() {
    addMaterialItem();
    // Auto-select camera-related items if available
    const lastItem = document.querySelector('.material-item:last-child');
    const select = lastItem.querySelector('select');
    const cameraItem = boqItems.find(item => item.item_name.toLowerCase().includes('camera'));
    if (cameraItem) {
        select.value = cameraItem.id;
        updateItemDetails(itemCounter, cameraItem.id);
        lastItem.querySelector('input[name*="[quantity]"]').value = <?php echo $survey['total_cameras'] ?? 1; ?>;
    }
}

function addRecommendedPOE() {
    addMaterialItem();
    // Auto-select POE-related items if available
    const lastItem = document.querySelector('.material-item:last-child');
    const select = lastItem.querySelector('select');
    const poeItem = boqItems.find(item => item.item_name.toLowerCase().includes('poe') || item.item_name.toLowerCase().includes('rack'));
    if (poeItem) {
        select.value = poeItem.id;
        updateItemDetails(itemCounter, poeItem.id);
        lastItem.querySelector('input[name*="[quantity]"]').value = 1;
    }
}

function saveDraft() {
    const form = document.getElementById('materialRequestForm');
    const formData = new FormData(form);
    formData.append('save_draft', '1');
    
    fetch('process-material-request.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Draft saved successfully!');
        } else {
            alert('Error saving draft: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while saving the draft.');
    });
}

// Form submission
document.getElementById('materialRequestForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const container = document.getElementById('materialItemsContainer');
    if (container.children.length === 0) {
        showToast('Please add at least one material item before submitting.', 'error');
        return;
    }
    
    <?php if (!empty($existingRequests)): ?>
    // Show confirmation for duplicate request
    window.showConfirmToast('You are creating a new request while existing requests are present. Are you sure you want to proceed?', {
        confirmText: 'Yes, Create New Request',
        cancelText: 'Cancel',
        onConfirm: () => {
            submitMaterialRequest();
        },
        onCancel: () => {
            showToast('Request creation cancelled', 'info');
        }
    });
    <?php else: ?>
    submitMaterialRequest();
    <?php endif; ?>
});

function submitMaterialRequest() {
    const form = document.getElementById('materialRequestForm');
    const formData = new FormData(form);
    
    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = `
        <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Submitting...
    `;
    
    fetch('process-material-request.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Material request submitted successfully!', 'success');
            setTimeout(() => {
                window.location.href = 'sites/index.php';
            }, 2000);
        } else {
            showToast('Error submitting request: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while submitting the request.', 'error');
    })
    .finally(() => {
        // Restore button state
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

// Add initial item
addMaterialItem();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../includes/admin_layout.php';
?>