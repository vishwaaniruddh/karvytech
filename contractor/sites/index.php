<?php 
require_once __DIR__ . '/../../config/auth.php';
// Require vendor authentication
Auth::requireVendor();
$title = 'Site Operations';
ob_start();
?>

<!-- Minimalist Toolbelt -->
<div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div class="flex items-center space-x-4">
        <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Project Hub</h1>
        <div class="flex p-1 bg-gray-100 rounded-xl border border-gray-200/50">
            <button onclick="setStatus('all')" class="status-btn active px-4 py-2 text-[10px] font-bold uppercase tracking-wider rounded-lg transition-all" data-status="all">All</button>
            <button onclick="setStatus('active')" class="status-btn px-4 py-2 text-[10px] font-bold uppercase tracking-wider rounded-lg transition-all" data-status="active">Active</button>
            <button onclick="setStatus('completed')" class="status-btn px-4 py-2 text-[10px] font-bold uppercase tracking-wider rounded-lg transition-all" data-status="completed">Done</button>
        </div>
    </div>
    
    <div class="flex items-center space-x-3">
        <div class="relative group">
            <input type="text" id="globalSearch" oninput="debounceSearch()" placeholder="Search sites..." class="pl-10 pr-4 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all w-64">
            <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </div>
        <button onclick="exportData()" class="flex items-center space-x-2 px-4 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-50 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <span>Export</span>
        </button>
    </div>
</div>

<!-- Intelligent Data Grid -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden relative min-h-[400px]">
    <div id="loadingOverlay" class="absolute inset-0 bg-white/60 backdrop-blur-[2px] z-20 flex items-center justify-center opacity-0 pointer-events-none transition-opacity duration-300">
        <div class="flex flex-col items-center">
            <div class="w-10 h-10 border-4 border-blue-500/20 border-t-blue-600 rounded-full animate-spin"></div>
            <p class="text-[10px] font-bold text-blue-600 uppercase tracking-widest mt-4">Syncing Operations</p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50/50 border-b border-gray-100">
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-widest w-12 text-center">#</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-500 uppercase tracking-widest">Site Identity</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-500 uppercase tracking-widest">Client & Geography</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-500 uppercase tracking-widest">Operation Status</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-500 uppercase tracking-widest text-center">Insights</th>
                    <th class="px-6 py-4 text-right text-[11px] font-bold text-gray-500 uppercase tracking-widest">Action</th>
                </tr>
            </thead>
            <tbody id="tableBody" class="divide-y divide-gray-50">
                <!-- Data cells injected here -->
            </tbody>
        </table>
    </div>

    <!-- Pagination Context -->
    <div class="px-8 py-4 bg-gray-50/50 border-t border-gray-100 flex items-center justify-between">
        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest" id="pageInfo">Showing 0 of 0 Sites</div>
        <div class="flex items-center space-x-2">
            <button onclick="prevPage()" id="prevBtn" class="p-2 bg-white border border-gray-200 rounded-lg text-gray-400 hover:text-blue-600 disabled:opacity-50 disabled:pointer-events-none transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </button>
            <div id="pageNumbers" class="flex items-center space-x-1"></div>
            <button onclick="nextPage()" id="nextBtn" class="p-2 bg-white border border-gray-200 rounded-lg text-gray-400 hover:text-blue-600 disabled:opacity-50 disabled:pointer-events-none transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </button>
        </div>
    </div>
</div>

<!-- Multi-Insight Modal Overlay -->
<div id="intelModal" class="fixed inset-0 z-[100] flex items-center justify-center opacity-0 pointer-events-none transition-opacity duration-300">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeIntelModal()"></div>
    <div class="relative w-full max-w-2xl bg-white rounded-3xl shadow-2xl overflow-hidden transform scale-95 transition-transform duration-300" id="modalContainer">
        <div class="p-6 border-b border-gray-50 flex items-center justify-between bg-gray-50/30">
            <div>
                <h3 class="text-xl font-bold text-gray-900" id="intelTitle">Operational Insight</h3>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1" id="intelSubtitle">Details Manifest</p>
            </div>
            <button onclick="closeIntelModal()" class="w-8 h-8 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-400 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="p-8 max-h-[70vh] overflow-y-auto" id="intelContent">
            <!-- Modal data injected here -->
        </div>
    </div>
</div>

<script>
let currentPage = 1;
let filters = { search: '', status: 'all', limit: 10 };
let searchDebounce = null;

document.addEventListener('DOMContentLoaded', () => {
    fetchData();
});

function debounceSearch() {
    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(() => {
        filters.search = document.getElementById('globalSearch').value;
        currentPage = 1;
        fetchData();
    }, 400);
}

function setStatus(status) {
    filters.status = status;
    document.querySelectorAll('.status-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.status === status);
    });
    currentPage = 1;
    fetchData();
}

async function fetchData() {
    toggleLoading(true);
    try {
        const query = new URLSearchParams({ page: currentPage, ...filters }).toString();
        const response = await fetch(`api/get-sites.php?${query}`);
        const result = await response.json();
        
        if (result.success) {
            renderTable(result.data, result.pagination);
            renderPagination(result.pagination);
        }
    } catch (e) {
        console.error("Fetch Error:", e);
    } finally {
        toggleLoading(false);
    }
}

function toggleLoading(show) {
    const loader = document.getElementById('loadingOverlay');
    if (show) {
        loader.classList.remove('pointer-events-none', 'opacity-0');
        loader.classList.add('opacity-100');
    } else {
        loader.classList.add('opacity-0', 'pointer-events-none');
        loader.classList.remove('opacity-100');
    }
}

function renderTable(data, pagination) {
    const tbody = document.getElementById('tableBody');
    if (data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="py-20 text-center text-gray-400 text-xs font-bold uppercase tracking-widest">No site records found</td></tr>`;
        return;
    }

    tbody.innerHTML = data.map((site, index) => {
        const serial = (pagination.page - 1) * pagination.limit + index + 1;
        return `
            <tr class="hover:bg-gray-50/50 transition-colors group">
                <td class="px-6 py-5 text-center text-xs font-bold text-gray-400">${serial}</td>
                <td class="px-6 py-5">
                    <div class="flex flex-col">
                        <span class="text-sm font-semibold text-gray-800">${site.site_code}</span>
                        <span class="text-[10px] font-medium text-gray-400 uppercase tracking-tight truncate max-w-[150px] mt-0.5">${site.location}</span>
                    </div>
                </td>
                <td class="px-6 py-5">
                    <div class="flex flex-col">
                        <span class="text-xs font-semibold text-gray-700">${site.customer_name || 'N/A'}</span>
                        <span class="text-[10px] text-gray-400 font-medium mt-0.5">${site.city_name || site.state_name}</span>
                    </div>
                </td>
                <td class="px-6 py-5">
                    <div class="flex items-center space-x-4">
                        <div class="flex flex-col">
                            <span class="text-[9px] font-bold text-gray-400 uppercase">Survey</span>
                            <span class="text-xs font-bold ${getStatusColor(site.survey_status)}">${site.survey_status || 'Pending'}</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[9px] font-bold text-gray-400 uppercase">Installation</span>
                            <span class="text-xs font-bold ${getStatusColor(site.installation_status)}">${site.installation_status || 'Pending'}</span>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-5">
                    <div class="flex items-center justify-center space-x-1.5 context-icons">
                        <button onclick="openIntel('boq', ${site.site_id}, '${site.site_code}')" title="View BOQ" class="p-1.5 hover:bg-blue-50 text-gray-400 hover:text-blue-600 rounded-lg transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        </button>
                        <button onclick="openIntel('survey', ${site.site_id}, '${site.site_code}')" title="Survey Details" class="p-1.5 hover:bg-amber-50 text-gray-400 hover:text-amber-600 rounded-lg transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </button>
                        <button onclick="openIntel('installation', ${site.site_id}, '${site.site_code}')" title="Install Logic" class="p-1.5 hover:bg-purple-50 text-gray-400 hover:text-purple-600 rounded-lg transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        </button>
                        <button onclick="openIntel('material', ${site.site_id}, '${site.site_code}')" title="Material Manifest" class="p-1.5 hover:bg-emerald-50 text-gray-400 hover:text-emerald-600 rounded-lg transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        </button>
                    </div>
                </td>
                <td class="px-6 py-5 text-right">
                    ${(site.survey_status === 'approved' || site.survey_status === 'completed') 
                        ? `<a href="${site.survey_source === 'dynamic' ? `../../shared/view-survey2.php?id=${site.survey_id}` : `../../shared/view-survey.php?id=${site.survey_id}`}" target="_blank" class="inline-flex items-center px-5 py-2.5 bg-gray-50 border border-gray-200 hover:border-blue-600 hover:text-blue-700 text-gray-600 text-[11px] font-bold uppercase tracking-widest rounded-xl transition-all shadow-sm">View Result</a>`
                        : `<a href="../site-survey2.php?delegation_id=${site.delegation_id}" class="inline-flex items-center px-5 py-2.5 bg-gray-800 hover:bg-black text-white text-[11px] font-bold uppercase tracking-widest rounded-xl transition-all shadow-sm">Execute</a>`
                    }
                </td>
            </tr>
        `;
    }).join('');
}

function getStatusColor(status) {
    if (!status || status === 'pending') return 'text-amber-500';
    if (status === 'completed' || status === 'approved' || status === 'delivered') return 'text-emerald-500';
    if (status === 'rejected') return 'text-rose-500';
    return 'text-blue-500';
}

function renderPagination(p) {
    document.getElementById('pageInfo').innerText = `Page ${p.page} of ${p.pages} (${p.total} total items)`;
    document.getElementById('prevBtn').disabled = p.page === 1;
    document.getElementById('nextBtn').disabled = p.page === p.pages;

    const nums = document.getElementById('pageNumbers');
    nums.innerHTML = '';
    
    // Generate simple page numbers
    const start = Math.max(1, p.page - 2);
    const end = Math.min(p.pages, start + 4);
    
    for (let i = start; i <= end; i++) {
        nums.innerHTML += `
            <button onclick="goToPage(${i})" class="w-8 h-8 rounded-lg text-xs font-bold transition-all ${i === p.page ? 'bg-blue-600 text-white shadow-lg' : 'bg-white text-gray-400 hover:text-blue-600'}">${i}</button>
        `;
    }
}

function goToPage(p) {
    currentPage = p;
    fetchData();
}

function nextPage() { currentPage++; fetchData(); }
function prevPage() { currentPage--; fetchData(); }

// Modal Logic
async function openIntel(type, siteId, siteCode) {
    const modal = document.getElementById('intelModal');
    const container = document.getElementById('modalContainer');
    const content = document.getElementById('intelContent');
    const title = document.getElementById('intelTitle');
    const subtitle = document.getElementById('intelSubtitle');

    const titles = {
        boq: 'Bill of Quantities',
        survey: 'Survey Protocol',
        installation: 'Installation Brief',
        material: 'Material Logistics'
    };

    title.innerText = titles[type];
    subtitle.innerText = `Site Context: ${siteCode}`;
    
    content.innerHTML = `<div class="py-12 flex justify-center"><div class="w-8 h-8 border-4 border-blue-100 border-t-blue-600 rounded-full animate-spin"></div></div>`;
    
    modal.classList.remove('pointer-events-none', 'opacity-0');
    modal.classList.add('opacity-100');
    container.classList.remove('scale-95');
    container.classList.add('scale-100');

    try {
        const response = await fetch(`api/get-site-intel.php?site_id=${siteId}&type=${type}`);
        const result = await response.json();
        if (result.success) renderIntel(type, result.data[type]);
    } catch (e) {
        content.innerHTML = `<p class="text-rose-500 text-center font-bold">Extraction Failed</p>`;
    }
}

function renderIntel(type, data) {
    const content = document.getElementById('intelContent');
    if (!data || (Array.isArray(data) && data.length === 0)) {
        content.innerHTML = `<div class="py-8 text-center text-gray-400 font-bold uppercase text-[10px]">No data available for this phase</div>`;
        return;
    }

    let html = '';
    if (type === 'boq') {
        html = `
            <div class="space-y-4">
                <div class="flex justify-between items-center mb-6">
                    <span class="text-xs font-bold text-gray-500 uppercase">Master Data: ${data.customer_name}</span>
                    <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-[10px] font-bold">Rev: ${data.id}</span>
                </div>
                <table class="w-full text-left">
                    <thead><tr class="text-[10px] font-bold text-gray-400 uppercase tracking-widest border-b"><th class="py-2">Item Descriptions</th><th class="py-2 text-right">Qty</th></tr></thead>
                    <tbody class="divide-y divide-gray-50">
                        ${data.items.map(it => `<tr class="text-xs"><td class="py-3 font-medium">${it.item_name}</td><td class="py-3 text-right font-bold">${it.quantity} ${it.unit}</td></tr>`).join('')}
                    </tbody>
                </table>
            </div>`;
    } else if (type === 'survey') {
        html = `
            <div class="grid grid-cols-2 gap-6">
                <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                    <div class="text-[10px] font-bold text-gray-400 uppercase mb-1">Delegation Date</div>
                    <div class="text-sm font-bold text-gray-900">${new Date(data.survey_delegation_date).toLocaleDateString()}</div>
                </div>
                <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                    <div class="text-[10px] font-bold text-gray-400 uppercase mb-1">Current Status</div>
                    <div class="text-sm font-bold ${getStatusColor(data.status)} uppercase">${data.status || 'Pending'}</div>
                </div>
                <div class="col-span-2 p-4 bg-blue-50 rounded-2xl border border-blue-100">
                    <div class="text-[10px] font-bold text-blue-500 uppercase mb-2">Instructions</div>
                    <div class="text-xs text-blue-700 leading-relaxed font-medium">${data.survey_notes || 'No specific technical instructions provided.'}</div>
                </div>
            </div>`;
    } else if (type === 'installation') {
        html = `
            <div class="space-y-6">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-purple-50 rounded-2xl flex items-center justify-center text-purple-600 border border-purple-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-gray-900">Assigned by ${data.assigned_by}</div>
                        <div class="text-xs text-gray-400 font-medium">${new Date(data.delegation_date).toLocaleDateString()}</div>
                    </div>
                </div>
                <div class="p-5 bg-white border border-gray-100 rounded-3xl shadow-sm">
                    <div class="text-[10px] font-bold text-gray-400 uppercase mb-3">Install Directives</div>
                    <div class="text-sm text-gray-700 leading-relaxed">${data.notes || 'Proceed with standard installation protocol.'}</div>
                </div>
            </div>`;
    } else if (type === 'material') {
        html = `
            <div class="space-y-4">
                ${data.map(m => `
                    <div class="p-5 bg-white border border-gray-100 rounded-3xl shadow-sm hover:border-emerald-500 transition-all">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <div class="text-sm font-bold text-gray-900">REQ-${m.id.toString().padStart(5, '0')}</div>
                                <div class="text-[10px] text-gray-400 font-bold uppercase">${new Date(m.created_date).toDateString()}</div>
                            </div>
                            <span class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full text-[10px] font-bold uppercase">${m.status}</span>
                        </div>
                        ${m.dispatch_number ? `
                            <div class="grid grid-cols-2 gap-4 mt-4 pt-4 border-t border-gray-50">
                                <div><div class="text-[9px] font-bold text-gray-400 uppercase mb-1">Docket #</div><div class="text-xs font-bold text-gray-700">${m.dispatch_number}</div></div>
                                <div><div class="text-[9px] font-bold text-gray-400 uppercase mb-1">Status</div><div class="text-xs font-bold text-blue-600 uppercase">${m.dispatch_status}</div></div>
                            </div>
                        ` : '<div class="text-[10px] text-amber-500 font-bold uppercase mt-4">Awaiting Dispatch</div>'}
                    </div>
                `).join('')}
            </div>`;
    }
    content.innerHTML = html;
}

function closeIntelModal() {
    const modal = document.getElementById('intelModal');
    const container = document.getElementById('modalContainer');
    modal.classList.add('pointer-events-none', 'opacity-0');
    modal.classList.remove('opacity-100');
    container.classList.add('scale-95');
    container.classList.remove('scale-100');
}

function exportData() {
    const query = new URLSearchParams(filters).toString();
    window.location.href = `api/export-sites.php?${query}`;
}
</script>

<style>
.status-btn.active {
    background-color: white;
    color: #2563eb;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}
.status-btn:not(.active) {
    color: #94a3b8;
}
.context-icons button:hover {
    transform: translateY(-2px);
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/vendor_layout.php';
?>
