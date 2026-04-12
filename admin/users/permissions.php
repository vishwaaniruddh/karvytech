<?php
require_once __DIR__ . '/../../controllers/PermissionsController.php';

$controller = new PermissionsController();

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
$title = 'Permissions Management';
ob_start();
?>

<div class="mb-4">
    <div class="flex justify-between items-center gap-3">
        <div class="flex items-center gap-2">
            <button onclick="exportPermissionsData()" class="px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50">
                Export
            </button>
        </div>
        <p class="text-xs text-gray-500">View and explore system permissions and capability definitions</p>
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
                    <input type="text" id="searchInput" placeholder="Search permissions..." class="block w-full pl-8 pr-3 py-1.5 text-xs border border-gray-300 rounded leading-5 bg-white placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" value="<?php echo htmlspecialchars($data['search'] ?? ''); ?>" onkeyup="debounce(filterPermissionsTable, 500)()">
                </div>
            </div>
            <div class="flex gap-2">
                <select id="moduleFilter" class="text-xs border border-gray-300 rounded py-1.5 px-2 focus:outline-none focus:ring-1 focus:ring-blue-500" onchange="filterPermissionsTable()">
                    <option value="">All Modules</option>
                    <?php foreach ($data['modules'] as $module): ?>
                        <option value="<?php echo $module['id']; ?>" <?php echo (isset($data['module_id']) && $data['module_id'] == $module['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($module['display_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Permissions Table -->
<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 text-gray-500 text-[10px] uppercase tracking-wider font-semibold">
                <tr>
                    <th class="px-4 py-2 text-left w-12">#</th>
                    <th class="px-4 py-2 text-left">Module</th>
                    <th class="px-4 py-2 text-left">Internal Name</th>
                    <th class="px-4 py-2 text-left">Display Name</th>
                    <th class="px-4 py-2 text-left">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100 text-xs">
                <?php if (empty($data['permissions'])): ?>
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">No permissions found</td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $startSn = ($data['pagination']['current_page'] - 1) * $data['pagination']['limit'] + 1;
                    foreach ($data['permissions'] as $index => $permission): 
                    ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-2 text-gray-400 font-mono"><?php echo $startSn + $index; ?></td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 font-medium">
                                    <?php echo htmlspecialchars($permission['module_display_name']); ?>
                                </span>
                            </td>
                            <td class="px-4 py-2 font-medium text-gray-900"><?php echo htmlspecialchars($permission['name']); ?></td>
                            <td class="px-4 py-2 text-gray-600"><?php echo htmlspecialchars($permission['display_name']); ?></td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-medium <?php 
                                    if ($permission['status'] === 'active') echo 'bg-green-100 text-green-700';
                                    elseif ($permission['status'] === 'pending_deletion') echo 'bg-orange-100 text-orange-700';
                                    else echo 'bg-gray-100 text-gray-700';
                                ?>">
                                    <?php echo ucwords(str_replace('_', ' ', $permission['status'])); ?>
                                </span>
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
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($data['search'] ?? ''); ?>&module_id=<?php echo urlencode($data['module_id'] ?? ''); ?>" 
                       class="px-2.5 py-1 text-xs rounded border <?php echo $i === $data['pagination']['current_page'] ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function filterPermissionsTable() {
    const search = document.getElementById('searchInput').value;
    const moduleId = document.getElementById('moduleFilter').value;
    const url = new URL(window.location);
    url.searchParams.set('search', search);
    url.searchParams.set('module_id', moduleId);
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

function exportPermissionsData() {
    const search = document.getElementById('searchInput').value;
    const moduleId = document.getElementById('moduleFilter').value;
    window.location.href = `permissions_export.php?search=${encodeURIComponent(search)}&module_id=${moduleId}`;
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
