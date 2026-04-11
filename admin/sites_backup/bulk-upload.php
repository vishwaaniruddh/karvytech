<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/Site.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$title = 'Bulk Upload Sites';
ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Bulk Upload Sites</h1>
        <p class="mt-2 text-sm text-gray-700">Upload multiple sites using Excel or CSV file</p>
    </div>
    <div class="flex space-x-2">
        <a href="download_template.php" class="btn btn-secondary">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
            Download Template
        </a>
        <a href="index.php" class="btn btn-secondary">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
            </svg>
            Back to Sites
        </a>
    </div>
</div>

<!-- Instructions Card -->
<div class="card mb-6">
    <div class="card-body">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Upload Instructions</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="font-medium text-gray-900 mb-2">File Requirements</h4>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>• Supported formats: Excel (.xlsx, .xls) or CSV (.csv)</li>
                    <li>• Maximum file size: 10MB</li>
                    <li>• First row should contain column headers</li>
                    <li>• Required columns: Site ID, Location</li>
                </ul>
            </div>
            
            <div>
                <h4 class="font-medium text-gray-900 mb-2">Column Order</h4>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>1. Site ID (Required)</li>
                    <li>2. Store ID</li>
                    <li>3. Location (Required)</li>
                    <li>4. Country</li>
                    <li>5. State</li>
                    <li>6. City</li>
                    <li>7. Branch</li>
                    <li>8. Customer</li>
                    <li>9. Bank</li>
                    <li>10. PO Number</li>
                    <li>11. PO Date (YYYY-MM-DD)</li>
                    <li>12. Remarks</li>
                </ul>
            </div>
        </div>
        
        <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <h5 class="text-sm font-medium text-blue-800">Important Notes</h5>
                    <p class="text-sm text-blue-700 mt-1">
                        • Download the template file to ensure correct format<br>
                        • Master data (Country, State, City, Customer, Bank) must exist in the system<br>
                        • Existing sites will be updated, new sites will be created<br>
                        • Invalid data will be reported in the results
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Form -->
<div class="card">
    <div class="card-body">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Upload File</h3>
        
        <form id="bulkUploadForm" enctype="multipart/form-data">
            <div class="form-group">
                <label for="excel_file" class="form-label">Select Excel or CSV File *</label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-sm text-gray-600">
                            <label for="excel_file" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                <span>Upload a file</span>
                                <input id="excel_file" name="excel_file" type="file" class="sr-only" accept=".xlsx,.xls,.csv" required>
                            </label>
                            <p class="pl-1">or drag and drop</p>
                        </div>
                        <p class="text-xs text-gray-500">Excel (.xlsx, .xls) or CSV files up to 10MB</p>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end space-x-4 mt-6">
                <button type="button" onclick="resetForm()" class="btn btn-secondary">Reset</button>
                <button type="submit" class="btn btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                    Upload Sites
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Progress Section (Hidden by default) -->
<div id="progressSection" class="card mt-6 hidden">
    <div class="card-body">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Upload Progress</h3>
        
        <div class="mb-4">
            <div class="flex justify-between text-sm text-gray-600 mb-1">
                <span>Processing...</span>
                <span id="progressText">0%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div id="progressBar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
        </div>
        
        <div id="progressMessages" class="space-y-2">
            <!-- Progress messages will be added here -->
        </div>
    </div>
</div>

<!-- Results Section (Hidden by default) -->
<div id="resultsSection" class="card mt-6 hidden">
    <div class="card-body">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Upload Results</h3>
        
        <div id="resultsSummary" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <!-- Results summary will be populated here -->
        </div>
        
        <div id="resultsDetails">
            <!-- Detailed results will be populated here -->
        </div>
        
        <div class="flex justify-end space-x-4 mt-6">
            <button onclick="resetUpload()" class="btn btn-secondary">Upload Another File</button>
            <a href="index.php" class="btn btn-primary">View All Sites</a>
        </div>
    </div>
</div>

<script>
// File upload handling
document.getElementById('excel_file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        updateFileDisplay(file);
    }
});

// Drag and drop handling
const dropZone = document.querySelector('.border-dashed');
dropZone.addEventListener('dragover', function(e) {
    e.preventDefault();
    this.classList.add('border-blue-500', 'bg-blue-50');
});

dropZone.addEventListener('dragleave', function(e) {
    e.preventDefault();
    this.classList.remove('border-blue-500', 'bg-blue-50');
});

dropZone.addEventListener('drop', function(e) {
    e.preventDefault();
    this.classList.remove('border-blue-500', 'bg-blue-50');
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        document.getElementById('excel_file').files = files;
        updateFileDisplay(files[0]);
    }
});

function updateFileDisplay(file) {
    const dropZone = document.querySelector('.border-dashed .space-y-1');
    dropZone.innerHTML = `
        <svg class="mx-auto h-12 w-12 text-green-500" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
        </svg>
        <div class="text-sm text-gray-900">
            <span class="font-medium">${file.name}</span>
        </div>
        <p class="text-xs text-gray-500">${formatFileSize(file.size)} • ${file.type || 'Unknown type'}</p>
    `;
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Form submission
document.getElementById('bulkUploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const fileInput = document.getElementById('excel_file');
    if (!fileInput.files[0]) {
        showAlert('Please select a file to upload', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('excel_file', fileInput.files[0]);
    
    // Show progress section
    document.getElementById('progressSection').classList.remove('hidden');
    document.getElementById('resultsSection').classList.add('hidden');
    
    // Disable form
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Processing...';
    submitBtn.disabled = true;
    
    // Simulate progress
    let progress = 0;
    const progressInterval = setInterval(() => {
        progress += Math.random() * 15;
        if (progress > 90) progress = 90;
        updateProgress(progress, 'Processing file...');
    }, 500);
    
    // Upload file
    fetch('bulk_upload.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        clearInterval(progressInterval);
        updateProgress(100, 'Upload complete!');
        
        setTimeout(() => {
            document.getElementById('progressSection').classList.add('hidden');
            showResults(data);
            
            // Reset form
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }, 1000);
    })
    .catch(error => {
        clearInterval(progressInterval);
        console.error('Error:', error);
        showAlert('An error occurred during upload. Please try again.', 'error');
        
        // Reset form
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        document.getElementById('progressSection').classList.add('hidden');
    });
});

function updateProgress(percent, message) {
    document.getElementById('progressBar').style.width = percent + '%';
    document.getElementById('progressText').textContent = Math.round(percent) + '%';
    
    const messagesContainer = document.getElementById('progressMessages');
    const messageDiv = document.createElement('div');
    messageDiv.className = 'text-sm text-gray-600';
    messageDiv.innerHTML = `<span class="text-gray-500">${new Date().toLocaleTimeString()}</span> ${message}`;
    messagesContainer.appendChild(messageDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function showResults(data) {
    const resultsSection = document.getElementById('resultsSection');
    const summaryContainer = document.getElementById('resultsSummary');
    const detailsContainer = document.getElementById('resultsDetails');
    
    // Show results section
    resultsSection.classList.remove('hidden');
    
    // Create summary cards
    const processed = data.details?.processed || 0;
    const created = data.details?.created || 0;
    const updated = data.details?.updated || 0;
    const errors = data.details?.errors?.length || 0;
    
    summaryContainer.innerHTML = `
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="text-2xl font-bold text-blue-900">${processed}</div>
            <div class="text-sm text-blue-700">Total Processed</div>
        </div>
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="text-2xl font-bold text-green-900">${created}</div>
            <div class="text-sm text-green-700">Sites Created</div>
        </div>
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="text-2xl font-bold text-yellow-900">${updated}</div>
            <div class="text-sm text-yellow-700">Sites Updated</div>
        </div>
    `;
    
    // Show success or error message
    if (data.success) {
        detailsContainer.innerHTML = `
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <h4 class="text-sm font-medium text-green-800">Upload Successful</h4>
                        <p class="text-sm text-green-700 mt-1">${data.message}</p>
                    </div>
                </div>
            </div>
        `;
    } else {
        let errorsList = '';
        if (data.details?.errors && data.details.errors.length > 0) {
            errorsList = '<ul class="mt-2 text-sm text-red-700 list-disc list-inside">';
            data.details.errors.forEach(error => {
                errorsList += `<li>${error}</li>`;
            });
            errorsList += '</ul>';
        }
        
        detailsContainer.innerHTML = `
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-red-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <h4 class="text-sm font-medium text-red-800">Upload Completed with Errors</h4>
                        <p class="text-sm text-red-700 mt-1">${data.message}</p>
                        ${errorsList}
                    </div>
                </div>
            </div>
        `;
    }
}

function resetForm() {
    document.getElementById('bulkUploadForm').reset();
    const dropZone = document.querySelector('.border-dashed .space-y-1');
    dropZone.innerHTML = `
        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <div class="flex text-sm text-gray-600">
            <label for="excel_file" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                <span>Upload a file</span>
            </label>
            <p class="pl-1">or drag and drop</p>
        </div>
        <p class="text-xs text-gray-500">Excel (.xlsx, .xls) or CSV files up to 10MB</p>
    `;
}

function resetUpload() {
    document.getElementById('resultsSection').classList.add('hidden');
    document.getElementById('progressSection').classList.add('hidden');
    resetForm();
}

function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `fixed top-4 right-4 z-50 p-4 rounded-md shadow-lg ${
        type === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 
        'bg-red-100 text-red-800 border border-red-200'
    }`;
    alertDiv.innerHTML = `
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                ${type === 'success' ? 
                    '<path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>' :
                    '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>'
                }
            </svg>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>
</content>