<?php
require_once __DIR__ . '/../../config/auth.php';
// Auth check could be here if needed, but layout might handle it
$selectedCustomer = $_GET['customer_id'] ?? '';

$title = 'Dynamic Survey Responses';
ob_start();
?>

<?php
require_once __DIR__ . '/../../config/auth.php';
// Require module access
Auth::requireModuleAccess('surveys');
$selectedCustomer = $_GET['customer_id'] ?? '';

$title = 'Survey Responses';
ob_start();
?>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8" id="statsGrid">
    <div class="stats-card bg-white rounded-2xl border border-gray-200 p-6 shadow-sm transition-all duration-300 cursor-pointer hover:shadow-lg hover:border-gray-300 hover:-translate-y-1">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <div class="text-xs text-gray-500 uppercase font-semibold tracking-wide mb-2">Survey Submitted</div>
                <div class="text-4xl font-bold text-gray-900 mb-2" id="stat-submitted">0</div>
                <div class="text-sm text-gray-600 font-medium">Total Submissions</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center flex-shrink-0 shadow-sm">
                <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"></path></svg>
            </div>
        </div>
    </div>
    <div class="stats-card bg-white rounded-2xl border border-gray-200 p-6 shadow-sm transition-all duration-300 cursor-pointer hover:shadow-lg hover:border-gray-300 hover:-translate-y-1">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <div class="text-xs text-gray-500 uppercase font-semibold tracking-wide mb-2">Approved</div>
                <div class="text-4xl font-bold text-gray-900 mb-2" id="stat-approved">0</div>
                <div class="text-sm text-gray-600 font-medium">Survey Approved</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-50 to-emerald-100 flex items-center justify-center flex-shrink-0 shadow-sm">
                <svg class="w-6 h-6 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
            </div>
        </div>
    </div>
    <div class="stats-card bg-white rounded-2xl border border-gray-200 p-6 shadow-sm transition-all duration-300 cursor-pointer hover:shadow-lg hover:border-gray-300 hover:-translate-y-1">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <div class="text-xs text-gray-500 uppercase font-semibold tracking-wide mb-2">Rejected</div>
                <div class="text-4xl font-bold text-gray-900 mb-2" id="stat-rejected">0</div>
                <div class="text-sm text-gray-600 font-medium">Survey Rejected</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-rose-50 to-rose-100 flex items-center justify-center flex-shrink-0 shadow-sm">
                <svg class="w-6 h-6 text-rose-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
            </div>
        </div>
    </div>
    <div class="stats-card bg-white rounded-2xl border border-gray-200 p-6 shadow-sm transition-all duration-300 cursor-pointer hover:shadow-lg hover:border-gray-300 hover:-translate-y-1">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <div class="text-xs text-gray-500 uppercase font-semibold tracking-wide mb-2">Pending</div>
                <div class="text-4xl font-bold text-gray-900 mb-2" id="stat-pending">0</div>
                <div class="text-sm text-gray-600 font-medium">Awaiting Review</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-50 to-amber-100 flex items-center justify-center flex-shrink-0 shadow-sm">
                <svg class="w-6 h-6 text-amber-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path></svg>
            </div>
        </div>
    </div>
</div>

<!-- Customer-wise Survey Records -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6">
        <div class="flex-1">
            <h2 class="text-xl font-semibold text-gray-900">Customer-wise Survey Records</h2>
            <p class="text-sm text-gray-500 mt-1">View survey responses by customer with status filters</p>
        </div>
        <div class="mt-4 lg:mt-0 flex items-center gap-3">
            <select id="statusFilter" class="form-select text-sm" onchange="applyFilters()">
                <option value="">All Status</option>
                <option value="submitted">Submitted</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="pending">Pending</option>
            </select>
            <input type="text" id="searchCustomer" placeholder="Search customers..." class="form-input text-sm" onkeyup="debounceSearch()">
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200" id="customerSurveyTable">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-10">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Total Surveys</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Approved</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Rejected</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Pending</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody id="customerTableBody" class="bg-white divide-y divide-gray-200">
                <!-- Data will be loaded here -->
            </tbody>
        </table>
    </div>
    
    <div id="customerTableLoading" class="text-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
        <p class="text-gray-500 mt-2">Loading customer survey data...</p>
    </div>
    
    <div id="customerTableEmpty" class="text-center py-8" style="display: none;">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No survey data found</h3>
        <p class="mt-1 text-sm text-gray-500">No customer survey records match your current filters.</p>
    </div>
</div>

<!-- Customer Filter -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
    <div class="flex items-end gap-4">
        <div class="flex-1">
            <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-2">Select Customer</label>
            <select id="customer_id" class="form-select w-full" onchange="handleCustomerChange(this.value)">
                <option value="">-- Loading Customers... --</option>
            </select>
        </div>
        <button id="clear_btn" style="display: none;" onclick="clearSelection()" class="btn btn-secondary">
            <svg class="w-4 h-4 mr-2 inline" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
            Clear
        </button>
    </div>
</div>

<!-- Content States -->
<div id="content_area">
    <!-- Initial State -->
    <div id="state_no_customer" class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
        <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No Customer Selected</h3>
        <p class="text-gray-500">Please select a customer from the dropdown above to view their survey responses</p>
    </div>

    <!-- Loading State -->
    <div id="state_loading" style="display: none;" class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
        <div class="flex flex-col items-center">
            <svg class="animate-spin h-10 w-10 text-blue-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900">Loading Report...</h3>
            <p class="text-gray-500">Fetching survey data and responses</p>
        </div>
    </div>

    <!-- Error State -->
    <div id="state_error" style="display: none;" class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
        <svg class="mx-auto h-16 w-16 text-red-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <h3 class="text-lg font-medium text-red-900 mb-2">Error Occurred</h3>
        <p id="error_message" class="text-red-500">Failed to load data. Please try again.</p>
    </div>

    <!-- Empty Form State -->
    <div id="state_no_form" style="display: none;" class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
        <svg class="mx-auto h-16 w-16 text-orange-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No Survey Form Found</h3>
        <p class="text-gray-500">No active survey form found for this customer</p>
        <a href="../masters/form-designer.php?type=survey" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
            Create Survey Form
        </a>
    </div>

    <!-- No Responses State -->
    <div id="state_no_responses" style="display: none;" class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
        <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No Responses Yet</h3>
        <p class="text-gray-500">No survey responses have been submitted for this customer</p>
    </div>

    <!-- Report Area -->
    <div id="report_area" style="display: none;">
        <!-- Survey Info Card -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-start justify-between">
                <div class="flex items-start flex-1">
                    <svg class="w-6 h-6 text-blue-600 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="flex-1">
                        <h4 id="survey_title" class="text-sm font-medium text-blue-800"></h4>
                        <p id="survey_description" class="text-sm text-blue-600 mt-1"></p>
                        <p class="text-xs text-blue-500 mt-2">Total Responses: <strong id="total_responses_count">0</strong></p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a id="export_excel" href="#" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Excel (XLS)
                    </a>
                    <a id="export_csv" href="#" class="inline-flex items-center px-4 py-2 border border-green-600 text-sm font-medium rounded-md text-green-700 bg-white hover:bg-green-50 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        CSV
                    </a>
                </div>
            </div>
        </div>

        <!-- Responses Table -->
        <div class="professional-table bg-white">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Survey Responses</h3>
                    <p id="table_subtitle" class="text-sm text-gray-500 mt-1"></p>
                </div>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table id="responses_table" class="min-w-full divide-y divide-gray-200 border border-gray-300">
                        <thead id="table_head">
                            <!-- Dynamic Headers -->
                        </thead>
                        <tbody id="table_body" class="bg-white divide-y divide-gray-200">
                            <!-- Dynamic Content -->
                        </tbody>
                    </table>
                </div>
                
                <!-- View Details Links -->
                <div id="details_links_section" class="mt-4 text-sm text-gray-500">
                    <p>💡 Tip: Click on image links to view photos. For detailed view, use the links below:</p>
                    <div id="details_links" class="mt-2 flex flex-wrap gap-2">
                        <!-- Dynamic Links -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let currentCustomerId = "<?php echo $selectedCustomer; ?>";
    let searchTimer;
    let customerData = [];

    document.addEventListener('DOMContentLoaded', () => {
        loadStats();
        loadCustomerSurveyData();
        loadCustomers();
        if (currentCustomerId) {
            handleCustomerChange(currentCustomerId);
        }
    });

    async function loadStats() {
        try {
            const response = await fetch('../../api/survey_responses.php?action=get_stats');
            const data = await response.json();
            
            if (data.success) {
                document.getElementById('stat-submitted').textContent = data.stats.submitted || 0;
                document.getElementById('stat-approved').textContent = data.stats.approved || 0;
                document.getElementById('stat-rejected').textContent = data.stats.rejected || 0;
                document.getElementById('stat-pending').textContent = data.stats.pending || 0;
            }
        } catch (error) {
            console.error('Error loading stats:', error);
        }
    }

    async function loadCustomerSurveyData() {
        try {
            const response = await fetch('../../api/survey_responses.php?action=get_customer_stats');
            const data = await response.json();
            
            if (data.success) {
                customerData = data.customers;
                renderCustomerTable(customerData);
            } else {
                showEmptyCustomerTable();
            }
        } catch (error) {
            console.error('Error loading customer data:', error);
            showEmptyCustomerTable();
        } finally {
            document.getElementById('customerTableLoading').style.display = 'none';
        }
    }

    function renderCustomerTable(customers) {
        const tbody = document.getElementById('customerTableBody');
        
        if (!customers || customers.length === 0) {
            showEmptyCustomerTable();
            return;
        }

        tbody.innerHTML = customers.map((customer, idx) => `
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap text-xs font-medium text-gray-500 text-center border-r border-gray-100">${idx + 1}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-semibold text-sm mr-3">
                            ${customer.name.charAt(0).toUpperCase()}
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-900">${customer.name}</div>
                            <div class="text-sm text-gray-500">ID: ${customer.id}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-gray-900">
                    ${customer.total_surveys || 0}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        ${customer.submitted || 0}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        ${customer.approved || 0}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        ${customer.rejected || 0}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        ${customer.pending || 0}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                    <button onclick="viewCustomerSurveys(${customer.id})" class="text-blue-600 hover:text-blue-900 mr-3">
                        Analyze
                    </button>
                    ${customer.total_surveys > 0 ? `<button onclick="exportCustomerSurveys(${customer.id})" class="text-green-600 hover:text-green-900">Export</button>` : ''}
                </td>
            </tr>
        `).join('');
        
        document.getElementById('customerTableEmpty').style.display = 'none';
    }

    function showEmptyCustomerTable() {
        document.getElementById('customerTableBody').innerHTML = '';
        document.getElementById('customerTableEmpty').style.display = 'block';
    }

    function debounceSearch() {
        if (searchTimer) clearTimeout(searchTimer);
        searchTimer = setTimeout(() => applyFilters(), 500);
    }

    function applyFilters() {
        const statusFilter = document.getElementById('statusFilter').value;
        const searchTerm = document.getElementById('searchCustomer').value.toLowerCase();
        
        let filteredData = customerData;
        
        if (searchTerm) {
            filteredData = filteredData.filter(customer => 
                customer.name.toLowerCase().includes(searchTerm)
            );
        }
        
        if (statusFilter) {
            filteredData = filteredData.filter(customer => 
                customer[statusFilter] > 0
            );
        }
        
        renderCustomerTable(filteredData);
    }

    function viewCustomerSurveys(customerId) {
        // Scroll to the customer selection section and select the customer
        document.getElementById('customer_id').value = customerId;
        handleCustomerChange(customerId);
        
        // Scroll to the customer filter section
        document.querySelector('#content_area').scrollIntoView({ behavior: 'smooth' });
    }

    function exportCustomerSurveys(customerId) {
        window.open(`export-responses.php?customer_id=${customerId}`, '_blank');
    }

    async function loadCustomers() {
        try {
            const response = await fetch('../../api/survey_responses.php?action=get_customers');
            const data = await response.json();
            
            const select = document.getElementById('customer_id');
            select.innerHTML = '<option value="">-- Select Customer --</option>';
            
            if (data.success) {
                data.customers.forEach(customer => {
                    const option = document.createElement('option');
                    option.value = customer.id;
                    option.textContent = customer.name;
                    if (customer.id == currentCustomerId) option.selected = true;
                    select.appendChild(option);
                });
            } else {
                select.innerHTML = '<option value="">Error loading customers</option>';
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('customer_id').innerHTML = '<option value="">Error loading customers</option>';
        }
    }

    async function handleCustomerChange(customerId) {
        currentCustomerId = customerId;
        
        // Update URL without reloading
        const url = new URL(window.location);
        if (customerId) {
            url.searchParams.set('customer_id', customerId);
            document.getElementById('clear_btn').style.display = 'inline-block';
        } else {
            url.searchParams.delete('customer_id');
            document.getElementById('clear_btn').style.display = 'none';
            showState('no_customer');
            return;
        }
        window.history.pushState({}, '', url);

        if (!customerId) {
            showState('no_customer');
            return;
        }

        showState('loading');

        try {
            const response = await fetch(`../../api/survey_responses.php?action=get_report&customer_id=${customerId}`);
            const data = await response.json();

            if (data.success) {
                if (!data.surveyForm) {
                    showState('no_form');
                } else if (!data.responses || data.responses.length === 0) {
                    showState('no_responses');
                    updateSurveyInfo(data.surveyForm, 0);
                } else {
                    renderReport(data);
                    showState('report');
                }
            } else {
                document.getElementById('error_message').textContent = data.message || 'Failed to load report';
                showState('error');
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('error_message').textContent = 'An unexpected error occurred while fetching data.';
            showState('error');
        }
    }

    function clearSelection() {
        document.getElementById('customer_id').value = '';
        handleCustomerChange('');
    }

    function showState(state) {
        const states = ['no_customer', 'loading', 'error', 'no_form', 'no_responses', 'report'];
        states.forEach(s => {
            const el = document.getElementById(`state_${s}`) || document.getElementById(`${s}_area`);
            if (el) el.style.display = (s === state) ? 'block' : 'none';
        });
    }

    function updateSurveyInfo(form, count) {
        document.getElementById('survey_title').textContent = form.title;
        document.getElementById('survey_description').textContent = form.description;
        document.getElementById('total_responses_count').textContent = count;
        
        document.getElementById('export_excel').href = `export-responses.php?customer_id=${currentCustomerId}`;
        document.getElementById('export_csv').href = `export-responses-csv.php?customer_id=${currentCustomerId}`;
        
        document.getElementById('table_subtitle').textContent = `Showing ${count} response(s) with all form data`;
    }

    function renderReport(data) {
        updateSurveyInfo(data.surveyForm, data.responses.length);
        
        const tableHead = document.getElementById('table_head');
        const tableBody = document.getElementById('table_body');
        const fields = data.structuredFields;
        
        // 1. Group fields by section for merged headers
        const sectionGroups = [];
        let currentGroup = null;
        
        fields.forEach(f => {
            if (!currentGroup || currentGroup.full_path !== f.full_path) {
                currentGroup = { full_path: f.full_path, count: 0 };
                sectionGroups.push(currentGroup);
            }
            currentGroup.count++;
        });

        // 2. Build Headers
        let headerHtml = `
            <tr class="bg-blue-600 text-white text-center">
                <th colspan="1" class="px-4 py-3 text-xs font-bold uppercase tracking-wider border-r border-blue-500">No</th>
                <th colspan="4" class="px-4 py-3 text-xs font-bold uppercase tracking-wider border-r border-blue-500">Response Identification</th>
                <th colspan="1" class="px-4 py-3 text-xs font-bold uppercase tracking-wider border-r border-blue-500">Action</th>
                ${sectionGroups.map(g => `
                    <th colspan="${g.count}" class="px-4 py-3 text-xs font-bold uppercase tracking-wider border-r border-blue-500">${g.full_path}</th>
                `).join('')}
            </tr>
            <tr class="bg-blue-100">
                <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider border-r border-gray-300 w-10">#</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider border-r border-gray-300">ID</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider border-r border-gray-300">Site</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider border-r border-gray-300">Surveyor</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider border-r border-gray-300">Date</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider border-r border-gray-300">View</th>
                ${fields.map(f => `
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider border-r border-gray-300" style="min-width: 150px;">${f.field_label}</th>
                `).join('')}
            </tr>
        `;
        tableHead.innerHTML = headerHtml;

        // 3. Build Body
        let bodyHtml = '';
        data.responses.forEach((response, idx) => {
            let formData = {};
            try {
                formData = JSON.parse(response.form_data || '{}');
            } catch(e) {
                console.error("Failed to parse form data for response", response.id);
            }
            
            const date = new Date(response.submitted_date);
            const formattedDate = date.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
            const formattedTime = date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: false });

            bodyHtml += `
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 whitespace-nowrap text-center text-xs font-black text-gray-400 border-r border-gray-200 bg-gray-50/50">${idx + 1}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 border-r border-gray-200">#${response.id}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 border-r border-gray-200">
                        <div class="font-medium">${response.site_code || 'N/A'}</div>
                        <div class="text-xs text-gray-500">${response.customer_name || ''}</div>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 border-r border-gray-200">${response.surveyor_name || 'Unknown'}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 border-r border-gray-200">
                        <div>${formattedDate}</div>
                        <div class="text-xs text-gray-500">${formattedTime}</div>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-center text-sm font-medium border-r border-gray-200">
                        <a href="view-response.php?id=${response.id}" class="text-indigo-600 hover:text-indigo-900 p-2 bg-indigo-50 rounded-lg inline-flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </a>
                    </td>
                    ${fields.map(f => {
                        const val = formData[f.field_id] || '';
                        let cellContent = '-';
                        
                        if (f.field_type === 'file' && val) {
                            if (typeof val === 'object' && val.file_path) {
                                const isImage = /\.(jpg|jpeg|png|gif|webp)$/i.test(val.original_name);
                                cellContent = isImage 
                                    ? `<a href="../../${val.file_path}" target="_blank" class="text-blue-600 hover:underline text-xs">📷 View</a>`
                                    : `<span class="text-xs text-gray-600">📄 ${val.original_name}</span>`;
                            } else if (Array.isArray(val)) {
                                cellContent = `<span class="text-xs text-blue-600">${val.length} file(s)</span>`;
                            }
                        } else if (Array.isArray(val)) {
                            cellContent = `<span class="text-xs">${val.join(', ')}</span>`;
                        } else {
                            cellContent = `<span class="text-xs">${val || '-'}</span>`;
                        }
                        
                        return `<td class="px-4 py-3 text-sm text-gray-900 border-r border-gray-200">${cellContent}</td>`;
                    }).join('')}
                </tr>
            `;
        });
        tableBody.innerHTML = bodyHtml;

        // 4. Update Details Links
        const linksContainer = document.getElementById('details_links');
        linksContainer.innerHTML = data.responses.map(r => `
            <a href="view-response.php?id=${r.id}" class="inline-flex items-center px-3 py-1 bg-blue-50 text-blue-700 rounded-md hover:bg-blue-100 text-xs">
                Response #${r.id} - ${r.site_code || 'N/A'}
            </a>
        `).join('');
    }
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>
