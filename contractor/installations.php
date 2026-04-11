<?php
require_once __DIR__ . '/../config/auth.php';
// constants.php is already included by auth.php
require_once __DIR__ . '/../models/Installation.php';

// Require vendor authentication
Auth::requireVendor();

$vendorId = Auth::getVendorId();
$installationModel = new Installation();

// Get all installations assigned to this vendor
$installations = $installationModel->getVendorInstallations($vendorId);
$stats = $installationModel->getVendorInstallationStats($vendorId);

$title = 'My Installations';
ob_start();
?>

<style>
/* Compact table styling */
table td, table th {
    white-space: normal !important;
}

.installations-table td {
    padding: 0.75rem 1rem !important;
    vertical-align: middle;
}

.installations-table th {
    padding: 0.75rem 1rem !important;
}

.installations-table tbody tr:hover {
    background-color: #f9fafb;
}
</style>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">My Installations</h1>
            <p class="mt-1 text-sm text-gray-600">Manage your assigned installation projects</p>
        </div>
        <button onclick="refreshInstallations()" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
            </svg>
            Refresh
        </button>
    </div>
</div>

<!-- Installations Table -->
<div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 installations-table">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">ID</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Site Code</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Location</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Priority</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Expected Start</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Expected End</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($installations)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center justify-center space-y-4">
                                <div class="w-20 h-20 bg-gradient-to-br from-blue-100 to-blue-200 rounded-full flex items-center justify-center">
                                    <svg class="w-10 h-10 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                </div>
                                <div class="text-center">
                                    <h3 class="text-lg font-semibold text-gray-900">No installations assigned</h3>
                                    <p class="mt-2 text-sm text-gray-600 max-w-md">You don't have any installation tasks assigned yet. New assignments will appear here when they become available.</p>
                                </div>
                                <div class="flex items-center space-x-2 text-xs text-gray-500">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span>Check back later for new assignments</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($installations as $installation): ?>
                        <tr class="hover:bg-gray-50">
                            <!-- ID -->
                            <td class="px-4 py-3">
                                <span class="text-sm font-semibold text-gray-900">#<?php echo $installation['id']; ?></span>
                            </td>
                            
                            <!-- Site Code -->
                            <td class="px-4 py-3">
                                <span class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($installation['site_code'] ?? 'N/A'); ?></span>
                            </td>
                            
                            <!-- Location -->
                            <td class="px-4 py-3">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($installation['location'] ?? 'N/A'); ?></div>
                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($installation['city_name'] . ', ' . $installation['state_name']); ?></div>
                            </td>
                            
                            <!-- Type -->
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                    <?php echo ucfirst($installation['installation_type']); ?>
                                </span>
                            </td>
                            
                            <!-- Priority -->
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold
                                    <?php 
                                    switch($installation['priority']) {
                                        case 'urgent': echo 'bg-red-100 text-red-800'; break;
                                        case 'high': echo 'bg-orange-100 text-orange-800'; break;
                                        case 'medium': echo 'bg-yellow-100 text-yellow-800'; break;
                                        default: echo 'bg-gray-100 text-gray-800'; break;
                                    }
                                    ?>">
                                    <?php echo ucfirst($installation['priority']); ?>
                                </span>
                            </td>
                            
                            <!-- Expected Start -->
                            <td class="px-4 py-3">
                                <span class="text-sm text-gray-900">
                                    <?php echo $installation['expected_start_date'] ? date('M j, Y', strtotime($installation['expected_start_date'])) : '-'; ?>
                                </span>
                            </td>
                            
                            <!-- Expected End -->
                            <td class="px-4 py-3">
                                <span class="text-sm text-gray-900">
                                    <?php echo $installation['expected_completion_date'] ? date('M j, Y', strtotime($installation['expected_completion_date'])) : '-'; ?>
                                </span>
                            </td>
                            
                            <!-- Status -->
                            <td class="px-4 py-3">
                                <?php
                                $status = $installation['status'];
                                switch($status) {
                                    case 'completed':
                                        echo '<span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-green-100 text-green-800">Completed</span>';
                                        break;
                                    case 'in_progress':
                                        echo '<span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-blue-100 text-blue-800">In Progress</span>';
                                        break;
                                    case 'on_hold':
                                        echo '<span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-yellow-100 text-yellow-800">On Hold</span>';
                                        break;
                                    case 'cancelled':
                                        echo '<span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-red-100 text-red-800">Cancelled</span>';
                                        break;
                                    case 'acknowledged':
                                        echo '<span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-purple-100 text-purple-800">Acknowledged</span>';
                                        break;
                                    case 'assigned':
                                        echo '<span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-indigo-100 text-indigo-800">Assigned</span>';
                                        break;
                                    default:
                                        echo '<span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-gray-100 text-gray-800">' . ucfirst(str_replace('_', ' ', $status)) . '</span>';
                                }
                                ?>
                            </td>
                            
                            <!-- Actions -->
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <?php if ($installation['survey_id']): ?>
                                        <a href="../shared/view-survey.php?id=<?php echo $installation['survey_id']; ?>" 
                                           class="inline-flex items-center p-1.5 border border-green-300 rounded text-green-700 bg-green-50 hover:bg-green-100" 
                                           title="View Site Survey">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                                                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                                            </svg>
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($installation['status'] === 'completed'): ?>
                                        <a href="../shared/view-installation.php?id=<?php echo $installation['id']; ?>" 
                                           class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded text-xs font-medium text-gray-700 bg-white hover:bg-gray-50" 
                                           title="View Details">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            View
                                        </a>
                                    <?php else: ?>
                                        <button onclick="viewInstallation(<?php echo $installation['id']; ?>)" 
                                                class="inline-flex items-center p-1.5 border border-gray-300 rounded text-gray-700 bg-white hover:bg-gray-50" 
                                                title="View">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                            </svg>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if ($installation['status'] === 'assigned'): ?>
                                        <button onclick="acknowledgeInstallation(<?php echo $installation['id']; ?>)" 
                                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-blue-600 hover:bg-blue-700" 
                                                title="Acknowledge">
                                            Acknowledge
                                        </button>
                                    <?php elseif (in_array($installation['status'], ['acknowledged', 'in_progress'])): ?>
                                        <button onclick="manageInstallation(<?php echo $installation['id']; ?>)" 
                                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-green-600 hover:bg-green-700" 
                                                title="Manage">
                                            Manage
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function refreshInstallations() {
    location.reload();
}

function viewInstallation(installationId) {
    window.location.href = 'manage-installation.php?id=' + installationId;
}

function acknowledgeInstallation(installationId) {
    if (confirm('Are you sure you want to acknowledge this installation assignment?')) {
        fetch('process-installation-action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'acknowledge',
                installation_id: installationId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Installation acknowledged successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing the request.');
        });
    }
}

function manageInstallation(installationId) {
    window.location.href = 'manage-installation.php?id=' + installationId;
}
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../includes/vendor_layout.php';
?>