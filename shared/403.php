<?php
require_once __DIR__ . '/../config/auth.php';

$title = 'Access Denied';
$permissionRequired = $_GET['permission'] ?? '';
$moduleRequired = $_GET['module'] ?? '';

// Determine layout
if (Auth::isVendor()) {
    $layoutPath = __DIR__ . '/../includes/vendor_layout.php';
} else {
    $layoutPath = __DIR__ . '/../includes/admin_layout.php';
}

ob_start();
?>

<div class="min-h-[70vh] flex items-center justify-center px-4">
    <div class="max-w-lg w-full text-center">
        <!-- Animated Icon -->
        <div class="mb-8 flex justify-center">
            <div class="relative">
                <div class="absolute inset-0 bg-red-100 rounded-full animate-ping opacity-25"></div>
                <div class="relative bg-red-50 p-6 rounded-full border-2 border-red-100 shadow-sm">
                    <svg class="w-20 h-20 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                            d="M12 15v2m0 0v2m0-2h2m-2 0H10m11.953-8.117a10.511 10.511 0 11-12.906-8.72l1.1-.18a10.49 10.49 0 0111.806 8.9zm-4.703-1.133a3.5 3.5 0 00-4.444-4.444 3.5 3.5 0 004.444 4.444z">
                        </path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                            d="M9 10l2 2m0 0l2 2m-2-2l2-2m-2 2l-2 2">
                        </path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Content -->
        <h1 class="text-4xl font-extrabold text-gray-900 mb-4 tracking-tight">Access Denied</h1>
        <p class="text-lg text-gray-600 mb-8 leading-relaxed">
            Sorry, you don't have the necessary permissions to access this page. 
            <?php if ($permissionRequired): ?>
                <br><span class="inline-block mt-2 px-3 py-1 bg-gray-100 text-gray-700 text-sm font-mono rounded border border-gray-200">
                    Required: <?php echo htmlspecialchars($permissionRequired); ?>
                </span>
            <?php elseif ($moduleRequired): ?>
                <br><span class="inline-block mt-2 px-3 py-1 bg-gray-100 text-gray-700 text-sm font-mono rounded border border-gray-200">
                    Module: <?php echo htmlspecialchars($moduleRequired); ?>
                </span>
            <?php endif; ?>
        </p>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <button onclick="window.history.back()" 
                class="w-full sm:w-auto px-8 py-3 bg-gray-900 text-white font-semibold rounded-xl hover:bg-black transition-all transform hover:-translate-y-0.5 shadow-lg flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Go Back
            </button>
            <a href="<?php echo url(Auth::isVendor() ? '/contractor/dashboard.php' : '/admin/dashboard.php'); ?>" 
                class="w-full sm:w-auto px-8 py-3 bg-white text-gray-900 font-semibold rounded-xl border-2 border-gray-200 hover:border-gray-900 transition-all transform hover:-translate-y-0.5 shadow-sm flex items-center justify-center">
                Return to Dashboard
            </a>
        </div>

        <!-- Help Text -->
        <p class="mt-12 text-sm text-gray-400">
            If you believe this is a mistake, please contact your system administrator.
        </p>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once $layoutPath;
?>
