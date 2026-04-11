<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/Site.php';
require_once __DIR__ . '/../models/SiteSurvey.php';
require_once __DIR__ . '/../models/BoqItem.php';

// Require vendor authentication
Auth::requireVendor();

$vendorId = Auth::getVendorId();
$siteId = $_GET['site_id'] ?? null;
$surveyId = $_GET['survey_id'] ?? null;

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
    include __DIR__ . '/../includes/vendor_layout.php';
    exit;
}

// Get site details
$siteModel = new Site();
$site = $siteModel->find($siteId);

if (!$site) {
    header('Location: sites/index.php');
    exit;
}

// Get survey details - always try to find the survey for this site and vendor
$surveyModel = new SiteSurvey();
$survey = $surveyModel->findBySiteAndVendor($siteId, $vendorId);

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

<!-- Site Information -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Site Information</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Site ID</label>
            <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo htmlspecialchars($site['site_id'] ?? 'N/A'); ?></p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
            <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo htmlspecialchars($site['location'] ?? 'N/A'); ?></p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Survey Status</label>
            <?php if ($survey): ?>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    Survey Completed
                </span>
            <?php else: ?>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    No Survey Found
                </span>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Material Request Form -->
<form id="materialRequestForm" action="process-material-request.php" method="POST">
    <input type="hidden" name="site_id" value="<?php echo $siteId; ?>">
    <input type="hidden" name="survey_id" value="<?php echo $survey['id'] ?? ''; ?>">
    
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
            <button type="button" onclick="addMaterialItem()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                </svg>
                Add Item
            </button>
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

<script>
let itemCounter = 0;
const boqItems = <?php echo json_encode($boqItems); ?>;

// Set minimum required date to tomorrow
document.getElementById('required_date').min = new Date(Date.now() + 86400000).toISOString().split('T')[0];

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
        alert('Please add at least one material item before submitting.');
        return;
    }
    
    const formData = new FormData(this);
    
    fetch('process-material-request.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Material request submitted successfully!');
            window.location.href = 'sites/index.php';
        } else {
            alert('Error submitting request: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while submitting the request.');
    });
});

// Add initial item
addMaterialItem();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../includes/vendor_layout.php';
?>