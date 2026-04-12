<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/Vendor.php';
require_once __DIR__ . '/../../models/VendorPermission.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$vendorModel = new Vendor();
$permissionModel = new VendorPermission();
$allPermissions = $permissionModel->getAllPermissions();

$title = 'Vendor Management';
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
    .stats-card.card-delegations::after { background: #f59e0b; }
    .stats-card.card-documents::after { background: #8b5cf6; }

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

    .vendor-name-cell {
        line-height: 1.1;
        font-weight: 600;
        color: #111827;
    }

    /* Modal Styling Adjustments */
    .form-section-title {
        font-size: 10px;
        font-bold;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #4b5563;
        margin-bottom: 1rem;
        border-bottom: 1px solid #f3f4f6;
        padding-bottom: 0.5rem;
    }
</style>

<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Vendor Management</h1>
        <p class="text-[13px] text-gray-500 mt-0.5">Manage vendor information, banking details, and portal access permissions.</p>
    </div>
    <div class="flex items-center gap-2">
        <button onclick="exportVendorsData()"
            class="inline-flex items-center px-3.5 py-2 text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-xl shadow-sm hover:bg-gray-50 transition-all active:scale-95">
            <svg class="w-3.5 h-3.5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                </path>
            </svg>
            Export
        </button>
        <button onclick="resetVendorForm(); openModal('vendorModal')"
            class="inline-flex items-center px-4 py-2 text-xs font-bold text-white bg-blue-600 rounded-xl shadow-md shadow-blue-100 hover:bg-blue-700 transition-all active:scale-95 focus:ring-4 focus:ring-blue-50 focus:border-blue-300">
            <svg class="w-3.5 h-3.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6">
                </path>
            </svg>
            Add Vendor
        </button>
    </div>
</div>

<!-- Statistics Cards Row -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8" id="statsGrid">
    <div class="stats-card card-total active" onclick="filterByStatus('')" id="stat-total">
        <div style="display: flex; align-items: flex-start; justify-content: space-between;">
            <div style="flex: 1;">
                <div class="card-header">Total Vendors</div>
                <div class="card-number" id="count-total">...</div>
                <div class="card-subtitle">System Registered</div>
            </div>
            <div class="card-icon" style="background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);">
                <svg style="color: #4f46e5;" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3.005 3.005 0 013.75-2.906z"></path>
                </svg>
            </div>
        </div>
    </div>
    <div class="stats-card card-active" onclick="filterByStatus('active')" id="stat-active">
        <div style="display: flex; align-items: flex-start; justify-content: space-between;">
            <div style="flex: 1;">
                <div class="card-header">Active Vendors</div>
                <div class="card-number" id="count-active">...</div>
                <div class="card-subtitle">Operational</div>
            </div>
            <div class="card-icon" style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);">
                <svg style="color: #059669;" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
            </div>
        </div>
    </div>
    <div class="stats-card card-delegations" id="stat-delegations">
        <div style="display: flex; align-items: flex-start; justify-content: space-between;">
            <div style="flex: 1;">
                <div class="card-header">Active Sites</div>
                <div class="card-number" id="count-delegations">...</div>
                <div class="card-subtitle">With Delegations</div>
            </div>
            <div class="card-icon" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);">
                <svg style="color: #d97706;" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd"></path>
                </svg>
            </div>
        </div>
    </div>
    <div class="stats-card card-documents" id="stat-documents">
        <div style="display: flex; align-items: flex-start; justify-content: space-between;">
            <div style="flex: 1;">
                <div class="card-header">Documented</div>
                <div class="card-number" id="count-documents">...</div>
                <div class="card-subtitle">Full Compliance</div>
            </div>
            <div class="card-icon" style="background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%);">
                <svg style="color: #7c3aed;" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
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
        <input type="text" id="searchInput" placeholder="Search vendors, codes, or contact details..."
            class="block w-full pl-10 pr-4 py-2 bg-white border border-gray-100 rounded-xl text-sm focus:ring-2 focus:ring-blue-50 focus:border-blue-200 transition-all outline-none shadow-sm"
            onkeyup="debounceFilter()">
    </div>
    <div class="flex items-center gap-2">
        <select id="statusFilter"
            class="appearance-none px-4 py-2 bg-white border border-gray-100 rounded-xl text-sm focus:ring-2 focus:ring-blue-50 focus:border-blue-200 shadow-sm outline-none cursor-pointer min-w-[130px]"
            onchange="applyFilters()">
            <option value="">All Status</option>
            <option value="active">Active Only</option>
            <option value="inactive">Inactive Only</option>
        </select>
    </div>
</div>

<!-- Vendors Table -->
<div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden min-h-[400px] relative">
    <div id="tableLoading" class="absolute inset-0 bg-white/80 z-10 flex items-center justify-center hidden">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/50 border-b border-gray-100">
                    <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center w-16">
                        #</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Vendor Profile</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Company Info</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Contact Details</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Compliance</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Status</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-right">Actions</th>
                </tr>
            </thead>
            <tbody id="vendorsTableBody" class="bg-white divide-y divide-gray-50">
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

<!-- Vendor Modal (Add/Edit) -->
<div id="vendorModal" class="modal">
    <div class="modal-content max-w-5xl rounded-3xl p-0 overflow-hidden">
        <div class="bg-gray-50/80 px-8 py-6 border-b flex justify-between items-center">
            <div>
                <h3 id="vendorModalTitle" class="text-xl font-bold text-gray-900">Add New Vendor</h3>
                <p class="text-xs text-gray-500 mt-1">Complete all sections to ensure full vendor compliance.</p>
            </div>
            <button onclick="closeModal('vendorModal')" class="text-gray-400 hover:text-gray-600 p-2 bg-white rounded-xl shadow-sm border border-gray-100">&times;</button>
        </div>
        <form id="vendorForm" class="p-8 space-y-10 max-h-[80vh] overflow-y-auto" enctype="multipart/form-data">
            <input type="hidden" name="vendor_id" id="edit_vendor_id">
            
            <!-- Basic Information -->
            <section>
                <div class="form-section-title">Basic Information</div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">Vendor Code</label>
                        <input type="text" name="vendor_code" id="vendor_code" placeholder="Auto-generated if empty"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm outline-none focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">Mobility ID</label>
                        <input type="text" name="mobility_id" id="mobility_id" placeholder="App ID"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm outline-none">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">Mobility Password</label>
                        <input type="password" name="mobility_password" id="mobility_password" placeholder="New password"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm outline-none">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">Name *</label>
                        <input type="text" name="vendorName" id="vendorName" placeholder="Full name"
                            class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-2xl text-sm outline-none focus:ring-2 focus:ring-blue-100 font-semibold" required>
                    </div>
                </div>
            </section>

            <!-- Company Information -->
            <section>
                <div class="form-section-title">Company Information</div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">Company Name</label>
                        <input type="text" name="company_name" id="company_name" placeholder="Legal Entity"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm outline-none">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">Address</label>
                        <input type="text" name="address" id="address" placeholder="Full Address"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm outline-none">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">Email</label>
                        <input type="email" name="email" id="email" placeholder="official@example.com"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm outline-none">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">Contact Number</label>
                        <input type="tel" name="contact_number" id="contact_number" placeholder="+91 00000 00000"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm outline-none">
                    </div>
                </div>
            </section>

            <!-- Banking Information -->
            <section>
                <div class="form-section-title">Banking Information</div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">Bank Name</label>
                        <input type="text" name="bank_name" id="bank_name" placeholder="e.g. HDFC Bank"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm outline-none">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">Account Number</label>
                        <input type="text" name="account_number" id="account_number" placeholder="0000 0000 0000"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm outline-none">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">IFSC Code</label>
                        <input type="text" name="ifsc_code" id="ifsc_code" placeholder="HDFC0001234"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm outline-none uppercase">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">GST Number</label>
                        <input type="text" name="gst_number" id="gst_number" placeholder="22AAAAA0000A1Z5"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm outline-none uppercase">
                    </div>
                </div>
            </section>

            <!-- Legal Documentation -->
            <section>
                <div class="form-section-title">Legal Documentation</div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">PAN Card Number</label>
                        <input type="text" name="pan_card_number" id="pan_card_number" placeholder="ABCDE1234F"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm outline-none uppercase">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">Aadhaar Card Number</label>
                        <input type="text" name="aadhaar_number" id="aadhaar_number" placeholder="0000 0000 0000"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm outline-none">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">MSME Number</label>
                        <input type="text" name="msme_number" id="msme_number" placeholder="UDYAM-XX-00-1234567"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm outline-none">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">ESIC Number</label>
                        <input type="text" name="esic_number" id="esic_number" placeholder="00-00-000000-000-0000"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm outline-none">
                    </div>
                </div>
            </section>

            <!-- Additional Information -->
            <section>
                <div class="form-section-title">Additional Information & Files</div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">PF Number</label>
                        <input type="text" name="pf_number" id="pf_number" placeholder="XX/XXX/00000"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm outline-none">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">PVC Status</label>
                        <select name="pvc_status" id="pvc_status" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm outline-none appearance-none cursor-pointer">
                            <option value="">Select</option>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">Experience Letter</label>
                        <input type="file" name="experience_letter" id="experience_letter" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                            class="w-full px-3 py-1.5 bg-white border border-dashed border-gray-200 rounded-2xl text-[11px] outline-none">
                        <p class="text-[10px] text-gray-400 mt-1" id="experience_letter_filename"></p>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">Photograph</label>
                        <input type="file" name="photograph" id="photograph" accept=".jpg,.jpeg,.png"
                            class="w-full px-3 py-1.5 bg-white border border-dashed border-gray-200 rounded-2xl text-[11px] outline-none">
                        <p class="text-[10px] text-gray-400 mt-1" id="photograph_filename"></p>
                    </div>
                </div>
            </section>

            <div class="pt-8 border-t flex justify-end gap-3">
                <button type="button" onclick="closeModal('vendorModal')" class="px-6 py-3 text-sm font-bold text-gray-400 hover:text-gray-600 transition-colors uppercase tracking-widest">Cancel</button>
                <button type="submit" id="submitVendorBtn"
                    class="px-12 py-3 bg-blue-600 text-white text-sm font-bold rounded-2xl shadow-xl shadow-blue-100 hover:bg-blue-700 transition-all active:scale-95 uppercase tracking-widest">Update Vendor</button>
            </div>
        </form>
    </div>
</div>

<!-- View Vendor Modal -->
<div id="viewVendorModal" class="modal">
    <div class="modal-content max-w-2xl rounded-3xl p-8 bg-white shadow-2xl">
        <div class="flex items-start justify-between mb-8">
            <div class="flex items-center gap-4">
                <div id="view_avatar_circle"
                    class="w-16 h-16 rounded-2xl bg-blue-50 flex items-center justify-center text-2xl font-black text-blue-600 border border-blue-100/50">
                </div>
                <div>
                    <h3 id="view_vendor_name" class="text-xl font-bold text-gray-900 leading-tight">---</h3>
                    <div class="flex items-center gap-2 mt-1">
                        <span id="view_vendor_code_badge" class="text-[10px] font-bold text-blue-600 uppercase tracking-wider">---</span>
                        <span class="text-gray-300">•</span>
                        <div id="view_status_badge_container" class="flex items-center">
                            <div id="view_status_dot" class="w-1.5 h-1.5 rounded-full mr-1.5"></div>
                            <span id="view_status_badge" class="text-[10px] font-bold uppercase tracking-wider">---</span>
                        </div>
                    </div>
                </div>
            </div>
            <button onclick="closeModal('viewVendorModal')" class="text-gray-400 hover:text-gray-600 transition-colors p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
            <div class="space-y-6">
                <div>
                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-2">Company Information</p>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg></div>
                            <div>
                                <p id="view_company_name" class="text-sm font-bold text-gray-800">---</p>
                                <p class="text-[11px] text-gray-400">Legal Entity Name</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg></div>
                            <div>
                                <p id="view_gst_number" class="text-sm font-bold text-gray-800">---</p>
                                <p class="text-[11px] text-gray-400">GST Registration</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="space-y-6">
                 <div>
                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-2">Contact Details</p>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg></div>
                            <div>
                                <p id="view_email" class="text-sm font-bold text-gray-800">---</p>
                                <p class="text-[11px] text-gray-400">Official Email</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg></div>
                            <div>
                                <p id="view_phone" class="text-sm font-bold text-gray-800">---</p>
                                <p class="text-[11px] text-gray-400">Mobile Number</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-6 border-t border-gray-100 flex justify-end gap-2">
            <button onclick="closeModal('viewVendorModal')" class="px-6 py-2 text-sm font-bold text-gray-400">Close</button>
            <button onclick="editVendorFromView()" class="px-8 py-2 bg-gray-900 text-white text-sm font-bold rounded-2xl">Edit Profile</button>
        </div>
    </div>
</div>

<!-- Permissions Modal -->
<div id="permissionsModal" class="modal">
    <div class="modal-content max-w-xl rounded-3xl p-8 bg-white overflow-hidden">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-xl font-bold text-gray-900">Module Permissions</h3>
                <p class="text-xs text-gray-500 mt-1" id="perm_vendor_name">Configure portal access for this vendor.</p>
            </div>
            <button onclick="closeModal('permissionsModal')" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        
        <form id="permissionsForm" class="space-y-4">
            <input type="hidden" id="perm_vendor_id" name="vendor_id">
            <div class="space-y-3" id="permissionsList">
                <?php foreach ($allPermissions as $key => $label): ?>
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-transparent hover:border-blue-100 transition-all">
                        <div class="flex-1 pr-4">
                            <label class="text-sm font-bold text-gray-900"><?php echo $label; ?></label>
                            <p class="text-[10px] text-gray-500 mt-0.5"><?php echo getPermissionDescription($key); ?></p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="perm_<?php echo $key; ?>" name="permissions[<?php echo $key; ?>]" value="1" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="pt-6">
                <button type="submit" class="w-full py-3 bg-blue-600 text-white font-bold rounded-2xl shadow-lg shadow-blue-100">Update Permissions</button>
            </div>
        </form>
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
        const status = document.getElementById('statusFilter').value;

        const params = new URLSearchParams(window.location.search);
        params.set('page', page);
        if (search) params.set('search', search); else params.delete('search');
        if (status) params.set('status', status); else params.delete('status');

        window.history.pushState({}, '', '?' + params.toString());
        fetchVendors();
    }

    function filterByStatus(status) {
        document.getElementById('statusFilter').value = status;
        applyFilters(1);
    }

    async function fetchVendors() {
        const loading = document.getElementById('tableLoading');
        loading.classList.remove('hidden');

        try {
            const params = new URLSearchParams(window.location.search);
            const response = await fetch(`../../api/vendors.php?${params.toString()}`);
            const result = await response.json();

            if (result.success) {
                currentData = result.data;
                renderStats(result.data.stats);
                renderTable(result.data.vendors);
                renderPagination(result.data.pagination);
            }
        } catch (error) {
            console.error('Fetch error:', error);
            showToast('Failed to load vendors data.', 'error');
        } finally {
            loading.classList.add('hidden');
        }
    }

    function renderStats(stats) {
        document.getElementById('count-total').textContent = (stats.total || 0).toLocaleString();
        document.getElementById('count-active').textContent = (stats.total_active || 0).toLocaleString();
        document.getElementById('count-delegations').textContent = (stats.with_delegations || 0).toLocaleString();
        document.getElementById('count-documents').textContent = (stats.with_documents || 0).toLocaleString();

        const params = new URLSearchParams(window.location.search);
        const status = params.get('status');

        document.querySelectorAll('.stats-card').forEach(c => c.classList.remove('active'));
        if (status === 'active') document.getElementById('stat-active').classList.add('active');
        else if (status === 'inactive') document.getElementById('stat-total').classList.add('active');
        else if (!status || status === '') document.getElementById('stat-total').classList.add('active');
    }

    function renderTable(vendors) {
        const tbody = document.getElementById('vendorsTableBody');
        tbody.innerHTML = '';

        if (vendors.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                        <div class="flex flex-col items-center">
                            <svg class="w-12 h-12 text-gray-100 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            <p class="text-xs font-bold text-gray-300 uppercase tracking-widest">No vendors found</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        const params = new URLSearchParams(window.location.search);
        const page = parseInt(params.get('page')) || 1;
        const limit = 10;

        vendors.forEach((vendor, index) => {
            const initial = vendor.name.charAt(0).toUpperCase();
            const colors = ['bg-blue-100 text-blue-600', 'bg-emerald-100 text-emerald-600', 'bg-purple-100 text-purple-600', 'bg-orange-100 text-orange-600'];
            const colorClass = colors[vendor.name.length % colors.length];

            const row = `
                <tr class="hover:bg-gray-50/50 transition-colors group">
                    <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-gray-400 text-center">
                        ${(page - 1) * limit + index + 1}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="w-9 h-9 rounded-xl ${colorClass} flex items-center justify-center text-xs font-bold mr-3 shadow-sm group-hover:scale-110 transition-transform">
                                ${initial}
                            </div>
                            <div>
                                <div class="text-sm vendor-name-cell text-gray-900 group-hover:text-blue-600 transition-colors cursor-pointer" onclick="viewVendor(${vendor.id})">
                                    ${vendor.name}
                                </div>
                                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">${vendor.vendor_code || '---'}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-700">${vendor.company_name || 'Individual'}</div>
                        <div class="text-[10px] text-gray-400 font-bold uppercase">${vendor.gst_number || 'No GST'}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex flex-col gap-0.5">
                            <div class="flex items-center text-[11px] text-gray-500 font-medium">
                                <svg class="w-3.5 h-3.5 mr-1.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                ${vendor.email || '---'}
                            </div>
                            <div class="flex items-center text-[11px] text-gray-500 font-medium">
                                <svg class="w-3.5 h-3.5 mr-1.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                ${vendor.phone || '---'}
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex gap-1">
                            ${vendor.experience_letter_path ? '<span class="w-2 h-2 rounded-full bg-emerald-500" title="Experience Letter OK"></span>' : '<span class="w-2 h-2 rounded-full bg-gray-200" title="Missing Experience Letter"></span>'}
                            ${vendor.photograph_path ? '<span class="w-2 h-2 rounded-full bg-blue-500" title="Photograph OK"></span>' : '<span class="w-2 h-2 rounded-full bg-gray-200" title="Missing Photograph"></span>'}
                            ${vendor.gst_number && vendor.pan_card_number ? '<span class="w-2 h-2 rounded-full bg-purple-500" title="Tax Docs OK"></span>' : '<span class="w-2 h-2 rounded-full bg-gray-200" title="Missing Tax Docs"></span>'}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="w-1.5 h-1.5 rounded-full mr-2 ${vendor.status === 'active' ? 'bg-emerald-500 animate-pulse' : 'bg-gray-300'}"></div>
                            <span class="text-[11px] font-bold ${vendor.status === 'active' ? 'text-emerald-600' : 'text-gray-400'}">
                                ${vendor.status.toUpperCase()}
                            </span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <div class="flex items-center justify-end gap-1">
                            <button onclick="managePermissions(${vendor.id})" class="p-1.5 text-gray-400 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition-all" title="Manage Permissions">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                            </button>
                            <button onclick="viewVendor(${vendor.id})" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all" title="View Details">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </button>
                            <button onclick="editVendor(${vendor.id})" class="p-1.5 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-all" title="Edit Profile">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </button>
                            <button onclick="toggleVendorStatus(${vendor.id})" class="p-1.5 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-all" title="Toggle Status">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 12.728l-3.536-3.536M12 3v4m0 10v4M3 12h4m10 0h4"></path></svg>
                            </button>
                            <button onclick="deleteVendor(${vendor.id})" class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all" title="Delete">
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

    function resetVendorForm() {
        document.getElementById('vendorForm').reset();
        document.getElementById('edit_vendor_id').value = '';
        document.getElementById('vendorModalTitle').textContent = 'Add New Vendor';
        document.getElementById('submitVendorBtn').textContent = 'Save Vendor';
        document.getElementById('experience_letter_filename').textContent = '';
        document.getElementById('photograph_filename').textContent = '';
    }

    document.getElementById('vendorForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        const id = document.getElementById('edit_vendor_id').value;
        const action = id ? `update&id=${id}` : 'create';
        
        const response = await fetch(`../../api/vendors.php?action=${action}`, { method: 'POST', body: formData });
        const result = await response.json();
        
        if (result.success) {
            closeModal('vendorModal');
            showToast(result.message, 'success');
            fetchVendors();
        } else {
            showToast(result.message, 'error');
        }
    });

    async function viewVendor(id) {
        const res = await fetch(`../../api/vendors.php?action=view&id=${id}`);
        const data = await res.json();
        if (data.success) {
            const v = data.vendor;
            document.getElementById('view_vendor_name').textContent = v.name;
            document.getElementById('view_company_name').textContent = v.company_name || 'Individual';
            document.getElementById('view_vendor_code_badge').textContent = v.vendor_code || 'No Code';
            document.getElementById('view_gst_number').textContent = v.gst_number || 'Not Registered';
            document.getElementById('view_email').textContent = v.email || 'No email';
            document.getElementById('view_phone').textContent = v.phone || 'No phone';
            document.getElementById('view_avatar_circle').textContent = v.name.charAt(0).toUpperCase();
            
            const statusBadge = document.getElementById('view_status_badge');
            const statusDot = document.getElementById('view_status_dot');
            statusBadge.textContent = v.status.toUpperCase();
            statusBadge.className = `text-[10px] font-bold uppercase tracking-wider ${v.status === 'active' ? 'text-emerald-600' : 'text-gray-400'}`;
            statusDot.className = `w-1.5 h-1.5 rounded-full mr-1.5 ${v.status === 'active' ? 'bg-emerald-500' : 'bg-gray-300'}`;
            
            document.querySelector('#viewVendorModal button[onclick="editVendorFromView()"]').onclick = () => {
                closeModal('viewVendorModal');
                editVendor(id);
            };
            
            openModal('viewVendorModal');
        }
    }

    async function editVendor(id) {
        const res = await fetch(`../../api/vendors.php?action=edit&id=${id}`);
        const data = await res.json();
        if (data.success) {
            const v = data.vendor;
            document.getElementById('edit_vendor_id').value = v.id;
            document.getElementById('vendorModalTitle').textContent = 'Edit Vendor Profile';
            document.getElementById('submitVendorBtn').textContent = 'Update Vendor';
            
            const form = document.getElementById('vendorForm');
            form.querySelector('[name="vendorName"]').value = v.name || '';
            form.querySelector('[name="vendor_code"]').value = v.vendor_code || '';
            form.querySelector('[name="mobility_id"]').value = v.mobility_id || '';
            form.querySelector('[name="mobility_password"]').value = ''; // Always clear password on edit
            form.querySelector('[name="company_name"]').value = v.company_name || '';
            form.querySelector('[name="address"]').value = v.address || '';
            form.querySelector('[name="email"]').value = v.email || '';
            form.querySelector('[name="contact_number"]').value = v.phone || '';
            form.querySelector('[name="bank_name"]').value = v.bank_name || '';
            form.querySelector('[name="account_number"]').value = v.account_number || '';
            form.querySelector('[name="ifsc_code"]').value = v.ifsc_code || '';
            form.querySelector('[name="gst_number"]').value = v.gst_number || '';
            form.querySelector('[name="pan_card_number"]').value = v.pan_card_number || '';
            form.querySelector('[name="aadhaar_number"]').value = v.aadhaar_number || '';
            form.querySelector('[name="msme_number"]').value = v.msme_number || '';
            form.querySelector('[name="esic_number"]').value = v.esic_number || '';
            form.querySelector('[name="pf_number"]').value = v.pf_number || '';
            form.querySelector('[name="pvc_status"]').value = v.pvc_status || '';

            // Show current filenames if any
            document.getElementById('experience_letter_filename').textContent = v.experience_letter_path ? 'Current: ' + v.experience_letter_path.split('/').pop() : '';
            document.getElementById('photograph_filename').textContent = v.photograph_path ? 'Current: ' + v.photograph_path.split('/').pop() : '';
            
            openModal('vendorModal');
        }
    }

    async function toggleVendorStatus(id) {
        const confirmed = await showConfirm(
            'Change Status',
            'Are you sure you want to change this vendor\'s status?',
            { confirmType: 'primary', confirmText: 'Yes, Change' }
        );
        if (!confirmed) return;

        const res = await fetch(`../../api/vendors.php?action=toggle-status&id=${id}`, { method: 'POST' });
        const data = await res.json();
        if (data.success) {
            showToast(data.message, 'success');
            fetchVendors();
        } else {
            showToast(data.message, 'error');
        }
    }

    async function deleteVendor(id) {
        const confirmed = await showConfirm(
            'Delete Vendor',
            'Are you sure you want to permanently delete this vendor? This action cannot be undone.',
            { confirmType: 'danger', confirmText: 'Yes, Delete' }
        );
        if (!confirmed) return;

        const res = await fetch(`../../api/vendors.php?action=delete&id=${id}`, { method: 'POST' });
        const data = await res.json();
        if (data.success) {
            showToast(data.message, 'success');
            fetchVendors();
        } else {
            showToast(data.message, 'error');
        }
    }

    async function managePermissions(id) {
        try {
            const res = await fetch(`get-vendor-permissions.php?vendor_id=${id}`);
            const result = await res.json();
            if (result.success) {
                document.getElementById('perm_vendor_id').value = id;
                document.getElementById('perm_vendor_name').textContent = `Manage permissions for ${result.vendor.name}`;
                
                document.querySelectorAll('#permissionsList input[type="checkbox"]').forEach(cb => cb.checked = false);
                
                Object.keys(result.permissions).forEach(key => {
                    const cb = document.getElementById(`perm_${key}`);
                    if (cb) cb.checked = result.permissions[key];
                });
                
                openModal('permissionsModal');
            }
        } catch (e) {
            console.error(e);
        }
    }

    document.getElementById('permissionsForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const res = await fetch('update-permissions.php', { method: 'POST', body: formData });
        const result = await res.json();
        if (result.success) {
            closeModal('permissionsModal');
            showToast('Permissions updated successfully', 'success');
        } else {
            showToast(result.message || 'Failed to update permissions', 'error');
        }
    });

    function exportVendorsData() {
        const params = new URLSearchParams(window.location.search);
        window.open(`export-vendors.php?${params.toString()}`, '_blank');
    }

    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func(...args), wait);
        };
    }

    document.addEventListener('DOMContentLoaded', fetchVendors);
</script>

<?php
function getPermissionDescription($key) {
    $descriptions = [
        'view_sites' => 'Allow vendor to view and manage their assigned sites',
        'update_progress' => 'Allow vendor to update installation progress',
        'view_masters' => 'Allow vendor to view master data (customers, banks, etc.)',
        'view_reports' => 'Allow vendor to view reports and analytics',
        'view_inventory' => 'Allow vendor to view inventory information',
        'view_material_requests' => 'Allow vendor to view material requests'
    ];
    return $descriptions[$key] ?? 'Permission description not available';
}

$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>