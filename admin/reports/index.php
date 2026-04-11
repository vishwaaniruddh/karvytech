<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/Site.php';
require_once __DIR__ . '/../../models/SiteSurvey.php';
require_once __DIR__ . '/../../models/MaterialRequest.php';
require_once __DIR__ . '/../../models/Installation.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$title = 'Reports & Analytics';
ob_start();
?>

<!-- Header Section -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
        <div class="flex-1">
            <h1 class="text-3xl font-bold text-gray-900">Reports & Analytics</h1>
            <p class="mt-2 text-lg text-gray-600">Generate and export comprehensive reports</p>
        </div>
        <div class="mt-6 lg:mt-0 lg:ml-6">
            <div class="flex items-center space-x-2 text-sm text-gray-500">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
                <span>All reports are exported as CSV files</span>
            </div>
        </div>
    </div>
</div>

<!-- Reports Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    
    <!-- Sites Report -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center mb-4">
            <div class="flex-shrink-0">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-900">Sites Report</h3>
                <p class="text-sm text-gray-500">Complete site details and information</p>
            </div>
        </div>
        
        <div class="space-y-3 mb-6">
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Includes:</span>
            </div>
            <ul class="text-sm text-gray-600 space-y-1 ml-4">
                <li>• Site ID, Location, Address</li>
                <li>• Customer & Vendor details</li>
                <li>• Bank information</li>
                <li>• Status and dates</li>
            </ul>
        </div>
        
        <button onclick="exportReport('sites')" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors flex items-center justify-center">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
            Export Sites Report
        </button>
    </div>
    
    <!-- Survey Report -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center mb-4">
            <div class="flex-shrink-0">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-900">Survey Report</h3>
                <p class="text-sm text-gray-500">Site survey status and details</p>
            </div>
        </div>
        
        <div class="space-y-3 mb-6">
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Includes:</span>
            </div>
            <ul class="text-sm text-gray-600 space-y-1 ml-4">
                <li>• Survey status and dates</li>
                <li>• Vendor assignments</li>
                <li>• Survey responses</li>
                <li>• Approval status</li>
            </ul>
        </div>
        
        <button onclick="exportReport('surveys')" class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors flex items-center justify-center">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
            Export Survey Report
        </button>
    </div>
    
    <!-- Material Report -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center mb-4">
            <div class="flex-shrink-0">
                <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 2L3 7v11a1 1 0 001 1h12a1 1 0 001-1V7l-7-5zM9 9a1 1 0 012 0v4a1 1 0 11-2 0V9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-900">Material Report</h3>
                <p class="text-sm text-gray-500">Material requests and dispatches</p>
            </div>
        </div>
        
        <div class="space-y-3 mb-6">
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Includes:</span>
            </div>
            <ul class="text-sm text-gray-600 space-y-1 ml-4">
                <li>• Material requests</li>
                <li>• Dispatch details</li>
                <li>• Delivery status</li>
                <li>• Quantities and items</li>
            </ul>
        </div>
        
        <button onclick="exportReport('materials')" class="w-full bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700 transition-colors flex items-center justify-center">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
            Export Material Report
        </button>
    </div>
    
    <!-- Installation Report -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center mb-4">
            <div class="flex-shrink-0">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-900">Installation Report</h3>
                <p class="text-sm text-gray-500">Installation progress and status</p>
            </div>
        </div>
        
        <div class="space-y-3 mb-6">
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Includes:</span>
            </div>
            <ul class="text-sm text-gray-600 space-y-1 ml-4">
                <li>• Installation status</li>
                <li>• Progress tracking</li>
                <li>• Vendor assignments</li>
                <li>• Completion dates</li>
            </ul>
        </div>
        
        <button onclick="exportReport('installations')" class="w-full bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 transition-colors flex items-center justify-center">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
            Export Installation Report
        </button>
    </div>
    
    <!-- Audit Logs Report -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center mb-4">
            <div class="flex-shrink-0">
                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-900">Audit Logs</h3>
                <p class="text-sm text-gray-500">User access and activity tracking</p>
            </div>
        </div>
        
        <div class="space-y-3 mb-6">
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Includes:</span>
            </div>
            <ul class="text-sm text-gray-600 space-y-1 ml-4">
                <li>• User access logs</li>
                <li>• API call tracking</li>
                <li>• Login/logout history</li>
                <li>• Activity statistics</li>
            </ul>
        </div>
        
        <button onclick="window.location.href='../audit/stats.php'" class="w-full bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition-colors flex items-center justify-center">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path>
            </svg>
            View Audit Statistics
        </button>
    </div>
    
</div>

<!-- Loading Modal -->
<div id="loadingModal" class="modal hidden">
    <div class="modal-content">
        <div class="text-center py-8">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Generating Report</h3>
            <p class="text-gray-500">Please wait while we prepare your CSV export...</p>
        </div>
    </div>
</div>

<script>
function exportReport(type) {
    // Show loading modal
    document.getElementById('loadingModal').classList.remove('hidden');
    document.getElementById('loadingModal').style.display = 'flex';
    
    // Create form and submit
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'export-report.php';
    form.target = '_blank';
    
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'report_type';
    input.value = type;
    
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
    
    // Hide loading modal after a delay
    setTimeout(() => {
        document.getElementById('loadingModal').classList.add('hidden');
        document.getElementById('loadingModal').style.display = 'none';
    }, 2000);
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>