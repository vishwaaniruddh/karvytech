<?php
require_once '../../config/auth.php';
require_once '../../includes/rbac_helper.php';

// Require admin access
requirePermission('users', 'manage_roles');

$title = 'RBAC Management';
ob_start();
?>

<div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-lg shadow-lg p-6 mb-8 text-white">
    <h1 class="text-3xl font-bold">RBAC Management</h1>
    <p class="text-blue-100 mt-2">Manage roles, modules, and permissions</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Roles Card -->
    <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-900">Roles</h2>
            <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"></path>
            </svg>
        </div>
        <p class="text-gray-600 mb-4">Manage system roles and their permissions</p>
        <a href="roles.php" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition">
            Manage Roles
        </a>
    </div>

    <!-- Modules Card -->
    <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-900">Modules</h2>
            <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
            </svg>
        </div>
        <p class="text-gray-600 mb-4">View and manage application modules</p>
        <a href="modules.php" class="inline-block bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded transition">
            Manage Modules
        </a>
    </div>

    <!-- Permissions Card -->
    <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-900">Permissions</h2>
            <svg class="w-8 h-8 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
            </svg>
        </div>
        <p class="text-gray-600 mb-4">View and manage permissions</p>
        <a href="permissions.php" class="inline-block bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded transition">
            Manage Permissions
        </a>
    </div>
</div>

<!-- Statistics -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6">
    <?php
    $roleModel = new Role();
    $permissionModel = new Permission();
    
    $roles = $roleModel->getAllRoles(null);
    $modules = $permissionModel->getAllModules(null);
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->query('SELECT COUNT(*) FROM permissions');
    $permCount = $stmt->fetchColumn();
    
    $stmt = $db->query('SELECT COUNT(*) FROM role_permissions');
    $rpCount = $stmt->fetchColumn();
    ?>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="text-3xl font-bold text-blue-600"><?php echo count($roles); ?></div>
        <div class="text-gray-600 text-sm mt-2">Total Roles</div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="text-3xl font-bold text-green-600"><?php echo count($modules); ?></div>
        <div class="text-gray-600 text-sm mt-2">Total Modules</div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="text-3xl font-bold text-purple-600"><?php echo $permCount; ?></div>
        <div class="text-gray-600 text-sm mt-2">Total Permissions</div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="text-3xl font-bold text-orange-600"><?php echo $rpCount; ?></div>
        <div class="text-gray-600 text-sm mt-2">Role-Permission Maps</div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once '../../includes/admin_layout.php';
?>
