<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/Inventory.php';
require_once __DIR__ . '/../../models/BoqItem.php';
require_once __DIR__ . '/../../models/Warehouse.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$inventoryModel = new Inventory();
$boqModel = new BoqItem();
$warehouseModel = new Warehouse();

// Fetch filter options (these are small/fast)
$categories = $boqModel->getCategories();
$warehouses = $warehouseModel->getAll('', 'active');

$title = 'Inventory Hub';
ob_start();
?>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Inventory Hub</h1>
        <p class="text-[13px] font-medium text-gray-500 uppercase tracking-wide">Real-time Stock Intelligence & Warehouse Manifest</p>
    </div>
    <div class="flex flex-wrap items-center gap-3">
        <a href="inwards/" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 rounded-xl text-xs font-bold uppercase tracking-wider transition-all shadow-sm flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
            Inward Logistics
        </a>
        <a href="dispatches/" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 rounded-xl text-xs font-bold uppercase tracking-wider transition-all shadow-sm flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7M5 12h11"/></svg>
            Outbound Manifest
        </a>
        <a href="stock-entries/add-individual-stock.php" class="px-5 py-2.5 bg-gray-900 text-white hover:bg-gray-800 rounded-xl text-xs font-bold uppercase tracking-wider transition-all shadow-md flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Ingest Materials
        </a>
    </div>
</div>

<!-- Statistics Intelligence Matrix (Skeleton) -->
<div id="statsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-6 mb-8">
    <?php for($i=0; $i<5; $i++): ?>
    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm animate-pulse">
        <div class="h-3 w-20 bg-gray-100 rounded mb-4"></div>
        <div class="flex items-end justify-between">
            <div>
                <div class="h-8 w-16 bg-gray-200 rounded"></div>
                <div class="h-2 w-12 bg-gray-50 rounded mt-2"></div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-gray-100"></div>
        </div>
    </div>
    <?php endfor; ?>
</div>

<!-- Refine manifest parameters -->
<div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm mb-8">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="lg:col-span-1">
            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 block">Search Ledger</label>
            <div class="relative">
                <input type="text" id="searchInput" class="w-full bg-gray-50 border-none rounded-xl px-4 py-2.5 text-sm font-semibold placeholder-gray-400 focus:ring-2 focus:ring-gray-900 transition-all" placeholder="Item name or code...">
            </div>
        </div>
        <div>
            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 block">Warehouse Node</label>
            <select id="warehouseFilter" class="w-full bg-gray-50 border-none rounded-xl px-4 py-2.5 text-sm font-semibold focus:ring-2 focus:ring-gray-900 transition-all">
                <option value="">Global Network</option>
                <?php foreach ($warehouses as $wh): ?>
                    <option value="<?php echo $wh['id']; ?>"><?php echo htmlspecialchars($wh['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 block">Classification</label>
            <select id="categoryFilter" class="w-full bg-gray-50 border-none rounded-xl px-4 py-2.5 text-sm font-semibold focus:ring-2 focus:ring-gray-900 transition-all">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="flex items-end pb-1">
            <label class="flex items-center cursor-pointer group">
                <div class="relative">
                    <input type="checkbox" id="lowStockFilter" class="sr-only">
                    <div class="block bg-gray-200 w-10 h-6 rounded-full transition-all peer-checked:bg-gray-900 shadow-inner"></div>
                    <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-all shadow-sm"></div>
                </div>
                <span class="ml-3 text-xs font-bold text-gray-500 uppercase tracking-wider group-hover:text-gray-900 transition-colors">Critical Understocks</span>
            </label>
        </div>
    </div>
</div>

<!-- Stock Intelligence Table -->
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-8">
    <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-200 flex justify-between items-center">
        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Global Stock Manifest</h3>
        <a href="stock-entries/" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 uppercase tracking-widest flex items-center gap-1 transition-colors">
            Detailed Entry Logs
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
        </a>
    </div>
    <div class="overflow-x-auto relative min-h-[400px]">
        <!-- Loading Overlay -->
        <div id="tableLoading" class="absolute inset-0 bg-white/60 backdrop-blur-[1px] flex items-center justify-center z-10 transition-opacity duration-300">
            <div class="flex flex-col items-center">
                <div class="w-10 h-10 border-4 border-gray-900 border-t-transparent rounded-full animate-spin mb-3"></div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Syncing Manifest...</p>
            </div>
        </div>

        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50/50">
                <tr>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-left w-12">#</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-left">Asset Identity</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-left">Warehouse Node</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-left">Volume Analysis</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-left">Operational Flow</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-left">Valuation</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-center">Actions</th>
                </tr>
            </thead>
            <tbody id="inventoryTableBody" class="divide-y divide-gray-50 text-xs text-gray-500 italic">
                <tr><td colspan="7" class="py-10 text-center">Synchronizing assets...</td></tr>
            </tbody>
        </table>
    </div>
    
    <!-- Unified Pagination Footer (Matching admin/sites style) -->
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

<style>
#lowStockFilter:checked ~ .dot {
    transform: translateX(100%);
}
#lowStockFilter:checked ~ div:first-of-type {
    background-color: #111827;
}
</style>

<script>
let currentPage = 1;
const limit = 10;

document.addEventListener('DOMContentLoaded', function() {
    loadStats();
    loadInventory(1);
});

// Stats Loader
async function loadStats() {
    try {
        const response = await fetch('../../api/inventory.php?action=get_stats');
        const result = await response.json();
        
        if (result.success) {
            const stats = result.data;
            const statsGrid = document.getElementById('statsGrid');
            
            const statsHTML = `
                <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm transition-all hover:shadow-md">
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Inventory Depth</p>
                    <div class="flex items-end justify-between">
                        <div>
                            <div class="text-2xl font-bold text-gray-900 tracking-tight">${new Intl.NumberFormat().format(stats.total_items)}</div>
                            <div class="text-[10px] font-bold text-gray-400 uppercase mt-1">Unique Materials</div>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm transition-all hover:shadow-md">
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Asset Valuation</p>
                    <div class="flex items-end justify-between">
                        <div>
                            <div class="text-2xl font-bold text-emerald-600 tracking-tight">₹${(stats.total_value / 100000).toFixed(2)}L</div>
                            <div class="text-[10px] font-bold text-gray-400 uppercase mt-1">Total Book Value</div>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm transition-all hover:shadow-md">
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Transit Volume</p>
                    <div class="flex items-end justify-between">
                        <div>
                            <div class="text-2xl font-bold text-amber-600 tracking-tight">${new Intl.NumberFormat().format(stats.dispatched_quantity || 0)}</div>
                            <div class="text-[10px] font-bold text-gray-400 uppercase mt-1">Active Outbounds</div>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm transition-all hover:shadow-md">
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Data Integrity</p>
                    <div class="flex items-end justify-between">
                        <div>
                            <div class="text-2xl font-bold text-indigo-600 tracking-tight">${new Intl.NumberFormat().format(stats.total_entries || 0)}</div>
                            <div class="text-[10px] font-bold text-gray-400 uppercase mt-1">Serial Logs</div>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm transition-all hover:shadow-md">
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Pending Ops</p>
                    <div class="flex items-end justify-between">
                        <div>
                            <div class="text-2xl font-bold text-rose-600 tracking-tight">${new Intl.NumberFormat().format(stats.pending_dispatches)}</div>
                            <div class="text-[10px] font-bold text-gray-400 uppercase mt-1">Queued Transfers</div>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-rose-50 flex items-center justify-center text-rose-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                </div>
            `;
            statsGrid.innerHTML = statsHTML;
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

// Inventory Loader
async function loadInventory(page = 1) {
    currentPage = page;
    const loading = document.getElementById('tableLoading');
    if (loading) {
        loading.style.opacity = '1';
        loading.classList.remove('hidden');
        loading.style.display = 'flex';
    }

    const search = document.getElementById('searchInput').value;
    const warehouseId = document.getElementById('warehouseFilter').value;
    const category = document.getElementById('categoryFilter').value;
    const lowStock = document.getElementById('lowStockFilter').checked ? '1' : '0';

    try {
        const queryParams = new URLSearchParams({
            action: 'get_overview',
            search,
            warehouse_id: warehouseId,
            category,
            low_stock: lowStock,
            page: page,
            limit: limit
        });

        const response = await fetch(`../../api/inventory.php?${queryParams.toString()}`);
        const result = await response.json();

        if (result.success) {
            renderTable(result.data.items, page);
            renderPagination(result.data);
        }
    } catch (error) {
        console.error('Error loading inventory:', error);
    } finally {
        if (loading) {
            loading.style.opacity = '0';
            setTimeout(() => { 
                loading.classList.add('hidden');
                loading.style.display = 'none';
            }, 300);
        }
    }
}

function renderTable(items, page) {
    const tbody = document.getElementById('inventoryTableBody');
    if (!tbody) return;

    if (!items || items.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-20 text-gray-400 font-bold italic">No materials found in current registry</td></tr>';
        return;
    }

    const startNum = ((page - 1) * limit) + 1;

    tbody.innerHTML = items.map((item, index) => {
        const availableStock = parseFloat(item.available_stock || 0);
        const totalStock = parseFloat(item.total_stock || 0);
        
        let status;
        if (totalStock === 0) status = { c: 'rose', l: 'Depleted' };
        else if (availableStock === 0) status = { c: 'rose', l: 'Stock-Out' };
        else if (availableStock < (totalStock * 0.2)) status = { c: 'amber', l: 'Critical' };
        else status = { c: 'emerald', l: 'Standard' };

        return `
            <tr class="hover:bg-gray-50/50 transition-colors group">
                <td class="px-6 py-5 text-xs font-bold text-gray-400">${startNum + index}</td>
                <td class="px-6 py-5">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 group-hover:bg-white group-hover:text-blue-600 transition-all border border-transparent group-hover:border-gray-100 mr-4 shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-gray-900 leading-tight">${item.item_name}</div>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter truncate max-w-[120px]">${item.item_code}</span>
                                <span class="px-1.5 py-0.5 bg-gray-100 text-gray-500 rounded text-[9px] font-bold uppercase tracking-widest whitespace-nowrap">${item.category || 'General'}</span>
                            </div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-5">
                    <div class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <span class="text-xs font-bold text-gray-700 uppercase tracking-tight">${item.warehouse_name || 'Central Hub'}</span>
                    </div>
                </td>
                <td class="px-6 py-5">
                    <div class="flex items-center gap-4">
                        <div>
                            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter mb-1">Total Assets</div>
                            <div class="text-sm font-bold text-gray-900">${new Intl.NumberFormat().format(totalStock)} <span class="text-[10px] font-medium text-gray-400">${item.unit}</span></div>
                        </div>
                        <div class="h-8 w-px bg-gray-100"></div>
                        <div>
                            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter mb-1">Operational</div>
                            <div class="text-sm font-bold text-blue-600">${new Intl.NumberFormat().format(availableStock)}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-5">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-widest bg-${status.c}-50 text-${status.c}-700 border border-${status.c}-100">
                        ${status.l}
                    </span>
                    <div class="mt-2 text-[10px] font-bold text-gray-400 uppercase tracking-tighter">
                        In-Transit: ${new Intl.NumberFormat().format(item.dispatched_stock || 0)} units
                    </div>
                </td>
                <td class="px-6 py-5">
                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter mb-1">Market Value</div>
                    <div class="text-sm font-bold text-emerald-600 tracking-tight">₹${new Intl.NumberFormat('en-IN').format(parseFloat(item.total_value || 0).toFixed(2))}</div>
                </td>
                <td class="px-6 py-5">
                    <div class="flex items-center justify-center gap-2">
                        <button onclick="viewStockDetails(${item.boq_item_id})" class="p-2 bg-white border border-gray-200 text-gray-400 hover:text-blue-600 hover:border-blue-100 hover:bg-blue-50 rounded-lg transition-all shadow-sm" title="Intelligence Report">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
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
        html += `<button onclick="loadInventory(1)" ${pg.page === 1 ? 'disabled' : ''} class="px-2 py-2 rounded-l-md border border-gray-300 bg-white text-[11px] font-bold uppercase text-gray-500 hover:bg-gray-50 disabled:opacity-50">First</button>`;
        
        for (let i = Math.max(1, pg.page - 2); i <= Math.min(pg.pages, pg.page + 2); i++) {
            html += `<button onclick="loadInventory(${i})" class="px-4 py-2 border text-xs font-bold ${i === pg.page ? 'bg-gray-900 border-gray-900 text-white shadow-md z-10' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'}">${i}</button>`;
        }

        html += `<button onclick="loadInventory(${pg.pages})" ${pg.page === pg.pages ? 'disabled' : ''} class="px-2 py-2 rounded-r-md border border-gray-300 bg-white text-[11px] font-bold uppercase text-gray-500 hover:bg-gray-50 disabled:opacity-50">Last</button>`;
        nav.innerHTML = html;
        
        // Mobile pagination can be a simpler version
        const mobile = document.getElementById('paginationMobile');
        if (mobile) {
            mobile.innerHTML = `
                <button onclick="loadInventory(${pg.page - 1})" ${pg.page === 1 ? 'disabled' : ''} class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">Previous</button>
                <button onclick="loadInventory(${pg.page + 1})" ${pg.page === pg.pages ? 'disabled' : ''} class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">Next</button>
            `;
        }
    } catch (e) { console.error('Error in renderPagination:', e); }
}

// Events
function debounce(func, wait) {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

document.getElementById('searchInput').addEventListener('keyup', debounce(() => loadInventory(1), 500));
document.getElementById('warehouseFilter').addEventListener('change', () => loadInventory(1));
document.getElementById('categoryFilter').addEventListener('change', () => loadInventory(1));
document.getElementById('lowStockFilter').addEventListener('change', () => loadInventory(1));

function viewStockDetails(boqItemId) {
    window.open(`stock-details.php?item_id=${boqItemId}`, '_blank');
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>