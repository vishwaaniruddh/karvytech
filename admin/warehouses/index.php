<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/Warehouse.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$warehouseModel = new Warehouse();

// Handle filters
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';

// Get warehouses
$warehouses = $warehouseModel->getAll($search, $status);

$title = 'Warehouse Management';
ob_start();
?>

<style>
    #warehouseModal {
        display: none;
        align-items: center;
        justify-content: center;
    }
    #warehouseModal .modal-content {
        /* margin-top: 2rem; */
    }
</style>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Warehouse Management</h1>
        <p class="mt-2 text-sm text-gray-700">Manage warehouse locations and inventory distribution</p>
    </div>
    <button onclick="openCreateModal()" class="btn btn-primary">
        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
        </svg>
        Add New Warehouse
    </button>
</div>

<!-- Search and Filters -->
<div class="card mb-6">
    <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <input type="text" id="searchInput" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Search warehouses..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div>
                <select id="statusFilter" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">All Status</option>
                    <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Warehouses Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($warehouses as $warehouse): ?>
    <div class="card">
        <div class="card-body">
            <div class="flex justify-between items-start mb-4">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($warehouse['name']); ?></h3>
                        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($warehouse['warehouse_code']); ?></p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <?php if ($warehouse['is_default']): ?>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        Default
                    </span>
                    <?php endif; ?>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium <?php echo $warehouse['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                        <?php echo ucfirst($warehouse['status']); ?>
                    </span>
                </div>
            </div>
            
            <div class="space-y-2 mb-4">
                <div class="flex items-start">
                    <svg class="w-4 h-4 text-gray-400 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="text-sm text-gray-600">
                        <?php echo htmlspecialchars($warehouse['address']); ?><br>
                        <?php echo htmlspecialchars($warehouse['city']); ?>, <?php echo htmlspecialchars($warehouse['state']); ?> <?php echo htmlspecialchars($warehouse['pincode']); ?>
                    </div>
                </div>
                
                <div class="flex items-center">
                    <svg class="w-4 h-4 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"></path>
                    </svg>
                    <span class="text-sm text-gray-600"><?php echo htmlspecialchars($warehouse['contact_phone']); ?></span>
                </div>
                
                <div class="flex items-center">
                    <svg class="w-4 h-4 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"></path>
                    </svg>
                    <span class="text-sm text-gray-600"><?php echo htmlspecialchars($warehouse['contact_person']); ?></span>
                </div>
                
                <?php if ($warehouse['contact_email']): ?>
                <div class="flex items-center">
                    <svg class="w-4 h-4 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                    </svg>
                    <span class="text-sm text-gray-600"><?php echo htmlspecialchars($warehouse['contact_email']); ?></span>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                <button onclick="viewWarehouse(<?php echo $warehouse['id']; ?>)" class="btn btn-sm btn-secondary">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                    </svg>
                    View Stock
                </button>
                <div class="flex space-x-2">
                    <button onclick="editWarehouse(<?php echo $warehouse['id']; ?>)" class="btn btn-sm btn-primary" title="Edit">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                        </svg>
                    </button>
                    <?php if (!$warehouse['is_default']): ?>
                    <button onclick="deleteWarehouse(<?php echo $warehouse['id']; ?>)" class="btn btn-sm btn-danger" title="Delete">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (empty($warehouses)): ?>
<div class="card">
    <div class="card-body text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No warehouses found</h3>
        <p class="mt-1 text-sm text-gray-500">Get started by creating a new warehouse.</p>
        <div class="mt-6">
            <button onclick="openCreateModal()" class="btn btn-primary">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                </svg>
                Add New Warehouse
            </button>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Create/Edit Warehouse Modal -->
<div id="warehouseModal" class="modal" style="display: none; align-items: center; justify-content: center;">
    <div class="modal-content" style="max-width: 48rem;">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Add New Warehouse</h3>
            <button type="button" class="modal-close" onclick="closeWarehouseModal()">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <form id="warehouseForm">
            <input type="hidden" id="warehouse_id" name="id">
            <div class="modal-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label for="warehouse_code" class="form-label">Warehouse Code *</label>
                        <input type="text" id="warehouse_code" name="warehouse_code" class="form-input" required placeholder="e.g., WH-MUM-001">
                    </div>
                    <div class="form-group">
                        <label for="name" class="form-label">Warehouse Name *</label>
                        <input type="text" id="name" name="name" class="form-input" required placeholder="e.g., Mumbai Main Warehouse">
                    </div>
                    <div class="form-group md:col-span-2">
                        <label for="address" class="form-label">Address *</label>
                        <textarea id="address" name="address" rows="2" class="form-input" required placeholder="Street address"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="city" class="form-label">City *</label>
                        <input type="text" id="city" name="city" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="state" class="form-label">State *</label>
                        <input type="text" id="state" name="state" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="pincode" class="form-label">Pincode *</label>
                        <input type="text" id="pincode" name="pincode" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="contact_person" class="form-label">Contact Person *</label>
                        <input type="text" id="contact_person" name="contact_person" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="contact_phone" class="form-label">Contact Phone *</label>
                        <input type="tel" id="contact_phone" name="contact_phone" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="contact_email" class="form-label">Contact Email</label>
                        <input type="email" id="contact_email" name="contact_email" class="form-input">
                    </div>
                    <div class="form-group">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="flex items-center">
                            <input type="checkbox" id="is_default" name="is_default" class="form-checkbox">
                            <span class="ml-2">Set as Default Warehouse</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeWarehouseModal()" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary" id="submitBtn">Create Warehouse</button>
            </div>
        </form>
    </div>
</div>

<script>
// Local modal functions (override if needed)
function openWarehouseModal() {
    const modal = document.getElementById('warehouseModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeWarehouseModal() {
    const modal = document.getElementById('warehouseModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    const modal = document.getElementById('warehouseModal');
    if (e.target === modal) {
        closeWarehouseModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('warehouseModal');
        if (modal && modal.style.display === 'flex') {
            closeWarehouseModal();
        }
    }
});

// Search functionality
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
    
    window.location.href = url.toString();
}

function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Add New Warehouse';
    document.getElementById('submitBtn').textContent = 'Create Warehouse';
    document.getElementById('warehouseForm').reset();
    document.getElementById('warehouse_id').value = '';
    document.getElementById('is_default').checked = false;
    openWarehouseModal();
}

function editWarehouse(id) {
    fetch(`../../api/warehouses.php?id=${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const warehouse = data.data;
                document.getElementById('modalTitle').textContent = 'Edit Warehouse';
                document.getElementById('submitBtn').textContent = 'Update Warehouse';
                document.getElementById('warehouse_id').value = warehouse.id;
                document.getElementById('warehouse_code').value = warehouse.warehouse_code;
                document.getElementById('name').value = warehouse.name;
                document.getElementById('address').value = warehouse.address;
                document.getElementById('city').value = warehouse.city;
                document.getElementById('state').value = warehouse.state;
                document.getElementById('pincode').value = warehouse.pincode;
                document.getElementById('contact_person').value = warehouse.contact_person;
                document.getElementById('contact_phone').value = warehouse.contact_phone;
                document.getElementById('contact_email').value = warehouse.contact_email || '';
                document.getElementById('status').value = warehouse.status;
                document.getElementById('is_default').checked = warehouse.is_default == 1;
                openWarehouseModal();
            } else {
                showAlert('Error loading warehouse data: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Failed to load warehouse data: ' + error.message, 'error');
        });
}

function viewWarehouse(id) {
    window.location.href = `stock.php?warehouse_id=${id}`;
}

function deleteWarehouse(id) {
    if (confirm('Are you sure you want to delete this warehouse? This action cannot be undone.')) {
        fetch(`../../api/warehouses.php?id=${id}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Warehouse deleted successfully!', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred while deleting the warehouse.', 'error');
        });
    }
}

// Form submission
document.getElementById('warehouseForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const warehouseId = document.getElementById('warehouse_id').value;
    const isEdit = warehouseId !== '';
    
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = isEdit ? 'Updating...' : 'Creating...';
    
    fetch('../../api/warehouses.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            closeWarehouseModal();
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while saving the warehouse.', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
});

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
