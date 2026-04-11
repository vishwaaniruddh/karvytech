<?php
require_once '../../config/auth.php';
require_once '../../includes/rbac_helper.php';
require_once '../../models/Permission.php';

// Require admin access
requirePermission('users', 'manage_roles');

$permissionModel = new Permission();

$title = 'Manage Permissions';
ob_start();
?>

<div class="bg-gradient-to-r from-purple-600 to-purple-800 rounded-lg shadow-lg p-6 mb-8 text-white">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold">Manage Permissions</h1>
            <p class="text-purple-100 mt-2">View all permissions grouped by module</p>
        </div>
        <a href="index.php" class="bg-white text-purple-600 px-4 py-2 rounded hover:bg-purple-50 transition">
            Back to RBAC
        </a>
    </div>
</div>

<?php
$allPermissions = $permissionModel->getAllPermissionsGrouped();
?>

<div class="space-y-6">
    <?php foreach ($allPermissions as $module): ?>
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-start mb-4">
            <h2 class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($module['module_display_name']); ?></h2>
            <span class="px-3 py-1 rounded text-sm font-semibold bg-blue-100 text-blue-800">
                <?php echo count($module['permissions']); ?> permissions
            </span>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($module['permissions'] as $permission): ?>
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($permission['display_name']); ?></h3>
                    <span class="px-2 py-1 rounded text-xs font-semibold bg-gray-100 text-gray-700">
                        <?php echo htmlspecialchars($permission['name']); ?>
                    </span>
                </div>
                
                <p class="text-sm text-gray-600 mb-2"><?php echo htmlspecialchars($permission['description'] ?? 'No description'); ?></p>
                
                <?php if ($permission['action']): ?>
                <p class="text-xs text-gray-500">
                    <strong>Action:</strong> <?php echo htmlspecialchars($permission['action']); ?>
                </p>
                <?php endif; ?>
                
                <div class="mt-3 pt-3 border-t border-gray-200">
                    <p class="text-xs text-gray-500">
                        <strong>Permission ID:</strong> <?php echo $permission['id']; ?>
                    </p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php
$content = ob_get_clean();
require_once '../../includes/admin_layout.php';
?>
