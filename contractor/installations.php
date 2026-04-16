<?php 
require_once __DIR__ . '/../config/auth.php';
// Require vendor authentication
Auth::requireVendor();
$title = 'Installation Hub';
ob_start();
?>

<!-- Enhanced Header Section -->
<div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
    <div class="flex items-center space-x-5">
        <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-purple-700 rounded-2xl shadow-lg shadow-purple-100 flex items-center justify-center text-white">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-800 tracking-tight">Deployment Intelligence</h2>
            <p class="text-gray-500 text-xs mt-1 font-medium">Real-time deployment tracking & assignment management</p>
        </div>
    </div>
    
    <div class="flex items-center space-x-3">
        <div class="bg-gray-100 p-1 rounded-xl flex border border-gray-200/50">
            <button onclick="setStatus('all')" class="status-btn active px-4 py-2 text-[10px] font-bold uppercase tracking-wider rounded-lg transition-all" data-status="all">All</button>
            <button onclick="setStatus('in_progress')" class="status-btn px-4 py-2 text-[10px] font-bold uppercase tracking-wider rounded-lg transition-all" data-status="in_progress">Active</button>
            <button onclick="setStatus('completed')" class="status-btn px-4 py-2 text-[10px] font-bold uppercase tracking-wider rounded-lg transition-all" data-status="completed">Done</button>
        </div>
    </div>
</div>

<!-- Search & Statistics -->
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
    <div class="lg:col-span-3">
        <div class="relative group">
            <input type="text" id="globalSearch" oninput="debounceSearch()" placeholder="Search project ID, city or location..." class="w-full pl-12 pr-4 py-4 bg-white border border-gray-100 rounded-2xl text-sm font-medium shadow-sm focus:ring-4 focus:ring-purple-500/5 focus:border-purple-500 outline-none transition-all placeholder:text-gray-400">
            <svg class="w-5 h-5 text-gray-400 absolute left-4 top-4.5 group-focus-within:text-purple-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 p-4 flex items-center justify-between shadow-sm">
        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Global Assignments</span>
        <span id="recordCount" class="text-xl font-bold text-gray-800">0</span>
    </div>
</div>

<!-- Intelligent Installation Grid -->
<div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden relative min-h-[500px]">
    <div id="loadingOverlay" class="absolute inset-0 bg-white/60 backdrop-blur-[2px] z-20 flex items-center justify-center opacity-0 pointer-events-none transition-opacity duration-300">
        <div class="flex flex-col items-center">
            <div class="w-12 h-12 border-4 border-purple-500/20 border-t-purple-600 rounded-full animate-spin"></div>
            <p class="text-[10px] font-black text-purple-600 uppercase tracking-widest mt-4">Syncing Deployments</p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50/50 border-b border-gray-100">
                    <th class="px-8 py-5 text-[11px] font-bold text-gray-400 uppercase tracking-widest w-16 text-center">#</th>
                    <th class="px-8 py-5 text-[11px] font-bold text-gray-500 uppercase tracking-widest">Project Identity</th>
                    <th class="px-8 py-5 text-[11px] font-bold text-gray-500 uppercase tracking-widest">Phase Metrics</th>
                    <th class="px-8 py-5 text-[11px] font-bold text-gray-500 uppercase tracking-widest">Timeline</th>
                    <th class="px-8 py-5 text-[11px] font-bold text-gray-500 uppercase tracking-widest text-center">Status</th>
                    <th class="px-8 py-5 text-right text-[11px] font-bold text-gray-500 uppercase tracking-widest">Action</th>
                </tr>
            </thead>
            <tbody id="tableBody" class="divide-y divide-gray-50">
                <!-- Records injected here -->
            </tbody>
        </table>
    </div>

    <!-- Pagination Context -->
    <div class="px-8 py-5 bg-gray-50/50 border-t border-gray-100 flex items-center justify-between">
        <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest" id="pageInfo">Showing 0 of 0 Records</div>
        <div class="flex items-center space-x-2">
            <button onclick="prevPage()" id="prevBtn" class="p-2.5 bg-white border border-gray-100 rounded-xl text-gray-400 hover:text-purple-600 disabled:opacity-50 disabled:pointer-events-none transition-all shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </button>
            <div id="pageNumbers" class="flex items-center space-x-1.5"></div>
            <button onclick="nextPage()" id="nextBtn" class="p-2.5 bg-white border border-gray-100 rounded-xl text-gray-400 hover:text-purple-600 disabled:opacity-50 disabled:pointer-events-none transition-all shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </button>
        </div>
    </div>
</div>

<script>
let currentPage = 1;
let filters = { search: '', status: 'all', limit: 12 };
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
        const response = await fetch(`installations/api/get-installations.php?${query}`);
        const result = await response.json();
        
        if (result.success) {
            renderTable(result.data, result.pagination);
            renderPagination(result.pagination);
            document.getElementById('recordCount').innerText = result.pagination.total;
        }
    } catch (e) {
        console.error("Installation Hub Fetch Error:", e);
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
        tbody.innerHTML = `<tr><td colspan="6" class="py-32 text-center text-gray-400 text-[10px] font-black uppercase tracking-[0.2em]">No installation tasks match your parameters</td></tr>`;
        return;
    }

    tbody.innerHTML = data.map((inst, index) => {
        const serial = (pagination.page - 1) * pagination.limit + index + 1;
        
        return `
            <tr class="hover:bg-gray-50/50 transition-colors group">
                <td class="px-8 py-6 text-center text-sm font-bold text-gray-400">#${serial}</td>
                <td class="px-8 py-6">
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-blue-600 tracking-wide uppercase">${inst.site_code}</span>
                        <div class="mt-2 flex items-center space-x-2">
                             <span class="text-[10px] font-bold px-2 py-0.5 bg-gray-100 text-gray-600 rounded-md uppercase tracking-tight">${inst.installation_type}</span>
                             <span class="text-[10px] font-bold px-2 py-0.5 ${getPriorityColor(inst.priority)} border border-current rounded-md uppercase tracking-tight">PRIORITY: ${inst.priority}</span>
                        </div>
                        <span class="text-xs font-semibold text-gray-500 truncate max-w-[250px] mt-2.5">${inst.location}</span>
                    </div>
                </td>
                <td class="px-8 py-6">
                    <div class="w-32">
                        <div class="flex justify-between items-center mb-1.5">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tight">Progress</span>
                            <span class="text-xs font-bold text-gray-800">${inst.progress_percentage}%</span>
                        </div>
                        <div class="w-full bg-gray-100 h-2 rounded-full overflow-hidden shadow-inner">
                            <div class="bg-purple-600 h-full rounded-full transition-all duration-1000 shadow-sm" style="width: ${inst.progress_percentage}%"></div>
                        </div>
                    </div>
                </td>
                <td class="px-8 py-6">
                    <div class="flex flex-col space-y-1.5">
                        <div class="flex items-center space-x-2">
                            <span class="text-[10px] font-bold text-gray-400 uppercase w-10">DEAD:</span>
                            <span class="text-xs font-bold text-gray-700">${formatDate(inst.expected_completion_date)}</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-[10px] font-bold text-gray-400 uppercase w-10">START:</span>
                            <span class="text-xs font-semibold text-gray-500">${formatDate(inst.expected_start_date)}</span>
                        </div>
                    </div>
                </td>
                <td class="px-8 py-6 text-center">
                    <span class="inline-flex px-4 py-2 rounded-lg text-[10px] font-bold uppercase tracking-widest ${getStatusColor(inst.status)} border border-current opacity-90">
                        ${inst.status.replace('_', ' ')}
                    </span>
                </td>
                <td class="px-8 py-6 text-right">
                    <div class="flex items-center justify-end">
                       <a href="manage-installation.php?id=${inst.id}" class="inline-flex items-center px-5 py-2.5 bg-gray-50 border border-gray-200 hover:border-purple-600 hover:text-purple-700 text-gray-600 text-xs font-bold uppercase tracking-widest rounded-xl transition-all shadow-sm">
                            Execute Assignment
                        </a>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

function getStatusColor(status) {
    if (!status) return 'bg-gray-50 text-gray-400';
    status = status.toLowerCase();
    if (status === 'completed') return 'bg-green-50 text-green-600';
    if (status === 'in_progress') return 'bg-blue-50 text-blue-600';
    if (status === 'on_hold') return 'bg-amber-50 text-amber-600';
    if (status === 'cancelled') return 'bg-red-50 text-red-600';
    if (status === 'acknowledged') return 'bg-purple-50 text-purple-600';
    return 'bg-indigo-50 text-indigo-600';
}

function getPriorityColor(p) {
    p = p ? p.toLowerCase() : 'medium';
    if (p === 'urgent') return 'bg-red-50 text-red-600';
    if (p === 'high') return 'bg-orange-50 text-orange-600';
    if (p === 'medium') return 'bg-yellow-50 text-yellow-600';
    return 'bg-gray-50 text-gray-400';
}

function formatDate(dateStr) {
    if (!dateStr) return '---';
    return new Date(dateStr).toLocaleDateString('en-GB', { day: '2-digit', month: 'short' });
}

function renderPagination(p) {
    document.getElementById('pageInfo').innerText = `Batch ${p.page} of ${p.pages} (${p.total} Jobs)`;
    document.getElementById('prevBtn').disabled = p.page === 1;
    document.getElementById('nextBtn').disabled = p.page === p.pages;

    const nums = document.getElementById('pageNumbers');
    nums.innerHTML = '';
    
    const start = Math.max(1, p.page - 1);
    const end = Math.min(p.pages, start + 3);
    
    for (let i = start; i <= end; i++) {
        nums.innerHTML += `
            <button onclick="goToPage(${i})" class="w-10 h-10 rounded-xl text-[10px] font-bold transition-all ${i === p.page ? 'bg-purple-600 text-white shadow-lg shadow-purple-100' : 'bg-white text-gray-400 hover:text-purple-600'}">${i}</button>
        `;
    }
}

function goToPage(p) {
    currentPage = p;
    fetchData();
}

function nextPage() { currentPage++; fetchData(); }
function prevPage() { currentPage--; fetchData(); }
</script>

<style>
.status-btn.active {
    background-color: white;
    color: #7c3aed;
    box-shadow: 0 4px 12px -2px rgba(124, 58, 237, 0.15);
}
.status-btn:not(.active) {
    color: #94a3b8;
}
.status-btn:hover:not(.active) {
    color: #475569;
}
#tableBody tr {
    animation: fadeInSlide 0.4s ease-out forwards;
}
@keyframes fadeInSlide {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../includes/vendor_layout.php';
?>