<?php
require_once __DIR__ . '/../../config/auth.php';
// constants.php is already included by auth.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Menu.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if (!$userId) {
    header('Location: ' . BASE_URL . '/admin/users/');
    exit;
}

$userModel = new User();
$menuModel = new Menu();

// Get user details directly from model
$user = $userModel->find($userId);
if (!$user) {
    header('Location: ' . BASE_URL . '/admin/users/');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $permissions = $_POST['permissions'] ?? [];
    
    try {
        $db = Database::getInstance()->getConnection();
        
        // Clear existing permissions
        $stmt = $db->prepare("DELETE FROM user_menu_permissions WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        // Add new permissions
        foreach ($permissions as $menuId) {
            $menuModel->setUserPermission($userId, $menuId, true);
        }
        
        $success = "Menu permissions updated successfully!";
    } catch (Exception $e) {
        $error = "Error updating permissions: " . $e->getMessage();
    }
}

// Get all menu items and user's current permissions
$allMenus = $menuModel->getAllMenuItems();
$userPermissions = $menuModel->getUserPermissions($userId);
$userMenuIds = array_column($userPermissions, 'menu_item_id');

$title = 'Menu Permissions - ' . htmlspecialchars($user['username']);
ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Menu Permissions</h1>
        <p class="mt-2 text-sm text-gray-700">
            Manage menu access for <strong><?php echo htmlspecialchars($user['username']); ?></strong>
            (<?php echo htmlspecialchars($user['role']); ?>)
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

<div class="card">
    <div class="card-body">
        <form method="POST">
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Select Menu Items to Grant Access</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Only selected menu items will be visible to this user. Parent menus are automatically included when child menus are selected.
                </p>
            </div>
            
            <div class="space-y-4">
                <?php
                $currentParent = null;
                foreach ($allMenus as $menu):
                    $isChecked = in_array($menu['id'], $userMenuIds);
                    $isParent = $menu['parent_id'] === null;
                    
                    if ($isParent && $currentParent !== null): ?>
                        </div> <!-- Close previous parent's children -->
                    <?php endif;
                    
                    if ($isParent): 
                        $currentParent = $menu['id']; ?>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="permissions[]" 
                                       value="<?php echo $menu['id']; ?>"
                                       class="parent-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                       <?php echo $isChecked ? 'checked' : ''; ?>>
                                <span class="ml-3 text-base font-medium text-gray-900">
                                    <?php echo htmlspecialchars($menu['title']); ?>
                                </span>
                                <?php if ($menu['url']): ?>
                                    <span class="ml-2 text-sm text-gray-500">(<?php echo htmlspecialchars($menu['url']); ?>)</span>
                                <?php endif; ?>
                            </label>
                            
                            <div class="ml-6 mt-3 space-y-2">
                    <?php else: ?>
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                           name="permissions[]" 
                                           value="<?php echo $menu['id']; ?>"
                                           class="child-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                           data-parent="<?php echo $menu['parent_id']; ?>"
                                           <?php echo $isChecked ? 'checked' : ''; ?>>
                                    <span class="ml-3 text-sm text-gray-700">
                                        <?php echo htmlspecialchars($menu['title']); ?>
                                    </span>
                                    <?php if ($menu['url']): ?>
                                        <span class="ml-2 text-xs text-gray-500">(<?php echo htmlspecialchars($menu['url']); ?>)</span>
                                    <?php endif; ?>
                                </label>
                    <?php endif;
                endforeach; ?>
                            </div> <!-- Close last parent's children -->
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
            const parentId = this.value;
            const childCheckboxes = document.querySelectorAll('.child-checkbox[data-parent="' + parentId + '"]');
            
            childCheckboxes.forEach(function(childCheckbox) {
                childCheckbox.checked = parentCheckbox.checked;
            });
        });
    });
    
    // Handle child checkbox changes
    document.querySelectorAll('.child-checkbox').forEach(function(childCheckbox) {
        childCheckbox.addEventListener('change', function() {
            const parentId = this.dataset.parent;
            const parentCheckbox = document.querySelector('.parent-checkbox[value="' + parentId + '"]');
            const siblingCheckboxes = document.querySelectorAll('.child-checkbox[data-parent="' + parentId + '"]');
            
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
            }
        });
    });
    
    // Initial check for parent checkboxes based on children
    document.querySelectorAll('.parent-checkbox').forEach(function(parentCheckbox) {
        const parentId = parentCheckbox.value;
        const childCheckboxes = document.querySelectorAll('.child-checkbox[data-parent="' + parentId + '"]');
        
        let anyChildChecked = false;
        childCheckboxes.forEach(function(childCheckbox) {
            if (childCheckbox.checked) {
                anyChildChecked = true;
            }
        });
        
        if (anyChildChecked && !parentCheckbox.checked) {
            parentCheckbox.checked = true;
        }
    });
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../includes/admin_layout.php';
?>