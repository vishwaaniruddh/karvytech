<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/Site.php';
require_once __DIR__ . '/../../models/Vendor.php';
require_once __DIR__ . '/../../models/SiteDelegation.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$siteId = $_GET['id'] ?? null;
if (!$siteId) {
    header('Location: index.php');
    exit;
}

$siteModel = new Site();
$vendorModel = new Vendor();
$delegationModel = new SiteDelegation();

$site = $siteModel->findWithRelations($siteId);
if (!$site) {
    header('Location: index.php');
    exit;
}

// Check if site is already delegated
$activeDelegation = $delegationModel->getActiveDelegation($siteId);
$vendors = $vendorModel->getActiveVendors();
$delegationHistory = $delegationModel->getDelegationHistory($siteId);

$title = 'Delegate Site - ' . $site['site_id'];
ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <!-- <h1 class="text-2xl font-semibold text-gray-900">Delegate Site</h1> -->
        <p class="mt-2 text-sm text-gray-700">Assign site to vendor for installation</p>
    </div>
    <div>
        <a href="index.php" class="btn btn-secondary">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
            </svg>
            Back to Sites
        </a>
    </div>
</div>

<!-- Site Information Card -->
<div class="card mb-6">
    <div class="card-body">
        <h3 class="card-title">Site Information</h3>
    </div>
    <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Site ID</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo htmlspecialchars($site['site_id']); ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Store ID</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo htmlspecialchars($site['store_id'] ?: 'N/A'); ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo htmlspecialchars($site['location']); ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo htmlspecialchars($site['city_name'] ?: 'N/A'); ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo htmlspecialchars($site['state_name'] ?: 'N/A'); ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo htmlspecialchars($site['customer_name'] ?: 'N/A'); ?></p>
            </div>
        </div>
    </div>
</div>

<?php if ($activeDelegation): ?>
<!-- Current Delegation Card -->
<div class="card mb-6">
    <div class="card-body">
        <h3 class="card-title">Current Delegation</h3>
        <span class="badge badge-success">Active</span>
    </div>
    <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Delegated To</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo htmlspecialchars($activeDelegation['vendor_name']); ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Delegated By</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo htmlspecialchars($activeDelegation['delegated_by_name']); ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Delegation Date</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo date('M d, Y H:i', strtotime($activeDelegation['delegation_date'])); ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <span class="badge badge-success"><?php echo ucfirst($activeDelegation['status']); ?></span>
            </div>
            <?php if ($activeDelegation['notes']): ?>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo htmlspecialchars($activeDelegation['notes']); ?></p>
            </div>
            <?php endif; ?>
        </div>
        <div class="mt-4 flex space-x-2" >
            <button style="display:none;" onclick="completeDelegation(<?php echo $activeDelegation['id']; ?>)" class="btn btn-success">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
                Mark as Completed
            </button>
            <button onclick="cancelDelegation(<?php echo $activeDelegation['id']; ?>)" class="btn btn-danger">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
                Cancel Delegation
            </button>
        </div>
    </div>
</div>
<?php else: ?>
<!-- Delegation Form -->
<div class="card mb-6">
    <div class="card-body">
        <h3 class="card-title">Delegate Site to Vendor</h3>
    </div>
    <div class="card-body">
        <form id="delegationForm" action="process_delegation.php" method="POST">
            <input type="hidden" name="site_id" value="<?php echo $siteId; ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="vendor_id" class="form-label">Select Vendor *</label>
                    <select id="vendor_id" name="vendor_id" class="form-select" required>
                        <option value="">Choose a vendor...</option>
                        <?php foreach ($vendors as $vendor): ?>
                            <option value="<?php echo $vendor['id']; ?>">
                                <?php echo htmlspecialchars($vendor['name']); ?>
                                <?php if ($vendor['contact_person']): ?>
                                    - <?php echo htmlspecialchars($vendor['contact_person']); ?>
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label">Delegation Date</label>
                    <input type="text" class="form-input" value="<?php echo date('M d, Y H:i'); ?>" readonly>
                </div>
                <div class="md:col-span-2">
                    <label for="notes" class="form-label">Notes (Optional)</label>
                    <textarea id="notes" name="notes" class="form-textarea" rows="3" placeholder="Add any special instructions or notes for the vendor..."></textarea>
                </div>
            </div>
            <div class="mt-6">
                <button type="submit" class="btn btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"></path>
                    </svg>
                    Delegate Site
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Delegation History -->
<?php if (!empty($delegationHistory)): ?>
<div class="card">
    <div class="card-body">
        <h3 class="card-title">Delegation History</h3>
    </div>
    <div class="card-body">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Vendor</th>
                        <th>Delegated By</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($delegationHistory as $delegation): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($delegation['vendor_name']); ?></td>
                        <td><?php echo htmlspecialchars($delegation['delegated_by_name']); ?></td>
                        <td><?php echo date('M d, Y H:i', strtotime($delegation['delegation_date'])); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $delegation['status'] === 'active' ? 'success' : ($delegation['status'] === 'completed' ? 'info' : 'secondary'); ?>">
                                <?php echo ucfirst($delegation['status']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($delegation['notes'] ?: 'No notes'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Form submission
document.getElementById('delegationForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner mr-2"></span>Processing...';
    
    fetch('process_delegation.php', {
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
        showAlert('An error occurred. Please try again.', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

// Complete delegation
function completeDelegation(delegationId) {
    if (confirm('Are you sure you want to mark this delegation as completed?')) {
        fetch('process_delegation.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=complete&delegation_id=${delegationId}`
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
            showAlert('An error occurred. Please try again.', 'error');
        });
    }
}

// Cancel delegation
function cancelDelegation(delegationId) {
    if (confirm('Are you sure you want to cancel this delegation?')) {
        fetch('process_delegation.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=cancel&delegation_id=${delegationId}`
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
            showAlert('An error occurred. Please try again.', 'error');
        });
    }
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>