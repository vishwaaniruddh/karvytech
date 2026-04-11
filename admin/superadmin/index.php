<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/SuperadminRequest.php';

// Require superadmin authentication
Auth::requireRole('superadmin');

$requestModel = new SuperadminRequest();
$currentUser = Auth::getCurrentUser();

// Handle filters
$filters = [
    'status' => $_GET['status'] ?? '',
    'request_type' => $_GET['request_type'] ?? '',
    'priority' => $_GET['priority'] ?? '',
    'search' => $_GET['search'] ?? ''
];

$page = (int)($_GET['page'] ?? 1);
$result = $requestModel->getAllWithPagination($page, 20, $filters);
$stats = $requestModel->getStats();
$requestTypes = $requestModel->getRequestTypes();

$title = 'Superadmin Actions';
ob_start();
?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">🔐 Superadmin Actions</h1>
        <p class="mt-2 text-sm text-gray-700">Manage and review all system requests requiring superadmin approval</p>
    </div>
    <div>
        <a href="restore-data.php" class="btn btn-success" title="Restore Deleted Data">
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
            </svg>
            Restore Data
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 mb-6">
    <!-- Total Requests -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 uppercase font-semibold">Total Requests</p>
                <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo number_format($stats['total_requests']); ?></p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Pending -->
    <div class="bg-white rounded-lg shadow-sm border border-yellow-200 p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-yellow-700 uppercase font-semibold">Pending</p>
                <p class="text-2xl font-bold text-yellow-900 mt-1"><?php echo number_format($stats['pending']); ?></p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-yellow-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Approved -->
    <div class="bg-white rounded-lg shadow-sm border border-green-200 p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-green-700 uppercase font-semibold">Approved</p>
                <p class="text-2xl font-bold text-green-900 mt-1"><?php echo number_format($stats['approved']); ?></p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Rejected -->
    <div class="bg-white rounded-lg shadow-sm border border-red-200 p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-red-700 uppercase font-semibold">Rejected</p>
                <p class="text-2xl font-bold text-red-900 mt-1"><?php echo number_format($stats['rejected']); ?></p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-red-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Urgent Pending -->
    <div class="bg-white rounded-lg shadow-sm border border-orange-200 p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-orange-700 uppercase font-semibold">Urgent</p>
                <p class="text-2xl font-bold text-orange-900 mt-1"><?php echo number_format($stats['urgent_pending']); ?></p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-orange-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- High Priority -->
    <div class="bg-white rounded-lg shadow-sm border border-purple-200 p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-purple-700 uppercase font-semibold">High Priority</p>
                <p class="text-2xl font-bold text-purple-900 mt-1"><?php echo number_format($stats['high_pending']); ?></p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-purple-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.05 3.636a1 1 0 010 1.414 7 7 0 000 9.9 1 1 0 11-1.414 1.414 9 9 0 010-12.728 1 1 0 011.414 0zm9.9 0a1 1 0 011.414 0 9 9 0 010 12.728 1 1 0 11-1.414-1.414 7 7 0 000-9.9 1 1 0 010-1.414zM7.879 6.464a1 1 0 010 1.414 3 3 0 000 4.243 1 1 0 11-1.415 1.414 5 5 0 010-7.07 1 1 0 011.415 0zm4.242 0a1 1 0 011.415 0 5 5 0 010 7.072 1 1 0 01-1.415-1.415 3 3 0 000-4.242 1 1 0 010-1.415zM10 9a1 1 0 011 1v.01a1 1 0 11-2 0V10a1 1 0 011-1z" clip-rule="evenodd"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-6">
    <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="lg:col-span-2">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <input type="text" id="searchInput" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md" placeholder="Search requests..." value="<?php echo htmlspecialchars($filters['search']); ?>">
                </div>
            </div>
            <div>
                <select id="statusFilter" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" <?php echo $filters['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="approved" <?php echo $filters['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                    <option value="rejected" <?php echo $filters['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                </select>
            </div>
            <div>
                <select id="priorityFilter" class="form-select">
                    <option value="">All Priority</option>
                    <option value="urgent" <?php echo $filters['priority'] === 'urgent' ? 'selected' : ''; ?>>Urgent</option>
                    <option value="high" <?php echo $filters['priority'] === 'high' ? 'selected' : ''; ?>>High</option>
                    <option value="medium" <?php echo $filters['priority'] === 'medium' ? 'selected' : ''; ?>>Medium</option>
                    <option value="low" <?php echo $filters['priority'] === 'low' ? 'selected' : ''; ?>>Low</option>
                </select>
            </div>
            <div>
                <select id="typeFilter" class="form-select">
                    <option value="">All Types</option>
                    <?php foreach ($requestTypes as $type): ?>
                        <option value="<?php echo htmlspecialchars($type); ?>" <?php echo $filters['request_type'] === $type ? 'selected' : ''; ?>>
                            <?php echo ucwords(str_replace('_', ' ', $type)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
</div>

<?php include 'requests-table.php'; ?>

<!-- View Request Modal -->
<div id="viewRequestModal" class="modal">
    <div class="modal-content-large max-w-2xl">
        <div class="modal-header">
            <h3 class="modal-title">Request Details</h3>
            <button type="button" class="modal-close" onclick="closeModal('viewRequestModal')">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <div class="modal-body" id="viewRequestContent">
            <!-- Content loaded dynamically -->
        </div>
        <div class="modal-footer">
            <button type="button" onclick="closeModal('viewRequestModal')" class="btn btn-secondary">Close</button>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div id="approveModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Approve Request</h3>
            <button type="button" class="modal-close" onclick="closeModal('approveModal')">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <form id="approveForm">
            <input type="hidden" id="approve_request_id">
            <div class="modal-body">
                <p class="text-sm text-gray-600 mb-4">Are you sure you want to approve this request?</p>
                <div class="form-group">
                    <label for="approve_remarks" class="form-label">Remarks (Optional)</label>
                    <textarea id="approve_remarks" class="form-textarea" rows="3" placeholder="Add any comments or notes..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('approveModal')" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-success">Approve Request</button>
            </div>
        </form>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Reject Request</h3>
            <button type="button" class="modal-close" onclick="closeModal('rejectModal')">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <form id="rejectForm">
            <input type="hidden" id="reject_request_id">
            <div class="modal-body">
                <p class="text-sm text-gray-600 mb-4">Please provide a reason for rejecting this request.</p>
                <div class="form-group">
                    <label for="reject_remarks" class="form-label">Rejection Reason *</label>
                    <textarea id="reject_remarks" class="form-textarea" rows="4" placeholder="Explain why this request is being rejected..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('rejectModal')" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-danger">Reject Request</button>
            </div>
        </form>
    </div>
</div>

<script>
// Filter functionality
document.getElementById('searchInput').addEventListener('keyup', debounce(applyFilters, 500));
document.getElementById('statusFilter').addEventListener('change', applyFilters);
document.getElementById('priorityFilter').addEventListener('change', applyFilters);
document.getElementById('typeFilter').addEventListener('change', applyFilters);

function applyFilters() {
    const url = new URL(window.location);
    const search = document.getElementById('searchInput').value;
    const status = document.getElementById('statusFilter').value;
    const priority = document.getElementById('priorityFilter').value;
    const type = document.getElementById('typeFilter').value;
    
    if (search) url.searchParams.set('search', search);
    else url.searchParams.delete('search');
    
    if (status) url.searchParams.set('status', status);
    else url.searchParams.delete('status');
    
    if (priority) url.searchParams.set('priority', priority);
    else url.searchParams.delete('priority');
    
    if (type) url.searchParams.set('request_type', type);
    else url.searchParams.delete('request_type');
    
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

function viewRequest(id) {
    fetch(`view-request.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('viewRequestContent').innerHTML = data.html;
                openModal('viewRequestModal');
            } else {
                showAlert(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Failed to load request details', 'error');
        });
}

function approveRequest(id) {
    document.getElementById('approve_request_id').value = id;
    document.getElementById('approve_remarks').value = '';
    openModal('approveModal');
}

function rejectRequest(id) {
    document.getElementById('reject_request_id').value = id;
    document.getElementById('reject_remarks').value = '';
    openModal('rejectModal');
}

// Approve form submission
document.getElementById('approveForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const id = document.getElementById('approve_request_id').value;
    const remarks = document.getElementById('approve_remarks').value;
    
    fetch('process-request.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'approve', id: id, remarks: remarks})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeModal('approveModal');
            showAlert(data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Failed to approve request', 'error');
    });
});

// Reject form submission
document.getElementById('rejectForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const id = document.getElementById('reject_request_id').value;
    const remarks = document.getElementById('reject_remarks').value;
    
    fetch('process-request.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'reject', id: id, remarks: remarks})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeModal('rejectModal');
            showAlert(data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Failed to reject request', 'error');
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
