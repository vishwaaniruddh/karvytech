<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/compatibility.php';
require_once __DIR__ . '/../../controllers/SitesController.php';
require_once __DIR__ . '/../../includes/master_functions.php';

require_once __DIR__ . '/../../models/Vendor.php';
require_once __DIR__ . '/../../models/SiteDelegation.php';

// Try to load PhpSpreadsheet if available
$phpSpreadsheetAvailable = false;
if (COMPOSER_AVAILABLE) {
    try {
        require_once __DIR__ . '/../../vendor/autoload.php';
        $phpSpreadsheetAvailable = class_exists('PhpOffice\PhpSpreadsheet\IOFactory');
    } catch (Exception $e) {
        logCompatibilityIssue("Failed to load PhpSpreadsheet: " . $e->getMessage());
    }
}

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

// Handle AJAX requests (return JSON)
$isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || 
          (isset($_POST['ajax']) && $_POST['ajax'] === '1');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAjax) {
    // Start output buffering to catch any stray output
    ob_start();
    
    // Suppress deprecation warnings from appearing in output
    $oldErrorReporting = error_reporting();
    error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    
    // Set up custom error handler for AJAX that outputs JSON
    set_error_handler(function($errno, $errstr, $errfile, $errline) {
        // Don't output anything, just log it
        error_log("PHP Error [$errno]: $errstr in $errfile on line $errline");
        return true; // Suppress the error
    });
    
    // Register shutdown function to catch fatal errors
    register_shutdown_function(function() use (&$oldErrorReporting) {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            error_log("Fatal error: " . $error['message'] . " in " . $error['file'] . " on line " . $error['line']);
            ob_end_clean();
            error_reporting($oldErrorReporting);
            echo json_encode([
                'success' => false,
                'message' => 'A fatal error occurred during upload processing. Please check the error logs.'
            ]);
        }
    });
    
    header('Content-Type: application/json');
    
    // Log the request for debugging (minimal logging to avoid output issues)
    error_log("Bulk upload AJAX request received");
    error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
    error_log("Content type: " . ($_SERVER['CONTENT_TYPE'] ?? 'Not set'));

    try {
        // Debug mode - add debug info to response
        $debugInfo = [
            'method' => $_SERVER['REQUEST_METHOD'],
            'ajax_detected' => $isAjax,
            'files_count' => count($_FILES),
            'files_keys' => array_keys($_FILES),
            'post_keys' => array_keys($_POST),
            'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'Not set',
            'content_length' => $_SERVER['CONTENT_LENGTH'] ?? 'Not set'
        ];
        
        // Check if file was uploaded
        if (!isset($_FILES['excel_file'])) {
            error_log("ERROR: excel_file not found in _FILES array");
            error_log("Available _FILES keys: " . implode(', ', array_keys($_FILES)));
            
            echo json_encode([
                'success' => false,
                'message' => 'No file field found in upload. Please select a file.',
                'debug' => $debugInfo
            ]);
            exit;
        }
        
        $file = $_FILES['excel_file'];
        
        // Check for upload errors with detailed messages
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds server upload limit (' . ini_get('upload_max_filesize') . '). Please use a smaller file.',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds form upload limit.',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded. Please try again.',
                UPLOAD_ERR_NO_FILE => 'No file was selected for upload.',
                UPLOAD_ERR_NO_TMP_DIR => 'Server error: Missing temporary folder.',
                UPLOAD_ERR_CANT_WRITE => 'Server error: Failed to write file to disk.',
                UPLOAD_ERR_EXTENSION => 'Server error: File upload stopped by extension.'
            ];
            
            $errorMessage = $errorMessages[$file['error']] ?? 'Unknown upload error (code: ' . $file['error'] . ')';
            throw new Exception($errorMessage);
        }
        
        // Check if file is empty
        if ($file['size'] === 0) {
            throw new Exception('The uploaded file is empty. Please select a valid file.');
        }
        
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['xlsx', 'xls'];
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new Exception('Invalid file type. Please upload an Excel file (.xlsx or .xls only)');
        }
        
        // Check if PhpSpreadsheet is available
        if (!$phpSpreadsheetAvailable) {
            throw new Exception('Excel file processing requires PhpSpreadsheet library. Please contact administrator to install dependencies.');
        }
        
        // Check file size against server limits
        $maxFileSize = min(
            parseSize(ini_get('upload_max_filesize')),
            parseSize(ini_get('post_max_size')),
            10 * 1024 * 1024  // 10MB
        );
        
        if ($file['size'] > $maxFileSize) {
            $maxFileSizeFormatted = formatFileSize($maxFileSize);
            throw new Exception("File size too large. Maximum size is {$maxFileSizeFormatted}");
        }
        
        $uploadDir = __DIR__ . '/../../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $uploadPath = $uploadDir . 'vendorassigned_' . time() . '_' . $file['name'];
        
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            throw new Exception('Failed to save uploaded file');
        }
        
        // Process the Excel file
        $results = processVendorExcel($uploadPath);
        
        // Clean up uploaded file
        unlink($uploadPath);
        
        // Clean output buffer and return detailed results
        ob_end_clean();
        error_reporting($oldErrorReporting);
        
        echo json_encode([
            'success' => $results['success'],
            'message' => generateSummaryMessage($results),
            'summary' => [
                'processed' => $results['processed'],
                'created' => $results['created'],
                'updated' => $results['updated'],
                'skipped' => $results['skipped'],
                'failed' => $results['failed']
            ],
            'rows' => $results['rows'],
            'errors' => $results['errors']
        ]);
        
    } catch (Exception $e) {
        // Clean output buffer on error
        ob_end_clean();
        error_reporting($oldErrorReporting);
        
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// Handle regular form submission (non-AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Check if file was uploaded
        if (!isset($_FILES['excel_file'])) {
            throw new Exception('No file field found in upload. Please select a file.');
        }
        
        $file = $_FILES['excel_file'];
        
        // Check for upload errors with detailed messages
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds server upload limit (' . ini_get('upload_max_filesize') . '). Please use a smaller file.',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds form upload limit.',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded. Please try again.',
                UPLOAD_ERR_NO_FILE => 'No file was selected for upload.',
                UPLOAD_ERR_NO_TMP_DIR => 'Server error: Missing temporary folder.',
                UPLOAD_ERR_CANT_WRITE => 'Server error: Failed to write file to disk.',
                UPLOAD_ERR_EXTENSION => 'Server error: File upload stopped by extension.'
            ];
            
            $errorMessage = $errorMessages[$file['error']] ?? 'Unknown upload error (code: ' . $file['error'] . ')';
            throw new Exception($errorMessage);
        }
        
        // Check if file is empty
        if ($file['size'] === 0) {
            throw new Exception('The uploaded file is empty. Please select a valid file.');
        }
        
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['xlsx', 'xls'];
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new Exception('Invalid file type. Please upload an Excel file (.xlsx or .xls only)');
        }
        
        // Check if PhpSpreadsheet is available
        if (!$phpSpreadsheetAvailable) {
            throw new Exception('Excel file processing requires PhpSpreadsheet library. Please contact administrator to install dependencies.');
        }
        
        // Check file size against server limits
        $maxFileSize = min(
            parseSize(ini_get('upload_max_filesize')),
            parseSize(ini_get('post_max_size')),
            10 * 1024 * 1024  // 10MB
        );
        
        if ($file['size'] > $maxFileSize) {
            $maxFileSizeFormatted = formatFileSize($maxFileSize);
            throw new Exception("File size too large. Maximum size is {$maxFileSizeFormatted}");
        }
        
        $uploadDir = __DIR__ . '/../../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $uploadPath = $uploadDir . 'vendorassigned_' . time() . '_' . $file['name'];
        
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            throw new Exception('Failed to save uploaded file');
        }
        
        // Process the Excel file
        $results = processVendorExcel($uploadPath);
        
        // Clean up uploaded file
        unlink($uploadPath);
        
        $success = $results['success'];
        $message = $results['success'] ? 
            "Successfully processed {$results['processed']} vendor assign. {$results['created']} created, {$results['updated']} updated." :
            'Bulk upload completed with errors';
        $uploadResult = $results;
        
    } catch (Exception $e) {
        $success = false;
        $message = $e->getMessage();
        $uploadResult = null;
    }
}

// Show UI for GET requests or after form submission
$title = 'Bulk Upload Vendor Assigned';
ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Bulk Upload Vendor Assigned</h1>
        <p class="mt-2 text-sm text-gray-700">Upload multiple Vendor Assigned using Excel file</p>
    </div>
    <div class="flex space-x-2">
        <a href="vendor_download_template.php" class="btn btn-secondary">
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

<?php if (isset($success)): ?>
    <?php if ($success): ?>
        <!-- Success Message -->
        <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800"><?php echo htmlspecialchars($message); ?></h3>
                    <?php if ($uploadResult): ?>
                        <div class="mt-2 text-sm text-green-700">
                            <p>Upload Summary:</p>
                            <ul class="list-disc list-inside mt-1">
                                <li>Total processed: <?php echo $uploadResult['processed'] ?? 0; ?> rows</li>
                                <li>Successfully created: <?php echo $uploadResult['created'] ?? 0; ?> sites</li>
                                <li>Successfully updated: <?php echo $uploadResult['updated'] ?? 0; ?> sites</li>
                                <?php if (isset($uploadResult['errors']) && count($uploadResult['errors']) > 0): ?>
                                    <li class="text-red-600">Errors: <?php echo count($uploadResult['errors']); ?> rows</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Error Message -->
        <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Upload Failed</h3>
                    <p class="mt-1 text-sm text-red-700"><?php echo htmlspecialchars($message); ?></p>
                    <?php if ($uploadResult && isset($uploadResult['errors']) && count($uploadResult['errors']) > 0): ?>
                        <div class="mt-2">
                            <p class="text-sm font-medium text-red-800">Error Details:</p>
                            <ul class="mt-1 text-sm text-red-700 list-disc list-inside">
                                <?php foreach (array_slice($uploadResult['errors'], 0, 10) as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                                <?php if (count($uploadResult['errors']) > 10): ?>
                                    <li>... and <?php echo count($uploadResult['errors']) - 10; ?> more errors</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- Instructions Card -->
<div class="card mb-6">
    <div class="card-body">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Upload Instructions</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="font-medium text-gray-900 mb-2">File Requirements</h4>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>• Supported formats: Excel (.xlsx, .xls) only</li>
                    <li>• Maximum file size: <?php 
                        $maxSize = min(
                            parseSize(ini_get('upload_max_filesize')),
                            parseSize(ini_get('post_max_size')),
                            10 * 1024 * 1024
                        );
                        echo formatFileSize($maxSize);
                    ?></li>
                    <li>• First row should contain column headers with Site ID</li>
                    <li>• Required rows: Site ID, Vendor Id</li>
                </ul>
            </div>
            
            <div>
                <h4 class="font-medium text-gray-900 mb-2">Row Order</h4>
                <ol class="text-sm text-gray-600 space-y-1 ordered_list">
                    <li>Site ID (Required)</li>
                    <li>Vendor ID (Required)</li>
                    <li>Delegated By</li>
                    <li>Delegation Date</li>
                    <li>Status</li>
                    <li>Notes</li>
                    
                </ol>
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
                        • Master data (Country, State, City, Customer) must exist in the system<br>
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
        
        <form method="POST" enctype="multipart/form-data" id="uploadForm">
            <div class="form-group">
                <label for="excel_file" class="form-label">Select Excel File *</label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors" id="dropZone">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-sm text-gray-600">
                            <label for="excel_file" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                <span>Upload a file</span>
                                <input id="excel_file" name="excel_file" type="file" class="sr-only" accept=".xlsx,.xls" required>
                            </label>
                            <p class="pl-1">or drag and drop</p>
                        </div>
                        <p class="text-xs text-gray-500">Excel (.xlsx, .xls) files up to <?php 
                            $maxSize = min(
                                parseSize(ini_get('upload_max_filesize')),
                                parseSize(ini_get('post_max_size')),
                                10 * 1024 * 1024
                            );
                            echo formatFileSize($maxSize);
                        ?></p>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end space-x-4 mt-6">
                <a href="index.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                    Upload Vendor Assigned
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Results Display -->
<div id="uploadResults" class="card mt-6" style="display: none;">
    <div class="card-body">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Upload Results</h3>
        
        <!-- Summary -->
        <div id="uploadSummary" class="mb-6"></div>
        
        <!-- Detailed Results Table -->
        <div id="detailedResults" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">Column</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Site ID</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider max-w-xs">Vendor ID</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Action</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Status</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                    </tr>
                </thead>
                <tbody id="resultsTableBody" class="bg-white divide-y divide-gray-200">
                    <!-- Results will be populated here -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// File upload handling
const fileInput = document.getElementById('excel_file');
const dropZone = document.getElementById('dropZone');

if (fileInput) {
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            updateFileDisplay(file);
        }
    });
}

// Drag and drop handling
if (dropZone) {
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
        if (files.length > 0 && fileInput) {
            fileInput.files = files;
            updateFileDisplay(files[0]);
        }
    });
}

function updateFileDisplay(file) {
    const dropZoneContent = dropZone.querySelector('.space-y-1');
    if (dropZoneContent) {
        dropZoneContent.innerHTML = `
            <svg class="mx-auto h-12 w-12 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
            </svg>
            <div class="text-sm text-gray-900">
                <span class="font-medium">${file.name}</span>
            </div>
            <p class="text-xs text-gray-500">${formatFileSize(file.size)} • ${file.type || 'Unknown type'}</p>
        `;
    }
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// AJAX form submission
const uploadForm = document.getElementById('uploadForm');
if (uploadForm) {
    uploadForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('submitBtn');
        const originalText = submitBtn.innerHTML;
        
        // Debug: Check if file is selected
        if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
            alert('Please select a file to upload.');
            return;
        }
        
        const file = fileInput.files[0];
        console.log('File selected:', {
            name: file.name,
            size: file.size,
            type: file.type
        });
        
        // Create FormData and explicitly add the file
        const formData = new FormData();
        formData.append('excel_file', file);
        formData.append('ajax', '1');
        
        // Debug: Log FormData contents
        console.log('FormData contents:');
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }
        
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = `
            <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Processing...
        `;
        
        // Hide previous results
        const uploadResults = document.getElementById('uploadResults');
        if (uploadResults) {
            uploadResults.style.display = 'none';
        }
        
        fetch(window.location.href, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return response.text().then(text => {
                console.log('Raw response:', text);
                console.log('Response length:', text.length);
                
                // Show the raw response in an alert for debugging
                if (text.length === 0) {
                    alert('ERROR: Server returned empty response!\n\nCheck browser console and server logs for details.');
                    throw new Error('Empty response from server');
                }
                
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('JSON parse error:', e);
                    // Show the actual response in an alert
                    alert('ERROR: Invalid JSON response from server!\n\nRaw response:\n' + text.substring(0, 500));
                    throw new Error('Invalid JSON response: ' + text.substring(0, 200));
                }
            });
        })
        .then(data => {
            console.log('Parsed response:', data);
            console.log('Errors:', data.errors);
            console.log('Rows:', data.rows);
            
            if (data.debug) {
                // Show debug information
                alert('Debug Info:\n' + JSON.stringify(data, null, 2));
            }
            
            // Show detailed errors if any
            if (data.errors && data.errors.length > 0) {
                console.error('Upload errors:', data.errors);
            }
            
            displayResults(data);
        })
        .catch(error => {
            console.error('Error:', error);
            displayError('Upload failed: ' + error.message);
        })
        .finally(() => {
            // Reset button
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });
}

function displayResults(data) {
    const resultsDiv = document.getElementById('uploadResults');
    const summaryDiv = document.getElementById('uploadSummary');
    const tableBody = document.getElementById('resultsTableBody');
    
    if (!resultsDiv || !summaryDiv || !tableBody) return;
    
    // Show results section
    resultsDiv.style.display = 'block';
    resultsDiv.scrollIntoView({ behavior: 'smooth' });
    
    // Display summary
    const summaryClass = data.success ? 'bg-green-100 border-green-400 text-green-700' : 'bg-yellow-100 border-yellow-400 text-yellow-700';
    summaryDiv.innerHTML = `
        <div class="border px-4 py-3 rounded ${summaryClass}">
            <div class="flex">
                <div class="flex-shrink-0">
                    ${data.success ? 
                        '<svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>' :
                        '<svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>'
                    }
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium">${data.message}</h3>
                    ${data.summary ? `
                        <div class="mt-2 text-sm">
                            <p>Processed: ${data.summary.processed} | Created: ${data.summary.created} | Updated: ${data.summary.updated} | Skipped: ${data.summary.skipped} | Failed: ${data.summary.failed}</p>
                        </div>
                    ` : ''}
                </div>
            </div>
        </div>
    `;
    
    // Show error alert if there are failures
    if (data.summary && data.summary.failed > 0 && data.errors && data.errors.length > 0) {
        const errorList = data.errors.slice(0, 5).join('\n');
        const moreErrors = data.errors.length > 5 ? `\n... and ${data.errors.length - 5} more errors` : '';
        alert('Upload completed with errors:\n\n' + errorList + moreErrors + '\n\nCheck the detailed results table below for more information.');
    }
    
    // Display detailed results
    if (data.rows && data.rows.length > 0) {
        tableBody.innerHTML = data.rows.map(row => {
            const statusClass = getStatusClass(row.status);
            const actionBadge = getActionBadge(row.action);
            
            // Truncate location if too long
            const location = row.location || '-';
            const truncatedLocation = location.length > 50 ? location.substring(0, 50) + '...' : location;
            
            return `
                <tr class="${row.status === 'failed' ? 'bg-red-50' : ''}">
                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">${row.col}</td>
                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900 font-medium">${row.site_id || '-'}</td>
                    <td class="px-3 py-2 whitespace-nowrap">${actionBadge}</td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">
                            ${row.status}
                        </span>
                    </td>
                    <td class="px-3 py-2 text-sm text-gray-900">
                        ${row.message}
                        ${row.errors && row.errors.length > 0 ? `
                            <div class="mt-1 text-xs text-red-600">
                                ${row.errors.join(', ')}
                            </div>
                        ` : ''}
                    </td>
                </tr>
            `;
        }).join('');
    } else {
        tableBody.innerHTML = '<tr><td colspan="6" class="px-3 py-2 text-center text-gray-500">No data to display</td></tr>';
    }
}

function getStatusClass(status) {
    switch (status) {
        case 'success': return 'bg-green-100 text-green-800';
        case 'failed': return 'bg-red-100 text-red-800';
        case 'skipped': return 'bg-gray-100 text-gray-800';
        default: return 'bg-yellow-100 text-yellow-800';
    }
}

function getActionBadge(action) {
    if (!action) return '-';
    
    const badges = {
        'create': '<span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded">Create</span>',
        'update': '<span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded">Update</span>'
    };
    
    return badges[action] || action;
}

function displayError(message) {
    const resultsDiv = document.getElementById('uploadResults');
    const summaryDiv = document.getElementById('uploadSummary');
    
    if (!resultsDiv || !summaryDiv) return;
    
    resultsDiv.style.display = 'block';
    summaryDiv.innerHTML = `
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium">Upload Error</h3>
                    <p class="mt-1 text-sm">${message}</p>
                </div>
            </div>
        </div>
    `;
    
    const tableBody = document.getElementById('resultsTableBody');
    if (tableBody) {
        tableBody.innerHTML = '';
    }
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
   
?>

<?php
// function processVendorExcel($filePath) {
//     $results = [
//         'success' => true,
//         'processed' => 0,
//         'created' => 0,
//         'updated' => 0,
//         'skipped' => 0,
//         'failed' => 0,
//         'errors' => [],
//         'rows' => []  // Detailed row-by-row status
//     ];
    
//     try {
//         // Only Excel files are supported
//         $data = readExcelFile($filePath);
//         $siteModel = new Site();
//         $vendorModel = new Vendor();
//         $siteDelegationModel = new SiteDelegation();
//       //  echo count($data[3]);

//         $sites = [];

//         foreach ($data[0] as $col => $value) {
//             if (!empty($value) && $col !== 'A' && $col !== 'B' && $col !== 'C') {
//                 $sites[$col] = $value; // column => SiteId
//             }
//         }
        
//          $result = [];

//         foreach ($sites as $col => $siteId) {
//             if($col>=3){
//                 try{
//                     $rowResult = [
//                         'col' => $col,
//                         'status' => 'processing',
//                         'action' => '',
//                         'site_id' => '',
//                         'vendor_id' => '',
//                         'message' => '',
//                         'errors' => []
//                     ];
//                     $rowResult['site_id'] = $siteId;
//                     $existingSiteBySiteId = $siteModel->findBySiteId($siteId);
//                     $_site_id = '';
//                     if (!$existingSiteBySiteId) {
//                         // Reject duplicate site_id
//                         $rowResult['status'] = 'failed';
//                         $rowResult['action'] = 'rejected';
//                         $rowResult['message'] = "Site ID '{$siteId}' does not exists in database";
//                         $rowResult['errors'][] = 'Site ID Does Not Exist';
//                         $results['failed']++;
//                         $results['errors'][] = "Col {$col}: Site ID '{$siteId}' does not exists";
//                         $results['rows'][] = $rowResult;
//                         continue;
//                     }else{
//                         $_site_id = $existingSiteBySiteId['id'];
//                     }
    
//                     $vendorId = $data[1][$col] ?? '';
//                     $rowResult['vendor_code'] = $vendorId;
//                     if($vendorId!=''){
//                         $existingVendorByVendorCode = $vendorModel->findByVendorCode($vendorCode);
//                         $_vendor_id = 0;   
//                         if (!$existingVendorByVendorCode) {
//                             // Reject duplicate store_id
//                             $rowResult['status'] = 'failed';
//                             $rowResult['action'] = 'rejected';
//                             $rowResult['message'] = "Vendor ID '{$vendorId}' does not exists in database";
//                             $rowResult['errors'][] = 'Vendor ID does not exist';
//                             $results['failed']++;
//                             $results['errors'][] = "Col {$col}: Vendor ID '{$vendorId}' does not exists";
//                             $results['rows'][] = $rowResult;
//                             continue;
//                         }else{
//                             $_vendor_id = $existingVendorByVendorCode['id'];
//                         }
//                     }else{
//                         $_vendor_id = 0;
//                     }
                    
                
//              // Create new User (no duplicates found)

//                 $rowResult['action'] = 'create';
//                 $sites = $siteModel->create($sites['data']);
                
//                 if ($sites) {
//                     $rowResult['status'] = 'success';
//                     $rowResult['message'] = 'Vendor Assigned successfully';
//                     $results['created']++;
//                 } else {
//                     $rowResult['status'] = 'failed';
//                     $rowResult['message'] = 'Failed to create Vendor Assigned  in database';
//                     $rowResult['errors'][] = 'Database insert failed';
//                     $results['failed']++;
//                     // $results['errors'][] = "Row {$rowNumber}: Failed to create user {$data['username']}";
//                     $results['errors'][] = "Row {$rowNumber}: Failed to create user {$sites['data']}";

//                 }
//                     // Add row result to results
//                     $results['rows'][] = $rowResult;
//                     $results['processed']++;
                    
//                 } catch (Exception $e) {
//                     $rowResult['status'] = 'failed';
//                     $rowResult['message'] = 'Unexpected error: ' . $e->getMessage();
//                     $rowResult['errors'][] = $e->getMessage();
//                     $results['cols'][] = $col;
//                     $results['failed']++;
//                     $results['errors'][] = "Col {$col}: " . $e->getMessage();
//                 }    
                    
//             }
            
//         }
         
//         //echo '<pre>';print_r($result);echo '</pre>';die;
        
//         // Set overall success status
//         $results['success'] = ($results['failed'] === 0);
        
//     } catch (Exception $e) {
//         $results['success'] = false;
//         $results['errors'][] = 'File processing error: ' . $e->getMessage();
//     }
    
//     return $results;
// }

function processVendorExcel($filePath)
{
    $results = [
        'success' => true,
        'processed' => 0,
        'created' => 0,
        'skipped' => 0,
        'failed' => 0,
        'errors' => [],
        'rows' => []
    ];

    try {
        $rows = readExcelFile($filePath);

        $siteModel = new Site();
        $vendorModel = new Vendor();
        $delegationModel = new SiteDelegation();

        foreach ($rows as $index => $row) {

            // Skip header row
            if ($index === 0) continue;

            $rowNumber = $index + 1;

            $rowResult = [
                'row' => $rowNumber,
                'site_id' => $row[0] ?? '',
                'vendor_id' => $row[1] ?? '',
                'status' => 'processing',
                'message' => ''
            ];

            try {
                // Normalize row
                $row = array_pad($row, 6, '');

                [$siteCode, $vendorCode, $delegatedBy, $delegationDate, $status, $notes] = $row;

                // Validate required
                if (!$siteCode || !$vendorCode || !$delegatedBy || !$delegationDate) {
                    throw new Exception('Required field missing');
                }

                // Validate Site
                $site = $siteModel->findBySiteId($siteCode);
                if (!$site) {
                    throw new Exception("Site '{$siteCode}' not found");
                }

                // Validate Vendor
                // $vendor = $vendorModel->findByVendorCode($vendorCode);
                // if (!$vendor) {
                //     throw new Exception("Vendor '{$vendorCode}' not found");
                // }
                
                if (is_numeric($vendorCode)) {
                        $vendor = $vendorModel->findById($vendorCode);
                    } else {
                        $vendor = $vendorModel->findByVendorCode($vendorCode);
                    }
                    
                    if (!$vendor) {
                        throw new Exception("Vendor '{$vendorCode}' not found");
                    }

                

                // Check existing active delegation
                $existing = $delegationModel->getActiveDelegation($site['id']);
                if ($existing) {
                    $rowResult['status'] = 'skipped';
                    $rowResult['message'] = 'Already delegated';
                    $results['skipped']++;
                    $results['rows'][] = $rowResult;
                    continue;
                }

                // Create delegation
                $delegationModel->create([
                    'site_id' => $site['id'],
                    'vendor_id' => $vendor['id'],
                    'delegated_by' => $delegatedBy,
                    'delegation_date' => $delegationDate,
                    'status' => $status ?: 'active',
                    'notes' => $notes
                ]);

                $rowResult['status'] = 'success';
                $rowResult['message'] = 'Delegated successfully';

                $results['created']++;

            } catch (Exception $e) {
                $rowResult['status'] = 'failed';
                $rowResult['message'] = $e->getMessage();
                $results['failed']++;
                $results['errors'][] = "Row {$rowNumber}: " . $e->getMessage();
            }

            $results['processed']++;
            $results['rows'][] = $rowResult;
        }

        $results['success'] = ($results['failed'] === 0);

    } catch (Exception $e) {
        $results['success'] = false;
        $results['errors'][] = $e->getMessage();
    }

    return $results;
}







function generateSummaryMessage($results) {
    $parts = [];
    
    if ($results['created'] > 0) {
        $parts[] = "{$results['created']} created";
    }
    if ($results['updated'] > 0) {
        $parts[] = "{$results['updated']} updated";
    }
    if ($results['skipped'] > 0) {
        $parts[] = "{$results['skipped']} skipped";
    }
    if ($results['failed'] > 0) {
        $parts[] = "{$results['failed']} failed";
    }
    
    $summary = implode(', ', $parts);
    
    if ($results['success']) {
        return "Upload completed successfully: {$summary}";
    } else {
        return "Upload completed with issues: {$summary}";
    }
}

function readExcelFile($filePath) {
    global $phpSpreadsheetAvailable;
    
    // Check if PhpSpreadsheet is available
    if (!$phpSpreadsheetAvailable || !class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
        throw new Exception('Excel file processing requires PhpSpreadsheet library. Please contact administrator to install dependencies.');
    }
    
    try {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $data = [];
        
        $highestRow = $worksheet->getHighestRow();
        $highestColumn = $worksheet->getHighestColumn();
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
        
        // Skip header row (start from row 1)
        for ($row = 1; $row <= $highestRow; $row++) {
            $rowData = [];
            for ($col = 'A'; $col <= $highestColumn; $col++) {
                $cell = $worksheet->getCell($col . $row);
                $value = $cell->getValue();
                
                // Handle date cells
                if (\PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($cell)) {
                    $dateValue = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                    $value = $dateValue->format('Y-m-d');
                }
                
                $rowData[] = (string)$value;
            }
            
            // Skip completely empty rows
            if (!empty(array_filter($rowData))) {
                $data[] = $rowData;
            }
        }
        
        return $data;
        
    } catch (Exception $e) {
        throw new Exception('Error reading Excel file: ' . $e->getMessage());
    }
}

function validateAndProcessRow($row, $rowNumber) {
    $errors = [];
    
    // Expected columns: Site ID, Vendor ID, Delegated By, Delegation Date ,status,Notes
    $expectedColumns = 6;
    
    // Pad row with empty strings if it has fewer columns
    while (count($row) < $expectedColumns) {
        $row[] = '';
    }
    
    if (count($row) < 3) { // At least Site ID, Vendor ID, and Delegation field
        $errors[] = "Row {$rowNumber}: Insufficient data columns";
        return ['errors' => $errors];
    }
    
    $siteData = [
        'site_id' => trim($row[0] ?? ''),
        'vendor_id' => trim($row[1] ?? ''),
        'delegated_by' => trim($row[2] ?? ''),
        'delegation_date' => trim($row[3] ?? ''),
        'status' => trim($row[4] ?? ''),
        'notes' => trim($row[5] ?? '')
    ];
    
    // Validate required fields
    if (empty($siteData['site_id'])) {
        $errors[] = "Row {$rowNumber}: Site ID is required";
    }
    
    if (empty($siteData['vendor_id'])) {
        $errors[] = "Row {$rowNumber}: Vendor ID is required";
    }
    if (empty($siteData['delegated_by'])) {
        $errors[] = "Row {$rowNumber}: Delegate By is required";
    }
    if (empty($siteData['delegation_date'])) {
        $errors[] = "Row {$rowNumber}: delegation date is required";
    }
    if (empty($siteData['status'])) {
        $errors[] = "Row {$rowNumber}: status is required";
    }
    if (empty($siteData['notes'])) {
        $errors[] = "Row {$rowNumber}: Notes is required";
    }
    
    
    
    return [
        'data' => $siteData,
        'errors' => $errors
    ];
}


function parseSize($size) {
    $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
    $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
    if ($unit) {
        // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
        return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
    } else {
        return round($size);
    }
}

function formatFileSize($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

?>