<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/Inventory.php';

// Require vendor authentication
Auth::requireVendor();

$vendorId = Auth::getVendorId();
$dispatchId = $_GET['id'] ?? null;

if (!$dispatchId) {
    header('Location: material-received.php');
    exit;
}

$inventoryModel = new Inventory();
$dispatch = $inventoryModel->getDispatchDetails($dispatchId);

// Verify ownership
if (!$dispatch || $dispatch['vendor_id'] != $vendorId) {
    die("Unauthorized access to this manifest.");
}

// Check if already audited
$isAudited = in_array($dispatch['dispatch_status'], ['delivered', 'partially_delivered']) || !empty($dispatch['confirmed_by']);

// Parse confirmation data if exists
$confirmations = [];
$additionalDocs = [];
if ($isAudited) {
    if (!empty($dispatch['item_confirmations'])) {
        $confirmations = json_decode($dispatch['item_confirmations'], true);
    }
    if (!empty($dispatch['additional_documents'])) {
        $additionalDocs = json_decode($dispatch['additional_documents'], true);
    }
}

$title = ($isAudited ? 'Audit Summary' : 'Authoritative Audit') . ' - ' . ($dispatch['dispatch_number'] ?? 'Manifest');
ob_start();
?>
<style>
    :root {
        --font-main: 'Inter', sans-serif;
    }

    .audit-row {
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .audit-row:hover {
        background: linear-gradient(90deg, #f0f9ff 0%, #f8fafc 100%) !important;
    }
    
    .input-error {
        border-color: #ef4444 !important;
        background-color: #fef2f2 !important;
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1) !important;
    }
    
    .required-label::after {
        content: " *";
        color: #ef4444;
    }

    .readonly-input {
        @apply bg-slate-50 border-none px-0 font-black text-slate-900 !important;
        box-shadow: none !important;
    }

    /* Premium Sidebar Panels */
    .audit-sidebar-panel {
        background: #ffffff;
        border: 1px solid #f1f5f9;
        border-radius: 18px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    .audit-sidebar-panel:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,0.04);
    }

    /* Table Enhancements */
    .audit-table-header th {
        background: #f8fafc !important;
        font-size: 9px !important;
        font-weight: 800 !important;
        letter-spacing: 0.14em !important;
        text-transform: uppercase;
        color: #94a3b8 !important;
        padding: 14px 16px !important;
        border-bottom: 1px solid #f1f5f9 !important;
        white-space: nowrap;
    }
    
    /* Input Focus States */
    .audit-input {
        transition: all 0.2s ease;
    }
    .audit-input:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.08) !important;
        background: #ffffff !important;
    }

    /* Premium Toast */
    #audit-toast {
        transform: translateY(100%);
        opacity: 0;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    #audit-toast.show {
        transform: translateY(0);
        opacity: 1;
    }

    /* Skeleton Loader */
    .audit-skeleton {
        background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
        background-size: 200% 100%;
        animation: audit-shimmer 1.5s infinite;
        border-radius: 8px;
    }
    @keyframes audit-shimmer {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    /* Submit Button */
    .audit-submit-btn {
        background: linear-gradient(135deg, #3b82f6, #2563eb) !important;
        box-shadow: 0 4px 16px rgba(37,99,235,0.25) !important;
        transition: all 0.25s ease !important;
    }
    .audit-submit-btn:hover {
        background: linear-gradient(135deg, #2563eb, #1d4ed8) !important;
        box-shadow: 0 8px 24px rgba(37,99,235,0.35) !important;
        transform: translateY(-1px) !important;
    }
</style>

<!-- Audit Toast Notification System -->
<div id="audit-toast" class="fixed bottom-10 left-1/2 -translate-x-1/2 z-[3000] pointer-events-none">
    <div class="bg-slate-900 border border-slate-800 text-white rounded-2xl px-6 py-4 shadow-2xl flex items-center gap-4 min-w-[320px]">
        <div id="toast-icon-box" class="w-10 h-10 rounded-xl flex items-center justify-center">
            <!-- Icon injected here -->
        </div>
        <div>
            <p id="toast-title" class="text-[10px] font-black uppercase tracking-widest text-slate-400">Audit Protocol</p>
            <p id="toast-msg" class="text-sm font-bold mt-0.5">Message Content</p>
        </div>
    </div>
</div>

<div class="max-w-[1400px]">
    <!-- Back Navigation -->
    <div class="mb-8 flex items-center justify-between px-6">
        <a href="material-received.php" class="group flex items-center text-xs font-black text-slate-400 hover:text-slate-900 uppercase tracking-widest transition-all">
            <svg class="w-4 h-4 mr-2 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Returns to Hub
        </a>
        <div class="flex items-center gap-3">
             <?php if ($isAudited): ?>
                <span class="text-[10px] font-black text-emerald-600 uppercase tracking-widest bg-emerald-50 px-3 py-1 rounded-full border border-emerald-100 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                    Audit Finalized
                </span>
             <?php else: ?>
                <span class="text-[10px] font-black text-blue-500 uppercase tracking-widest bg-blue-50 px-3 py-1 rounded-full border border-blue-100">Audit Protocol Active</span>
             <?php endif; ?>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 px-6">
        <!-- Sidebar: Manifest Information -->
        <div class="lg:col-span-4 space-y-6">
            <!-- Chain of Custody (Sender Info) -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                <h3 class="text-[10px] font-bold uppercase tracking-[0.15em] mb-4 text-slate-400">Chain of Custody (Sender)</h3>
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center text-slate-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Dispatched By</p>
                        <p class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars($dispatch['dispatched_by_name'] ?: 'Corporate Admin'); ?></p>
                        <p class="text-[10px] font-bold text-blue-600 mt-0.5"><?php echo date('d M Y, h:i A', strtotime($dispatch['created_at'])); ?></p>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4 py-4 border-y border-slate-50">
                    <div>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Manifest ID</p>
                        <p class="text-sm font-bold text-slate-900 whitespace-nowrap"><?php echo htmlspecialchars($dispatch['dispatch_number']); ?></p>
                    </div>
                    <div>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1 text-right">Site Code</p>
                        <p class="text-sm font-bold text-slate-900 text-right"><?php echo htmlspecialchars($dispatch['site_code']); ?></p>
                    </div>
                </div>
                
                <div class="mt-4 flex items-center justify-between border-b border-slate-50 pb-4">
                    <div>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Logistics / Courier</p>
                        <p class="text-xs font-bold text-slate-700"><?php echo htmlspecialchars($dispatch['courier_name'] ?: '--'); ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Tracking ID</p>
                        <p class="text-xs font-mono font-bold text-slate-700"><?php echo htmlspecialchars($dispatch['tracking_number'] ?: '--'); ?></p>
                    </div>
                </div>

                <div class="mt-4">
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Destination Location</p>
                    <p class="text-[11px] font-bold text-slate-600 leading-normal"><?php echo htmlspecialchars($dispatch['site_name'] ?: 'Vendor Operational Site'); ?></p>
                </div>
            </div>

            <!-- Receipt & Acceptance Details -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                <div class="flex items-center gap-2 mb-6">
                    <div class="w-1.5 h-6 bg-blue-600 rounded-full"></div>
                    <h3 class="text-sm font-bold uppercase tracking-widest text-slate-900">Acceptance Record</h3>
                </div>
                
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 <?php echo !$isAudited ? 'required-label' : ''; ?>">Receipt Date</label>
                            <input type="date" id="receipt-date" 
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-xs font-bold <?php echo $isAudited ? 'readonly-input' : ''; ?>" 
                                   value="<?php echo htmlspecialchars($dispatch['delivery_date'] ?: date('Y-m-d')); ?>"
                                   <?php echo $isAudited ? 'disabled' : ''; ?>>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 <?php echo !$isAudited ? 'required-label' : ''; ?>">Receipt Time</label>
                            <input type="time" id="receipt-time" 
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-xs font-bold <?php echo $isAudited ? 'readonly-input' : ''; ?>" 
                                   value="<?php echo htmlspecialchars($dispatch['delivery_time'] ?: date('H:i')); ?>"
                                   <?php echo $isAudited ? 'disabled' : ''; ?>>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 <?php echo !$isAudited ? 'required-label' : ''; ?>">Received By</label>
                        <input type="text" id="received-by" 
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs font-bold <?php echo $isAudited ? 'readonly-input' : ''; ?>" 
                               placeholder="Full name of receiver"
                               value="<?php echo htmlspecialchars($dispatch['received_by'] ?: ''); ?>"
                               <?php echo $isAudited ? 'disabled' : ''; ?>>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Contact Phone</label>
                        <input type="tel" id="received-phone" 
                               maxlength="10"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs font-bold transition-all <?php echo $isAudited ? 'readonly-input' : ''; ?>" 
                               placeholder="10-digit mobile number"
                               value="<?php echo htmlspecialchars($dispatch['received_by_phone'] ?: ''); ?>"
                               <?php echo $isAudited ? 'disabled' : ''; ?>>
                        <p id="phone-error" class="hidden text-[8px] font-bold text-rose-500 uppercase mt-1 tracking-wider">Invalid 10-digit number</p>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Receipt Notes</label>
                        <textarea id="receipt-notes" rows="3" 
                                  class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs font-bold resize-none <?php echo $isAudited ? 'readonly-input' : ''; ?>" 
                                  placeholder="Add package condition notes..."
                                  <?php echo $isAudited ? 'disabled' : ''; ?>><?php echo htmlspecialchars($dispatch['delivery_notes'] ?: ''); ?></textarea>
                    </div>

                    <?php if ($isAudited): ?>
                        <?php if ($dispatch['lr_copy_path']): ?>
                        <div class="pt-4 border-t border-slate-50">
                             <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Authenticated LR Copy</label>
                             <a href="../<?php echo $dispatch['lr_copy_path']; ?>" target="_blank" class="flex items-center gap-3 p-3 bg-blue-50/50 border border-blue-100 rounded-xl hover:bg-blue-50 transition-all group">
                                 <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600">
                                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                 </div>
                                 <div>
                                     <p class="text-[10px] font-black text-blue-700 uppercase tracking-widest">View Uploaded LR</p>
                                     <p class="text-[8px] font-bold text-blue-400">Primary Proof of Delivery</p>
                                 </div>
                             </a>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($additionalDocs)): ?>
                        <div class="pt-4 border-t border-slate-50 space-y-2">
                             <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Supplemental Documents</label>
                             <?php foreach ($additionalDocs as $doc): ?>
                             <?php 
                                // Handle both new format (object) and legacy format (string)
                                $docPath = is_array($doc) ? ($doc['path'] ?? '') : $doc;
                                $docName = is_array($doc) ? ($doc['name'] ?? 'Attachment') : basename($doc);
                             ?>
                             <a href="../<?php echo $docPath; ?>" target="_blank" class="flex items-center gap-2 p-2 bg-slate-50 border border-slate-100 rounded-lg hover:bg-slate-100 transition-all group">
                                 <div class="w-6 h-6 bg-slate-200 rounded flex items-center justify-center text-slate-500">
                                     <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                 </div>
                                 <p class="text-[9px] font-bold text-slate-600 truncate flex-1"><?php echo htmlspecialchars($docName); ?></p>
                             </a>
                             <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- Fresh Audit Upload Inputs -->
                        <div class="pt-4 border-t border-slate-100 space-y-4">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 required-label">LR Copy / Delivery Receipt</label>
                                <input type="file" id="lr-copy" class="hidden" onchange="updateFileName(this, 'lr-label')" accept="image/*,.pdf">
                                <label for="lr-copy" id="lr-label" class="flex items-center gap-3 p-3 bg-white border border-slate-200 border-dashed rounded-xl hover:border-blue-500 hover:bg-blue-50 transition-all cursor-pointer group">
                                    <div class="w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center text-slate-400 group-hover:bg-blue-100 group-hover:text-blue-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                    </div>
                                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Select receipt document...</span>
                                </label>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Additional Documents</label>
                                <input type="file" id="additional-docs" class="hidden" multiple onchange="updateFileName(this, 'extra-label')" accept="image/*,.pdf">
                                <label for="additional-docs" id="extra-label" class="flex items-center gap-3 p-3 bg-white border border-slate-200 border-dashed rounded-xl hover:border-blue-500 hover:bg-blue-50 transition-all cursor-pointer group">
                                    <div class="w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center text-slate-400 group-hover:bg-blue-100 group-hover:text-blue-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    </div>
                                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Add more files...</span>
                                </label>
                                <p class="text-[8px] font-bold text-slate-400 uppercase mt-2 tracking-widest text-center">Unboxing photos / Damage proof</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Main Content: Material Items -->
        <div class="lg:col-span-8">
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
                <div class="px-6 py-4 bg-slate-50/50 border-b border-slate-100 flex justify-between items-center">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wide">Material Audit Records</h2>
                        <p id="item-count-label" class="text-[10px] text-slate-400 font-semibold uppercase tracking-wide mt-1">Initializing Ledger...</p>
                    </div>
                </div>

                <div class="overflow-x-auto min-h-[300px] relative">
                    <!-- Loading Overlay -->
                    <div id="table-loader" class="absolute inset-0 bg-white/80 backdrop-blur-[2px] z-10 flex flex-col items-center justify-center gap-3 transition-opacity duration-300">
                        <div class="w-10 h-10 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
                        <p class="text-[9px] font-bold text-blue-600 uppercase tracking-[0.15em] animate-pulse">Fetching Manifest Data</p>
                    </div>

                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-white border-b border-slate-50">
                                <th class="px-4 py-4 text-[9px] font-bold text-slate-400 uppercase tracking-wide w-12 text-center">#</th>
                                <th class="px-6 py-4 text-[9px] font-bold text-slate-400 uppercase tracking-wide">Material Item & Code</th>
                                <th class="px-4 py-4 text-[9px] font-bold text-slate-400 uppercase tracking-wide text-center w-24">Sent</th>
                                <th class="px-4 py-4 text-[9px] font-bold text-blue-500 uppercase tracking-wide text-center w-32">Received</th>
                                <th class="px-6 py-4 text-[9px] font-bold text-rose-500 uppercase tracking-wide text-center w-32">Damaged</th>
                            </tr>
                        </thead>
                        <tbody id="audit-items-body" class="divide-y divide-slate-50">
                            <!-- Items injected via API -->
                        </tbody>
                    </table>
                </div>

                <!-- Footer Summary / Action -->
                <div class="px-6 py-6 bg-slate-900 border-t border-slate-800 flex items-center justify-between mt-auto">
                    <div>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide leading-relaxed">
                            <?php echo $isAudited ? 'Authoritative Audit Ledger' : 'Secured Audit Submission'; ?>
                        </p>
                        <p class="text-[10px] font-medium text-slate-500 mt-1">
                            <?php echo $isAudited ? 'This record is immutable and verified.' : 'Cross-check against physical LR Copy required.'; ?>
                        </p>
                    </div>
                    <?php if (!$isAudited): ?>
                    <button id="final-submit-btn" onclick="submitAudit()" class="audit-submit-btn px-10 py-3.5 text-white text-[10px] font-bold rounded-xl active:scale-95 flex items-center gap-3 uppercase tracking-wide group">
                        Confirm & Close Manifest
                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                    <?php else: ?>
                    <a href="material-received.php" class="px-10 py-3.5 bg-slate-800 text-white text-[10px] font-bold rounded-xl hover:bg-slate-700 transition-all flex items-center gap-3 uppercase tracking-wide group border border-slate-700">
                        Exit Summary
                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Global Configuration from PHP
const AUDIT_CONFIG = {
    dispatchId: '<?php echo $dispatchId; ?>',
    isAudited: <?php echo $isAudited ? 'true' : 'false'; ?>,
    confirmations: <?php echo json_encode($confirmations); ?>
};

async function loadAuditItems() {
    const tableBody = document.getElementById('audit-items-body');
    const loader = document.getElementById('table-loader');
    const countLabel = document.getElementById('item-count-label');

    try {
        const response = await fetch(`v2/get-dispatch-items.php?dispatch_id=${AUDIT_CONFIG.dispatchId}`);
        const result = await response.json();

        if (!result.success) throw new Error(result.message);

        const items = result.data;
        countLabel.textContent = `${items.length} Items Reconciled`;
        
        tableBody.innerHTML = '';
        
        items.forEach((item, index) => {
            // Find existing confirmation if audited
            let confirm = null;
            if (AUDIT_CONFIG.isAudited) {
                confirm = AUDIT_CONFIG.confirmations.find(c => c.boq_item_id == (item.boq_item_id || item.id));
            }

            let qtyRecv, qtyDmg;
            if (AUDIT_CONFIG.isAudited) {
                qtyRecv = confirm ? (confirm.quantity_received ?? confirm.received_quantity ?? 0) : 0;
                qtyDmg = confirm ? (confirm.quantity_damaged ?? confirm.damaged_quantity ?? 0) : 0;
            } else {
                qtyRecv = item.quantity_dispatched;
                qtyDmg = 0;
            }

            const missing = parseFloat(item.quantity_dispatched) - parseFloat(qtyRecv) - parseFloat(qtyDmg);
            const rowClass = AUDIT_CONFIG.isAudited ? 'bg-slate-50/20' : 'hover:bg-slate-50/50';
            const readonlyClass = AUDIT_CONFIG.isAudited ? 'readonly-input' : '';
            const disabledAttr = AUDIT_CONFIG.isAudited ? 'disabled' : '';

            const rowHtml = `
                <tr class="audit-row ${rowClass} transition-all" 
                    data-item-id="${item.boq_item_id}" 
                    data-dispatch-qty="${item.quantity_dispatched}">
                    <td class="px-4 py-3 text-center text-[10px] font-semibold text-slate-300">
                        ${String(index + 1).padStart(2, '0')}
                    </td>
                    <td class="px-6 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center text-slate-400 shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-semibold text-slate-800 truncate">${item.item_name}</p>
                                <p class="text-[9px] font-medium text-slate-400 font-mono mt-0.5">${item.item_code} <span class="text-slate-200">|</span> <span class="text-blue-500 uppercase">${item.unit}</span></p>
                            </div>
                        </div>
                        <div id="missing-alert-${index}" class="${missing > 0 ? '' : 'hidden'} mt-2">
                            <div class="bg-rose-50 px-2 py-1 rounded-md inline-flex items-center gap-1.5 border border-rose-100">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                <span class="text-[8px] font-bold text-rose-600 uppercase">Shortage: <span class="val-missing">${missing.toFixed(2)}</span></span>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-xs font-semibold text-slate-500">${item.quantity_dispatched}</span>
                    </td>
                    <td class="px-4 py-3">
                        <input type="number" 
                               class="w-full h-8 bg-white border border-slate-200 rounded-lg text-center font-semibold text-blue-900 text-xs focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all input-recv ${readonlyClass}" 
                               value="${qtyRecv}"
                               min="0"
                               max="${item.quantity_dispatched}"
                               data-index="${index}"
                               ${disabledAttr}>
                    </td>
                    <td class="px-6 py-3 text-right">
                        <input type="number" 
                               class="w-full h-8 bg-white border border-slate-200 rounded-lg text-center font-semibold text-rose-900 text-xs focus:ring-4 focus:ring-rose-500/10 focus:border-rose-500 transition-all input-faulty ${readonlyClass}" 
                               value="${qtyDmg}"
                               min="0"
                               max="${item.quantity_dispatched}"
                               data-index="${index}"
                               ${disabledAttr}>
                    </td>
                </tr>
            `;
            tableBody.insertAdjacentHTML('beforeend', rowHtml);
        });

        // Initialize listeners if editing
        if (!AUDIT_CONFIG.isAudited) initAuditListeners();

        // Hide loader
        loader.style.opacity = '0';
        setTimeout(() => loader.classList.add('hidden'), 300);

    } catch (error) {
        console.error('Audit Load Error:', error);
        tableBody.innerHTML = `<tr><td colspan="5" class="py-20 text-center"><p class="text-xs font-black text-rose-500 uppercase tracking-widest">Master Data Synchronization Failed</p></td></tr>`;
        loader.classList.add('hidden');
    }
}

function showAuditToast(msg, type = 'error') {
    const toast = document.getElementById('audit-toast');
    const toastMsg = document.getElementById('toast-msg');
    const iconBox = document.getElementById('toast-icon-box');
    
    toastMsg.textContent = msg;
    
    if (type === 'success') {
        iconBox.className = "w-10 h-10 rounded-xl flex items-center justify-center bg-emerald-500/10 text-emerald-500";
        iconBox.innerHTML = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
    } else {
        iconBox.className = "w-10 h-10 rounded-xl flex items-center justify-center bg-rose-500/10 text-rose-500";
        iconBox.innerHTML = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>';
    }
    
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 4000);
}

// Initial load call
document.addEventListener('DOMContentLoaded', loadAuditItems);

<?php if (!$isAudited): ?>
/**
 * AUDIT EDIT MODE LOGIC
 */
function updateFileName(input, labelId) {
    const label = document.getElementById(labelId);
    if (input.files && input.files[0]) {
        const count = input.files.length;
        label.textContent = count > 1 ? `${count} Files Selected` : input.files[0].name;
        label.classList.add('!border-emerald-500', '!text-emerald-700', 'bg-emerald-50');
        label.classList.remove('input-error');
    }
}

const phoneInput = document.getElementById('received-phone');
const phoneError = document.getElementById('phone-error');

// Real-time clearance for required fields
['receipt-date', 'receipt-time', 'received-by'].forEach(id => {
    const el = document.getElementById(id);
    if (el) {
        el.addEventListener('input', () => {
            if (el.value.trim()) {
                el.classList.remove('input-error');
                validateForm();
            }
        });
    }
});

if (phoneInput) {
    phoneInput.addEventListener('input', (e) => {
        e.target.value = e.target.value.replace(/[^0-9]/g, '');
        const val = e.target.value;
        if (val.length > 0 && val.length !== 10) {
            phoneInput.classList.add('!border-rose-400', 'bg-rose-50');
            phoneError.classList.remove('hidden');
        } else {
            phoneInput.classList.remove('!border-rose-400', 'bg-rose-50');
            phoneError.classList.add('hidden');
        }
        validateForm();
    });
}

function initAuditListeners() {
    document.querySelectorAll('.input-recv, .input-faulty').forEach(input => {
        input.addEventListener('input', (e) => {
            const row = e.target.closest('.audit-row');
            const index = e.target.dataset.index;
            const dispatched = parseFloat(row.dataset.dispatchQty);
            
            let recvVal = parseFloat(row.querySelector('.input-recv').value) || 0;
            let faultyVal = parseFloat(row.querySelector('.input-faulty').value) || 0;

            if (recvVal < 0) { row.querySelector('.input-recv').value = 0; recvVal = 0; }
            if (faultyVal < 0) { row.querySelector('.input-faulty').value = 0; faultyVal = 0; }

            const total = recvVal + faultyVal;
            const missing = dispatched - total;
            const alertBox = document.getElementById(`missing-alert-${index}`);

            if (missing > 0) {
                alertBox.classList.remove('hidden');
                alertBox.querySelector('.val-missing').textContent = missing.toFixed(2);
            } else {
                alertBox.classList.add('hidden');
            }

            if (total > dispatched) {
                row.querySelector('.input-recv').classList.add('input-error');
                row.querySelector('.input-faulty').classList.add('input-error');
            } else {
                row.querySelector('.input-recv').classList.remove('input-error');
                row.querySelector('.input-faulty').classList.remove('input-error');
            }
            validateForm();
        });

        input.addEventListener('blur', (e) => {
            const row = e.target.closest('.audit-row');
            const dispatched = parseFloat(row.dataset.dispatchQty);
            let recvInput = row.querySelector('.input-recv');
            let faultyInput = row.querySelector('.input-faulty');
            
            let rv = parseFloat(recvInput.value) || 0;
            let fv = parseFloat(faultyInput.value) || 0;

            if (rv + fv > dispatched) {
                if (e.target === recvInput) {
                    recvInput.value = (dispatched - fv).toFixed(2);
                } else {
                    faultyInput.value = (dispatched - rv).toFixed(2);
                }
                e.target.dispatchEvent(new Event('input'));
            }
        });
    });
}

function validateForm() {
    const quantityErrors = document.querySelectorAll('.input-recv.input-error, .input-faulty.input-error');
    const phoneVal = phoneInput ? phoneInput.value : '';
    const isPhoneValid = phoneVal.length === 0 || phoneVal.length === 10;
    
    const submitBtn = document.getElementById('final-submit-btn');
    if (!submitBtn) return;
    
    const hasCriticalError = quantityErrors.length > 0;
    const hasFormError = !isPhoneValid;
    const isDisabled = hasCriticalError || hasFormError;
    
    submitBtn.disabled = isDisabled;
    submitBtn.style.opacity = isDisabled ? '0.5' : '1';
    submitBtn.style.cursor = isDisabled ? 'not-allowed' : 'pointer';
}

async function submitAudit() {
    const rDateInput = document.getElementById('receipt-date');
    const rTimeInput = document.getElementById('receipt-time');
    const rByInput = document.getElementById('received-by');
    const rPhone = phoneInput ? phoneInput.value : '';
    const rByNotes = document.getElementById('receipt-notes').value.trim();
    const lrCopyInput = document.getElementById('lr-copy');
    const lrLabel = document.getElementById('lr-label');
    const addDocsInput = document.getElementById('additional-docs');

    [rDateInput, rTimeInput, rByInput, lrLabel].forEach(el => el && el.classList.remove('input-error'));

    let hasErrors = false;
    if (!rDateInput.value) { rDateInput.classList.add('input-error'); hasErrors = true; }
    if (!rTimeInput.value) { rTimeInput.classList.add('input-error'); hasErrors = true; }
    if (!rByInput.value.trim()) { rByInput.classList.add('input-error'); hasErrors = true; }
    if (!lrCopyInput.files[0]) { lrLabel.classList.add('input-error'); hasErrors = true; }

    if (hasErrors) {
        showAuditToast('Audit Blocked: Required fields (Date, Time, Receiver, LR Copy) are missing.');
        return;
    }
    
    const itemData = [];
    document.querySelectorAll('.audit-row').forEach(row => {
        const recvVal = parseFloat(row.querySelector('.input-recv').value) || 0;
        const faultyVal = parseFloat(row.querySelector('.input-faulty').value) || 0;
        const dispatched = parseFloat(row.dataset.dispatchQty);
        const missing = dispatched - recvVal - faultyVal;

        itemData.push({
            boq_item_id: row.dataset.itemId,
            quantity_received: recvVal,
            quantity_damaged: faultyVal,
            quantity_missing: missing < 0 ? 0 : missing
        });
    });
    
    const btn = document.getElementById('final-submit-btn');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div> SYNCING LEDGER...';
    btn.disabled = true;
    
    try {
        const formData = new FormData();
        formData.append('dispatch_id', AUDIT_CONFIG.dispatchId);
        formData.append('receipt_date', rDateInput.value);
        formData.append('receipt_time', rTimeInput.value);
        formData.append('received_by', rByInput.value);
        formData.append('contact_phone', rPhone);
        formData.append('notes', rByNotes);
        formData.append('items', JSON.stringify(itemData));
        formData.append('lr_copy', lrCopyInput.files[0]);
        
        if (addDocsInput.files.length > 0) {
            for (let i = 0; i < addDocsInput.files.length; i++) {
                formData.append('additional_docs[]', addDocsInput.files[i]);
            }
        }

        const response = await fetch('v1/process-material-audit.php', { method: 'POST', body: formData });
        const result = await response.json();
        
        if (result.success) {
            showAuditToast('Manifest verified and synchronized.', 'success');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showAuditToast('Audit Rejection: ' + (result.message || 'Server rejected submission.'));
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    } catch (e) {
        showAuditToast('Connection Error: Failed to reach central inventory.');
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}
<?php endif; ?>
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../includes/vendor_layout.php';
?>