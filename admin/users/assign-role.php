<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Role.php';
require_once __DIR__ . '/../../includes/rbac_helper.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if (!$userId) {
    header('Location: ' . BASE_URL . '/admin/users/');
    exit;
}

$userModel = new User();
$roleModel = new Role();

// Get user details
$user = $userModel->find($userId);
if (!$user) {
    header('Location: ' . BASE_URL . '/admin/users/');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roleId = isset($_POST['role_id']) ? (int)$_POST['role_id'] : 0;
    
    try {
        if ($roleId) {
            $userModel->assignRole($userId, $roleId);
            
            // Update the role field as well for backward compatibility
            $role = $roleModel->getRoleById($roleId);
            if ($role) {
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("UPDATE users SET role = ? WHERE id = ?");
                $stmt->execute([$role['name'], $userId]);
            }
            
            $success = "Role assigned successfully!";
            // Refresh user data
            $user = $userModel->find($userId);
        } else {
            $error = "Please select a role";
        }
    } catch (Exception $e) {
        $error = "Error assigning role: " . $e->getMessage();
    }
}

// Get all roles and user's current role
$allRoles = $roleModel->getAllRoles();
$currentRoleId = $user['role_id'] ?? null;

// Get user's current permissions if they have a role
$userPermissions = [];
if ($currentRoleId) {
    // Get actual user permissions (role + custom - removed)
    $allUserPerms = $userModel->getUserPermissions($userId);
    
    // Group by module
    foreach ($allUserPerms as $perm) {
        $moduleName = $perm['module_name'];
        if (!isset($userPermissions[$moduleName])) {
            $userPermissions[$moduleName] = [
                'module_display_name' => $perm['module_display_name'],
                'permissions' => []
            ];
        }
        $userPermissions[$moduleName]['permissions'][] = $perm;
    }
}

$title = 'Assign Role - ' . htmlspecialchars($user['username']);
ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Assign Role</h1>
        <p class="mt-2 text-sm text-gray-700">
            Manage role and permissions for <strong><?php echo htmlspecialchars($user['username']); ?></strong>
            (<?php echo htmlspecialchars($user['email']); ?>)
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

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Assign Role Card -->
    <div class="card">
        <div class="card-body">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Select Role</h3>
            <p class="text-sm text-gray-600 mb-4">
                Choose a role for this user. The role determines what permissions and access the user will have.
            </p>
            
            <form method="POST">
                <div class="space-y-4">
                    <?php foreach ($allRoles as $role): ?>
                    <label class="flex items-start p-4 border rounded-lg cursor-pointer hover:bg-gray-50 <?php echo $currentRoleId == $role['id'] ? 'border-blue-500 bg-blue-50' : 'border-gray-200'; ?>">
                        <input 
                            type="radio" 
                            name="role_id" 
                            value="<?php echo $role['id']; ?>"
                            <?php echo $currentRoleId == $role['id'] ? 'checked' : ''; ?>
                            class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500"
                        >
                        <div class="ml-3 flex-1">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($role['display_name']); ?>
                                </span>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs px-2 py-1 rounded <?php echo $role['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                        <?php echo ucfirst($role['status']); ?>
                                    </span>
                                    <button type="button" onclick="viewRolePermissions(<?php echo $role['id']; ?>, '<?php echo htmlspecialchars($role['display_name']); ?>')" class="text-xs text-blue-600 hover:text-blue-800 underline">
                                        View Permissions
                                    </button>
                                </div>
                            </div>
                            <p class="text-xs text-gray-600 mt-1">
                                <?php echo htmlspecialchars($role['description'] ?? 'No description'); ?>
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                <code class="bg-gray-100 px-1 py-0.5 rounded"><?php echo htmlspecialchars($role['name']); ?></code>
                            </p>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
                
                <div class="mt-6">
                    <button type="submit" class="btn btn-primary w-full">
                        Assign Role
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Current Permissions Card -->
    <div class="card">
        <div class="card-body">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Current Permissions</h3>
                <?php if ($currentRoleId): ?>
                <button 
                    type="button" 
                    onclick="openEditPermissionsModal()"
                    class="btn btn-sm btn-primary"
                    title="Edit Permissions"
                >
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                    </svg>
                    Edit Permissions
                </button>
                <?php endif; ?>
            </div>
            
            <?php if (empty($userPermissions)): ?>
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <p class="mt-2 text-sm text-gray-600">No role assigned yet</p>
                    <p class="text-xs text-gray-500 mt-1">Select a role to see permissions</p>
                </div>
            <?php else: ?>
                <p class="text-sm text-gray-600 mb-4">
                    This user has access to the following modules and actions:
                </p>
                
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    <?php foreach ($userPermissions as $moduleName => $data): ?>
                    <div class="border border-gray-200 rounded-lg p-3">
                        <h4 class="text-sm font-semibold text-gray-900 mb-2">
                            <?php echo htmlspecialchars($data['module_display_name']); ?>
                        </h4>
                        <div class="flex flex-wrap gap-1">
                            <?php foreach ($data['permissions'] as $perm): ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                <?php echo htmlspecialchars($perm['name']); ?>
                            </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                    <p class="text-xs text-blue-800">
                        <strong>Total Permissions:</strong> 
                        <?php 
                        $totalPerms = 0;
                        foreach ($userPermissions as $data) {
                            $totalPerms += count($data['permissions']);
                        }
                        echo $totalPerms;
                        ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Role Details Section -->
<?php if (!empty($allRoles)): ?>
<div class="card mt-6">
    <div class="card-body">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Role Comparison</h3>
        <p class="text-sm text-gray-600 mb-4">
            Compare permissions across different roles to choose the right one:
        </p>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Permissions</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($allRoles as $role): ?>
                    <?php
                    $rolePerms = $roleModel->getRolePermissions($role['id']);
                    ?>
                    <tr class="<?php echo $currentRoleId == $role['id'] ? 'bg-blue-50' : ''; ?>">
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($role['display_name']); ?>
                                <?php if ($currentRoleId == $role['id']): ?>
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                    Current
                                </span>
                                <?php endif; ?>
                            </div>
                            <div class="text-xs text-gray-500">
                                <code><?php echo htmlspecialchars($role['name']); ?></code>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-gray-600">
                                <?php echo htmlspecialchars($role['description'] ?? 'No description'); ?>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button 
                                type="button"
                                onclick="viewRolePermissions(<?php echo $role['id']; ?>, '<?php echo htmlspecialchars($role['display_name'], ENT_QUOTES); ?>')"
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 hover:bg-blue-200 cursor-pointer transition-colors"
                                title="Click to view permissions"
                            >
                                <?php echo count($rolePerms); ?> permissions
                            </button>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium <?php echo $role['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                <?php echo ucfirst($role['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- View Role Permissions Modal -->
<div id="rolePermissionsModal" class="modal">
    <div class="modal-content max-w-4xl">
        <div class="modal-header">
            <h3 class="modal-title" id="rolePermissionsTitle">Role Permissions</h3>
            <button type="button" class="modal-close" onclick="closeModal('rolePermissionsModal')">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <div id="rolePermissionsContent" class="space-y-4">
                <div class="text-center py-8">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                    <p class="mt-2 text-sm text-gray-600">Loading permissions...</p>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" onclick="closeModal('rolePermissionsModal')" class="btn btn-secondary">Close</button>
        </div>
    </div>
</div>

<!-- Edit Permissions Modal -->
<div id="editPermissionsModal" class="modal">
    <div class="modal-content max-w-6xl">
        <div class="modal-header">
            <h3 class="modal-title">Edit User Permissions</h3>
            <button type="button" class="modal-close" onclick="closeModal('editPermissionsModal')">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex">
                    <svg class="w-5 h-5 text-yellow-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-yellow-800">Permission Management</p>
                        <p class="text-xs text-yellow-700 mt-1">
                            You can customize permissions for this user. Changes will override the default role permissions.
                        </p>
                    </div>
                </div>
            </div>

            <div class="mb-4 flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <button type="button" onclick="selectAllPermissions()" class="text-sm text-blue-600 hover:text-blue-800">
                        Select All
                    </button>
                    <button type="button" onclick="deselectAllPermissions()" class="text-sm text-blue-600 hover:text-blue-800">
                        Deselect All
                    </button>
                    <button type="button" onclick="resetToRolePermissions()" class="text-sm text-orange-600 hover:text-orange-800">
                        Reset to Role Defaults
                    </button>
                </div>
                <div>
                    <input type="text" id="permissionSearch" placeholder="Search permissions..." class="form-input text-sm" onkeyup="filterPermissions()">
                </div>
            </div>
            
            <form id="editPermissionsForm">
                <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                <input type="hidden" name="role_id" value="<?php echo $currentRoleId; ?>">
                
                <div id="permissionsContent" class="space-y-4 max-h-96 overflow-y-auto">
                    <div class="text-center py-8">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                        <p class="mt-2 text-sm text-gray-600">Loading permissions...</p>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" onclick="closeModal('editPermissionsModal')" class="btn btn-secondary">Cancel</button>
            <button type="button" onclick="savePermissions()" class="btn btn-primary">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
                Save Changes
            </button>
        </div>
    </div>
</div>

<script>
let allPermissionsData = [];
let rolePermissionsData = [];

function viewRolePermissions(roleId, roleName) {
    console.log('Opening modal for role:', roleId, roleName);
    
    // Reset modal content to loading state
    const contentDiv = document.getElementById('rolePermissionsContent');
    if (!contentDiv) {
        console.error('rolePermissionsContent element not found');
        return;
    }
    
    contentDiv.innerHTML = `
        <div class="text-center py-8">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
            <p class="mt-2 text-sm text-gray-600">Loading permissions...</p>
        </div>
    `;
    
    // Update title
    const titleElement = document.getElementById('rolePermissionsTitle');
    if (titleElement) {
        titleElement.textContent = roleName + ' - Permissions';
    }
    
    // Open modal
    openModal('rolePermissionsModal');
    
    // Fetch role permissions
    const url = '<?php echo BASE_URL; ?>/api/rbac/roles.php?action=permissions&role_id=' + roleId;
    console.log('Fetching from:', url);
    
    fetch(url)
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Data received:', data);
            if (data.success && data.permissions) {
                displayRolePermissions(data.permissions);
            } else {
                contentDiv.innerHTML = 
                    '<div class="text-center py-8"><p class="text-red-600">Failed to load permissions: ' + (data.message || 'Unknown error') + '</p></div>';
            }
        })
        .catch(error => {
            console.error('Error loading permissions:', error);
            contentDiv.innerHTML = 
                '<div class="text-center py-8"><p class="text-red-600">Error loading permissions: ' + error.message + '</p></div>';
        });
}

function displayRolePermissions(permissions) {
    // Group permissions by module
    const grouped = {};
    permissions.forEach(perm => {
        const moduleName = perm.module_display_name || perm.module_name;
        if (!grouped[moduleName]) {
            grouped[moduleName] = [];
        }
        grouped[moduleName].push(perm);
    });
    
    let html = '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';
    
    Object.keys(grouped).sort().forEach(moduleName => {
        html += `
            <div class="border border-gray-200 rounded-lg p-4">
                <h4 class="text-sm font-semibold text-gray-900 mb-3">${moduleName}</h4>
                <div class="flex flex-wrap gap-2">
        `;
        
        grouped[moduleName].forEach(perm => {
            html += `
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    ${perm.display_name || perm.name}
                </span>
            `;
        });
        
        html += `
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    
    html += `
        <div class="mt-4 p-4 bg-blue-50 rounded-lg">
            <p class="text-sm text-blue-800">
                <strong>Total Permissions:</strong> ${permissions.length}
            </p>
        </div>
    `;
    
    document.getElementById('rolePermissionsContent').innerHTML = html;
}

function openEditPermissionsModal() {
    const roleId = <?php echo $currentRoleId ?? 0; ?>;
    const userId = <?php echo $userId; ?>;
    
    if (!roleId) {
        showAlert('Please assign a role first', 'error');
        return;
    }
    
    openModal('editPermissionsModal');
    loadPermissionsForEditing(roleId, userId);
}

function loadPermissionsForEditing(roleId, userId) {
    const contentDiv = document.getElementById('permissionsContent');
    contentDiv.innerHTML = `
        <div class="text-center py-8">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
            <p class="mt-2 text-sm text-gray-600">Loading permissions...</p>
        </div>
    `;
    
    // Fetch all available permissions and user's current permissions
    Promise.all([
        fetch('<?php echo BASE_URL; ?>/api/rbac/permissions.php?action=all').then(r => r.json()),
        fetch('<?php echo BASE_URL; ?>/api/rbac/roles.php?action=permissions&role_id=' + roleId).then(r => r.json()),
        fetch('<?php echo BASE_URL; ?>/api/rbac/users.php?action=permissions&user_id=' + userId).then(r => r.json())
    ])
    .then(([allPerms, rolePerms, userPerms]) => {
        if (allPerms.success && rolePerms.success && userPerms.success) {
            allPermissionsData = allPerms.permissions || [];
            rolePermissionsData = rolePerms.permissions || [];
            const userPermissionsData = userPerms.permissions || [];
            
            displayEditablePermissions(allPermissionsData, rolePermissionsData, userPermissionsData);
        } else {
            contentDiv.innerHTML = '<div class="text-center py-8"><p class="text-red-600">Failed to load permissions</p></div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        contentDiv.innerHTML = '<div class="text-center py-8"><p class="text-red-600">Error loading permissions: ' + error.message + '</p></div>';
    });
}

function displayEditablePermissions(allPermissions, rolePermissions, userPermissions) {
    // Group by module
    const grouped = {};
    const rolePermIds = new Set(rolePermissions.map(p => p.id));
    const userPermIds = new Set(userPermissions.map(p => p.id));
    
    allPermissions.forEach(perm => {
        const moduleName = perm.module_display_name || perm.module_name;
        if (!grouped[moduleName]) {
            grouped[moduleName] = {
                module_id: perm.module_id,
                permissions: []
            };
        }
        grouped[moduleName].permissions.push({
            ...perm,
            fromRole: rolePermIds.has(perm.id),
            checked: userPermIds.has(perm.id)
        });
    });
    
    let html = '<div class="space-y-4">';
    
    Object.keys(grouped).sort().forEach(moduleName => {
        const moduleData = grouped[moduleName];
        const allChecked = moduleData.permissions.every(p => p.checked);
        const someChecked = moduleData.permissions.some(p => p.checked);
        
        html += `
            <div class="border border-gray-200 rounded-lg p-4 permission-module">
                <div class="flex items-center justify-between mb-3">
                    <label class="flex items-center cursor-pointer">
                        <input 
                            type="checkbox" 
                            class="module-checkbox h-4 w-4 text-blue-600 rounded"
                            ${allChecked ? 'checked' : ''}
                            ${someChecked && !allChecked ? 'indeterminate' : ''}
                            onchange="toggleModulePermissions(this, '${moduleName}')"
                        >
                        <span class="ml-2 text-sm font-semibold text-gray-900">${moduleName}</span>
                    </label>
                    <span class="text-xs text-gray-500">${moduleData.permissions.length} permissions</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 module-permissions" data-module="${moduleName}">
        `;
        
        moduleData.permissions.forEach(perm => {
            const badgeClass = perm.fromRole ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200';
            const labelClass = perm.fromRole ? 'text-green-900' : 'text-gray-900';
            
            html += `
                <label class="flex items-start p-2 border rounded ${badgeClass} cursor-pointer hover:bg-opacity-75 transition-colors permission-item">
                    <input 
                        type="checkbox" 
                        name="permissions[]" 
                        value="${perm.id}"
                        class="permission-checkbox mt-0.5 h-4 w-4 text-blue-600 rounded"
                        ${perm.checked ? 'checked' : ''}
                        data-module="${moduleName}"
                        data-from-role="${perm.fromRole}"
                        onchange="updateModuleCheckbox('${moduleName}')"
                    >
                    <div class="ml-2 flex-1">
                        <span class="text-xs font-medium ${labelClass}">${perm.display_name || perm.name}</span>
                        ${perm.fromRole ? '<span class="ml-1 text-xs text-green-600">(Role)</span>' : ''}
                        ${perm.description ? `<p class="text-xs text-gray-500 mt-0.5">${perm.description}</p>` : ''}
                    </div>
                </label>
            `;
        });
        
        html += `
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    
    document.getElementById('permissionsContent').innerHTML = html;
    
    // Set indeterminate state for module checkboxes
    document.querySelectorAll('.module-checkbox').forEach(checkbox => {
        if (checkbox.hasAttribute('indeterminate')) {
            checkbox.indeterminate = true;
        }
    });
}

function toggleModulePermissions(checkbox, moduleName) {
    const moduleDiv = document.querySelector(`.module-permissions[data-module="${moduleName}"]`);
    const permCheckboxes = moduleDiv.querySelectorAll('.permission-checkbox');
    
    permCheckboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
    
    checkbox.indeterminate = false;
}

function updateModuleCheckbox(moduleName) {
    const moduleDiv = document.querySelector(`.module-permissions[data-module="${moduleName}"]`);
    const permCheckboxes = moduleDiv.querySelectorAll('.permission-checkbox');
    const moduleCheckbox = moduleDiv.closest('.permission-module').querySelector('.module-checkbox');
    
    const total = permCheckboxes.length;
    const checked = Array.from(permCheckboxes).filter(cb => cb.checked).length;
    
    if (checked === 0) {
        moduleCheckbox.checked = false;
        moduleCheckbox.indeterminate = false;
    } else if (checked === total) {
        moduleCheckbox.checked = true;
        moduleCheckbox.indeterminate = false;
    } else {
        moduleCheckbox.checked = false;
        moduleCheckbox.indeterminate = true;
    }
}

function selectAllPermissions() {
    document.querySelectorAll('.permission-checkbox').forEach(cb => {
        cb.checked = true;
    });
    document.querySelectorAll('.module-checkbox').forEach(cb => {
        cb.checked = true;
        cb.indeterminate = false;
    });
}

function deselectAllPermissions() {
    document.querySelectorAll('.permission-checkbox').forEach(cb => {
        cb.checked = false;
    });
    document.querySelectorAll('.module-checkbox').forEach(cb => {
        cb.checked = false;
        cb.indeterminate = false;
    });
}

function resetToRolePermissions() {
    document.querySelectorAll('.permission-checkbox').forEach(cb => {
        cb.checked = cb.getAttribute('data-from-role') === 'true';
    });
    
    // Update module checkboxes
    document.querySelectorAll('.module-permissions').forEach(moduleDiv => {
        const moduleName = moduleDiv.getAttribute('data-module');
        updateModuleCheckbox(moduleName);
    });
}

function filterPermissions() {
    const searchTerm = document.getElementById('permissionSearch').value.toLowerCase();
    const items = document.querySelectorAll('.permission-item');
    
    items.forEach(item => {
        const text = item.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
}

function savePermissions() {
    const form = document.getElementById('editPermissionsForm');
    const formData = new FormData(form);
    
    // Show loading state
    const saveBtn = event.target;
    const originalText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<svg class="animate-spin h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M4 2a2 2 0 00-2 2v11a3 3 0 106 0V4a2 2 0 00-2-2H4z"></path></svg> Saving...';
    
    fetch('<?php echo BASE_URL; ?>/api/rbac/users.php?action=update_permissions', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Permissions updated successfully', 'success');
            closeModal('editPermissionsModal');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert(data.message || 'Failed to update permissions', 'error');
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error updating permissions: ' + error.message, 'error');
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalText;
    });
}
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../includes/admin_layout.php';
?>
