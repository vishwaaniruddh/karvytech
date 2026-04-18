<?php
require_once __DIR__ . '/../config/auth.php';

// Require vendor authentication
Auth::requireVendor();

$title = 'Material Received Hub';
ob_start();
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700;800;900&display=swap');

    :root {
        --primary-blue: #2563eb;
        --soft-slate: #f1f5f9;
        --border-slate: #e2e8f0;
        --font-main: 'Inter', sans-serif;
        --font-heading: 'Outfit', sans-serif;
    }

    body {
        font-family: var(--font-main);
        letter-spacing: -0.011em;
    }

    h1, h2, h3, h4, .font-heading {
        font-family: var(--font-heading) !important;
        letter-spacing: -0.02em;
    }

    /* Modal Animation States */
    #sidebar-modal {
        visibility: hidden;
        pointer-events: none;
        transition: all 0.3s ease;
    }
    #sidebar-modal.active {
        visibility: visible;
        pointer-events: auto;
    }
    #modal-panel {
        transform: translateX(100%);
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    #sidebar-modal.active #modal-panel {
        transform: translateX(0);
    }
    #modal-overlay {
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    #sidebar-modal.active #modal-overlay {
        opacity: 1;
    }

    .premium-card {
        background: #ffffff;
        border: 1px solid var(--border-slate);
        border-radius: 1rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }

    .premium-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .label-meta {
        font-size: 0.65rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.075em;
        color: #94a3b8;
    }

    .status-badge {
        font-size: 0.6rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
    }
</style>


    <!-- Sophisticated Header (Integrated Style) -->
    <div class="px-6 py-6 border-b border-slate-100 mb-6 bg-slate-50/30">
        <div class="max-w-[1400px] mx-auto flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight font-heading">Manifest Hub</h1>
                <p class="text-sm font-medium text-slate-500 mt-1">Institutional Material Tracking & Ledger Control</p>
            </div>
            <div class="flex items-center gap-3 mt-4 md:mt-0">
                <button onclick="fetchMaterials()" class="group flex items-center px-4 py-2 bg-white border border-slate-200 text-slate-600 text-xs font-semibold rounded-xl hover:bg-slate-50 hover:border-slate-300 transition-all shadow-sm">
                    <svg id="refresh-icon" class="w-4 h-4 mr-2 text-slate-400 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Sync
                </button>
                <a href="material-request.php" class="flex items-center px-4 py-2 bg-slate-900 text-white text-xs font-semibold rounded-xl hover:bg-emerald-600 transition-all shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    New Request
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-[1400px] mx-auto px-6">
        <!-- Operational Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="premium-card p-6 bg-white">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    </div>
                    <span class="text-xs font-bold text-slate-600 bg-slate-50 px-2.5 py-1 rounded-full uppercase tracking-wider">Total</span>
                </div>
                <div id="stat-total" class="text-2xl font-bold text-gray-800">0</div>
                <div class="text-xs text-gray-500 mt-1 uppercase tracking-wider font-semibold">Total Shipments</div>
            </div>

            <div class="premium-card p-6 bg-white">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center text-amber-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <span class="text-xs font-bold text-amber-600 bg-amber-50 px-2.5 py-1 rounded-full uppercase tracking-wider">Transit</span>
                </div>
                <div id="stat-transit" class="text-2xl font-bold text-gray-800">0</div>
                <div class="text-xs text-gray-500 mt-1 uppercase tracking-wider font-semibold">Pending Audit</div>
            </div>

            <div class="premium-card p-6 bg-white">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full uppercase tracking-wider">Finalized</span>
                </div>
                <div id="stat-finalized" class="text-2xl font-bold text-gray-800">0</div>
                <div class="text-xs text-gray-500 mt-1 uppercase tracking-wider font-semibold">Closed Manifests</div>
            </div>

            <div class="premium-card p-6 bg-white">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-rose-50 rounded-xl flex items-center justify-center text-rose-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <span class="text-xs font-bold text-rose-600 bg-rose-50 px-2.5 py-1 rounded-full uppercase tracking-wider">Discrepancy</span>
                </div>
                <div id="stat-shortage" class="text-2xl font-bold text-gray-800">0</div>
                <div class="text-xs text-gray-500 mt-1 uppercase tracking-wider font-semibold">Partial Deliveries</div>
            </div>
        </div>

        <!-- Data Ledger -->
        <div class="premium-card overflow-hidden">
            <div class="p-6 border-b border-gray-50">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <h3 class="text-lg font-bold text-gray-900">Inbound Ledger</h3>
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
                        <tr class="bg-gray-50/50">
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">#</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Material Identification</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Logistics</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Payload</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Administrative Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100" id="manifest-body">
                        <!-- Loading State -->
                        <tr>
                            <td colspan="6" class="px-4 py-20 text-center">
                                <div class="flex flex-col items-center justify-center space-y-4">
                                    <div class="w-10 h-10 border-4 border-slate-900 border-t-transparent rounded-full animate-spin"></div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest animate-pulse">Synchronizing Ledger...</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="p-6 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between">
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
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
                <span class="text-xs font-black text-slate-300 font-mono" id="modal-dispatch-id">#0000</span>
            </div>
            <h3 class="text-2xl font-black text-slate-900 tracking-tight font-heading" id="modal-title">Manifest Overview</h3>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1" id="modal-subtitle">Package & Payload Decomposition</p>
        </div>
        
        <div class="flex-1 overflow-y-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/50 sticky top-0 z-10 border-b border-gray-100">
                    <tr>
                        <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Material Identity</th>
                        <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Dispatched</th>
                    </tr>
                </thead>
                <tbody id="modal-body-content" class="divide-y divide-gray-50"></tbody>
            </table>
        </div>

        <div class="px-8 py-6 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
            <button onclick="closeModal()" class="text-[10px] font-black text-slate-400 hover:text-slate-900 uppercase tracking-widest">Close Record</button>
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
                    <div class="text-sm font-black text-slate-900 item-name uppercase">Item Name</div>
                    <div class="text-[9px] font-bold text-slate-400 font-mono item-code">CODE-000</div>
                </div>
            </div>
        </td>
        <td class="px-8 py-5 text-right">
            <div class="text-lg font-black text-slate-900 item-qty">0</div>
            <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest item-unit">Units</div>
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
    refreshIcon.classList.add('animate-spin');
    
    try {
        const params = new URLSearchParams({
            page: currentPage,
            limit: recordsPerPage
        });
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
        refreshIcon.classList.remove('animate-spin');
    }
}

function updateStats(data, total) {
    // We'd ideally fetch all for stats, but let's approximate or just use what we have
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
        obj.innerHTML = Math.floor(progress * (end - start) + start);
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
            <td class="px-6 py-5 text-[10px] font-black text-slate-300">#${String(globalIndex).padStart(2, '0')}</td>
            <td class="px-6 py-5">
                <div class="flex flex-col">
                    <span class="text-sm font-black text-slate-800 uppercase tracking-tight">${dispatch.dispatch_number}</span>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Site: ${dispatch.site_code || 'N/A'}</span>
                </div>
            </td>
            <td class="px-6 py-5">
                <div class="flex flex-col">
                    <span class="text-[11px] font-black text-slate-700 uppercase">${dispatch.courier_name || 'Internal'}</span>
                    <span class="text-[10px] font-bold text-blue-500 font-mono mt-0.5">${dispatch.tracking_number || '--'}</span>
                </div>
            </td>
            <td class="px-6 py-5 text-center">
                <span class="text-sm font-black text-slate-900">${dispatch.actual_item_count || 0}</span>
                <span class="text-[9px] font-bold text-slate-400 block uppercase">Items</span>
            </td>
            <td class="px-6 py-5">
                <span class="status-badge ${colors.bg} ${colors.text} border ${colors.border}">
                    ${status.replace('_', ' ')}
                </span>
            </td>
            <td class="px-6 py-5 text-right">
                <a href="audit-dispatch.php?id=${dispatch.id}" 
                   onclick="event.stopPropagation()" 
                   class="inline-flex items-center px-4 py-2 ${isAudited ? 'bg-slate-100 text-slate-600 hover:bg-slate-200' : 'bg-slate-900 text-white hover:bg-emerald-600'} text-[10px] font-black rounded-xl transition-all shadow-sm uppercase tracking-widest">
                    ${isAudited ? 'Review Audit' : 'Perform Audit'}
                    <svg class="w-3 h-3 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
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
    const paginationControls = document.getElementById('pagination-controls');
    if (totalPages <= 1) { paginationControls.innerHTML = ''; return; }
    
    let html = '';
    html += `<button onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''} class="px-3 py-1 text-[10px] font-black uppercase tracking-widest rounded-xl border ${currentPage === 1 ? 'text-slate-300' : 'text-slate-600 hover:bg-slate-50'}">Prev</button>`;
    for (let i = 1; i <= totalPages; i++) {
        html += `<button onclick="changePage(${i})" class="px-3 py-1 text-[10px] font-black rounded-xl border ${i === currentPage ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-50'}">${i}</button>`;
    }
    html += `<button onclick="changePage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''} class="px-3 py-1 text-[10px] font-black uppercase tracking-widest rounded-xl border ${currentPage === totalPages ? 'text-slate-300' : 'text-slate-600 hover:bg-slate-50'}">Next</button>`;
    paginationControls.innerHTML = html;
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
    const badge = document.getElementById('modal-status-badge');
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
            
            // Link in footer
            footer.innerHTML = `
                <a href="audit-dispatch.php?id=${dispatchId}" class="px-6 py-2 bg-slate-900 text-white text-[10px] font-black rounded-xl hover:bg-emerald-600 transition-all flex items-center gap-2 uppercase tracking-widest">
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
