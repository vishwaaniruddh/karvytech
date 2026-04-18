<?php 
require_once __DIR__ . '/../config/auth.php';
// Require vendor authentication
Auth::requireVendor();
$title = 'Survey Intelligence';
ob_start();
?>

<style>
    .surv-page { font-family: 'Inter', sans-serif; }

    .surv-stat {
        position: relative; overflow: hidden; border-radius: 20px; padding: 28px;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(255,255,255,0.08);
    }
    .surv-stat::before {
        content: ''; position: absolute; top: -40%; right: -30%;
        width: 180px; height: 180px; border-radius: 50%; opacity: 0.08; transition: all 0.5s ease;
    }
    .surv-stat:hover { transform: translateY(-4px); }
    .surv-stat:hover::before { opacity: 0.14; transform: scale(1.2); }

    .surv-stat.c-slate  { background: linear-gradient(135deg, #0f172a, #1e293b); box-shadow: 0 8px 32px rgba(15,23,42,0.25); }
    .surv-stat.c-slate::before { background: #3b82f6; }
    .surv-stat.c-amber  { background: linear-gradient(135deg, #78350f, #92400e); box-shadow: 0 8px 32px rgba(146,64,14,0.2); }
    .surv-stat.c-amber::before { background: #fbbf24; }
    .surv-stat.c-green  { background: linear-gradient(135deg, #064e3b, #065f46); box-shadow: 0 8px 32px rgba(6,95,70,0.2); }
    .surv-stat.c-green::before { background: #34d399; }
    .surv-stat.c-rose   { background: linear-gradient(135deg, #881337, #9f1239); box-shadow: 0 8px 32px rgba(159,18,57,0.2); }
    .surv-stat.c-rose::before { background: #fb7185; }

    .ssv { font-size: 2.5rem; font-weight: 900; line-height: 1; color: #fff; font-variant-numeric: tabular-nums; letter-spacing: -0.03em; }
    .ssl { font-size: 0.65rem; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase; color: rgba(255,255,255,0.5); margin-top: 8px; }
    .ssi { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.08); backdrop-filter: blur(8px); transition: all 0.3s; }
    .surv-stat:hover .ssi { background: rgba(255,255,255,0.14); transform: scale(1.08); }

    .status-btn.active { background-color: white; color: #059669; box-shadow: 0 4px 12px -2px rgba(5,150,105,0.15); }
    .status-btn:not(.active) { color: #94a3b8; }
    .status-btn:hover:not(.active) { color: #475569; }
    #tableBody tr { animation: slideIn 0.3s ease-out forwards; }
    @keyframes slideIn { from { opacity:0; transform:translateX(-5px); } to { opacity:1; transform:translateX(0); } }
    @keyframes ssFade { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }
    .ssf { animation: ssFade 0.4s cubic-bezier(0.4,0,0.2,1) both; }
    .ssf1 { animation-delay:0.05s; } .ssf2 { animation-delay:0.1s; } .ssf3 { animation-delay:0.15s; }
</style>

<div class="surv-page">
    <!-- Stat Cards -->
    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:20px; margin-bottom:28px;">
        <div class="surv-stat c-slate ssf">
            <div style="display:flex; align-items:flex-start; justify-content:space-between;">
                <div><div class="ssv" id="s-total">0</div><div class="ssl">Total Surveys</div></div>
                <div class="ssi"><svg width="22" height="22" fill="none" stroke="#60a5fa" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg></div>
            </div>
            <div style="margin-top:16px; display:flex; align-items:center; gap:6px;"><div style="width:24px;height:3px;border-radius:2px;background:rgba(96,165,250,0.4);"></div><span style="font-size:10px;font-weight:600;color:rgba(255,255,255,0.35);">All reports</span></div>
        </div>
        <div class="surv-stat c-amber ssf ssf1">
            <div style="display:flex; align-items:flex-start; justify-content:space-between;">
                <div><div class="ssv" id="s-pending">0</div><div class="ssl">Pending Review</div></div>
                <div class="ssi"><svg width="22" height="22" fill="none" stroke="#fcd34d" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            </div>
            <div style="margin-top:16px; display:flex; align-items:center; gap:6px;"><div style="width:24px;height:3px;border-radius:2px;background:rgba(251,191,36,0.4);"></div><span style="font-size:10px;font-weight:600;color:rgba(255,255,255,0.35);">Awaiting approval</span></div>
        </div>
        <div class="surv-stat c-green ssf ssf2">
            <div style="display:flex; align-items:flex-start; justify-content:space-between;">
                <div><div class="ssv" id="s-approved">0</div><div class="ssl">Approved</div></div>
                <div class="ssi"><svg width="22" height="22" fill="none" stroke="#6ee7b7" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            </div>
            <div style="margin-top:16px; display:flex; align-items:center; gap:6px;"><div style="width:24px;height:3px;border-radius:2px;background:rgba(52,211,153,0.4);"></div><span style="font-size:10px;font-weight:600;color:rgba(255,255,255,0.35);">Verified reports</span></div>
        </div>
        <div class="surv-stat c-rose ssf ssf3">
            <div style="display:flex; align-items:flex-start; justify-content:space-between;">
                <div><div class="ssv" id="s-rejected">0</div><div class="ssl">Rejected</div></div>
                <div class="ssi"><svg width="22" height="22" fill="none" stroke="#fda4af" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            </div>
            <div style="margin-top:16px; display:flex; align-items:center; gap:6px;"><div style="width:24px;height:3px;border-radius:2px;background:rgba(251,113,133,0.4);"></div><span style="font-size:10px;font-weight:600;color:rgba(255,255,255,0.35);">Needs revision</span></div>
        </div>
    </div>

    <!-- Toolbar -->
    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px; margin-bottom:20px;">
        <div style="display:flex; align-items:center; gap:12px;">
            <h2 style="font-size:15px; font-weight:800; color:#0f172a; letter-spacing:-0.01em;">Intelligence Dashboard</h2>
            <div class="bg-gray-100 p-1 rounded-xl flex border border-gray-200/50">
                <button onclick="setStatus('all')" class="status-btn active px-4 py-2 text-[10px] font-bold uppercase tracking-wider rounded-lg transition-all" data-status="all">All</button>
                <button onclick="setStatus('pending')" class="status-btn px-4 py-2 text-[10px] font-bold uppercase tracking-wider rounded-lg transition-all" data-status="pending">Pending</button>
                <button onclick="setStatus('approved')" class="status-btn px-4 py-2 text-[10px] font-bold uppercase tracking-wider rounded-lg transition-all" data-status="approved">Approved</button>
            </div>
        </div>
        <div class="relative group">
            <input type="text" id="globalSearch" oninput="debounceSearch()" placeholder="Search site ID, location or type..." class="pl-10 pr-4 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-green-500/20 focus:border-green-500 outline-none transition-all w-72">
            <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </div>
    </div>

    <!-- Data Grid -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden relative min-h-[500px]">
        <div id="loadingOverlay" class="absolute inset-0 bg-white/60 backdrop-blur-[2px] z-20 flex items-center justify-center opacity-0 pointer-events-none transition-opacity duration-300">
            <div class="flex flex-col items-center">
                <div class="w-10 h-10 border-4 border-green-500/20 border-t-green-600 rounded-full animate-spin"></div>
                <p style="font-size:9px; font-weight:700; color:#059669; text-transform:uppercase; letter-spacing:0.12em; margin-top:16px;">Analyzing Records</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr style="background:#f8fafc; border-bottom:1px solid #f1f5f9;">
                        <th style="padding:14px 28px; font-size:9px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#94a3b8; width:56px; text-align:center;">#</th>
                        <th style="padding:14px 28px; font-size:9px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#94a3b8;">Project Identity</th>
                        <th style="padding:14px 28px; font-size:9px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#94a3b8;">Date Submitted</th>
                        <th style="padding:14px 28px; font-size:9px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#94a3b8; text-align:center;">Status</th>
                        <th style="padding:14px 28px; font-size:9px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#94a3b8; text-align:right;">Manifest</th>
                    </tr>
                </thead>
                <tbody id="tableBody" class="divide-y divide-gray-50"></tbody>
            </table>
        </div>

        <div style="padding:14px 28px; background:#f8fafc; border-top:1px solid #f1f5f9; display:flex; align-items:center; justify-content:space-between;">
            <div style="font-size:10px; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:0.08em;" id="pageInfo">Showing 0 of 0 Records</div>
            <div class="flex items-center space-x-2">
                <button onclick="prevPage()" id="prevBtn" class="p-2 bg-white border border-gray-200 rounded-lg text-gray-400 hover:text-green-600 disabled:opacity-50 disabled:pointer-events-none transition-all"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg></button>
                <div id="pageNumbers" class="flex items-center space-x-1"></div>
                <button onclick="nextPage()" id="nextBtn" class="p-2 bg-white border border-gray-200 rounded-lg text-gray-400 hover:text-green-600 disabled:opacity-50 disabled:pointer-events-none transition-all"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg></button>
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
        const response = await fetch(`surveys/api/get-surveys.php?${query}`);
        const result = await response.json();
        if (result.success) {
            renderTable(result.data, result.pagination);
            renderPagination(result.pagination);
            updateStats(result.data, result.pagination.total);
        }
    } catch (e) { console.error("Survey Hub Fetch Error:", e); }
    finally { toggleLoading(false); }
}

function updateStats(data, total) {
    const pending = data.filter(d => ['pending','submitted'].includes(d.survey_status?.toLowerCase())).length;
    const approved = data.filter(d => ['approved','completed'].includes(d.survey_status?.toLowerCase())).length;
    const rejected = data.filter(d => d.survey_status?.toLowerCase() === 'rejected').length;
    animateCounter('s-total', total);
    animateCounter('s-pending', pending);
    animateCounter('s-approved', approved);
    animateCounter('s-rejected', rejected);
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
    if (data.length === 0) { tbody.innerHTML = `<tr><td colspan="5" class="py-32 text-center text-gray-400 text-xs font-semibold uppercase tracking-wide">No survey records match your parameters</td></tr>`; return; }
    tbody.innerHTML = data.map((survey, index) => {
        const serial = (pagination.page - 1) * pagination.limit + index + 1;
        const viewLink = survey.source === 'dynamic' ? `../shared/view-survey2.php?id=${survey.id}` : `../shared/view-survey.php?id=${survey.id}`;
        return `
            <tr class="hover:bg-gray-50/50 transition-colors group">
                <td class="px-8 py-5 text-center text-xs font-semibold text-gray-400">#${serial}</td>
                <td class="px-8 py-5">
                    <div class="flex flex-col">
                        <span class="text-sm font-semibold text-blue-600 uppercase">${survey.site_code}</span>
                        <span class="text-[13px] font-semibold text-gray-800 line-clamp-1 mt-1">${survey.survey_title}</span>
                        <span class="text-xs font-medium text-gray-500 truncate max-w-[300px] mt-1">${survey.location}</span>
                    </div>
                </td>
                <td class="px-8 py-5">
                    <div class="flex flex-col">
                        <span class="text-sm font-semibold text-gray-700">${formatDate(survey.submitted_date)}</span>
                        <span class="text-xs font-medium text-gray-400 mt-0.5">${formatTime(survey.submitted_date)}</span>
                    </div>
                </td>
                <td class="px-8 py-5 text-center">
                    <span class="inline-flex px-4 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wide ${getStatusColor(survey.survey_status)} border border-current opacity-90">
                        ${survey.survey_status || 'Submitted'}
                    </span>
                </td>
                <td class="px-8 py-5 text-right">
                    <a href="${viewLink}" target="_blank" class="inline-flex items-center px-5 py-2.5 bg-gray-50 border border-gray-200 hover:border-green-600 hover:text-green-700 text-gray-600 text-[11px] font-semibold uppercase tracking-wide rounded-xl transition-all shadow-sm">
                        Review Result
                    </a>
                </td>
            </tr>`;
    }).join('');
}

function getStatusColor(status) {
    if (!status) return 'bg-gray-50 text-gray-400';
    status = status.toLowerCase();
    if (status === 'approved' || status === 'completed') return 'bg-green-50 text-green-600';
    if (status === 'rejected') return 'bg-red-50 text-red-600';
    if (status === 'submitted' || status === 'pending') return 'bg-amber-50 text-amber-600';
    return 'bg-blue-50 text-blue-600';
}

function formatDate(dateStr) {
    if (!dateStr) return '---';
    return new Date(dateStr).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}

function formatTime(dateStr) {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
}

function renderPagination(p) {
    document.getElementById('pageInfo').innerText = `Page ${p.page} of ${p.pages} (${p.total} reports)`;
    document.getElementById('prevBtn').disabled = p.page === 1;
    document.getElementById('nextBtn').disabled = p.page === p.pages;
    const nums = document.getElementById('pageNumbers');
    nums.innerHTML = '';
    const start = Math.max(1, p.page - 1), end = Math.min(p.pages, start + 3);
    for (let i = start; i <= end; i++) {
        nums.innerHTML += `<button onclick="goToPage(${i})" class="w-8 h-8 rounded-lg text-xs font-bold transition-all ${i === p.page ? 'bg-green-600 text-white shadow-lg' : 'bg-white text-gray-400 hover:text-green-600'}">${i}</button>`;
    }
}

function goToPage(p) { currentPage = p; fetchData(); }
function nextPage() { currentPage++; fetchData(); }
function prevPage() { currentPage--; fetchData(); }
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../includes/vendor_layout.php';
?>