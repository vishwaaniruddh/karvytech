<?php
require_once __DIR__ . '/../../../controllers/CouriersController.php';

$controller = new CouriersController();
$data = $controller->index();

$title = 'Couriers Management';

ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900"><?php echo $title; ?></h1>
        <p class="mt-2 text-sm text-gray-700">Manage courier service providers</p>
    </div>
    <div class="flex space-x-3">
        <button onclick="openCreateModal()" class="btn btn-primary">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
            </svg>
            Add New Courier
        </button>
    </div>
</div>

<!-- Search and Filters -->
<div class="card mb-6">
    <div class="card-body">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <input type="text" id="searchInput" class="search-input pl-10" placeholder="Search couriers..." value="<?php echo htmlspecialchars($data['search']); ?>">
                </div>
            </div>
            <div class="flex gap-2">
                <select id="statusFilter" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" <?php echo $data['status_filter'] === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $data['status_filter'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Couriers Table -->
<div class="card">
    <div class="card-body">
        <div class="overflow-x-auto">
            <table class="data-table" id="couriersTable">
                <thead>
                    <tr>
                        <th>Courier Name</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['records'])): ?>
                    <tr>
                        <td colspan="4" class="text-center text-gray-500 py-8">
                            No couriers found. Click "Add New Courier" to create one.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($data['records'] as $record): ?>
                    <tr>
                        <td>
                            <div class="text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($record['courier_name']); ?>
                            </div>
                            <div class="text-sm text-gray-500">
                                ID: <?php echo $record['id']; ?>
                            </div>
                        </td>
                        <td>
                            <span class="badge <?php echo $record['status'] === 'active' ? 'badge-success' : 'badge-danger'; ?>">
                                <?php echo ucfirst($record['status']); ?>
                            </span>
                        </td>
                        <td class="text-sm text-gray-500">
                            <?php echo date('M j, Y', strtotime($record['created_at'])); ?>
                            <?php if (!empty($record['created_by_name'])): ?>
                            <br><small>by <?php echo htmlspecialchars($record['created_by_name']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="flex items-center space-x-2">
                                <button onclick="editCourier(<?php echo $record['id']; ?>)" class="btn btn-sm btn-primary" title="Edit">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                                    </svg>
                                </button>
                                <button onclick="toggleCourierStatus(<?php echo $record['id']; ?>)" class="btn btn-sm <?php echo $record['status'] === 'active' ? 'btn-secondary' : 'btn-success'; ?>" title="<?php echo $record['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>">
                                    <?php if ($record['status'] === 'active'): ?>
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    <?php else: ?>
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    <?php endif; ?>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($data['pagination']['total_pages'] > 1): ?>
        <div class="mt-4 flex items-center justify-between border-t border-gray-200 pt-4">
            <div class="text-sm text-gray-700">
                Showing <?php echo (($data['pagination']['current_page'] - 1) * $data['pagination']['limit']) + 1; ?> to 
                <?php echo min($data['pagination']['current_page'] * $data['pagination']['limit'], $data['pagination']['total_records']); ?> of 
                <?php echo $data['pagination']['total_records']; ?> results
            </div>
            <nav class="flex space-x-2">
                <?php for ($i = 1; $i <= $data['pagination']['total_pages']; $i++): ?>
                    <a href="?page=<?php echo $i; ?><?php echo !empty($data['search']) ? '&search=' . urlencode($data['search']) : ''; ?><?php echo !empty($data['status_filter']) ? '&status=' . urlencode($data['status_filter']) : ''; ?>" 
                       class="px-3 py-2 text-sm font-medium rounded-md <?php echo $i === $data['pagination']['current_page'] ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Create/Edit Courier Modal -->
<div id="courierModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Add New Courier</h3>
            <button type="button" class="modal-close" onclick="closeModal('courierModal')">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <form id="courierForm">
            <input type="hidden" id="courierId" name="id">
            <div class="modal-body">
                <div class="form-group">
                    <label for="courier_name" class="form-label">Courier Name *</label>
                    <input type="text" id="courier_name" name="name" class="form-input" required placeholder="Enter courier name">
                </div>
                
                <div class="form-group">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('courierModal')" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary" id="submitBtn">Create Courier</button>
            </div>
        </form>
    </div>
</div>

<script>
// Search and filter functionality
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

// Modal functions
function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Add New Courier';
    document.getElementById('courierForm').reset();
    document.getElementById('courierId').value = '';
    document.getElementById('submitBtn').textContent = 'Create Courier';
    openModal('courierModal');
}

function editCourier(id) {
    document.getElementById('modalTitle').textContent = 'Edit Courier';
    document.getElementById('submitBtn').textContent = 'Update Courier';
    
    // Fetch courier data
    fetch(`view.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const record = data.record;
                document.getElementById('courierId').value = record.id;
                document.getElementById('courier_name').value = record.courier_name;
                document.getElementById('status').value = record.status;
                openModal('courierModal');
            } else {
                showAlert(data.message || 'Failed to load courier data', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Failed to load courier data', 'error');
        });
}

// Form submission
document.getElementById('courierForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const courierId = document.getElementById('courierId').value;
    const isEdit = courierId !== '';
    
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = isEdit ? 'Updating...' : 'Creating...';
    
    const url = isEdit ? `edit.php?id=${courierId}` : 'create.php';
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeModal('courierModal');
            showAlert(data.message || `Courier ${isEdit ? 'updated' : 'created'} successfully!`, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert(data.message || `Failed to ${isEdit ? 'update' : 'create'} courier`, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert(`An error occurred while ${isEdit ? 'updating' : 'creating'} the courier`, 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
});

function toggleCourierStatus(id) {
    if (confirm('Are you sure you want to change this courier\'s status?')) {
        fetch(`toggle_status.php?id=${id}`, { method: 'POST' })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message || 'Courier status updated successfully', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert(data.message || 'Failed to update courier status', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Failed to update courier status', 'error');
            });
    }
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

function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `fixed top-4 right-4 z-50 p-4 rounded-md shadow-lg ${
        type === 'success' ? 'bg-green-100 border border-green-400 text-green-700' :
        type === 'error' ? 'bg-red-100 border border-red-400 text-red-700' :
        'bg-blue-100 border border-blue-400 text-blue-700'
    }`;
    alertDiv.textContent = message;
    
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.parentNode.removeChild(alertDiv);
        }
    }, 3000);
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../includes/admin_layout.php';
?>
