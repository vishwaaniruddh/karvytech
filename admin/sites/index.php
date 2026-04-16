<?php
require_once __DIR__ . '/../../config/auth.php';
// Require module access
Auth::requireModuleAccess('sites');

$title = 'Advanced Sites Management';
ob_start();
?>

<style>
    th,
    td {
        white-space: nowrap;
    }

    .operation-group-header {
        border-bottom: 2px solid #e5e7eb;
    }

    .table-container {
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 #f8fafc;
        overflow-x: auto;
        max-width: 100%;
    }

    .section-masters {
        background-color: #f8fafc;
    }

    .section-delegation {
        background-color: #f0fdf4;
    }

    .section-survey {
        background-color: #eff6ff;
    }

    .section-material {
        background-color: #fffbeb;
    }

    .section-installation {
        background-color: #f5f3ff;
    }

    .loading-overlay {
        position: absolute;
        inset: 0;
        background: rgba(255, 255, 255, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 100;
        transition: opacity 0.3s ease;
    }

    .loading-overlay.hidden {
        display: none !important;
        opacity: 0;
        pointer-events: none;
    }

    /* Custom scrollbar for better look */
    .table-container::-webkit-scrollbar {
        height: 8px;
    }

    .table-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .table-container::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }

    .table-container::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>

<!-- Stats Overview -->
<div id="statsContainer" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7 gap-3 mb-6">
    <div class="col-span-full py-8 text-center bg-white rounded-lg border border-dashed border-gray-300">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-blue-500 border-t-transparent"></div>
        <p class="mt-2 text-sm text-gray-500">Initializing dashboard...</p>
    </div>
</div>

<!-- Search and Filters -->
<div class="card mb-6 shadow-sm border-gray-200">
    <div class="card-body p-4">
        <form id="filterForm" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <div class="sm:col-span-2 lg:col-span-2">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" id="searchInput" name="search"
                        class="block w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Search Site ID, Location, Customer...">
                </div>
            </div>
            <div>
                <select id="cityFilter" name="city"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Cities</option>
                </select>
            </div>
            <div>
                <select id="stateFilter" name="state"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All States</option>
                </select>
            </div>
            <div>
                <select id="statusFilter" name="activity_status"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Status (All)</option>
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
            <div>
                <select id="surveyStatusFilter" name="survey_status"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Survey (All)</option>
                    <option value="pending">Pending</option>
                    <option value="submitted">Submitted</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
        </form>
    </div>
</div>

<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
    <div class="flex items-center gap-3">
        <h2 class="text-xl font-bold text-gray-800">Operational Dashboard</h2>
        <span id="recordCount" class="px-2 py-1 bg-gray-100 text-gray-600 text-xs font-bold rounded-full">0
            Records</span>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <button id="refreshBtn" class="btn btn-secondary !px-3 font-bold text-xs whitespace-nowrap">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                </path>
            </svg>
            Refresh
        </button>
        <button id="exportBtn"
            class="btn btn-success !px-3 font-bold text-xs whitespace-nowrap inline-flex items-center">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
            </svg>
            <span id="exportText">Export</span>
        </button>
        <a href="bulk_upload.php" class="btn btn-info !px-3 font-bold text-xs whitespace-nowrap">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
            </svg>
            Bulk Import
        </a>
        <button onclick="openModal('createSiteModal')"
            class="btn btn-primary !px-3 font-bold text-xs ring-2 ring-blue-100 whitespace-nowrap">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Add Site
        </button>
    </div>
</div>

<!-- Main Sites Table -->
<div class="card shadow-md border-gray-200 overflow-hidden relative" id="tableCard">
    <div id="loadingOverlay" class="loading-overlay hidden">
        <div class="animate-spin rounded-full h-10 w-10 border-4 border-blue-600 border-t-transparent"></div>
    </div>

    <div class="table-container">
        <table class="min-w-full divide-y divide-gray-200" id="sitesTable">
            <thead class="bg-gray-50 border-b border-gray-200">
                <!-- Operation Group Headers (Row 1) -->
                <tr class="text-[11px] font-bold text-gray-500 uppercase tracking-wider text-center">
                    <th colspan="5" class="px-4 py-3 bg-gray-100 border-r border-gray-200 text-gray-700">1. Masters Data
                    </th>
                    <th colspan="2" class="px-4 py-3 bg-green-50 border-r border-gray-200 text-green-800">2. Delegation
                    </th>
                    <th colspan="4" class="px-4 py-3 bg-blue-50 border-r border-gray-200 text-blue-800">3. Survey Status
                    </th>
                    <th colspan="4" class="px-4 py-3 bg-yellow-50 border-r border-gray-200 text-yellow-800">4. Material
                        Part</th>
                    <th colspan="4" class="px-4 py-3 bg-purple-50 text-purple-800">5. Installation</th>
                </tr>
                <!-- Sub-columns (Row 2) -->
                <tr class="text-[10px] font-bold text-gray-600 uppercase tracking-wider">
                    <th class="px-4 py-2 border-r border-gray-200 section-masters">#</th>
                    <th class="px-4 py-2 border-r border-gray-200 section-masters">Actions</th>
                    <th class="px-4 py-2 border-r border-gray-200 section-masters">Site ID / Ticket</th>
                    <th class="px-4 py-2 border-r border-gray-200 section-masters">Store / PO</th>
                    <th class="px-4 py-2 border-r border-gray-200 section-masters">Client / Location</th>
                    <th class="px-4 py-2 border-r border-gray-200 section-delegation">Survey Vendor</th>
                    <th class="px-4 py-2 border-r border-gray-200 section-delegation">Inst Vendor</th>
                    <th class="px-4 py-2 border-r border-gray-200 section-survey">Surveyor</th>
                    <th class="px-4 py-2 border-r border-gray-200 section-survey">Date/Time</th>
                    <th class="px-4 py-2 border-r border-gray-200 section-survey">Status</th>
                    <th class="px-4 py-2 border-r border-gray-200 section-survey">View</th>
                    <th class="px-4 py-2 border-r border-gray-200 section-material">Req #</th>
                    <th class="px-4 py-2 border-r border-gray-200 section-material">Status</th>
                    <th class="px-4 py-2 border-r border-gray-200 section-material">Dispatch</th>
                    <th class="px-4 py-2 border-r border-gray-200 section-material">Delivery</th>
                    <th class="px-4 py-2 border-r border-gray-200 section-installation">Installer</th>
                    <th class="px-4 py-2 border-r border-gray-200 section-installation">Completed</th>
                    <th class="px-4 py-2 border-r border-gray-200 section-installation">Status</th>
                    <th class="px-4 py-2 section-installation">View</th>
                </tr>
            </thead>
            <tbody id="sitesTableBody" class="bg-white divide-y divide-gray-100 italic text-gray-400">
                <tr>
                    <td colspan="19" class="py-10 text-center">Dashboard initialization...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="hidden py-16 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
            </path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No sites found</h3>
        <p class="mt-1 text-sm text-gray-500">Try adjusting your search or filters.</p>
    </div>

    <!-- Pagination Footer -->
    <div class="bg-white px-4 py-3 border-t border-gray-200 flex items-center justify-between sm:px-6">
        <div class="flex-1 flex justify-between sm:hidden" id="paginationMobile"></div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-[11px] text-gray-700" id="paginationSummary">
                    Showing <span class="font-bold">0</span> to <span class="font-bold">0</span> of <span
                        class="font-bold">0</span> results
                </p>
            </div>
            <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination"
                    id="paginationDesktop"></nav>
            </div>
        </div>
    </div>
</div>

<script>
    let currentPage = 1;
    const limit = 20;

    document.addEventListener('DOMContentLoaded', () => {
        loadSites();
        setupEventListeners();
    });

    function setupEventListeners() {
        const filterForm = document.getElementById('filterForm');
        filterForm.querySelectorAll('select').forEach(select => {
            select.addEventListener('change', () => { currentPage = 1; loadSites(); });
        });

        let searchTimeout;
        document.getElementById('searchInput').addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => { currentPage = 1; loadSites(); }, 500);
        });

        document.getElementById('refreshBtn').addEventListener('click', loadSites);
        document.getElementById('exportBtn').addEventListener('click', exportToExcel);
    }

    async function exportToExcel() {
        const btn = document.getElementById('exportBtn');
        const text = document.getElementById('exportText');
        const originalContent = btn.innerHTML;

        // Disable and show loading
        btn.disabled = true;
        btn.classList.add('opacity-75', 'cursor-not-allowed');
        btn.innerHTML = `
        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Exporting...
    `;

        if (window.Swal) {
            Swal.fire({
                title: 'Generating Export',
                text: 'Please wait, we are preparing your data...',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => { Swal.showLoading(); }
            });
        }

        const filterForm = document.getElementById('filterForm');
        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);

        try {
            const response = await fetch(`api/export-sites-advanced.php?${params.toString()}`);
            if (!response.ok) throw new Error('Download failed');

            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');

            // Use current date for filename
            const filename = `sites_export_${new Date().toISOString().slice(0, 19).replace(/[:T]/g, '-')}.xlsx`;

            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            a.remove();

            if (window.Swal) Swal.fire({ icon: 'success', title: 'Export Ready!', text: 'Your file has been downloaded.', timer: 2000, showConfirmButton: false });

        } catch (error) {
            console.error('Export failed:', error);
            if (window.Swal) Swal.fire('Error', 'Failed to generate export file. Please try again.', 'error');
        } finally {
            // Restore button
            btn.disabled = false;
            btn.classList.remove('opacity-75', 'cursor-not-allowed');
            btn.innerHTML = originalContent;
        }
    }

    async function loadSites() {
        console.log('Loading sites...');
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.classList.remove('hidden');
            overlay.style.display = 'flex';
        }

        const filterForm = document.getElementById('filterForm');
        const formData = filterForm ? new FormData(filterForm) : new FormData();
        const params = new URLSearchParams(formData);
        params.set('page', currentPage);
        params.set('limit', limit);

        try {
            const response = await fetch(`api/get-sites-advanced.php?${params.toString()}`);
            const result = await response.json();

            if (result.success) {
                console.log('Sites loaded successfully', result.sites.length);
                renderTable(result.sites || []);
                renderStats(result.stats || {});
                renderPagination(result.pagination || {});
            } else {
                console.error('API Error:', result.message);
                if (window.Swal) Swal.fire('Error', result.message, 'error');
            }
        } catch (error) {
            console.error('Fetch error:', error);
        } finally {
            console.log('Hiding overlay');
            if (overlay) {
                overlay.classList.add('hidden');
                overlay.style.display = 'none'; // Force hide
            }
        }
    }

    function formatDateTime(dateStr) {
        if (!dateStr) return '-';
        return new Date(dateStr).toLocaleString('en-IN', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
    }

    function getStatusConfig(status) {
        const configs = {
            'Pending': { class: 'bg-yellow-100 text-yellow-700' },
            'Submitted': { class: 'bg-blue-100 text-blue-700' },
            'Approved': { class: 'bg-green-100 text-green-700' },
            'Rejected': { class: 'bg-red-100 text-red-700' },
            'Completed': { class: 'bg-purple-100 text-purple-700' }
        };
        return configs[status] || { class: 'bg-gray-100 text-gray-600' };
    }

    function renderTable(sites) {
        try {
            const tbody = document.getElementById('sitesTableBody');
            const emptyState = document.getElementById('emptyState');
            if (!tbody) return;

            if (!sites || sites.length === 0) {
                tbody.innerHTML = '';
                if (emptyState) emptyState.classList.remove('hidden');
                return;
            }

            if (emptyState) emptyState.classList.add('hidden');
            const sNoStart = (currentPage - 1) * limit + 1;

            tbody.innerHTML = sites.map((site, index) => {
                return `
                <tr class="hover:bg-gray-50 transition-colors text-xs">
                    <td class="px-4 py-3 font-medium text-gray-400 text-center border-r border-gray-100 section-masters">${sNoStart + index}</td>
                    <td class="px-4 py-3 border-r border-gray-100 section-masters">
                        <div class="flex items-center gap-1">
                            <button onclick="viewSite(${site.id})" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="View Site"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
                            <button onclick="editSite(${site.id})" class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors" title="Edit Site"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                            <button onclick="conductMaterials(${site.id}, ${site.survey_id})" class="p-1.5 text-orange-600 hover:bg-orange-50 rounded-lg transition-colors" title="Material Request"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></button>
                            <button onclick="${site.survey_id ? `viewSurvey(${site.survey_id})` : `conductSurvey(${site.id})`}" 
                                    class="p-1.5 ${site.survey_id ? 'text-blue-500' : 'text-emerald-600'} hover:bg-gray-50 rounded-lg transition-colors ${(!site.survey_id && !site.delegated_vendor_name) ? 'opacity-30 pointer-events-none' : ''}" 
                                    title="${site.survey_id ? 'View Survey' : 'Conduct Survey'}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </button>
                            <button onclick="deleteSite(${site.id})" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete Site"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                        </div>
                    </td>
                    <td class="px-4 py-3 border-r border-gray-100 section-masters">
                        <div class="font-bold text-gray-900">${site.site_id}</div>
                        <div class="text-[9px] text-gray-500">${site.site_ticket_id || '-'}</div>
                    </td>
                    <td class="px-4 py-3 border-r border-gray-100 section-masters">
                        <div class="font-semibold text-gray-800">${site.store_id || '-'}</div>
                        <div class="text-[9px] text-gray-500">${site.po_number || '-'}</div>
                    </td>
                    <td class="px-4 py-3 border-r border-gray-100 section-masters">
                        <div class="font-semibold text-gray-800 truncate max-w-[150px]">${site.customer || '-'}</div>
                        <div class="text-[10px] text-gray-500">${site.city || '-'}, ${site.state || '-'}</div>
                    </td>
                    <td class="px-4 py-3 border-r border-gray-100 section-delegation">
                        <div class="flex items-center justify-between gap-2">
                            <div class="text-[10px] font-bold ${site.delegated_vendor_name ? 'text-green-700' : 'text-gray-400'}">${site.delegated_vendor_name || 'Not Assigned'}</div>
                            ${(site.actual_survey_status || '').toLowerCase() !== 'approved' ? `
                                <button onclick="delegateSurvey(${site.id})" class="p-1 text-indigo-600 hover:bg-indigo-50 rounded transition-colors" title="Delegate Survey">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                </button>
                            ` : ''}
                        </div>
                    </td>
                    <td class="px-4 py-3 border-r border-gray-100 section-delegation">
                        <div class="flex items-center justify-between gap-2">
                            <div class="text-[10px] font-bold ${site.installation_vendor_name ? 'text-indigo-700' : 'text-gray-400'}">${site.installation_vendor_name || 'Not Assigned'}</div>
                            ${((site.actual_survey_status || '').toLowerCase() === 'approved' && (site.installation_status_label || '').toLowerCase() !== 'completed') ? `
                                <button onclick="delegateInstallation(${site.id}, ${site.survey_id})" class="p-1 text-purple-600 hover:bg-purple-50 rounded transition-colors" title="Delegate Installation">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                </button>
                            ` : ''}
                        </div>
                    </td>
                    <td class="px-4 py-3 border-r border-gray-100 section-survey">
                        <div class="font-medium text-gray-800">${site.surveyor_name || '-'}</div>
                    </td>
                    <td class="px-4 py-3 border-r border-gray-100 section-survey">
                        <div class="text-[10px] text-gray-500">${formatDateTime(site.survey_submitted_date)}</div>
                    </td>
                    <td class="px-4 py-3 border-r border-gray-100 section-survey">
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase ${getStatusConfig(site.actual_survey_status).class}">
                            ${site.actual_survey_status || 'Pending'}
                        </span>
                    </td>
                    <td class="px-4 py-3 border-r border-gray-100 section-survey">
                        ${site.has_survey_submitted ? `
                            <a href="../../shared/view-survey2.php?id=${site.survey_id}" target="_blank" class="text-blue-600 hover:text-blue-800 font-bold text-[10px] underline decoration-dotted">View</a>
                        ` : '<span class="text-gray-300">-</span>'}
                    </td>
                    <td class="px-4 py-3 border-r border-gray-100 section-material">
                        <div class="text-[10px] font-mono font-bold text-gray-600">${site.material_request_number || '-'}</div>
                    </td>
                    <td class="px-4 py-3 border-r border-gray-100 section-material">
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase ${getStatusConfig(site.material_status).class}">
                            ${site.material_status || 'Pending'}
                        </span>
                    </td>
                    <td class="px-4 py-3 border-r border-gray-100 section-material">
                         <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase ${site.dispatch_status === 'Dispatched' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400'}">
                            ${site.dispatch_status || 'Pending'}
                        </span>
                    </td>
                    <td class="px-4 py-3 border-r border-gray-100 section-material text-center">
                        <div class="text-[10px] font-bold ${site.delivery_status === 'Received' ? 'text-green-700' : 'text-amber-600'}">
                            ${site.delivery_status || '-'}
                        </div>
                    </td>
                    <td class="px-4 py-3 border-r border-gray-100 section-installation">
                        <div class="text-[10px] font-semibold text-gray-800">${site.installer_name || '-'}</div>
                    </td>
                    <td class="px-4 py-3 border-r border-gray-100 section-installation">
                        <div class="text-[10px] text-gray-500">${formatDateTime(site.installation_completed_time)}</div>
                    </td>
                    <td class="px-4 py-3 border-r border-gray-100 section-installation">
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase ${getStatusConfig(site.installation_status_label).class}">
                            ${site.installation_status_label || 'Pending'}
                        </span>
                    </td>
                    <td class="px-4 py-3 section-installation text-center">
                        ${site.installation_id ? `
                            <a href="../../shared/view-installation.php?id=${site.installation_id}" target="_blank" class="text-purple-600 hover:text-purple-800 font-bold text-[10px] underline decoration-dotted">View</a>
                        ` : '<span class="text-gray-300">-</span>'}
                    </td>
                </tr>
            `;
            }).join('');
        } catch (e) { console.error('Error in renderTable:', e); }
    }

    function renderStats(stats) {
        try {
            const container = document.getElementById('statsContainer');
            if (!container) return;

            const sections = [
                { label: 'Total Sites', count: stats.total_sites || 0, color: 'indigo', icon: 'M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z' },
                { label: 'Delegated', count: stats.delegation_active || 0, color: 'blue', icon: 'M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z' },
                { label: 'Pending Del.', count: stats.delegation_pending || 0, color: 'orange', icon: 'M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z' },
                { label: 'Approved', count: stats.survey_approved || 0, color: 'green', icon: 'M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z' },
                { label: 'Pending Surv.', count: stats.survey_pending || 0, color: 'yellow', icon: 'M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z' },
                { label: 'Rejected', count: stats.survey_rejected || 0, color: 'red', icon: 'M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z' },
                { label: 'Inst. Done', count: stats.installation_done || 0, color: 'purple', icon: 'M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z' }
            ];

            container.innerHTML = sections.map(s => `
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow group">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-10 h-10 rounded-lg bg-${s.color}-100 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5 text-${s.color}-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="${s.icon}" clip-rule="evenodd"></path></svg>
                    </div>
                    <div class="text-2xl font-bold text-gray-900">${parseInt(s.count || 0).toLocaleString()}</div>
                </div>
                <div class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">${s.label}</div>
            </div>
        `).join('');
        } catch (e) { console.error('Error in renderStats:', e); }
    }

    function renderPagination(pg) {
        try {
            if (!pg || typeof pg.total_records === 'undefined') return;

            const summary = document.getElementById('paginationSummary');
            if (summary) {
                summary.innerHTML = `Showing <span class="font-bold">${((pg.current_page - 1) * pg.limit) + 1}</span> to <span class="font-bold">${Math.min(pg.current_page * pg.limit, pg.total_records)}</span> of <span class="font-bold">${pg.total_records}</span> results`;
            }

            const recordCount = document.getElementById('recordCount');
            if (recordCount) recordCount.innerText = `${pg.total_records} Records`;

            const nav = document.getElementById('paginationDesktop');
            if (!nav) return;

            let html = '';
            html += `<button onclick="goToPage(1)" ${pg.current_page === 1 ? 'disabled' : ''} class="px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm text-gray-500 hover:bg-gray-50 disabled:opacity-50">First</button>`;

            for (let i = Math.max(1, pg.current_page - 2); i <= Math.min(pg.total_pages, pg.current_page + 2); i++) {
                html += `<button onclick="goToPage(${i})" class="px-4 py-2 border text-sm ${i === pg.current_page ? 'bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'}">${i}</button>`;
            }

            html += `<button onclick="goToPage(${pg.total_pages})" ${pg.current_page === pg.total_pages ? 'disabled' : ''} class="px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm text-gray-500 hover:bg-gray-50 disabled:opacity-50">Last</button>`;
            nav.innerHTML = html;
        } catch (e) { console.error('Error in renderPagination:', e); }
    }

    function goToPage(p) { currentPage = p; loadSites(); }

    function delegateSurvey(id) {
        window.location.href = `delegate.php?id=${id}`;
    }

    function delegateInstallation(siteId, surveyId) {
        if (!surveyId || surveyId == 0) {
            if (window.Swal) Swal.fire('Error', 'Site survey must be approved before installation delegation.', 'error');
            return;
        }
        window.location.href = `delegate-installation.php?site_id=${siteId}&survey_id=${surveyId}`;
    }

    function conductSurvey(id) {
        window.location.href = `../../admin/site-survey2.php?site_id=${id}`;
    }

    function conductMaterials(siteId, surveyId) {
        if (!surveyId || surveyId == 0) {
            if (window.Swal) Swal.fire('Error', 'Please complete the survey first before requesting materials.', 'error');
            return;
        }
        window.location.href = `../../admin/material-request.php?site_id=${siteId}&survey_id=${surveyId}`;
    }

    function viewSurvey(surveyId) {
        if (!surveyId) return;
        window.open(`../../shared/view-survey2.php?id=${surveyId}`, '_blank');
    }

    function viewSite(id) {
        window.location.href = `view.php?id=${id}`;
    }

    async function deleteSite(id) {
        if (!window.Swal) return;

        const result = await Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        });

        if (result.isConfirmed) {
            try {
                const formData = new FormData();
                formData.append('id', id);
                const response = await fetch('delete.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                if (data.success) {
                    Swal.fire('Deleted!', 'Site has been deleted.', 'success');
                    loadSites();
                } else {
                    Swal.fire('Error!', data.message || 'Failed to delete site.', 'error');
                }
            } catch (error) {
                Swal.fire('Error!', 'Technical error occurred.', 'error');
            }
        }
    }
</script>

<?php
require_once 'modals.php';
$content = ob_get_clean();
require_once __DIR__ . '/../../includes/admin_layout.php';
?>