<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/Site.php';

// Require superadmin authentication
Auth::requireRole('superadmin');

$siteModel = new Site();

// Handle search and pagination
$search = $_GET['search'] ?? '';
$page = (int)($_GET['page'] ?? 1);

$result = $siteModel->getTrashed($page, 20, $search);

$title = 'Deleted Sites - Trash';
ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">🗑️ Deleted Sites (Trash)</h1>
        <p class="mt-2 text-sm text-gray-700">Manage soft-deleted sites - Restore or permanently delete</p>
    </div>
    <div class="flex space-x-2">
        <a href="index.php" class="btn btn-secondary">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
            </svg>
            Back to Sites
        </a>
    </div>
</div>

<!-- Stats -->
<div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
    <div class="flex items-center">
        <svg class="w-6 h-6 text-red-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
        </svg>
        <div>
            <h3 class="text-sm font-semibold text-red-900">Superadmin Only Area</h3>
            <p class="text-xs text-red-700 mt-1">
                <strong><?php echo $result['total']; ?> sites</strong> in trash. 
                You can restore them or permanently delete them. 
                <span class="font-bold">Permanent deletion cannot be undone!</span>
            </p>
        </div>
    </div>
</div>

<!-- Search -->
<div class="card mb-6">
    <div class="card-body">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <input type="text" id="searchInput" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Search deleted sites..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
    </div>
</div>

<!-- Deleted Sites Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">#</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Site Details</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Location</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Customer</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Deleted Info</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php 
                    if (empty($result['records'])):
                    ?>
                        <tr>
                            <td colspan="6" class="px-3 py-8 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                                <p class="text-sm">No deleted sites found</p>
                            </td>
                        </tr>
                    <?php 
                    else:
                        $serialNo = (($result['page'] - 1) * $result['limit']) + 1;
                        foreach ($result['records'] as $site):
                    ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-3 py-2 whitespace-nowrap text-xs font-medium text-gray-600"><?php echo $serialNo++; ?></td>
                            <td class="px-3 py-2 whitespace-nowrap">
                                <div class="flex items-center space-x-2">
                                    <button onclick="restoreSite(<?php echo $site['id']; ?>)" class="btn btn-sm btn-success" title="Restore Site">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                    <button onclick="permanentDeleteSite(<?php echo $site['id']; ?>)" class="btn btn-sm btn-danger" title="Permanent Delete">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                            <td class="px-3 py-2">
                                <div>
                                    <div class="text-xs font-semibold text-gray-900"><?php echo htmlspecialchars($site['site_id']); ?></div>
                                    <?php if ($site['store_id']): ?>
                                        <div class="text-[10px] text-gray-500">Store: <?php echo htmlspecialchars($site['store_id']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-3 py-2">
                                <div class="text-xs text-gray-900"><?php echo htmlspecialchars($site['city']); ?>, <?php echo htmlspecialchars($site['state']); ?></div>
                            </td>
                            <td class="px-3 py-2">
                                <div class="text-xs text-gray-900"><?php echo htmlspecialchars($site['customer_name'] ?: 'N/A'); ?></div>
                            </td>
                            <td class="px-3 py-2">
                                <div class="text-xs text-red-600 font-medium">
                                    <?php echo date('M d, Y h:i A', strtotime($site['deleted_at'])); ?>
                                </div>
                                <div class="text-[10px] text-gray-500">
                                    By: <?php echo htmlspecialchars($site['deleted_by_name'] ?: 'Unknown'); ?>
                                </div>
                            </td>
                        </tr>
                    <?php 
                        endforeach;
                    endif;
                    ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($result['pages'] > 1): ?>
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 rounded-b-lg">
            <div class="flex-1 flex justify-between sm:hidden">
                <?php if ($result['page'] > 1): ?>
                    <a href="?page=<?php echo $result['page'] - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                       class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Previous
                    </a>
                <?php endif; ?>
                <?php if ($result['page'] < $result['pages']): ?>
                    <a href="?page=<?php echo $result['page'] + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                       class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Next
                    </a>
                <?php endif; ?>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing
                        <span class="font-medium"><?php echo (($result['page'] - 1) * $result['limit']) + 1; ?></span>
                        to
                        <span class="font-medium"><?php echo min($result['page'] * $result['limit'], $result['total']); ?></span>
                        of
                        <span class="font-medium"><?php echo $result['total']; ?></span>
                        results
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                        <?php
                        $current = $result['page'];
                        $total = $result['pages'];
                        $search_param = !empty($search) ? '&search=' . urlencode($search) : '';
                        
                        // Previous button
                        if ($current > 1): ?>
                            <a href="?page=<?php echo $current - 1 . $search_param; ?>" 
                               class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </a>
                        <?php else: ?>
                            <span class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400 cursor-not-allowed">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </span>
                        <?php endif;
                        
                        // Page numbers
                        for ($i = 1; $i <= $total; $i++) {
                            if ($i == $current) {
                                echo '<span class="relative inline-flex items-center px-4 py-2 border border-blue-500 bg-blue-50 text-sm font-medium text-blue-600 z-10">' . $i . '</span>';
                            } else {
                                echo '<a href="?page=' . $i . $search_param . '" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">' . $i . '</a>';
                            }
                        }
                        
                        // Next button
                        if ($current < $total): ?>
                            <a href="?page=<?php echo $current + 1 . $search_param; ?>" 
                               class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </a>
                        <?php else: ?>
                            <span class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400 cursor-not-allowed">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </span>
                        <?php endif; ?>
                    </nav>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Search functionality
document.getElementById('searchInput').addEventListener('keyup', debounce(function() {
    const searchTerm = this.value;
    const url = new URL(window.location);
    
    if (searchTerm) url.searchParams.set('search', searchTerm);
    else url.searchParams.delete('search');
    
    url.searchParams.delete('page');
    window.location.href = url.toString();
}, 500));

function restoreSite(id) {
    showConfirmDialog(
        'Restore Site',
        'Are you sure you want to restore this site? It will be moved back to the active sites list.',
        function() {
            fetch(`restore.php?id=${id}`, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Failed to restore site', 'error');
            });
        }
    );
}

function permanentDeleteSite(id) {
    showConfirmDialog(
        '⚠️ PERMANENT DELETE',
        'This will PERMANENTLY delete this site and ALL related data (surveys, delegations, installations, materials). This action CANNOT be undone! Are you absolutely sure?',
        function() {
            fetch(`permanent-delete.php?id=${id}`, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Failed to permanently delete site', 'error');
            });
        }
    );
}

function showConfirmDialog(title, message, onConfirm, onCancel) {
    const overlay = document.createElement('div');
    overlay.className = 'fixed inset-0 bg-black bg-opacity-50 z-[9998] flex items-center justify-center';
    overlay.style.animation = 'fadeIn 0.2s ease-out';
    
    const dialog = document.createElement('div');
    dialog.className = 'bg-white rounded-lg shadow-xl max-w-md w-full mx-4 transform transition-all';
    dialog.style.animation = 'slideIn 0.3s ease-out';
    dialog.innerHTML = `
        <div class="p-6">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">${title}</h3>
                </div>
            </div>
            <p class="text-sm text-gray-600 mb-6">${message}</p>
            <div class="flex justify-end space-x-3">
                <button id="cancelBtn" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    Cancel
                </button>
                <button id="confirmBtn" class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                    Confirm
                </button>
            </div>
        </div>
    `;
    
    overlay.appendChild(dialog);
    document.body.appendChild(overlay);
    
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideIn {
            from { transform: scale(0.95) translateY(-10px); opacity: 0; }
            to { transform: scale(1) translateY(0); opacity: 1; }
        }
    `;
    document.head.appendChild(style);
    
    const confirmBtn = dialog.querySelector('#confirmBtn');
    const cancelBtn = dialog.querySelector('#cancelBtn');
    
    const closeDialog = () => {
        overlay.style.animation = 'fadeIn 0.2s ease-out reverse';
        setTimeout(() => {
            document.body.removeChild(overlay);
            document.head.removeChild(style);
        }, 200);
    };
    
    confirmBtn.addEventListener('click', () => {
        closeDialog();
        if (onConfirm) onConfirm();
    });
    
    cancelBtn.addEventListener('click', () => {
        closeDialog();
        if (onCancel) onCancel();
    });
    
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            closeDialog();
            if (onCancel) onCancel();
        }
    });
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>
