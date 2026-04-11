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

$materialUsageModel = new MaterialUsage();

// Get existing materials or initialize with sample data
$materials = $materialUsageModel->getInstallationMaterials($installationId);

if (empty($materials)) {
    // Initialize with sample materials (in real implementation, this would come from BOQ/material dispatch)
    $sampleMaterials = [
        ['name' => '6U Rack with One Additional Tray', 'total_qty' => 2, 'unit' => 'Nos'],
        ['name' => '12U Rack with One Additional Tray', 'total_qty' => 1, 'unit' => 'Nos'],
        ['name' => '24 Port Patch Panels', 'total_qty' => 4, 'unit' => 'Nos'],
        ['name' => 'Patch Panel Labeling', 'total_qty' => 100, 'unit' => 'Labels'],
        ['name' => 'Cable Manager', 'total_qty' => 6, 'unit' => 'Nos'],
        ['name' => '1m Patch Cord', 'total_qty' => 50, 'unit' => 'Nos'],
        ['name' => '5m Patch Cord', 'total_qty' => 20, 'unit' => 'Nos'],
        ['name' => 'I/O Box Kit - Face Plate', 'total_qty' => 10, 'unit' => 'Nos'],
        ['name' => 'Cat6 Cable (305m)', 'total_qty' => 2, 'unit' => 'Boxes'],
        ['name' => 'RJ45 Connectors', 'total_qty' => 100, 'unit' => 'Nos']
    ];
    
    // Initialize materials in database
    $materialUsageModel->initializeInstallationMaterials($installationId, $sampleMaterials);
    
    // Get the initialized materials
    $materials = $materialUsageModel->getInstallationMaterials($installationId);
}

// Get existing daily work
$dailyWork = $materialUsageModel->getDailyWorkByDay($installationId);

$title = 'Material Usage - Installation #' . $installationId;
ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Material Usage Tracking</h1>
        <p class="mt-2 text-sm text-gray-700">
            Installation #<?php echo $installationId; ?> - <?php echo htmlspecialchars($installation['site_code']); ?>
        </p>
    </div>
    <a href="manage-installation.php?id=<?php echo $installationId; ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
        </svg>
        Back to Installation
    </a>
</div>

<!-- Site Summary -->
<div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
    <div class="px-4 py-5 sm:px-6 bg-gray-800 text-white">
        <h3 class="text-lg leading-6 font-medium">Site Summary</h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-300">Installation site information and current status</p>
    </div>
    <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Site Name</label>
                <input type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                       value="<?php echo htmlspecialchars($installation['site_code']); ?>" readonly>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Engineer Name</label>
                <input type="text" id="engineer_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                       placeholder="Enter engineer name">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Date</label>
                <input type="date" id="work_date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                       value="<?php echo date('Y-m-d'); ?>">
            </div>
        </div>
    </div>
</div>

<!-- Material Summary -->
<div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
    <div class="px-4 py-5 sm:px-6 bg-blue-600 text-white">
        <h3 class="text-lg leading-6 font-medium">Material Summary</h3>
        <p class="mt-1 max-w-2xl text-sm text-blue-100">Track material usage and remaining quantities</p>
    </div>
    <div class="border-t border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sr No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Material Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Qty</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Used Qty</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remaining</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="materialTableBody">
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
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $index + 1; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($material['material_name']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $material['material_unit']; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="totalQty inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                <?php echo $material['total_quantity']; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="usedQty inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?php echo $material['used_quantity'] > 0 ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-800'; ?>">
                                <?php echo $material['used_quantity']; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center space-x-2">
                                <span class="remainingQty inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $remainingClass; ?>">
                                    <?php echo $remaining; ?>
                                </span>
                                <button type="button" class="requestMaterialBtn text-orange-600 hover:text-orange-800 <?php echo $showRequestBtn ? '' : 'hidden'; ?>" title="Request More Material">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Daily Work Progress -->
<div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
    <div class="px-4 py-5 sm:px-6 bg-green-600 text-white flex justify-between items-center">
        <div>
            <h3 class="text-lg leading-6 font-medium">Daily Work Progress</h3>
            <p class="mt-1 max-w-2xl text-sm text-green-100">Add daily work updates and material usage</p>
        </div>
        <button type="button" id="addDayBtn" class="inline-flex items-center px-4 py-2 border border-green-300 text-sm font-medium rounded-md text-green-600 bg-white hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
            </svg>
            Add Day
        </button>
    </div>
    <div class="border-t border-gray-200 p-6">
        <div id="daysContainer">
            <!-- Daily work entries will be added here -->
        </div>
    </div>
</div>

<!-- Save Progress Button -->
<div class="flex justify-end space-x-3 mb-6">
    <button type="button" onclick="saveProgress()" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
        </svg>
        Save Progress
    </button>
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
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100 sm:text-sm">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="current_remaining" class="block text-sm font-medium text-gray-700">Current Remaining</label>
                            <input type="number" id="current_remaining" readonly
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100 sm:text-sm">
                        </div>
                        <div>
                            <label for="request_quantity" class="block text-sm font-medium text-gray-700">Request Quantity</label>
                            <input type="number" id="request_quantity" name="request_quantity" min="1" required
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                    </div>
                    
                    <div>
                        <label for="request_priority" class="block text-sm font-medium text-gray-700">Priority</label>
                        <select id="request_priority" name="priority" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="urgent">Urgent - Work Stopped</option>
                            <option value="high">High - Needed Today</option>
                            <option value="medium" selected>Medium - Needed This Week</option>
                            <option value="low">Low - Future Planning</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="request_reason" class="block text-sm font-medium text-gray-700">Reason for Request</label>
                        <textarea id="request_reason" name="reason" rows="3" required
                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
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
    
    // Get materials from the main table
    document.querySelectorAll('#materialTableBody tr').forEach(row => {
        const materialId = row.dataset.materialId;
        const materialName = row.querySelector('td:nth-child(2)').textContent.trim();
        
        materialRows += `
            <tr>
                <td class="px-4 py-2 text-sm text-gray-900">${materialName}</td>
                <td class="px-4 py-2">
                    <input type="number" class="usedToday w-20 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                           value="0" min="0" data-material-id="${materialId}">
                </td>
                <td class="px-4 py-2">
                    <div class="flex flex-col space-y-2">
                        <input type="file" class="materialPhoto text-xs border-gray-300 rounded-md" 
                               accept="image/*,video/*" data-material-id="${materialId}"
                               title="Optional: Upload photo/video for this material">
                        <div class="materialPhotoPreview flex flex-wrap gap-1"></div>
                    </div>
                </td>
            </tr>
        `;
    });

    // Get current date and time
    const now = new Date();
    const currentDate = now.toLocaleDateString('en-US', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
    const currentTime = now.toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit',
        hour12: true 
    });

    // Only show remove button for Day 2 and onwards
    const removeButton = day > 1 ? `
        <button type="button" class="removeDay inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
            Remove
        </button>
    ` : '';

    return `
    <div class="border border-gray-200 rounded-lg mb-4 day-block" data-day="${day}">
        <div class="bg-blue-50 px-4 py-3 border-b border-gray-200 flex justify-between items-center">
            <div>
                <h4 class="text-lg font-medium text-gray-900">Day ${day} Work Progress</h4>
                <p class="text-sm text-gray-600 mt-1">
                    <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                    </svg>
                    ${currentDate}
                    <svg class="w-4 h-4 inline ml-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                    </svg>
                    ${currentTime}
                </p>
            </div>
            <div class="flex items-center space-x-2">
                ${day === 1 ? `
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Primary Day
                    </span>
                ` : ''}
                ${removeButton}
            </div>
        </div>
        <div class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Remarks:</label>
                    <textarea class="dayRemarks w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                              rows="3" placeholder="Enter remarks for today's work"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Report:</label>
                    <textarea class="dayReport w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                              rows="3" placeholder="Enter detailed report"></textarea>
                </div>
            </div>

            <!-- Material Usage for Today -->
            <div class="mb-4">
                <h5 class="text-md font-medium text-gray-900 mb-2">Material Used Today</h5>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-md">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Material Name</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Used Qty (Today)</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Photo/Video (Optional)</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            ${materialRows}
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Site Photos/Videos -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Site Photos/Videos <span class="text-red-500">*</span>
                    <span class="text-xs text-gray-500">(Required if no individual material photos uploaded)</span>
                </label>
                <input type="file" class="siteFiles w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                       multiple accept="image/*,video/*" required>
                <div class="preview mt-3 grid grid-cols-2 md:grid-cols-4 gap-2"></div>
                <p class="text-xs text-gray-500 mt-1">Upload overall site progress photos/videos. This is required if you haven't uploaded individual material photos.</p>
            </div>

            <!-- Validation Status -->
            <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-md">
                <h6 class="text-sm font-medium text-blue-800 mb-2">Checkout Requirements:</h6>
                <ul class="text-xs text-blue-700 space-y-1">
                    <li>✓ Add work remarks and report</li>
                    <li>✓ For each material used, either upload individual photo OR overall site photos</li>
                    <li>✓ System will check remaining stock and suggest material requests if needed</li>
                </ul>
            </div>

            <div class="flex justify-end">
                <button type="button" class="checkOutBtn inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
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
            alert('Day 1 cannot be removed as it is the primary work day.');
            return;
        }
        
        // Confirm removal for other days
        if (confirm(`Are you sure you want to remove Day ${dayNumber}? This action cannot be undone.`)) {
            dayBlock.remove();
            
            // Update day numbers for remaining days
            updateDayNumbers();
        }
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
        
        // Check if overall site photos are provided when no individual photos
        const siteFiles = dayBlock.querySelector('.siteFiles');
        const hasSitePhotos = siteFiles && siteFiles.files.length > 0;
        
        if (!hasIndividualPhotos && !hasSitePhotos) {
            validationErrors.push('Either upload individual material photos OR overall site photos to document the work.');
        }
        
        // Check work description
        let remarks = dayBlock.querySelector('.dayRemarks').value.trim();
        let report = dayBlock.querySelector('.dayReport').value.trim();
        
        if (!hasWork && !remarks && !report) {
            validationErrors.push('Please add some work details, material usage, or remarks before checking out.');
        }
        
        // Show validation errors
        if (validationErrors.length > 0) {
            let errorMessage = 'Please fix the following issues before checking out:\n\n';
            validationErrors.forEach((error, index) => {
                errorMessage += `${index + 1}. ${error}\n`;
            });
            errorMessage += '\nNote: For consumed materials, you must either:\n- Upload individual photos showing how each material was used, OR\n- Upload overall site photos with detailed work description';
            
            alert(errorMessage);
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
            
            if (confirm(stockMessage)) {
                // Store materials for request generation after checkout
                window.pendingMaterialRequests = lowStockMaterials;
            }
        }
        
        // Show material consumption summary and low stock warnings
        let confirmMessage = `Day ${dayNumber} Checkout Summary:\n\n`;
        
        if (consumedMaterials.length > 0) {
            confirmMessage += 'Materials consumed:\n';
            consumedMaterials.forEach(material => {
                confirmMessage += `• ${material.name}: ${material.quantity} units\n`;
            });
            confirmMessage += '\n';
        }
        
        if (lowStockMaterials.length > 0) {
            confirmMessage += 'LOW STOCK WARNING:\n';
            lowStockMaterials.forEach(material => {
                confirmMessage += `• ${material.name}: Only ${material.remaining} units will remain\n`;
            });
            confirmMessage += '\nConsider generating material requests for these items.\n\n';
        }
        
        confirmMessage += 'This will update your main material inventory and cannot be undone.\n\nProceed with checkout?';
        
        if (!confirm(confirmMessage)) {
            return;
        }
        
        // First save the daily work
        saveDailyWork(dayNumber)
        .then(result => {
            if (!result.success) {
                throw new Error(result.message);
            }
            
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
                
                alert(successMessage);
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error checking out day:', error);
            alert('Error: ' + error.message);
        });
        
        // Mark day as checked out
        let checkOutBtn = e.target.closest('.checkOutBtn');
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
        
        // Add checked out timestamp
        let header = dayBlock.querySelector('.bg-blue-50');
        header.insertAdjacentHTML('beforeend', `
            <div class="text-xs text-gray-600 mt-1">
                <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
                Checked out at ${new Date().toLocaleTimeString()}
            </div>
        `);
        
        alert(`Day ${dayNumber} work has been checked out successfully! Material quantities have been updated.`);
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

// Handle pending material requests after checkout
function handlePendingMaterialRequests() {
    if (!window.pendingMaterialRequests || window.pendingMaterialRequests.length === 0) {
        alert('Day checked out successfully! Material quantities have been updated.');
        location.reload();
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
            alert(`Material request submitted successfully!\n\nRequest ID: ${data.request_id}\nMaterial: ${data.data.material_name}\nQuantity: ${data.data.quantity}\nPriority: ${data.data.priority}\nStatus: ${data.data.status}\n\nYour request will be processed by the admin team and visible at admin/requests.`);
            
            closeMaterialRequestModal();
            
            // Check if there are more pending requests
            if (window.pendingMaterialRequests && window.pendingMaterialRequests.length > 0) {
                setTimeout(() => {
                    handlePendingMaterialRequests();
                }, 500);
            } else {
                // All requests handled, reload page
                alert('All material requests submitted successfully. They are now visible in the admin requests panel.');
                location.reload();
            }
        } else {
            alert('Error submitting request: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while submitting the material request. Please try again.');
    });
});

// Load existing daily work from database
function loadExistingDailyWork() {
    fetch('process-material-usage.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'get_daily_work',
            installation_id: <?php echo $installationId; ?>
        })
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
                const checkoutTime = new Date(dayData.checked_out_at).toLocaleTimeString();
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
}

// Save daily work to database
function saveDailyWork(dayNumber) {
    const dayBlock = document.querySelector(`[data-day="${dayNumber}"]`);
    if (!dayBlock) return;
    
    const materialUsage = [];
    dayBlock.querySelectorAll('.usedToday').forEach(input => {
        if (input.value > 0) {
            materialUsage.push({
                material_id: input.dataset.materialId,
                quantity_used: parseFloat(input.value)
            });
        }
    });
    
    const workData = {
        action: 'save_daily_work',
        installation_id: <?php echo $installationId; ?>,
        day_number: dayNumber,
        work_date: document.getElementById('work_date').value,
        engineer_name: document.getElementById('engineer_name').value,
        remarks: dayBlock.querySelector('.dayRemarks').value,
        report: dayBlock.querySelector('.dayReport').value,
        material_usage: materialUsage
    };
    
    return fetch('process-material-usage.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(workData)
    })
    .then(response => response.json());
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
            alert('Progress saved successfully!');
        } else {
            alert('Some data could not be saved. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error saving progress:', error);
        alert('An error occurred while saving progress.');
    });
}
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../includes/vendor_layout.php';
?>