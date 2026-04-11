<?php
require_once '../../config/auth.php';
require_once '../../includes/rbac_helper.php';
require_once '../../models/Role.php';
require_once '../../models/Permission.php';

// Require admin access
requirePermission('users', 'manage_roles');

$roleModel = new Role();
$permissionModel = new Permission();

$roleId = $_GET['id'] ?? null;
if (!$roleId) {
    header('Location: roles.php');
    exit;
}

$role = $roleModel->getRoleById($roleId);
if (!$role) {
    header('Location: roles.php');
    exit;
}

$title = 'Edit Role: ' . $role['display_name'];
ob_start();

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $permissionIds = $_POST['permissions'] ?? [];
    try {
        $roleModel->assignPermissionsToRole($roleId, $permissionIds);
        $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">✓ Permissions updated successfully!</div>';
    } catch (Exception $e) {
        $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">✗ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

$currentPermissions = $roleModel->getRolePermissions($roleId);
$currentPermIds = array_map(function($p) { return $p['id']; }, $currentPermissions);
$allPermissions = $permissionModel->getAllPermissionsGrouped();
?>

<div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-lg shadow-lg p-6 mb-8 text-white">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold">Edit Role: <?php echo htmlspecialchars($role['display_name']); ?></h1>
            <p class="text-blue-100 mt-2">Manage permissions for this role</p>
        </div>
        <a href="roles.php" class="bg-white text-blue-600 px-4 py-2 rounded hover:bg-blue-50 transition">
            Back to Roles
        </a>
    </div>
</div>

<?php echo $message; ?>

<form method="POST" class="bg-white rounded-lg shadow-md p-6">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Role Information</h2>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Role Name</label>
                <input type="text" value="<?php echo htmlspecialchars($role['name']); ?>" disabled class="w-full px-4 py-2 border border-gray-300 rounded bg-gray-100">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Display Name</label>
                <input type="text" value="<?php echo htmlspecialchars($role['display_name']); ?>" disabled class="w-full px-4 py-2 border border-gray-300 rounded bg-gray-100">
            </div>
        </div>
        <div class="mt-4">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
            <textarea disabled class="w-full px-4 py-2 border border-gray-300 rounded bg-gray-100"><?php echo htmlspecialchars($role['description'] ?? ''); ?></textarea>
        </div>
    </div>

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Permissions</h2>
        <p class="text-gray-600 mb-4">Select permissions to assign to this role:</p>
        
        <div class="space-y-4">
            <?php foreach ($allPermissions as $module): ?>
            <div class="border border-gray-200 rounded-lg p-4">
                <h3 class="font-bold text-gray-900 mb-3"><?php echo htmlspecialchars($module['module_display_name']); ?></h3>
                
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    <?php foreach ($module['permissions'] as $permission): ?>
                    <label class="flex items-center">
                        <input 
                            type="checkbox" 
                            name="permissions[]" 
                            value="<?php echo $permission['id']; ?>"
                            <?php echo in_array($permission['id'], $currentPermIds) ? 'checked' : ''; ?>
                            class="w-4 h-4 text-blue-600 rounded"
                        >
                        <span class="ml-2 text-sm text-gray-700">
                            <?php echo htmlspecialchars($permission['display_name']); ?>
                        </span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="flex gap-4">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded transition">
            Save Permissions
        </button>
        <a href="roles.php" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded transition">
            Cancel
        </a>
    </div>
</form>

<?php
$content = ob_get_clean();
require_once '../../includes/admin_layout.php';
?>
