<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/Site.php';
require_once __DIR__ . '/../../models/Vendor.php';
require_once __DIR__ . '/../../models/Installation.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$siteId = $_GET['site_id'] ?? null;
$surveyId = $_GET['survey_id'] ?? null;

if (!$siteId) {
    header('Location: index.php');
    exit;
}

$siteModel = new Site();
$vendorModel = new Vendor();
$instModel = new Installation();

$site = $siteModel->findWithRelations($siteId);
if (!$site) {
    header('Location: index.php');
    exit;
}

// Get active installation delegation if any
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT * FROM installation_delegations WHERE site_id = ? AND status != 'cancelled' ORDER BY id DESC LIMIT 1");
$stmt->execute([$siteId]);
$activeDelegation = $stmt->fetch(PDO::FETCH_ASSOC);

if ($activeDelegation) {
    // Get vendor name for active delegation
    $vStmt = $db->prepare("SELECT name, company_name FROM vendors WHERE id = ?");
    $vStmt->execute([$activeDelegation['vendor_id']]);
    $vData = $vStmt->fetch(PDO::FETCH_ASSOC);
    $activeDelegation['vendor_name'] = $vData['company_name'] ?: $vData['name'];
}

$vendors = $vendorModel->getActiveVendors();
$delegationHistory = $instModel->getInstallationHistory($siteId);

$title = 'Delegate Installation - ' . $site['site_id'];
ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Delegate Installation</h1>
        <p class="mt-1 text-sm text-gray-500">Assign site to vendor for implementation/installation</p>
    </div>
    <div>
        <a href="index.php" class="btn btn-secondary flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Dashboard
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Site & Survey Info -->
    <div class="lg:col-span-1 space-y-6">
        <div class="card shadow-sm border-gray-200">
            <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/50">
                <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Site Information</h3>
            </div>
            <div class="card-body p-4 space-y-4">
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase">Site ID</label>
                    <div class="text-sm font-bold text-blue-600"><?php echo htmlspecialchars($site['site_id']); ?></div>
                </div>
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase">Location</label>
                    <div class="text-xs text-gray-700"><?php echo htmlspecialchars($site['location']); ?></div>
                    <div class="text-[10px] text-gray-500"><?php echo htmlspecialchars($site['city_name']); ?>, <?php echo htmlspecialchars($site['state_name']); ?></div>
                </div>
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase">Customer</label>
                    <div class="text-xs font-semibold text-gray-800"><?php echo htmlspecialchars($site['customer_name']); ?></div>
                </div>
            </div>
        </div>

        <?php if ($activeDelegation): ?>
        <div class="card shadow-sm border-indigo-200 bg-indigo-50/30">
            <div class="px-4 py-3 border-b border-indigo-100 bg-indigo-50">
                <h3 class="text-sm font-bold text-indigo-700 uppercase tracking-wider">Active Delegation</h3>
            </div>
            <div class="card-body p-4 space-y-4">
                <div>
                    <label class="text-[10px] font-bold text-indigo-400 uppercase">Assigned To</label>
                    <div class="text-sm font-bold text-indigo-900"><?php echo htmlspecialchars($activeDelegation['vendor_name']); ?></div>
                </div>
                <div>
                    <label class="text-[10px] font-bold text-indigo-400 uppercase">Date</label>
                    <div class="text-xs text-indigo-700"><?php echo date('d M Y, H:i', strtotime($activeDelegation['delegation_date'])); ?></div>
                </div>
                <div>
                    <label class="text-[10px] font-bold text-indigo-400 uppercase">Status</label>
                    <div class="mt-1">
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase bg-indigo-100 text-indigo-700">
                            <?php echo $activeDelegation['status']; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Delegation Form -->
    <div class="lg:col-span-2">
        <div class="card shadow-md border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 bg-white">
                <h3 class="text-lg font-bold text-gray-800">Assign Installation Vendor</h3>
            </div>
            <div class="card-body p-6">
                <form id="installationForm" action="process-installation-delegation-sites.php" method="POST">
                    <input type="hidden" name="site_id" value="<?php echo $siteId; ?>">
                    <input type="hidden" name="survey_id" value="<?php echo $surveyId; ?>">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="form-group">
                            <label for="vendor_id" class="form-label font-bold text-gray-700">Select Vendor *</label>
                            <select name="vendor_id" id="vendor_id" class="form-select border-gray-300 focus:ring-purple-500 focus:border-purple-500 rounded-lg" required>
                                <option value="">-- Choose Installation Vendor --</option>
                                <?php foreach ($vendors as $v): ?>
                                    <option value="<?php echo $v['id']; ?>" <?php echo ($activeDelegation && $activeDelegation['vendor_id'] == $v['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars(($v['company_name'] ?? '') ?: ($v['name'] ?? '')); ?> 
                                        <?php if (!empty($v['contact_person'])): ?>
                                            - <?php echo htmlspecialchars($v['contact_person']); ?>
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="priority" class="form-label font-bold text-gray-700">Priority</label>
                            <select name="priority" id="priority" class="form-select border-gray-300 rounded-lg">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="expected_start_date" class="form-label font-bold text-gray-700">Expected Start Date</label>
                            <input type="date" name="expected_start_date" id="expected_start_date" class="form-input rounded-lg" value="<?php echo date('Y-m-d'); ?>">
                        </div>

                        <div class="form-group">
                            <label for="expected_completion_date" class="form-label font-bold text-gray-700">Expected Completion Date</label>
                            <input type="date" name="expected_completion_date" id="expected_completion_date" class="form-input rounded-lg" value="<?php echo date('Y-m-d', strtotime('+3 days')); ?>">
                        </div>

                        <div class="form-group md:col-span-2">
                            <label for="notes" class="form-label font-bold text-gray-700">Special Instructions / Notes</label>
                            <textarea name="notes" id="notes" rows="4" class="form-textarea border-gray-300 rounded-lg" placeholder="Enter any specific requirements for this installation..."></textarea>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-100 flex justify-end gap-3">
                        <a href="index.php" class="btn btn-secondary px-6 font-bold">Cancel</a>
                        <button type="submit" class="btn btn-primary bg-purple-600 hover:bg-purple-700 border-none shadow-lg shadow-purple-100 px-8 font-bold">
                            <?php echo $activeDelegation ? 'Update Delegation' : 'Assign Vendor'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- History Section -->
<?php if (!empty($delegationHistory)): ?>
<div class="card shadow-sm border-gray-200">
    <div class="px-6 py-4 border-b border-gray-200 bg-white">
        <h3 class="text-lg font-bold text-gray-800">Installation Delegation History</h3>
    </div>
    <div class="card-body p-0 overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Vendor</th>
                    <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Delegated By</th>
                    <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Notes</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($delegationHistory as $hist): ?>
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="text-sm font-bold text-gray-900"><?php echo htmlspecialchars($hist['vendor_name']); ?></div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-600"><?php echo htmlspecialchars($hist['delegated_by_name']); ?></div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        <?php echo date('d M Y, H:i', strtotime($hist['delegation_date'])); ?>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase <?php 
                            echo $hist['status'] === 'completed' ? 'bg-green-100 text-green-700' : 
                                ($hist['status'] === 'cancelled' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700'); 
                        ?>">
                            <?php echo $hist['status']; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 italic max-w-xs truncate">
                        <?php echo htmlspecialchars($hist['notes'] ?: 'No notes'); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<script>
document.getElementById('installationForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full mr-2"></span> Processing...';

    try {
        const response = await fetch('process-installation-delegation-sites.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            showToast(result.message, 'success');
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 2000);
        } else {
            showToast(result.message, 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('An unexpected error occurred.', 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../includes/admin_layout.php';
?>
