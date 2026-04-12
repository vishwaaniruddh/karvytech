<?php
require_once __DIR__ . '/../../controllers/RolesController.php';

$controller = new RolesController();

// Handle AJAX requests
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'store') {
        echo $controller->store();
        exit;
    } elseif ($_GET['action'] === 'update' && isset($_GET['id'])) {
        echo $controller->update($_GET['id']);
        exit;
    } elseif ($_GET['action'] === 'delete' && isset($_GET['id'])) {
        echo $controller->delete($_GET['id']);
        exit;
    }
}

$data = $controller->index();
$title = 'Roles Management';
ob_start();
?>

<div class="mb-4">
    <div class="flex justify-between items-center gap-3">
        <div class="flex items-center gap-2">
            <button onclick="exportRolesData()" class="px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50">
                Export
            </button>
            <button onclick="resetRoleForm(); openModal('roleModal')" class="px-3 py-1.5 text-xs font-medium text-white bg-blue-600 rounded hover:bg-blue-700">
                Add Role
            </button>
        </div>
        <p class="text-xs text-gray-500">Manage system roles and their definitions</p>
    </div>
</div>

<!-- Search and Filters -->
<div class="card mb-4">
    <div class="card-body p-3">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <input type="text" id="searchInput" placeholder="Search roles..." class="block w-full pl-8 pr-3 py-1.5 text-xs border border-gray-300 rounded leading-5 bg-white placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" value="<?php echo htmlspecialchars($data['search']); ?>" onkeyup="debounce(filterRolesTable, 500)()">
                </div>
            </div>
            <div class="flex gap-2">
                <select id="statusFilter" class="text-xs border border-gray-300 rounded py-1.5 px-2 focus:outline-none focus:ring-1 focus:ring-blue-500" onchange="filterRolesTable()">
                    <option value="">All Status</option>
                    <option value="active" <?php echo $data['status_filter'] === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $data['status_filter'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Roles Table -->
<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 text-gray-500 text-[10px] uppercase tracking-wider font-semibold">
                <tr>
                    <th class="px-4 py-2 text-left w-12">#</th>
                    <th class="px-4 py-2 text-left">Role Name</th>
                    <th class="px-4 py-2 text-left">Display Name</th>
                    <th class="px-4 py-2 text-left">Description</th>
                    <th class="px-4 py-2 text-left">Status</th>
                    <th class="px-4 py-2 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100 text-xs">
                <?php if (empty($data['roles'])): ?>
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">No roles found</td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $startSn = ($data['pagination']['current_page'] - 1) * $data['pagination']['limit'] + 1;
                    foreach ($data['roles'] as $index => $role): 
                    ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-2 text-gray-400 font-mono"><?php echo $startSn + $index; ?></td>
                            <td class="px-4 py-2 font-medium text-gray-900"><?php echo htmlspecialchars($role['name']); ?></td>
                            <td class="px-4 py-2 text-gray-600"><?php echo htmlspecialchars($role['display_name']); ?></td>
                            <td class="px-4 py-2 text-gray-500 max-w-xs truncate" title="<?php echo htmlspecialchars($role['description'] ?? ''); ?>">
                                <?php echo htmlspecialchars($role['description'] ?? '-'); ?>
                            </td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-medium <?php 
                                    if ($role['status'] === 'active') echo 'bg-green-100 text-green-700';
                                    elseif ($role['status'] === 'pending_deletion') echo 'bg-orange-100 text-orange-700';
                                    else echo 'bg-gray-100 text-gray-700';
                                ?>">
                                    <?php echo ucwords(str_replace('_', ' ', $role['status'])); ?>
                                </span>
                            </td>
                            <td class="px-4 py-2 text-right space-x-2 whitespace-nowrap">
                                <?php if ($role['status'] === 'pending_deletion'): ?>
                                    <span class="text-gray-400 italic">Deletion Pending</span>
                                <?php else: ?>
                                    <button onclick="editRole(<?php echo htmlspecialchars(json_encode($role)); ?>)" class="text-blue-600 hover:text-blue-800">Edit</button>
                                    <button onclick="confirmRequestDelete(<?php echo $role['id']; ?>, '<?php echo htmlspecialchars($role['display_name']); ?>')" class="text-red-600 hover:text-red-800">Delete</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($data['pagination']['total_pages'] > 1): ?>
        <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
            <div class="text-xs text-gray-600">
                Showing <?php echo ($data['pagination']['current_page'] - 1) * $data['pagination']['limit'] + 1; ?> to 
                <?php echo min($data['pagination']['current_page'] * $data['pagination']['limit'], $data['pagination']['total_records']); ?> of 
                <?php echo $data['pagination']['total_records']; ?> entries
            </div>
            <div class="flex gap-1">
                <?php for ($i = 1; $i <= $data['pagination']['total_pages']; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($data['search']); ?>&status=<?php echo urlencode($data['status_filter']); ?>" 
                       class="px-2.5 py-1 text-xs rounded border <?php echo $i === $data['pagination']['current_page'] ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Role Modal -->
<div id="roleModal" class="modal">
    <div class="modal-content max-w-md">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Add New Role</h3>
            <button type="button" class="modal-close" onclick="closeModal('roleModal')">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form id="roleForm" onsubmit="handleRoleSubmit(event)">
            <input type="hidden" id="roleId" name="id">
            <div class="modal-body space-y-4">
                <div id="nameGroup">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Role Name (Technical ID)</label>
                    <input type="text" id="roleName" name="name" class="w-full px-3 py-2 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 outline-none" placeholder="e.g. superadmin" required>
                    <p class="text-[10px] text-gray-500 mt-1">Cannot be changed later</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Display Name</label>
                    <input type="text" id="roleDisplayName" name="display_name" class="w-full px-3 py-2 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 outline-none" placeholder="e.g. Super Administrator" required>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="roleDescription" name="description" rows="3" class="w-full px-3 py-2 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 outline-none" placeholder="Describe what this role does..."></textarea>
                </div>
                <div id="statusGroup" class="hidden">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                    <select id="roleStatus" name="status" class="w-full px-3 py-2 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 outline-none">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('roleModal')" class="px-4 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 text-xs font-medium text-white bg-blue-600 rounded hover:bg-blue-700">Save Role</button>
            </div>
        </form>
    </div>
</div>

<script>
function filterRolesTable() {
    const search = document.getElementById('searchInput').value;
    const status = document.getElementById('statusFilter').value;
    const url = new URL(window.location);
    url.searchParams.set('search', search);
    url.searchParams.set('status', status);
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

function resetRoleForm() {
    document.getElementById('roleForm').reset();
    document.getElementById('roleId').value = '';
    document.getElementById('modalTitle').innerText = 'Add New Role';
    document.getElementById('nameGroup').classList.remove('hidden');
    document.getElementById('roleName').disabled = false;
    document.getElementById('statusGroup').classList.add('hidden');
}

function editRole(role) {
    resetRoleForm();
    document.getElementById('roleId').value = role.id;
    document.getElementById('roleName').value = role.name;
    document.getElementById('roleName').disabled = true;
    document.getElementById('roleDisplayName').value = role.display_name;
    document.getElementById('roleDescription').value = role.description;
    document.getElementById('roleStatus').value = role.status;
    
    document.getElementById('modalTitle').innerText = 'Edit Role: ' + role.display_name;
    document.getElementById('nameGroup').classList.add('hidden');
    document.getElementById('statusGroup').classList.remove('hidden');
    openModal('roleModal');
}

function handleRoleSubmit(e) {
    e.preventDefault();
    const roleId = document.getElementById('roleId').value;
    const action = roleId ? 'update' : 'store';
    const url = `roles.php?action=${action}${roleId ? '&id=' + roleId : ''}`;
    
    const formData = new FormData(e.target);
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred. Please try again.', 'error');
    });
}

async function confirmRequestDelete(id, name) {
    const confirmed = await showConfirm(
        'Request Role Deletion', 
        `Are you sure you want to request deletion for the role "${name}"? This action will require Superadmin approval before the role is permanently removed.`,
        {
            confirmText: 'Yes, Request Deletion',
            cancelText: 'Cancel',
            confirmType: 'danger'
        }
    );

    if (confirmed) {
        fetch(`roles.php?action=delete&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred. Please try again.', 'error');
        });
    }
}

function exportRolesData() {
    const search = document.getElementById('searchInput').value;
    const status = document.getElementById('statusFilter').value;
    window.location.href = `roles_export.php?search=${encodeURIComponent(search)}&status=${status}`;
}

function debounce(func, wait) {
    let timeout;
    return function() {
        clearTimeout(timeout);
        timeout = setTimeout(func, wait);
    };
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>
