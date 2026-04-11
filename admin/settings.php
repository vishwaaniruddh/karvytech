<?php
require_once __DIR__ . '/../config/auth.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);
$currentUser = Auth::getCurrentUser();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$title = 'Settings';
ob_start();

?>

<!-- Critical CSS Reset for this page -->
<style>
    /* Remove any potential decorative elements */
    body::before,
    body::after,
    .main-content::before,
    .main-content::after,
    main::before,
    main::after {
        display: none !important;
    }
    
    /* Hide login page elements */
    .floating-particles,
    .particle,
    .login-container,
    .login-card {
        display: none !important;
    }
    
    /* Ensure proper stacking */
    .max-w-4xl {
        position: relative;
        z-index: 10;
        background: #f9fafb;
    }
</style>

<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900 mb-2">Settings</h1>
        <p class="text-sm text-gray-600">Manage your application settings and preferences</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Settings Menu -->
        <div class="lg:col-span-1">
            <div class="card">
                <div class="card-body">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Settings Menu</h3>
                    <nav class="space-y-2">
                        <a href="profile.php" class="flex items-center px-3 py-2 text-sm text-gray-700 rounded-md hover:bg-gray-100">
                            <svg class="w-4 h-4 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                            </svg>
                            Profile Settings
                        </a>
                        <a href="#" class="flex items-center px-3 py-2 text-sm text-blue-600 bg-blue-50 rounded-md">
                            <svg class="w-4 h-4 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"></path>
                            </svg>
                            General Settings
                        </a>
                        <a href="users/menu-permissions.php?user_id=<?php echo $currentUser['id']; ?>" class="flex items-center px-3 py-2 text-sm text-gray-700 rounded-md hover:bg-gray-100">
                            <svg class="w-4 h-4 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                            </svg>
                            Menu Permissions
                        </a>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Settings Content -->
        <div class="lg:col-span-2">
            <div class="card">
                <div class="card-body">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">General Settings</h3>
                    
                    <div class="space-y-6">
                        <!-- Application Settings -->
                        <div class="border-b border-gray-200 pb-6">
                            <h4 class="text-md font-medium text-gray-900 mb-4">Application Settings</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="form-label">Application Name</label>
                                    <input type="text" class="form-input" value="<?php echo APP_NAME; ?>" readonly>
                                    <p class="text-xs text-gray-500 mt-1">Defined in config/constants.php</p>
                                </div>
                                <div>
                                    <label class="form-label">Base URL</label>
                                    <input type="text" class="form-input" value="<?php echo BASE_URL; ?>" readonly>
                                    <p class="text-xs text-gray-500 mt-1">Defined in config/constants.php</p>
                                </div>
                            </div>
                        </div>

                        <!-- Session Settings -->
                        <div class="border-b border-gray-200 pb-6">
                            <h4 class="text-md font-medium text-gray-900 mb-4">Session Settings</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="form-label">Session Timeout</label>
                                    <input type="text" class="form-input" value="<?php echo SESSION_TIMEOUT; ?> seconds" readonly>
                                    <p class="text-xs text-gray-500 mt-1">Defined in config/auth.php</p>
                                </div>
                                <div>
                                    <label class="form-label">Current Session</label>
                                    <input type="text" class="form-input" value="Active (<?php echo $currentUser['username']; ?>)" readonly>
                                    <p class="text-xs text-gray-500 mt-1">Your current login session</p>
                                </div>
                            </div>
                        </div>

                        <!-- System Information -->
                        <div>
                            <h4 class="text-md font-medium text-gray-900 mb-4">System Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="form-label">PHP Version</label>
                                    <input type="text" class="form-input" value="<?php echo PHP_VERSION; ?>" readonly>
                                </div>
                                <div>
                                    <label class="form-label">Server Software</label>
                                    <input type="text" class="form-input" value="<?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?>" readonly>
                                </div>
                                <div>
                                    <label class="form-label">Database</label>
                                    <input type="text" class="form-input" value="MySQL (<?php echo DB_NAME; ?>)" readonly>
                                </div>
                                <div>
                                    <label class="form-label">Upload Max Size</label>
                                    <input type="text" class="form-input" value="<?php echo ini_get('upload_max_filesize'); ?>" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 p-4 bg-blue-50 rounded-lg">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-blue-400 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <h5 class="text-sm font-medium text-blue-800">Configuration Note</h5>
                                <p class="text-sm text-blue-700 mt-1">
                                    Most settings are configured in the application's configuration files. 
                                    Contact your system administrator to modify these settings.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../includes/admin_layout.php';
?>