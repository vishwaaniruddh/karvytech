<?php
require_once __DIR__ . '/../../config/auth.php';
Auth::requireRole(ADMIN_ROLE);

$title = 'Users Management';
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

    .stats-card.card-total::after {
        background: #6366f1;
    }

    .stats-card.card-active::after {
        background: #10b981;
    }

    .stats-card.card-disabled::after {
        background: #ef4444;
    }

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

    .user-name-cell {
        line-height: 1.1;
        font-weight: 600;
        color: #111827;
    }
</style>

<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Users Management</h1>
        <p class="text-[13px] text-gray-500 mt-0.5">Create, edit, and manage user accounts, roles, and access
            permissions for your system.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="bulk_upload.php"
            class="inline-flex items-center px-3.5 py-2 text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-xl shadow-sm hover:bg-gray-50 transition-all active:scale-95">
            <svg class="w-3.5 h-3.5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
            </svg>
            Bulk Import
        </a>
        <button onclick="exportUsersData()"
            class="inline-flex items-center px-3.5 py-2 text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-xl shadow-sm hover:bg-gray-50 transition-all active:scale-95">
            <svg class="w-3.5 h-3.5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                </path>
            </svg>
            Export
        </button>
        <button onclick="resetCreateUserForm(); openModal('createUserModal')"
            class="inline-flex items-center px-4 py-2 text-xs font-bold text-white bg-blue-600 rounded-xl shadow-md shadow-blue-100 hover:bg-blue-700 transition-all active:scale-95 focus:ring-4 focus:ring-blue-50 focus:border-blue-300">
            <svg class="w-3.5 h-3.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6">
                </path>
            </svg>
            Create User
        </button>
    </div>
</div>

<!-- Statistics Cards Row (Improved Design) -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8" id="statsGrid">
    <div class="stats-card card-total active" onclick="filterByStatus('')" id="stat-total">
        <div style="display: flex; align-items: flex-start; justify-content: space-between;">
            <div style="flex: 1;">
                <div class="card-header">Total Users</div>
                <div class="card-number" id="count-total">...</div>
                <div class="card-subtitle">System Records</div>
            </div>
            <div class="card-icon" style="background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);">
                <svg style="color: #4f46e5;" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                        clip-rule="evenodd"></path>
                </svg>
            </div>
        </div>
    </div>
    <div class="stats-card card-active" onclick="filterByStatus('active')" id="stat-active">
        <div style="display: flex; align-items: flex-start; justify-content: space-between;">
            <div style="flex: 1;">
                <div class="card-header">Active Users</div>
                <div class="card-number" id="count-active">...</div>
                <div class="card-subtitle">Currently Online</div>
            </div>
            <div class="card-icon" style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);">
                <svg style="color: #059669;" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd"></path>
                </svg>
            </div>
        </div>
    </div>
    <div class="stats-card card-disabled" onclick="filterByStatus('disabled')" id="stat-disabled">
        <div style="display: flex; align-items: flex-start; justify-content: space-between;">
            <div style="flex: 1;">
                <div class="card-header">Disabled Users</div>
                <div class="card-number" id="count-inactive">...</div>
                <div class="card-subtitle">Access Revoked</div>
            </div>
            <div class="card-icon" style="background: linear-gradient(135deg, #fef2f2 0%, #fecaca 100%);">
                <svg style="color: #dc2626;" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                        clip-rule="evenodd"></path>
                </svg>
            </div>
        </div>
    </div>
    <div class="stats-card card-admins" id="stat-admins">
        <div style="display: flex; align-items: flex-start; justify-content: space-between;">
            <div style="flex: 1;">
                <div class="card-header">Admin Users</div>
                <div class="card-number" id="count-admins">...</div>
                <div class="card-subtitle">Privileged Access</div>
            </div>
            <div class="card-icon" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);">
                <svg style="color: #2563eb;" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M2.166 4.9L10 1.55l7.834 3.35a1 1 0 01.666.945V10c0 5.825-3.824 10.29-9 11.622C4.324 20.29.5 15.825.5 10V5.845a1 1 0 01.666-.945zM10 8a1 1 0 00-1 1v5a1 1 0 102 0V9a1 1 0 00-1-1z"
                        clip-rule="evenodd"></path>
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
        <input type="text" id="searchInput" placeholder="Search accounts..."
            class="block w-full pl-10 pr-4 py-2 bg-white border border-gray-100 rounded-xl text-sm focus:ring-2 focus:ring-blue-50 focus:border-blue-200 transition-all outline-none shadow-sm"
            onkeyup="debounceFilter()">
    </div>
    <div class="flex items-center gap-2">
        <select id="roleFilter"
            class="appearance-none px-4 py-2 bg-white border border-gray-100 rounded-xl text-sm focus:ring-2 focus:ring-blue-50 focus:border-blue-200 shadow-sm outline-none cursor-pointer min-w-[130px]"
            onchange="applyFilters()">
            <option value="">All Roles</option>
            <!-- Dynamic roles will be injected here -->
        </select>
        <select id="statusFilter"
            class="appearance-none px-4 py-2 bg-white border border-gray-100 rounded-xl text-sm focus:ring-2 focus:ring-blue-50 focus:border-blue-200 shadow-sm outline-none cursor-pointer min-w-[130px]"
            onchange="applyFilters()">
            <option value="">All Status</option>
            <option value="active">Active Only</option>
            <option value="disabled">Disabled Only</option>
        </select>
    </div>
</div>

<!-- Users Table -->
<div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden min-h-[400px] relative">
    <div id="tableLoading" class="absolute inset-0 bg-white/80 z-10 flex items-center justify-center hidden">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/50 border-b border-gray-100">
                    <th
                        class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center w-16">
                        #</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">User Profile
                    </th>
                    <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Contact Details
                    </th>
                    <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">System Role</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Access Status
                    </th>
                    <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Registration
                    </th>
                    <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-right">
                        Management</th>
                </tr>
            </thead>
            <tbody id="usersTableBody" class="bg-white divide-y divide-gray-50">
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

<!-- Simple Modals (Generic Container) -->
<div id="createUserModal" class="modal">
    <div class="modal-content max-w-xl rounded-2xl p-6">
        <div class="flex justify-between items-center mb-6 border-b pb-4">
            <h3 class="font-bold text-gray-900">New Account</h3>
            <button onclick="closeModal('createUserModal')" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        <form id="createUserForm" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <input type="text" name="username" placeholder="Username"
                    class="w-full px-4 py-2 bg-gray-50 border-none rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-100"
                    required>
                <input type="email" name="email" placeholder="Email"
                    class="w-full px-4 py-2 bg-gray-50 border-none rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-100"
                    required>
                <input type="tel" name="phone" placeholder="Phone"
                    class="w-full px-4 py-2 bg-gray-50 border-none rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-100">
                <input type="password" name="password" placeholder="Password"
                    class="w-full px-4 py-2 bg-gray-50 border-none rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-100"
                    required>
                <select name="role" id="create_role"
                    class="w-full px-4 py-2 bg-gray-50 border-none rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-100"
                    onchange="toggleVendorField(this.value)" required>
                    <option value="">Role</option>
                    <!-- Roles injected here -->
                </select>
                <select name="status"
                    class="w-full px-4 py-2 bg-gray-50 border-none rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-100">
                    <option value="active">Active</option>
                    <option value="disabled">Disabled</option>
                </select>
            </div>
            <div id="vendor_field" class="hidden">
                <select id="vendor_id" name="vendor_id"
                    class="w-full px-4 py-2 bg-gray-50 border-none rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-100">
                    <option value="">Assign Vendor</option>
                </select>
            </div>
            <button type="submit"
                class="w-full py-2.5 bg-blue-600 text-white font-bold rounded-xl shadow-lg shadow-blue-100">Create
                Account</button>
        </form>
    </div>
</div>

<div id="editUserModal" class="modal">
    <div class="modal-content max-w-xl rounded-2xl p-6">
        <div class="flex justify-between items-center mb-6 border-b pb-4">
            <h3 class="font-bold text-gray-900">Edit Account</h3>
            <button onclick="closeModal('editUserModal')" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        <form id="editUserForm" class="space-y-4">
            <input type="hidden" id="edit_user_id">
            <div class="grid grid-cols-2 gap-4">
                <input type="text" id="edit_username" name="username" placeholder="Username"
                    class="w-full px-4 py-2 bg-gray-50 border-none rounded-xl text-sm outline-none" required>
                <input type="email" id="edit_email" name="email" placeholder="Email"
                    class="w-full px-4 py-2 bg-gray-50 border-none rounded-xl text-sm outline-none" required>
                <input type="tel" id="edit_phone" name="phone" placeholder="Phone"
                    class="w-full px-4 py-2 bg-gray-50 border-none rounded-xl text-sm outline-none">
                <input type="password" id="edit_password" name="password" placeholder="New Password (Optional)"
                    class="w-full px-4 py-2 bg-gray-50 border-none rounded-xl text-sm outline-none">
                <select id="edit_role" name="role"
                    class="w-full px-4 py-2 bg-gray-50 border-none rounded-xl text-sm outline-none"
                    onchange="toggleEditVendorField(this.value)" required>
                    <option value="">Select Role</option>
                    <!-- Roles injected here -->
                </select>
                <select id="edit_status" name="status"
                    class="w-full px-4 py-2 bg-gray-50 border-none rounded-xl text-sm outline-none">
                    <option value="active">Active</option>
                    <option value="disabled">Disabled</option>
                </select>
            </div>
            <div id="edit_vendor_field" class="hidden">
                <select id="edit_vendor_id" name="vendor_id"
                    class="w-full px-4 py-2 bg-gray-50 border-none rounded-xl text-sm outline-none">
                    <option value="">Assign Vendor</option>
                </select>
            </div>
            <button type="submit" class="w-full py-2.5 bg-emerald-600 text-white font-bold rounded-xl">Update
                Account</button>
        </form>
    </div>
</div>

<div id="viewUserModal" class="modal">
    <div class="modal-content max-w-md rounded-2xl p-8 bg-white shadow-2xl">
        <div class="flex items-start justify-between mb-8">
            <div class="flex items-center gap-4">
                <div id="view_avatar_circle"
                    class="w-16 h-16 rounded-2xl bg-blue-50 flex items-center justify-center text-2xl font-black text-blue-600 border border-blue-100/50">
                </div>
                <div>
                    <h3 id="view_fullname" class="text-xl font-bold text-gray-900 leading-tight">---</h3>
                    <div class="flex items-center gap-2 mt-1">
                        <span id="view_role_badge"
                            class="text-[10px] font-bold text-blue-600 uppercase tracking-wider">---</span>
                        <span class="text-gray-300">•</span>
                        <div id="view_status_badge_container" class="flex items-center">
                            <div id="view_status_dot" class="w-1.5 h-1.5 rounded-full mr-1.5"></div>
                            <span id="view_status_badge"
                                class="text-[10px] font-bold uppercase tracking-wider">---</span>
                        </div>
                    </div>
                </div>
            </div>
            <button onclick="closeModal('viewUserModal')"
                class="text-gray-400 hover:text-gray-600 transition-colors p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>

        <div class="space-y-6">
            <div class="grid grid-cols-1 gap-5">
                <div class="flex items-start gap-3">
                    <svg class="w-4 h-4 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                        </path>
                    </svg>
                    <div class="min-w-0">
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-0.5">Contact Email</p>
                        <p id="view_email" class="text-sm font-medium text-gray-700 truncate"></p>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <svg class="w-4 h-4 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                        </path>
                    </svg>
                    <div>
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-0.5">Phone Number</p>
                        <p id="view_phone" class="text-sm font-medium text-gray-700"></p>
                    </div>
                </div>

                <div id="view_vendor_container" class="flex items-start gap-3 hidden">
                    <svg class="w-4 h-4 text-emerald-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                        </path>
                    </svg>
                    <div>
                        <p class="text-[9px] font-bold text-emerald-500 uppercase tracking-widest mb-0.5">Vendor Partner
                        </p>
                        <p id="view_vendor_name" class="text-sm font-bold text-emerald-700"></p>
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-gray-100">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-1">Created</p>
                        <p id="view_created_at" class="text-[11px] text-gray-600 font-medium"></p>
                    </div>
                    <div>
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-1">Updated</p>
                        <p id="view_updated_at" class="text-[11px] text-gray-600 font-medium"></p>
                    </div>
                </div>
            </div>
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
        const role = document.getElementById('roleFilter').value;
        const status = document.getElementById('statusFilter').value;

        const params = new URLSearchParams(window.location.search);
        params.set('page', page);
        if (search) params.set('search', search); else params.delete('search');
        if (role) params.set('role', role); else params.delete('role');
        if (status) params.set('status', status); else params.delete('status');

        window.history.pushState({}, '', '?' + params.toString());
        fetchUsers();
    }

    function filterByStatus(status) {
        document.getElementById('statusFilter').value = status;
        applyFilters(1);
    }

    async function fetchUsers() {
        const loading = document.getElementById('tableLoading');
        loading.classList.remove('hidden');

        try {
            const params = new URLSearchParams(window.location.search);
            const response = await fetch(`../../api/users.php?${params.toString()}`);
            const result = await response.json();

            if (result.success) {
                currentData = result.data;
                renderStats(result.data.stats);
                renderTable(result.data.users);
                renderPagination(result.data.pagination);
            }
        } catch (error) {
            console.error('Fetch error:', error);
            alert('Failed to load users data.');
        } finally {
            loading.classList.add('hidden');
        }
    }

    function renderStats(stats) {
        document.getElementById('count-total').textContent = stats.total.toLocaleString();
        document.getElementById('count-active').textContent = stats.active.toLocaleString();
        document.getElementById('count-inactive').textContent = stats.disabled.toLocaleString();
        document.getElementById('count-admins').textContent = stats.admins.toLocaleString();

        // Active card state
        const params = new URLSearchParams(window.location.search);
        const status = params.get('status');

        document.querySelectorAll('.stats-card').forEach(c => c.classList.remove('active'));
        if (status === 'active') document.getElementById('stat-active').classList.add('active');
        else if (status === 'disabled') document.getElementById('stat-disabled').classList.add('active');
        else if (!status || status === '') document.getElementById('stat-total').classList.add('active');
    }

    function renderTable(users) {
        const tbody = document.getElementById('usersTableBody');
        tbody.innerHTML = '';

        if (users.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                        <div class="flex flex-col items-center">
                            <svg class="w-12 h-12 text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            <p class="text-sm font-medium">No users found matching your filters.</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        const params = new URLSearchParams(window.location.search);
        const page = parseInt(params.get('page')) || 1;
        const limit = 20;

        users.forEach((user, index) => {
            const initial = user.username.charAt(0).toUpperCase();
            const colors = ['bg-blue-100 text-blue-600', 'bg-emerald-100 text-emerald-600', 'bg-purple-100 text-purple-600', 'bg-orange-100 text-orange-600'];
            const colorClass = colors[user.username.length % colors.length];

            const role = (user.rbac_role || user.role || 'user').toLowerCase();
            const roleClass = role.includes('admin') ? 'bg-blue-50 text-blue-600 border border-blue-100/50' :
                (role.includes('vendor') || role.includes('contractor') ? 'bg-amber-50 text-amber-600 border border-amber-100/50' : 'bg-gray-100 text-gray-600');

            const row = `
                <tr class="hover:bg-gray-50/50 transition-colors group">
                    <td class="px-6 py-3 whitespace-nowrap text-xs font-bold text-gray-400 text-center">
                        ${(page - 1) * limit + index + 1}
                    </td>
                    <td class="px-6 py-3 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-lg ${colorClass} flex items-center justify-center text-xs font-bold mr-3 shadow-sm group-hover:scale-110 transition-transform">
                                ${initial}
                            </div>
                            <div>
                                <div class="text-sm user-name-cell text-gray-900 group-hover:text-blue-600 transition-colors cursor-pointer" onclick="viewUser(${user.id})">
                                    ${user.username}
                                </div>
                                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">UID: #USR-${String(user.id).padStart(4, '0')}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-3 whitespace-nowrap">
                        <div class="flex flex-col gap-0.5">
                            <div class="flex items-center text-[11px] text-gray-500 font-medium">
                                <svg class="w-3.5 h-3.5 mr-1.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                ${user.email}
                            </div>
                            <div class="flex items-center text-[11px] text-gray-500 font-medium">
                                <svg class="w-3.5 h-3.5 mr-1.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                ${user.phone || '---'}
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-3 whitespace-nowrap">
                        <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg ${roleClass}">
                            ${role.toUpperCase()}
                        </span>
                    </td>
                    <td class="px-6 py-3 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="w-1.5 h-1.5 rounded-full mr-2 ${user.status === 'active' ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500'}"></div>
                            <span class="text-[11px] font-bold ${user.status === 'active' ? 'text-emerald-600' : 'text-rose-600'}">
                                ${user.status.toUpperCase()}
                            </span>
                        </div>
                    </td>
                    <td class="px-6 py-3 whitespace-nowrap">
                        <div class="text-[11px] font-bold text-gray-900">${new Date(user.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</div>
                    </td>
                    <td class="px-6 py-3 whitespace-nowrap text-right">
                        <div class="flex items-center justify-end gap-1">
                            <a href="assign-role.php?user_id=${user.id}" class="p-1.5 text-gray-400 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition-all" title="Assign Role/Permissions">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            </a>
                            <button onclick="viewUser(${user.id})" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all" title="View Details">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </button>
                            <button onclick="editUser(${user.id})" class="p-1.5 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-all" title="Edit Profile">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </button>
                            <button onclick="toggleUserStatus(${user.id})" class="p-1.5 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-all" title="Toggle Status">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 12.728l-3.536-3.536M12 3v4m0 10v4M3 12h4m10 0h4"></path></svg>
                            </button>
                            <button onclick="deleteUser(${user.id})" class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all" title="Delete">
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

        // Helper to add button
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

        const firstIcon = '<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M15.707 15.707a1 1 0 01-1.414 0l-5-5a1 1 0 010-1.414l5-5a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 010 1.414zm-6 0a1 1 0 01-1.414 0l-5-5a1 1 0 010-1.414l5-5a1 1 0 011.414 1.414L5.414 10l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" /></svg>';
        const prevIcon = '<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>';
        const nextIcon = '<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>';
        const lastIcon = '<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10.293 15.707a1 1 0 010-1.414L14.586 10l-4.293-4.293a1 1 0 111.414-1.414l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0z" clip-rule="evenodd" /><path fill-rule="evenodd" d="M4.293 15.707a1 1 0 010-1.414L8.586 10 4.293 5.707a1 1 0 011.414-1.414l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>';

        // First & Prev
        addBtn('First', 1, false, current === 1, firstIcon);
        addBtn('Previous', current - 1, false, current === 1, prevIcon);

        // Smart Range
        const range = 2;
        if (current > range + 2) {
            addBtn('1', 1);
            if (current > range + 3) {
                const dot = document.createElement('span');
                dot.className = 'relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700';
                dot.textContent = '...';
                nav.appendChild(dot);
            }
        }

        for (let i = max(1, current - range); i <= min(total, current + range); i++) {
            addBtn(i.toString(), i, i === current);
        }

        if (current < total - range - 1) {
            if (current < total - range - 2) {
                const dot = document.createElement('span');
                dot.className = 'relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700';
                dot.textContent = '...';
                nav.appendChild(dot);
            }
            addBtn(total.toString(), total);
        }

        // Next & Last
        addBtn('Next', current + 1, false, current === total, nextIcon);
        addBtn('Last', total, false, current === total, lastIcon);
    }

    function max(a, b) { return a > b ? a : b; }
    function min(a, b) { return a < b ? a : b; }

    // Modal Operations
    function resetCreateUserForm() {
        document.getElementById('createUserForm').reset();
        document.getElementById('vendor_field').className = 'hidden';
    }

    document.getElementById('createUserForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        const response = await fetch('../../api/users.php?action=create', { method: 'POST', body: formData });
        const result = await response.json();
        if (result.success) {
            closeModal('createUserModal');
            showToast('User account created successfully!', 'success');
            fetchUsers();
        } else {
            showToast(result.message, 'error');
        }
    });

    document.getElementById('editUserForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const id = document.getElementById('edit_user_id').value;
        const formData = new FormData(this);
        const response = await fetch(`../../api/users.php?action=update&id=${id}`, { method: 'POST', body: formData });
        const result = await response.json();
        if (result.success) {
            closeModal('editUserModal');
            showToast('User account updated successfully!', 'success');
            fetchUsers();
        } else {
            showToast(result.message, 'error');
        }
    });

    async function viewUser(id) {
        const res = await fetch(`../../api/users.php?action=view&id=${id}`);
        const data = await res.json();
        if (data.success) {
            const u = data.user;
            document.getElementById('view_fullname').textContent = u.username;
            document.getElementById('view_role_badge').textContent = (u.rbac_role || u.role).toUpperCase();
            document.getElementById('view_email').textContent = u.email;
            document.getElementById('view_phone').textContent = u.phone || 'Not Provided';

            // Status
            const statusBadge = document.getElementById('view_status_badge');
            const statusDot = document.getElementById('view_status_dot');
            statusBadge.textContent = u.status.toUpperCase();
            statusBadge.className = `text-[10px] font-bold uppercase tracking-wider ${u.status === 'active' ? 'text-emerald-600' : 'text-rose-600'}`;
            statusDot.className = `w-1.5 h-1.5 rounded-full mr-1.5 ${u.status === 'active' ? 'bg-emerald-500' : 'bg-rose-500'}`;

            // Dates
            const dateOptions = { month: 'short', day: 'numeric', year: 'numeric' };
            document.getElementById('view_created_at').textContent = u.created_at ? new Date(u.created_at).toLocaleDateString('en-US', dateOptions) : '---';
            document.getElementById('view_updated_at').textContent = u.updated_at ? new Date(u.updated_at).toLocaleDateString('en-US', dateOptions) : '---';

            document.getElementById('view_avatar_circle').textContent = u.username.charAt(0).toUpperCase();

            const vc = document.getElementById('view_vendor_container');
            if (u.vendor_name) {
                document.getElementById('view_vendor_name').textContent = u.vendor_name;
                vc.classList.remove('hidden');
            } else {
                vc.classList.add('hidden');
            }
            openModal('viewUserModal');
        } else {
            showToast(data.message || 'Failed to fetch user details', 'error');
        }
    }

    async function editUser(id) {
        const res = await fetch(`../../api/users.php?action=edit&id=${id}`);
        const data = await res.json();
        if (data.success) {
            const u = data.user;
            document.getElementById('edit_user_id').value = u.id;
            document.getElementById('edit_username').value = u.username;
            document.getElementById('edit_email').value = u.email;
            document.getElementById('edit_phone').value = u.phone || '';
            document.getElementById('edit_role').value = u.role;
            document.getElementById('edit_status').value = u.status;
            toggleEditVendorField(u.role);
            if (u.role === 'vendor' || u.role === 'contractor') {
                setTimeout(() => document.getElementById('edit_vendor_id').value = u.vendor_id || '', 300);
            }
            openModal('editUserModal');
        } else {
            showToast(data.message || 'Failed to fetch user data', 'error');
        }
    }

    async function toggleUserStatus(id) {
        const confirmed = await showConfirm('Toggle Status', 'Are you sure you want to change this user\'s access status?', {
            confirmType: 'primary',
            confirmText: 'Yes, Change Status'
        });

        if (confirmed) {
            const response = await fetch(`../../api/users.php?action=toggle-status&id=${id}`, { method: 'POST' });
            const result = await response.json();
            if (result.success) {
                showToast('User status updated successfully!', 'info');
                fetchUsers();
            } else {
                showToast(result.message, 'error');
            }
        }
    }

    async function deleteUser(id) {
        const confirmed = await showConfirm('Delete User', 'Are you sure you want to permanently delete this user account? This action cannot be reversed.', {
            confirmType: 'danger',
            confirmText: 'Yes, Delete Account'
        });

        if (confirmed) {
            const response = await fetch(`../../api/users.php?action=delete&id=${id}`, { method: 'POST' });
            const result = await response.json();
            if (result.success) {
                showToast('User account deleted forever.', 'success');
                fetchUsers();
            } else {
                showToast(result.message, 'error');
            }
        }
    }

    async function loadRoles() {
        try {
            const res = await fetch('../../api/rbac/roles.php?action=list');
            const data = await res.json();
            if (data.success) {
                const roles = data.roles;
                const filters = document.getElementById('roleFilter');
                const createSelect = document.getElementById('create_role');
                const editSelect = document.getElementById('edit_role');
                
                // Clear existing dynamic roles (keep the first option)
                while(filters.options.length > 1) filters.remove(1);
                while(createSelect.options.length > 1) createSelect.remove(1);
                while(editSelect.options.length > 1) editSelect.remove(1);

                roles.forEach(role => {
                    const opt1 = new Option(role.display_name, role.name);
                    const opt2 = new Option(role.display_name, role.name);
                    const opt3 = new Option(role.display_name, role.name);
                    filters.add(opt1);
                    createSelect.add(opt2);
                    editSelect.add(opt3);
                });
            }
        } catch (e) {
            console.error('Error loading roles:', e);
        }
    }

    function toggleVendorField(role) {
        const f = document.getElementById('vendor_field');
        if (role === 'vendor' || role === 'contractor') { f.className = 'block'; loadVendors('vendor_id'); }
        else f.className = 'hidden';
    }

    function toggleEditVendorField(role) {
        const f = document.getElementById('edit_vendor_field');
        if (role === 'vendor' || role === 'contractor') { f.className = 'block'; loadVendors('edit_vendor_id'); }
        else f.className = 'hidden';
    }

    function loadVendors(id) {
        const s = document.getElementById(id);
        fetch('../../api/masters.php?path=vendors&status=active&limit=1000')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data.records) {
                    s.innerHTML = '<option value="">Choose Vendor</option>';
                    data.data.records.forEach(v => s.innerHTML += `<option value="${v.id}">${v.name}</option>`);
                }
            });
    }

    function copyToClipboard(t) {
        navigator.clipboard.writeText(t).then(() => alert('Copied!'));
    }

    function exportUsersData() {
        const params = new URLSearchParams(window.location.search);
        window.open(`export-users.php?${params.toString()}`, '_blank');
    }

    // Initialize
    window.onload = () => {
        const params = new URLSearchParams(window.location.search);
        document.getElementById('searchInput').value = params.get('search') || '';
        document.getElementById('roleFilter').value = params.get('role') || '';
        document.getElementById('statusFilter').value = params.get('status') || '';
        fetchUsers();
        loadRoles();
    };
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>