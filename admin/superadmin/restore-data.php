<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/DeletedDataBackup.php';

// Require superadmin authentication
Auth::requireRole('superadmin');

$backupModel = new DeletedDataBackup();
$currentUser = Auth::getCurrentUser();

// Handle restore action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $action = $_POST['action'];
    $requestId = isset($_POST['request_id']) ? (int)$_POST['request_id'] : 0;
    
    try {
        if ($action === 'restore_all' && $requestId) {
            $result = $backupModel->restoreAllByRequest($requestId, $currentUser['id']);
            
            if ($result['success']) {
                echo json_encode([
                    'success' => true,
                    'message' => 'All data restored successfully',
                    'restored' => $result['restored']
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Some data could not be restored',
                    'restored' => $result['restored'],
                    'errors' => $result['errors']
                ]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Get pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$result = $backupModel->getRestorableBackups($page, 20);
$stats = $backupModel->getStats();

$title = 'Restore Deleted Data';
ob_start();
?>

<div class="mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">🔄 Restore Deleted Data</h1>
    <p class="mt-2 text-sm text-gray-700">View and restore permanently deleted data from approved deletion requests</p>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 uppercase font-semibold">Total Backups</p>
                <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo number_format($stats['total_backups']); ?></p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M7 9a2 2 0 012-2h6a2 2 0 012 2v6a2 2 0 01-2 2H9a2 2 0 01-2-2V9z"></path>
                    <path d="M5 3a2 2 0 00-2 2v6a2 2 0 002 2V5h8a2 2 0 00-2-2H5z"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-green-200 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-green-700 uppercase font-semibold">Can Restore</p>
                <p class="text-2xl font-bold text-green-900 mt-1"><?php echo number_format($stats['can_restore']); ?></p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 uppercase font-semibold">Already Restored</p>
                <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo number_format($stats['already_restored']); ?></p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-purple-200 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-purple-700 uppercase font-semibold">Total Requests</p>
                <p class="text-2xl font-bold text-purple-900 mt-1"><?php echo number_format($stats['total_requests']); ?></p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-purple-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-orange-200 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-orange-700 uppercase font-semibold">Tables Affected</p>
                <p class="text-2xl font-bold text-orange-900 mt-1"><?php echo number_format($stats['tables_affected']); ?></p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-orange-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5 4a3 3 0 00-3 3v6a3 3 0 003 3h10a3 3 0 003-3V7a3 3 0 00-3-3H5zm-1 9v-1h5v2H5a1 1 0 01-1-1zm7 1h4a1 1 0 001-1v-1h-5v2zm0-4h5V8h-5v2zM9 8H4v2h5V8z" clip-rule="evenodd"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Restorable Backups Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested By</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tables</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Backups</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deleted At</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php 
                    if (empty($result['records'])):
                    ?>
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                                <p class="mt-2">No restorable backups found</p>
                            </td>
                        </tr>
                    <?php 
                    else:
                        $serial = (($result['page'] - 1) * $result['limit']) + 1;
                        foreach ($result['records'] as $record):
                    ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600"><?php echo $serial++; ?></td>
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($record['request_title']); ?></div>
                                <div class="text-xs text-gray-500"><?php echo ucwords(str_replace('_', ' ', $record['request_type'])); ?></div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                <?php echo htmlspecialchars($record['requested_by_name']); ?>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
                                    <?php 
                                    $tables = explode(',', $record['tables']);
                                    foreach ($tables as $table):
                                    ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                            <?php echo htmlspecialchars($table); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <?php echo $record['backup_count']; ?> records
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                <?php echo date('M d, Y h:i A', strtotime($record['deleted_at'])); ?>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                <button 
                                    onclick="restoreData(<?php echo $record['request_id']; ?>, '<?php echo htmlspecialchars($record['request_title'], ENT_QUOTES); ?>')"
                                    class="btn btn-sm btn-success"
                                    title="Restore All Data">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
                                    </svg>
                                    Restore
                                </button>
                            </td>
                        </tr>
                    <?php 
                        endforeach;
                    endif;
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination -->
<?php if ($result['pages'] > 1): ?>
<div class="mt-6 flex items-center justify-between">
    <div class="text-sm text-gray-700">
        Showing page <?php echo $result['page']; ?> of <?php echo $result['pages']; ?>
        (<?php echo number_format($result['total']); ?> total requests)
    </div>
    <div class="flex space-x-2">
        <?php if ($result['page'] > 1): ?>
            <a href="?page=<?php echo $result['page'] - 1; ?>" class="btn btn-secondary">Previous</a>
        <?php endif; ?>
        
        <?php if ($result['page'] < $result['pages']): ?>
            <a href="?page=<?php echo $result['page'] + 1; ?>" class="btn btn-secondary">Next</a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<script>
function restoreData(requestId, requestTitle) {
    showConfirmDialog(
        'Restore Deleted Data',
        `Are you sure you want to restore all data for "${requestTitle}"? This will recreate all deleted records.`,
        function() {
            const formData = new FormData();
            formData.append('action', 'restore_all');
            formData.append('request_id', requestId);
            
            fetch('restore-data.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert(data.message || 'Failed to restore data', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred while restoring data', 'error');
            });
        }
    );
}

function showConfirmDialog(title, message, onConfirm) {
    if (confirm(message)) {
        onConfirm();
    }
}

function showAlert(message, type) {
    alert(message);
}
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../includes/admin_layout.php';
?>
