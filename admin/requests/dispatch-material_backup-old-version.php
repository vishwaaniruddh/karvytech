<?php
require_once __DIR__ . '/../../config/auth.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$requestId = $_GET['request_id'] ?? null;

if (!$requestId) {
    header('Location: index.php');
    exit;
}

$title = 'Material Dispatch - Request #' . $requestId;
ob_start();
?>

<style>
    /* Modern Geometric & Professional Styles */
    .premium-card {
        background: rgba(255, 255, 255, 1);
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        transition: all 0.2s ease;
    }
    .premium-card:hover {
        border-color: #3b82f6;
    }
    .info-tile {
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: 0.5rem;
        padding: 1.25rem;
    }
    .info-icon-wrapper {
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        flex-shrink: 0;
    }
    .font-professional {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }
    .status-badge {
        padding: 2px 10px;
        border-radius: 4px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .item-card {
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        overflow: hidden;
        transition: border-color 0.2s;
    }
    .item-card:hover {
        border-color: #3b82f6;
    }
    .gradient-btn {
        background: #2563eb;
        color: white;
        font-weight: 600;
        transition: background 0.2s;
    }
    .gradient-btn:hover:not(:disabled) {
        background: #1d4ed8;
    }
    .gradient-btn:disabled {
        background: #94a3b8;
        cursor: not-allowed;
        opacity: 0.7;
    }
    .input-professional {
        border-radius: 0.375rem !important;
        border: 1px solid #d1d5db !important;
        padding: 0.5rem 0.75rem !important;
    }
    .input-professional:focus {
        border-color: #3b82f6 !important;
        ring: 2px rgba(59, 130, 246, 0.2);
    }

    /* Professional Notification System */
    #notification-container {
        position: fixed;
        top: 24px;
        right: 24px;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 12px;
        pointer-events: none;
    }
    .toast {
        background: white;
        border-radius: 12px;
        padding: 16px 20px;
        min-width: 320px;
        max-width: 450px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        border: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        gap: 14px;
        transform: translateX(120%);
        transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        pointer-events: auto;
    }
    .toast.show {
        transform: translateX(0);
    }
    .toast-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .toast-success .toast-icon { background: #f0fdf4; color: #16a34a; }
    .toast-error .toast-icon { background: #fef2f2; color: #dc2626; }
    .toast-warning .toast-icon { background: #fffbeb; color: #d97706; }
    
    .toast-content { flex-grow: 1; }
    .toast-title { font-weight: 700; color: #111827; font-size: 14px; margin-bottom: 2px; }
    .toast-message { color: #6b7280; font-size: 12px; line-height: 1.4; }
    
    .progress-bar {
        position: absolute;
        bottom: 0;
        left: 0;
        height: 3px;
        background: currentColor;
        opacity: 0.3;
        width: 100%;
        border-bottom-left-radius: 12px;
        transform-origin: left;
    }

    /* Skeleton Loading Animation */
    @keyframes skeleton-loading {
        0% { background-color: #f3f4f6; }
        50% { background-color: #e5e7eb; }
        100% { background-color: #f3f4f6; }
    }
    .skeleton {
        animation: skeleton-loading 1.5s infinite linear;
        border-radius: 4px;
        display: block;
    }
</style>

<div id="notification-container"></div>

<div class="font-professional">
    <div id="page-header">
        <nav class="flex mb-2" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 text-xs font-medium text-gray-500">
                <li><a href="../dashboard.php" class="hover:text-blue-600">Dashboard</a></li>
                <li><svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"></path></svg></li>
                <li><a href="index.php" class="hover:text-blue-600">Material Requests</a></li>
                <li><svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"></path></svg></li>
                <li class="text-gray-900 font-bold">Process Dispatch</li>
            </ol>
        </nav>
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Material Dispatch</h1>
                <p id="sub-header" class="mt-1 text-sm text-gray-500 flex items-center">
                    <span class="skeleton w-32 h-4"></span>
                </p>
            </div>
            <div id="header-actions" class="flex space-x-3">
                <!-- Actions injected here -->
            </div>
        </div>
    </div>

    <!-- Information Overview Grid -->
    <div id="info-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-8 mb-8">
        <!-- Site Info Skeleton -->
        <div class="premium-card p-5"><div class="skeleton w-full h-32"></div></div>
        <div class="premium-card p-5"><div class="skeleton w-full h-32"></div></div>
        <div class="premium-card p-5"><div class="skeleton w-full h-32"></div></div>
        <div class="premium-card p-5"><div class="skeleton w-full h-32"></div></div>
    </div>

    <!-- Inventory Issues Alert (Injected) -->
    <div id="stock-alerts"></div>

    <!-- Dispatch Form -->
    <form id="dispatchForm" class="space-y-8 hidden">
        <input type="hidden" name="material_request_id" value="<?php echo $requestId; ?>">
        
        <!-- Logistics Section -->
        <div class="premium-card p-8">
            <h3 class="text-xl font-bold text-gray-900 mb-6 form-section-header">Shipment Logistics</h3>
            <div id="logistics-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-6">
                <!-- Inputs injected here for pre-filling -->
            </div>
        </div>

        <!-- Material Items Section -->
        <div class="premium-card p-8">
            <h3 class="text-xl font-bold text-gray-900 mb-6 form-section-header">Material Allocation</h3>
            <div id="items-container" class="space-y-6">
                <!-- Items injected here -->
            </div>
        </div>

        <!-- Action Controls -->
        <div id="form-actions" class="flex flex-col md:flex-row justify-end items-center gap-4 py-8">
            <!-- Buttons injected here -->
        </div>
    </form>
</div>

<script>
const REQUEST_ID = <?php echo $requestId; ?>;

document.addEventListener('DOMContentLoaded', initDispatchPage);

async function initDispatchPage() {
    try {
        const response = await fetch(`../../api/material_requests.php?action=get_dispatch_data&request_id=${REQUEST_ID}`);
        const result = await response.json();

        if (!result.success) {
            showNotification('Error', result.message, 'error');
            return;
        }

        const { request, items, couriers, has_stock_issues } = result.data;

        renderHeader(request);
        renderInfoGrid(request);
        renderStockAlerts(items);
        renderLogistics(request, couriers);
        renderItems(items);
        renderFormActions(request, has_stock_issues);

        document.getElementById('dispatchForm').classList.remove('hidden');
    } catch (error) {
        console.error(error);
        showNotification('System Error', 'Failed to fetch dispatch data. Please refresh.', 'error');
    }
}

function renderHeader(request) {
    const subHeader = document.getElementById('sub-header');
    subHeader.innerHTML = `
        <span class="inline-flex items-center px-1.5 py-0.5 rounded-md bg-blue-50 text-blue-700 font-bold mr-2 border border-blue-100 italic">MR #${request.id}</span>
        Preparing shipment for approved material request
    `;

    const actions = document.getElementById('header-actions');
    actions.innerHTML = `
        <a href="view-request.php?id=${request.id}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-50 transition-all shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Review Request
        </a>
    `;
}

function renderInfoGrid(request) {
    const grid = document.getElementById('info-grid');
    grid.innerHTML = `
        <!-- Site Info -->
        <div class="premium-card p-5">
            <div class="flex items-center gap-4 mb-4">
                <div class="info-icon-wrapper bg-blue-50 text-blue-600 font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </div>
                <div>
                    <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Site Details</p>
                    <h3 class="text-sm font-bold text-gray-800 leading-tight">${escapeHtml(request.site_name)}</h3>
                </div>
            </div>
            <div class="space-y-3">
                <div class="flex justify-between items-center text-[11px]">
                    <span class="text-gray-500">Site Code</span>
                    <span class="font-mono font-bold text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded">${escapeHtml(request.site_code)}</span>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-[10px] text-gray-400 uppercase font-bold">Location</span>
                    <p class="text-[11px] text-gray-600 leading-tight line-clamp-2">${escapeHtml(request.site_location)}</p>
                </div>
            </div>
        </div>

        <!-- Vendor Info -->
        <div class="premium-card p-5">
            <div class="flex items-center gap-4 mb-4">
                <div class="info-icon-wrapper bg-indigo-50 text-indigo-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <div>
                    <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Vendor Info</p>
                    <h3 class="text-sm font-bold text-gray-800 leading-tight">${escapeHtml(request.vendor_company_name)}</h3>
                </div>
            </div>
            <div class="space-y-2 text-[11px]">
                <div class="flex justify-between">
                    <span class="text-gray-500">Contact Person</span>
                    <span class="font-bold text-gray-700 text-right">${escapeHtml(request.vendor_contact)}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Phone</span>
                    <span class="font-bold text-gray-700">${escapeHtml(request.vendor_phone)}</span>
                </div>
            </div>
        </div>

        <!-- Delegation -->
        <div class="premium-card p-5">
            <div class="flex items-center gap-4 mb-4">
                <div class="info-icon-wrapper bg-orange-50 text-orange-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <div>
                    <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Delegation</p>
                    <h3 class="text-sm font-bold text-gray-800 leading-tight">${escapeHtml(request.delegated_vendor_name)}</h3>
                </div>
            </div>
            <div class="space-y-2 text-[11px]">
                <div class="flex justify-between">
                    <span class="text-gray-500">Status</span>
                    <span class="font-bold ${request.delegated_vendor_name !== 'Direct Operations' ? 'text-green-600' : 'text-orange-600'}">
                        ${request.delegated_vendor_name !== 'Direct Operations' ? 'Site Delegated' : 'Direct site operations'}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Reference</span>
                    <span class="text-gray-400 italic">Project active</span>
                </div>
            </div>
        </div>

        <!-- Survey Insights -->
        <div class="premium-card p-5">
            <div class="flex items-center gap-4 mb-4">
                <div class="info-icon-wrapper bg-indigo-50 text-indigo-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                </div>
                <div>
                    <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Survey Insight</p>
                    <h3 class="text-sm font-bold text-gray-800 leading-tight">${escapeHtml(request.unified_survey_status)}</h3>
                </div>
            </div>
            <div class="space-y-2 text-[11px]">
                <div class="flex justify-between">
                    <span class="text-gray-500">Current Status</span>
                    <span class="status-badge ${request.unified_survey_status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'}">
                        ${escapeHtml(request.unified_survey_status)}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Submission Date</span>
                    <span class="font-bold text-gray-700 text-right">${request.unified_survey_date ? new Date(request.unified_survey_date).toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'}) : 'Pending submission'}</span>
                </div>
            </div>
        </div>
    `;
}

function renderStockAlerts(items) {
    const container = document.getElementById('stock-alerts');
    const outOfStock = items.filter(i => i.is_out_of_stock);
    
    if (outOfStock.length === 0) {
        container.innerHTML = '';
        return;
    }

    container.innerHTML = `
        <div class="bg-red-50 border-l-4 border-red-500 p-6 rounded-lg mb-8 shadow-sm transition-all animate-in fade-in slide-in-from-top-4">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 bg-red-100 text-red-600 flex items-center justify-center rounded-lg flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <div>
                    <h4 class="text-red-800 font-extrabold text-lg">Inventory Validation Failure</h4>
                    <p class="text-red-700 text-sm mb-4">Dispatch is blocked. The following items are out of stock or not correctly registered in the inventory system.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        ${outOfStock.map(item => `
                            <div class="bg-white border border-red-100 p-3 rounded flex justify-between items-center">
                                <div>
                                    <div class="text-[10px] font-bold text-gray-500 uppercase">${escapeHtml(item.item_name)}</div>
                                    <div class="text-xs font-bold text-red-600">${item.stock && !item.stock.not_found ? 'Shortage: ' + item.stock.shortage : 'Not Found in Inventory'}</div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            </div>
        </div>
    `;
}

function renderLogistics(request, couriers) {
    const grid = document.getElementById('logistics-grid');
    const today = new Date().toISOString().split('T')[0];
    
    grid.innerHTML = `
        <div class="form-group">
            <label for="contact_person_name" class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 block">Contact Person Name *</label>
            <input type="text" id="contact_person_name" name="contact_person_name" class="form-input rounded-xl border-gray-200 bg-gray-50/50 p-3 w-full input-focus-effect font-medium" required 
                   value="${escapeHtml(request.site_contact || request.vendor_contact || '')}">
        </div>
        
        <div class="form-group">
            <label for="contact_person_phone" class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 block">Contact Person Phone *</label>
            <input type="text" id="contact_person_phone" name="contact_person_phone" class="form-input rounded-xl border-gray-200 bg-gray-50/50 p-3 w-full input-focus-effect font-medium" required
                   pattern="[6-9][0-9]{9}" maxlength="10" placeholder="10-digit mobile number"
                   value="${escapeHtml(request.site_phone || request.vendor_phone || '')}">
            <p id="phone_error" class="mt-1 text-[10px] text-red-500 font-bold hidden"></p>
        </div>
        
        <div class="form-group">
            <label for="courier_name" class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 block">Courier Service *</label>
            <select id="courier_name" name="courier_name" class="form-input rounded-xl border-gray-200 bg-gray-50/50 p-3 w-full input-focus-effect font-medium h-[47px]" required>
                <option value="">Select Courier</option>
                ${couriers.map(c => `<option value="${escapeHtml(c.courier_name)}">${escapeHtml(c.courier_name)}</option>`).join('')}
            </select>
        </div>
        
        <div class="form-group">
            <label for="pod_number" class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 block">POD / AWB Tracking Number</label>
            <input type="text" id="pod_number" name="pod_number" class="form-input rounded-xl border-gray-200 bg-gray-50/50 p-3 w-full input-focus-effect font-medium" 
                   placeholder="Enter tracking ID">
        </div>
        
        <div class="form-group">
            <label for="dispatch_date" class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 block">Dispatch Date *</label>
            <input type="date" id="dispatch_date" name="dispatch_date" class="form-input rounded-xl border-gray-200 bg-gray-50/50 p-3 w-full input-focus-effect font-medium" required 
                   value="${today}">
        </div>
        
        <div class="form-group">
            <label for="expected_delivery_date" class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 block">Expected Delivery</label>
            <input type="date" id="expected_delivery_date" name="expected_delivery_date" class="form-input rounded-xl border-gray-200 bg-gray-50/50 p-3 w-full input-focus-effect font-medium"
                   value="${request.required_date || ''}">
        </div>
        
        <div class="form-group md:col-span-2 lg:col-span-3">
            <label for="delivery_address" class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 block">Destination Address *</label>
            <textarea id="delivery_address" name="delivery_address" rows="3" class="form-input rounded-xl border-gray-200 bg-gray-50/50 p-4 w-full input-focus-effect font-medium" required
                      placeholder="Complete site delivery address...">${escapeHtml(request.address || '')}</textarea>
        </div>
        
        <div class="form-group md:col-span-2 lg:col-span-3">
            <label for="dispatch_remarks" class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 block">Dispatch Instructions / Remarks</label>
            <textarea id="dispatch_remarks" name="dispatch_remarks" rows="2" class="form-input rounded-xl border-gray-200 bg-gray-50/50 p-4 w-full input-focus-effect font-medium" 
                      placeholder="Special instructions for the courier or recipient...">${escapeHtml(request.request_notes || '')}</textarea>
        </div>
    `;

    // Re-bind phone helper
    bindPhoneHelper();
}

function renderItems(items) {
    const container = document.getElementById('items-container');
    container.innerHTML = items.map((item, index) => `
        <div class="item-card bg-white ${item.is_out_of_stock ? 'border-red-200' : 'border-gray-100'}">
            <div class="p-4 flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-gray-50">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-lg ${item.is_out_of_stock ? 'bg-red-50 text-red-600' : 'bg-blue-50 text-blue-600'} flex items-center justify-center flex-shrink-0">
                        <i class="${item.icon_class} text-sm"></i>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-bold text-gray-800 leading-tight">${escapeHtml(item.item_name)}</h4>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">
                            Code: ${escapeHtml(item.item_code)} | Unit: ${escapeHtml(item.unit)}
                            ${item.need_serial_number ? '<span class="ml-2 text-indigo-600">| Serialized</span>' : ''}
                        </p>
                    </div>
                </div>
                
                <div class="flex items-center gap-6">
                    <div class="text-right">
                        <p class="text-[10px] font-bold text-gray-400 uppercase">Warehouse Stock</p>
                        <p class="text-sm font-bold ${item.is_out_of_stock ? 'text-red-600' : 'text-gray-700'}">
                            ${item.stock ? item.stock.available_qty : '0'} ${escapeHtml(item.unit)}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-bold text-gray-400 uppercase">Requested</p>
                        <p class="text-sm font-bold text-gray-800">
                            ${parseInt(item.original.quantity)} ${escapeHtml(item.unit)}
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="p-6 bg-gray-50/50">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                    <div class="md:col-span-3">
                        <label class="text-[10px] font-extrabold text-gray-500 uppercase block mb-2 tracking-widest">Dispatch Quantity *</label>
                        <div class="relative">
                            <input type="number" name="items[${index}][dispatch_quantity]" 
                                   class="form-input rounded-xl border-gray-200 p-3 w-full font-bold focus:ring-4 focus:ring-blue-100 transition-all ${item.is_out_of_stock ? 'bg-gray-100 cursor-not-allowed' : ''}" 
                                   step="1" min="0" 
                                   max="${item.stock ? Math.min(parseInt(item.original.quantity), item.stock.available_qty) : parseInt(item.original.quantity)}" 
                                   value="${item.stock ? Math.min(parseInt(item.original.quantity), item.stock.available_qty) : parseInt(item.original.quantity)}" 
                                   ${item.is_out_of_stock ? 'disabled readonly' : 'required'}
                                   onchange="updateIndividualRows(${index})">
                            <div class="absolute right-3 top-3 text-[10px] font-bold text-gray-400">${escapeHtml(item.unit)}</div>
                        </div>
                        <input type="hidden" name="items[${index}][boq_item_id]" value="${item.boq_item_id || ''}">
                        <input type="hidden" name="items[${index}][material_name]" value="${escapeHtml(item.item_name)}">
                    </div>
                    
                    <div class="md:col-span-4">
                        <label class="text-[10px] font-extrabold text-gray-500 uppercase block mb-2 tracking-widest">Reference / Batch No</label>
                        <input type="text" name="items[${index}][batch_number]" 
                               class="form-input rounded-xl border-gray-200 p-3 w-full font-medium focus:ring-4 focus:ring-blue-100 transition-all ${item.is_out_of_stock ? 'bg-gray-100 cursor-not-allowed' : ''}" 
                               placeholder="Production batch ID"
                               ${item.is_out_of_stock ? 'disabled readonly' : ''}>
                    </div>
                    
                    <div class="md:col-span-5">
                        <label class="text-[10px] font-extrabold text-gray-500 uppercase block mb-2 tracking-widest">Line Item Notes</label>
                        <textarea name="items[${index}][dispatch_notes]" 
                                  class="form-input rounded-xl border-gray-200 p-3 w-full font-medium focus:ring-4 focus:ring-blue-100 transition-all ${item.is_out_of_stock ? 'bg-gray-100 cursor-not-allowed' : ''}" 
                                  rows="1" 
                                  placeholder="Specific notes for this item..."
                                  ${item.is_out_of_stock ? 'disabled readonly' : ''}>${escapeHtml(item.original.notes || '')}</textarea>
                    </div>
                </div>
                
                ${item.need_serial_number ? `
                <div class="mt-6 border-t border-gray-200 pt-6">
                    <div class="flex items-center justify-between mb-4">
                        <h5 class="text-sm font-bold text-purple-900 flex items-center uppercase tracking-wider">
                            <i class="fas fa-barcode mr-2 text-purple-600"></i> Register Serial Numbers
                        </h5>
                        <span class="text-[10px] font-extrabold px-3 py-1 bg-purple-50 text-purple-700 rounded-lg">
                            ALLOCATING <span id="individual_count_${index}">0</span> UNITS
                        </span>
                    </div>
                    <div id="individual_items_${index}" class="grid grid-cols-1 md:grid-cols-2 gap-4" data-needs-serial="true"></div>
                </div>` : `<input type="hidden" id="individual_items_${index}" data-needs-serial="false">`}
            </div>
        </div>
    `).join('');

    // Trigger row updates
    items.forEach((_, i) => updateIndividualRows(i));
}

function renderFormActions(request, has_stock_issues) {
    const actions = document.getElementById('form-actions');
    actions.innerHTML = `
        <a href="view-request.php?id=${request.id}" class="px-6 py-2 bg-white border border-gray-300 text-gray-700 font-bold rounded hover:bg-gray-50 transition-all">Discard</a>
        
        <button type="submit" 
                class="gradient-btn px-10 py-3 font-bold rounded flex items-center shadow-lg"
                ${has_stock_issues ? 'disabled' : ''}>
            ${has_stock_issues ? 'Locked: Stock Shortage' : 'Confirm & Finalize Dispatch'}
            <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
        </button>
    `;

    // Bind submit handler
    bindSubmitHandler();
}

function updateIndividualRows(itemIndex) {
    const input = document.querySelector(`input[name="items[${itemIndex}][dispatch_quantity]"]`);
    if(!input) return;
    
    const dispatchQty = Math.floor(parseFloat(input.value)) || 0;
    const container = document.getElementById(`individual_items_${itemIndex}`);
    const countDisplay = document.getElementById(`individual_count_${itemIndex}`);
    const needsSerial = container && container.getAttribute('data-needs-serial') === 'true';
    
    if (countDisplay) countDisplay.textContent = dispatchQty;
    if (!needsSerial) return;
    
    container.innerHTML = '';
    for (let i = 0; i < dispatchQty; i++) {
        const itemDiv = document.createElement('div');
        itemDiv.className = 'bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col gap-2 transition-all hover:border-purple-300';
        itemDiv.innerHTML = `
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold text-purple-600 uppercase tracking-widest">Unit Item ${i + 1}</span>
            </div>
            <div class="grid grid-cols-1 gap-2">
                <input type="text" name="items[${itemIndex}][individual][${i}][serial_number]" 
                       class="form-input rounded-lg border-gray-200 p-2 text-xs font-bold w-full focus:ring-2 focus:ring-purple-100" 
                       placeholder="Enter Unique Serial No *" required>
                <input type="text" name="items[${itemIndex}][individual][${i}][batch_number]" 
                       class="form-input rounded-lg border-gray-100 p-2 text-[10px] w-full bg-gray-50" 
                       placeholder="Production Batch (Optional)">
            </div>
        `;
        container.appendChild(itemDiv);
    }
}

function bindPhoneHelper() {
    document.getElementById('contact_person_phone')?.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
        const error = document.getElementById('phone_error');
        if (this.value.length > 0 && !/^[6-9][0-9]{9}$/.test(this.value)) {
            error?.classList.remove('hidden');
            error.textContent = 'Invalid Mobile Format (10 digits starting with 6-9)';
            this.classList.add('border-red-400', 'bg-red-50');
        } else {
            error?.classList.add('hidden');
            this.classList.remove('border-red-400', 'bg-red-50');
        }
    });
}

function bindSubmitHandler() {
    document.getElementById('dispatchForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const phone = document.getElementById('contact_person_phone').value;
        if (!/^[6-9][0-9]{9}$/.test(phone)) {
            showNotification('Validation Error', 'Please enter a valid 10-digit mobile number.', 'warning');
            return;
        }
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = `
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Synchronizing Dispatch...
        `;
        submitBtn.disabled = true;
        
        const formData = new FormData(this);
        
        fetch('process-material-dispatch.php', { method: 'POST', body: formData })
        .then(async response => {
            const text = await response.text();
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Raw Server Response:', text);
                throw new Error('Server responded with an invalid format. Check debug logs.');
            }
        })
        .then(data => {
            if (data.success) {
                showNotification('Dispatch Confirmed', 'The material dispatch has been successfully registered.', 'success');
                submitBtn.innerHTML = '<i class="fas fa-check mr-3"></i> Confirmed';
                setTimeout(() => window.location.href = `view-request.php?id=${REQUEST_ID}`, 1500);
            } else {
                showNotification('Operation Failed', data.message || 'An unknown error occurred during processing.', 'error');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        })
        .catch(err => {
            showNotification('System Exception', err.message || 'A network error prevented the dispatch from completing.', 'error');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });
}

function showNotification(title, message, type = 'success', duration = 5000) {
    const container = document.getElementById('notification-container');
    const toast = document.createElement('div');
    toast.className = `toast toast-${type} font-professional`;
    
    const iconMap = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-triangle',
        warning: 'fa-exclamation-circle'
    };
    
    toast.innerHTML = `
        <div class="toast-icon">
            <i class="fas ${iconMap[type]} text-lg"></i>
        </div>
        <div class="toast-content">
            <div class="toast-title">${title}</div>
            <div class="toast-message">${message}</div>
        </div>
        <div class="progress-bar"></div>
    `;
    
    container.appendChild(toast);
    requestAnimationFrame(() => toast.classList.add('show'));
    
    const progressBar = toast.querySelector('.progress-bar');
    progressBar.style.transition = `transform ${duration}ms linear`;
    progressBar.style.transform = 'scaleX(0)';
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 400);
    }, duration);
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>