<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Role.php';
require_once __DIR__ . '/../../models/Permission.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if (!$userId) {
    header('Location: ' . BASE_URL . '/admin/users/');
    exit;
}

$userModel = new User();
$roleModel = new Role();
$permissionModel = new Permission();

// Get user details
$user = $userModel->find($userId);
if (!$user) {
    header('Location: ' . BASE_URL . '/admin/users/');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'grant') {
            $permissionIds = $_POST['permissions'] ?? [];
            $notes = $_POST['notes'] ?? '';
            $currentUserId = Auth::getCurrentUser()['id'];
            
            foreach ($permissionIds as $permissionId) {
                $userModel->grantPermission($userId, $permissionId, $currentUserId, $notes);
            }
            
            $success = count($permissionIds) . " permission(s) granted successfully!";
        } elseif ($action === 'revoke') {
            $permissionId = $_POST['permission_id'] ?? 0;
            
            if ($permissionId) {
                $userModel->revokePermission($userId, $permissionId);
                $success = "Permission revoked successfully!";
            }
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Get user's role and permissions
$rolePermissions = [];
$userSpecificPermissions = [];
$allPermissions = [];

if ($user['role_id']) {
    $rolePermissions = $userModel->getRolePermissions($userId);
    $userSpecificPermissions = $userModel->getUserSpecificPermissions($userId);
    $allPermissions = $permissionModel->getAllPermissionsByModule();
}

// Create lookup arrays
$rolePermissionIds = array_column($rolePermissions, 'id');
$userPermissionIds = array_column($userSpecificPermissions, 'id');

$title = 'Manage Permissions - ' . htmlspecialchars($user['username']);
ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Manage Permissions</h1>
        <p class="mt-2 text-sm text-gray-700">
            Grant additional permissions to <strong><?php echo htmlspecialchars($user['username']); ?></strong>
            <?php if ($user['role_id']): ?>
                (Role: <strong><?php echo htmlspecialchars($user['rbac_role_display'] ?? $user['role']); ?></strong>)
            <?php endif; ?>
        </p>
    </div>
    <a href="<?php echo BASE_URL; ?>/admin/users/" class="btn btn-secondary">
        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
        </svg>
        Back to Users
    </a>
</div>

<?php if (isset($success)): ?>
<div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-4">
    <?php echo $success; ?>
</div>
<?php endif; ?>

<?php if (isset($error)): ?>
<div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
    <?php echo $error; ?>
</div>
<?php endif; ?>

<?php if (!$user['role_id']): ?>
<div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded mb-4">
    <strong>Warning:</strong> This user doesn't have a role assigned. Please <a href="assign-role.php?user_id=<?php echo $userId; ?>" class="underline">assign a role</a> first.
</div>
<?php else: ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Current Permissions Summary -->
    <div class="lg:col-span-3">
        <div class="card">
            <div class="card-body">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Permission Summary</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="text-sm text-blue-600 font-medium">Role Permissions</div>
                        <div class="text-2xl font-bold text-blue-900 mt-1"><?php echo count($rolePermissions); ?></div>
                        <div class="text-xs text-blue-600 mt-1">From assigned role</div>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <div class="text-sm text-green-600 font-medium">Additional Permissions</div>
                        <div class="text-2xl font-bold text-green-900 mt-1"><?php echo count($userSpecificPermissions); ?></div>
                        <div class="text-xs text-green-600 mt-1">User-specific grants</div>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <div class="text-sm text-purple-600 font-medium">Total Permissions</div>
                        <div class="text-2xl font-bold text-purple-900 mt-1"><?php echo count($rolePermissions) + count($userSpecificPermissions); ?></div>
                        <div class="text-xs text-purple-600 mt-1">Combined access</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User-Specific Permissions (Overrides) -->
    <div class="lg:col-span-1">
        <div class="card">
            <div class="card-body">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Additional Permissions
                    <span class="text-sm font-normal text-gray-500">(<?php echo count($userSpecificPermissions); ?>)</span>
                </h3>
                
                <?php if (empty($userSpecificPermissions)): ?>
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        <p class="mt-2 text-sm text-gray-600">No additional permissions</p>
                        <p class="text-xs text-gray-500 mt-1">Grant permissions from the list →</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-2 max-h-96 overflow-y-auto">
                        <?php 
                        $groupedUserPerms = [];
                        foreach ($userSpecificPermissions as $perm) {
                            $groupedUserPerms[$perm['module_display_name']][] = $perm;
                        }
                        
                        foreach ($groupedUserPerms as $moduleName => $perms): 
                        ?>
                        <div class="border border-green-200 rounded-lg p-3 bg-green-50">
                            <h4 class="text-sm font-semibold text-gray-900 mb-2"><?php echo htmlspecialchars($moduleName); ?></h4>
                            <?php foreach ($perms as $perm): ?>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs text-gray-700"><?php echo htmlspecialchars($perm['display_name']); ?></span>
                                <form method="POST" class="inline" onsubmit="return confirm('Revoke this permission?');">
                                    <input type="hidden" name="action" value="revoke">
                                    <input type="hidden" name="permission_id" value="<?php echo $perm['id']; ?>">
                                    <button type="submit" class="text-red-600 hover:text-red-800" title="Revoke">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                            <?php if (!empty($perm['notes'])): ?>
                            <div class="text-xs text-gray-500 italic mt-1">
                                Note: <?php echo htmlspecialchars($perm['notes']); ?>
                            </div>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Grant Additional Permissions -->
    <div class="lg:col-span-2">
        <div class="card">
            <div class="card-body">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Grant Additional Permissions</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Select permissions to grant beyond the user's role. These are additional permissions not included in their assigned role.
                </p>
                
                <form method="POST">
                    <input type="hidden" name="action" value="grant">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Reason (Optional)</label>
                        <textarea name="notes" rows="2" class="form-input text-sm" placeholder="Why are you granting these permissions?"></textarea>
                    </div>
                    
                    <div class="space-y-4 max-h-96 overflow-y-auto border border-gray-200 rounded-lg p-4">
                        <?php foreach ($allPermissions as $moduleName => $moduleData): ?>
                        <div class="border-b border-gray-200 pb-3 last:border-0">
                            <h4 class="text-sm font-semibold text-gray-900 mb-2">
                                <?php echo htmlspecialchars($moduleData['display_name']); ?>
                            </h4>
                            <div class="grid grid-cols-2 gap-2">
                                <?php foreach ($moduleData['permissions'] as $perm): ?>
                                <?php 
                                $hasFromRole = in_array($perm['id'], $rolePermissionIds);
                                $hasFromUser = in_array($perm['id'], $userPermissionIds);
                                $isDisabled = $hasFromRole || $hasFromUser;
                                ?>
                                <label class="flex items-center text-sm <?php echo $isDisabled ? 'opacity-50' : ''; ?>">
                                    <input 
                                        type="checkbox" 
                                        name="permissions[]" 
                                        value="<?php echo $perm['id']; ?>"
                                        <?php echo $isDisabled ? 'disabled checked' : ''; ?>
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-2"
                                    >
                                    <span class="<?php echo $hasFromRole ? 'text-blue-600' : ($hasFromUser ? 'text-green-600' : 'text-gray-700'); ?>">
                                        <?php echo htmlspecialchars($perm['display_name']); ?>
                                        <?php if ($hasFromRole): ?>
                                            <span class="text-xs">(from role)</span>
                                        <?php elseif ($hasFromUser): ?>
                                            <span class="text-xs">(granted)</span>
                                        <?php endif; ?>
                                    </span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="mt-4 flex justify-end space-x-3">
                        <a href="<?php echo BASE_URL; ?>/admin/users/" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Grant Selected Permissions</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../includes/admin_layout.php';
?>
