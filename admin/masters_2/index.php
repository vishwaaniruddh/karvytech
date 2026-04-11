<?php
require_once __DIR__ . '/../../controllers/BanksController.php';
require_once __DIR__ . '/../../controllers/CustomersController.php';
require_once __DIR__ . '/../../controllers/ZonesController.php';
require_once __DIR__ . '/../../controllers/CountriesController.php';
require_once __DIR__ . '/../../controllers/StatesController.php';
require_once __DIR__ . '/../../controllers/CitiesController.php';
require_once __DIR__ . '/../../controllers/BoqMasterController.php';
require_once __DIR__ . '/../../controllers/CouriersController.php';


// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);



// Determine which master to show
$masterType = $_GET['type'] ?? 'banks';
$validTypes = ['banks', 'customers', 'zones', 'countries', 'states', 'cities', 'boq', 'couriers'];

if (!in_array($masterType, $validTypes)) {
    $masterType = 'banks';
}


// echo 'masterType' . $masterType ; 
// Initialize appropriate controller
switch ($masterType) {
    case 'banks':
        $controller = new BanksController();
        $title = 'Banks Management';
        $singular = 'Bank';
        break;
    case 'customers':
        $controller = new CustomersController();
        $title = 'Customers Management';
        $singular = 'Customer';
        break;
    case 'zones':
        $controller = new ZonesController();
        $title = 'Zones Management';
        $singular = 'Zone';
        break;
    case 'countries':
        $controller = new CountriesController();
        $title = 'Countries Management';
        $singular = 'Country';
        break;
    case 'states':
        $controller = new StatesController();
        $title = 'States Management';
        $singular = 'State';
        break;
    case 'cities':
        $controller = new CitiesController();
        $title = 'Cities Management';
        $singular = 'City';
        break;
    case 'boq':
        $controller = new BoqMasterController();
        $title = 'BOQ Management';
        $singular = 'BOQ';
        break;
    case 'couriers':
        $controller = new CouriersController();
        $title = 'Courier Management';
        $singular = 'Courier';
        break;
    default:
        $controller = new BanksController();
        $title = 'Banks Management';
        $singular = 'Bank';
}

$data = $controller->index();

ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900"><?php echo $title; ?></h1>
        <p class="mt-2 text-sm text-gray-700">Manage master data for <?php echo strtolower($singular); ?>s</p>
    </div>
    <div class="flex space-x-3">
        <!-- Master Type Selector -->
        <select id="masterTypeSelector" class="form-select" onchange="changeMasterType(this.value)">
            <option value="banks" <?php echo $masterType === 'banks' ? 'selected' : ''; ?>>Banks</option>
            <option value="customers" <?php echo $masterType === 'customers' ? 'selected' : ''; ?>>Customers</option>
            <option value="zones" <?php echo $masterType === 'zones' ? 'selected' : ''; ?>>Zones</option>
            <option value="countries" <?php echo $masterType === 'countries' ? 'selected' : ''; ?>>Countries</option>
            <option value="states" <?php echo $masterType === 'states' ? 'selected' : ''; ?>>States</option>
            <option value="cities" <?php echo $masterType === 'cities' ? 'selected' : ''; ?>>Cities</option>
            <option value="boq" <?php echo $masterType === 'boq' ? 'selected' : ''; ?>>BOQ Master</option>
            <option value="couriers" <?php echo $masterType === 'courier' ? 'selected' : ''; ?>>Courier</option>
        </select>
        <button onclick="openCreateModal()" class="btn btn-primary">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
            </svg>
            Add New <?php echo $singular; ?>
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
                    <input type="text" id="searchInput" class="search-input" placeholder="Search <?php echo strtolower($singular); ?>s..." value="<?php echo htmlspecialchars($data['search']); ?>">
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

<!-- Masters Table -->
<div class="card">
    <div class="card-body">
        <div class="overflow-x-auto">
            <table class="data-table" id="mastersTable">
                <thead>
                    <tr>
                        <th><?php echo $masterType === 'boq' ? 'BOQ Name' : 'Name'; ?></th>
                        <?php if ($masterType === 'boq'): ?>
                            <th>Serial Required</th>
                        <?php endif; ?>
                        <?php if ($masterType === 'states'): ?>
                            <th>Country</th>
                        <?php endif; ?>
                        <?php if ($masterType === 'cities'): ?>
                            <th>State</th>
                            <th>Zone</th>
                            <th>Country</th>
                        <?php endif; ?>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['records'] as $record): ?>
                        <tr>
                            <td>
                                <div class="text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($masterType === 'boq' ? $record['boq_name'] : $record['name']); ?>
                                </div>
                                <div class="text-sm text-gray-500">
                                    ID: <?php echo $masterType === 'boq' ? $record['boq_id'] : $record['id']; ?>
                                </div>
                            </td>
                            <?php if ($masterType === 'boq'): ?>
                                <td>
                                    <span class="badge <?php echo $record['is_serial_number_required'] ? 'badge-info' : 'badge-secondary'; ?>">
                                        <?php echo $record['is_serial_number_required'] ? 'Yes' : 'No'; ?>
                                    </span>
                                </td>
                            <?php endif; ?>
                            <?php if ($masterType === 'states'): ?>
                                <td class="text-sm text-gray-500">
                                    <?php echo htmlspecialchars($record['country_name'] ?? 'N/A'); ?>
                                </td>
                            <?php endif; ?>
                            <?php if ($masterType === 'cities'): ?>
                                <td class="text-sm text-gray-500">
                                    <?php echo htmlspecialchars($record['state_name'] ?? 'N/A'); ?>
                                </td>
                                <td class="text-sm text-gray-500">
                                    <?php echo htmlspecialchars($record['zone_name'] ?? 'N/A'); ?>
                                </td>
                                <td class="text-sm text-gray-500">
                                    <?php echo htmlspecialchars($record['country_name'] ?? 'N/A'); ?>
                                </td>
                            <?php endif; ?>
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
                                    <button onclick="viewMaster(<?php echo $masterType === 'boq' ? $record['boq_id'] : $record['id']; ?>)" class="btn btn-sm btn-secondary" title="View">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                    <button onclick="editMaster(<?php echo $masterType === 'boq' ? $record['boq_id'] : $record['id']; ?>)" class="btn btn-sm btn-primary" title="Edit">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                                        </svg>
                                    </button>
                                    <button onclick="toggleMasterStatus(<?php echo $masterType === 'boq' ? $record['boq_id'] : $record['id']; ?>)" class="btn btn-sm <?php echo $record['status'] === 'active' ? 'btn-warning' : 'btn-success'; ?>" title="<?php echo $record['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>">
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
                                    <button onclick="deleteMaster(<?php echo $masterType === 'boq' ? $record['boq_id'] : $record['id']; ?>)" class="btn btn-sm btn-danger" title="Delete">
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

        <!-- Pagination -->
        <?php if ($data['pagination']['total_pages'] > 1): ?>
            <div class="pagination">
                <div class="pagination-info">
                    Showing <?php echo (($data['pagination']['current_page'] - 1) * $data['pagination']['limit']) + 1; ?> to
                    <?php echo min($data['pagination']['current_page'] * $data['pagination']['limit'], $data['pagination']['total_records']); ?> of
                    <?php echo $data['pagination']['total_records']; ?> results
                </div>
                <div class="pagination-nav-desktop">
                    <nav class="flex space-x-2">
                        <?php for ($i = 1; $i <= $data['pagination']['total_pages']; $i++): ?>
                            <a href="?type=<?php echo $masterType; ?>&page=<?php echo $i; ?><?php echo !empty($data['search']) ? '&search=' . urlencode($data['search']) : ''; ?>"
                                class="pagination-btn <?php echo $i === $data['pagination']['current_page'] ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </nav>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Create Master Modal -->
<div id="createMasterModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Add New <?php echo $singular; ?></h3>
            <button type="button" class="modal-close" onclick="closeModal('createMasterModal')">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <form id="createMasterForm">
            <div class="modal-body">
                <div class="form-group">
                    <label for="<?php echo $masterType === 'boq' ? 'boq_name' : 'name'; ?>" class="form-label"><?php echo $singular; ?> Name *</label>
                    <input type="text" id="<?php echo $masterType === 'boq' ? 'boq_name' : 'name'; ?>" name="<?php echo $masterType === 'boq' ? 'boq_name' : 'name'; ?>" class="form-input" required>
                </div>

                <?php if ($masterType === 'boq'): ?>
                    <div class="form-group">
                        <label class="flex items-center">
                            <input type="checkbox" id="is_serial_number_required" name="is_serial_number_required" class="form-checkbox">
                            <span class="ml-2">Serial Number Required</span>
                        </label>
                    </div>
                <?php endif; ?>

                <?php if ($masterType === 'states'): ?>
                    <div class="form-group">
                        <label for="country_id" class="form-label">Country *</label>
                        <select id="country_id" name="country_id" class="form-select" required onchange="loadStates(this.value)">
                            <option value="">Select Country</option>
                            <!-- Countries will be loaded dynamically -->
                        </select>
                    </div>
                <?php endif; ?>

                <?php if ($masterType === 'cities'): ?>
                    <div class="form-group">
                        <label for="country_id" class="form-label">Country *</label>
                        <select id="country_id" name="country_id" class="form-select" required onchange="loadStates(this.value)">
                            <option value="">Select Country</option>
                            <!-- Countries will be loaded dynamically -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="zone_id" class="form-label">Zone</label>
                        <select id="zone_id" name="zone_id" class="form-select" readonly>
                            <option value="">Auto-selected based on state</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="state_id" class="form-label">State *</label>
                        <select id="state_id" name="state_id" class="form-select" required onchange="loadZoneForState(this.value)">
                            <option value="">Select State</option>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('createMasterModal')" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Create <?php echo $singular; ?></button>
            </div>
        </form>
    </div>
</div>

<script>
    const currentMasterType = '<?php echo $masterType; ?>';
    const currentSingular = '<?php echo $singular; ?>';

    function changeMasterType(type) {
        window.location.href = `?type=${type}`;
    }

    // Search and filter functionality
    document.getElementById('searchInput').addEventListener('keyup', debounce(function() {
        applyFilters();
    }, 500));

    document.getElementById('statusFilter').addEventListener('change', applyFilters);

    function applyFilters() {
        const searchTerm = document.getElementById('searchInput').value;
        const status = document.getElementById('statusFilter').value;

        const url = new URL(window.location);
        url.searchParams.set('type', currentMasterType);

        if (searchTerm) url.searchParams.set('search', searchTerm);
        else url.searchParams.delete('search');

        if (status) url.searchParams.set('status', status);
        else url.searchParams.delete('status');

        url.searchParams.delete('page');
        window.location.href = url.toString();
    }

    // Form submission
    document.getElementById('createMasterForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        formData.append('action', 'create');
        formData.append('type', currentMasterType);

        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Creating...';

        fetch(`../../api/masters.php?path=${currentMasterType}`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal('createMasterModal');
                    showAlert(`${currentSingular} created successfully!`, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert(data.message || 'Failed to create record', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred while creating the record', 'error');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            });
    });

    // Master management functions
    function viewMaster(id) {
        fetch(`../../api/masters.php?path=${currentMasterType}/${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const record = data.data.record;
                    const nameField = currentMasterType === 'boq' ? 'boq_name' : 'name';
                    alert(`${currentSingular} Details:\n\nName: ${record[nameField]}\nStatus: ${record.status}\nCreated: ${formatDate(record.created_at)}\nUpdated: ${formatDate(record.updated_at)}`);
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Failed to load data', 'error');
            });
    }

    function editMaster(id) {
        // For now, show a simple prompt - can be enhanced with a modal later
        const nameField = currentMasterType === 'boq' ? 'boq_name' : 'name';
        const newName = prompt(`Enter new name for ${currentSingular}:`);
        if (newName && newName.trim()) {
            const formData = new FormData();
            formData.append(nameField, newName.trim());
            formData.append('status', 'active');

            fetch(`/project/api/masters.php?path=${currentMasterType}/${id}`, {
                    method: 'POST',
                    body: formData
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
                    showAlert('Failed to update', 'error');
                });
        }
    }

    function toggleMasterStatus(id) {
        confirmAction(`Are you sure you want to change this ${currentSingular.toLowerCase()}'s status?`, function() {
            fetch(`../../api/masters.php?path=${currentMasterType}/${id}/toggle-status`, {
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
                    showAlert('Failed to update status', 'error');
                });
        });
    }

    function deleteMaster(id) {
        confirmAction(`Are you sure you want to delete this ${currentSingular.toLowerCase()}? This action cannot be undone.`, function() {
            fetch(`../../api/masters.php?path=${currentMasterType}/${id}`, {
                    method: 'DELETE'
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
                    showAlert('Failed to delete', 'error');
                });
        });
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
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

    function confirmAction(message, callback) {
        if (confirm(message)) {
            callback();
        }
    }

    function showAlert(message, type) {
        // Create alert element
        const alertDiv = document.createElement('div');
        alertDiv.className = `fixed top-4 right-4 z-50 p-4 rounded-md shadow-lg ${
        type === 'success' ? 'bg-green-100 border border-green-400 text-green-700' :
        type === 'error' ? 'bg-red-100 border border-red-400 text-red-700' :
        'bg-blue-100 border border-blue-400 text-blue-700'
    }`;
        alertDiv.textContent = message;

        document.body.appendChild(alertDiv);

        // Remove after 3 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.parentNode.removeChild(alertDiv);
            }
        }, 3000);
    }

    // Load countries for states and cities forms
    function loadCountries() {
        const countrySelect = document.getElementById('country_id');
        if (!countrySelect) return;

        fetch('../../api/masters.php?path=countries')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    countrySelect.innerHTML = '<option value="">Select Country</option>';
                    data.data.records.forEach(country => {
                        countrySelect.innerHTML += `<option value="${country.id}">${country.name}</option>`;
                    });
                }
            })
            .catch(error => console.error('Error loading countries:', error));
    }

    // Load zones for dropdown
    function loadZones() {
        const zoneSelect = document.getElementById('zone_id');
        if (!zoneSelect) return;

        fetch('../../api/masters.php?path=zones')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Keep the zones data for later use
                    window.zonesData = data.data.records;
                }
            })
            .catch(error => console.error('Error loading zones:', error));
    }

    // Load states when country is selected
    function loadStates(countryId) {
        const stateSelect = document.getElementById('state_id');
        const zoneSelect = document.getElementById('zone_id');

        if (!stateSelect) return;

        stateSelect.innerHTML = '<option value="">Select State</option>';
        if (zoneSelect) {
            zoneSelect.innerHTML = '<option value="">Auto-selected based on state</option>';
        }

        if (!countryId) return;

        fetch(`../../api/masters.php?path=states&country_id=${countryId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    data.data.records.forEach(state => {
                        if (state.country_id == countryId) {
                            stateSelect.innerHTML += `<option value="${state.id}" data-zone-id="${state.zone_id}">${state.name}</option>`;
                        }
                    });
                }
            })
            .catch(error => console.error('Error loading states:', error));
    }

    // Load zone when state is selected
    function loadZoneForState(stateId) {
        const stateSelect = document.getElementById('state_id');
        const zoneSelect = document.getElementById('zone_id');

        if (!stateSelect || !zoneSelect || !stateId) {
            if (zoneSelect) {
                zoneSelect.innerHTML = '<option value="">Auto-selected based on state</option>';
            }
            return;
        }

        // Get the selected state option
        const selectedOption = stateSelect.options[stateSelect.selectedIndex];
        const zoneId = selectedOption.getAttribute('data-zone-id');

        if (zoneId && window.zonesData) {
            // Find the zone name
            const zone = window.zonesData.find(z => z.id == zoneId);
            if (zone) {
                zoneSelect.innerHTML = `<option value="${zone.id}" selected>${zone.name}</option>`;
            } else {
                zoneSelect.innerHTML = '<option value="">Zone not found</option>';
            }
        } else {
            // Fetch zone info from API if not available
            fetch(`../../api/masters.php?path=states/${stateId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data.record.zone_id) {
                        const zoneId = data.data.record.zone_id;
                        // Fetch zone details
                        fetch(`../../api/masters.php?path=zones/${zoneId}`)
                            .then(response => response.json())
                            .then(zoneData => {
                                if (zoneData.success) {
                                    const zone = zoneData.data.record;
                                    zoneSelect.innerHTML = `<option value="${zone.id}" selected>${zone.name}</option>`;
                                }
                            });
                    } else {
                        zoneSelect.innerHTML = '<option value="">No zone assigned</option>';
                    }
                })
                .catch(error => {
                    console.error('Error loading zone for state:', error);
                    zoneSelect.innerHTML = '<option value="">Error loading zone</option>';
                });
        }
    }

    function openCreateModal() {
        // Load countries for states and cities
        if (currentMasterType === 'states' || currentMasterType === 'cities') {
            loadCountries();
        }
        // Load zones for cities
        if (currentMasterType === 'cities') {
            loadZones();
        }
        openModal('createMasterModal');
    }
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>