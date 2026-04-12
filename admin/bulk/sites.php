<?php
require_once __DIR__ . '/../../config/auth.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);
$currentUser = Auth::getCurrentUser();

$title = 'Bulk Site Operations';
ob_start();
?>

<!-- Header Section -->
<div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg shadow-lg mb-8 overflow-hidden">
    <div class="px-6 py-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">Bulk Site Operations</h1>
                <p class="text-blue-100 text-lg">Perform batch operations on multiple sites efficiently</p>
            </div>
            <div class="flex items-center space-x-4">
                <a href="index.php" class="inline-flex items-center px-4 py-2 border border-blue-300 text-sm font-medium rounded-md text-blue-100 hover:bg-blue-500 hover:text-white transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                    </svg>
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Site Selection Section -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
    <div class="flex items-center mb-6">
        <div class="flex-shrink-0">
            <svg class="w-8 h-8 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" clip-rule="evenodd"></path>
            </svg>
        </div>
        <div class="ml-3">
            <h3 class="text-lg font-semibold text-gray-900">Select Sites for Bulk Operations</h3>
            <p class="text-sm text-gray-500">Choose sites using text input or searchable dropdown</p>
        </div>
    </div>
    
    <!-- Selection Method Toggle -->
    <div class="mb-6">
        <div class="flex space-x-6">
            <label class="inline-flex items-center">
                <input type="radio" name="siteSelectionMethod" value="text" checked class="form-radio text-indigo-600 focus:ring-indigo-500">
                <span class="ml-2 text-sm font-medium text-gray-700">Text Input (Comma/Space Separated)</span>
            </label>
            <label class="inline-flex items-center">
                <input type="radio" name="siteSelectionMethod" value="dropdown" class="form-radio text-indigo-600 focus:ring-indigo-500">
                <span class="ml-2 text-sm font-medium text-gray-700">Searchable Dropdown</span>
            </label>
        </div>
    </div>
    
    <!-- Text Input Method -->
    <div id="textInputMethod" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Enter Site IDs</label>
            <textarea id="siteIdsTextInput" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Enter site IDs separated by comma or space (e.g., SITE001, SITE002 SITE003 SITE004)"></textarea>
            <p class="text-sm text-gray-500 mt-1">Separate multiple site IDs with commas or spaces</p>
        </div>
        <div class="flex justify-end">
            <button type="button" id="fetchSitesFromText" class="px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Fetch Sites
            </button>
        </div>
    </div>
    
    <!-- Dropdown Method -->
    <div id="dropdownMethod" class="hidden space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Search and Select Sites</label>
            <div class="relative">
                <input type="text" id="siteSearchDropdown" placeholder="Type to search sites..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <div id="siteDropdownResults" class="hidden absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto mt-1">
                    <!-- Search results will appear here -->
                </div>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Selected Sites</label>
            <div id="selectedSitesContainer" class="min-h-20 p-3 border border-gray-300 rounded-md bg-gray-50">
                <p class="text-sm text-gray-500">No sites selected</p>
            </div>
        </div>
        <div class="flex justify-end">
            <button type="button" id="fetchSitesFromDropdown" class="px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Fetch Selected Sites
            </button>
        </div>
    </div>
</div>

<!-- Sites Confirmation Table -->
<div id="sitesConfirmationSection" class="hidden bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">Sites Confirmation</h3>
            <p class="text-sm text-gray-500">Review the fetched sites before performing operations</p>
        </div>
        <div class="flex space-x-3">
            <button type="button" id="clearSites" class="px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Clear All
            </button>
            <span class="text-sm text-gray-500 py-2">
                <span id="confirmedSitesCount">0</span> sites found
            </span>
        </div>
    </div>
    
    <!-- Sites Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <input type="checkbox" id="selectAllConfirmedSites" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Site ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">City</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">State</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendor</th>
                </tr>
            </thead>
            <tbody id="sitesConfirmationTableBody" class="bg-white divide-y divide-gray-200">
                <!-- Sites will be populated here -->
            </tbody>
        </table>
    </div>
    
    <!-- Not Found Sites -->
    <div id="notFoundSitesSection" class="hidden mt-6">
        <div class="bg-red-50 border border-red-200 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Sites Not Found</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <p>The following site IDs were not found in the database:</p>
                        <div id="notFoundSitesList" class="mt-2 font-mono text-xs bg-red-100 p-2 rounded">
                            <!-- Not found sites will be listed here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Operations Section -->
<div id="operationsSection" class="hidden grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Bulk Updates -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center mb-6">
            <div class="flex-shrink-0">
                <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-lg font-semibold text-gray-900">Bulk Site Updates</h3>
                <p class="text-sm text-gray-500">Update selected sites</p>
            </div>
        </div>
        
        <form id="bulkUpdateForm" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Update Field</label>
                    <select id="updateField" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select field to update</option>
                        <option value="activity_status">Activity Status</option>
                        <option value="customer_id">Customer</option>
                        <option value="bank_id">Bank</option>
                        <option value="vendor">Vendor</option>
                        <option value="delegated_vendor">Delegated Vendor</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">New Value</label>
                    <select id="newValue" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" disabled>
                        <option value="">Select new value</option>
                    </select>
                </div>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" id="previewUpdates" class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Preview Changes
                </button>
                <button type="submit" class="px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Apply Updates
                </button>
            </div>
        </form>
    </div>
    
    <!-- Bulk Delegation -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center mb-6">
            <div class="flex-shrink-0">
                <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-lg font-semibold text-gray-900">Bulk Site Delegation</h3>
                <p class="text-sm text-gray-500">Delegate selected sites to vendors</p>
            </div>
        </div>
        
        <form id="bulkDelegationForm" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Vendor</label>
                <select id="delegationVendor" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="">Select vendor</option>
                    <!-- Vendors will be loaded here -->
                </select>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Delegation Date</label>
                    <input type="date" id="delegationDate" value="<?php echo date('Y-m-d'); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                    <select id="delegationPriority" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="normal">Normal</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Delegation Notes</label>
                <textarea id="delegationNotes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="Any special instructions or notes..."></textarea>
            </div>
            
            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    Create Delegations
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Operations Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- This section is now replaced by the dynamic operations section above -->
</div>

<!-- Bulk Import Reference Card -->
<div class="mt-8 bg-gradient-to-r from-purple-50 to-indigo-50 border border-purple-200 rounded-lg shadow-sm p-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="w-10 h-10 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-semibold text-purple-900">Bulk Site Import</h3>
                <p class="text-purple-700 text-sm mt-1">Use our tested bulk upload module for importing multiple sites from CSV/Excel files</p>
                <div class="mt-2 text-sm text-purple-600">
                    <span class="inline-flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Fully tested and validated
                    </span>
                    <span class="inline-flex items-center ml-4">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Supports CSV & Excel formats
                    </span>
                </div>
            </div>
        </div>
        <div class="flex-shrink-0">
            <a href="../sites/bulk_upload.php" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors shadow-sm">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
                Go to Bulk Upload
            </a>
        </div>
    </div>
</div>
<!-- JavaScript will be included at the bottom of the file -->

<script>
// Basic UI interactions
document.addEventListener('DOMContentLoaded', function() {
    setupUIEventListeners();
    loadVendors();
});

function setupUIEventListeners() {
    // Selection method toggle
    document.querySelectorAll('input[name="siteSelectionMethod"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const textMethod = document.getElementById('textInputMethod');
            const dropdownMethod = document.getElementById('dropdownMethod');
            
            if (this.value === 'text') {
                textMethod.classList.remove('hidden');
                dropdownMethod.classList.add('hidden');
            } else {
                textMethod.classList.add('hidden');
                dropdownMethod.classList.remove('hidden');
            }
        });
    });
    
    // Fetch buttons
    const fetchFromTextBtn = document.getElementById('fetchSitesFromText');
    if (fetchFromTextBtn) {
        fetchFromTextBtn.addEventListener('click', function() {
            const siteIds = document.getElementById('siteIdsTextInput').value.trim();
            if (!siteIds) {
                showToast('Please enter site IDs', 'warning');
                return;
            }
            const siteIdArray = siteIds.split(/[,\s]+/).filter(id => id.trim() !== '').map(id => id.trim());
            if (siteIdArray.length === 0) {
                showToast('Please enter valid site IDs', 'warning');
                return;
            }
            fetchSitesFromAPI(siteIdArray, 'text');
        });
    }
    
    const fetchFromDropdownBtn = document.getElementById('fetchSitesFromDropdown');
    if (fetchFromDropdownBtn) {
        fetchFromDropdownBtn.addEventListener('click', function() {
            const selectedSites = getSelectedSitesFromDropdown();
            if (selectedSites.length === 0) {
                showToast('Please select sites from dropdown', 'warning');
                return;
            }
            const siteIds = selectedSites.map(site => site.site_id);
            fetchSitesFromAPI(siteIds, 'dropdown');
        });
    }
    
    // Clear sites button
    const clearBtn = document.getElementById('clearSites');
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            hideSitesConfirmationSection();
            hideOperationsSection();
            showToast('Sites cleared', 'info');
        });
    }
    
    // Select all checkbox in confirmation table
    const selectAllBtn = document.getElementById('selectAllConfirmedSites');
    if (selectAllBtn) {
        selectAllBtn.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('#sitesConfirmationTableBody input[type="checkbox"]');
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateOperationsVisibility();
        });
    }
    
    // Dropdown search functionality
    let searchTimeout;
    const searchDropdown = document.getElementById('siteSearchDropdown');
    if (searchDropdown) {
        searchDropdown.addEventListener('input', function() {
            const query = this.value.trim();
            clearTimeout(searchTimeout);
            
            if (query.length >= 2) {
                searchTimeout = setTimeout(() => {
                    searchSites(query);
                }, 300);
            } else {
                hideDropdownResults();
            }
        });
    }
    
    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#dropdownMethod')) {
            hideDropdownResults();
        }
    });
    
    // Update field change
    const updateField = document.getElementById('updateField');
    if (updateField) {
        updateField.addEventListener('change', function() {
            loadFieldValues(this.value);
        });
    }
    
    // Form submissions
    const updateForm = document.getElementById('bulkUpdateForm');
    if (updateForm) updateForm.addEventListener('submit', handleBulkUpdate);
    
    const delegationForm = document.getElementById('bulkDelegationForm');
    if (delegationForm) delegationForm.addEventListener('submit', handleBulkDelegation);
    
    // Preview button
    const previewBtn = document.getElementById('previewUpdates');
    if (previewBtn) previewBtn.addEventListener('click', previewBulkUpdate);
}

function renderSitesList(sites, containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    container.innerHTML = '';
    
    sites.forEach(site => {
        const div = document.createElement('div');
        div.className = 'flex items-center p-2 hover:bg-gray-50 rounded';
        div.innerHTML = `
            <input type="checkbox" value="${site.id}" data-site-id="${site.site_id}" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-3">
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900 truncate">${site.site_id}</p>
                        <p class="text-xs text-gray-500 truncate">${site.location || 'No location'}</p>
                    </div>
                    <div class="flex-shrink-0 ml-2">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${getStatusBadgeClass(site.activity_status)}">
                            ${site.activity_status || 'pending'}
                        </span>
                    </div>
                </div>
                ${site.customer_name ? `<p class="text-xs text-gray-400 mt-1">${site.customer_name}</p>` : ''}
            </div>
        `;
        
        const checkbox = div.querySelector('input[type="checkbox"]');
        checkbox.addEventListener('change', () => {
            updateOperationsVisibility();
        });
        
        container.appendChild(div);
    });
}

function loadVendors() {
    fetch('api/get-vendors.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('delegationVendor');
                if (select) {
                    select.innerHTML = '<option value="">Select vendor</option>';
                    data.vendors.forEach(vendor => {
                        select.innerHTML += `<option value="${vendor.id}">${vendor.name} (${vendor.company_name})</option>`;
                    });
                }
            }
        })
        .catch(error => {
            console.error('Error loading vendors:', error);
        });
}

function loadFieldValues(field) {
    const newValueSelect = document.getElementById('newValue');
    if (!newValueSelect) return;
    
    newValueSelect.innerHTML = '<option value="">Select new value</option>';
    newValueSelect.disabled = !field;
    
    if (!field) return;
    
    fetch(`api/get-field-values.php?field=${field}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                data.values.forEach(value => {
                    newValueSelect.innerHTML += `<option value="${value.value}">${value.label}</option>`;
                });
            }
        })
        .catch(error => {
            console.error('Error loading field values:', error);
        });
}

function getStatusBadgeClass(status) {
    switch (status) {
        case 'completed': return 'bg-green-100 text-green-800';
        case 'in_progress': return 'bg-blue-100 text-blue-800';
        case 'on_hold': return 'bg-yellow-100 text-yellow-800';
        case 'cancelled': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}

function fetchSitesFromAPI(siteIds, method) {
    showToast('Fetching sites...', 'info');
    
    fetch('api/fetch-sites.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ site_ids: siteIds, method: method })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            populateSitesTable(data.data.found_sites, data.data.not_found_sites);
            showSitesConfirmationSection();
            showToast(`Found ${data.data.total_found} of ${data.data.total_requested} sites`, 'success');
        } else {
            showToast('Failed to fetch sites: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error fetching sites:', error);
        showToast('Error fetching sites', 'error');
    });
}

function searchSites(query) {
    fetch(`api/search-sites.php?q=${encodeURIComponent(query)}&limit=10`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateDropdownResults(data.sites);
                showDropdownResults();
            } else {
                hideDropdownResults();
            }
        })
        .catch(error => {
            console.error('Error searching sites:', error);
            hideDropdownResults();
        });
}

function populateDropdownResults(sites) {
    const container = document.getElementById('siteDropdownResults');
    if (!container) return;
    
    if (sites.length === 0) {
        container.innerHTML = '<div class="px-3 py-2 text-sm text-gray-500">No sites found</div>';
        return;
    }
    
    container.innerHTML = '';
    sites.forEach(site => {
        const div = document.createElement('div');
        div.className = 'px-3 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0';
        div.innerHTML = `
            <div class="font-medium text-sm text-gray-900">${site.site_id}</div>
            <div class="text-xs text-gray-500">${site.location || 'No location'}</div>
            <div class="text-xs text-gray-400">${site.city || ''} ${site.state || ''}</div>
        `;
        div.addEventListener('click', function() {
            addSiteToSelection(site);
        });
        container.appendChild(div);
    });
}

function getSelectedSitesFromDropdown() {
    const container = document.getElementById('selectedSitesContainer');
    if (!container) return [];
    const siteTags = container.querySelectorAll('[data-site-id]');
    const selectedSites = [];
    siteTags.forEach(tag => {
        const siteId = tag.getAttribute('data-site-id');
        const siteIdText = tag.textContent.trim().split('\n')[0];
        selectedSites.push({ id: siteId, site_id: siteIdText });
    });
    return selectedSites;
}

function populateSitesTable(foundSites, notFoundSites) {
    const tbody = document.getElementById('sitesConfirmationTableBody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    foundSites.forEach(site => {
        const row = document.createElement('tr');
        row.className = 'hover:bg-gray-50';
        row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">
                <input type="checkbox" value="${site.id}" data-site-id="${site.site_id}" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" onchange="updateOperationsVisibility()">
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${site.site_id}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${site.location || '-'}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${site.city || '-'}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${site.state || '-'}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${site.customer || '-'}</td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusBadgeClass(site.activity_status)}">
                    ${site.activity_status || 'pending'}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${site.vendor_name || site.vendor || '-'}</td>
        `;
        tbody.appendChild(row);
    });
    
    const countSpan = document.getElementById('confirmedSitesCount');
    if (countSpan) countSpan.textContent = foundSites.length;
    
    if (notFoundSites.length > 0) {
        document.getElementById('notFoundSitesSection').classList.remove('hidden');
        document.getElementById('notFoundSitesList').textContent = notFoundSites.join(', ');
    } else {
        document.getElementById('notFoundSitesSection').classList.add('hidden');
    }
}

function addSiteToSelection(site) {
    const container = document.getElementById('selectedSitesContainer');
    if (!container) return;
    if (container.querySelector('p')) container.innerHTML = '';
    if (container.querySelector(`[data-site-id="${site.id}"]`)) return;
    
    const siteTag = document.createElement('span');
    siteTag.className = 'inline-flex items-center px-3 py-1 rounded-full text-sm bg-indigo-100 text-indigo-800 mr-2 mb-2';
    siteTag.setAttribute('data-site-id', site.id);
    siteTag.innerHTML = `
        ${site.site_id}
        <button type="button" class="ml-2 inline-flex items-center justify-center w-4 h-4 rounded-full text-indigo-400 hover:text-indigo-600" onclick="removeSiteFromSelection(${site.id})">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
        </button>
    `;
    container.appendChild(siteTag);
    document.getElementById('siteSearchDropdown').value = '';
    hideDropdownResults();
}

function removeSiteFromSelection(siteId) {
    const siteTag = document.querySelector(`[data-site-id="${siteId}"]`);
    if (siteTag) siteTag.remove();
    const container = document.getElementById('selectedSitesContainer');
    if (container && container.children.length === 0) {
        container.innerHTML = '<p class="text-sm text-gray-500">No sites selected</p>';
    }
}

function getSelectedSitesFromTable() {
    const checkboxes = document.querySelectorAll('#sitesConfirmationTableBody input[type="checkbox"]:checked');
    return Array.from(checkboxes).map(cb => cb.getAttribute('data-site-id'));
}

function previewBulkUpdate() {
    const selectedSites = getSelectedSitesFromTable();
    const field = document.getElementById('updateField').value;
    const value = document.getElementById('newValue').value;
    
    if (selectedSites.length === 0 || !field || !value) {
        showToast('Please select sites, field, and new value', 'warning');
        return;
    }
    
    fetch('api/validate-sites.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ site_ids: selectedSites, field: field, value: value })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const validation = data.validation;
            let message = `Preview Results:\n• ${validation.total_to_update} sites will be updated\n`;
            if (validation.sites_already_set.length > 0) message += `• ${validation.sites_already_set.length} already have this value\n`;
            if (validation.sites_not_found.length > 0) message += `• ${validation.sites_not_found.length} not found\n`;
            showToast(message, 'info');
        } else {
            showToast('Preview failed: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error previewing changes:', error);
        showToast('Error previewing changes', 'error');
    });
}

function handleBulkUpdate(e) {
    e.preventDefault();
    const selectedSites = getSelectedSitesFromTable();
    const field = document.getElementById('updateField').value;
    const value = document.getElementById('newValue').value;
    
    if (selectedSites.length === 0 || !field || !value) {
        showToast('Please select sites, field, and new value', 'warning');
        return;
    }
    
    if (!confirm(`Are you sure you want to update ${selectedSites.length} sites?`)) return;
    
    fetch('api/bulk-update-sites.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ sites: selectedSites, field: field, value: value })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(`Successfully updated ${data.updated_count} sites`, 'success');
            const currentSiteIds = Array.from(document.querySelectorAll('#sitesConfirmationTableBody tr')).map(row => {
                return row.querySelector('input[type="checkbox"]').getAttribute('data-site-id');
            });
            fetchSitesFromAPI(currentSiteIds, 'refresh');
        } else {
            showToast('Update failed: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error updating sites:', error);
    });
}

function handleBulkDelegation(e) {
    e.preventDefault();
    const selectedSites = getSelectedSitesFromTable();
    const vendorId = document.getElementById('delegationVendor').value;
    const delegationDate = document.getElementById('delegationDate').value;
    const priority = document.getElementById('delegationPriority').value;
    const notes = document.getElementById('delegationNotes').value;
    
    if (selectedSites.length === 0 || !vendorId) {
        showToast('Please select sites and vendor', 'warning');
        return;
    }
    
    if (!confirm(`Are you sure you want to delegate ${selectedSites.length} sites?`)) return;
    
    fetch('api/bulk-delegate-sites.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            sites: selectedSites,
            vendor_id: vendorId,
            delegation_date: delegationDate,
            priority: priority,
            notes: notes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(`Successfully delegated ${data.delegated_count} sites`, 'success');
            document.getElementById('bulkDelegationForm').reset();
            const currentSiteIds = Array.from(document.querySelectorAll('#sitesConfirmationTableBody tr')).map(row => {
                return row.querySelector('input[type="checkbox"]').getAttribute('data-site-id');
            });
            fetchSitesFromAPI(currentSiteIds, 'refresh');
        } else {
            showToast('Delegation failed: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error delegating sites:', error);
    });
}

function showSitesConfirmationSection() { document.getElementById('sitesConfirmationSection').classList.remove('hidden'); }
function hideSitesConfirmationSection() {
    document.getElementById('sitesConfirmationSection').classList.add('hidden');
    document.getElementById('sitesConfirmationTableBody').innerHTML = '';
}
function showOperationsSection() { document.getElementById('operationsSection').classList.remove('hidden'); }
function hideOperationsSection() { document.getElementById('operationsSection').classList.add('hidden'); }
function updateOperationsVisibility() {
    const selected = document.querySelectorAll('#sitesConfirmationTableBody input[type="checkbox"]:checked');
    if (selected.length > 0) showOperationsSection(); else hideOperationsSection();
}
function showDropdownResults() { document.getElementById('siteDropdownResults').classList.remove('hidden'); }
function hideDropdownResults() { document.getElementById('siteDropdownResults').classList.add('hidden'); }
function showToast(message, type = 'info') {
    if (typeof window.showToast === 'function') window.showToast(message, type);
    else alert(message);
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>