<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/Site.php';
require_once __DIR__ . '/../../models/Vendor.php';
require_once __DIR__ . '/../../models/SiteDelegation.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$siteId = $_GET['id'] ?? null;
if (!$siteId) {
    header('Location: index.php');
    exit;
}

$siteModel = new Site();
$vendorModel = new Vendor();
$delegationModel = new SiteDelegation();

$site = $siteModel->findWithRelations($siteId);
if (!$site) {
    header('Location: index.php');
    exit;
}

// Check if site is already delegated
$activeDelegation = $delegationModel->getActiveDelegation($siteId);
$vendors = $vendorModel->getActiveVendors();
$delegationHistory = $delegationModel->getDelegationHistory($siteId);

// Get layout files if delegation exists
$layoutFiles = [];
if ($activeDelegation) {
    require_once __DIR__ . '/../../models/DelegationLayout.php';
    $layoutModel = new DelegationLayout();
    $layoutFiles = $layoutModel->getLayoutsByDelegation($activeDelegation['id']);
}

$title = 'Delegate Site - ' . $site['site_id'];
ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <!-- <h1 class="text-2xl font-semibold text-gray-900">Delegate Site</h1> -->
        <p class="mt-2 text-sm text-gray-700">Assign site to vendor for installation</p>
    </div>
    <div>
        <a href="index.php" class="btn btn-secondary">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
            </svg>
            Back to Sites
        </a>
    </div>
</div>

<!-- Site Information Card -->
<div class="card mb-6">
    <div class="card-body">
        <h3 class="card-title">Site Information</h3>
    </div>
    <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Site ID</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo htmlspecialchars($site['site_id']); ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Store ID</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo htmlspecialchars($site['store_id'] ?: 'N/A'); ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo htmlspecialchars($site['location']); ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo htmlspecialchars($site['city_name'] ?: 'N/A'); ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo htmlspecialchars($site['state_name'] ?: 'N/A'); ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo htmlspecialchars($site['customer_name'] ?: 'N/A'); ?></p>
            </div>
        </div>
    </div>
</div>

<?php if ($activeDelegation): ?>
<!-- Current Delegation Card -->
<div class="card mb-6">
    <div class="card-body">
        <h3 class="card-title">Current Delegation</h3>
        <span class="badge badge-success">Active</span>
    </div>
    <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Delegated To</label>
                <div class="text-sm text-gray-900 bg-gray-50 p-2 rounded">
                    <div class="font-medium"><?php echo htmlspecialchars($activeDelegation['vendor_company_name'] ?: $activeDelegation['vendor_contact_name']); ?></div>
                    <?php if ($activeDelegation['vendor_company_name'] && $activeDelegation['vendor_contact_name']): ?>
                    <div class="text-xs text-gray-600 mt-1">Contact: <?php echo htmlspecialchars($activeDelegation['vendor_contact_name']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Delegated By</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo htmlspecialchars($activeDelegation['delegated_by_name']); ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Delegation Date</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo date('M d, Y H:i', strtotime($activeDelegation['delegation_date'])); ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <span class="badge badge-success"><?php echo ucfirst($activeDelegation['status']); ?></span>
            </div>
            <?php if ($activeDelegation['notes']): ?>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo htmlspecialchars($activeDelegation['notes']); ?></p>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($layoutFiles)): ?>
        <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700 mb-3">Uploaded Layout Files</label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($layoutFiles as $layout): ?>
                <div class="border rounded-lg p-4 bg-white hover:shadow-md transition-shadow">
                    <div class="flex items-start space-x-3">
                        <?php 
                        $isImage = in_array(strtolower($layout['file_type']), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                        if ($isImage): 
                        ?>
                            <img src="<?php echo htmlspecialchars($layout['file_path']); ?>" 
                                 alt="Layout" 
                                 class="h-20 w-20 object-cover rounded cursor-pointer"
                                 onclick="window.open('<?php echo htmlspecialchars($layout['file_path']); ?>', '_blank')">
                        <?php else: ?>
                            <div class="h-20 w-20 flex items-center justify-center bg-gray-100 rounded">
                                <?php if ($layout['file_type'] === 'pdf'): ?>
                                    <svg class="w-10 h-10 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M4 18h12V6h-4V2H4v16zm-2 1V0h12l4 4v16H2v-1z"></path>
                                    </svg>
                                <?php elseif (in_array($layout['file_type'], ['xls', 'xlsx'])): ?>
                                    <svg class="w-10 h-10 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M4 2h12l4 4v12H4V2zm1 1v14h10V7h-4V3H5z"></path>
                                    </svg>
                                <?php elseif (in_array($layout['file_type'], ['doc', 'docx'])): ?>
                                    <svg class="w-10 h-10 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M4 2h12l4 4v12H4V2zm1 1v14h10V7h-4V3H5z"></path>
                                    </svg>
                                <?php else: ?>
                                    <svg class="w-10 h-10 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M4 2h12l4 4v12H4V2zm1 1v14h10V7h-4V3H5z"></path>
                                    </svg>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                <?php echo htmlspecialchars($layout['original_filename']); ?>
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                <?php 
                                $size = $layout['file_size'];
                                if ($size < 1024) {
                                    echo $size . ' B';
                                } elseif ($size < 1048576) {
                                    echo round($size / 1024, 2) . ' KB';
                                } else {
                                    echo round($size / 1048576, 2) . ' MB';
                                }
                                ?>
                            </p>
                            <p class="text-xs text-gray-500">
                                <?php echo date('M d, Y H:i', strtotime($layout['uploaded_at'])); ?>
                            </p>
                            <?php if ($layout['remarks']): ?>
                            <p class="text-xs text-gray-600 mt-2 italic">
                                "<?php echo htmlspecialchars($layout['remarks']); ?>"
                            </p>
                            <?php endif; ?>
                            <a href="<?php echo htmlspecialchars($layout['file_path']); ?>" 
                               download 
                               class="inline-flex items-center text-xs text-blue-600 hover:text-blue-800 mt-2">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                                Download
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="mt-4 flex space-x-2" >
            <button style="display:none;" onclick="completeDelegation(<?php echo $activeDelegation['id']; ?>)" class="btn btn-success">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
                Mark as Completed
            </button>
            <button onclick="cancelDelegation(<?php echo $activeDelegation['id']; ?>)" class="btn btn-danger">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
                Cancel Delegation
            </button>
        </div>
    </div>
</div>
<?php else: ?>
<!-- Delegation Form -->
<div class="card mb-6">
    <div class="card-body">
        <h3 class="card-title">Delegate Site to Vendor</h3>
    </div>
    <div class="card-body">
        <form id="delegationForm" action="process_delegation.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="site_id" value="<?php echo $siteId; ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="vendor_id" class="form-label">Select Vendor *</label>
                    <select id="vendor_id" name="vendor_id" class="form-select" required>
                        <option value="">Choose a vendor...</option>
                        <?php foreach ($vendors as $vendor): ?>
                            <option value="<?php echo $vendor['id']; ?>">
                                <?php echo htmlspecialchars($vendor['company_name'] ?: $vendor['name']); ?>
                                <?php if ($vendor['contact_person']): ?>
                                    - <?php echo htmlspecialchars($vendor['contact_person']); ?>
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label">Delegation Date</label>
                    <input type="text" class="form-input" value="<?php echo date('M d, Y H:i'); ?>" readonly>
                </div>
                <div class="md:col-span-2">
                    <label for="layout_files" class="form-label">Layout Upload (Optional) - Multiple Files Allowed</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 hover:border-gray-400 transition-colors">
                        <input type="file" 
                               id="layout_files" 
                               name="layout_files[]" 
                               class="hidden" 
                               accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.xls,.xlsx,.doc,.docx"
                               multiple>
                        <div id="file-upload-area" class="text-center cursor-pointer">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <p class="mt-2 text-sm text-gray-600">
                                Click to upload or drag and drop multiple files
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                Images, PDF, Excel, Word (Max 10MB each)
                            </p>
                        </div>
                        <div id="files-preview" class="mt-4 space-y-2"></div>
                    </div>
                </div>
                <div class="md:col-span-2" id="remarks-section" style="display:none;">
                    <label for="layout_remarks" class="form-label">Layout Remarks (Optional)</label>
                    <textarea id="layout_remarks" 
                              name="layout_remarks" 
                              class="form-textarea" 
                              rows="2" 
                              maxlength="500"
                              placeholder="Add any notes about the uploaded layout files..."></textarea>
                    <p class="text-xs text-gray-500 mt-1">
                        <span id="remarks-count">0</span>/500 characters
                    </p>
                </div>
                <div class="md:col-span-2">
                    <label for="notes" class="form-label">Notes (Optional)</label>
                    <textarea id="notes" name="notes" class="form-textarea" rows="3" placeholder="Add any special instructions or notes for the vendor..."></textarea>
                </div>
            </div>
            <div class="mt-6">
                <button type="submit" class="btn btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"></path>
                    </svg>
                    Delegate Site
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Delegation History -->
<?php if (!empty($delegationHistory)): ?>
<div class="card">
    <div class="card-body">
        <h3 class="card-title">Delegation History</h3>
    </div>
    <div class="card-body">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Vendor</th>
                        <th>Delegated By</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($delegationHistory as $delegation): ?>
                    <tr>
                        <td>
                            <div class="font-medium"><?php echo htmlspecialchars($delegation['vendor_company_name'] ?: $delegation['vendor_contact_name']); ?></div>
                            <?php if ($delegation['vendor_company_name'] && $delegation['vendor_contact_name']): ?>
                            <div class="text-xs text-gray-500 mt-1">Contact: <?php echo htmlspecialchars($delegation['vendor_contact_name']); ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($delegation['delegated_by_name']); ?></td>
                        <td><?php echo date('M d, Y H:i', strtotime($delegation['delegation_date'])); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $delegation['status'] === 'active' ? 'success' : ($delegation['status'] === 'completed' ? 'info' : 'secondary'); ?>">
                                <?php echo ucfirst($delegation['status']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($delegation['notes'] ?: 'No notes'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// File upload handling for multiple files
const fileInput = document.getElementById('layout_files');
const fileUploadArea = document.getElementById('file-upload-area');
const filesPreview = document.getElementById('files-preview');
const remarksSection = document.getElementById('remarks-section');
const remarksTextarea = document.getElementById('layout_remarks');
const remarksCount = document.getElementById('remarks-count');

let selectedFiles = [];

// Allowed file types
const allowedTypes = {
    'image/jpeg': 'jpg', 'image/jpg': 'jpg', 'image/png': 'png',
    'image/gif': 'gif', 'image/webp': 'webp',
    'application/pdf': 'pdf',
    'application/vnd.ms-excel': 'xls',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': 'xlsx',
    'application/msword': 'doc',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document': 'docx'
};

const maxFileSize = 10 * 1024 * 1024; // 10MB

// Click to upload
fileUploadArea?.addEventListener('click', () => {
    fileInput?.click();
});

// File selection
fileInput?.addEventListener('change', function(e) {
    const files = Array.from(e.target.files);
    if (files.length > 0) {
        handleFilesSelect(files);
    }
});

// Drag and drop
fileUploadArea?.addEventListener('dragover', (e) => {
    e.preventDefault();
    e.stopPropagation();
    fileUploadArea.classList.add('border-blue-400', 'bg-blue-50');
});

fileUploadArea?.addEventListener('dragleave', (e) => {
    e.preventDefault();
    e.stopPropagation();
    fileUploadArea.classList.remove('border-blue-400', 'bg-blue-50');
});

fileUploadArea?.addEventListener('drop', (e) => {
    e.preventDefault();
    e.stopPropagation();
    fileUploadArea.classList.remove('border-blue-400', 'bg-blue-50');
    
    const files = Array.from(e.dataTransfer.files);
    if (files.length > 0) {
        const dataTransfer = new DataTransfer();
        files.forEach(file => dataTransfer.items.add(file));
        fileInput.files = dataTransfer.files;
        handleFilesSelect(files);
    }
});

// Handle multiple files selection
function handleFilesSelect(files) {
    let validFiles = [];
    let hasErrors = false;
    
    files.forEach(file => {
        if (validateFile(file)) {
            validFiles.push(file);
        } else {
            hasErrors = true;
        }
    });
    
    if (validFiles.length > 0) {
        selectedFiles = validFiles;
        displayFilesPreview(validFiles);
        remarksSection.style.display = 'block';
    }
    
    if (hasErrors && validFiles.length === 0) {
        fileInput.value = '';
    }
}

// Validate file
function validateFile(file) {
    if (file.size > maxFileSize) {
        showAlert(`File "${file.name}" exceeds 10MB limit`, 'error');
        return false;
    }
    
    if (file.size === 0) {
        showAlert(`File "${file.name}" is empty`, 'error');
        return false;
    }
    
    if (!allowedTypes[file.type]) {
        showAlert(`File "${file.name}" type not supported`, 'error');
        return false;
    }
    
    return true;
}

// Display multiple files preview
function displayFilesPreview(files) {
    filesPreview.innerHTML = '';
    
    files.forEach((file, index) => {
        const isImage = file.type.startsWith('image/');
        const fileSize = formatFileSize(file.size);
        
        const fileCard = document.createElement('div');
        fileCard.className = 'flex items-center justify-between bg-gray-50 p-3 rounded-lg';
        fileCard.id = `file-card-${index}`;
        
        if (isImage) {
            const reader = new FileReader();
            reader.onload = function(e) {
                fileCard.innerHTML = `
                    <div class="flex items-center space-x-3">
                        <img src="${e.target.result}" alt="Preview" class="h-16 w-16 object-cover rounded">
                        <div>
                            <p class="text-sm font-medium text-gray-900">${file.name}</p>
                            <p class="text-xs text-gray-500">${fileSize}</p>
                        </div>
                    </div>
                    <button type="button" onclick="removeFileByIndex(${index})" class="text-red-600 hover:text-red-800">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                `;
            };
            reader.readAsDataURL(file);
        } else {
            const icon = getFileIcon(file.type);
            fileCard.innerHTML = `
                <div class="flex items-center space-x-3">
                    <div class="h-16 w-16 flex items-center justify-center bg-gray-200 rounded">
                        ${icon}
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">${file.name}</p>
                        <p class="text-xs text-gray-500">${fileSize}</p>
                    </div>
                </div>
                <button type="button" onclick="removeFileByIndex(${index})" class="text-red-600 hover:text-red-800">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            `;
        }
        
        filesPreview.appendChild(fileCard);
    });
}

// Remove file by index
function removeFileByIndex(index) {
    selectedFiles.splice(index, 1);
    
    const dataTransfer = new DataTransfer();
    selectedFiles.forEach(file => dataTransfer.items.add(file));
    fileInput.files = dataTransfer.files;
    
    if (selectedFiles.length === 0) {
        filesPreview.innerHTML = '';
        remarksSection.style.display = 'none';
        remarksTextarea.value = '';
        remarksCount.textContent = '0';
    } else {
        displayFilesPreview(selectedFiles);
    }
}

// Format file size
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

// Get file icon
function getFileIcon(fileType) {
    if (fileType === 'application/pdf') {
        return '<svg class="w-8 h-8 text-red-600" fill="currentColor" viewBox="0 0 20 20"><path d="M4 18h12V6h-4V2H4v16zm-2 1V0h12l4 4v16H2v-1z"></path></svg>';
    } else if (fileType.includes('excel') || fileType.includes('spreadsheet')) {
        return '<svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path d="M4 2h12l4 4v12H4V2zm1 1v14h10V7h-4V3H5z"></path></svg>';
    } else if (fileType.includes('word') || fileType.includes('document')) {
        return '<svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path d="M4 2h12l4 4v12H4V2zm1 1v14h10V7h-4V3H5z"></path></svg>';
    }
    return '<svg class="w-8 h-8 text-gray-600" fill="currentColor" viewBox="0 0 20 20"><path d="M4 2h12l4 4v12H4V2zm1 1v14h10V7h-4V3H5z"></path></svg>';
}

// Remarks character counter
remarksTextarea?.addEventListener('input', function() {
    remarksCount.textContent = this.value.length;
});

// Form submission
document.getElementById('delegationForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner mr-2"></span>Processing...';
    
    fetch('process_delegation.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred. Please try again.', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

// Complete delegation
function completeDelegation(delegationId) {
    if (confirm('Are you sure you want to mark this delegation as completed?')) {
        fetch('process_delegation.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=complete&delegation_id=${delegationId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred. Please try again.', 'error');
        });
    }
}

// Cancel delegation
function cancelDelegation(delegationId) {
    if (confirm('Are you sure you want to cancel this delegation?')) {
        fetch('process_delegation.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=cancel&delegation_id=${delegationId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred. Please try again.', 'error');
        });
    }
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>
