<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/Inventory.php';
require_once __DIR__ . '/../../models/BoqItem.php';
require_once __DIR__ . '/../../models/Warehouse.php';

// Require module access
Auth::requireModuleAccess('inventory');

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
<!-- Stock Intelligence Table -->
<div class="v-table-wrap mb-8">
    <div id="tableLoading" class="v-table-loading">
        <div class="spinner"></div>
    </div>

    <div style="padding: 16px 20px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; background: #fff;">
        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Global Stock Manifest</h3>
        <a href="stock-entries/" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 uppercase tracking-widest flex items-center gap-1 transition-colors">
            Detailed Entry Logs
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
        </a>
    </div>

    <div style="overflow-x: auto;">
        <table class="v-table">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Asset Identity</th>
                    <th>Warehouse Node</th>
                    <th>Volume Analysis</th>
                    <th>Operational Flow</th>
                    <th>Valuation</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody id="inventoryTableBody">
                <tr><td colspan="7" style="padding: 40px; text-align: center; color: #94a3b8; font-weight: 500;">Synchronizing assets...</td></tr>
            </tbody>
        </table>
    </div>
    
    <!-- Unified Pagination Footer -->
    <div id="paginationContainer" class="v-pag">
        <!-- Will be populated by renderPagination() -->
    </div>
</div>

<style>
/* ── Premium Settings CSS ── */
.v-table-wrap{background:#fff;border:1px solid #f1f5f9;border-radius:16px;overflow:hidden;position:relative;min-height:300px}
.v-table-loading{position:absolute;inset:0;background:rgba(255,255,255,.85);z-index:10;display:none;align-items:center;justify-content:center}
.v-table-loading.show{display:flex}
.v-table-loading .spinner{width:32px;height:32px;border:3px solid #e2e8f0;border-top-color:#6366f1;border-radius:50%;animation:spin .6s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
.v-table{width:100%;border-collapse:separate;border-spacing:0}
.v-table thead{background:linear-gradient(135deg,#f8fafc,#f1f5f9)}
.v-table th{padding:12px 16px;text-align:left;font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#64748b;border-bottom:1px solid #e2e8f0;white-space:nowrap}
.v-table td{padding:14px 16px;font-size:13px;color:#334155;border-bottom:1px solid #f8fafc;vertical-align:middle}
.v-table tbody tr{transition:all .15s ease}
.v-table tbody tr:hover{background:#fafbff}

.v-row-num{width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;background:#f1f5f9;border-radius:8px;font-size:11px;font-weight:700;color:#94a3b8}

.v-avatar{width:36px;height:36px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;flex-shrink:0;transition:transform .2s ease}
.v-table tbody tr:hover .v-avatar{transform:scale(1.1)}
.v-avatar-blue{background:#eff6ff;color:#3b82f6}

.v-name{font-weight:600;color:#0f172a;cursor:pointer;transition:color .15s}
.v-table tbody tr:hover .v-name{color:#4f46e5}
.v-code{font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.02em;margin-top:1px}

.v-pill{display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:100px;font-size:10px;font-weight:700;letter-spacing:.04em;text-transform:uppercase}
.v-pill-active{background:#ecfdf5;color:#059669}
.v-pill-warning{background:#fffbeb;color:#d97706}
.v-pill-critical{background:#fef2f2;color:#dc2626}

.v-act{display:flex;align-items:center;gap:5px;justify-content:flex-end}
.v-act-btn{width:30px;height:30px;display:inline-flex;align-items:center;justify-content:center;border-radius:9px;border:1px solid transparent;cursor:pointer;transition:all .2s ease;position:relative;background:transparent;padding:0}
.v-act-btn svg{width:14px;height:14px}
.v-act-btn.v-view{color:#94a3b8}
.v-act-btn.v-view:hover{background:#eff6ff;color:#3b82f6;border-color:#bfdbfe}
.v-act-btn[data-tip]:hover::after{content:attr(data-tip);position:absolute;bottom:calc(100% + 6px);left:50%;transform:translateX(-50%);padding:4px 8px;background:#0f172a;color:#fff;font-size:10px;font-weight:600;border-radius:6px;white-space:nowrap;z-index:10;pointer-events:none;animation:tipFade .15s ease}
.v-act-btn[data-tip]:hover::before{content:'';position:absolute;bottom:calc(100% + 2px);left:50%;transform:translateX(-50%);border:4px solid transparent;border-top-color:#0f172a;z-index:10}
@keyframes tipFade{from{opacity:0;transform:translateX(-50%) translateY(4px)}to{opacity:1;transform:translateX(-50%) translateY(0)}}

.v-pag{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-top:1px solid #f1f5f9;flex-wrap:wrap;gap:12px;background:#fff;}
.v-pag-info{font-size:12px;font-weight:500;color:#64748b}
.v-pag-info strong{font-weight:700;color:#0f172a}
.v-pag-nav{display:flex;align-items:center;gap:4px}
.v-pag-btn{min-width:32px;height:32px;display:inline-flex;align-items:center;justify-content:center;border-radius:8px;border:1px solid #e2e8f0;background:#fff;font-size:12px;font-weight:600;color:#475569;cursor:pointer;transition:all .2s ease;text-decoration:none;padding:0 6px}
.v-pag-btn:hover{background:#f8fafc;border-color:#c7d2fe;color:#4f46e5}
.v-pag-btn.active{background:linear-gradient(135deg,#4f46e5,#6366f1);color:#fff;border-color:transparent;box-shadow:0 2px 6px rgba(99,102,241,.3)}
.v-pag-btn.disabled{opacity:.4;cursor:not-allowed;pointer-events:none}

/* ── Premium Inventory Styles ── */
#lowStockFilter:checked ~ .dot { transform: translateX(100%); }
#lowStockFilter:checked ~ div:first-of-type { background-color: #111827; }

.stat-card {
    position: relative;
    border-radius: 20px;
    padding: 24px 28px;
    color: #ffffff;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 140px;
}
.stat-card::before {
    content: '';
    position: absolute;
    top: -50px;
    right: -50px;
    width: 150px;
    height: 150px;
    border-radius: 50%;
    opacity: 0.08;
    transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}
.stat-card:hover { transform: translateY(-4px); }
.stat-card:hover::before { opacity: 0.14; transform: scale(1.2); }

.stat-card.card-slate { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); box-shadow: 0 8px 32px rgba(15, 23, 42, 0.25); }
.stat-card.card-slate::before { background: #3b82f6; }

.stat-card.card-cyan { background: linear-gradient(135deg, #164e63 0%, #0891b2 100%); box-shadow: 0 8px 32px rgba(8, 145, 178, 0.25); }
.stat-card.card-cyan::before { background: #22d3ee; }

.stat-card.card-amber { background: linear-gradient(135deg, #78350f 0%, #92400e 100%); box-shadow: 0 8px 32px rgba(146, 64, 14, 0.2); }
.stat-card.card-amber::before { background: #fbbf24; }

.stat-card.card-green { background: linear-gradient(135deg, #064e3b 0%, #065f46 100%); box-shadow: 0 8px 32px rgba(6, 95, 70, 0.2); }
.stat-card.card-green::before { background: #34d399; }

.stat-card.card-purple { background: linear-gradient(135deg, #2e1065 0%, #4c1d95 100%); box-shadow: 0 8px 32px rgba(76, 29, 149, 0.2); }
.stat-card.card-purple::before { background: #a78bfa; }

.stat-card.card-rose { background: linear-gradient(135deg, #881337 0%, #be123c 100%); box-shadow: 0 8px 32px rgba(190, 18, 60, 0.2); }
.stat-card.card-rose::before { background: #fb7185; }

.stat-value {
    font-size: 2rem;
    font-weight: 900;
    line-height: 1;
    color: #ffffff;
    font-variant-numeric: tabular-nums;
    letter-spacing: -0.03em;
}
.stat-label {
    font-size: 0.65rem;
    font-weight: 800;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.6);
    margin-top: 8px;
}
.stat-icon-ring {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(8px);
    transition: all 0.3s ease;
}
.stat-card:hover .stat-icon-ring {
    background: rgba(255, 255, 255, 0.14);
    transform: scale(1.08);
}
.stat-icon-ring svg { width: 22px; height: 22px; }
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
                <div class="stat-card card-slate">
                    <div style="display:flex; align-items:flex-start; justify-content:space-between;">
                        <div>
                            <div class="stat-value">${new Intl.NumberFormat().format(stats.total_items)}</div>
                            <div class="stat-label">Inventory Depth</div>
                        </div>
                        <div class="stat-icon-ring">
                            <svg fill="none" stroke="#60a5fa" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                    </div>
                </div>

                <div class="stat-card card-green">
                    <div style="display:flex; align-items:flex-start; justify-content:space-between;">
                        <div>
                            <div class="stat-value">₹${(stats.total_value / 100000).toFixed(2)}L</div>
                            <div class="stat-label">Asset Valuation</div>
                        </div>
                        <div class="stat-icon-ring">
                            <svg fill="none" stroke="#34d399" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                </div>

                <div class="stat-card card-amber">
                    <div style="display:flex; align-items:flex-start; justify-content:space-between;">
                        <div>
                            <div class="stat-value">${new Intl.NumberFormat().format(stats.dispatched_quantity || 0)}</div>
                            <div class="stat-label">Transit Volume</div>
                        </div>
                        <div class="stat-icon-ring">
                            <svg fill="none" stroke="#fbbf24" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                    </div>
                </div>

                <div class="stat-card card-cyan">
                    <div style="display:flex; align-items:flex-start; justify-content:space-between;">
                        <div>
                            <div class="stat-value">${new Intl.NumberFormat().format(stats.total_entries || 0)}</div>
                            <div class="stat-label">Serial Logs</div>
                        </div>
                        <div class="stat-icon-ring">
                            <svg fill="none" stroke="#22d3ee" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                    </div>
                </div>

                <div class="stat-card card-rose">
                    <div style="display:flex; align-items:flex-start; justify-content:space-between;">
                        <div>
                            <div class="stat-value">${new Intl.NumberFormat().format(stats.pending_dispatches)}</div>
                            <div class="stat-label">Pending Ops</div>
                        </div>
                        <div class="stat-icon-ring">
                            <svg fill="none" stroke="#fb7185" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
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
        tbody.innerHTML = '<tr><td colspan="7" style="padding: 48px; text-align: center; color: #94a3b8; font-weight: 500;">No materials found in current registry.</td></tr>';
        return;
    }

    const startNum = ((page - 1) * limit) + 1;

    tbody.innerHTML = items.map((item, index) => {
        const availableStock = parseFloat(item.available_stock || 0);
        const totalStock = parseFloat(item.total_stock || 0);
        
        let status;
        if (totalStock === 0) status = { c: 'v-pill-critical', l: 'Depleted' };
        else if (availableStock === 0) status = { c: 'v-pill-critical', l: 'Stock-Out' };
        else if (availableStock < (totalStock * 0.2)) status = { c: 'v-pill-warning', l: 'Critical' };
        else status = { c: 'v-pill-active', l: 'Standard' };

        return `
            <tr>
                <td><span class="v-row-num">${startNum + index}</span></td>
                <td>
                    <div style="display:flex; align-items:center; gap:12px;">
                        <div class="v-avatar v-avatar-blue">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:20px;height:20px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <div>
                            <div class="v-name">${item.item_name}</div>
                            <div class="v-code" style="display:flex; align-items:center; gap:6px;">
                                ${item.item_code} <span style="display:inline-block; width:3px; height:3px; border-radius:50%; background:#cbd5e1;"></span> ${item.category || 'General'}
                            </div>
                        </div>
                    </div>
                </td>
                <td>
                    <div style="font-weight:600; color:#334155;">${item.warehouse_name || 'Central Hub'}</div>
                </td>
                <td>
                    <div style="font-weight:700; color:#0f172a; font-variant-numeric:tabular-nums;">
                        <span style="font-size:10px; color:#94a3b8; font-weight:600; text-transform:uppercase;">Tot </span>${new Intl.NumberFormat().format(totalStock)} 
                        <span style="font-size:10px; color:#94a3b8; font-weight:600;">${item.unit}</span>
                    </div>
                    <div style="font-weight:700; color:#3b82f6; font-variant-numeric:tabular-nums; margin-top:2px;">
                        <span style="font-size:10px; color:#94a3b8; font-weight:600; text-transform:uppercase;">Op </span>${new Intl.NumberFormat().format(availableStock)}
                    </div>
                </td>
                <td>
                    <span class="v-pill ${status.c}">${status.l}</span>
                    <div style="font-size:11px; font-weight:600; color:#64748b; margin-top:4px;">
                        In-Transit: <span style="color:#0f172a;">${new Intl.NumberFormat().format(item.dispatched_stock || 0)}</span>
                    </div>
                </td>
                <td>
                    <div style="font-weight:700; color:#059669;">₹${new Intl.NumberFormat('en-IN').format(parseFloat(item.total_value || 0).toFixed(2))}</div>
                </td>
                <td>
                    <div class="v-act">
                        <button onclick="viewStockDetails(${item.boq_item_id})" class="v-act-btn v-view" data-tip="Intelligence Report">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

function renderPagination(pg) {
    try {
        const container = document.getElementById('paginationContainer');
        if (!container) return;

        let html = `
            <div class="v-pag-info">
                Showing <strong>${((pg.page - 1) * pg.limit) + 1}</strong> to <strong>${Math.min(pg.page * pg.limit, pg.total)}</strong> of <strong>${pg.total}</strong> items
            </div>
            <div class="v-pag-nav">
                <button onclick="loadInventory(1)" class="v-pag-btn ${pg.page === 1 ? 'disabled' : ''}">&laquo;</button>
                <button onclick="loadInventory(${pg.page - 1})" class="v-pag-btn ${pg.page === 1 ? 'disabled' : ''}">&lsaquo;</button>
        `;

        let startPage = Math.max(1, pg.page - 2);
        let endPage = Math.min(pg.pages, startPage + 4);
        if (endPage - startPage < 4) {
            startPage = Math.max(1, endPage - 4);
        }

        for (let i = startPage; i <= endPage; i++) {
            if (i === pg.page) {
                html += `<button class="v-pag-btn active">${i}</button>`;
            } else {
                html += `<button onclick="loadInventory(${i})" class="v-pag-btn">${i}</button>`;
            }
        }

        html += `
                <button onclick="loadInventory(${pg.page + 1})" class="v-pag-btn ${pg.page === pg.pages ? 'disabled' : ''}">&rsaquo;</button>
                <button onclick="loadInventory(${pg.pages})" class="v-pag-btn ${pg.page === pg.pages ? 'disabled' : ''}">&raquo;</button>
            </div>
        `;
        
        container.innerHTML = html;
    } catch (e) {
        console.error('Error in renderPagination:', e);
    }
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