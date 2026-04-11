<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../models/Installation.php';

// Require vendor authentication
Auth::requireVendor();

$vendorId = Auth::getVendorId();
$installationId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$installationId) {
    header('Location: installations.php');
    exit;
}

$installationModel = new Installation();

// Get installation details and verify vendor access
$installation = $installationModel->getInstallationDetails($installationId);
if (!$installation || $installation['vendor_id'] != $vendorId) {
    header('Location: installations.php');
    exit;
}

require_once __DIR__ . '/../models/MaterialUsage.php';
require_once __DIR__ . '/../models/MaterialRequest.php';
require_once __DIR__ . '/../models/BoqItem.php';

$materialUsageModel = new MaterialUsage();
$materialRequestModel = new MaterialRequest();
$boqModel = new BoqItem();

// Always fetch fresh materials from material requests
$materialRequests = $materialRequestModel->findBySite($installation['site_id']);

$materialsToInitialize = [];

if (!empty($materialRequests)) {
    // Process each material request
    foreach ($materialRequests as $request) {
        // Only include approved or dispatched requests
        if (!in_array($request['status'], ['approved', 'dispatched', 'fulfilled', 'partially_fulfilled'])) {
            continue;
        }
        
        if ($request['items']) {
            $itemsData = json_decode($request['items'], true);
            if ($itemsData && is_array($itemsData)) {
                foreach ($itemsData as $item) {
                    // Get BOQ item details if available
                    $materialName = 'Unknown Item';
                    $materialUnit = 'Nos';
                    
                    if (isset($item['boq_item_id'])) {
                        $boqItem = $boqModel->find($item['boq_item_id']);
                        if ($boqItem) {
                            $materialName = $boqItem['item_name'];
                            $materialUnit = $boqItem['unit'];
                        }
                    } elseif (isset($item['material_name'])) {
                        $materialName = $item['material_name'];
                    } elseif (isset($item['item_name'])) {
                        $materialName = $item['item_name'];
                    }
                    
                    if (isset($item['unit'])) {
                        $materialUnit = $item['unit'];
                    }
                    
                    $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 0;
                    
                    if ($quantity > 0) {
                        // Check if material already exists in our list (to avoid duplicates)
                        $found = false;
                        foreach ($materialsToInitialize as &$existingMaterial) {
                            if ($existingMaterial['name'] === $materialName) {
                                $existingMaterial['total_qty'] += $quantity;
                                $found = true;
                                break;
                            }
                        }
                        
                        if (!$found) {
                            $materialsToInitialize[] = [
                                'name' => $materialName,
                                'total_qty' => $quantity,
                                'unit' => $materialUnit
                            ];
                        }
                    }
                }
            }
        }
    }
}

// Get existing materials from database
$materials = $materialUsageModel->getInstallationMaterials($installationId);

// If we have new materials from requests, update the database
if (!empty($materialsToInitialize)) {
    // Check if materials need to be updated (compare with existing)
    $needsUpdate = false;
    
    if (empty($materials)) {
        $needsUpdate = true;
    } else {
        // Check if material list has changed
        if (count($materials) !== count($materialsToInitialize)) {
            $needsUpdate = true;
        } else {
            // Check if any material names or quantities differ
            $existingMaterialNames = array_column($materials, 'material_name');
            $newMaterialNames = array_column($materialsToInitialize, 'name');
            
            if (array_diff($existingMaterialNames, $newMaterialNames) || array_diff($newMaterialNames, $existingMaterialNames)) {
                $needsUpdate = true;
            }
        }
    }
    
    // Update materials if needed
    if ($needsUpdate) {
        // Clear existing materials
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM installation_materials WHERE installation_id = ?");
        $stmt->execute([$installationId]);
        
        // Initialize with new materials
        $materialUsageModel->initializeInstallationMaterials($installationId, $materialsToInitialize);
        
        // Get the updated materials
        $materials = $materialUsageModel->getInstallationMaterials($installationId);
    }
}
// If no materials found, $materials will be empty array

// Get existing daily work
$dailyWork = $materialUsageModel->getDailyWorkByDay($installationId);

$title = 'Material Usage - Installation #' . $installationId;
ob_start();
?>

<style>
input, textarea, select {
    border: 1px solid #d1d5db !important;
}
</style>

<div class="flex justify-between items-center mb-4">
    <div>
        <h1 class="text-xl font-semibold text-gray-900">Material Usage - Installation #<?php echo $installationId; ?></h1>
        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($installation['site_code']); ?></p>
    </div>
    <a href="manage-installation.php?id=<?php echo $installationId; ?>" class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm rounded text-gray-700 bg-white hover:bg-gray-50">
        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
        </svg>
        Back
    </a>
</div>

<!-- Site Summary -->
<div class="bg-white rounded-lg border mb-4">
    <div class="px-4 py-2 bg-gray-50 border-b">
        <h3 class="text-sm font-medium text-gray-900">Site Info</h3>
    </div>
    <div class="p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Site</label>
                <input type="text" class="w-full text-sm border-gray-300 rounded bg-gray-50 px-3 py-2" 
                       value="<?php echo htmlspecialchars($installation['site_code']); ?>" readonly>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Engineer</label>
                <input type="text" id="engineer_name" class="w-full text-sm border-gray-300 rounded px-3 py-2" 
                       placeholder="Enter name">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Date</label>
                <input type="date" id="work_date" class="w-full text-sm border-gray-300 rounded px-3 py-2" 
                       value="<?php echo date('Y-m-d'); ?>">
            </div>
        </div>
    </div>
</div>

<!-- Material Summary -->
<div class="bg-white rounded-lg border mb-4">
    <div class="px-4 py-2 bg-blue-50 border-b">
        <h3 class="text-sm font-medium text-blue-900">Materials</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">#</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Material</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Unit</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Total</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Used</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Remaining</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200" id="materialTableBody">
                <?php if (empty($materials)): ?>
                <tr>
                    <td colspan="6" class="px-3 py-8 text-center">
                        <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No materials found</h3>
                        <p class="mt-1 text-xs text-gray-500">No materials have been assigned to this installation yet.</p>
                        <p class="mt-1 text-xs text-gray-500">Materials will be loaded from approved material requests.</p>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($materials as $index => $material): 
                    $remaining = $material['total_quantity'] - $material['used_quantity'];
                    $remainingClass = 'bg-green-100 text-green-800';
                    $showRequestBtn = false;
                    
                    if ($remaining <= 0) {
                        $remainingClass = 'bg-red-100 text-red-800';
                        $showRequestBtn = true;
                    } elseif ($remaining <= 5) {
                        $remainingClass = 'bg-yellow-100 text-yellow-800';
                        $showRequestBtn = true;
                    }
                ?>
                <tr data-material-id="<?php echo $material['id']; ?>">
                    <td class="px-3 py-2 text-gray-900"><?php echo $index + 1; ?></td>
                    <td class="px-3 py-2 font-medium text-gray-900"><?php echo htmlspecialchars($material['material_name']); ?></td>
                    <td class="px-3 py-2 text-gray-500"><?php echo $material['material_unit']; ?></td>
                    <td class="px-3 py-2">
                        <span class="totalQty px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                            <?php echo $material['total_quantity']; ?>
                        </span>
                    </td>
                    <td class="px-3 py-2">
                        <span class="usedQty px-2 py-1 rounded text-xs font-medium <?php echo $material['used_quantity'] > 0 ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-800'; ?>">
                            <?php echo $material['used_quantity']; ?>
                        </span>
                    </td>
                    <td class="px-3 py-2">
                        <div class="flex items-center space-x-1">
                            <span class="remainingQty px-2 py-1 rounded text-xs font-medium <?php echo $remainingClass; ?>">
                                <?php echo $remaining; ?>
                            </span>
                            <button type="button" class="requestMaterialBtn text-orange-600 hover:text-orange-800 <?php echo $showRequestBtn ? '' : 'hidden'; ?>" title="Request More">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Daily Work Progress -->
<div class="bg-white rounded-lg border mb-4">
    <div class="px-4 py-2 bg-green-50 border-b flex justify-between items-center">
        <h3 class="text-sm font-medium text-green-900">Daily Work</h3>
        <button type="button" id="addDayBtn" class="inline-flex items-center px-2 py-1 border border-green-300 text-xs rounded text-green-700 bg-white hover:bg-green-50">
            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
            </svg>
            Add Day
        </button>
    </div>
    <div class="p-4">
        <div id="daysContainer">
            <!-- Daily work entries will be added here -->
        </div>
    </div>
</div>


<!-- Material Request Modal -->
<div id="materialRequestModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Request Additional Material</h3>
                <button type="button" onclick="closeMaterialRequestModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
            
            <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-4">
                <div class="flex">
                    <svg class="w-5 h-5 text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <h4 class="text-sm font-medium text-yellow-800">Low Stock Alert</h4>
                        <p class="text-sm text-yellow-700 mt-1" id="stockAlertMessage"></p>
                    </div>
                </div>
            </div>
            
            <form id="materialRequestForm">
                <div class="space-y-4">
                    <div>
                        <label for="request_material_name" class="block text-sm font-medium text-gray-700">Material Name</label>
                        <input type="text" id="request_material_name" name="material_name" readonly
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100 sm:text-sm px-3 py-2">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="current_remaining" class="block text-sm font-medium text-gray-700">Current Remaining</label>
                            <input type="number" id="current_remaining" readonly
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100 sm:text-sm px-3 py-2">
                        </div>
                        <div>
                            <label for="request_quantity" class="block text-sm font-medium text-gray-700">Request Quantity</label>
                            <input type="number" id="request_quantity" name="request_quantity" min="1" required
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm px-3 py-2">
                        </div>
                    </div>
                    
                    <div>
                        <label for="request_priority" class="block text-sm font-medium text-gray-700">Priority</label>
                        <select id="request_priority" name="priority" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm px-3 py-2">
                            <option value="urgent">Urgent - Work Stopped</option>
                            <option value="high">High - Needed Today</option>
                            <option value="medium" selected>Medium - Needed This Week</option>
                            <option value="low">Low - Future Planning</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="request_reason" class="block text-sm font-medium text-gray-700">Reason for Request</label>
                        <textarea id="request_reason" name="reason" rows="3" required
                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm px-3 py-2"
                                  placeholder="Explain why additional material is needed..."></textarea>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeMaterialRequestModal()" 
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                        </svg>
                        Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Stock Alert Toast -->
<div id="stockAlertToast" class="fixed top-4 right-4 max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden hidden">
    <div class="p-4">
        <div class="flex items-start">
            <div class="flex-shrink-0" id="toastIcon">
                <!-- Icon will be inserted here -->
            </div>
            <div class="ml-3 w-0 flex-1 pt-0.5">
                <p class="text-sm font-medium text-gray-900" id="toastTitle"></p>
                <p class="mt-1 text-sm text-gray-500" id="toastMessage"></p>
            </div>
            <div class="ml-4 flex-shrink-0 flex">
                <button onclick="hideStockAlert()" class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Validation Errors Modal -->
<div id="validationErrorsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-4 border w-11/12 md:w-96 shadow-lg rounded-lg bg-white">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center">
                <svg class="h-5 w-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <h3 class="text-sm font-medium text-gray-900">Fix These Issues</h3>
            </div>
            <button type="button" onclick="closeValidationModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        
        <div class="bg-red-50 border border-red-200 rounded p-3 mb-3">
            <div class="space-y-2 text-sm" id="validationErrorsList">
                <!-- Validation errors will be populated here -->
            </div>
        </div>
        
        <div class="text-xs text-gray-600 mb-3">
            <strong>Tip:</strong> Upload individual material photos OR overall site photos with work description.
        </div>
        
        <div class="flex justify-end">
            <button type="button" onclick="closeValidationModal()" 
                    class="px-3 py-1.5 text-sm font-medium rounded text-white bg-red-600 hover:bg-red-700">
                Got it
            </button>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-4 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-lg bg-white">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center">
                <svg class="h-5 w-5 text-blue-500 mr-2" id="confirmIcon" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
                <h3 class="text-sm font-medium text-gray-900" id="confirmTitle">Confirm Action</h3>
            </div>
            <button type="button" onclick="closeConfirmModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        
        <div class="mb-4">
            <div class="text-sm text-gray-700" id="confirmMessage">Are you sure you want to proceed?</div>
        </div>
        
        <div class="flex justify-end space-x-2">
            <button type="button" onclick="closeConfirmModal()" 
                    class="px-3 py-1.5 text-sm font-medium rounded text-gray-700 bg-gray-100 hover:bg-gray-200">
                Cancel
            </button>
            <button type="button" onclick="materialConfirmAction()" 
                    class="px-3 py-1.5 text-sm font-medium rounded text-white bg-blue-600 hover:bg-blue-700" id="confirmButton">
                Confirm
            </button>
        </div>
    </div>
</div>

<script>
let dayCount = 0;
const materials = <?php echo json_encode(array_map(function($m) { 
    return [
        'id' => $m['id'], 
        'name' => $m['material_name'], 
        'unit' => $m['material_unit']
    ]; 
}, $materials)); ?>;

function renderDayBlock(day) {
    let materialRows = '';
    
    // Get materials from the main table - check if materials exist first
    const materialTableRows = document.querySelectorAll('#materialTableBody tr[data-material-id]');
    
    if (materialTableRows.length > 0) {
        materialTableRows.forEach(row => {
            const materialId = row.dataset.materialId;
            const materialNameCell = row.querySelector('td:nth-child(2)');
            const remainingQtySpan = row.querySelector('.remainingQty');
            
            // Safety check - ensure elements exist
            if (materialId && materialNameCell && remainingQtySpan) {
                const materialName = materialNameCell.textContent.trim();
                const remainingQty = parseFloat(remainingQtySpan.textContent.trim());
                
                materialRows += `
                    <tr class="text-sm">
                        <td class="px-2 py-1 text-gray-900">${materialName}</td>
                        <td class="px-2 py-1">
                            <input type="number" class="usedToday w-16 text-sm border-gray-300 rounded px-3 py-2" 
                                   value="0" min="0" max="${remainingQty}" data-material-id="${materialId}" data-remaining="${remainingQty}"
                                   title="Max: ${remainingQty}">
                        </td>
                        <td class="px-2 py-1">
                            <input type="file" class="materialPhoto text-xs border-gray-300 rounded w-full px-3 py-2" 
                                   accept="image/*,video/*" data-material-id="${materialId}" title="Optional photo">
                            <div class="materialPhotoPreview flex flex-wrap gap-1 mt-1"></div>
                        </td>
                    </tr>
                `;
            }
        });
    } else {
        // No materials available
        materialRows = `
            <tr>
                <td colspan="3" class="px-2 py-4 text-center text-sm text-gray-500">
                    No materials available for tracking
                </td>
            </tr>
        `;
    }

    const removeButton = day > 1 ? `
        <button type="button" class="removeDay px-2 py-1 text-xs rounded text-red-700 bg-red-100 hover:bg-red-200">
            <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>Remove
        </button>
    ` : '';

    return `
    <div class="border border-gray-200 rounded mb-3 day-block" data-day="${day}">
        <div class="bg-gray-50 px-3 py-2 border-b flex justify-between items-center">
            <div>
                <h4 class="text-sm font-medium text-gray-900">Day ${day}</h4>
                <p class="text-xs text-gray-600">${new Date().toLocaleDateString()}</p>
            </div>
            <div class="flex items-center space-x-2">
                ${day === 1 ? '<span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800">Primary</span>' : ''}
                ${removeButton}
            </div>
        </div>
        <div class="p-3">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Remarks:</label>
                    <textarea class="dayRemarks w-full text-sm border-gray-300 rounded px-3 py-2" 
                              rows="2" placeholder="Work remarks"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Report:</label>
                    <textarea class="dayReport w-full text-sm border-gray-300 rounded px-3 py-2" 
                              rows="2" placeholder="Detailed report"></textarea>
                </div>
            </div>

            <div class="mb-3">
                <h5 class="text-sm font-medium text-gray-900 mb-2">Materials Used</h5>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs border border-gray-200 rounded">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-2 py-1 text-left text-gray-500">Material</th>
                                <th class="px-2 py-1 text-left text-gray-500">Qty</th>
                                <th class="px-2 py-1 text-left text-gray-500">Photo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            ${materialRows}
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mb-3">
                <label class="block text-xs font-medium text-gray-700 mb-1">
                    Site Photos <span class="text-red-500">*</span>
                </label>
                <input type="file" class="siteFiles w-full text-sm border-gray-300 rounded px-3 py-2" 
                       multiple accept="image/*,video/*" required>
                <div class="preview mt-2 grid grid-cols-3 gap-1"></div>
            </div>

            <div class="mb-3 p-2 bg-blue-50 border border-blue-200 rounded text-xs">
                <strong>Requirements:</strong> Site photos are COMPULSORY for all submissions. Add remarks/report to describe work. For materials used: individual photos OR site photos with description.
            </div>

            <div class="flex justify-end">
                <button type="button" class="checkOutBtn px-3 py-1.5 text-sm rounded text-white bg-green-600 hover:bg-green-700">
                    <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    Check Out Day ${day}
                </button>
            </div>
        </div>
    </div>`;
}

// Initialize with existing data on page load
document.addEventListener('DOMContentLoaded', function() {
    loadExistingDailyWork();
});

// Add Day Button Click
document.getElementById('addDayBtn').addEventListener('click', function() {
    dayCount++;
    document.getElementById('daysContainer').insertAdjacentHTML('beforeend', renderDayBlock(dayCount));
});

// Remove Day Button Click
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('removeDay') || e.target.closest('.removeDay')) {
        const dayBlock = e.target.closest('.day-block');
        const dayNumber = parseInt(dayBlock.dataset.day);
        
        // Prevent removing Day 1
        if (dayNumber === 1) {
            showErrorToast('Day 1 cannot be removed as it is the primary work day.');
            return;
        }
        
        // Confirm removal for other days
        showConfirmModal(
            'Remove Day',
            `Are you sure you want to remove Day ${dayNumber}? This action cannot be undone.`,
            () => {
                dayBlock.remove();
                updateDayNumbers();
            },
            'Remove',
            'danger'
        );
    }
});

// Function to update day numbers after removal
function updateDayNumbers() {
    const dayBlocks = document.querySelectorAll('.day-block');
    dayBlocks.forEach((block, index) => {
        const newDayNumber = index + 1;
        block.dataset.day = newDayNumber;
        
        // Update the header text
        const header = block.querySelector('h4');
        header.textContent = `Day ${newDayNumber} Work Progress`;
        
        // Update check out button text if it exists
        const checkOutBtn = block.querySelector('.checkOutBtn');
        if (checkOutBtn && !checkOutBtn.disabled) {
            checkOutBtn.innerHTML = `
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
                Check Out Day ${newDayNumber}
            `;
        }
        
        // Update remove button visibility (hide for Day 1, show for others)
        const removeBtn = block.querySelector('.removeDay');
        if (removeBtn) {
            if (newDayNumber === 1) {
                removeBtn.style.display = 'none';
                // Add primary day badge if not exists
                const badgeContainer = block.querySelector('.flex.items-center.space-x-2');
                if (!badgeContainer.querySelector('.bg-green-100')) {
                    badgeContainer.insertAdjacentHTML('afterbegin', `
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Primary Day
                        </span>
                    `);
                }
            } else {
                removeBtn.style.display = 'inline-flex';
            }
        }
    });
    
    // Update global day count
    dayCount = dayBlocks.length;
}

// File Preview for both site files and material photos
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('siteFiles')) {
        let preview = e.target.nextElementSibling;
        preview.innerHTML = '';
        
        for (let file of e.target.files) {
            let reader = new FileReader();
            reader.onload = function(event) {
                if (file.type.startsWith('image')) {
                    preview.insertAdjacentHTML('beforeend', 
                        `<img src="${event.target.result}" class="rounded-lg shadow-sm w-full h-24 object-cover">`);
                } else if (file.type.startsWith('video')) {
                    preview.insertAdjacentHTML('beforeend', 
                        `<video src="${event.target.result}" controls class="rounded-lg shadow-sm w-full h-24"></video>`);
                }
            };
            reader.readAsDataURL(file);
        }
    }
    
    // Handle individual material photo previews
    if (e.target.classList.contains('materialPhoto')) {
        let preview = e.target.nextElementSibling;
        preview.innerHTML = '';
        
        for (let file of e.target.files) {
            let reader = new FileReader();
            reader.onload = function(event) {
                if (file.type.startsWith('image')) {
                    preview.insertAdjacentHTML('beforeend', 
                        `<img src="${event.target.result}" class="rounded shadow-sm w-12 h-12 object-cover" title="${file.name}">`);
                } else if (file.type.startsWith('video')) {
                    preview.insertAdjacentHTML('beforeend', 
                        `<video src="${event.target.result}" class="rounded shadow-sm w-12 h-12 object-cover" title="${file.name}">
                            <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center">
                                <svg class="w-4 h-4 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                         </video>`);
                }
            };
            reader.readAsDataURL(file);
        }
        
        // Add validation indicator
        const materialRow = e.target.closest('tr');
        const quantityInput = materialRow.querySelector('.usedToday');
        if (parseFloat(quantityInput.value) > 0 && e.target.files.length > 0) {
            quantityInput.classList.add('border-green-500');
            quantityInput.classList.remove('border-red-500');
        }
    }
});

// Add validation styling when quantity changes
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('usedToday')) {
        const materialRow = e.target.closest('tr');
        const photoInput = materialRow.querySelector('.materialPhoto');
        const quantity = parseFloat(e.target.value) || 0;
        const remainingQty = parseFloat(e.target.dataset.remaining) || 0;
        const materialName = materialRow.querySelector('td:first-child').textContent.trim();
        
        // Validate quantity doesn't exceed remaining
        if (quantity > remainingQty) {
            e.target.value = remainingQty;
            showStockAlert(materialName, `has only ${remainingQty} units remaining. Cannot use ${quantity} units`, 'error');
            e.target.classList.add('border-red-500');
            e.target.classList.remove('border-green-500');
            return;
        }
        
        if (quantity > 0) {
            // Material is being used, check if photo is provided
            if (photoInput.files.length > 0) {
                e.target.classList.add('border-green-500');
                e.target.classList.remove('border-red-500');
            } else {
                e.target.classList.add('border-red-500');
                e.target.classList.remove('border-green-500');
            }
        } else {
            // No material used, remove validation styling
            e.target.classList.remove('border-green-500', 'border-red-500');
        }
    }
});

// Material Summary is read-only and updated automatically through daily activities

// Update Remaining Quantity Function
function updateRemainingQuantity(row) {
    const total = parseFloat(row.querySelector('.totalQty').textContent) || 0;
    const used = parseFloat(row.querySelector('.usedQty').textContent) || 0;
    const remaining = total - used;
    
    const remainingSpan = row.querySelector('.remainingQty');
    const requestBtn = row.querySelector('.requestMaterialBtn');
    
    remainingSpan.textContent = remaining;
    
    // Update color and show/hide request button based on remaining quantity
    remainingSpan.className = 'remainingQty inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium';
    
    if (remaining > 5) {
        remainingSpan.classList.add('bg-green-100', 'text-green-800');
        requestBtn.classList.add('hidden');
    } else if (remaining > 0) {
        remainingSpan.classList.add('bg-yellow-100', 'text-yellow-800');
        requestBtn.classList.remove('hidden');
    } else {
        remainingSpan.classList.add('bg-red-100', 'text-red-800');
        requestBtn.classList.remove('hidden');
    }
}

// Request Material Button
document.addEventListener('click', function(e) {
    if (e.target.closest('.requestMaterialBtn')) {
        const row = e.target.closest('tr');
        const materialName = row.querySelector('td:nth-child(2)').textContent.trim();
        const remaining = parseInt(row.querySelector('.remainingQty').textContent);
        
        showMaterialRequestModal(materialName, remaining, row.dataset.materialId);
    }
});

// Check Out Day
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('checkOutBtn') || e.target.closest('.checkOutBtn')) {
        let dayBlock = e.target.closest('.day-block');
        let dayNumber = dayBlock.querySelector('h4').textContent.match(/Day (\d+)/)[1];
        
        // Validate material usage and documentation
        let validationErrors = [];
        let hasWork = false;
        let usedTodayInputs = dayBlock.querySelectorAll('.usedToday');
        let hasIndividualPhotos = false;
        let consumedMaterials = [];
        
        // Check each material usage
        usedTodayInputs.forEach(input => {
            const quantityUsed = parseFloat(input.value) || 0;
            const materialId = input.dataset.materialId;
            const materialRow = input.closest('tr');
            const materialName = materialRow.querySelector('td:first-child').textContent.trim();
            const materialPhoto = materialRow.querySelector('.materialPhoto');
            const hasPhoto = materialPhoto && materialPhoto.files.length > 0;
            
            if (quantityUsed > 0) {
                hasWork = true;
                consumedMaterials.push({
                    id: materialId,
                    name: materialName,
                    quantity: quantityUsed,
                    hasPhoto: hasPhoto
                });
                
                if (hasPhoto) {
                    hasIndividualPhotos = true;
                }
            }
        });
        
        // Validate consumed materials have documentation
        consumedMaterials.forEach(material => {
            if (!material.hasPhoto) {
                validationErrors.push(`${material.name}: Used ${material.quantity} units but no photo provided to show how it was used.`);
            }
        });
        
        // Check if overall site photos are provided
        const siteFiles = dayBlock.querySelector('.siteFiles');
        const hasSitePhotos = siteFiles && siteFiles.files.length > 0;
        
        // ALWAYS require site photos - this is compulsory for all cases
        if (!hasSitePhotos) {
            validationErrors.push('Site Photos are REQUIRED for all daily work submissions. Please upload at least one site photo to document the work progress.');
        }
        
        // Additional validation for materials consumed
        if (hasWork && !hasIndividualPhotos && !hasSitePhotos) {
            validationErrors.push('When using materials, you must upload either individual material photos OR overall site photos to document the work.');
        }
        
        // Check work description - always require either remarks OR report
        let remarks = dayBlock.querySelector('.dayRemarks').value.trim();
        let report = dayBlock.querySelector('.dayReport').value.trim();
        
        if (!remarks && !report) {
            if (hasWork) {
                validationErrors.push('Please add remarks or report to describe the work done with the materials.');
            } else {
                validationErrors.push('Please add either Remarks OR Report to describe what work was done today.');
            }
        }
        
        // Show validation errors
        if (validationErrors.length > 0) {
            showValidationErrors(validationErrors);
            return;
        }
        
        // Enhanced material consumption validation
        let materialValidationErrors = [];
        let lowStockMaterials = [];
        
        consumedMaterials.forEach(material => {
            const mainRow = document.querySelector(`tr[data-material-id="${material.id}"]`);
            if (mainRow) {
                const totalQty = parseFloat(mainRow.querySelector('.totalQty').textContent) || 0;
                const currentUsed = parseFloat(mainRow.querySelector('.usedQty').textContent) || 0;
                const remaining = totalQty - (currentUsed + material.quantity);
                
                // Check for insufficient stock
                if (remaining < 0) {
                    materialValidationErrors.push(`${material.name}: Insufficient stock! You're trying to use ${material.quantity} but only ${totalQty - currentUsed} remaining. Please generate a material request or reduce usage.`);
                } else if (remaining <= 2) {
                    lowStockMaterials.push({
                        name: material.name,
                        remaining: remaining,
                        status: 'out_of_stock'
                    });
                } else if (remaining <= 2) {
                    lowStockMaterials.push({
                        name: material.name,
                        remaining: remaining,
                        status: 'low_stock'
                    });
                }
            }
        });
        
        // Show material request suggestions
        if (lowStockMaterials.length > 0) {
            let stockMessage = 'Material Stock Alert:\n\n';
            lowStockMaterials.forEach(material => {
                if (material.status === 'out_of_stock') {
                    stockMessage += `⚠️ ${material.name}: OUT OF STOCK (${material.remaining} remaining)\n`;
                } else {
                    stockMessage += `⚠️ ${material.name}: LOW STOCK (${material.remaining} remaining)\n`;
                }
            });
            stockMessage += '\nDo you want to generate material requests for these items after checkout?';
            
            showConfirmModal(
                'Material Stock Alert',
                stockMessage,
                () => {
                    // Store materials for request generation after checkout
                    window.pendingMaterialRequests = lowStockMaterials;
                },
                'Generate Requests',
                'warning'
            );
        }
        
        // Show material consumption summary and low stock warnings
        let confirmMessageHTML = `
            <div class="space-y-3">
                <div class="text-sm font-medium text-gray-900">Day ${dayNumber} Checkout Summary</div>
        `;
        
        if (consumedMaterials.length > 0) {
            confirmMessageHTML += `
                <div>
                    <div class="text-xs font-medium text-gray-700 mb-2">Materials Consumed:</div>
                    <div class="bg-gray-50 rounded border">
                        <table class="w-full text-xs">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-2 py-1 text-left text-gray-600">Material</th>
                                    <th class="px-2 py-1 text-right text-gray-600">Quantity</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
            `;
            
            consumedMaterials.forEach(material => {
                confirmMessageHTML += `
                    <tr>
                        <td class="px-2 py-1 text-gray-900">${material.name}</td>
                        <td class="px-2 py-1 text-right text-gray-900 font-medium">${material.quantity} units</td>
                    </tr>
                `;
            });
            
            confirmMessageHTML += `
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
        }
        
        if (lowStockMaterials.length > 0) {
            confirmMessageHTML += `
                <div>
                    <div class="text-xs font-medium text-red-700 mb-2">⚠️ Low Stock Warning:</div>
                    <div class="bg-red-50 border border-red-200 rounded">
                        <table class="w-full text-xs">
                            <thead class="bg-red-100">
                                <tr>
                                    <th class="px-2 py-1 text-left text-red-700">Material</th>
                                    <th class="px-2 py-1 text-right text-red-700">Remaining</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-red-200">
            `;
            
            lowStockMaterials.forEach(material => {
                confirmMessageHTML += `
                    <tr>
                        <td class="px-2 py-1 text-red-900">${material.name}</td>
                        <td class="px-2 py-1 text-right text-red-900 font-medium">${material.remaining} units</td>
                    </tr>
                `;
            });
            
            confirmMessageHTML += `
                            </tbody>
                        </table>
                    </div>
                    <div class="text-xs text-red-600 mt-1">Consider generating material requests for these items.</div>
                </div>
            `;
        }
        
        confirmMessageHTML += `
                <div class="text-xs text-gray-600 bg-yellow-50 border border-yellow-200 rounded p-2">
                    <strong>⚠️ Warning:</strong> This will update your main material inventory and cannot be undone.
                </div>
                <div class="text-sm text-gray-700">Proceed with checkout?</div>
            </div>
        `;
        
        showConfirmModalHTML(
            'Checkout Confirmation',
            confirmMessageHTML,
            function() {
                // Proceed with checkout
                proceedWithCheckout(dayNumber, consumedMaterials);
            },
            'Proceed',
            'info'
        );
        
        function proceedWithCheckout(dayNumber, consumedMaterials) {
            // First save the daily work
            saveDailyWork(dayNumber)
            .then(result => {
                console.log('Save daily work result:', result);
                
                if (!result.success) {
                    throw new Error(result.message);
                }
                
                showSuccessToast('Success: ' + result.message + '\n\nUploaded ' + (result.uploaded_files ? result.uploaded_files.length : 0) + ' files');
                
                // Then checkout the day
                return fetch('process-material-usage.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'checkout_day',
                        installation_id: <?php echo $installationId; ?>,
                        day_number: dayNumber
                    })
                });
            })
            .then(response => response.json())
            .then(data => {
                console.log('Checkout result:', data);
                
                if (!data.success) {
                    throw new Error(data.message);
                }
                
                // Handle pending material requests
                if (window.pendingMaterialRequests && window.pendingMaterialRequests.length > 0) {
                    handlePendingMaterialRequests();
                } else {
                    // Check for critically low stock and suggest material requests
                    let criticallyLowStock = [];
                    consumedMaterials.forEach(material => {
                        const mainRow = document.querySelector(`tr[data-material-id="${material.id}"]`);
                        if (mainRow) {
                            const totalQty = parseFloat(mainRow.querySelector('.totalQty').textContent) || 0;
                            const currentUsed = parseFloat(mainRow.querySelector('.usedQty').textContent) || 0;
                            const remaining = totalQty - (currentUsed + material.quantity);
                            
                            if (remaining <= 0) {
                                criticallyLowStock.push(material.name);
                            }
                        }
                    });
                    
                    let successMessage = `Day ${dayNumber} work has been checked out successfully! Material quantities have been updated.`;
                    
                    if (criticallyLowStock.length > 0) {
                        successMessage += `\n\nCRITICAL: The following materials are now out of stock:\n• ${criticallyLowStock.join('\n• ')}\n\nPlease generate material requests immediately to avoid work delays.`;
                    }
                    
                    showSuccessToast(successMessage);
                    
                    // Ask user if they want to reload
                    setTimeout(() => {
                        showConfirmModal(
                            'Reload Page',
                            'Would you like to reload the page to see updated data?',
                            () => location.reload(),
                            'Reload',
                            'info'
                        );
                    }, 2000);
                }
            })
            .catch(error => {
                console.error('Error checking out day:', error);
                showErrorToast('Error: ' + error.message);
            });
            
            // Mark day as checked out
            const dayBlock = document.querySelector(`[data-day="${dayNumber}"]`);
            let checkOutBtn = dayBlock.querySelector('.checkOutBtn');
            checkOutBtn.innerHTML = `
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
                Day ${dayNumber} Checked Out
            `;
            checkOutBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
            checkOutBtn.classList.add('bg-gray-600', 'cursor-not-allowed');
            checkOutBtn.disabled = true;
            
            // Disable inputs in this day block
            dayBlock.querySelectorAll('input, textarea').forEach(input => {
                input.disabled = true;
                input.classList.add('bg-gray-100');
            });
        }
    }
});

// Material Request Modal Functions
function showMaterialRequestModal(materialName, remaining, materialId) {
    document.getElementById('request_material_name').value = materialName;
    document.getElementById('current_remaining').value = remaining;
    document.getElementById('request_quantity').value = Math.max(1, Math.abs(remaining) + 5); // Suggest quantity
    
    let alertMessage = '';
    if (remaining <= 0) {
        alertMessage = `${materialName} is out of stock. Work may be delayed without immediate replenishment.`;
    } else {
        alertMessage = `${materialName} has only ${remaining} units remaining. Consider requesting more to avoid work stoppage.`;
    }
    document.getElementById('stockAlertMessage').textContent = alertMessage;
    
    document.getElementById('materialRequestModal').classList.remove('hidden');
}

function closeMaterialRequestModal() {
    document.getElementById('materialRequestModal').classList.add('hidden');
    document.getElementById('materialRequestForm').reset();
}

// Stock Alert Toast Functions
function showStockAlert(materialName, alertType, severity) {
    const toast = document.getElementById('stockAlertToast');
    const icon = document.getElementById('toastIcon');
    const title = document.getElementById('toastTitle');
    const message = document.getElementById('toastMessage');
    
    // Set icon and colors based on severity
    if (severity === 'error') {
        icon.innerHTML = `
            <svg class="w-6 h-6 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
        `;
        title.textContent = 'Material Out of Stock';
    } else {
        icon.innerHTML = `
            <svg class="w-6 h-6 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
        `;
        title.textContent = 'Low Stock Warning';
    }
    
    message.textContent = `${materialName} ${alertType}. Consider requesting additional material.`;
    
    toast.classList.remove('hidden');
    
    // Auto hide after 5 seconds
    setTimeout(() => {
        hideStockAlert();
    }, 5000);
}

function hideStockAlert() {
    document.getElementById('stockAlertToast').classList.add('hidden');
}

// Validation Modal Functions
function showValidationErrors(errors) {
    const modal = document.getElementById('validationErrorsModal');
    const errorsList = document.getElementById('validationErrorsList');
    
    // Clear previous errors
    errorsList.innerHTML = '';
    
    // Add each error as a list item
    errors.forEach((error, index) => {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'flex items-start text-red-700';
        errorDiv.innerHTML = `
            <span class="font-medium mr-2">${index + 1}.</span>
            <span>${error}</span>
        `;
        errorsList.appendChild(errorDiv);
    });
    
    // Show modal
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeValidationModal() {
    const modal = document.getElementById('validationErrorsModal');
    modal.classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Confirmation Modal Functions - Isolated from admin.js conflicts
let materialConfirmCallback = null;

function showConfirmModal(title, message, callback, buttonText = 'Confirm', type = 'info') {
    const modal = document.getElementById('confirmationModal');
    const titleEl = document.getElementById('confirmTitle');
    const messageEl = document.getElementById('confirmMessage');
    const buttonEl = document.getElementById('confirmButton');
    const iconEl = document.getElementById('confirmIcon');
    
    titleEl.textContent = title;
    messageEl.textContent = message;
    buttonEl.textContent = buttonText;
    materialConfirmCallback = callback;
    
    // Set icon based on type
    if (type === 'warning') {
        iconEl.innerHTML = `
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
        `;
        iconEl.className = 'h-5 w-5 text-yellow-500 mr-2';
        buttonEl.className = 'px-3 py-1.5 text-sm font-medium rounded text-white bg-yellow-600 hover:bg-yellow-700';
    } else {
        iconEl.innerHTML = `
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
        `;
        iconEl.className = 'h-5 w-5 text-blue-500 mr-2';
        buttonEl.className = 'px-3 py-1.5 text-sm font-medium rounded text-white bg-blue-600 hover:bg-blue-700';
    }
    
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

// HTML version of confirm modal for rich content
function showConfirmModalHTML(title, htmlContent, callback, buttonText = 'Confirm', type = 'info') {
    const modal = document.getElementById('confirmationModal');
    const titleEl = document.getElementById('confirmTitle');
    const messageEl = document.getElementById('confirmMessage');
    const buttonEl = document.getElementById('confirmButton');
    const iconEl = document.getElementById('confirmIcon');
    
    titleEl.textContent = title;
    messageEl.innerHTML = htmlContent; // Use innerHTML for HTML content
    buttonEl.textContent = buttonText;
    materialConfirmCallback = callback;
    
    // Set icon based on type
    if (type === 'warning') {
        iconEl.innerHTML = `
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
        `;
        iconEl.className = 'h-5 w-5 text-yellow-500 mr-2';
        buttonEl.className = 'px-3 py-1.5 text-sm font-medium rounded text-white bg-yellow-600 hover:bg-yellow-700';
    } else {
        iconEl.innerHTML = `
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
        `;
        iconEl.className = 'h-5 w-5 text-blue-500 mr-2';
        buttonEl.className = 'px-3 py-1.5 text-sm font-medium rounded text-white bg-blue-600 hover:bg-blue-700';
    }
    
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeConfirmModal() {
    const modal = document.getElementById('confirmationModal');
    modal.classList.add('hidden');
    document.body.style.overflow = 'auto';
    materialConfirmCallback = null;
}

// Use a unique function name to avoid conflicts with admin.js
function materialConfirmAction() {
    if (materialConfirmCallback && typeof materialConfirmCallback === 'function') {
        materialConfirmCallback();
    }
    closeConfirmModal();
}

// Success and Error Toast Functions
function showSuccessToast(message) {
    showToast(message, 'success');
}

function showErrorToast(message) {
    showToast(message, 'error');
}

function showToast(message, type) {
    const toast = document.getElementById('stockAlertToast');
    const icon = document.getElementById('toastIcon');
    const title = document.getElementById('toastTitle');
    const messageEl = document.getElementById('toastMessage');
    
    // Set content based on type
    if (type === 'success') {
        title.textContent = 'Success';
        messageEl.textContent = message;
        icon.innerHTML = `
            <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        `;
        toast.className = 'fixed top-4 right-4 max-w-sm w-full bg-green-50 border border-green-200 shadow-lg rounded-lg pointer-events-auto ring-1 ring-green-200 ring-opacity-5 overflow-hidden';
    } else if (type === 'error') {
        title.textContent = 'Error';
        messageEl.textContent = message;
        icon.innerHTML = `
            <svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        `;
        toast.className = 'fixed top-4 right-4 max-w-sm w-full bg-red-50 border border-red-200 shadow-lg rounded-lg pointer-events-auto ring-1 ring-red-200 ring-opacity-5 overflow-hidden';
    }
    
    // Show toast
    toast.classList.remove('hidden');
    
    // Auto hide after 5 seconds
    setTimeout(() => {
        hideStockAlert();
    }, 5000);
}

// Refresh materials from material requests
function refreshMaterials() {
    showConfirmModal(
        'Refresh Materials',
        'This will replace all current materials with items from approved material requests. Continue?',
        () => {
            location.reload();
        },
        'Refresh',
        'warning'
    );
}

// Handle pending material requests after checkout
function handlePendingMaterialRequests() {
    if (!window.pendingMaterialRequests || window.pendingMaterialRequests.length === 0) {
        showSuccessToast('Day checked out successfully! Material quantities have been updated.');
        setTimeout(() => location.reload(), 2000);
        return;
    }
    
    const material = window.pendingMaterialRequests.shift(); // Get first material
    const suggestedQty = material.status === 'out_of_stock' ? Math.abs(material.remaining) + 10 : 10;
    
    // Show material request modal for this material
    document.getElementById('request_material_name').value = material.name;
    document.getElementById('current_remaining').value = material.remaining;
    document.getElementById('request_quantity').value = suggestedQty;
    document.getElementById('request_priority').value = material.status === 'out_of_stock' ? 'urgent' : 'high';
    document.getElementById('request_reason').value = `Material ${material.status === 'out_of_stock' ? 'out of stock' : 'running low'} after Day work completion. Need replenishment to continue installation work.`;
    
    let alertMessage = `${material.name} is ${material.status === 'out_of_stock' ? 'out of stock' : 'running low'} (${material.remaining} remaining). `;
    alertMessage += material.status === 'out_of_stock' ? 'Work may be delayed without immediate replenishment.' : 'Consider requesting more to avoid work stoppage.';
    document.getElementById('stockAlertMessage').textContent = alertMessage;
    
    document.getElementById('materialRequestModal').classList.remove('hidden');
}

// Material Request Form Submission
document.getElementById('materialRequestForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const requestData = {
        installation_id: <?php echo $installationId; ?>,
        material_name: formData.get('material_name'),
        request_quantity: formData.get('request_quantity'),
        priority: formData.get('priority'),
        reason: formData.get('reason'),
        current_remaining: document.getElementById('current_remaining').value
    };
    
    // Submit material request to database
    fetch('submit-material-request.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccessToast(`Material request submitted successfully!\n\nRequest ID: ${data.request_id}\nMaterial: ${data.data.material_name}\nQuantity: ${data.data.quantity}\nPriority: ${data.data.priority}\nStatus: ${data.data.status}\n\nYour request will be processed by the admin team.`);
            
            closeMaterialRequestModal();
            
            // Check if there are more pending requests
            if (window.pendingMaterialRequests && window.pendingMaterialRequests.length > 0) {
                setTimeout(() => {
                    handlePendingMaterialRequests();
                }, 500);
            } else {
                // All requests handled, reload page
                showSuccessToast('All material requests submitted successfully. They are now visible in the admin requests panel.');
                setTimeout(() => location.reload(), 2000);
            }
        } else {
            showErrorToast('Error submitting request: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorToast('An error occurred while submitting the material request. Please try again.');
    });
});

// Load existing daily work from database
function loadExistingDailyWork() {
    // First, load the attachments
    fetch('get-daily-work-attachments.php?installation_id=<?php echo $installationId; ?>')
    .then(response => response.json())
    .then(attachmentData => {
        window.dailyWorkAttachments = attachmentData.success ? attachmentData.attachments : {};
        
        // Then load the daily work data
        return fetch('process-material-usage.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'get_daily_work',
                installation_id: <?php echo $installationId; ?>
            })
        });
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.daily_work.length > 0) {
                // Load existing days
                data.daily_work.forEach(day => {
                    dayCount = Math.max(dayCount, day.day_number);
                    loadExistingDay(day);
                });
            } else {
                // Add Day 1 if no existing work
                dayCount = 1;
                document.getElementById('daysContainer').insertAdjacentHTML('beforeend', renderDayBlock(dayCount));
            }
        } else {
            console.error('Failed to load daily work:', data.message);
            // Add Day 1 as fallback
            dayCount = 1;
            document.getElementById('daysContainer').insertAdjacentHTML('beforeend', renderDayBlock(dayCount));
        }
    })
    .catch(error => {
        console.error('Error loading daily work:', error);
        // Add Day 1 as fallback
        dayCount = 1;
        document.getElementById('daysContainer').insertAdjacentHTML('beforeend', renderDayBlock(dayCount));
    });
}

// Load existing day data
function loadExistingDay(dayData) {
    const dayBlock = renderDayBlock(dayData.day_number);
    document.getElementById('daysContainer').insertAdjacentHTML('beforeend', dayBlock);
    
    // Get the newly added day block
    const addedBlock = document.querySelector(`[data-day="${dayData.day_number}"]`);
    
    // Fill in the data
    if (dayData.engineer_name) {
        document.getElementById('engineer_name').value = dayData.engineer_name;
    }
    if (dayData.work_date) {
        document.getElementById('work_date').value = dayData.work_date;
    }
    if (dayData.remarks) {
        addedBlock.querySelector('.dayRemarks').value = dayData.remarks;
    }
    if (dayData.work_report) {
        addedBlock.querySelector('.dayReport').value = dayData.work_report;
    }
    
    // Fill in material usage
    if (dayData.materials) {
        dayData.materials.forEach(material => {
            const input = addedBlock.querySelector(`[data-material-id="${material.material_id}"]`);
            if (input) {
                input.value = material.quantity_used;
            }
        });
    }
    
    // If day is checked out, disable it
    if (dayData.is_checked_out) {
        const checkOutBtn = addedBlock.querySelector('.checkOutBtn');
        if (checkOutBtn) {
            checkOutBtn.innerHTML = `
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
                Day ${dayData.day_number} Checked Out
            `;
            checkOutBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
            checkOutBtn.classList.add('bg-gray-600', 'cursor-not-allowed');
            checkOutBtn.disabled = true;
            
            // Disable inputs
            addedBlock.querySelectorAll('input, textarea').forEach(input => {
                input.disabled = true;
                input.classList.add('bg-gray-100');
            });
            
            // Add checked out timestamp
            const header = addedBlock.querySelector('.bg-blue-50');
            if (dayData.checked_out_at) {
                // Format time in India timezone (Asia/Kolkata)
                const checkoutDate = new Date(dayData.checked_out_at);
                const checkoutTime = checkoutDate.toLocaleTimeString('en-IN', { 
                    timeZone: 'Asia/Kolkata',
                    hour: '2-digit', 
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true 
                });
                header.insertAdjacentHTML('beforeend', `
                    <div class="text-xs text-gray-600 mt-1">
                        <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Checked out at ${checkoutTime}
                    </div>
                `);
            }
        }
    }
    
    // Load attachments for this day
    if (window.dailyWorkAttachments && window.dailyWorkAttachments[dayData.day_number]) {
        const dayAttachments = window.dailyWorkAttachments[dayData.day_number];
        
        // Load site photos
        if (dayAttachments.site && dayAttachments.site.length > 0) {
            const preview = addedBlock.querySelector('.siteFiles').nextElementSibling;
            if (preview && preview.classList.contains('preview')) {
                dayAttachments.site.forEach(file => {
                    const baseUrl = '<?php echo BASE_URL; ?>';
                    const fileUrl = baseUrl + '/' + file.file_path;
                    
                    if (file.file_type === 'video') {
                        preview.insertAdjacentHTML('beforeend', `
                            <div class="relative">
                                <video src="${fileUrl}" class="w-full h-32 object-cover rounded" controls></video>
                                <div class="text-xs text-gray-500 mt-1 truncate">${file.original_name}</div>
                            </div>
                        `);
                    } else {
                        preview.insertAdjacentHTML('beforeend', `
                            <div class="relative">
                                <img src="${fileUrl}" class="w-full h-32 object-cover rounded" alt="${file.original_name}">
                                <div class="text-xs text-gray-500 mt-1 truncate">${file.original_name}</div>
                            </div>
                        `);
                    }
                });
            }
        }
        
        // Load material photos
        if (dayAttachments.material) {
            Object.keys(dayAttachments.material).forEach(materialId => {
                const files = dayAttachments.material[materialId];
                const materialRow = addedBlock.querySelector(`[data-material-id="${materialId}"]`)?.closest('tr');
                if (materialRow) {
                    const preview = materialRow.querySelector('.materialPhotoPreview');
                    if (preview) {
                        files.forEach(file => {
                            const baseUrl = '<?php echo BASE_URL; ?>';
                            const fileUrl = baseUrl + '/' + file.file_path;
                            
                            if (file.file_type === 'video') {
                                preview.insertAdjacentHTML('beforeend', `
                                    <video src="${fileUrl}" class="w-16 h-16 object-cover rounded" controls></video>
                                `);
                            } else {
                                preview.insertAdjacentHTML('beforeend', `
                                    <img src="${fileUrl}" class="w-16 h-16 object-cover rounded" alt="${file.original_name}">
                                `);
                            }
                        });
                    }
                }
            });
        }
    }
}

// Save daily work to database with files
function saveDailyWork(dayNumber) {
    const dayBlock = document.querySelector(`[data-day="${dayNumber}"]`);
    if (!dayBlock) return Promise.reject({success: false, message: 'Day block not found'});
    
    const formData = new FormData();
    formData.append('action', 'save_daily_work_with_files');
    formData.append('installation_id', <?php echo $installationId; ?>);
    formData.append('day_number', dayNumber);
    formData.append('work_date', document.getElementById('work_date').value);
    formData.append('engineer_name', document.getElementById('engineer_name').value);
    formData.append('remarks', dayBlock.querySelector('.dayRemarks').value);
    formData.append('report', dayBlock.querySelector('.dayReport').value);
    
    // Collect material usage with validation
    const materialUsage = [];
    let hasQuantityError = false;
    let errorMessage = '';
    
    dayBlock.querySelectorAll('.usedToday').forEach(input => {
        if (input.value > 0) {
            const quantityUsed = parseFloat(input.value);
            const remainingQty = parseFloat(input.dataset.remaining) || 0;
            
            // Validate quantity doesn't exceed remaining
            if (quantityUsed > remainingQty) {
                hasQuantityError = true;
                const materialName = input.closest('tr').querySelector('td:first-child').textContent.trim();
                errorMessage += `${materialName}: Cannot use ${quantityUsed} units (only ${remainingQty} available)\n`;
                return;
            }
            
            materialUsage.push({
                material_id: input.dataset.materialId,
                quantity_used: quantityUsed
            });
        }
    });
    
    // Check for quantity errors before proceeding
    if (hasQuantityError) {
        return Promise.reject({
            success: false, 
            message: 'Material quantity validation failed:\n' + errorMessage
        });
    }
    
    formData.append('material_usage', JSON.stringify(materialUsage));
    
    console.log('Material usage:', materialUsage);
    
    // Add site files
    const siteFiles = dayBlock.querySelector('.siteFiles');
    let siteFileCount = 0;
    if (siteFiles && siteFiles.files.length > 0) {
        for (let i = 0; i < siteFiles.files.length; i++) {
            formData.append('site_files[]', siteFiles.files[i]);
            siteFileCount++;
        }
    }
    console.log('Site files count:', siteFileCount);
    
    // Add individual material photos
    let materialPhotoCount = 0;
    dayBlock.querySelectorAll('.materialPhoto').forEach(input => {
        if (input.files.length > 0) {
            const materialId = input.dataset.materialId;
            for (let i = 0; i < input.files.length; i++) {
                formData.append(`material_photos[${materialId}][]`, input.files[i]);
                materialPhotoCount++;
            }
        }
    });
    console.log('Material photos count:', materialPhotoCount);
    console.log('Total files to upload:', siteFileCount + materialPhotoCount);
    
    return fetch('process-daily-work-with-files.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        return data;
    })
    .catch(error => {
        console.error('Fetch error:', error);
        return {success: false, message: error.message};
    });
}

// Save Progress Function
function saveProgress() {
    const promises = [];
    
    // Save all day blocks
    document.querySelectorAll('.day-block').forEach((dayBlock, index) => {
        const dayNumber = parseInt(dayBlock.dataset.day);
        promises.push(saveDailyWork(dayNumber));
    });
    
    Promise.all(promises)
    .then(results => {
        const allSuccessful = results.every(result => result.success);
        if (allSuccessful) {
            showSuccessToast('Progress saved successfully!');
        } else {
            showErrorToast('Some data could not be saved. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error saving progress:', error);
        showErrorToast('An error occurred while saving progress.');
    });
}
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../includes/vendor_layout.php';
?>