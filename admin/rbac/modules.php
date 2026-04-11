<?php
require_once '../../config/auth.php';
require_once '../../includes/rbac_helper.php';
require_once '../../models/Permission.php';

// Require admin access
requirePermission('users', 'manage_roles');

$permissionModel = new Permission();

$title = 'Manage Modules';
ob_start();
?>

<div class="bg-gradient-to-r from-green-600 to-green-800 rounded-lg shadow-lg p-6 mb-8 text-white">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold">Manage Modules</h1>
            <p class="text-green-100 mt-2">View application modules and their permissions</p>
        </div>
        <a href="index.php" class="bg-white text-green-600 px-4 py-2 rounded hover:bg-green-50 transition">
            Back to RBAC
        </a>
    </div>
</div>

<?php
$modules = $permissionModel->getAllModules(null);
?>

<div class="space-y-6">
    <?php foreach ($modules as $module): ?>
    <?php
    $permissions = $permissionModel->getModulePermissions($module['id']);
    ?>
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($module['display_name']); ?></h2>
                <p class="text-gray-600 text-sm mt-1">
                    <code class="bg-gray-100 px-2 py-1 rounded"><?php echo htmlspecialchars($module['name']); ?></code>
                </p>
            </div>
            <span class="px-3 py-1 rounded text-sm font-semibold <?php echo $module['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                <?php echo ucfirst($module['status']); ?>
            </span>
        </div>
        
        <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($module['description'] ?? 'No description'); ?></p>
        
        <div class="mb-4">
            <h3 class="font-semibold text-gray-900 mb-3">Permissions (<?php echo count($permissions); ?>)</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                <?php foreach ($permissions as $permission): ?>
                <div class="bg-gray-50 p-3 rounded">
                    <p class="font-semibold text-sm text-gray-900"><?php echo htmlspecialchars($permission['display_name']); ?></p>
                    <p class="text-xs text-gray-600 mt-1">
                        <code><?php echo htmlspecialchars($permission['name']); ?></code>
                    </p>
                    <?php if ($permission['action']): ?>
                    <p class="text-xs text-gray-500 mt-1">Action: <?php echo htmlspecialchars($permission['action']); ?></p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php
$content = ob_get_clean();
require_once '../../includes/admin_layout.php';
?>
