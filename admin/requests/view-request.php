<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/MaterialRequest.php';
require_once __DIR__ . '/../../models/BoqItem.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$requestId = $_GET['id'] ?? null;

if (!$requestId) {
    header('Location: index.php');
    exit;
}

$materialRequestModel = new MaterialRequest();
$boqModel = new BoqItem();

// Get request details
$request = $materialRequestModel->findWithDetails($requestId);

if (!$request) {
    header('Location: index.php');
    exit;
}

// Parse items
$items = json_decode($request['items'], true) ?: [];

// Get BOQ item details for each item
$boqItems = [];
foreach ($items as $item) {
    if (!empty($item['boq_item_id'])) {
        $boqItem = $boqModel->find($item['boq_item_id']);
        if ($boqItem) {
            $boqItems[$item['boq_item_id']] = $boqItem;
        }
    }
}

$title = 'Material Request #' . $request['id'];
ob_start();
?>

<style>
    :root {
        --primary: #2563eb;
        --secondary: #64748b;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --background: #f8fafc;
        --card-bg: #ffffff;
    }

    .status-stage {
        flex: 1;
        text-align: center;
        padding: 1rem 0.5rem;
        position: relative;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #94a3b8;
        transition: all 0.3s ease;
    }

    .status-stage::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: #e2e8f0;
        border-radius: 4px;
    }

    .status-stage.active {
        color: var(--primary);
    }

    .status-stage.active::after {
        background: var(--primary);
    }

    .status-stage.completed {
        color: var(--success);
    }

    .status-stage.completed::after {
        background: var(--success);
    }

    .status-stage.rejected {
        color: var(--danger);
    }

    .status-stage.rejected::after {
        background: var(--danger);
    }

    .info-card {
        background: white;
        border-radius: 0.5rem;
        border: 1px solid #e2e8f0;
        padding: 1.5rem;
        height: 100%;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }

    .label-tiny {
        display: block;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        margin-bottom: 0.25rem;
    }

    .value-bold {
        font-weight: 600;
        color: #1e293b;
        font-size: 1rem;
    }

    .data-table-modern {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table-modern thead th {
        padding: 0.75rem 1rem;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        text-align: left;
    }

    .data-table-modern tbody td {
        padding: 1rem;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.875rem;
    }

    .action-btn {
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-weight: 700;
        font-size: 0.75rem;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
</style>

<div class="px-4 py-6">
    <!-- Header with Status Pipe -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2 py-0.5 bg-blue-50 text-blue-600 rounded text-[11px] font-semibold uppercase tracking-wider border border-blue-100">Request #<?php echo $request['id']; ?></span>
                    <span class="text-[12px] font-medium text-gray-500"><?php echo date('d M Y', strtotime($request['created_date'])); ?></span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Material Requisition Details</h1>
            </div>
            
            <div class="flex gap-2">
                <a href="index.php" class="action-btn bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Back to List
                </a>
                
                <?php if ($request['status'] === 'pending'): ?>
                    <button onclick="approveRequest(<?php echo $request['id']; ?>)" class="action-btn bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Approve
                    </button>
                    <button onclick="rejectRequest(<?php echo $request['id']; ?>)" class="action-btn bg-rose-600 text-white hover:bg-rose-700 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Reject
                    </button>
                <?php endif; ?>

                <?php if (in_array($request['status'], ['pending', 'approved'])): ?>
                    <button onclick="toggleEditMode()" id="editItemsBtn" class="action-btn bg-amber-500 text-white hover:bg-amber-600 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit Items
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Status Pipe -->
        <div class="px-6 flex bg-gray-50/50">
            <div class="status-stage <?php echo in_array($request['status'], ['draft', 'pending', 'approved', 'dispatched', 'completed']) ? 'active' : ''; ?> <?php echo in_array($request['status'], ['approved', 'dispatched', 'completed']) ? 'completed' : ''; ?>">Draft</div>
            <div class="status-stage <?php echo in_array($request['status'], ['pending', 'approved', 'dispatched', 'completed']) ? 'active' : ''; ?> <?php echo in_array($request['status'], ['approved', 'dispatched', 'completed']) ? 'completed' : ''; ?>">Pending</div>
            <div class="status-stage <?php echo in_array($request['status'], ['approved', 'dispatched', 'completed']) ? 'active' : ''; ?> <?php echo in_array($request['status'], ['dispatched', 'completed']) ? 'completed' : ''; ?> <?php echo $request['status'] === 'rejected' ? 'rejected' : ''; ?>"><?php echo $request['status'] === 'rejected' ? 'Rejected' : 'Approved'; ?></div>
            <div class="status-stage <?php echo in_array($request['status'], ['dispatched', 'completed']) ? 'active' : ''; ?> <?php echo $request['status'] === 'completed' ? 'completed' : ''; ?>">Dispatch</div>
            <div class="status-stage <?php echo $request['status'] === 'completed' ? 'active completed' : ''; ?>">Delivery</div>
        </div>
    </div>

    <!-- Info Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-8">
        <div class="lg:col-span-4">
            <div class="info-card">
                <h3 class="text-[11px] font-bold text-blue-600 uppercase tracking-wider mb-6">Site Details</h3>
                <div class="space-y-6">
                    <div>
                        <span class="label-tiny">Site Reference</span>
                        <div class="value-bold text-lg"><?php echo htmlspecialchars($request['site_code']); ?></div>
                    </div>
                    <div>
                        <span class="label-tiny">Location / Address</span>
                        <div class="text-sm font-medium text-slate-600 leading-relaxed"><?php echo htmlspecialchars($request['location']); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-4">
            <div class="info-card">
                <h3 class="text-[11px] font-bold text-rose-600 uppercase tracking-wider mb-6">Vendor Assignment</h3>
                <div class="space-y-6">
                    <div>
                        <span class="label-tiny">Vendor Name</span>
                        <div class="value-bold text-lg"><?php echo htmlspecialchars($request['vendor_name']); ?></div>
                    </div>
                    <?php if ($request['vendor_company_name']): ?>
                    <div>
                        <span class="label-tiny">Company</span>
                        <div class="text-sm font-medium text-slate-600 leading-relaxed"><?php echo htmlspecialchars($request['vendor_company_name']); ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="lg:col-span-4">
            <div class="info-card">
                <h3 class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider mb-6">Logistics Info</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center py-2 border-b border-gray-50">
                        <span class="label-tiny mb-0">Requested On</span>
                        <span class="value-bold"><?php echo date('d M Y', strtotime($request['request_date'])); ?></span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-50">
                        <span class="label-tiny mb-0">Required By</span>
                        <span class="value-bold text-blue-600"><?php echo $request['required_date'] ? date('d M Y', strtotime($request['required_date'])) : '--'; ?></span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-50">
                        <span class="label-tiny mb-0">Current Status</span>
                        <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[10px] font-bold uppercase"><?php echo $request['status']; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Manifest -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Material Manifest</h3>
                <p class="text-[12px] font-medium text-gray-500 mt-0.5 uppercase tracking-wide">Detailed list of requested items and quantities</p>
            </div>
            
            <div id="editModeActions" class="hidden flex gap-2">
                <button onclick="saveItemChanges()" class="action-btn bg-emerald-600 text-white hover:bg-emerald-700">
                    Save Changes
                </button>
                <button onclick="cancelEditMode()" class="action-btn bg-white border border-gray-300 text-gray-700">
                    Cancel
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="data-table-modern" id="itemsTable">
                <thead>
                    <tr>
                        <th class="w-16 text-gray-500 font-semibold">#</th>
                        <th class="text-gray-500 font-semibold">Material Description</th>
                        <th class="text-gray-500 font-semibold">Code</th>
                        <th class="w-24 text-gray-500 font-semibold">Qty</th>
                        <th class="w-24 text-gray-500 font-semibold">Unit</th>
                        <th class="text-gray-500 font-semibold">Notes</th>
                        <th class="edit-mode-column hidden w-20"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-12 text-gray-400 font-medium">No materials linked to this request</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($items as $index => $item): ?>
                        <?php 
                            $isBoqRequest = isset($item['boq_item_id']);
                            $itemName = $isBoqRequest 
                                ? (isset($boqItems[$item['boq_item_id']]) ? $boqItems[$item['boq_item_id']]['item_name'] : 'Item not found')
                                : ($item['material_name'] ?? 'Unknown Material');
                            $itemCode = $item['item_code'] ?? 'N/A';
                            $quantity = $item['quantity'] ?? 0;
                            $unit = $item['unit'] ?? 'units';
                            $notes = $item['notes'] ?? $item['reason'] ?? '--';
                        ?>
                        <tr data-index="<?php echo $index; ?>" data-boq-id="<?php echo $item['boq_item_id'] ?? ''; ?>">
                            <td class="text-gray-400 font-medium"><?php echo $index + 1; ?></td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg <?php echo $isBoqRequest ? 'bg-blue-50 text-blue-600' : 'bg-orange-50 text-orange-600'; ?> flex items-center justify-center border border-current border-opacity-10">
                                        <i class="<?php echo $isBoqRequest ? ($boqItems[$item['boq_item_id']]['icon_class'] ?? 'fas fa-cube') : 'fas fa-tools'; ?> text-xs"></i>
                                    </div>
                                    <div class="font-semibold text-gray-900"><?php echo htmlspecialchars($itemName); ?></div>
                                </div>
                            </td>
                            <td><span class="view-mode"><?php echo htmlspecialchars($itemCode); ?></span><input type="text" class="form-input text-xs edit-mode hidden border-gray-200 rounded" value="<?php echo htmlspecialchars($itemCode); ?>" data-field="item_code"></td>
                            <td><span class="view-mode font-bold text-blue-600"><?php echo number_format($quantity); ?></span><input type="number" class="form-input text-xs edit-mode hidden border-gray-200 rounded w-full" value="<?php echo $quantity; ?>" data-field="quantity" min="1" step="1"></td>
                            <td><span class="view-mode text-xs font-bold text-gray-500 uppercase"><?php echo htmlspecialchars($unit); ?></span><input type="text" class="form-input text-xs edit-mode hidden border-gray-200 rounded w-full" value="<?php echo htmlspecialchars($unit); ?>" data-field="unit"></td>
                            <td><span class="view-mode text-gray-500 text-xs"><?php echo htmlspecialchars($notes); ?></span><input type="text" class="form-input text-xs edit-mode hidden border-gray-200 rounded w-full" value="<?php echo htmlspecialchars($notes); ?>" data-field="notes"></td>
                            <td class="edit-mode-column hidden">
                                <button onclick="removeItem(<?php echo $index; ?>)" class="w-8 h-8 rounded bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition-colors flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($request['request_notes']): ?>
        <div class="mt-8 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Internal Notes</span>
            <div class="text-sm text-gray-600 italic">"<?php echo nl2br(htmlspecialchars($request['request_notes'])); ?>"</div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let isEditMode = false;
let originalItems = [];

function approveRequest(requestId) {
    Swal.fire({
        title: 'Approve Request?',
        text: 'This will authorize the dispatch process.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        confirmButtonText: 'Yes, Approve'
    }).then((result) => {
        if (result.isConfirmed) updateRequestStatus(requestId, 'approved');
    });
}

function rejectRequest(requestId) {
    Swal.fire({
        title: 'Reject Request?',
        text: 'Please provide a reason if necessary.',
        input: 'textarea',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Yes, Reject'
    }).then((result) => {
        if (result.isConfirmed) updateRequestStatus(requestId, 'rejected', result.value);
    });
}

function updateRequestStatus(requestId, status, reason = '') {
    fetch('update-request-status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ request_id: requestId, status: status, reason: reason })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire({ icon: 'success', title: 'Success', text: `Status updated to ${status}` });
            setTimeout(() => location.reload(), 1500);
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    });
}

function toggleEditMode() {
    isEditMode = !isEditMode;
    if (isEditMode) {
        document.querySelectorAll('.view-mode').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.edit-mode').forEach(el => el.classList.remove('hidden'));
        document.querySelectorAll('.edit-mode-column').forEach(el => el.classList.remove('hidden'));
        document.getElementById('editModeActions').classList.remove('hidden');
        document.getElementById('editItemsBtn').classList.add('hidden');
    } else {
        cancelEditMode();
    }
}

function cancelEditMode() {
    location.reload();
}

function removeItem(index) {
    const row = document.querySelector(`#itemsTable tbody tr[data-index="${index}"]`);
    if (row) {
        row.classList.add('opacity-40', 'pointer-events-none', 'bg-slate-50');
        row.dataset.removed = 'true';
    }
}

async function saveItemChanges() {
    const items = [];
    document.querySelectorAll('#itemsTable tbody tr').forEach(row => {
        if (row.dataset.removed !== 'true' && row.dataset.index !== undefined) {
             const item = {
                boq_item_id: row.dataset.boqId,
                item_code: row.querySelector('[data-field="item_code"]').value,
                quantity: row.querySelector('[data-field="quantity"]').value,
                unit: row.querySelector('[data-field="unit"]').value,
                notes: row.querySelector('[data-field="notes"]').value
            };
            items.push(item);
        }
    });

    try {
        const res = await fetch('update-request-items.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ request_id: <?php echo $request['id']; ?>, items: items })
        });
        const data = await res.json();
        if (data.success) {
            Swal.fire('Updated', 'Manifest changes saved successfully', 'success');
            setTimeout(() => location.reload(), 1500);
        }
    } catch(e) {}
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>