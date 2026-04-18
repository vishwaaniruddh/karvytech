<?php 
require_once __DIR__ . '/../config/auth.php';
// Require vendor authentication
Auth::requireVendor();
$title = 'Installation Hub';
ob_start();
?>

<style>
    .inst-page { font-family: 'Inter', sans-serif; }

    .inst-stat {
        position: relative; overflow: hidden; border-radius: 20px; padding: 28px;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(255,255,255,0.08);
    }
    .inst-stat::before {
        content: ''; position: absolute; top: -40%; right: -30%;
        width: 180px; height: 180px; border-radius: 50%; opacity: 0.08; transition: all 0.5s ease;
    }
    .inst-stat:hover { transform: translateY(-4px); }
    .inst-stat:hover::before { opacity: 0.14; transform: scale(1.2); }

    .inst-stat.c-slate  { background: linear-gradient(135deg, #0f172a, #1e293b); box-shadow: 0 8px 32px rgba(15,23,42,0.25); }
    .inst-stat.c-slate::before { background: #3b82f6; }
    .inst-stat.c-purple { background: linear-gradient(135deg, #2e1065, #4c1d95); box-shadow: 0 8px 32px rgba(76,29,149,0.2); }
    .inst-stat.c-purple::before { background: #a78bfa; }
    .inst-stat.c-green  { background: linear-gradient(135deg, #064e3b, #065f46); box-shadow: 0 8px 32px rgba(6,95,70,0.2); }
    .inst-stat.c-green::before { background: #34d399; }
    .inst-stat.c-amber  { background: linear-gradient(135deg, #78350f, #92400e); box-shadow: 0 8px 32px rgba(146,64,14,0.2); }
    .inst-stat.c-amber::before { background: #fbbf24; }

    .isv { font-size: 2.5rem; font-weight: 900; line-height: 1; color: #fff; font-variant-numeric: tabular-nums; letter-spacing: -0.03em; }
    .isl { font-size: 0.65rem; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase; color: rgba(255,255,255,0.5); margin-top: 8px; }
    .isi { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.08); backdrop-filter: blur(8px); transition: all 0.3s; }
    .inst-stat:hover .isi { background: rgba(255,255,255,0.14); transform: scale(1.08); }

    .status-btn.active { background-color: white; color: #7c3aed; box-shadow: 0 4px 12px -2px rgba(124,58,237,0.15); }
    .status-btn:not(.active) { color: #94a3b8; }
    .status-btn:hover:not(.active) { color: #475569; }
    #tableBody tr { animation: fadeInSlide 0.4s ease-out forwards; }
    @keyframes fadeInSlide { from { opacity:0; transform:translateY(5px); } to { opacity:1; transform:translateY(0); } }
    @keyframes isFade { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }
    .isf { animation: isFade 0.4s cubic-bezier(0.4,0,0.2,1) both; }
    .isf1 { animation-delay:0.05s; } .isf2 { animation-delay:0.1s; } .isf3 { animation-delay:0.15s; }
</style>

<div class="inst-page">
    <!-- Stat Cards -->
    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:20px; margin-bottom:28px;">
        <div class="inst-stat c-slate isf">
            <div style="display:flex; align-items:flex-start; justify-content:space-between;">
                <div><div class="isv" id="s-total">0</div><div class="isl">Total Jobs</div></div>
                <div class="isi"><svg width="22" height="22" fill="none" stroke="#60a5fa" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg></div>
            </div>
            <div style="margin-top:16px; display:flex; align-items:center; gap:6px;"><div style="width:24px;height:3px;border-radius:2px;background:rgba(96,165,250,0.4);"></div><span style="font-size:10px;font-weight:600;color:rgba(255,255,255,0.35);">All assignments</span></div>
        </div>
        <div class="inst-stat c-purple isf isf1">
            <div style="display:flex; align-items:flex-start; justify-content:space-between;">
                <div><div class="isv" id="s-active">0</div><div class="isl">In Progress</div></div>
                <div class="isi"><svg width="22" height="22" fill="none" stroke="#c4b5fd" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg></div>
            </div>
            <div style="margin-top:16px; display:flex; align-items:center; gap:6px;"><div style="width:24px;height:3px;border-radius:2px;background:rgba(167,139,250,0.4);"></div><span style="font-size:10px;font-weight:600;color:rgba(255,255,255,0.35);">Active deployments</span></div>
        </div>
        <div class="inst-stat c-green isf isf2">
            <div style="display:flex; align-items:flex-start; justify-content:space-between;">
                <div><div class="isv" id="s-done">0</div><div class="isl">Completed</div></div>
                <div class="isi"><svg width="22" height="22" fill="none" stroke="#6ee7b7" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            </div>
            <div style="margin-top:16px; display:flex; align-items:center; gap:6px;"><div style="width:24px;height:3px;border-radius:2px;background:rgba(52,211,153,0.4);"></div><span style="font-size:10px;font-weight:600;color:rgba(255,255,255,0.35);">Successfully installed</span></div>
        </div>
        <div class="inst-stat c-amber isf isf3">
            <div style="display:flex; align-items:flex-start; justify-content:space-between;">
                <div><div class="isv" id="s-hold">0</div><div class="isl">On Hold</div></div>
                <div class="isi"><svg width="22" height="22" fill="none" stroke="#fcd34d" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            </div>
            <div style="margin-top:16px; display:flex; align-items:center; gap:6px;"><div style="width:24px;height:3px;border-radius:2px;background:rgba(251,191,36,0.4);"></div><span style="font-size:10px;font-weight:600;color:rgba(255,255,255,0.35);">Awaiting action</span></div>
        </div>
    </div>

    <!-- Toolbar -->
    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px; margin-bottom:20px;">
        <div style="display:flex; align-items:center; gap:12px;">
            <h2 style="font-size:15px; font-weight:800; color:#0f172a; letter-spacing:-0.01em;">Deployment Intelligence</h2>
            <div class="bg-gray-100 p-1 rounded-xl flex border border-gray-200/50">
                <button onclick="setStatus('all')" class="status-btn active px-4 py-2 text-[10px] font-bold uppercase tracking-wider rounded-lg transition-all" data-status="all">All</button>
                <button onclick="setStatus('in_progress')" class="status-btn px-4 py-2 text-[10px] font-bold uppercase tracking-wider rounded-lg transition-all" data-status="in_progress">Active</button>
                <button onclick="setStatus('completed')" class="status-btn px-4 py-2 text-[10px] font-bold uppercase tracking-wider rounded-lg transition-all" data-status="completed">Done</button>
            </div>
        </div>
        <div class="relative group">
            <input type="text" id="globalSearch" oninput="debounceSearch()" placeholder="Search project ID, city or location..." class="pl-10 pr-4 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 outline-none transition-all w-72">
            <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </div>
    </div>

    <!-- Data Grid -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden relative min-h-[500px]">
        <div id="loadingOverlay" class="absolute inset-0 bg-white/60 backdrop-blur-[2px] z-20 flex items-center justify-center opacity-0 pointer-events-none transition-opacity duration-300">
            <div class="flex flex-col items-center">
                <div class="w-10 h-10 border-4 border-purple-500/20 border-t-purple-600 rounded-full animate-spin"></div>
                <p style="font-size:9px; font-weight:700; color:#7c3aed; text-transform:uppercase; letter-spacing:0.12em; margin-top:16px;">Syncing Deployments</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr style="background:#f8fafc; border-bottom:1px solid #f1f5f9;">
                        <th style="padding:14px 28px; font-size:9px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#94a3b8; width:56px; text-align:center;">#</th>
                        <th style="padding:14px 28px; font-size:9px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#94a3b8;">Project Identity</th>
                        <th style="padding:14px 28px; font-size:9px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#94a3b8;">Phase Metrics</th>
                        <th style="padding:14px 28px; font-size:9px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#94a3b8;">Timeline</th>
                        <th style="padding:14px 28px; font-size:9px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#94a3b8; text-align:center;">Status</th>
                        <th style="padding:14px 28px; font-size:9px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#94a3b8; text-align:right;">Action</th>
                    </tr>
                </thead>
                <tbody id="tableBody" class="divide-y divide-gray-50"></tbody>
            </table>
        </div>

        <div style="padding:14px 28px; background:#f8fafc; border-top:1px solid #f1f5f9; display:flex; align-items:center; justify-content:space-between;">
            <div style="font-size:10px; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:0.08em;" id="pageInfo">Showing 0 of 0 Records</div>
            <div class="flex items-center space-x-2">
                <button onclick="prevPage()" id="prevBtn" class="p-2 bg-white border border-gray-200 rounded-lg text-gray-400 hover:text-purple-600 disabled:opacity-50 disabled:pointer-events-none transition-all"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg></button>
                <div id="pageNumbers" class="flex items-center space-x-1"></div>
                <button onclick="nextPage()" id="nextBtn" class="p-2 bg-white border border-gray-200 rounded-lg text-gray-400 hover:text-purple-600 disabled:opacity-50 disabled:pointer-events-none transition-all"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg></button>
            </div>
        </div>
    </div>
</div>

<script>
let currentPage = 1;
let filters = { search: '', status: 'all', limit: 12 };
let searchDebounce = null;

document.addEventListener('DOMContentLoaded', () => { fetchData(); });

function debounceSearch() {
    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(() => { filters.search = document.getElementById('globalSearch').value; currentPage = 1; fetchData(); }, 400);
}

function setStatus(status) {
    filters.status = status;
    document.querySelectorAll('.status-btn').forEach(btn => btn.classList.toggle('active', btn.dataset.status === status));
    currentPage = 1; fetchData();
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
            updateStats(result.data, result.pagination.total);
        }
    } catch (e) { console.error("Installation Hub Fetch Error:", e); }
    finally { toggleLoading(false); }
}

function updateStats(data, total) {
    const active = data.filter(d => d.status === 'in_progress').length;
    const done = data.filter(d => d.status === 'completed').length;
    const hold = data.filter(d => ['on_hold','assigned','acknowledged'].includes(d.status)).length;
    animateCounter('s-total', total);
    animateCounter('s-active', active);
    animateCounter('s-done', done);
    animateCounter('s-hold', hold);
}

function animateCounter(id, target) {
    const el = document.getElementById(id); if (!el) return;
    const dur = 800, start = performance.now();
    function tick(now) {
        const p = Math.min((now - start) / dur, 1);
        el.textContent = Math.round(target * (1 - Math.pow(1 - p, 4)));
        if (p < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
}

function toggleLoading(show) {
    const l = document.getElementById('loadingOverlay');
    if (show) { l.classList.remove('pointer-events-none','opacity-0'); l.classList.add('opacity-100'); }
    else { l.classList.add('opacity-0','pointer-events-none'); l.classList.remove('opacity-100'); }
}

function renderTable(data, pagination) {
    const tbody = document.getElementById('tableBody');
    if (data.length === 0) { tbody.innerHTML = `<tr><td colspan="6" class="py-32 text-center text-gray-400 text-xs font-semibold uppercase tracking-wide">No installation tasks match your parameters</td></tr>`; return; }
    tbody.innerHTML = data.map((inst, index) => {
        const serial = (pagination.page - 1) * pagination.limit + index + 1;
        return `
            <tr class="hover:bg-gray-50/50 transition-colors group">
                <td class="px-8 py-5 text-center text-xs font-semibold text-gray-400">#${serial}</td>
                <td class="px-8 py-5">
                    <div class="flex flex-col">
                        <span class="text-sm font-semibold text-blue-600 uppercase">${inst.site_code}</span>
                        <div class="mt-2 flex items-center space-x-2">
                             <span class="text-[10px] font-semibold px-2 py-0.5 bg-gray-100 text-gray-600 rounded-md uppercase">${inst.installation_type}</span>
                             <span class="text-[10px] font-semibold px-2 py-0.5 ${getPriorityColor(inst.priority)} border border-current rounded-md uppercase">PRIORITY: ${inst.priority}</span>
                        </div>
                        <span class="text-xs font-medium text-gray-500 truncate max-w-[250px] mt-2">${inst.location}</span>
                    </div>
                </td>
                <td class="px-8 py-5">
                    <div class="w-32">
                        <div class="flex justify-between items-center mb-1.5">
                            <span class="text-[10px] font-semibold text-gray-400 uppercase">Progress</span>
                            <span class="text-xs font-bold text-gray-800">${inst.progress_percentage}%</span>
                        </div>
                        <div class="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
                            <div class="bg-purple-600 h-full rounded-full transition-all duration-1000" style="width: ${inst.progress_percentage}%"></div>
                        </div>
                    </div>
                </td>
                <td class="px-8 py-5">
                    <div class="flex flex-col space-y-1">
                        <div class="flex items-center space-x-2">
                            <span class="text-[10px] font-semibold text-gray-400 uppercase w-10">DEAD:</span>
                            <span class="text-xs font-semibold text-gray-700">${formatDate(inst.expected_completion_date)}</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-[10px] font-semibold text-gray-400 uppercase w-10">START:</span>
                            <span class="text-xs font-medium text-gray-500">${formatDate(inst.expected_start_date)}</span>
                        </div>
                    </div>
                </td>
                <td class="px-8 py-5 text-center">
                    <span class="inline-flex px-4 py-2 rounded-lg text-[10px] font-bold uppercase tracking-wide ${getStatusColor(inst.status)} border border-current opacity-90">
                        ${inst.status.replace('_', ' ')}
                    </span>
                </td>
                <td class="px-8 py-5 text-right">
                   <a href="manage-installation.php?id=${inst.id}" class="inline-flex items-center px-5 py-2.5 bg-gray-50 border border-gray-200 hover:border-purple-600 hover:text-purple-700 text-gray-600 text-[11px] font-semibold uppercase tracking-wide rounded-xl transition-all shadow-sm">
                        Execute Assignment
                    </a>
                </td>
            </tr>`;
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
    const start = Math.max(1, p.page - 1), end = Math.min(p.pages, start + 3);
    for (let i = start; i <= end; i++) {
        nums.innerHTML += `<button onclick="goToPage(${i})" class="w-8 h-8 rounded-lg text-xs font-bold transition-all ${i === p.page ? 'bg-purple-600 text-white shadow-lg' : 'bg-white text-gray-400 hover:text-purple-600'}">${i}</button>`;
    }
}

function goToPage(p) { currentPage = p; fetchData(); }
function nextPage() { currentPage++; fetchData(); }
function prevPage() { currentPage--; fetchData(); }
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../includes/vendor_layout.php';
?>