<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../models/Inventory.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$dispatchId = $_GET['id'] ?? null;
if (!$dispatchId) { header('Location: index.php'); exit; }

$inventoryModel = new Inventory();
$dispatch = $inventoryModel->getDispatchDetails($dispatchId);
if (!$dispatch) { header('Location: index.php?error=dispatch_not_found'); exit; }

$deliveryConfirmation = $inventoryModel->getDeliveryConfirmationDetails($dispatchId);
$hasDocuments = $inventoryModel->hasUploadedDocuments($dispatchId);

$title = 'Logistics Details - ' . $dispatch['dispatch_number'];
ob_start();
?>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
    <div>
        <div class="flex items-center gap-3 mb-1">
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Dispatch Manifest</h1>
            <span class="px-2.5 py-1 bg-gray-900 text-white rounded-lg text-[10px] font-bold uppercase tracking-widest"><?php echo htmlspecialchars($dispatch['dispatch_number']); ?></span>
        </div>
        <p class="text-[13px] font-medium text-gray-500 uppercase tracking-wide">Transit & Delivery Intelligence Report</p>
    </div>
    <div class="flex items-center gap-3">
        <button onclick="window.print()" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 rounded-xl text-xs font-bold uppercase tracking-wider transition-all shadow-sm flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Print Ticket
        </button>
        <a href="index.php" class="p-2.5 bg-white border border-gray-200 text-gray-400 hover:text-gray-900 hover:border-gray-900 rounded-xl transition-all shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
        </a>
    </div>
</div>

<!-- Status Metrics -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Operational Status</p>
        <?php
        $actualStatus = $dispatch['dispatch_status'] ?: ($deliveryConfirmation ? ($deliveryConfirmation['confirmation_date'] ? 'confirmed' : 'delivered') : 'dispatched');
        $statusMap = [
            'prepared' => ['color' => 'blue', 'label' => 'Requisition Prepared'],
            'dispatched' => ['color' => 'amber', 'label' => 'Sent to Courier'],
            'in_transit' => ['color' => 'indigo', 'label' => 'In Transit'],
            'delivered' => ['color' => 'emerald', 'label' => 'Gate Entry Done'],
            'confirmed' => ['color' => 'emerald', 'label' => 'Fully Confirmed'],
            'returned' => ['color' => 'rose', 'label' => 'Returning Inbound']
        ];
        $st = $statusMap[$actualStatus] ?? ['color' => 'gray', 'label' => $actualStatus];
        ?>
        <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-<?php echo $st['color']; ?>-500 animate-pulse"></span>
            <span class="text-sm font-bold text-gray-900 uppercase tracking-tight"><?php echo $st['label']; ?></span>
        </div>
    </div>
    
    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Associated Request</p>
        <div class="text-sm font-bold text-gray-900">REQ#<?php echo $dispatch['material_request_id'] ?: 'EXT-00'; ?></div>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Dispatch Timestamp</p>
        <div class="text-sm font-bold text-gray-900"><?php echo date('d M Y, h:i A', strtotime($dispatch['dispatch_date'])); ?></div>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Manifest Volume</p>
        <div class="text-sm font-bold text-gray-900"><?php echo count($dispatch['items']); ?> <span class="text-gray-400">Inventory Units</span></div>
    </div>
</div>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Logistics Strategy Card -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-200">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Logistics Strategy</h3>
        </div>
        <div class="p-6 space-y-4">
            <div class="flex justify-between items-center py-1">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-tight">Destination Site</span>
                <span class="text-sm font-bold text-gray-900"><?php echo htmlspecialchars($dispatch['site_code'] ?: 'N/A'); ?></span>
            </div>
            <div class="flex justify-between items-center py-1">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-tight">Contractor Partner</span>
                <span class="text-sm font-bold text-indigo-600">
                    <?php echo htmlspecialchars($dispatch['vendor_company_name'] ?: ($dispatch['vendor_name'] ?: 'Corporate Logistics')); ?>
                </span>
            </div>
            <div class="flex justify-between items-center py-1">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-tight">Expected Arrival</span>
                <span class="text-sm font-bold text-rose-600">
                    <?php echo $dispatch['expected_delivery_date'] ? date('d M Y', strtotime($dispatch['expected_delivery_date'])) : '--'; ?>
                </span>
            </div>
            <div class="flex justify-between items-center py-1">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-tight">Carrier Body</span>
                <span class="text-sm font-bold text-gray-900"><?php echo htmlspecialchars($dispatch['courier_name'] ?: 'Internal Transit'); ?></span>
            </div>
            <div class="flex justify-between items-center py-1">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-tight">Tracking Ledger</span>
                <span class="text-sm font-bold text-blue-600 font-mono"><?php echo htmlspecialchars($dispatch['tracking_number'] ?: 'NOT_ASSIGNED'); ?></span>
            </div>
        </div>
    </div>

    <!-- Contact & Recovery Card -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-200">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Recovery & Contact</h3>
        </div>
        <div class="p-6 space-y-4">
            <div class="flex justify-between items-center py-1">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-tight">PoC Identity</span>
                <span class="text-sm font-bold text-gray-900"><?php echo htmlspecialchars($dispatch['contact_person_name']); ?></span>
            </div>
            <div class="flex justify-between items-center py-1">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-tight">Response Line</span>
                <span class="text-sm font-bold text-gray-900"><?php echo htmlspecialchars($dispatch['contact_person_phone'] ?: '--'); ?></span>
            </div>
            <div class="pt-2">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-tight block mb-2">Primary Delivery Node</span>
                <div class="bg-gray-50 p-4 rounded-xl text-sm font-semibold text-gray-600 leading-relaxed italic border border-gray-100">
                    <?php echo nl2br(htmlspecialchars($dispatch['delivery_address'])); ?>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Delivery Confirmation Section -->
    <?php if ($deliveryConfirmation && is_array($deliveryConfirmation) && ($deliveryConfirmation['delivery_date'] || $deliveryConfirmation['received_by'])): ?>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <svg class="w-5 h-5 text-emerald-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                Delivery Confirmation
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (isset($deliveryConfirmation['delivery_date']) && $deliveryConfirmation['delivery_date']): ?>
                <div class="bg-emerald-50 p-4 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-emerald-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm font-medium text-emerald-800">Delivery Date</span>
                    </div>
                    <p class="mt-2 text-lg font-semibold text-emerald-900">
                        <?php echo date('d M Y', strtotime($deliveryConfirmation['delivery_date'])); ?>
                        <?php if (isset($deliveryConfirmation['delivery_time']) && $deliveryConfirmation['delivery_time']): ?>
                            <span class="text-sm font-normal">at <?php echo date('H:i', strtotime($deliveryConfirmation['delivery_time'])); ?></span>
                        <?php endif; ?>
                    </p>
                </div>
                <?php endif; ?>
                
                <?php if (isset($deliveryConfirmation['received_by']) && $deliveryConfirmation['received_by']): ?>
                <div class="bg-blue-50 p-4 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm font-medium text-blue-800">Received By</span>
                    </div>
                    <p class="mt-2 text-lg font-semibold text-blue-900"><?php echo htmlspecialchars($deliveryConfirmation['received_by']); ?></p>
                    <?php if (isset($deliveryConfirmation['received_by_phone']) && $deliveryConfirmation['received_by_phone']): ?>
                        <p class="text-sm text-blue-700"><?php echo htmlspecialchars($deliveryConfirmation['received_by_phone']); ?></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <?php if (isset($deliveryConfirmation['confirmation_date']) && $deliveryConfirmation['confirmation_date']): ?>
                <div class="bg-purple-50 p-4 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-purple-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm font-medium text-purple-800">Confirmed</span>
                    </div>
                    <p class="mt-2 text-lg font-semibold text-purple-900"><?php echo date('d M Y H:i', strtotime($deliveryConfirmation['confirmation_date'])); ?></p>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if (isset($deliveryConfirmation['actual_delivery_address']) && $deliveryConfirmation['actual_delivery_address'] && $deliveryConfirmation['actual_delivery_address'] != $dispatch['delivery_address']): ?>
            <div class="mt-6 p-4 bg-yellow-50 rounded-lg">
                <h4 class="text-sm font-medium text-yellow-800 mb-2">Actual Delivery Address</h4>
                <p class="text-sm text-yellow-700"><?php echo nl2br(htmlspecialchars($deliveryConfirmation['actual_delivery_address'])); ?></p>
            </div>
            <?php endif; ?>
            
            <?php if (isset($deliveryConfirmation['delivery_notes']) && $deliveryConfirmation['delivery_notes']): ?>
            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                <h4 class="text-sm font-medium text-gray-800 mb-2">Delivery Notes</h4>
                <p class="text-sm text-gray-700"><?php echo nl2br(htmlspecialchars($deliveryConfirmation['delivery_notes'])); ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    <!-- Documents Section -->
    <?php 
    // Debug: Check delivery confirmation data
    if ($deliveryConfirmation) {
        echo "<!-- DEBUG: Delivery confirmation exists -->";
        if (isset($deliveryConfirmation['lr_copy_path'])) {
            echo "<!-- DEBUG: LR copy path: " . htmlspecialchars($deliveryConfirmation['lr_copy_path']) . " -->";
        }
        if (isset($deliveryConfirmation['additional_documents'])) {
            echo "<!-- DEBUG: Additional documents: " . (is_array($deliveryConfirmation['additional_documents']) ? count($deliveryConfirmation['additional_documents']) : 'not array') . " -->";
        }
    } else {
        echo "<!-- DEBUG: No delivery confirmation data -->";
    }
    ?>
    <?php if ($hasDocuments || ($deliveryConfirmation && is_array($deliveryConfirmation) && (isset($deliveryConfirmation['lr_copy_path']) || !empty($deliveryConfirmation['additional_documents'])))): ?>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <svg class="w-5 h-5 text-indigo-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                </svg>
                Documents & Attachments
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php if ($deliveryConfirmation && isset($deliveryConfirmation['lr_copy_path']) && !empty($deliveryConfirmation['lr_copy_path'])): ?>
                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center">
                        <svg class="w-8 h-8 text-red-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900">LR Copy</p>
                            <p class="text-xs text-gray-500">Delivery Receipt</p>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="<?php echo '../../../'.htmlspecialchars($deliveryConfirmation['lr_copy_path']); ?>" 
                           target="_blank" 
                           class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                            View Document →
                        </a>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($deliveryConfirmation && !empty($deliveryConfirmation['additional_documents'])): ?>
                    <?php foreach ($deliveryConfirmation['additional_documents'] as $index => $doc): ?>
                    <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center">
                            <svg class="w-8 h-8 text-blue-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <?php 
                                // Handle string paths (current format)
                                if (is_string($doc)) {
                                    $docPath = $doc;
                                    $docName = "Document " . ($index + 1);
                                    $docType = "Additional Document";
                                } else {
                                    // Handle object format (future compatibility)
                                    $docPath = $doc['path'] ?? '';
                                    $docName = $doc['name'] ?? "Document " . ($index + 1);
                                    $docType = $doc['type'] ?? 'Additional Document';
                                }
                                ?>
                                <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($docName); ?></p>
                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($docType); ?></p>
                            </div>
                        </div>

                        <div class="mt-3">
                            <?php if (!empty($docPath)): ?>
                                <a href="<?php echo '../../../'.htmlspecialchars($docPath); ?>" 
                                   target="_blank" 
                                   class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                    View Document →
                                </a>
                            <?php else: ?>
                                <span class="text-sm text-gray-400">Document path not available</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <?php if (!$hasDocuments && (!$deliveryConfirmation || (empty($deliveryConfirmation['lr_copy_path']) && empty($deliveryConfirmation['additional_documents'])))): ?>
                <div class="col-span-full text-center py-8 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                    </svg>
                    <p>No documents uploaded for this dispatch</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
<!-- Material Manifest Table -->
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-8">
    <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-200">
        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Material Manifest</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50/50">
                <tr>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-left w-12">#</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-left">Item Intelligence</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-left">Sent / Recv</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-left">Valuation</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-left">Logistics Meta</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-left">Condition</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-left">Remarks</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                        <?php if (!empty($dispatch['items'])): ?>
                        <?php 
                        $totalValue = 0;
                        $sno = 1;
                        foreach ($dispatch['items'] as $item): 
                            $totalValue += $item['total_cost'];
                            
                            $itemConfirmation = null;
                            if ($deliveryConfirmation && !empty($deliveryConfirmation['item_confirmations'])) {
                                foreach ($deliveryConfirmation['item_confirmations'] as $confirmation) {
                                    if ($confirmation['boq_item_id'] == $item['boq_item_id']) { $itemConfirmation = $confirmation; break; }
                                }
                            }
                            $receivedQty = $itemConfirmation ? $itemConfirmation['received_quantity'] : null;
                            $receivedCondition = $itemConfirmation ? ($itemConfirmation['condition'] ?? null) : null;
                            $receivedRemarks = $itemConfirmation ? ($itemConfirmation['notes'] ?? null) : null;
                        ?>
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 text-xs font-bold text-gray-400"><?php echo $sno++; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 mr-3">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-gray-900"><?php echo htmlspecialchars($item['item_name']); ?></div>
                                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-tight"><?php echo htmlspecialchars($item['item_code']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-xs font-bold text-gray-900"><?php echo number_format($item['quantity_dispatched'], 0); ?> <span class="text-gray-400 font-medium"><?php echo htmlspecialchars($item['unit']); ?></span></div>
                                <?php if ($receivedQty !== null): ?>
                                    <div class="flex items-center gap-1 mt-1">
                                        <span class="text-[10px] font-bold <?php echo $receivedQty == $item['quantity_dispatched'] ? 'text-emerald-600' : 'text-rose-600'; ?> uppercase tracking-tighter">RECV: <?php echo number_format($receivedQty, 0); ?></span>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-xs font-bold text-gray-900">₹<?php echo number_format($item['unit_cost'] ?? 0, 2); ?></div>
                                <div class="text-[10px] font-bold text-emerald-600 mt-0.5">TTL: ₹<?php echo number_format($item['total_cost'] ?? 0, 2); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if (!empty($item['serial_numbers'])): ?>
                                    <div class="text-[10px] font-bold text-blue-600 font-mono uppercase truncate max-w-[120px]" title="<?php echo htmlspecialchars($item['serial_numbers']); ?>">SN: <?php echo htmlspecialchars($item['serial_numbers']); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($item['batch_number'])): ?>
                                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter mt-1">BTCH: <?php echo htmlspecialchars($item['batch_number']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-widest bg-blue-50 text-blue-700 border border-blue-100 mb-1">SENT: <?php echo strtoupper($item['item_condition'] ?: 'NEW'); ?></span>
                                <?php if ($receivedCondition): ?>
                                    <br><span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-widest bg-emerald-50 text-emerald-700 border border-emerald-100">RECV: <?php echo strtoupper($receivedCondition); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-[10px] font-medium text-gray-500 leading-relaxed line-clamp-2 max-w-[200px]">
                                    <?php echo htmlspecialchars($item['remarks'] ?: ($receivedRemarks ?: '--')); ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center">
                                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    <p class="text-gray-500">No items found in this dispatch</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="bg-gray-50/50">
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-right text-[11px] font-bold text-gray-400 uppercase tracking-widest">Total Valuation</td>
                            <td class="px-6 py-4 text-sm font-bold text-emerald-600">₹<?php echo number_format($totalValue, 2); ?></td>
                            <td colspan="3" class="px-6 py-4"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
<style>
@media print {
    .btn, .card-header {
        display: none !important;
    }
    
    .bg-white {
        border: 1px solid #000 !important;
        box-shadow: none !important;
    }
    
    body {
        background: white !important;
    }
}
</style>

<!-- Serial Numbers Modal -->
<div id="serialModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900" id="serialModalTitle">Serial Numbers</h3>
                <button type="button" onclick="closeSerialModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
            
            <div class="bg-gray-50 border border-gray-200 rounded-md p-4 max-h-96 overflow-y-auto">
                <table class="w-full text-sm" id="serialTable">
                    <thead class="bg-gray-100 sticky top-0">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Serial Number</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200" id="serialModalContent">
                        <!-- Serial numbers will be displayed here -->
                    </tbody>
                </table>
            </div>
            
            <div class="flex justify-end mt-4">
                <button type="button" onclick="copySerialNumbers()" 
                        class="mr-3 inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z"></path>
                        <path d="M6 3a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2 3 3 0 01-3 3H9a3 3 0 01-3-3z"></path>
                    </svg>
                    Copy
                </button>
                <button type="button" onclick="closeSerialModal()" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentSerialNumbers = '';

function showSerialModal(serialNumbers, itemName) {
    currentSerialNumbers = serialNumbers;
    document.getElementById('serialModalTitle').textContent = 'Serial Numbers - ' + itemName + ' (' + serialArray.length + ' items)';
    
    // Parse serial numbers (assuming they are comma, semicolon, or newline separated)
    const serialArray = serialNumbers.split(/[,;\n\r]+/)
        .map(s => s.trim())
        .filter(s => s.length > 0);
    
    // Generate table rows
    let tableHTML = '';
    serialArray.forEach((serial, index) => {
        tableHTML += `
            <tr class="${index % 2 === 0 ? 'bg-white' : 'bg-gray-50'}">
                <td class="px-3 py-2 text-gray-900 font-medium">${index + 1}</td>
                <td class="px-3 py-2 text-gray-900 font-mono">${serial}</td>
            </tr>
        `;
    });
    
    document.getElementById('serialModalContent').innerHTML = tableHTML;
    document.getElementById('serialModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeSerialModal() {
    document.getElementById('serialModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function copySerialNumbers() {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(currentSerialNumbers).then(function() {
            // Show success feedback
            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            button.innerHTML = '<svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>Copied!';
            setTimeout(() => {
                button.innerHTML = originalText;
            }, 2000);
        }).catch(function(err) {
            console.error('Could not copy text: ', err);
            fallbackCopyTextToClipboard(currentSerialNumbers);
        });
    } else {
        fallbackCopyTextToClipboard(currentSerialNumbers);
    }
}

function fallbackCopyTextToClipboard(text) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.top = "0";
    textArea.style.left = "0";
    textArea.style.position = "fixed";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        const successful = document.execCommand('copy');
        if (successful) {
            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            button.innerHTML = '<svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>Copied!';
            setTimeout(() => {
                button.innerHTML = originalText;
            }, 2000);
        }
    } catch (err) {
        console.error('Fallback: Oops, unable to copy', err);
    }
    
    document.body.removeChild(textArea);
}

// Close modal when clicking outside
document.getElementById('serialModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeSerialModal();
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../includes/admin_layout.php';
?>