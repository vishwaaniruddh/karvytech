<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/BoqItem.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$boqModel = new BoqItem();
$categories = $boqModel->getCategories();

$title = 'BOQ Items Management';
ob_start();
?>

<style>
    /* Premium Stats Cards */
    .stats-card {
        background: white;
        border-radius: 20px;
        border: 1px solid rgba(229, 231, 235, 0.8);
        padding: 24px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.01);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }

    .stats-card:hover {
        transform: translateY(-4px) scale(1.01);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        border-color: rgba(59, 130, 246, 0.3);
    }

    .stats-card::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .stats-card.card-total::after { background: #6366f1; }
    .stats-card.card-active::after { background: #10b981; }
    .stats-card.card-categories::after { background: #f59e0b; }
    .stats-card.card-serial::after { background: #8b5cf6; }

    .stats-card:hover::after {
        opacity: 1;
    }

    .stats-card.active {
        border-color: #3b82f6;
        background: linear-gradient(to bottom right, #ffffff, #f9fafb);
    }

    .stats-card .card-header {
        font-size: 11px;
        color: #9ca3af;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.1em;
        margin-bottom: 6px;
    }

    .stats-card .card-number {
        font-size: 38px;
        font-weight: 800;
        color: #111827;
        margin-bottom: 4px;
        line-height: 1;
        letter-spacing: -0.02em;
    }

    .stats-card .card-subtitle {
        font-size: 13px;
        color: #6b7280;
        font-weight: 500;
    }

    .stats-card .card-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: all 0.4s ease;
        box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.02);
    }

    .stats-card:hover .card-icon {
        transform: rotate(-3deg) scale(1.1);
    }

    .item-name-cell {
        line-height: 1.2;
        font-weight: 600;
        color: #111827;
    }

    .item-code-badge {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        letter-spacing: -0.02em;
    }
</style>

<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">BOQ Items Management</h1>
        <p class="text-[13px] text-gray-500 mt-0.5">Manage Bill of Quantities items, inventory categories, and material tracking.</p>
    </div>
    <div class="flex items-center gap-2">
        <button onclick="exportBOQ()"
            class="inline-flex items-center px-3.5 py-2 text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-xl shadow-sm hover:bg-gray-50 transition-all active:scale-95">
            <svg class="w-3.5 h-3.5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                </path>
            </svg>
            Export BOQ
        </button>
        <button onclick="resetBoqForm(); openModal('boqModal')"
            class="inline-flex items-center px-4 py-2 text-xs font-bold text-white bg-blue-600 rounded-xl shadow-md shadow-blue-100 hover:bg-blue-700 transition-all active:scale-95 focus:ring-4 focus:ring-blue-50 focus:border-blue-300">
            <svg class="w-3.5 h-3.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6">
                </path>
            </svg>
            Add New Item
        </button>
    </div>
</div>

<!-- Statistics Cards Row -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8" id="statsGrid">
    <div class="stats-card card-total active" onclick="filterByStatus('')" id="stat-total">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <div class="card-header">Total Items</div>
                <div class="card-number" id="count-total">...</div>
                <div class="card-subtitle">Master Catalog</div>
            </div>
            <div class="card-icon" style="background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);">
                <svg style="color: #4f46e5;" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"></path>
                </svg>
            </div>
        </div>
    </div>
    <div class="stats-card card-active" onclick="filterByStatus('active')" id="stat-active">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <div class="card-header">Active Items</div>
                <div class="card-number" id="count-active">...</div>
                <div class="card-subtitle">Available for BOQ</div>
            </div>
            <div class="card-icon" style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);">
                <svg style="color: #059669;" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
            </div>
        </div>
    </div>
    <div class="stats-card card-categories" id="stat-categories">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <div class="card-header">Categories</div>
                <div class="card-number" id="count-categories">...</div>
                <div class="card-subtitle">Item Groups</div>
            </div>
            <div class="card-icon" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);">
                <svg style="color: #d97706;" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm3 1h2v2H5V6zm6 0h2v2h-2V6zM5 10h2v2H5v-2zm6 0h2v2h-2v-2z" clip-rule="evenodd"></path>
                </svg>
            </div>
        </div>
    </div>
    <div class="stats-card card-serial" id="stat-serial">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <div class="card-header">Serial Required</div>
                <div class="card-number" id="count-serial">...</div>
                <div class="card-subtitle">Trackable Assets</div>
            </div>
            <div class="card-icon" style="background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%);">
                <svg style="color: #7c3aed;" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm2 2V5h1v1H5zM3 13a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1H4a1 1 0 01-1-1v-3zm2 2v-1h1v1H5zM13 3a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1V3zm2 2V4h1v1H5zM12 12a1 1 0 011-1h5a1 1 0 110 2h-5a1 1 0 01-1-1zM12 16a1 1 0 011-1h5a1 1 0 110 2h-5a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Search and Filters -->
<div class="flex flex-col lg:flex-row lg:items-center gap-3 mb-6">
    <div class="flex-1 relative group">
        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
            <svg class="h-3.5 w-3.5 text-gray-400 group-focus-within:text-blue-500 transition-colors" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
        <input type="text" id="searchInput" placeholder="Search item name, code, or description..."
            class="block w-full pl-10 pr-4 py-2 bg-white border border-gray-100 rounded-xl text-sm focus:ring-2 focus:ring-blue-50 focus:border-blue-200 transition-all outline-none shadow-sm"
            onkeyup="debounceFilter()">
    </div>
    <div class="flex items-center gap-2">
        <select id="categoryFilter"
            class="appearance-none px-4 py-2 bg-white border border-gray-100 rounded-xl text-sm focus:ring-2 focus:ring-blue-50 focus:border-blue-200 shadow-sm outline-none cursor-pointer min-w-[130px]"
            onchange="applyFilters()">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
            <?php endforeach; ?>
        </select>
        <select id="statusFilter"
            class="appearance-none px-4 py-2 bg-white border border-gray-100 rounded-xl text-sm focus:ring-2 focus:ring-blue-50 focus:border-blue-200 shadow-sm outline-none cursor-pointer min-w-[130px]"
            onchange="applyFilters()">
            <option value="">All Status</option>
            <option value="active">Active Only</option>
            <option value="inactive">Inactive Only</option>
        </select>
    </div>
</div>

<!-- BOQ Items Table -->
<div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden min-h-[400px] relative">
    <div id="tableLoading" class="absolute inset-0 bg-white/80 z-10 flex items-center justify-center hidden">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/50 border-b border-gray-100">
                    <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center w-16">#</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Item Description</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Item Code</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Category</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Unit</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Status</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-right">Actions</th>
                </tr>
            </thead>
            <tbody id="boqTableBody" class="bg-white divide-y divide-gray-50">
                <!-- Data will be injected here via API -->
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div id="paginationContainer"
        class="px-6 py-4 bg-white border-t border-gray-100 flex items-center justify-between hidden">
        <div id="paginationInfo" class="text-sm text-gray-700"></div>
        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" id="paginationNav">
            <!-- Pagination buttons injected here -->
        </nav>
    </div>
</div>

<!-- Item Modal (Add/Edit) -->
<div id="boqModal" class="modal">
    <div class="modal-content max-w-2xl rounded-3xl p-0 overflow-hidden">
        <div class="bg-gray-50/80 px-8 py-6 border-b flex justify-between items-center">
            <div>
                <h3 id="boqModalTitle" class="text-xl font-bold text-gray-900">Add BOQ Item</h3>
                <p class="text-xs text-gray-500 mt-1">Configure item specifications and tracking requirements.</p>
            </div>
            <button onclick="closeModal('boqModal')" class="text-gray-400 hover:text-gray-600 p-2 bg-white rounded-xl shadow-sm border border-gray-100">&times;</button>
        </div>
        <form id="boqForm" class="p-8 space-y-6">
            <input type="hidden" name="id" id="edit_item_id">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="space-y-1.5">
                    <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">Item Code *</label>
                    <input type="text" name="item_code" id="item_code" placeholder="e.g. CAM-JIO-001" required
                        class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-2xl text-sm outline-none focus:ring-2 focus:ring-blue-100 font-mono">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">Item Name *</label>
                    <input type="text" name="item_name" id="item_name" placeholder="Item display name" required
                        class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-2xl text-sm outline-none focus:ring-2 focus:ring-blue-100 font-semibold">
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">Description</label>
                <textarea name="description" id="description" rows="3" placeholder="Technical specifications and details..."
                    class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-2xl text-sm outline-none focus:ring-2 focus:ring-blue-100"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="space-y-1.5">
                    <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">Unit *</label>
                    <select name="unit" id="unit" required
                        class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-2xl text-sm outline-none appearance-none cursor-pointer">
                        <option value="Nos">Nos</option>
                        <option value="Meter">Meter</option>
                        <option value="Pcs">Pcs</option>
                        <option value="Set">Set</option>
                        <option value="Box">Box</option>
                        <option value="Roll">Roll</option>
                        <option value="Kg">Kg</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">Category</label>
                    <input type="text" name="category" id="category" list="categoryList" placeholder="e.g. Network"
                        class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-2xl text-sm outline-none">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">Icon Class</label>
                    <input type="text" name="icon_class" id="icon_class" placeholder="fas fa-cube"
                        class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-2xl text-sm outline-none">
                </div>
            </div>

            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl">
                <div>
                    <label class="text-sm font-bold text-gray-900">Serial Number Tracking</label>
                    <p class="text-[10px] text-gray-500 mt-0.5">Toggle if this item requires individual serial number reporting.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="need_serial_number" id="need_serial_number" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>

            <div class="pt-6 border-t flex justify-end gap-3">
                <button type="button" onclick="closeModal('boqModal')" class="px-6 py-3 text-sm font-bold text-gray-400 hover:text-gray-600 transition-colors uppercase tracking-widest">Cancel</button>
                <button type="submit" id="submitBoqBtn"
                    class="px-12 py-3 bg-blue-600 text-white text-sm font-bold rounded-2xl shadow-xl shadow-blue-100 hover:bg-blue-700 transition-all active:scale-95 uppercase tracking-widest">Save Item</button>
            </div>
        </form>
    </div>
</div>

<!-- View Item Modal -->
<div id="viewBoqModal" class="modal">
    <div class="modal-content max-w-xl rounded-3xl p-8 bg-white shadow-2xl">
        <div class="flex items-start justify-between mb-8">
            <div class="flex items-center gap-4">
                <div id="view_icon_container"
                    class="w-16 h-16 rounded-2xl bg-blue-50 flex items-center justify-center text-2xl text-blue-600 border border-blue-100/50">
                    <i id="view_icon" class="fas fa-cube"></i>
                </div>
                <div>
                    <h3 id="view_item_name" class="text-xl font-bold text-gray-900 leading-tight">---</h3>
                    <div class="flex items-center gap-2 mt-1">
                        <span id="view_item_code" class="text-[10px] font-bold text-blue-600 uppercase tracking-widest font-mono">---</span>
                        <span class="text-gray-300">•</span>
                        <span id="view_category" class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">---</span>
                    </div>
                </div>
            </div>
            <button onclick="closeModal('viewBoqModal')" class="text-gray-400 hover:text-gray-600 transition-colors p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="space-y-6 mb-8">
            <div>
                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-2">Description</p>
                <p id="view_description" class="text-sm text-gray-600 leading-relaxed bg-gray-50/50 p-4 rounded-2xl border border-gray-50">---</p>
            </div>
            
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Unit of Measurement</p>
                    <p id="view_unit" class="text-sm font-bold text-gray-900 bg-gray-50 px-3 py-2 rounded-xl inline-block">---</p>
                </div>
                <div>
                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Serial Requirement</p>
                    <div id="view_serial_badge" class="inline-flex items-center px-3 py-1.5 rounded-xl text-[10px] font-bold uppercase tracking-widest">
                        ---
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-6 border-t border-gray-100 flex justify-end gap-2">
            <button onclick="closeModal('viewBoqModal')" class="px-6 py-2 text-sm font-bold text-gray-400">Close</button>
            <button onclick="editBoqFromView()" class="px-8 py-2 bg-gray-900 text-white text-sm font-bold rounded-2xl transition-all active:scale-95">Edit Item</button>
        </div>
    </div>
</div>

<script>
    let currentData = null;
    let searchTimer;

    function debounceFilter() {
        if (searchTimer) clearTimeout(searchTimer);
        searchTimer = setTimeout(() => applyFilters(), 500);
    }

    function applyFilters(page = 1) {
        const search = document.getElementById('searchInput').value;
        const category = document.getElementById('categoryFilter').value;
        const status = document.getElementById('statusFilter').value;

        const params = new URLSearchParams(window.location.search);
        params.set('page', page);
        if (search) params.set('search', search); else params.delete('search');
        if (category) params.set('category', category); else params.delete('category');
        if (status) params.set('status', status); else params.delete('status');

        window.history.pushState({}, '', '?' + params.toString());
        fetchBoqItems();
    }

    function filterByStatus(status) {
        document.getElementById('statusFilter').value = status;
        applyFilters(1);
    }

    async function fetchBoqItems() {
        const loading = document.getElementById('tableLoading');
        loading.classList.remove('hidden');

        try {
            const params = new URLSearchParams(window.location.search);
            const response = await fetch(`../../api/boq_items.php?action=list&${params.toString()}`);
            const result = await response.json();

            if (result.success) {
                currentData = result.data;
                renderStats(result.data.stats);
                renderTable(result.data.items);
                renderPagination(result.data.pagination);
            }
        } catch (error) {
            console.error('Fetch error:', error);
            showToast('Failed to load BOQ data.', 'error');
        } finally {
            loading.classList.add('hidden');
        }
    }

    function renderStats(stats) {
        document.getElementById('count-total').textContent = (stats.total || 0).toLocaleString();
        document.getElementById('count-active').textContent = (stats.active || 0).toLocaleString();
        document.getElementById('count-categories').textContent = (stats.categories || 0).toLocaleString();
        document.getElementById('count-serial').textContent = (stats.serial_required || 0).toLocaleString();

        const params = new URLSearchParams(window.location.search);
        const status = params.get('status');

        document.querySelectorAll('.stats-card').forEach(c => c.classList.remove('active'));
        if (status === 'active') document.getElementById('stat-active').classList.add('active');
        else if (status === 'inactive') document.getElementById('stat-total').classList.add('active');
        else if (!status || status === '') document.getElementById('stat-total').classList.add('active');
    }

    function renderTable(items) {
        const tbody = document.getElementById('boqTableBody');
        tbody.innerHTML = '';

        if (items.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                        <div class="flex flex-col items-center">
                            <svg class="w-12 h-12 text-gray-100 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            <p class="text-xs font-bold text-gray-300 uppercase tracking-widest">No items found</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        const params = new URLSearchParams(window.location.search);
        const page = parseInt(params.get('page')) || 1;
        const limit = 10;

        items.forEach((item, index) => {
            const row = `
                <tr class="hover:bg-gray-50/50 transition-colors group">
                    <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-gray-400 text-center">
                        ${(page - 1) * limit + index + 1}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 mr-3 shadow-sm group-hover:scale-110 transition-transform">
                                <i class="${item.icon_class || 'fas fa-cube'} text-xs"></i>
                            </div>
                            <div>
                                <div class="text-sm item-name-cell text-gray-900 group-hover:text-blue-600 transition-colors cursor-pointer" onclick="viewBoqItem(${item.id})">
                                    ${item.item_name}
                                </div>
                                <div class="text-[10px] text-gray-400 font-medium truncate max-w-[200px]">${item.description || 'No description'}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-xs font-bold text-gray-600 item-code-badge bg-gray-50 px-2 py-1 rounded-lg border border-gray-100 inline-block">${item.item_code}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-[11px] font-bold text-gray-500 bg-gray-100/50 px-2.5 py-1 rounded-full uppercase tracking-tighter">
                            ${item.category || 'Uncategorized'}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-700">${item.unit}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="w-1.5 h-1.5 rounded-full mr-2 ${item.status === 'active' ? 'bg-emerald-500 animate-pulse' : 'bg-gray-300'}"></div>
                            <span class="text-[11px] font-bold ${item.status === 'active' ? 'text-emerald-600' : 'text-gray-400'}">
                                ${item.status.toUpperCase()}
                            </span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <div class="flex items-center justify-end gap-1">
                            <button onclick="viewBoqItem(${item.id})" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all" title="View Details">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </button>
                            <button onclick="editBoqItem(${item.id})" class="p-1.5 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-all" title="Edit Item">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </button>
                            <button onclick="toggleBoqStatus(${item.id})" class="p-1.5 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-all" title="Toggle Status">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 12.728l-3.536-3.536M12 3v4m0 10v4M3 12h4m10 0h4"></path></svg>
                            </button>
                            <button onclick="deleteBoqItem(${item.id})" class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all" title="Delete">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            tbody.innerHTML += row;
        });
    }

    function renderPagination(pagination) {
        const container = document.getElementById('paginationContainer');
        const info = document.getElementById('paginationInfo');
        const nav = document.getElementById('paginationNav');

        if (pagination.total_pages <= 1) {
            container.classList.add('hidden');
            return;
        }

        container.classList.remove('hidden');
        const start = ((pagination.current_page - 1) * pagination.limit) + 1;
        const end = Math.min(pagination.current_page * pagination.limit, pagination.total_records);
        info.innerHTML = `Showing <span class="font-medium">${start}</span> to <span class="font-medium">${end}</span> of <span class="font-medium">${pagination.total_records}</span> results`;

        nav.innerHTML = '';
        const current = pagination.current_page;
        const total = pagination.total_pages;

        const addBtn = (label, page, isActive = false, isDisabled = false, icon = null) => {
            const btn = document.createElement(isDisabled ? 'span' : 'a');
            if (!isDisabled) {
                btn.href = '#';
                btn.onclick = (e) => { e.preventDefault(); applyFilters(page); };
            }
            btn.className = `relative inline-flex items-center px-4 py-2 border text-sm font-medium transition-colors ${isActive ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : isDisabled ? 'bg-gray-50 text-gray-300 border-gray-200 cursor-not-allowed' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'}`;
            if (icon) {
                btn.innerHTML = `<span class="sr-only">${label}</span>${icon}`;
                btn.classList.add('px-2');
            } else {
                btn.textContent = label;
            }
            nav.appendChild(btn);
        };

        const prevIcon = '<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>';
        const nextIcon = '<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>';

        addBtn('Previous', current - 1, false, current === 1, prevIcon);
        for (let i = 1; i <= total; i++) {
            if (i === 1 || i === total || (i >= current - 1 && i <= current + 1)) {
                addBtn(i.toString(), i, i === current);
            } else if (i === current - 2 || i === current + 2) {
                const dot = document.createElement('span');
                dot.className = 'relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700';
                dot.textContent = '...';
                nav.appendChild(dot);
            }
        }
        addBtn('Next', current + 1, false, current === total, nextIcon);
    }

    function resetBoqForm() {
        document.getElementById('boqForm').reset();
        document.getElementById('edit_item_id').value = '';
        document.getElementById('boqModalTitle').textContent = 'Add BOQ Item';
        document.getElementById('submitBoqBtn').textContent = 'Save Item';
        document.getElementById('need_serial_number').checked = false;
    }

    document.getElementById('boqForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        const id = document.getElementById('edit_item_id').value;
        const action = id ? `update&id=${id}` : 'create';
        
        const response = await fetch(`../../api/boq_items.php?action=${action}`, { method: 'POST', body: formData });
        const result = await response.json();
        
        if (result.success) {
            closeModal('boqModal');
            showToast(result.message, 'success');
            fetchBoqItems();
        } else {
            showToast(result.message, 'error');
        }
    });

    async function viewBoqItem(id) {
        const res = await fetch(`../../api/boq_items.php?action=view&id=${id}`);
        const data = await res.json();
        if (data.success) {
            const item = data.item;
            document.getElementById('view_item_name').textContent = item.item_name;
            document.getElementById('view_item_code').textContent = item.item_code;
            document.getElementById('view_category').textContent = item.category || 'Uncategorized';
            document.getElementById('view_description').textContent = item.description || 'No detailed description available.';
            document.getElementById('view_unit').textContent = item.unit;
            document.getElementById('view_icon').className = item.icon_class || 'fas fa-cube';
            
            const serialBadge = document.getElementById('view_serial_badge');
            if (item.need_serial_number == 1) {
                serialBadge.textContent = 'Required';
                serialBadge.className = 'inline-flex items-center px-3 py-1.5 rounded-xl text-[10px] font-bold uppercase tracking-widest bg-purple-50 text-purple-600 border border-purple-100';
            } else {
                serialBadge.textContent = 'Not Required';
                serialBadge.className = 'inline-flex items-center px-3 py-1.5 rounded-xl text-[10px] font-bold uppercase tracking-widest bg-gray-50 text-gray-400 border border-gray-100';
            }
            
            document.querySelector('#viewBoqModal button[onclick="editBoqFromView()"]').onclick = () => {
                closeModal('viewBoqModal');
                editBoqItem(id);
            };
            
            openModal('viewBoqModal');
        }
    }

    async function editBoqItem(id) {
        const res = await fetch(`../../api/boq_items.php?action=view&id=${id}`);
        const data = await res.json();
        if (data.success) {
            const item = data.item;
            document.getElementById('edit_item_id').value = item.id;
            document.getElementById('boqModalTitle').textContent = 'Edit BOQ Item';
            document.getElementById('submitBoqBtn').textContent = 'Update Item';
            
            const form = document.getElementById('boqForm');
            form.querySelector('[name="item_name"]').value = item.item_name || '';
            form.querySelector('[name="item_code"]').value = item.item_code || '';
            form.querySelector('[name="description"]').value = item.description || '';
            form.querySelector('[name="unit"]').value = item.unit || 'Nos';
            form.querySelector('[name="category"]').value = item.category || '';
            form.querySelector('[name="icon_class"]').value = item.icon_class || '';
            form.querySelector('[name="need_serial_number"]').checked = item.need_serial_number == 1;

            openModal('boqModal');
        }
    }

    async function toggleBoqStatus(id) {
        const confirmed = await showConfirm(
            'Change Status',
            'Are you sure you want to toggle the status of this item?',
            { confirmType: 'primary', confirmText: 'Yes, Toggle' }
        );
        if (!confirmed) return;

        const res = await fetch(`../../api/boq_items.php?action=toggle-status&id=${id}`, { method: 'POST' });
        const data = await res.json();
        if (data.success) {
            showToast(data.message, 'success');
            fetchBoqItems();
        } else {
            showToast(data.message, 'error');
        }
    }

    async function deleteBoqItem(id) {
        const confirmed = await showConfirm(
            'Delete BOQ Item',
            'Are you sure you want to permanently delete this item? This action cannot be undone.',
            { confirmType: 'danger', confirmText: 'Yes, Delete' }
        );
        if (!confirmed) return;

        const res = await fetch(`../../api/boq_items.php?action=delete&id=${id}`, { method: 'POST' });
        const data = await res.json();
        if (data.success) {
            showToast(data.message, 'success');
            fetchBoqItems();
        } else {
            showToast(data.message, 'error');
        }
    }

    function exportBOQ() {
        window.open('export.php', '_blank');
    }

    document.addEventListener('DOMContentLoaded', fetchBoqItems);
</script>

<datalist id="categoryList">
    <?php foreach ($categories as $cat): ?>
        <option value="<?php echo htmlspecialchars($cat); ?>">
    <?php endforeach; ?>
</datalist>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>