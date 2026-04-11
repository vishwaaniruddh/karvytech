<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/Vendor.php';

// Require vendor authentication
Auth::requireVendor();

$vendorId = Auth::getVendorId();
$vendorModel = new Vendor();

// Get vendor info
$vendor = $vendorModel->find($vendorId);
if (!$vendor) {
    Auth::logout();
}

// Define the badge class based on status
$statusBadgeClass = $vendor['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';

$title = 'Vendor Profile - ' . $vendor['name'];
ob_start();
?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 leading-tight">Vendor Profile</h1>
            <p class="mt-1 text-sm text-gray-500">View and manage your essential vendor information.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="index.php" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                </svg>
                Back to Dashboard
            </a>
        </div>
    </div>
    

    <div class="bg-white shadow-lg overflow-hidden rounded-lg">
        <div class="px-6 py-5 sm:px-6 flex justify-between items-center border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Vendor Details </span></h2>
            <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium <?php echo $statusBadgeClass; ?>">
                Status: <?php echo ucfirst($vendor['status']); ?>
            </span>
        </div>
        <div class="px-6 py-5">
            <dl class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-2">
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Company Name</dt>
                    <dd class="mt-1 text-base text-gray-900"><?php echo htmlspecialchars($vendor['name']); ?> - <span><?php echo $vendor['id']; ?></dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Contact Person</dt>
                    <dd class="mt-1 text-base text-gray-900"><?php echo htmlspecialchars($vendor['contact_person'] ?: 'N/A'); ?></dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Email Address</dt>
                    <dd class="mt-1 text-base text-gray-900"><?php echo htmlspecialchars($vendor['email'] ?: 'N/A'); ?></dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Phone Number</dt>
                    <dd class="mt-1 text-base text-gray-900"><?php echo htmlspecialchars($vendor['phone'] ?: 'N/A'); ?></dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Business Address</dt>
                    <dd class="mt-1 text-base text-gray-900"><?php echo nl2br(htmlspecialchars($vendor['address'] ?: 'N/A')); ?></dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Registered Since</dt>
                    <dd class="mt-1 text-base text-gray-900"><?php echo date('M d, Y', strtotime($vendor['created_at'])); ?></dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                    <dd class="mt-1 text-base text-gray-900"><?php echo date('M d, Y \a\t H:i', strtotime($vendor['updated_at'])); ?></dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="bg-yellow-50 shadow-lg border border-yellow-200 overflow-hidden rounded-lg p-6">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.394 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div class="ml-4 w-full">
                <h3 class="text-lg font-semibold text-yellow-800">Login Information</h3>
                <div class="mt-2 text-sm text-yellow-700 space-y-1">
                    <p>
                        <strong class="font-medium">Username:</strong> 
                        <span class="font-mono bg-yellow-100 px-2 py-0.5 rounded text-yellow-900"><?php echo htmlspecialchars(Auth::getCurrentUser()['username']); ?></span>
                    </p>
                    <p>
                        <strong class="font-medium">Default Password:</strong> 
                        <span class="font-mono bg-yellow-100 px-2 py-0.5 rounded text-yellow-900">password</span>
                    </p>
                    <p class="pt-2 text-xs text-yellow-600 border-t border-yellow-200 mt-2">
                        Action Required: Please contact the administrator immediately to securely change your password and update your user-specific profile details.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../includes/vendor_layout.php';
?>