<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/BoqMaster.php';
require_once __DIR__ . '/../../models/Customer.php';
require_once __DIR__ . '/../../models/BoqItem.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$boqMaster = new BoqMaster();
$customerModel = new Customer();
$boqItemModel = new BoqItem();

// Handle search and filters
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$page = (int)($_GET['page'] ?? 1);

$result = $boqMaster->getAllWithPagination($page, 20, $search, $status);
$customers = $customerModel->getActive(); // Use BaseMaster getActive()
$allMaterials = $boqItemModel->getActive(); // From BoqItem model

$title = 'BOQ Management';
ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">BOQ Management</h1>
        <p class="mt-2 text-sm text-gray-700">Create and manage customer-specific BOQ sets</p>
    </div>
    <div class="flex space-x-2">
        <a href="items.php" class="btn btn-secondary" title="Manage Materials Master">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
            Materials Master
        </a>
        <button onclick="openModal('createBoqModal')" class="btn btn-primary" title="Add New BOQ Set">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
            </svg>
            Add BOQ
        </button>
    </div>
</div>

<!-- Search and Filters -->
<div class="card mb-6">
    <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="lg:col-span-2">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <input type="text" id="searchInput" class="form-input pl-10" placeholder="Search by BOQ name or customer..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div>
                <select id="statusFilter" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- BOQ Sets Table -->
<div class="card">
    <div class="card-body">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="w-12 text-center">#</th>
                        <th>Actions</th>
                        <th>BOQ Name</th>
                        <th>Customer</th>
                        <th class="text-center">Items Count</th>
                        <th>Status</th>
                        <th>Created Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($result['records'])): ?>
                        <tr>
                            <td colspan="7" class="text-center py-8 text-gray-500">No BOQ sets found. Create your first one!</td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $serialNo = (($result['page'] - 1) * $result['limit']) + 1;
                        foreach ($result['records'] as $record): 
                        ?>
                        <tr>
                            <td class="text-center text-gray-400 font-mono text-xs"><?php echo $serialNo++; ?></td>
                            <td>
                                <div class="flex items-center space-x-2">
                                    <button onclick="viewBoqSet(<?php echo $record['id']; ?>)" class="btn btn-sm btn-secondary" title="View Details">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                    <button onclick="editBoq(<?php echo $record['id']; ?>)" class="btn btn-sm btn-primary" title="Edit">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                                        </svg>
                                    </button>
                                    <button onclick="deleteBoq(<?php echo $record['id']; ?>)" class="btn btn-sm btn-danger" title="Delete">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"></path>
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 012 0v4a1 1 0 11-2 0V7zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V7a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                            <td class="font-medium text-gray-900"><?php echo htmlspecialchars($record['boq_name']); ?></td>
                            <td><?php echo htmlspecialchars($record['customer_name']); ?></td>
                            <td class="text-center">
                                <span class="badge badge-info"><?php echo $record['item_count']; ?> Items</span>
                            </td>
                            <td>
                                <span class="badge <?php echo $record['status'] === 'active' ? 'badge-success' : 'badge-secondary'; ?>">
                                    <?php echo ucfirst($record['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars(ucfirst($record['created_by_name'] ?: 'System')); ?></span>
                                    <span class="text-[10px] text-gray-500"><?php echo date('M d, Y h:i A', strtotime($record['created_at'])); ?></span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($result['pages'] > 1): ?>
        <div class="pagination mt-4">
            <div class="pagination-info">
                Showing <?php echo (($result['page'] - 1) * $result['limit']) + 1; ?> to 
                <?php echo min($result['page'] * $result['limit'], $result['total']); ?> of 
                <?php echo $result['total']; ?> results
            </div>
            <div class="flex space-x-2">
                <?php for ($i = 1; $i <= $result['pages']; $i++): ?>
                    <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($status) ? '&status=' . urlencode($status) : ''; ?>" 
                       class="pagination-btn <?php echo $i === $result['page'] ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Create/Edit BOQ Modal -->
<div id="createBoqModal" class="modal">
    <div class="modal-content-large max-w-4xl">
        <div class="modal-header-fixed">
            <h3 class="modal-title" id="modalTitle">Add New BOQ Set</h3>
            <button type="button" class="modal-close" onclick="closeModal('createBoqModal')">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <form id="boqForm" action="save-master.php" method="POST">
            <input type="hidden" name="id" id="boq_id">
            <div class="modal-body-scrollable">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="form-group">
                        <label for="boq_name" class="form-label">BOQ Name *</label>
                        <input type="text" id="boq_name" name="boq_name" class="form-input" required placeholder="e.g., Standard Installation Package">
                    </div>
                    <div class="form-group">
                        <label for="customer_id" class="form-label">Customer *</label>
                        <select id="customer_id" name="customer_id" class="form-select" required>
                            <option value="">Select Customer</option>
                            <?php foreach ($customers as $customer): ?>
                                <option value="<?php echo $customer['id']; ?>"><?php echo htmlspecialchars($customer['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="status" class="form-label">Status</label>
                        <select id="status_field" name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-6">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Select Materials / Items</h4>
                        <div class="text-xs text-gray-500">
                            Check the materials to include in this BOQ
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-96 overflow-y-auto pr-2" id="materialsGrid">
                            <?php foreach ($allMaterials as $item): ?>
                            <label class="flex items-start p-3 bg-white border border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-all cursor-pointer group">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" name="materials[]" value="<?php echo $item['id']; ?>" class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                </div>
                                <div class="ml-3 text-sm">
                                    <span class="font-medium text-gray-900 block group-hover:text-blue-700"><?php echo htmlspecialchars($item['item_name']); ?></span>
                                    <span class="text-xs text-gray-500 font-mono"><?php echo htmlspecialchars($item['item_code']); ?></span>
                                    <span class="text-[10px] text-blue-600 block mt-0.5"><?php echo htmlspecialchars($item['category'] ?: 'Uncategorized'); ?></span>
                                </div>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer-fixed">
                <button type="button" onclick="closeModal('createBoqModal')" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary" id="saveButton">Create BOQ Set</button>
            </div>
        </form>
    </div>
</div>

<!-- View BOQ Modal -->
<div id="viewBoqModal" class="modal">
    <div class="modal-content-large max-w-2xl">
        <div class="modal-header">
            <h3 class="modal-title">BOQ Set Details</h3>
            <button type="button" class="modal-close" onclick="closeModal('viewBoqModal')">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="text-xs text-gray-500 uppercase font-bold">BOQ Name</label>
                    <p id="view_boq_name" class="text-sm font-medium text-gray-900"></p>
                </div>
                <div>
                    <label class="text-xs text-gray-500 uppercase font-bold">Customer</label>
                    <p id="view_customer_name" class="text-sm font-medium text-gray-900"></p>
                </div>
                <div>
                    <label class="text-xs text-gray-500 uppercase font-bold">Status</label>
                    <div id="view_status_badge"></div>
                </div>
                <div>
                    <label class="text-xs text-gray-500 uppercase font-bold">Created By</label>
                    <p id="view_created_by" class="text-sm text-gray-600"></p>
                </div>
            </div>
            
            <div class="border-t border-gray-100 pt-4">
                <h4 class="text-sm font-bold text-gray-700 mb-3">Included Materials</h4>
                <div class="max-h-64 overflow-y-auto" id="view_materials_list">
                    <!-- Loaded dynamically -->
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" onclick="closeModal('viewBoqModal')" class="btn btn-secondary">Close</button>
            <button type="button" id="editFromViewBtn" class="btn btn-primary">Edit BOQ</button>
        </div>
    </div>
</div>

<script>
// Search/Filter Logic
document.getElementById('searchInput').addEventListener('keyup', debounce(function() {
    applyFilters();
}, 500));

document.getElementById('statusFilter').addEventListener('change', applyFilters);

function applyFilters() {
    const searchTerm = document.getElementById('searchInput').value;
    const status = document.getElementById('statusFilter').value;
    const url = new URL(window.location);
    
    if (searchTerm) url.searchParams.set('search', searchTerm);
    else url.searchParams.delete('search');
    
    if (status) url.searchParams.set('status', status);
    else url.searchParams.delete('status');
    
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

// Form Submission
document.getElementById('boqForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const saveBtn = document.getElementById('saveButton');
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<svg class="animate-spin h-4 w-4 mr-2 inline-block" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Saving...';

    fetch('save-master.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeModal('createBoqModal');
            showAlert(data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert(data.message, 'error');
            saveBtn.disabled = false;
            saveBtn.innerText = document.getElementById('boq_id').value ? 'Update BOQ Set' : 'Create BOQ Set';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while saving', 'error');
        saveBtn.disabled = false;
    });
});

function viewBoqSet(id) {
    fetch(`get-master.php?id=${id}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const boq = data.boq;
            document.getElementById('view_boq_name').innerText = boq.boq_name;
            document.getElementById('view_customer_name').innerText = boq.customer_name;
            const creator = boq.created_by_name || 'System';
            document.getElementById('view_created_by').innerText = creator.charAt(0).toUpperCase() + creator.slice(1) + ' on ' + new Date(boq.created_at).toLocaleString();
            
            const badge = document.getElementById('view_status_badge');
            badge.innerHTML = `<span class="badge ${boq.status === 'active' ? 'badge-success' : 'badge-secondary'}">${boq.status.charAt(0).toUpperCase() + boq.status.slice(1)}</span>`;
            
            // Render materials list
            const list = document.getElementById('view_materials_list');
            if (data.item_details && data.item_details.length > 0) {
                list.innerHTML = `<ul class="divide-y divide-gray-100 border border-gray-100 rounded-lg">
                    ${data.item_details.map(item => `
                        <li class="p-2 flex justify-between items-center text-xs">
                            <span class="font-medium text-gray-800">${item.item_name}</span>
                            <span class="text-gray-400 font-mono">${item.item_code}</span>
                        </li>
                    `).join('')}
                </ul>`;
            } else {
                list.innerHTML = '<p class="text-xs text-center py-4 text-gray-500">No materials included.</p>';
            }
            
            document.getElementById('editFromViewBtn').onclick = () => {
                closeModal('viewBoqModal');
                editBoq(id);
            };
            
            openModal('viewBoqModal');
        } else {
            showAlert(data.message, 'error');
        }
    });
}

function editBoq(id) {
    document.getElementById('modalTitle').innerText = 'Edit BOQ Set';
    document.getElementById('saveButton').innerText = 'Update BOQ Set';
    
    // Reset form
    document.getElementById('boqForm').reset();
    document.getElementById('boq_id').value = id;
    
    // Fetch data
    fetch(`get-master.php?id=${id}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const boq = data.boq;
            document.getElementById('boq_name').value = boq.boq_name;
            document.getElementById('customer_id').value = boq.customer_id;
            document.getElementById('status_field').value = boq.status;
            
            // Check materials
            const materialCheckboxes = document.querySelectorAll('input[name="materials[]"]');
            materialCheckboxes.forEach(cb => {
                cb.checked = data.item_ids.includes(parseInt(cb.value));
            });
            
            openModal('createBoqModal');
        } else {
            showAlert(data.message, 'error');
        }
    });
}

function deleteBoq(id) {
    console.log('deleteBoq called with ID:', id);
    
    showConfirmDialog(
        'Delete BOQ Set',
        'Are you sure you want to delete this BOQ set? This will not delete the materials, only the set.',
        () => {
            console.log('User confirmed, sending delete request...');
            
            fetch(`delete-master.php?id=${id}`, { 
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.text().then(text => {
                    console.log('Raw response:', text);
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('Failed to parse JSON:', e);
                        throw new Error('Invalid JSON response: ' + text);
                    }
                });
            })
            .then(data => {
                console.log('Parsed data:', data);
                if (data.success) {
                    showAlert(data.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert(data.message || 'Failed to delete BOQ set', 'error');
                }
            })
            .catch(err => {
                console.error('Delete error:', err);
                showAlert('Failed to delete BOQ set: ' + err.message, 'error');
            });
        },
        () => {
            console.log('User cancelled deletion');
        }
    );
}

function showConfirmDialog(title, message, onConfirm, onCancel) {
    // Create overlay
    const overlay = document.createElement('div');
    overlay.className = 'fixed inset-0 bg-black bg-opacity-50 z-[9998] flex items-center justify-center';
    overlay.style.animation = 'fadeIn 0.2s ease-out';
    
    // Create dialog
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
                    Delete
                </button>
            </div>
        </div>
    `;
    
    overlay.appendChild(dialog);
    document.body.appendChild(overlay);
    
    // Add animations
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
    
    // Handle buttons
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

// Utility
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
