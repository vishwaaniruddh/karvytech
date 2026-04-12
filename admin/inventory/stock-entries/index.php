<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../models/Inventory.php';
require_once __DIR__ . '/../../../models/BoqItem.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$inventoryModel = new Inventory();
$boqModel = new BoqItem();

// Get active BOQ items for the filter
$boqItems = $boqModel->getActive();

$title = 'Individual Stock Registry';
ob_start();
?>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Individual Stock Registry</h1>
        <p class="text-[13px] font-medium text-gray-500 uppercase tracking-wide">Granular Material Tracking & Audit Logs</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="../" class="p-2.5 bg-white border border-gray-200 text-gray-400 hover:text-gray-900 hover:border-gray-900 rounded-xl transition-all shadow-sm" title="Back to Hub">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
        </a>
        <button onclick="exportStockEntries()" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 rounded-xl text-xs font-bold uppercase tracking-wider transition-all shadow-sm flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Export Logs
        </button>
        <button onclick="openModal('addStockEntryModal')" class="px-5 py-2.5 bg-gray-900 text-white hover:bg-black rounded-xl text-xs font-bold uppercase tracking-wider transition-all shadow-lg flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Ingest Entry
        </button>
    </div>
</div>

<!-- Registry Insights (Skeleton) -->
<div id="entriesStats" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <?php for($i=0; $i<4; $i++): ?>
    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm animate-pulse h-[100px]"></div>
    <?php endfor; ?>
</div>

<!-- Refine Registry Parameters -->
<div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm mb-8">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 block">Search Batch / Serial</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" id="searchInput" class="w-full bg-gray-50 border-none rounded-xl pl-10 pr-4 py-2.5 text-sm font-semibold focus:ring-2 focus:ring-gray-900 transition-all placeholder:text-gray-400" placeholder="Search item, batch, serial...">
            </div>
        </div>
        <div>
            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 block">Material Ledger</label>
            <select id="boqItemFilter" class="w-full bg-gray-50 border-none rounded-xl px-4 py-2.5 text-sm font-semibold focus:ring-2 focus:ring-gray-900 transition-all cursor-pointer">
                <option value="">Full Registry</option>
                <?php foreach ($boqItems as $item): ?>
                    <option value="<?php echo $item['id']; ?>">
                        <?php echo htmlspecialchars($item['item_name']); ?> (<?php echo htmlspecialchars($item['item_code']); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 block">Current Placement</label>
            <select id="locationFilter" class="w-full bg-gray-50 border-none rounded-xl px-4 py-2.5 text-sm font-semibold focus:ring-2 focus:ring-gray-900 transition-all cursor-pointer">
                <option value="">Global Network</option>
                <option value="warehouse">Warehouse Node</option>
                <option value="site">Project Site</option>
                <option value="vendor">Vendor Base</option>
                <option value="in_transit">In Transit / Logistics</option>
            </select>
        </div>
    </div>
</div>

<!-- Registry Data Table -->
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-8">
    <div class="overflow-x-auto relative">
        <div id="tableLoading" class="absolute inset-0 bg-white/60 backdrop-blur-[1px] flex items-center justify-center z-10 transition-opacity">
            <div class="flex flex-col items-center">
                <div class="w-10 h-10 border-4 border-gray-900 border-t-transparent rounded-full animate-spin"></div>
            </div>
        </div>

        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50/50 text-[11px] font-bold text-gray-400 uppercase tracking-widest">
                <tr>
                    <th class="px-6 py-4 text-left">Item Analysis</th>
                    <th class="px-6 py-4 text-left">Batch/Serial Identity</th>
                    <th class="px-6 py-4 text-left">Placement & Quality</th>
                    <th class="px-6 py-4 text-left">Timeline</th>
                    <th class="px-6 py-4 text-left">Valuation</th>
                    <th class="px-6 py-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody id="stockEntriesTableBody" class="divide-y divide-gray-50">
                <!-- Data will be dynamically injected -->
            </tbody>
        </table>
    </div>

    <!-- Unified Pagination Footer -->
    <div class="bg-white px-4 py-3 border-t border-gray-200 flex items-center justify-between sm:px-6">
        <div class="flex-1 flex justify-between sm:hidden" id="paginationMobile"></div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-[11px] text-gray-700" id="paginationSummary">
                    Showing <span class="font-bold">0</span> to <span class="font-bold">0</span> of <span class="font-bold">0</span> results
                </p>
            </div>
            <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination" id="paginationDesktop"></nav>
            </div>
        </div>
    </div>
</div>

<!-- Modal Framework -->
<div id="addStockEntryModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeModal('addStockEntryModal')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-2xl bg-white rounded-3xl shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300">
        <div class="px-8 py-6 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-xl font-bold text-gray-900">Ingest Individual Material</h3>
                <p class="text-xs font-medium text-gray-500 mt-0.5">Define metadata for a single item entry</p>
            </div>
            <button onclick="closeModal('addStockEntryModal')" class="w-10 h-10 rounded-xl hover:bg-white hover:shadow-sm text-gray-400 hover:text-gray-900 transition-all flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="addStockEntryForm" class="p-8">
            <div class="grid grid-cols-2 gap-6 mb-8">
                <div class="col-span-2">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 block">Asset Selection</label>
                    <select name="boq_item_id" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-semibold focus:ring-2 focus:ring-gray-900 transition-all" required>
                        <option value="">Select Material From Ledger</option>
                        <?php foreach ($boqItems as $item): ?>
                            <option value="<?php echo $item['id']; ?>"><?php echo htmlspecialchars($item['item_name']); ?> (<?php echo htmlspecialchars($item['item_code']); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 block">Unit Cost (₹)</label>
                    <input type="number" name="unit_cost" step="0.01" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-semibold focus:ring-2 focus:ring-gray-900 transition-all" placeholder="0.00" required>
                </div>
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 block">Batch / LOT Number</label>
                    <input type="text" name="batch_number" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-semibold focus:ring-2 focus:ring-gray-900 transition-all" placeholder="Enter batch ID...">
                </div>
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 block">Serial Physical UID</label>
                    <input type="text" name="serial_number" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-semibold focus:ring-2 focus:ring-gray-900 transition-all" placeholder="Unique serial number...">
                </div>
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 block">Operational Placement</label>
                    <select name="location_type" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-semibold focus:ring-2 focus:ring-gray-900 transition-all" required>
                        <option value="warehouse">Warehouse Node</option>
                        <option value="site">Project Site</option>
                        <option value="vendor">Vendor Base</option>
                        <option value="in_transit">In Transit</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="closeModal('addStockEntryModal')" class="flex-1 py-3 text-sm font-bold text-gray-500 hover:text-gray-900 transition-colors uppercase tracking-widest">Cancel</button>
                <button type="submit" class="flex-[2] py-3 bg-gray-900 text-white rounded-xl text-sm font-bold uppercase tracking-widest hover:bg-black transition-all shadow-lg shadow-gray-200">Finalize Entry</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let currentPage = 1;

document.addEventListener('DOMContentLoaded', () => {
    loadStats();
    loadEntries();
});

async function loadStats() {
    try {
        const response = await fetch('../../../api/inventory.php?action=get_stats');
        const res = await response.json();
        if (res.success) {
            const stats = res.data;
            document.getElementById('entriesStats').innerHTML = `
                <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm transition-all hover:shadow-md">
                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Material volume</div>
                    <div class="text-2xl font-bold text-gray-900">${new Intl.NumberFormat().format(stats.total_entries)}</div>
                    <div class="text-[10px] font-medium text-gray-400 mt-1 uppercase">Total serial logs</div>
                </div>
                <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm transition-all hover:shadow-md">
                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Live Valuation</div>
                    <div class="text-2xl font-bold text-emerald-600">₹${(stats.total_value / 100000).toFixed(2)}L</div>
                    <div class="text-[10px] font-medium text-gray-400 mt-1 uppercase">Book Asset Value</div>
                </div>
                <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm transition-all hover:shadow-md">
                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Quality Index</div>
                    <div class="text-2xl font-bold text-blue-600">${new Intl.NumberFormat().format(stats.available_quantity)}</div>
                    <div class="text-[10px] font-medium text-gray-400 mt-1 uppercase">A-Grade Operational</div>
                </div>
                <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm transition-all hover:shadow-md">
                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Recent Flux</div>
                    <div class="text-2xl font-bold text-indigo-600">+${new Intl.NumberFormat().format(stats.recent_additions)}</div>
                    <div class="text-[10px] font-medium text-gray-400 mt-1 uppercase">Additions (7D)</div>
                </div>
            `;
        }
    } catch (e) { console.error(e); }
}

async function loadEntries(page = 1) {
    currentPage = page;
    const loading = document.getElementById('tableLoading');
    if (loading) {
        loading.style.opacity = '1';
        loading.classList.remove('hidden');
    }

    const search = document.getElementById('searchInput').value;
    const boqItemId = document.getElementById('boqItemFilter').value;
    const location = document.getElementById('locationFilter').value;

    try {
        const query = new URLSearchParams({
            action: 'get_stock_entries',
            page,
            search,
            boq_item_id: boqItemId,
            location
        });

        const res = await fetch(`../../../api/inventory.php?${query.toString()}`);
        const result = await res.json();
        
        if (result.success) {
            renderTable(result.data.entries);
            renderPagination(result.data);
        }
    } catch (e) { console.error(e); }
    finally {
        if (loading) {
            loading.style.opacity = '0';
            setTimeout(() => loading.classList.add('hidden'), 300);
        }
    }
}

function renderTable(entries) {
    const tbody = document.getElementById('stockEntriesTableBody');
    if (!tbody) return;

    if (!entries || entries.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-20 text-gray-400 font-bold italic">No log items found in registry</td></tr>';
        return;
    }

    tbody.innerHTML = entries.map(entry => `
        <tr class="hover:bg-gray-50/50 transition-colors group">
            <td class="px-6 py-5">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 border border-transparent group-hover:border-gray-200 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-gray-900">${entry.item_name}</div>
                        <div class="text-[10px] font-bold text-indigo-600 uppercase tracking-tighter mt-0.5">${entry.item_code}</div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-5">
                <div class="text-xs font-bold text-gray-700">${entry.batch_number ? 'BATCH: ' + entry.batch_number : '--'}</div>
                <div class="text-[10px] font-medium text-gray-400 uppercase tracking-tighter mt-1">SERIAL: ${entry.serial_number || 'GENERIC LOG'}</div>
            </td>
            <td class="px-6 py-5">
                <div class="flex items-center gap-2 mb-1">
                    <span class="w-1.5 h-1.5 rounded-full ${entry.location_type === 'warehouse' ? 'bg-emerald-500' : 'bg-amber-500'}"></span>
                    <span class="text-xs font-bold text-gray-700 uppercase">${entry.location_type}</span>
                </div>
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-widest ${entry.quality_status === 'good' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'}">
                    ${entry.quality_status} condition
                </span>
            </td>
            <td class="px-6 py-5 text-sm">
                <div class="text-xs font-bold text-gray-900">${entry.purchase_date ? new Date(entry.purchase_date).toLocaleDateString() : '--'}</div>
                <div class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mt-1">Entry Timestamp</div>
            </td>
            <td class="px-6 py-5">
                <div class="text-sm font-bold text-gray-900">₹${new Intl.NumberFormat('en-IN').format(parseFloat(entry.unit_cost || 0).toFixed(2))}</div>
                <div class="text-[9px] font-bold text-emerald-600 uppercase tracking-widest mt-1">Acquisition Cost</div>
            </td>
            <td class="px-6 py-5 text-center">
                <div class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button class="w-8 h-8 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-400 hover:text-gray-900 transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    </button>
                    <button onclick="markDamaged(${entry.id})" class="w-8 h-8 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-400 hover:text-rose-600 transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function renderPagination(pg) {
    try {
        const summary = document.getElementById('paginationSummary');
        if (summary) {
            summary.innerHTML = `Showing <span class="font-bold">${((pg.page - 1) * pg.limit) + 1}</span> to <span class="font-bold">${Math.min(pg.page * pg.limit, pg.total)}</span> of <span class="font-bold">${pg.total}</span> results`;
        }
        
        const nav = document.getElementById('paginationDesktop');
        if (!nav) return;
        
        let html = '';
        html += `<button onclick="loadEntries(1)" ${pg.page === 1 ? 'disabled' : ''} class="px-2 py-2 rounded-l-md border border-gray-300 bg-white text-[11px] font-bold uppercase text-gray-500 hover:bg-gray-50 disabled:opacity-50">First</button>`;
        
        for (let i = Math.max(1, pg.page - 2); i <= Math.min(pg.pages, pg.page + 2); i++) {
            html += `<button onclick="loadEntries(${i})" class="px-4 py-2 border text-xs font-bold ${i === pg.page ? 'bg-gray-900 border-gray-900 text-white shadow-md z-10' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'}">${i}</button>`;
        }

        html += `<button onclick="loadEntries(${pg.pages})" ${pg.page === pg.pages ? 'disabled' : ''} class="px-2 py-2 rounded-r-md border border-gray-300 bg-white text-[11px] font-bold uppercase text-gray-500 hover:bg-gray-50 disabled:opacity-50">Last</button>`;
        nav.innerHTML = html;
        
        const mobile = document.getElementById('paginationMobile');
        if (mobile) {
            mobile.innerHTML = `
                <button onclick="loadEntries(${pg.page - 1})" ${pg.page === 1 ? 'disabled' : ''} class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">Previous</button>
                <button onclick="loadEntries(${pg.page + 1})" ${pg.page === pg.pages ? 'disabled' : ''} class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">Next</button>
            `;
        }
    } catch (e) { console.error('Error in renderPagination:', e); }
}

// Modal Control
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

// Filters with debounce
function debounce(func, wait) {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

document.getElementById('searchInput').addEventListener('keyup', debounce(() => loadEntries(1), 500));
document.getElementById('boqItemFilter').addEventListener('change', () => loadEntries(1));
document.getElementById('locationFilter').addEventListener('change', () => loadEntries(1));

function exportStockEntries() {
    window.open('export-stock-entries.php', '_blank');
}

function markDamaged(id) {
    Swal.fire({
        title: 'Report Damage',
        text: 'Confirm material degradation entry?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Confirm'
    }).then(res => {
        if (res.isConfirmed) {
            // API call logic
            console.log('Reporting damage for ' + id);
        }
    });
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../includes/admin_layout.php';
?>