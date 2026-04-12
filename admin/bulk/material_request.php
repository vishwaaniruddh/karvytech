<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/BoqMaster.php';
require_once __DIR__ . '/../../models/BoqItem.php';

// Auth::requireRole(ADMIN_ROLE);

$title = 'Bulk Site Requisition';
ob_start();

$boqMasterModel = new BoqMaster();
$boqMasters = $boqMasterModel->getAll();

$boqItemModel = new BoqItem();
$allItems = $boqItemModel->getActive();
?>

<div class="min-h-screen bg-transparent pb-12">
    <!-- Header Area -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                        <div class="p-2 bg-blue-600 rounded-lg text-white shadow-lg shadow-blue-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        </div>
                        Bulk Requisition Engine
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">Deploy material requests across multiple sites with survey validation</p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto px-4 mt-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- STEP 1: Site Selection (4 Cols) -->
            <div class="lg:col-span-4 space-y-6">
                <div class="bg-white rounded-3xl shadow-sm border border-gray-200 p-8">
                    <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest mb-6">1. Target Reconciliation</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Site IDs (Space or Comma Separated)</label>
                            <textarea id="site-query" rows="4" class="w-full px-4 py-3 border border-gray-200 rounded-xl font-bold text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-gray-50/50" placeholder="e.g. 8025, SZ9G T2FP..."></textarea>
                        </div>
                        <button onclick="fetchSites()" class="w-full py-3 bg-gray-900 text-white rounded-xl font-black text-xs uppercase tracking-widest hover:bg-black transition-all active:scale-95">Verify Survey Status</button>
                    </div>

                    <!-- Site Result List -->
                    <div id="site-results" class="hidden mt-8 space-y-3">
                        <div class="flex items-center justify-between px-2 mb-4 text-[10px] font-black uppercase text-gray-400">
                            <div class="flex items-center gap-2">
                                <input type="checkbox" id="select-all-sites" onchange="toggleSelectAll(this.checked)" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <label for="select-all-sites" class="cursor-pointer">Select All Approved</label>
                            </div>
                            <span id="site-stats">0 Found</span>
                        </div>
                        <div id="site-list-container" class="max-h-96 overflow-y-auto pr-2 space-y-2 custom-scrollbar">
                            <!-- Sites will appear here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 2: Configuration & BOQ (8 Cols) -->
            <div class="lg:col-span-8 space-y-6">
                <!-- Global Config -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-200 p-8">
                    <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest mb-6">2. Requisition Parameters</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Required by Date</label>
                            <input type="date" id="required-date" class="w-full px-4 py-3 border border-gray-200 rounded-xl font-bold text-sm focus:ring-2 focus:ring-blue-500 outline-none" value="<?php echo date('Y-m-d', strtotime('+3 days')); ?>">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">BOQ Reference Template</label>
                            <select id="boq-master-id" onchange="loadBoqTemplate(this.value)" class="w-full px-4 py-3 border border-gray-200 rounded-xl font-bold text-sm focus:ring-2 focus:ring-blue-500 outline-none appearance-none bg-gray-50/50 hover:bg-white transition-colors">
                                <option value="">Select BOQ Master...</option>
                                <?php foreach ($boqMasters as $bm): ?>
                                    <option value="<?php echo $bm['id']; ?>"><?php echo htmlspecialchars($bm['boq_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                             <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Internal Request Notes</label>
                             <textarea id="request-notes" rows="2" class="w-full px-4 py-3 border border-gray-200 rounded-xl font-bold text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-gray-50/50" placeholder="Special handling or procurement instructions..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Material Manifest -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden min-h-[400px] flex flex-col">
                    <div class="px-8 py-5 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                        <h3 class="font-black text-gray-900 tracking-tight text-sm uppercase">3. Material Manifest</h3>
                        <div class="flex gap-2">
                             <button onclick="addManualRow()" class="px-4 py-1.5 bg-gray-900 text-white text-[10px] font-black rounded-lg uppercase tracking-widest shadow-md">Manual Add</button>
                        </div>
                    </div>
                    
                    <div class="flex-1">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-white border-b">
                                <tr>
                                    <th class="px-8 py-5 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Material Item</th>
                                    <th class="px-8 py-5 text-[10px] font-bold text-gray-400 uppercase tracking-widest w-32">Qty</th>
                                    <th class="px-8 py-5 text-[10px] font-bold text-gray-400 uppercase tracking-widest w-32">Unit</th>
                                    <th class="px-8 py-5 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="requisition-body" class="divide-y divide-gray-50">
                                <tr id="empty-state">
                                    <td colspan="4" class="text-center py-20 text-gray-400 italic font-medium">No materials defined. Select a BOQ set above or add manually.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-8 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                        <div class="text-xs font-bold text-gray-400">Total Selection: <span id="selected-site-count" class="text-blue-600 font-black">0 Sites</span></div>
                        <button onclick="submitBulkRequisition()" class="px-8 py-4 bg-blue-600 text-white rounded-2xl font-black text-sm uppercase shadow-xl shadow-blue-200 hover:-translate-y-1 transition-all active:scale-95">Finalize & Deploy Requisitions</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const allMasterItems = <?php echo json_encode($allItems); ?>;
let rowCount = 0;

async function fetchSites() {
    const query = document.getElementById('site-query').value;
    if (!query) return;

    const container = document.getElementById('site-list-container');
    const resultsDiv = document.getElementById('site-results');
    
    container.innerHTML = '<div class="text-center py-10 animate-pulse text-[10px] font-black text-blue-500 uppercase">Validating Site Records...</div>';
    resultsDiv.classList.remove('hidden');

    try {
        const res = await fetch('api/fetch-sites-by-ids.php', {
            method: 'POST',
            body: JSON.stringify({ query }),
            headers: { 
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const data = await res.json();
        
        if (data.success) {
            document.getElementById('site-stats').textContent = `${data.sites.length} Found`;
            container.innerHTML = data.sites.map(s => {
                const isApproved = s.approval_status === 'approved';
                return `
                    <div class="p-4 rounded-2xl border ${isApproved ? 'border-gray-100 bg-white hover:border-blue-200' : 'border-rose-100 bg-rose-50 opacity-60'} transition-all flex items-center gap-4">
                        <input type="checkbox" class="site-cb w-5 h-5 rounded-lg border-gray-300 text-blue-600 focus:ring-blue-500" 
                               value="${s.id}" 
                               ${isApproved ? '' : 'disabled'}
                               onchange="updateSelectionCount()">
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-black text-gray-900">${s.site_id}</span>
                                <span class="px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-widest ${isApproved ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'}">
                                    ${s.approval_status || 'No Survey'}
                                </span>
                            </div>
                            <div class="text-[10px] font-bold text-gray-400 mt-0.5 truncate max-w-[180px]">${s.location}</div>
                        </div>
                    </div>
                `;
            }).join('');
            
            if (data.sites.length === 0) {
                 container.innerHTML = '<div class="text-center py-10 text-[10px] font-black text-rose-500 uppercase">No valid sites found for these IDs</div>';
            }
        }
    } catch (e) { console.error(e); }
}

function updateSelectionCount() {
    const checkedCount = document.querySelectorAll('.site-cb:checked').length;
    const totalEnabledCount = document.querySelectorAll('.site-cb:not(:disabled)').length;
    
    document.getElementById('selected-site-count').textContent = `${checkedCount} Sites`;
    
    // Sync select-all checkbox
    const selectAllCb = document.getElementById('select-all-sites');
    if (selectAllCb) {
        selectAllCb.checked = checkedCount > 0 && checkedCount === totalEnabledCount;
        selectAllCb.indeterminate = checkedCount > 0 && checkedCount < totalEnabledCount;
    }
}

function toggleSelectAll(checked) {
    document.querySelectorAll('.site-cb:not(:disabled)').forEach(cb => {
        cb.checked = checked;
    });
    updateSelectionCount();
}

function addManualRow() {
    const emptyState = document.getElementById('empty-state');
    if (emptyState) emptyState.classList.add('hidden');
    rowCount++;
    const row = `
        <tr id="row-${rowCount}" class="group hover:bg-gray-50 transition-colors">
            <td class="px-8 py-6">
                <select onchange="updateRowDetails(${rowCount}, this.value)" class="w-full border-none focus:ring-0 font-bold text-sm bg-transparent">
                    <option value="">Select Material...</option>
                    ${allMasterItems.map(i => `<option value="${i.id}" data-unit="${i.unit}" data-name="${i.item_name}" data-code="${i.item_code}">${i.item_name}</option>`).join('')}
                </select>
            </td>
            <td class="px-8 py-6">
                <input type="number" class="qty-input w-24 px-3 py-1.5 bg-gray-50 rounded-lg font-black text-indigo-600 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-all" value="1">
            </td>
            <td class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest unit-label">Units</td>
            <td class="px-8 py-6 text-center">
                <button onclick="removeRow(${rowCount})" class="text-gray-300 hover:text-rose-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
            </td>
        </tr>
    `;
    document.getElementById('requisition-body').insertAdjacentHTML('beforeend', row);
}

function removeRow(id) {
    const row = document.getElementById(`row-${id}`);
    if (row) row.remove();
    updateEmptyState();
}

function updateEmptyState() {
    const tbody = document.getElementById('requisition-body');
    const rows = tbody.querySelectorAll('tr:not(#empty-state)');
    let emptyState = document.getElementById('empty-state');
    
    if (rows.length === 0) {
        if (!emptyState) {
            tbody.innerHTML = `
                <tr id="empty-state">
                    <td colspan="4" class="text-center py-20 text-gray-400 italic font-medium">No materials defined. Select a BOQ set above or add manually.</td>
                </tr>
            `;
        } else {
            emptyState.classList.remove('hidden');
        }
    } else if (emptyState) {
        emptyState.classList.add('hidden');
    }
}

function updateRowDetails(id, val) {
    const opt = document.querySelector(`#row-${id} option[value="${val}"]`);
    if (opt) { document.querySelector(`#row-${id} .unit-label`).textContent = opt.dataset.unit || 'Units'; }
}

async function loadBoqTemplate(id) {
    if (!id) return;
    try {
        const res = await fetch(`../boq/get-boq-details.php?id=${id}`, {
            headers: { 
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const data = await res.json();
        if (data.success) {
            document.getElementById('requisition-body').innerHTML = '';
            data.items.forEach(item => {
                rowCount++;
                const row = `
                    <tr id="row-${rowCount}" class="hover:bg-gray-50 transition-colors">
                        <td class="px-8 py-6">
                            <span class="text-sm font-black text-gray-900 block" data-id="${item.boq_item_id}">${item.item_name}</span>
                            <span class="text-[9px] font-bold text-gray-400 uppercase tracking-tighter">${item.item_code}</span>
                        </td>
                        <td class="px-8 py-6"><input type="number" class="qty-input w-24 px-3 py-1.5 bg-gray-50 rounded-lg font-black text-indigo-600 outline-none" value="${item.quantity}"></td>
                        <td class="px-8 py-6 text-[10px] font-black text-gray-400 tracking-widest uppercase">${item.unit}</td>
                        <td class="px-8 py-6 text-center"><button onclick="removeRow(${rowCount})" class="text-gray-300 hover:text-rose-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button></td>
                    </tr>
                `;
                document.getElementById('requisition-body').insertAdjacentHTML('beforeend', row);
            });
            Swal.fire({ icon: 'success', title: 'Manifest Updated', text: `Synchronized ${data.items.length} items from template`, timer: 1500, showConfirmButton: false });
        }
    } catch(e) {}
}

async function submitBulkRequisition() {
    const selectedSites = Array.from(document.querySelectorAll('.site-cb:checked')).map(cb => cb.value);
    const items = [];
    document.querySelectorAll('#requisition-body tr:not(#empty-state)').forEach(row => {
        const sel = row.querySelector('select');
        let materialId, materialName, unit, code;
        if (sel) {
            const opt = sel.options[sel.selectedIndex];
            materialId = sel.value; materialName = opt.dataset.name; unit = opt.dataset.unit; code = opt.dataset.code;
        } else {
            const span = row.querySelector('span[data-id]');
            materialId = span.dataset.id; materialName = span.textContent;
            unit = row.cells[2].textContent; code = row.querySelector('span.text-\\[9px\\]').textContent;
        }
        const qty = row.querySelector('.qty-input').value;
        if (materialId && qty > 0) items.push({ material_id: materialId, material_name: materialName, quantity: qty, unit, item_code: code });
    });

    if (selectedSites.length === 0) return Swal.fire('Deployment Error', 'Please select at least one site with an approved survey', 'warning');
    if (items.length === 0) return Swal.fire('Deployment Error', 'Manifest is empty. Please add materials.', 'warning');

    const result = await Swal.fire({
        title: 'Initialize Deployment?',
        text: `You are about to create ${selectedSites.length} separate material requests. This action will be logged.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        confirmButtonText: 'Yes, Deploy Now'
    });

    if (result.isConfirmed) {
        try {
            const res = await fetch('api/material_requests.php?action=create_bulk_admin', {
                method: 'POST',
                body: JSON.stringify({
                    site_ids: selectedSites,
                    items: items,
                    required_date: document.getElementById('required-date').value,
                    notes: document.getElementById('request-notes').value
                }),
                headers: { 
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Deployment Finalized', text: 'All requisitions have been successfully commissioned', showConfirmButton: false, timer: 2000 });
                setTimeout(() => backToHub(), 2000);
            }
        } catch(e) {}
    }
}
</script>

<?php
$content = ob_get_clean();
if (isset($_GET['ajax'])) {
    echo $content;
} else {
    require_once __DIR__ . '/../../includes/admin_layout.php';
}
?>
