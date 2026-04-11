<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/Vendor.php';
require_once __DIR__ . '/../../models/VendorPermission.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$vendorModel = new Vendor();
$permissionModel = new VendorPermission();

// Handle filters
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';

$vendors = $vendorModel->getAllVendors($search, $status ?: 'active');
$allPermissions = $permissionModel->getAllPermissions();

// Get vendor stats with error handling for database schema issues
try {
    $vendorStats = $vendorModel->getVendorStats();
} catch (Exception $e) {
    // If there's a database error (likely missing columns), provide default stats
    $vendorStats = [
        'total_active' => count(array_filter($vendors, function($v) { return $v['status'] === 'active'; })),
        'with_delegations' => 0,
        'with_documents' => 0
    ];
}

$title = 'Vendor Management';
ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Vendor Management</h1>
        <p class="mt-2 text-sm text-gray-700">Manage vendor information, access and permissions</p>
    </div>
    <div class="flex space-x-2">
        <button onclick="openModal('addVendorModal')" class="btn btn-primary">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
            </svg>
            Add Vendor
        </button>
    </div>
</div>

<?php
// Check if enhanced vendor fields are available
$enhancedFieldsAvailable = false;
try {
    // Try to find a vendor with vendor_code to test if enhanced fields exist
    $testVendor = $vendorModel->findByVendorCode('TEST');
    $enhancedFieldsAvailable = true;
} catch (Exception $e) {
    // Enhanced fields not available - this will throw an error if vendor_code column doesn't exist
    $enhancedFieldsAvailable = false;
}

if (!$enhancedFieldsAvailable): ?>
<!-- Database Update Notice -->
<div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-6">
    <div class="flex">
        <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
        </div>
        <div class="ml-3">
            <h3 class="text-sm font-medium text-yellow-800">Database Update Required</h3>
            <div class="mt-2 text-sm text-yellow-700">
                <p>To use the enhanced vendor management features, please update your database schema.</p>
                <div class="mt-3">
                    <p class="font-medium">Run this command in your project directory:</p>
                    <code class="bg-yellow-100 px-2 py-1 rounded text-xs">php database/setup_enhanced_vendors.php</code>
                </div>
                <div class="mt-2">
                    <p class="text-xs">This will add fields for vendor codes, banking info, legal documents, and file uploads.</p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="card">
        <div class="card-body">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <div class="text-2xl font-bold text-gray-900"><?php echo $vendorStats['total_active']; ?></div>
                    <div class="text-sm text-gray-500">Active Vendors</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <div class="text-2xl font-bold text-gray-900"><?php echo $vendorStats['with_delegations']; ?></div>
                    <div class="text-sm text-gray-500">With Active Sites</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <div class="text-2xl font-bold text-gray-900"><?php echo $vendorStats['with_documents']; ?></div>
                    <div class="text-sm text-gray-500">Complete Documentation</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search and Filters -->
<div class="card mb-6">
    <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <input type="text" id="searchInput" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Search vendors..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div>
                <select id="statusFilter" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">All Status</option>
                    <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <div>
                <button onclick="exportVendors()" class="btn btn-secondary w-full">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                    Export
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Vendors List -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Vendors</h3>
        <span class="badge badge-primary"><?php echo count($vendors); ?> vendors</span>
    </div>
    <div class="card-body">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Vendor Details</th>
                        <th>Company Info</th>
                        <th>Contact Information</th>
                        <th>Documentation</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($vendors)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-8 text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <p class="mt-2">No vendors found</p>
                            <button onclick="openModal('addVendorModal')" class="mt-2 btn btn-primary btn-sm">Add First Vendor</button>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($vendors as $vendor): ?>
                        <tr>
                            <td>
                                <div>
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($vendor['name']); ?></div>
                                    <div class="text-sm text-gray-500">Code: <?php echo htmlspecialchars($vendor['vendor_code'] ?? 'N/A'); ?></div>
                                    <?php if ($vendor['contact_person']): ?>
                                        <div class="text-sm text-gray-500">Contact: <?php echo htmlspecialchars($vendor['contact_person']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <?php if ($vendor['company_name']): ?>
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($vendor['company_name']); ?></div>
                                    <?php endif; ?>
                                    <?php if ($vendor['gst_number']): ?>
                                        <div class="text-sm text-gray-500">GST: <?php echo htmlspecialchars($vendor['gst_number']); ?></div>
                                    <?php endif; ?>
                                    <?php if ($vendor['pan_card_number']): ?>
                                        <div class="text-sm text-gray-500">PAN: <?php echo htmlspecialchars($vendor['pan_card_number']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <?php if ($vendor['email']): ?>
                                        <div class="text-sm text-gray-900"><?php echo htmlspecialchars($vendor['email']); ?></div>
                                    <?php endif; ?>
                                    <?php if ($vendor['phone']): ?>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($vendor['phone']); ?></div>
                                    <?php endif; ?>
                                    <?php if ($vendor['address']): ?>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars(substr($vendor['address'], 0, 50)) . (strlen($vendor['address']) > 50 ? '...' : ''); ?></div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="flex flex-col space-y-1">
                                    <?php if ($vendor['experience_letter_path']): ?>
                                        <span class="badge badge-success text-xs">Experience Letter</span>
                                    <?php endif; ?>
                                    <?php if ($vendor['photograph_path']): ?>
                                        <span class="badge badge-success text-xs">Photograph</span>
                                    <?php endif; ?>
                                    <?php if ($vendor['pvc_status'] === 'Yes'): ?>
                                        <span class="badge badge-info text-xs">PVC Verified</span>
                                    <?php endif; ?>
                                    <?php if (!$vendor['experience_letter_path'] && !$vendor['photograph_path']): ?>
                                        <span class="text-xs text-gray-400">No documents</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge <?php echo $vendor['status'] === 'active' ? 'badge-success' : 'badge-secondary'; ?>">
                                    <?php echo ucfirst($vendor['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="flex items-center space-x-2">
                                    <button onclick="viewVendor(<?php echo $vendor['id']; ?>)" class="btn btn-sm btn-secondary" title="View Details">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                    <button onclick="editVendor(<?php echo $vendor['id']; ?>)" class="btn btn-sm btn-primary" title="Edit Vendor">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                                        </svg>
                                    </button>
                                    <button onclick="managePermissions(<?php echo $vendor['id']; ?>)" class="btn btn-sm btn-info" title="Manage Permissions">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                    <button onclick="deleteVendor(<?php echo $vendor['id']; ?>)" class="btn btn-sm btn-danger" title="Delete Vendor">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"></path>
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
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
</div>

<!-- Add/Edit Vendor Modal -->
<div id="addVendorModal" class="modal">
    <div class="modal-content max-w-6xl">
        <div class="modal-header">
            <h3 class="modal-title" id="vendorModalTitle">Add New Vendor</h3>
            <button type="button" class="modal-close" onclick="closeModal('addVendorModal')">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <form id="vendorForm" action="process-vendor.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" id="vendor_id" name="vendor_id">
            <input type="hidden" name="action" id="form_action" value="create">
            
            <div class="modal-body">
                <!-- Basic Information -->
                <div class="mb-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="form-group">
                            <label for="vendor_code" class="form-label">Vendor Code</label>
                            <input type="text" id="vendor_code" name="vendor_code" class="form-input" placeholder="Auto-generated if empty">
                        </div>
                        <div class="form-group">
                            <label for="mobility_id" class="form-label">Mobility ID</label>
                            <input type="text" id="mobility_id" name="mobility_id" class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="mobility_password" class="form-label">Mobility Password</label>
                            <input type="password" id="mobility_password" name="mobility_password" class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="vendorName" class="form-label">Name *</label>
                            <input type="text" id="vendorName" name="vendorName" class="form-input" required>
                        </div>
                    </div>
                </div>

                <!-- Company Information -->
                <div class="mb-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Company Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="form-group">
                            <label for="company_name" class="form-label">Company Name</label>
                            <input type="text" id="company_name" name="company_name" class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" id="address" name="address" class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="email" class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="contact_number" class="form-label">Contact Number</label>
                            <input type="tel" id="contact_number" name="contact_number" class="form-input">
                        </div>
                    </div>
                </div>

                <!-- Banking Information -->
                <div class="mb-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Banking Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="form-group">
                            <label for="bank_name" class="form-label">Bank Name</label>
                            <input type="text" id="bank_name" name="bank_name" class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="account_number" class="form-label">Account Number</label>
                            <input type="text" id="account_number" name="account_number" class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="ifsc_code" class="form-label">IFSC Code</label>
                            <input type="text" id="ifsc_code" name="ifsc_code" class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="gst_number" class="form-label">GST Number</label>
                            <input type="text" id="gst_number" name="gst_number" class="form-input">
                        </div>
                    </div>
                </div>

                <!-- Legal Documentation -->
                <div class="mb-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Legal Documentation</h4>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="form-group">
                            <label for="pan_card_number" class="form-label">PAN Card Number</label>
                            <input type="text" id="pan_card_number" name="pan_card_number" class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="aadhaar_number" class="form-label">Aadhaar Card Number</label>
                            <input type="text" id="aadhaar_number" name="aadhaar_number" class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="msme_number" class="form-label">MSME Number</label>
                            <input type="text" id="msme_number" name="msme_number" class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="esic_number" class="form-label">ESIC Number</label>
                            <input type="text" id="esic_number" name="esic_number" class="form-input">
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="mb-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Additional Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="form-group">
                            <label for="pf_number" class="form-label">PF Number</label>
                            <input type="text" id="pf_number" name="pf_number" class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="pvc_status" class="form-label">PVC Status</label>
                            <select id="pvc_status" name="pvc_status" class="form-select">
                                <option value="">Select</option>
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="experience_letter" class="form-label">Experience Letter</label>
                            <input type="file" id="experience_letter" name="experience_letter" class="form-input" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            <div class="text-xs text-gray-500 mt-1">PDF, DOC, DOCX, JPG, PNG (Max 5MB)</div>
                        </div>
                        <div class="form-group">
                            <label for="photograph" class="form-label">Photograph</label>
                            <input type="file" id="photograph" name="photograph" class="form-input" accept=".jpg,.jpeg,.png">
                            <div class="text-xs text-gray-500 mt-1">JPG, PNG (Max 2MB)</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" onclick="closeModal('addVendorModal')" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary" id="submitVendorBtn">Add Vendor</button>
            </div>
        </form>
    </div>
</div>

<!-- View Vendor Modal -->
<div id="viewVendorModal" class="modal">
    <div class="modal-content max-w-4xl">
        <div class="modal-header">
            <h3 class="modal-title">Vendor Details</h3>
            <button type="button" class="modal-close" onclick="closeModal('viewVendorModal')">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <div id="vendorDetailsContent">
                <!-- Vendor details will be loaded here -->
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" onclick="closeModal('viewVendorModal')" class="btn btn-secondary">Close</button>
            <button type="button" onclick="editVendorFromView()" class="btn btn-primary" id="editFromViewBtn">Edit Vendor</button>
        </div>
    </div>
</div>

<!-- Permissions Modal -->
<div id="permissionsModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Manage Vendor Permissions</h3>
            <button type="button" class="modal-close" onclick="closeModal('permissionsModal')">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <form id="permissionsForm" action="update-permissions.php" method="POST">
            <input type="hidden" id="vendor_id" name="vendor_id">
            <div class="modal-body">
                <div id="vendor-info" class="mb-4 p-3 bg-gray-50 rounded"></div>
                
                <div class="space-y-4">
                    <h4 class="font-medium text-gray-900">Portal Access Permissions</h4>
                    <?php foreach ($allPermissions as $key => $label): ?>
                    <div class="flex items-center justify-between p-3 border rounded">
                        <div>
                            <label class="font-medium text-gray-900"><?php echo $label; ?></label>
                            <p class="text-sm text-gray-500"><?php echo getPermissionDescription($key); ?></p>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="perm_<?php echo $key; ?>" name="permissions[<?php echo $key; ?>]" value="1" class="mr-2">
                            <label for="perm_<?php echo $key; ?>" class="text-sm text-gray-700">Enabled</label>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('permissionsModal')" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Permissions</button>
            </div>
        </form>
    </div>
</div>

<script>
// Search functionality
document.getElementById('searchInput').addEventListener('keyup', debounce(function() {
    applyFilters();
}, 500));

document.getElementById('statusFilter').addEventListener('change', applyFilters);

function applyFilters() {
    const searchTerm = document.getElementById('searchInput').value;
    const status = document.getElementById('statusFilter').value;
    
    const url = new URL(window.location);
    
    if (searchTerm) url.searchParams.set('search', searchTerm);
    else url.searchParams.delete('search');
    
    if (status) url.searchParams.set('status', status);
    else url.searchParams.delete('status');
    
    window.location.href = url.toString();
}

// Vendor CRUD operations
function addVendor() {
    document.getElementById('vendorModalTitle').textContent = 'Add New Vendor';
    document.getElementById('form_action').value = 'create';
    document.getElementById('submitVendorBtn').textContent = 'Add Vendor';
    document.getElementById('vendorForm').reset();
    document.getElementById('vendor_id').value = '';
    openModal('addVendorModal');
}

function editVendor(vendorId) {
    fetch(`get-vendor.php?id=${vendorId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const vendor = data.vendor;
                
                // Populate form fields
                document.getElementById('vendor_id').value = vendor.id;
                document.getElementById('vendor_code').value = vendor.vendor_code || '';
                document.getElementById('mobility_id').value = vendor.mobility_id || '';
                document.getElementById('vendorName').value = vendor.name || '';
                document.getElementById('company_name').value = vendor.company_name || '';
                document.getElementById('address').value = vendor.address || '';
                document.getElementById('email').value = vendor.email || '';
                document.getElementById('contact_number').value = vendor.phone || '';
                document.getElementById('bank_name').value = vendor.bank_name || '';
                document.getElementById('account_number').value = vendor.account_number || '';
                document.getElementById('ifsc_code').value = vendor.ifsc_code || '';
                document.getElementById('gst_number').value = vendor.gst_number || '';
                document.getElementById('pan_card_number').value = vendor.pan_card_number || '';
                document.getElementById('aadhaar_number').value = vendor.aadhaar_number || '';
                document.getElementById('msme_number').value = vendor.msme_number || '';
                document.getElementById('esic_number').value = vendor.esic_number || '';
                document.getElementById('pf_number').value = vendor.pf_number || '';
                document.getElementById('pvc_status').value = vendor.pvc_status || '';
                
                // Update modal
                document.getElementById('vendorModalTitle').textContent = 'Edit Vendor';
                document.getElementById('form_action').value = 'update';
                document.getElementById('submitVendorBtn').textContent = 'Update Vendor';
                
                openModal('addVendorModal');
            } else {
                showAlert(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Failed to load vendor details', 'error');
        });
}

function viewVendor(vendorId) {
    fetch(`get-vendor.php?id=${vendorId}&detailed=true`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const vendor = data.vendor;
                
                console.log(vendor);
                document.getElementById('vendorDetailsContent').innerHTML = `
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h4>
                            <dl class="space-y-2">
                                <div><dt class="text-sm font-medium text-gray-500">Vendor Code:</dt><dd class="text-sm text-gray-900">${vendor.vendor_code || 'N/A'}</dd></div>
                                <div><dt class="text-sm font-medium text-gray-500">Name:</dt><dd class="text-sm text-gray-900">${vendor.name || 'N/A'}</dd></div>
                                <div><dt class="text-sm font-medium text-gray-500">Company:</dt><dd class="text-sm text-gray-900">${vendor.company_name || 'N/A'}</dd></div>
                                <div><dt class="text-sm font-medium text-gray-500">Email:</dt><dd class="text-sm text-gray-900">${vendor.email || 'N/A'}</dd></div>
                                <div><dt class="text-sm font-medium text-gray-500">Phone:</dt><dd class="text-sm text-gray-900">${vendor.phone || 'N/A'}</dd></div>
                                <div><dt class="text-sm font-medium text-gray-500">Address:</dt><dd class="text-sm text-gray-900">${vendor.address || 'N/A'}</dd></div>
                            </dl>
                        </div>
                        <div>
                            <h4 class="text-lg font-medium text-gray-900 mb-4">Documentation</h4>
                            <dl class="space-y-2">
                                <div><dt class="text-sm font-medium text-gray-500">GST Number:</dt><dd class="text-sm text-gray-900">${vendor.gst_number || 'N/A'}</dd></div>
                                <div><dt class="text-sm font-medium text-gray-500">PAN Card:</dt><dd class="text-sm text-gray-900">${vendor.pan_card_number || 'N/A'}</dd></div>
                                <div><dt class="text-sm font-medium text-gray-500">Aadhaar:</dt><dd class="text-sm text-gray-900">${vendor.aadhaar_number || 'N/A'}</dd></div>
                                <div><dt class="text-sm font-medium text-gray-500">MSME:</dt><dd class="text-sm text-gray-900">${vendor.msme_number || 'N/A'}</dd></div>
                                <div><dt class="text-sm font-medium text-gray-500">PVC Status:</dt><dd class="text-sm text-gray-900">${vendor.pvc_status || 'N/A'}</dd></div>
                            </dl>
                        </div>
                    </div>
                    ${vendor.experience_letter_path || vendor.photograph_path ? `
                    <div class="mt-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Documents</h4>
                        <div class="flex space-x-4">
                            ${vendor.experience_letter_path ? `<a href="../../${vendor.experience_letter_path}" target="_blank" class="btn btn-sm btn-secondary">View Experience Letter</a>` : ''}
                            ${vendor.photograph_path ? `<a href="../../${vendor.photograph_path}" target="_blank" class="btn btn-sm btn-secondary">View Photograph</a>` : ''}
                        </div>
                    </div>
                    ` : ''}
                `;
                
                document.getElementById('editFromViewBtn').onclick = () => {
                    closeModal('viewVendorModal');
                    editVendor(vendorId);
                };
                
                openModal('viewVendorModal');
            } else {
                showAlert(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Failed to load vendor details', 'error');
        });
}

function deleteVendor(vendorId) {
    if (confirm('Are you sure you want to delete this vendor? This action cannot be undone.')) {
        fetch('process-vendor.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=delete&vendor_id=${vendorId}`
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
            showAlert('An error occurred while deleting the vendor.', 'error');
        });
    }
}

function exportVendors() {
    const params = new URLSearchParams(window.location.search);
    window.open(`export-vendors.php?${params.toString()}`, '_blank');
}

// Form submission
document.getElementById('vendorForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = document.getElementById('submitVendorBtn');
    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Processing...';
    
    fetch('process-vendor.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            closeModal('addVendorModal');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while processing the vendor.', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
});

function managePermissions(vendorId) {
    // Fetch vendor details and current permissions
    fetch(`get-vendor-permissions.php?vendor_id=${vendorId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Populate modal
                document.getElementById('vendor_id').value = vendorId;
                document.getElementById('vendor-info').innerHTML = `
                    <h5 class="font-medium">${data.vendor.name}</h5>
                    <p class="text-sm text-gray-600">${data.vendor.email || 'No email'}</p>
                `;
                
                // Set permission checkboxes
                Object.keys(data.permissions).forEach(key => {
                    const checkbox = document.getElementById(`perm_${key}`);
                    if (checkbox) {
                        checkbox.checked = data.permissions[key];
                    }
                });
                
                openModal('permissionsModal');
            } else {
                showAlert(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Failed to load vendor permissions', 'error');
        });
}

function viewVendorStats(vendorId) {
    window.location.href = `vendor-stats.php?id=${vendorId}`;
}

// Form submission
document.getElementById('permissionsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Updating...';
    
    fetch('update-permissions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            closeModal('permissionsModal');
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
        submitBtn.textContent = originalText;
    });
});

// Utility function for debouncing
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
</script>

<?php
function getPermissionDescription($key) {
    $descriptions = [
        'view_sites' => 'Allow vendor to view and manage their assigned sites',
        'update_progress' => 'Allow vendor to update installation progress',
        'view_masters' => 'Allow vendor to view master data (customers, banks, etc.)',
        'view_reports' => 'Allow vendor to view reports and analytics',
        'view_inventory' => 'Allow vendor to view inventory information',
        'view_material_requests' => 'Allow vendor to view material requests'
    ];
    
    return $descriptions[$key] ?? 'Permission description not available';
}

$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>