<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/Installation.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$installationModel = new Installation();

// Get installation ID from URL
$installationId = $_GET['id'] ?? null;
if (!$installationId) {
    header('Location: index.php');
    exit;
}

// Get installation details
$installation = $installationModel->getInstallationDetails($installationId);
if (!$installation) {
    header('Location: index.php?error=Installation not found');
    exit;
}

// Get installation progress
$progress = $installationModel->getInstallationProgress($installationId);

$title = 'Installation Details - ' . ($installation['site_code'] ?? 'Unknown Site');
ob_start();
?>

<!-- Header Section -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
        <div class="flex-1">
            <div class="flex items-center space-x-4 mb-2">
                <a href="index.php" class="text-blue-600 hover:text-blue-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <h1 class="text-3xl font-bold text-gray-900">Installation Details</h1>
            </div>
            <p class="text-lg text-gray-600">Site: <?php echo htmlspecialchars($installation['site_code'] ?? 'Unknown'); ?></p>
            <p class="text-sm text-gray-500">Installation ID: <?php echo $installation['id']; ?></p>
        </div>
        <div class="mt-6 lg:mt-0 lg:ml-6">
            <?php
            $statusClass = '';
            $statusText = '';
            switch($installation['status']) {
                case 'assigned':
                    $statusClass = 'bg-blue-100 text-blue-800';
                    $statusText = 'Assigned';
                    break;
                case 'acknowledged':
                    $statusClass = 'bg-indigo-100 text-indigo-800';
                    $statusText = 'Acknowledged';
                    break;
                case 'in_progress':
                    $statusClass = 'bg-yellow-100 text-yellow-800';
                    $statusText = 'In Progress';
                    break;
                case 'on_hold':
                    $statusClass = 'bg-orange-100 text-orange-800';
                    $statusText = 'On Hold';
                    break;
                case 'completed':
                    $statusClass = 'bg-green-100 text-green-800';
                    $statusText = 'Completed';
                    break;
                case 'cancelled':
                    $statusClass = 'bg-red-100 text-red-800';
                    $statusText = 'Cancelled';
                    break;
                default:
                    $statusClass = 'bg-gray-100 text-gray-800';
                    $statusText = ucfirst($installation['status']);
            }
            ?>
            <span class="inline-flex items-center px-4 py-2 rounded-full text-lg font-medium <?php echo $statusClass; ?>">
                <?php echo $statusText; ?>
            </span>
        </div>
    </div>
</div>

<!-- Installation Information -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- Basic Information -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Installation Information</h3>
        <div class="space-y-3">
            <div class="flex justify-between">
                <span class="text-gray-500">Vendor:</span>
                <span class="font-medium"><?php echo htmlspecialchars($installation['vendor_name'] ?? 'Unknown'); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Priority:</span>
                <span class="font-medium capitalize"><?php echo $installation['priority']; ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Installation Type:</span>
                <span class="font-medium capitalize"><?php echo str_replace('_', ' ', $installation['installation_type']); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Delegated By:</span>
                <span class="font-medium"><?php echo htmlspecialchars($installation['delegated_by_name'] ?? 'Unknown'); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Delegation Date:</span>
                <span class="font-medium"><?php echo date('M j, Y g:i A', strtotime($installation['delegation_date'])); ?></span>
            </div>
        </div>
    </div>
    
    <!-- Site Information -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Site Information</h3>
        <div class="space-y-3">
            <div class="flex justify-between">
                <span class="text-gray-500">Site Code:</span>
                <span class="font-medium"><?php echo htmlspecialchars($installation['site_code'] ?? 'N/A'); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Location:</span>
                <span class="font-medium"><?php echo htmlspecialchars($installation['location'] ?? 'N/A'); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">City:</span>
                <span class="font-medium"><?php echo htmlspecialchars($installation['city_name'] ?? 'N/A'); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">State:</span>
                <span class="font-medium"><?php echo htmlspecialchars($installation['state_name'] ?? 'N/A'); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Remarks:</span>
                <span class="font-medium"><?php echo htmlspecialchars($installation['remarks'] ?? 'N/A'); ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Schedule Information -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Schedule Information</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div>
            <span class="text-gray-500 text-sm">Expected Start Date</span>
            <div class="font-medium">
                <?php echo $installation['expected_start_date'] ? date('M j, Y', strtotime($installation['expected_start_date'])) : 'Not set'; ?>
            </div>
        </div>
        <div>
            <span class="text-gray-500 text-sm">Expected Completion</span>
            <div class="font-medium">
                <?php echo $installation['expected_completion_date'] ? date('M j, Y', strtotime($installation['expected_completion_date'])) : 'Not set'; ?>
            </div>
        </div>
        <div>
            <span class="text-gray-500 text-sm">Actual Start Date</span>
            <div class="font-medium">
                <?php echo $installation['actual_start_date'] ? date('M j, Y', strtotime($installation['actual_start_date'])) : 'Not started'; ?>
            </div>
        </div>
        <div>
            <span class="text-gray-500 text-sm">Actual Completion</span>
            <div class="font-medium">
                <?php echo $installation['actual_completion_date'] ? date('M j, Y', strtotime($installation['actual_completion_date'])) : 'Not completed'; ?>
            </div>
        </div>
    </div>
</div>

<!-- Instructions and Notes -->
<?php if ($installation['special_instructions'] || $installation['notes']): ?>
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Instructions & Notes</h3>
    <div class="space-y-4">
        <?php if ($installation['special_instructions']): ?>
            <div>
                <h4 class="font-medium text-gray-900 mb-2">Special Instructions</h4>
                <p class="text-gray-700 bg-gray-50 p-3 rounded"><?php echo nl2br(htmlspecialchars($installation['special_instructions'])); ?></p>
            </div>
        <?php endif; ?>
        <?php if ($installation['notes']): ?>
            <div>
                <h4 class="font-medium text-gray-900 mb-2">Notes</h4>
                <p class="text-gray-700 bg-gray-50 p-3 rounded"><?php echo nl2br(htmlspecialchars($installation['notes'])); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Vendor Information -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Vendor Contact Information</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
            <span class="text-gray-500 text-sm">Vendor Name</span>
            <div class="font-medium"><?php echo htmlspecialchars($installation['vendor_name'] ?? 'Unknown'); ?></div>
        </div>
        <div>
            <span class="text-gray-500 text-sm">Email</span>
            <div class="font-medium">
                <?php if ($installation['vendor_email']): ?>
                    <a href="mailto:<?php echo htmlspecialchars($installation['vendor_email']); ?>" class="text-blue-600 hover:text-blue-800">
                        <?php echo htmlspecialchars($installation['vendor_email']); ?>
                    </a>
                <?php else: ?>
                    Not available
                <?php endif; ?>
            </div>
        </div>
        <div>
            <span class="text-gray-500 text-sm">Phone</span>
            <div class="font-medium">
                <?php if ($installation['vendor_phone']): ?>
                    <a href="tel:<?php echo htmlspecialchars($installation['vendor_phone']); ?>" class="text-blue-600 hover:text-blue-800">
                        <?php echo htmlspecialchars($installation['vendor_phone']); ?>
                    </a>
                <?php else: ?>
                    Not available
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Daily Work Progress & Material Usage -->
<?php
// Get daily work and material usage data
require_once __DIR__ . '/../../models/MaterialUsage.php';
$materialUsageModel = new MaterialUsage();
$dailyWork = $materialUsageModel->getDailyWorkByDay($installationId);
$materialSummary = $materialUsageModel->getMaterialUsageSummary($installationId);
?>

<?php if (!empty($materialSummary)): ?>
<!-- Material Summary -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Material Usage Summary</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Material Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Qty</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Used Qty</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remaining</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($materialSummary as $material): 
                    $remaining = $material['total_quantity'] - $material['used_quantity'];
                    $usagePercentage = $material['total_quantity'] > 0 ? ($material['used_quantity'] / $material['total_quantity']) * 100 : 0;
                    
                    // Determine status color
                    if ($remaining <= 0) {
                        $statusClass = 'bg-red-100 text-red-800';
                        $statusText = 'Out of Stock';
                    } elseif ($remaining <= 5) {
                        $statusClass = 'bg-yellow-100 text-yellow-800';
                        $statusText = 'Low Stock';
                    } else {
                        $statusClass = 'bg-green-100 text-green-800';
                        $statusText = 'Available';
                    }
                ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        <?php echo htmlspecialchars($material['material_name']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?php echo htmlspecialchars($material['material_unit']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            <?php echo $material['total_quantity']; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 mr-2">
                                <?php echo $material['used_quantity']; ?>
                            </span>
                            <div class="w-16 bg-gray-200 rounded-full h-2">
                                <div class="bg-orange-600 h-2 rounded-full" style="width: <?php echo min(100, $usagePercentage); ?>%"></div>
                            </div>
                            <span class="ml-2 text-xs text-gray-500"><?php echo round($usagePercentage, 1); ?>%</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $remaining <= 5 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?>">
                            <?php echo $remaining; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $statusClass; ?>">
                            <?php echo $statusText; ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($dailyWork)): ?>
<!-- Daily Work Progress -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Daily Work Progress</h3>
    <div class="space-y-6">
        <?php foreach ($dailyWork as $day): 
            // Get material usage for this day
            $dayMaterials = $materialUsageModel->getDailyMaterialUsage($installationId, $day['day_number']);
            // Get photos for this day
            $dayPhotos = $materialUsageModel->getDailyWorkPhotos($installationId, $day['day_number']);
        ?>
        <div class="border border-gray-200 rounded-lg overflow-hidden">
            <!-- Day Header -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h4 class="text-lg font-medium text-gray-900">
                            Day <?php echo $day['day_number']; ?> - <?php echo date('M j, Y', strtotime($day['work_date'])); ?>
                        </h4>
                        <?php if ($day['engineer_name']): ?>
                            <p class="text-sm text-gray-600 mt-1">
                                <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                </svg>
                                Engineer: <?php echo htmlspecialchars($day['engineer_name']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <div class="flex items-center space-x-2">
                        <?php if ($day['is_checked_out']): ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Completed
                            </span>
                            <?php if ($day['checked_out_at']): ?>
                                <span class="text-xs text-gray-500">
                                    <?php echo date('g:i A', strtotime($day['checked_out_at'])); ?>
                                </span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                In Progress
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Day Content -->
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Work Details -->
                    <div>
                        <?php if ($day['remarks']): ?>
                            <div class="mb-4">
                                <h5 class="text-sm font-medium text-gray-900 mb-2">Work Remarks</h5>
                                <div class="bg-gray-50 rounded-md p-3 text-sm text-gray-700">
                                    <?php echo nl2br(htmlspecialchars($day['remarks'])); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($day['work_report']): ?>
                            <div class="mb-4">
                                <h5 class="text-sm font-medium text-gray-900 mb-2">Detailed Report</h5>
                                <div class="bg-gray-50 rounded-md p-3 text-sm text-gray-700">
                                    <?php echo nl2br(htmlspecialchars($day['work_report'])); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Material Usage for this day -->
                        <?php if (!empty($dayMaterials)): ?>
                            <div>
                                <h5 class="text-sm font-medium text-gray-900 mb-3">Materials Used</h5>
                                <div class="space-y-2">
                                    <?php foreach ($dayMaterials as $material): ?>
                                        <div class="flex justify-between items-center py-2 px-3 bg-blue-50 rounded-md">
                                            <span class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($material['material_name']); ?>
                                            </span>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <?php echo $material['quantity_used']; ?> <?php echo htmlspecialchars($material['material_unit'] ?? 'units'); ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Photos/Videos -->
                    <div>
                        <?php if (!empty($dayPhotos)): ?>
                            <h5 class="text-sm font-medium text-gray-900 mb-3">Work Documentation</h5>
                            <div class="grid grid-cols-2 gap-3">
                                <?php foreach ($dayPhotos as $photo): ?>
                                    <div class="relative group">
                                        <?php if (strpos($photo['file_type'], 'image') === 0): ?>
                                            <img src="<?php echo htmlspecialchars($photo['file_path']); ?>" 
                                                 alt="Work photo" 
                                                 class="w-full h-24 object-cover rounded-lg shadow-sm cursor-pointer hover:shadow-md transition-shadow"
                                                 onclick="openImageModal('<?php echo htmlspecialchars($photo['file_path']); ?>', '<?php echo htmlspecialchars($photo['file_name']); ?>')">
                                        <?php elseif (strpos($photo['file_type'], 'video') === 0): ?>
                                            <video src="<?php echo htmlspecialchars($photo['file_path']); ?>" 
                                                   class="w-full h-24 object-cover rounded-lg shadow-sm cursor-pointer hover:shadow-md transition-shadow"
                                                   onclick="openVideoModal('<?php echo htmlspecialchars($photo['file_path']); ?>', '<?php echo htmlspecialchars($photo['file_name']); ?>')">
                                        <?php endif; ?>
                                        
                                        <!-- File info overlay -->
                                        <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white text-xs p-2 rounded-b-lg opacity-0 group-hover:opacity-100 transition-opacity">
                                            <div class="truncate"><?php echo htmlspecialchars($photo['file_name']); ?></div>
                                            <div><?php echo date('M j, g:i A', strtotime($photo['uploaded_at'])); ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-8 text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                                </svg>
                                <p class="text-sm">No photos/videos uploaded for this day</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Progress History with Attachments -->
<?php 
// Get progress with attachments
$progressWithAttachments = $installationModel->getInstallationProgressWithAttachments($installationId);
?>
<?php if (!empty($progressWithAttachments)): ?>
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Progress History & Attachments</h3>
    <div class="space-y-6">
        <?php foreach ($progressWithAttachments as $entry): ?>
            <div class="border border-gray-200 rounded-lg p-4">
                <!-- Progress Header -->
                <div class="flex justify-between items-start mb-4">
                    <div class="flex-1">
                        <div class="flex items-center space-x-3 mb-2">
                            <div class="font-medium text-gray-900 text-lg">
                                Progress: <?php echo $entry['progress_percentage']; ?>%
                            </div>
                            <div class="w-32 bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: <?php echo $entry['progress_percentage']; ?>%"></div>
                            </div>
                        </div>
                        <div class="text-sm text-gray-600 mb-2">
                            <?php echo htmlspecialchars($entry['work_description'] ?? 'No description'); ?>
                        </div>
                        <?php if ($entry['issues_faced']): ?>
                            <div class="text-sm text-red-600 mb-2">
                                <strong>Issues:</strong> <?php echo htmlspecialchars($entry['issues_faced']); ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($entry['next_steps']): ?>
                            <div class="text-sm text-blue-600 mb-2">
                                <strong>Next Steps:</strong> <?php echo htmlspecialchars($entry['next_steps']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="text-right text-sm text-gray-500">
                        <div class="font-medium"><?php echo date('M j, Y', strtotime($entry['progress_date'])); ?></div>
                        <div><?php echo date('g:i A', strtotime($entry['progress_date'])); ?></div>
                        <div>by <?php echo htmlspecialchars($entry['updated_by_name'] ?? 'Unknown'); ?></div>
                        <?php if ($entry['total_attachments'] > 0): ?>
                            <div class="mt-2">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8 4a3 3 0 00-6 0v4a5 5 0 0010 0V4a3 3 0 00-6 0v4a1 1 0 102 0V4a1 1 0 10-2 0v4a3 3 0 01-6 0V4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <?php echo $entry['total_attachments']; ?> files
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Attachments -->
                <?php if (!empty($entry['attachments'])): ?>
                    <div class="border-t border-gray-200 pt-4">
                        <h5 class="text-sm font-medium text-gray-900 mb-3">Attachments</h5>
                        
                        <?php 
                        // Group attachments by type
                        $attachmentsByType = [];
                        foreach ($entry['attachments'] as $attachment) {
                            $attachmentsByType[$attachment['attachment_type']][] = $attachment;
                        }
                        ?>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <?php foreach ($attachmentsByType as $type => $attachments): ?>
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <h6 class="text-xs font-medium text-gray-700 mb-2 uppercase tracking-wide">
                                        <?php 
                                        $typeLabels = [
                                            'final_report' => 'Final Report',
                                            'site_snaps' => 'Site Snaps',
                                            'excel_sheet' => 'Excel/Data Files',
                                            'drawing_attachment' => 'Drawings'
                                        ];
                                        echo $typeLabels[$type] ?? ucfirst(str_replace('_', ' ', $type));
                                        ?>
                                        <span class="ml-1 text-gray-500">(<?php echo count($attachments); ?>)</span>
                                    </h6>
                                    
                                    <div class="space-y-2">
                                        <?php foreach ($attachments as $attachment): ?>
                                            <div class="flex items-center space-x-2 p-2 bg-white rounded border">
                                                <?php if (strpos($attachment['mime_type'], 'image') === 0): ?>
                                                    <img src="<?php echo htmlspecialchars($attachment['file_path']); ?>" 
                                                         alt="<?php echo htmlspecialchars($attachment['original_name']); ?>"
                                                         class="w-8 h-8 object-cover rounded cursor-pointer"
                                                         onclick="openImageModal('<?php echo htmlspecialchars($attachment['file_path']); ?>', '<?php echo htmlspecialchars($attachment['original_name']); ?>')">
                                                <?php else: ?>
                                                    <div class="w-8 h-8 bg-gray-200 rounded flex items-center justify-center">
                                                        <svg class="w-4 h-4 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 0v12h8V6H8a2 2 0 01-2-2z" clip-rule="evenodd"></path>
                                                        </svg>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-xs font-medium text-gray-900 truncate" title="<?php echo htmlspecialchars($attachment['original_name']); ?>">
                                                        <?php echo htmlspecialchars($attachment['original_name']); ?>
                                                    </p>
                                                    <p class="text-xs text-gray-500">
                                                        <?php echo formatFileSize($attachment['file_size']); ?>
                                                    </p>
                                                </div>
                                                
                                                <a href="<?php echo htmlspecialchars($attachment['file_path']); ?>" 
                                                   download="<?php echo htmlspecialchars($attachment['original_name']); ?>"
                                                   class="text-blue-600 hover:text-blue-800"
                                                   title="Download">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                    </svg>
                                                </a>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if (!empty($entry['attachment_description'])): ?>
                            <div class="mt-3 p-3 bg-blue-50 rounded-md">
                                <p class="text-sm text-blue-800">
                                    <strong>Description:</strong> <?php echo htmlspecialchars($entry['attachment_description']); ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
// Helper function for file size formatting
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}
?>

<?php elseif (!empty($progress)): ?>
<!-- Fallback to regular progress if no attachments -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Progress History</h3>
    <div class="space-y-4">
        <?php foreach ($progress as $entry): ?>
            <div class="border-l-4 border-blue-500 pl-4 py-2">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <div class="font-medium text-gray-900">
                            Progress: <?php echo $entry['progress_percentage']; ?>%
                        </div>
                        <div class="text-sm text-gray-600 mt-1">
                            <?php echo htmlspecialchars($entry['work_description'] ?? 'No description'); ?>
                        </div>
                        <?php if ($entry['issues_faced']): ?>
                            <div class="text-sm text-red-600 mt-1">
                                <strong>Issues:</strong> <?php echo htmlspecialchars($entry['issues_faced']); ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($entry['next_steps']): ?>
                            <div class="text-sm text-blue-600 mt-1">
                                <strong>Next Steps:</strong> <?php echo htmlspecialchars($entry['next_steps']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="text-right text-sm text-gray-500">
                        <div><?php echo date('M j, Y', strtotime($entry['progress_date'])); ?></div>
                        <div>by <?php echo htmlspecialchars($entry['updated_by_name'] ?? 'Unknown'); ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="max-w-4xl max-h-full p-4">
        <div class="relative">
            <img id="modalImage" src="" alt="" class="max-w-full max-h-full rounded-lg">
            <button onclick="closeImageModal()" class="absolute top-4 right-4 text-white bg-black bg-opacity-50 rounded-full p-2 hover:bg-opacity-75">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
            <div id="imageCaption" class="absolute bottom-4 left-4 right-4 text-white bg-black bg-opacity-50 rounded p-2 text-center"></div>
        </div>
    </div>
</div>

<!-- Video Modal -->
<div id="videoModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="max-w-4xl max-h-full p-4">
        <div class="relative">
            <video id="modalVideo" controls class="max-w-full max-h-full rounded-lg">
                Your browser does not support the video tag.
            </video>
            <button onclick="closeVideoModal()" class="absolute top-4 right-4 text-white bg-black bg-opacity-50 rounded-full p-2 hover:bg-opacity-75">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
            <div id="videoCaption" class="absolute bottom-4 left-4 right-4 text-white bg-black bg-opacity-50 rounded p-2 text-center"></div>
        </div>
    </div>
</div>

<script>
function openImageModal(imageSrc, caption) {
    document.getElementById('modalImage').src = imageSrc;
    document.getElementById('imageCaption').textContent = caption;
    document.getElementById('imageModal').classList.remove('hidden');
}

function closeImageModal() {
    document.getElementById('imageModal').classList.add('hidden');
    document.getElementById('modalImage').src = '';
}

function openVideoModal(videoSrc, caption) {
    document.getElementById('modalVideo').src = videoSrc;
    document.getElementById('videoCaption').textContent = caption;
    document.getElementById('videoModal').classList.remove('hidden');
}

function closeVideoModal() {
    document.getElementById('videoModal').classList.add('hidden');
    document.getElementById('modalVideo').src = '';
    document.getElementById('modalVideo').pause();
}

// Close modals when clicking outside
document.getElementById('imageModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeImageModal();
    }
});

document.getElementById('videoModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeVideoModal();
    }
});

// Close modals with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeImageModal();
        closeVideoModal();
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>