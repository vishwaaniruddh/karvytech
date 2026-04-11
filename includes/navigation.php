<?php
$currentUser = Auth::getCurrentUser();
$isAdmin = $currentUser && $currentUser['role'] === ADMIN_ROLE;
$isVendor = $currentUser && $currentUser['role'] === VENDOR_ROLE;
?>

<nav class="bg-blue-600 shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <a href="<?php echo BASE_URL; ?>" class="text-white text-xl font-bold">
                    <?php echo APP_NAME; ?>
                </a>
            </div>
            
            <?php if ($currentUser): ?>
            <div class="flex items-center space-x-4">
                <?php if ($isAdmin): ?>
                <a href="<?php echo url('admin/dashboard.php'); ?>" class="text-white hover:text-blue-200">Dashboard</a>
                <a href="<?php echo url('admin/sites/'); ?>" class="text-white hover:text-blue-200">Sites</a>
                <a href="<?php echo url('admin/inventory/'); ?>" class="text-white hover:text-blue-200">Inventory</a>
                <a href="<?php echo url('admin/reports/'); ?>" class="text-white hover:text-blue-200">Reports</a>
                <?php elseif ($isVendor): ?>
                <a href="<?php echo url('vendor/'); ?>" class="text-white hover:text-blue-200">Dashboard</a>
                <a href="<?php echo url('vendor/sites/'); ?>" class="text-white hover:text-blue-200">My Sites</a>
                <a href="<?php echo url('vendor/materials/'); ?>" class="text-white hover:text-blue-200">Materials</a>
                <?php endif; ?>
                
                <div class="relative flex items-center space-x-3">
                    <!-- Environment Indicator -->
                    <?php 
                    // Ensure constants are loaded
                    if (!function_exists('getEnvironment')) {
                        require_once __DIR__ . '/../config/constants.php';
                    }
                    
                    $env = function_exists('getEnvironment') ? getEnvironment() : (defined('APP_ENV') ? APP_ENV : 'unknown');
                    $envColors = [
                        'development' => 'bg-green-500 text-white',
                        'testing' => 'bg-yellow-500 text-black',
                        'production' => 'bg-red-500 text-white'
                    ];
                    $envColor = $envColors[$env] ?? 'bg-gray-500 text-white';
                    ?>
                    <span class="<?php echo $envColor; ?> px-2 py-1 rounded-full text-xs font-bold uppercase tracking-wide">
                        <?php echo strtoupper($env); ?>
                    </span>
                    
                    <span class="text-white">Welcome, <?php echo htmlspecialchars($currentUser['username']); ?></span>
                    <a href="<?php echo url('auth/logout.php'); ?>" class="ml-4 text-white hover:text-blue-200">Logout</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</nav>