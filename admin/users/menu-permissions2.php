<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/VendorPermission.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if (!$userId) {
    header('Location: ' . BASE_URL . '/admin/users/');
    exit;
}

$userModel = new User();
$permissionModel = new VendorPermission();

// Get user details
$user = $userModel->find($userId);
if (!$user) {
    header('Location: ' . BASE_URL . '/admin/users/');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $permissions = $_POST['permissions'] ?? [];
    
    try {
        $currentUserId = Auth::getUserId();
        
        // Get vendor_id from user record
        $vendorId = $user['vendor_id'];
        
        if (!$vendorId) {
            throw new Exception("This user is not assigned to any vendor. Please assign a vendor first.");
        }
        
        // Define all vendor menu items with their structure
        $allVendorMenus = getVendorMenuStructure();
        
        // Update each permission using vendor_id and user_id
        foreach ($allVendorMenus as $menu) {
            if (isset($menu['permission_key'])) {
                $value = in_array($menu['permission_key'], $permissions);
                $permissionModel->setPermission($vendorId, $menu['permission_key'], $value, $currentUserId, $userId);
            }
            
            // Handle sub-items
            if (isset($menu['children'])) {
                foreach ($menu['children'] as $child) {
                    if (isset($child['permission_key'])) {
                        $value = in_array($child['permission_key'], $permissions);
                        $permissionModel->setPermission($vendorId, $child['permission_key'], $value, $currentUserId, $userId);
                    }
                }
            }
        }
        
        $success = "Vendor menu permissions updated successfully!";
    } catch (Exception $e) {
        $error = "Error updating permissions: " . $e->getMessage();
    }
}

// Get vendor_id from user record
$vendorId = $user['vendor_id'];

// Get current permissions using user_id (not vendor_id)
$currentPermissions = [];
if ($vendorId) {
    $currentPermissions = $permissionModel->getUserPermissions($userId);
}

// Define vendor menu structure
function getVendorMenuStructure() {
    return [
        [
            'id' => 'dashboard',
            'title' => 'Dashboard',
            'url' => '/vendor/',
            'icon' => 'dashboard',
            'permission_key' => null, // Always visible
            'description' => 'Main dashboard with overview stats',
            'always_visible' => true
        ],
        [
            'id' => 'sites',
            'title' => 'Sites',
            'url' => null,
            'icon' => 'building',
            'permission_key' => null,
            'description' => 'Access to sites, surveys, and installations',
            'is_parent' => true,
            'children' => [
                [
                    'id' => 'my_sites',
                    'title' => 'My Sites',
                    'url' => '/vendor/sites/',
                    'permission_key' => 'view_my_sites'
                ],
                [
                    'id' => 'site_surveys',
                    'title' => 'Site Surveys',
                    'url' => '/vendor/surveys.php',
                    'permission_key' => 'view_site_surveys'
                ],
                [
                    'id' => 'installations',
                    'title' => 'Installations',
                    'url' => '/vendor/installations.php',
                    'permission_key' => 'view_installations'
                ]
            ]
        ],

        [
            'id' => 'inventory',
            'title' => 'Inventory',
            'url' => null,
            'icon' => 'warehouse',
            'permission_key' => null,
            'description' => 'Access to inventory management features',
            'is_parent' => true,
            'children' => [
                [
                    'id' => 'inventory_overview',
                    'title' => 'Inventory Overview',
                    'url' => '/vendor/inventory/',
                    'permission_key' => 'view_inventory_overview'
                ],
                [
                    'id' => 'material_requests',
                    'title' => 'Material Requests',
                    'url' => '/vendor/material-requests-list.php',
                    'permission_key' => 'view_material_requests'
                ],
                [
                    'id' => 'material_received',
                    'title' => 'Material Received',
                    'url' => '/vendor/material-received.php',
                    'permission_key' => 'view_material_received'
                ],
                [
                    'id' => 'material_dispatches',
                    'title' => 'Material Dispatches',
                    'url' => '/vendor/material-dispatches.php',
                    'permission_key' => 'view_material_dispatches'
                ]
            ]
        ],

    ];
}

$allVendorMenus = getVendorMenuStructure();

$title = 'Vendor Menu Permissions - ' . htmlspecialchars($user['username']);
ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Vendor Menu Permissions</h1>
        <p class="mt-2 text-sm text-gray-700">
            Manage vendor menu access for <strong><?php echo htmlspecialchars($user['username']); ?></strong>
            (<?php echo htmlspecialchars($user['role']); ?>)
        </p>
    </div>
    <div class="flex space-x-3">
        <?php if ($user['role'] !== VENDOR_ROLE): ?>
        <a href="<?php echo BASE_URL; ?>/admin/users/menu-permissions.php?user_id=<?php echo $userId; ?>" class="btn btn-secondary">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" clip-rule="evenodd"></path>
            </svg>
            Admin Menu Permissions
        </a>
        <?php endif; ?>
        <a href="<?php echo BASE_URL; ?>/admin/users/" class="btn btn-secondary">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
            </svg>
            Back to Users
        </a>
    </div>
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

<?php if (!$vendorId): ?>
<div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded mb-4">
    <div class="flex items-center">
        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
        </svg>
        <div>
            <strong>No Vendor Assigned</strong>
            <p class="text-sm mt-1">This user is not assigned to any vendor. Please assign a vendor to this user in the users table before setting permissions.</p>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST">
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Select Vendor Menu Items to Grant Access</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Only selected menu items will be visible to this vendor user in their portal. Parent menus are automatically included when child menus are selected.
                </p>
            </div>
            
            <div class="space-y-4">
                <?php foreach ($allVendorMenus as $menu): 
                    $hasChildren = isset($menu['children']) && count($menu['children']) > 0;
                    $isAlwaysVisible = isset($menu['always_visible']) && $menu['always_visible'];
                    $permissionKey = $menu['permission_key'] ?? null;
                    $isChecked = $permissionKey && isset($currentPermissions[$permissionKey]) && $currentPermissions[$permissionKey];
                    
                    // Get icon SVG
                    $iconSvg = getMenuIcon($menu['icon'] ?? 'default');
                ?>
                
                <div class="border border-gray-200 rounded-lg p-4 <?php echo $isAlwaysVisible ? 'bg-gray-50' : ($isChecked ? 'bg-blue-50 border-blue-300' : ''); ?>">
                    <label class="flex items-start <?php echo $isAlwaysVisible ? 'cursor-not-allowed' : 'cursor-pointer'; ?>">
                        <?php if (!$isAlwaysVisible && $permissionKey): ?>
                        <input type="checkbox" 
                               name="permissions[]" 
                               value="<?php echo $permissionKey; ?>"
                               class="parent-checkbox mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                               data-menu-id="<?php echo $menu['id']; ?>"
                               <?php echo $isChecked ? 'checked' : ''; ?>>
                        <?php else: ?>
                        <input type="checkbox" 
                               class="mt-1 rounded border-gray-300"
                               checked 
                               disabled>
                        <?php endif; ?>
                        
                        <div class="ml-3 flex-1">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2 <?php echo $isAlwaysVisible ? 'text-gray-400' : ($isChecked ? 'text-blue-600' : 'text-gray-400'); ?>" fill="currentColor" viewBox="0 0 20 20">
                                    <?php echo $iconSvg; ?>
                                </svg>
                                <span class="text-base font-medium <?php echo $isAlwaysVisible ? 'text-gray-600' : ($isChecked ? 'text-blue-900' : 'text-gray-900'); ?>">
                                    <?php echo htmlspecialchars($menu['title']); ?>
                                </span>
                                <?php if ($menu['url']): ?>
                                    <span class="ml-2 text-sm text-gray-500">(<?php echo htmlspecialchars($menu['url']); ?>)</span>
                                <?php endif; ?>
                                <?php if ($isAlwaysVisible): ?>
                                    <span class="ml-2 px-2 py-1 text-xs font-medium bg-gray-200 text-gray-700 rounded">Always Visible</span>
                                <?php endif; ?>
                            </div>
                            <?php if (isset($menu['description'])): ?>
                                <p class="mt-1 text-sm <?php echo $isAlwaysVisible ? 'text-gray-500' : ($isChecked ? 'text-blue-700' : 'text-gray-500'); ?>">
                                    <?php echo htmlspecialchars($menu['description']); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </label>
                    
                    <?php if ($hasChildren): ?>
                    <div class="ml-8 mt-3 space-y-2">
                        <?php foreach ($menu['children'] as $child): 
                            $childPermissionKey = $child['permission_key'] ?? null;
                            $childIsChecked = $childPermissionKey && isset($currentPermissions[$childPermissionKey]) && $currentPermissions[$childPermissionKey];
                        ?>
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   name="permissions[]" 
                                   value="<?php echo $childPermissionKey; ?>"
                                   class="child-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                   data-parent="<?php echo $menu['id']; ?>"
                                   <?php echo $childIsChecked ? 'checked' : ''; ?>>
                            <span class="ml-3 text-sm text-gray-700">
                                <?php echo htmlspecialchars($child['title']); ?>
                            </span>
                            <?php if ($child['url']): ?>
                                <span class="ml-2 text-xs text-gray-500">(<?php echo htmlspecialchars($child['url']); ?>)</span>
                            <?php endif; ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php endforeach; ?>
            </div>
            
            <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex">
                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <h4 class="text-sm font-medium text-blue-800">About Vendor Menu Permissions</h4>
                        <p class="mt-1 text-sm text-blue-700">
                            These permissions control what menu items appear in the vendor portal sidebar. 
                            The Dashboard is always visible. When you check a parent menu, all its sub-items will also be granted access.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end space-x-3">
                <a href="<?php echo BASE_URL; ?>/admin/users/" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Permissions</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle parent checkbox changes
    document.querySelectorAll('.parent-checkbox').forEach(function(parentCheckbox) {
        parentCheckbox.addEventListener('change', function() {
            const menuId = this.dataset.menuId;
            const childCheckboxes = document.querySelectorAll('.child-checkbox[data-parent="' + menuId + '"]');
            const container = this.closest('.border');
            
            childCheckboxes.forEach(function(childCheckbox) {
                childCheckbox.checked = parentCheckbox.checked;
            });
            
            // Update visual styling
            updateContainerStyle(container, this.checked);
        });
    });
    
    // Handle child checkbox changes
    document.querySelectorAll('.child-checkbox').forEach(function(childCheckbox) {
        childCheckbox.addEventListener('change', function() {
            const parentMenuId = this.dataset.parent;
            const parentCheckbox = document.querySelector('.parent-checkbox[data-menu-id="' + parentMenuId + '"]');
            const siblingCheckboxes = document.querySelectorAll('.child-checkbox[data-parent="' + parentMenuId + '"]');
            
            // Check if any sibling is checked
            let anyChecked = false;
            siblingCheckboxes.forEach(function(sibling) {
                if (sibling.checked) {
                    anyChecked = true;
                }
            });
            
            // Auto-check parent if any child is checked
            if (parentCheckbox) {
                parentCheckbox.checked = anyChecked;
                const container = parentCheckbox.closest('.border');
                updateContainerStyle(container, anyChecked);
            }
        });
    });
    
    // Initial check for parent checkboxes based on children
    document.querySelectorAll('.parent-checkbox').forEach(function(parentCheckbox) {
        const menuId = parentCheckbox.dataset.menuId;
        const childCheckboxes = document.querySelectorAll('.child-checkbox[data-parent="' + menuId + '"]');
        
        let anyChildChecked = false;
        childCheckboxes.forEach(function(childCheckbox) {
            if (childCheckbox.checked) {
                anyChildChecked = true;
            }
        });
        
        if (anyChildChecked && !parentCheckbox.checked) {
            parentCheckbox.checked = true;
            const container = parentCheckbox.closest('.border');
            updateContainerStyle(container, true);
        }
    });
    
    // Function to update container styling
    function updateContainerStyle(container, isChecked) {
        const icon = container.querySelector('svg');
        const title = container.querySelector('.text-base');
        const description = container.querySelector('.text-sm');
        
        if (isChecked) {
            container.classList.add('bg-blue-50', 'border-blue-300');
            container.classList.remove('bg-white');
            if (icon) {
                icon.classList.add('text-blue-600');
                icon.classList.remove('text-gray-400');
            }
            if (title) {
                title.classList.add('text-blue-900');
                title.classList.remove('text-gray-900');
            }
            if (description) {
                description.classList.add('text-blue-700');
                description.classList.remove('text-gray-500');
            }
        } else {
            container.classList.remove('bg-blue-50', 'border-blue-300');
            container.classList.add('bg-white');
            if (icon) {
                icon.classList.remove('text-blue-600');
                icon.classList.add('text-gray-400');
            }
            if (title) {
                title.classList.remove('text-blue-900');
                title.classList.add('text-gray-900');
            }
            if (description) {
                description.classList.remove('text-blue-700');
                description.classList.add('text-gray-500');
            }
        }
    }
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../includes/admin_layout.php';

// Helper function to get menu icons
function getMenuIcon($iconName) {
    $icons = [
        'dashboard' => '<path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>',
        'building' => '<path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm0 2h12v8H4V6z" clip-rule="evenodd"></path>',
        'database' => '<path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" clip-rule="evenodd"></path>',
        'warehouse' => '<path fill-rule="evenodd" d="M10 2L3 7v11a1 1 0 001 1h12a1 1 0 001-1V7l-7-5zM8 15a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" clip-rule="evenodd"></path>',
        'chart' => '<path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>',
        'default' => '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>'
    ];
    
    return $icons[$iconName] ?? $icons['default'];
}
?>
