<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Role.php';
require_once __DIR__ . '/../../models/Vendor.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

// Fetch Master Data for the bottom cards
$roleModel = new Role();
$vendorModel = new Vendor();

$allRoles = $roleModel->getAllRoles('active');
$displayRoles = array_filter($allRoles, function($r) {
    return strtolower($r['name']) !== 'superadmin';
});

$displayVendors = $vendorModel->getActiveVendors();

// Try to load PhpSpreadsheet if available
$phpSpreadsheetAvailable = false;
if (class_exists('Composer\Autoload\ClassLoader')) {
    try {
        require_once __DIR__ . '/../../vendor/autoload.php';
        $phpSpreadsheetAvailable = class_exists('PhpOffice\PhpSpreadsheet\IOFactory');
    } catch (Exception $e) {
        error_log("Failed to load PhpSpreadsheet: " . $e->getMessage());
    }
} else {
    // Check if it's already loaded or manually included somewhere
    $phpSpreadsheetAvailable = class_exists('PhpOffice\PhpSpreadsheet\IOFactory');
}

// Handle AJAX requests
$isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || 
          (isset($_POST['ajax']) && $_POST['ajax'] === '1');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAjax) {
    header('Content-Type: application/json');
    ob_start();
    
    try {
        if (!isset($_FILES['excel_file'])) {
            throw new Exception('No file selected for upload.');
        }
        
        $file = $_FILES['excel_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Upload error: ' . $file['error']);
        }
        
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExtension, ['xlsx', 'xls', 'csv'])) {
            throw new Exception('Invalid file type. Please upload an Excel or CSV file.');
        }
        
        if (!$phpSpreadsheetAvailable && in_array($fileExtension, ['xlsx', 'xls'])) {
            throw new Exception('Excel processing library (PhpSpreadsheet) is not installed. Please use CSV or contact admin.');
        }
        
        $uploadDir = __DIR__ . '/../../uploads/temp/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        
        $tempPath = $uploadDir . 'users_bulk_' . time() . '.' . $fileExtension;
        move_uploaded_file($file['tmp_name'], $tempPath);
        
        $results = processUserUpload($tempPath, $fileExtension);
        unlink($tempPath);
        
        ob_end_clean();
        echo json_encode($results);
    } catch (Exception $e) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

$title = 'Bulk User Upload';
ob_start();
?>

<div class="min-h-screen bg-gray-50 pb-12">
    <!-- Header Area -->
    <div class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                        <div class="p-2 bg-blue-600 rounded-lg text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        Bulk User Upload
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">Import multiple users into the system via Excel or CSV</p>
                </div>
                <div class="flex items-center gap-3">
                    <button onclick="downloadTemplate()" class="inline-flex items-center px-4 py-2 border border-blue-600 text-blue-600 bg-white hover:bg-blue-50 text-sm font-medium rounded-xl transition-all shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Download Template
                    </button>
                    <a href="../users/" class="inline-flex items-center px-4 py-2 bg-gray-900 text-white hover:bg-black text-sm font-medium rounded-xl transition-all shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Back to Users
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto px-4 mt-8">
        <!-- Main Upload Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-8">
                <div id="drop-zone" class="relative group border-2 border-dashed border-gray-300 rounded-2xl p-12 text-center transition-all hover:border-blue-400 hover:bg-blue-50/30 cursor-pointer">
                    <input type="file" id="user-file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept=".xlsx,.xls,.csv">
                    
                    <div class="transition-transform group-hover:scale-110 duration-300">
                        <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        </div>
                    </div>
                    
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Select your file to upload</h3>
                    <p class="text-gray-500 mb-6">Support for .xlsx, .xls and .csv formats</p>
                    
                    <div class="flex items-center justify-center gap-4">
                        <div class="px-4 py-2 bg-gray-100 rounded-lg text-sm text-gray-600 font-medium border border-gray-200">Max 5MB</div>
                        <span class="text-gray-300">|</span>
                        <div class="px-4 py-2 bg-gray-100 rounded-lg text-sm text-gray-600 font-medium border border-gray-200">Headers Req.</div>
                    </div>
                </div>

                <!-- Processing Overlay -->
                <div id="progress-container" class="hidden mt-8">
                    <div class="flex items-center justify-between mb-2">
                        <span id="progress-status" class="text-sm font-semibold text-blue-600">Initialiazing...</span>
                        <span id="progress-percent" class="text-sm font-bold text-gray-900">0%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                        <div id="progress-bar" class="bg-blue-600 h-full w-0 transition-all duration-300 shadow-sm shadow-blue-200"></div>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="p-6 bg-amber-50 rounded-2xl border border-amber-100">
                        <h4 class="font-bold text-amber-900 flex items-center gap-2 mb-3 text-sm uppercase tracking-wider">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                            Required Columns
                        </h4>
                        <ul class="space-y-2">
                            <li class="flex items-center gap-2 text-sm text-amber-800">
                                <span class="w-1.5 h-1.5 bg-amber-400 rounded-full"></span>
                                <strong>Username</strong> (Required, Unique)
                            </li>
                            <li class="flex items-center gap-2 text-sm text-amber-800">
                                <span class="w-1.5 h-1.5 bg-amber-400 rounded-full"></span>
                                <strong>Email</strong> (Required, Valid format)
                            </li>
                            <li class="flex items-center gap-2 text-sm text-amber-800">
                                <span class="w-1.5 h-1.5 bg-amber-400 rounded-full"></span>
                                <strong>Password</strong> (Required for new)
                            </li>
                            <li class="flex items-center gap-2 text-sm text-amber-800">
                                <span class="w-1.5 h-1.5 bg-amber-400 rounded-full"></span>
                                <strong>Role</strong> (Required: admin, vendor)
                            </li>
                        </ul>
                    </div>

                    <div class="p-6 bg-blue-50 rounded-2xl border border-blue-100">
                        <h4 class="font-bold text-blue-900 flex items-center gap-2 mb-3 text-sm uppercase tracking-wider">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.450 1.450c.346.346.528.814.528 1.155 0 .346-.182.814-.528 1.155a1 1 0 001.450 1.450c.715-.715 1.053-1.666 1.053-2.605 0-.94-.338-1.89-1.053-2.605zM9.352 4.997a1 1 0 00-1.18 1.455c.442.442.712 1.079.712 1.638 0 .56-.27 1.196-.712 1.638a1 1 0 001.18 1.455c.954-.954 1.432-2.193 1.432-3.093 0-.9-.478-2.139-1.432-3.093z" clip-rule="evenodd"></path><path d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-1 1H4a1 1 0 01-1-1V3z"></path></svg>
                            Optional Fields
                        </h4>
                        <ul class="space-y-2">
                            <li class="flex items-center gap-2 text-sm text-blue-800">
                                <span class="w-1.5 h-1.5 bg-blue-400 rounded-full"></span>
                                <strong>Phone</strong> (Numeric)
                            </li>
                            <li class="flex items-center gap-2 text-sm text-blue-800">
                                <span class="w-1.5 h-1.5 bg-blue-400 rounded-full"></span>
                                <strong>Vendor ID</strong> (Use ID from reference below)
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Master Data Reference Cards -->
        <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
            <!-- Roles Reference -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <h3 class="font-bold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        System Roles Reference
                    </h3>
                    <span class="px-2 py-1 bg-indigo-100 text-indigo-700 text-[10px] font-bold uppercase rounded">Use for 'Role' Column</span>
                </div>
                <div class="p-0">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase">Role Name</th>
                                <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase">Display Name</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($displayRoles as $role): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-3 text-sm font-mono text-indigo-600"><?php echo htmlspecialchars($role['name']); ?></td>
                                <td class="px-6 py-3 text-sm text-gray-600"><?php echo htmlspecialchars($role['display_name']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Vendors Reference -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <h3 class="font-bold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        Active Vendors (Contractors)
                    </h3>
                    <span class="px-2 py-1 bg-emerald-100 text-emerald-700 text-[10px] font-bold uppercase rounded">Reference for Vendor Mapping</span>
                </div>
                <div class="p-0">
                    <div class="overflow-y-auto max-h-[300px]">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-gray-50 sticky top-0 shadow-sm">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase">Vendor ID</th>
                                    <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase">Vendor Name</th>
                                    <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase">Code</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php foreach ($displayVendors as $v): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-3 text-sm font-bold text-gray-900">ID: <?php echo htmlspecialchars($v['id']); ?></td>
                                    <td class="px-6 py-3 text-sm text-gray-600"><?php echo htmlspecialchars($v['name']); ?></td>
                                    <td class="px-6 py-3 text-sm font-mono text-emerald-600"><?php echo htmlspecialchars($v['vendor_code']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results Section (Dynamic) -->
        <div id="results-section" class="hidden mt-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                    <h3 class="font-bold text-gray-900">Processing Results</h3>
                    <div id="results-summary" class="flex items-center gap-4 text-sm font-medium">
                        <!-- Summary badges will be injected here -->
                    </div>
                </div>
                <div class="overflow-x-auto max-h-[500px]">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-white sticky top-0 border-b shadow-sm z-[5]">
                            <tr>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Row</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Username</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Message</th>
                            </tr>
                        </thead>
                        <tbody id="results-body" class="divide-y divide-gray-100">
                            <!-- Rows will be injected here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const dropZone = document.getElementById('drop-zone');
const fileInput = document.getElementById('user-file');
const progContainer = document.getElementById('progress-container');
const progBar = document.getElementById('progress-bar');
const progStatus = document.getElementById('progress-status');
const progPercent = document.getElementById('progress-percent');
const resultsSection = document.getElementById('results-section');
const resultsBody = document.getElementById('results-body');
const resultsSummary = document.getElementById('results-summary');

fileInput.addEventListener('change', handleUpload);

dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('border-blue-500', 'bg-blue-50');
});

dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('border-blue-500', 'bg-blue-50');
});

dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('border-blue-500', 'bg-blue-50');
    if (e.dataTransfer.files.length) {
        fileInput.files = e.dataTransfer.files;
        handleUpload();
    }
});

async function handleUpload() {
    const file = fileInput.files[0];
    if (!file) return;

    resultsSection.classList.add('hidden');
    progContainer.classList.remove('hidden');
    updateProgress(10, 'Uploading file...');

    const formData = new FormData();
    formData.append('excel_file', file);
    formData.append('ajax', '1');

    try {
        const response = await fetch(window.location.href, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        updateProgress(50, 'Processing data...');
        const data = await response.json();
        
        if (data.success) {
            updateProgress(100, 'Processing complete!');
            showResults(data);
            Swal.fire({
                title: 'Upload Complete',
                text: `${data.created} users created, ${data.updated} users updated.`,
                icon: 'success',
                timer: 3000,
                showConfirmButton: false
            });
        } else {
            Swal.fire('Upload Failed', data.message || 'Unknown error occurred', 'error');
            progContainer.classList.add('hidden');
        }
    } catch (err) {
        console.error(err);
        Swal.fire('Error', 'Technical failure during upload', 'error');
        progContainer.classList.add('hidden');
    } finally {
        fileInput.value = '';
    }
}

function updateProgress(percent, label) {
    progBar.style.width = percent + '%';
    progPercent.textContent = percent + '%';
    progStatus.textContent = label;
}

function showResults(data) {
    resultsSection.classList.remove('hidden');
    progContainer.classList.add('hidden');

    // Update Summary
    resultsSummary.innerHTML = `
        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-lg">Created: ${data.created}</span>
        <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-lg">Updated: ${data.updated}</span>
        <span class="px-3 py-1 bg-red-100 text-red-700 rounded-lg">Failed: ${data.failed}</span>
    `;

    // Populate Table
    resultsBody.innerHTML = (data.rows || []).map(row => `
        <tr class="${row.status === 'failed' ? 'bg-red-50/50' : ''}">
            <td class="px-6 py-4 text-sm text-gray-500">${row.row}</td>
            <td class="px-6 py-4 text-sm font-bold text-gray-900">${row.username || '-'}</td>
            <td class="px-6 py-4">
                <span class="px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider ${getActionClass(row.action)}">
                    ${row.action || '-'}
                </span>
            </td>
            <td class="px-6 py-4">
                <span class="flex items-center gap-1.5 text-sm ${row.status === 'success' ? 'text-green-600' : 'text-red-600'}">
                    <span class="w-1.5 h-1.5 rounded-full ${row.status === 'success' ? 'bg-green-500' : 'bg-red-500'}"></span>
                    ${row.status === 'success' ? 'Success' : 'Failed'}
                </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-600">${row.message}</td>
        </tr>
    `).join('');

    resultsSection.scrollIntoView({ behavior: 'smooth' });
}

function getActionClass(action) {
    switch(action?.toLowerCase()) {
        case 'create': return 'bg-blue-100 text-blue-700';
        case 'update': return 'bg-purple-100 text-purple-700';
        default: return 'bg-gray-100 text-gray-600';
    }
}

function downloadTemplate() {
    Swal.fire({
        title: 'Downloading Template',
        text: 'Generating user import template...',
        didOpen: () => { Swal.showLoading(); }
    });
    window.location.href = '../users/download_template.php';
    setTimeout(() => { Swal.close(); }, 2000);
}
</script>

<?php
/**
 * Backend Processing Functions
 */
function processUserUpload($path, $ext) {
    $results = ['success' => true, 'created' => 0, 'updated' => 0, 'failed' => 0, 'rows' => []];
    
    try {
        $rows = [];
        if ($ext === 'csv') {
            if (($handle = fopen($path, "r")) !== FALSE) {
                $headers = fgetcsv($handle, 1000, ",");
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $rows[] = $data;
                }
                fclose($handle);
            }
        } else {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);
            array_shift($rows); // Remove header
        }

        $userModel = new User();
        $rowNum = 1;

        foreach ($rows as $rowData) {
            $rowNum++;
            // Normalize data mapping
            $raw = array_values((array)$rowData);
            $username = trim($raw[0] ?? '');
            $email = trim($raw[1] ?? '');
            $phone = trim($raw[2] ?? '');
            $password = trim($raw[3] ?? '');
            $role = strtolower(trim($raw[4] ?? ''));
            $vendor_id = trim($raw[5] ?? '');

            if (empty($username)) continue;

            $rowResult = ['row' => $rowNum, 'username' => $username, 'status' => 'success', 'action' => 'create', 'message' => ''];

            try {
                if (empty($role)) {
                    throw new Exception("Role is required.");
                }

                $existing = $userModel->findByUsername($username);
                if ($existing) {
                    $rowResult['action'] = 'update';
                    $data = ['email' => $email, 'phone' => $phone, 'role' => $role, 'status' => 'active'];
                    if (!empty($vendor_id)) $data['vendor_id'] = $vendor_id;
                    if (!empty($password)) $data['password'] = $password;
                    
                    $userModel->update($existing['id'], $data);
                    $results['updated']++;
                    $rowResult['message'] = 'User updated';
                } else {
                    if (empty($email) || empty($password)) {
                        throw new Exception("Email and Password are required for new users.");
                    }
                    $data = [
                        'username' => $username,
                        'email'    => $email,
                        'phone'    => $phone,
                        'password' => $password,
                        'role'     => $role,
                        'status'   => 'active',
                        'vendor_id' => !empty($vendor_id) ? $vendor_id : null
                    ];
                    $userModel->create($data);
                    $results['created']++;
                    $rowResult['message'] = 'User created';
                }
            } catch (Exception $e) {
                $rowResult['status'] = 'failed';
                $rowResult['message'] = $e->getMessage();
                $results['failed']++;
                $results['success'] = false;
            }
            $results['rows'][] = $rowResult;
        }
    } catch (Exception $e) {
        throw new Exception("File processing failed: " . $e->getMessage());
    }
    return $results;
}

$content = ob_get_clean();
require_once __DIR__ . '/../../includes/admin_layout.php';
?>
