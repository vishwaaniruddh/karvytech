<?php
require_once __DIR__ . '/../config/auth.php';

// Require vendor authentication
Auth::requireVendor();

$title = 'Material Received Hub';
ob_start();
?>

<style>
    .mr-page { font-family: 'Inter', sans-serif; }

    .mr-stat {
        position: relative; overflow: hidden; border-radius: 20px; padding: 28px;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(255,255,255,0.08);
    }
    .mr-stat::before {
        content: ''; position: absolute; top: -40%; right: -30%;
        width: 180px; height: 180px; border-radius: 50%; opacity: 0.08; transition: all 0.5s ease;
    }
    .mr-stat:hover { transform: translateY(-4px); }
    .mr-stat:hover::before { opacity: 0.14; transform: scale(1.2); }

    .mr-stat.c-slate { background: linear-gradient(135deg, #0f172a, #1e293b); box-shadow: 0 8px 32px rgba(15,23,42,0.25); }
    .mr-stat.c-slate::before { background: #3b82f6; }
    .mr-stat.c-amber { background: linear-gradient(135deg, #78350f, #92400e); box-shadow: 0 8px 32px rgba(146,64,14,0.2); }
    .mr-stat.c-amber::before { background: #fbbf24; }
    .mr-stat.c-green { background: linear-gradient(135deg, #064e3b, #065f46); box-shadow: 0 8px 32px rgba(6,95,70,0.2); }
    .mr-stat.c-green::before { background: #34d399; }
    .mr-stat.c-rose  { background: linear-gradient(135deg, #881337, #9f1239); box-shadow: 0 8px 32px rgba(159,18,57,0.2); }
    .mr-stat.c-rose::before { background: #fb7185; }

    .mrv { font-size: 2.5rem; font-weight: 900; line-height: 1; color: #fff; font-variant-numeric: tabular-nums; letter-spacing: -0.03em; }
    .mrl { font-size: 0.65rem; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase; color: rgba(255,255,255,0.5); margin-top: 8px; }
    .mri { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.08); backdrop-filter: blur(8px); transition: all 0.3s; }
    .mr-stat:hover .mri { background: rgba(255,255,255,0.14); transform: scale(1.08); }

    /* Modal Animation States */
    #sidebar-modal { visibility: hidden; pointer-events: none; transition: all 0.3s ease; }
    #sidebar-modal.active { visibility: visible; pointer-events: auto; }
    #modal-panel { transform: translateX(100%); transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
    #sidebar-modal.active #modal-panel { transform: translateX(0); }
    #modal-overlay { opacity: 0; transition: opacity 0.3s ease; }
    #sidebar-modal.active #modal-overlay { opacity: 1; }

    .premium-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: all 0.3s ease; }
    .premium-card:hover { border-color: #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    .status-badge { font-size: 0.6rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; padding: 0.25rem 0.75rem; border-radius: 9999px; }

    @keyframes mrfade { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }
    .mrf { animation: mrfade 0.4s cubic-bezier(0.4,0,0.2,1) both; }
    .mrf1 { animation-delay: 0.05s; } .mrf2 { animation-delay: 0.1s; } .mrf3 { animation-delay: 0.15s; }
</style>

<div class="mr-page">
    <!-- Header -->
    <div style="display:flex; flex-wrap:wrap; align-items:flex-end; justify-content:space-between; gap:16px; margin-bottom:28px;">
        <div>
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:6px;">
                <div style="width:8px; height:8px; border-radius:50%; background:linear-gradient(135deg,#3b82f6,#8b5cf6); box-shadow:0 0 10px rgba(59,130,246,0.4);"></div>
                <span style="font-size:10px; font-weight:800; letter-spacing:0.18em; text-transform:uppercase; color:#3b82f6;">Supply Chain</span>
            </div>
            <h1 style="font-size:24px; font-weight:900; color:#0f172a; letter-spacing:-0.03em;">Manifest Hub</h1>
            <p style="font-size:13px; font-weight:500; color:#94a3b8; margin-top:4px;">Institutional Material Tracking & Ledger Control</p>
        </div>
        <div style="display:flex; align-items:center; gap:10px;">
            <button onclick="fetchMaterials()" style="display:flex; align-items:center; gap:6px; padding:8px 16px; background:#fff; border:1px solid #e2e8f0; border-radius:12px; font-size:11px; font-weight:600; color:#64748b; cursor:pointer;">
                <svg id="refresh-icon" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="transition:transform 0.5s;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                Sync
            </button>
            <a href="material-request.php" style="display:flex; align-items:center; gap:6px; padding:8px 16px; background:#0f172a; color:#fff; border-radius:12px; font-size:11px; font-weight:600; text-decoration:none;">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                New Request
            </a>
        </div>
    </div>

    <!-- Stat Cards (Dark Gradient) -->
    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:20px; margin-bottom:28px;">
        <div class="mr-stat c-slate mrf">
            <div style="display:flex; align-items:flex-start; justify-content:space-between;">
                <div><div class="mrv" id="stat-total">0</div><div class="mrl">Total Shipments</div></div>
                <div class="mri"><svg width="22" height="22" fill="none" stroke="#60a5fa" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></div>
            </div>
            <div style="margin-top:16px; display:flex; align-items:center; gap:6px;"><div style="width:24px;height:3px;border-radius:2px;background:rgba(96,165,250,0.4);"></div><span style="font-size:10px;font-weight:600;color:rgba(255,255,255,0.35);">All manifests</span></div>
        </div>
        <div class="mr-stat c-amber mrf mrf1">
            <div style="display:flex; align-items:flex-start; justify-content:space-between;">
                <div><div class="mrv" id="stat-transit">0</div><div class="mrl">Pending Audit</div></div>
                <div class="mri"><svg width="22" height="22" fill="none" stroke="#fcd34d" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            </div>
            <div style="margin-top:16px; display:flex; align-items:center; gap:6px;"><div style="width:24px;height:3px;border-radius:2px;background:rgba(251,191,36,0.4);"></div><span style="font-size:10px;font-weight:600;color:rgba(255,255,255,0.35);">In transit</span></div>
        </div>
        <div class="mr-stat c-green mrf mrf2">
            <div style="display:flex; align-items:flex-start; justify-content:space-between;">
                <div><div class="mrv" id="stat-finalized">0</div><div class="mrl">Closed Manifests</div></div>
                <div class="mri"><svg width="22" height="22" fill="none" stroke="#6ee7b7" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            </div>
            <div style="margin-top:16px; display:flex; align-items:center; gap:6px;"><div style="width:24px;height:3px;border-radius:2px;background:rgba(52,211,153,0.4);"></div><span style="font-size:10px;font-weight:600;color:rgba(255,255,255,0.35);">Audit finalized</span></div>
        </div>
        <div class="mr-stat c-rose mrf mrf3">
            <div style="display:flex; align-items:flex-start; justify-content:space-between;">
                <div><div class="mrv" id="stat-shortage">0</div><div class="mrl">Partial Deliveries</div></div>
                <div class="mri"><svg width="22" height="22" fill="none" stroke="#fda4af" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></div>
            </div>
            <div style="margin-top:16px; display:flex; align-items:center; gap:6px;"><div style="width:24px;height:3px;border-radius:2px;background:rgba(251,113,133,0.4);"></div><span style="font-size:10px;font-weight:600;color:rgba(255,255,255,0.35);">Discrepancies</span></div>
        </div>
    </div>

    <!-- Data Ledger -->
    <div class="premium-card overflow-hidden">
        <div class="p-6 border-b border-gray-50">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <h3 style="font-size:15px; font-weight:800; color:#0f172a; letter-spacing:-0.01em;">Inbound Ledger</h3>
                <div class="flex flex-wrap items-center gap-3">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </span>
                        <input type="text" id="manifest-search" onkeyup="applyFilters()" placeholder="Search..." class="block w-48 pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-blue-500/10 focus:border-blue-500 transition-all">
                    </div>
                    
                    <select id="status-filter" onchange="applyFilters()" class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-blue-500/10 focus:border-blue-500 transition-all">
                        <option value="">All Status</option>
                        <option value="dispatched">Dispatched</option>
                        <option value="in_transit">In Transit</option>
                        <option value="delivered">Delivered (Audited)</option>
                        <option value="partially_delivered">Partial Audit</option>
                    </select>
                    
                    <button onclick="exportToExcel()" class="flex items-center px-4 py-2 bg-emerald-600 text-white text-xs font-semibold rounded-xl hover:bg-emerald-700 transition-all shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Export Excel
                    </button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left" id="manifest-table">
                <thead>
                    <tr style="background:#f8fafc;">
                        <th style="padding:14px 24px; font-size:9px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#94a3b8;">#</th>
                        <th style="padding:14px 24px; font-size:9px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#94a3b8;">Material Identification</th>
                        <th style="padding:14px 24px; font-size:9px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#94a3b8;">Logistics</th>
                        <th style="padding:14px 24px; font-size:9px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#94a3b8; text-align:center;">Payload</th>
                        <th style="padding:14px 24px; font-size:9px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#94a3b8;">Status</th>
                        <th style="padding:14px 24px; font-size:9px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#94a3b8; text-align:right;">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="manifest-body">
                    <tr>
                        <td colspan="6" class="px-4 py-20 text-center">
                            <div class="flex flex-col items-center justify-center space-y-4">
                                <div class="w-10 h-10 border-4 border-slate-900 border-t-transparent rounded-full animate-spin"></div>
                                <p style="font-size:9px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.12em;">Synchronizing Ledger...</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div style="padding:14px 24px; background:#f8fafc; border-top:1px solid #f1f5f9; display:flex; align-items:center; justify-content:space-between;">
            <div style="font-size:10px; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:0.08em;">
                Displaying <span id="showing-start">0</span> - <span id="showing-end">0</span> of <span id="total-records">0</span> Manifests
            </div>
            <div class="flex items-center gap-2" id="pagination-controls"></div>
        </div>
    </div>
</div>

<!-- Sidebar Modal -->
<div id="sidebar-modal" class="fixed inset-0 z-[2000] flex justify-end">
    <div id="modal-overlay" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm cursor-pointer"></div>
    <div id="modal-panel" class="relative h-full w-full max-w-2xl bg-white shadow-2xl flex flex-col">
        <button id="close-modal-btn" class="absolute left-[-4rem] top-10 w-12 h-12 bg-slate-900 text-white rounded-2xl flex items-center justify-center hover:bg-black transition-all shadow-xl active:scale-95 group">
            <svg class="w-6 h-6 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>

        <div id="modal-header-container" class="px-8 py-6 border-b border-gray-100 bg-gray-50/50">
            <div class="flex items-center gap-3 mb-3">
                <span class="status-badge" id="modal-status-badge">Status</span>
                <span style="font-size:11px; font-weight:700; color:#cbd5e1; font-family:monospace;" id="modal-dispatch-id">#0000</span>
            </div>
            <h3 style="font-size:20px; font-weight:800; color:#0f172a; letter-spacing:-0.02em;" id="modal-title">Manifest Overview</h3>
            <p style="font-size:10px; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:0.1em; margin-top:4px;" id="modal-subtitle">Package & Payload Decomposition</p>
        </div>
        
        <div class="flex-1 overflow-y-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/50 sticky top-0 z-10 border-b border-gray-100">
                    <tr>
                        <th style="padding:14px 32px; font-size:9px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#94a3b8;">Material Identity</th>
                        <th style="padding:14px 32px; font-size:9px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#94a3b8; text-align:right;">Dispatched</th>
                    </tr>
                </thead>
                <tbody id="modal-body-content" class="divide-y divide-gray-50"></tbody>
            </table>
        </div>

        <div class="px-8 py-6 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
            <button onclick="closeModal()" style="font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.1em; background:none; border:none; cursor:pointer;">Close Record</button>
            <div id="modal-footer-actions"></div>
        </div>
    </div>
</div>

<template id="item-row-template">
    <tr class="hover:bg-slate-50/50 transition-colors">
        <td class="px-8 py-5">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center text-slate-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <div>
                    <div style="font-size:13px; font-weight:600; color:#0f172a;" class="item-name">Item Name</div>
                    <div style="font-size:10px; font-weight:500; color:#94a3b8; font-family:monospace;" class="item-code">CODE-000</div>
                </div>
            </div>
        </td>
        <td class="px-8 py-5 text-right">
            <div style="font-size:18px; font-weight:800; color:#0f172a;" class="item-qty">0</div>
            <div style="font-size:9px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.1em;" class="item-unit">Units</div>
        </td>
    </tr>
</template>

<script>
let currentPage = 1;
let totalRecords = 0;
let totalPages = 0;
const recordsPerPage = 10;
let searchTerm = '';
let statusFilter = '';

document.addEventListener('DOMContentLoaded', () => {
    fetchMaterials();
    document.getElementById('modal-overlay').onclick = closeModal;
    document.getElementById('close-modal-btn').onclick = closeModal;
});

async function fetchMaterials() {
    const tableBody = document.getElementById('manifest-body');
    const refreshIcon = document.getElementById('refresh-icon');
    refreshIcon.style.transform = 'rotate(360deg)';
    
    try {
        const params = new URLSearchParams({ page: currentPage, limit: recordsPerPage });
        if (searchTerm) params.append('search', searchTerm);
        if (statusFilter) params.append('status', statusFilter);
        
        const response = await fetch(`v1/get-received-materials.php?${params.toString()}`);
        const result = await response.json();
        
        if (result.success) {
            totalRecords = result.pagination.total;
            totalPages = result.pagination.totalPages;
            renderTable(result.data);
            renderPagination();
            updateStats(result.data, totalRecords);
        }
    } catch (error) {
        console.error('Fetch error:', error);
    } finally {
        setTimeout(() => { refreshIcon.style.transform = 'rotate(0deg)'; }, 500);
    }
}

function updateStats(data, total) {
    const transit = data.filter(d => !['delivered', 'partially_delivered'].includes(d.dispatch_status)).length;
    const finalized = data.filter(d => d.dispatch_status === 'delivered').length;
    const shortage = data.filter(d => d.dispatch_status === 'partially_delivered').length;

    animateValue('stat-total', 0, total, 600);
    animateValue('stat-transit', 0, transit, 600);
    animateValue('stat-finalized', 0, finalized, 600);
    animateValue('stat-shortage', 0, shortage, 600);
}

function animateValue(id, start, end, duration) {
    const obj = document.getElementById(id);
    if(!obj) return;
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 4);
        obj.innerHTML = Math.floor(eased * (end - start) + start);
        if (progress < 1) window.requestAnimationFrame(step);
    };
    window.requestAnimationFrame(step);
}

function applyFilters() {
    searchTerm = document.getElementById('manifest-search').value.trim();
    statusFilter = document.getElementById('status-filter').value;
    currentPage = 1;
    fetchMaterials();
}

function renderTable(data) {
    const tableBody = document.getElementById('manifest-body');
    const startIndex = (currentPage - 1) * recordsPerPage;
    const endIndex = Math.min(startIndex + data.length, totalRecords);

    tableBody.innerHTML = '';
    data.forEach((dispatch, index) => {
        const status = dispatch.dispatch_status || 'dispatched';
        const colors = getStatusColors(status);
        const globalIndex = startIndex + index + 1;
        const isAudited = ['delivered', 'partially_delivered'].includes(status);
        
        const row = document.createElement('tr');
        row.className = 'hover:bg-slate-50/50 transition-all cursor-pointer group';
        row.onclick = () => showItems(dispatch.id);
        
        row.innerHTML = `
            <td class="px-6 py-5" style="font-size:10px; font-weight:600; color:#cbd5e1;">#${String(globalIndex).padStart(2, '0')}</td>
            <td class="px-6 py-5">
                <div class="flex flex-col">
                    <span style="font-size:13px; font-weight:700; color:#1e293b;">${dispatch.dispatch_number}</span>
                    <span style="font-size:10px; font-weight:500; color:#94a3b8; margin-top:2px;">Site: ${dispatch.site_code || 'N/A'}</span>
                </div>
            </td>
            <td class="px-6 py-5">
                <div class="flex flex-col">
                    <span style="font-size:11px; font-weight:600; color:#475569;">${dispatch.courier_name || 'Internal'}</span>
                    <span style="font-size:10px; font-weight:500; color:#3b82f6; font-family:monospace; margin-top:2px;">${dispatch.tracking_number || '--'}</span>
                </div>
            </td>
            <td class="px-6 py-5 text-center">
                <span style="font-size:14px; font-weight:800; color:#0f172a;">${dispatch.actual_item_count || 0}</span>
                <span style="font-size:9px; font-weight:600; color:#94a3b8; display:block; text-transform:uppercase;">Items</span>
            </td>
            <td class="px-6 py-5">
                <span class="status-badge ${colors.bg} ${colors.text} border ${colors.border}">
                    ${status.replace('_', ' ')}
                </span>
            </td>
            <td class="px-6 py-5 text-right">
                <a href="audit-dispatch.php?id=${dispatch.id}" 
                   onclick="event.stopPropagation()" 
                   style="display:inline-flex; align-items:center; gap:6px; padding:8px 16px; font-size:10px; font-weight:700; border-radius:12px; text-decoration:none; text-transform:uppercase; letter-spacing:0.05em; transition:all 0.2s; ${isAudited ? 'background:#f1f5f9; color:#64748b;' : 'background:#0f172a; color:#fff;'}">
                    ${isAudited ? 'Review Audit' : 'Perform Audit'}
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </td>
        `;
        tableBody.appendChild(row);
    });
    
    updatePaginationInfo(startIndex + 1, endIndex, totalRecords);
}

function getStatusColors(status) {
    const map = {
        'dispatched': { bg: 'bg-amber-100/30', text: 'text-amber-700', border: 'border-amber-200/50' },
        'in_transit': { bg: 'bg-purple-100/30', text: 'text-purple-700', border: 'border-purple-200/50' },
        'delivered': { bg: 'bg-emerald-100/30', text: 'text-emerald-700', border: 'border-emerald-200/50' },
        'partially_delivered': { bg: 'bg-rose-100/30', text: 'text-rose-700', border: 'border-rose-200/50' }
    };
    return map[status] || map.dispatched;
}

function renderPagination() {
    const pc = document.getElementById('pagination-controls');
    if (totalPages <= 1) { pc.innerHTML = ''; return; }
    let html = '';
    html += `<button onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''} style="padding:4px 12px; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; border-radius:12px; border:1px solid #e2e8f0; background:#fff; cursor:pointer; color:${currentPage === 1 ? '#cbd5e1' : '#64748b'};">Prev</button>`;
    for (let i = 1; i <= totalPages; i++) {
        html += `<button onclick="changePage(${i})" style="padding:4px 12px; font-size:10px; font-weight:700; border-radius:12px; border:1px solid #e2e8f0; cursor:pointer; ${i === currentPage ? 'background:#0f172a; color:#fff;' : 'background:#fff; color:#64748b;'}">${i}</button>`;
    }
    html += `<button onclick="changePage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''} style="padding:4px 12px; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; border-radius:12px; border:1px solid #e2e8f0; background:#fff; cursor:pointer; color:${currentPage === totalPages ? '#cbd5e1' : '#64748b'};">Next</button>`;
    pc.innerHTML = html;
}

function changePage(page) { if (page < 1 || page > totalPages) return; currentPage = page; fetchMaterials(); }
function updatePaginationInfo(start, end, total) {
    document.getElementById('showing-start').textContent = start;
    document.getElementById('showing-end').textContent = end;
    document.getElementById('total-records').textContent = total;
}

async function showItems(dispatchId) {
    const modal = document.getElementById('sidebar-modal');
    const modalBody = document.getElementById('modal-body-content');
    const footer = document.getElementById('modal-footer-actions');
    
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';

    try {
        const response = await fetch(`v1/get-dispatch-items.php?dispatch_id=${dispatchId}`);
        const result = await response.json();

        if (result.success) {
            modalBody.innerHTML = '';
            const template = document.getElementById('item-row-template');
            result.data.forEach(item => {
                const clone = template.content.cloneNode(true);
                clone.querySelector('.item-name').textContent = item.item_name;
                clone.querySelector('.item-code').textContent = item.item_code;
                clone.querySelector('.item-qty').textContent = item.quantity_dispatched;
                modalBody.appendChild(clone);
            });
            
            footer.innerHTML = `
                <a href="audit-dispatch.php?id=${dispatchId}" style="display:flex; align-items:center; gap:8px; padding:8px 20px; background:#0f172a; color:#fff; font-size:10px; font-weight:700; border-radius:12px; text-decoration:none; text-transform:uppercase; letter-spacing:0.08em;">
                    Enter Audit Lab
                </a>
            `;
        }
    } catch (e) { console.error(e); }
}

function closeModal() {
    document.getElementById('sidebar-modal').classList.remove('active');
    document.body.style.overflow = 'auto';
}

function exportToExcel() {
    const params = new URLSearchParams();
    if (searchTerm) params.append('search', searchTerm);
    if (statusFilter) params.append('status', statusFilter);
    window.location.href = `v1/export-received-materials.php?${params.toString()}`;
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../includes/vendor_layout.php';
?>
