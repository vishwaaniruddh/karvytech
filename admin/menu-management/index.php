<?php
require_once __DIR__ . '/../../models/Menu.php';

Auth::requireRole(ADMIN_ROLE);

$menuModel = new Menu();
$menuItems = $menuModel->getAllMenuItems();

$title = 'Menu Management';
ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Menu Management</h1>
        <p class="mt-2 text-sm text-gray-700">Manage system menus and user permissions</p>
    </div>
    <button onclick="openModal('createMenuModal')" class="btn btn-primary">
        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
        </svg>
        Add Menu Item
    </button>
</div>

<!-- Menu Items Table -->
<div class="card">
    <div class="card-body">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Menu Structure</th>
                        <th>URL</th>
                        <th>Icon</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($menuItems as $item): ?>
                    <tr>
                        <td>
                            <div class="text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($item['full_path']); ?>
                            </div>
                        </td>
                        <td class="text-sm text-gray-500">
                            <?php echo $item['url'] ? htmlspecialchars($item['url']) : '-'; ?>
                        </td>
                        <td class="text-sm text-gray-500">
                            <?php echo $item['icon'] ? htmlspecialchars($item['icon']) : '-'; ?>
                        </td>
                        <td class="text-sm text-gray-500">
                            <?php echo $item['sort_order']; ?>
                        </td>
                        <td>
                            <span class="badge <?php echo $item['status'] === 'active' ? 'badge-success' : 'badge-danger'; ?>">
                                <?php echo ucfirst($item['status']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="flex items-center space-x-2">
                                <button onclick="editMenuItem(<?php echo $item['id']; ?>)" class="btn btn-sm btn-primary" title="Edit">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                                    </svg>
                                </button>
                                <button onclick="managePermissions(<?php echo $item['id']; ?>)" class="btn btn-sm btn-secondary" title="Permissions">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 8a6 6 0 01-7.743 5.743L10 14l-1 1-1 1H6v2H2v-4l4.257-4.257A6 6 0 1118 8zm-6-4a1 1 0 100 2 2 2 0 012 2 1 1 0 102 0 4 4 0 00-4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                                <button onclick="deleteMenuItem(<?php echo $item['id']; ?>)" class="btn btn-sm btn-danger" title="Delete">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"></path>
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 012 0v4a1 1 0 11-2 0V7zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V7a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function editMenuItem(id) {
    showAlert('Menu editing functionality will be implemented in the next phase', 'info');
}

function managePermissions(id) {
    showAlert('Permission management functionality will be implemented in the next phase', 'info');
}

function deleteMenuItem(id) {
    confirmAction('Are you sure you want to delete this menu item?', function() {
        showAlert('Menu deletion functionality will be implemented in the next phase', 'info');
    });
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>