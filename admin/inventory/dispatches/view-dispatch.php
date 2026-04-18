<?php
require_once __DIR__ . '/../../../config/auth.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$dispatchId = $_GET['id'] ?? null;
if (!$dispatchId) { header('Location: index.php'); exit; }

$title = 'Logistics Details';
ob_start();
?>

<style>
    :root {
        --primary: #2563eb;
        --secondary: #64748b;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --background: #f8fafc;
        --card-bg: #ffffff;
    }

    .skeleton {
        background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
        background-size: 200% 100%;
        animation: skeleton-loading 1.5s infinite;
        border-radius: 1rem;
    }

    @keyframes skeleton-loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    .status-stage {
        flex: 1;
        text-align: center;
        padding: 1.25rem 0.75rem;
        position: relative;
        font-weight: 800;
        font-size: 0.625rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #94a3b8;
        transition: all 0.3s ease;
    }

    .status-stage::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background: #e2e8f0;
        border-radius: 3px;
    }

    .status-stage.active { color: var(--primary); font-weight: 900; }
    .status-stage.active::after { background: var(--primary); }
    .status-stage.completed { color: var(--success); font-weight: 900; }
    .status-stage.completed::after { background: var(--success); }
    .status-stage.rejected { color: var(--danger); font-weight: 900; }
    .status-stage.rejected::after { background: var(--danger); }

    .premium-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 1.25rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }

    .premium-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .label-meta {
        font-size: 0.625rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #94a3b8;
    }

    .status-badge {
        font-size: 0.625rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.075em;
        padding: 0.375rem 0.875rem;
        border-radius: 9999px;
    }
</style>

<div id="dispatchApp" class="px-4 py-6" data-dispatch-id="<?php echo $dispatchId; ?>">
    <!-- Loader / Skeleton -->
    <div id="loader" class="space-y-6">
        <div class="h-20 skeleton w-full"></div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="h-24 skeleton"></div>
            <div class="h-24 skeleton"></div>
            <div class="h-24 skeleton"></div>
            <div class="h-24 skeleton"></div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="h-64 skeleton"></div>
            <div class="h-64 skeleton"></div>
        </div>
    </div>

    <!-- Main Content (Hidden initially) -->
    <div id="mainContent" class="hidden">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight" id="headerTitle">Dispatch Manifest</h1>
                    <span id="dispatchBadge" class="px-2.5 py-1 bg-gray-900 text-white rounded-lg text-[10px] font-bold uppercase tracking-widest"></span>
                </div>
                <p class="text-[13px] font-medium text-gray-500 uppercase tracking-wide">Transit & Delivery Intelligence Report</p>
            </div>
            <div class="flex items-center gap-3">
                <button id="viewChallanBtn" style="display: none;" class="px-5 py-2.5 bg-indigo-50 border border-indigo-100 text-indigo-700 hover:bg-indigo-100 rounded-xl text-xs font-bold uppercase tracking-wider transition-all shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Delivery Challan
                </button>
                <button id="printManifestBtn" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 rounded-xl text-xs font-bold uppercase tracking-wider transition-all shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Print Manifest
                </button>
                <a href="index.php" class="p-2.5 bg-white border border-gray-200 text-gray-400 hover:text-gray-900 hover:border-gray-900 rounded-xl transition-all shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                </a>
            </div>
        </div>

        <!-- Status Progress Pipe -->
        <div id="statusPipe" class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-6 flex bg-gray-50/50">
            <!-- Stages will be rendered here -->
        </div>

        <!-- Metric Grid -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8" id="metricsGrid">
            <!-- Stat cards will be rendered here -->
        </div>

        <!-- Details Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Logistics Card -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden" id="logisticsCard"></div>
            <!-- Contact Card -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden" id="contactCard"></div>
        </div>

        <!-- Delivery Confirmation -->
        <div id="deliveryConfirmationSection"></div>

        <!-- Documents Section -->
        <div id="documentsSection"></div>

        <!-- Manifest Table -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-8" id="manifestSection"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const dispatchId = document.getElementById('dispatchApp').dataset.dispatchId;
    loadDispatchData(dispatchId);
});

async function loadDispatchData(id) {
    try {
        const response = await fetch(`get-dispatch-details.php?id=${id}`);
        const data = await response.json();

        if (data.success) {
            renderUI(data);
        } else {
            document.getElementById('dispatchApp').innerHTML = `
                <div class="bg-rose-50 border border-rose-200 text-rose-700 p-6 rounded-2xl text-center">
                    <p class="font-bold">Error Loading Dispatch</p>
                    <p class="text-sm">${data.message}</p>
                    <a href="index.php" class="inline-block mt-4 text-sm font-bold underline">Back to List</a>
                </div>
            `;
        }
    } catch (error) {
        console.error('Fetch error:', error);
    }
}

function renderUI(data) {
    const { dispatch, deliveryConfirmation, hasDocuments } = data;
    
    // Header
    document.title = `Logistics Details - ${dispatch.dispatch_number}`;
    document.getElementById('dispatchBadge').textContent = dispatch.dispatch_number;
    
    // Status Pipe
    renderStatusPipe(dispatch.dispatch_status, deliveryConfirmation);
    
    // Metrics
    renderMetrics(dispatch, deliveryConfirmation);
    
    // Cards
    renderLogisticsCard(dispatch);
    renderContactCard(dispatch);
    
    // Action Buttons
    const challanBtn = document.getElementById('viewChallanBtn');
    challanBtn.style.display = 'flex';
    challanBtn.onclick = () => window.open(`view-delivery-challan.php?id=${dispatch.id}`, '_blank');
    
    document.getElementById('printManifestBtn').onclick = () => window.open(`print-dispatch.php?id=${dispatch.id}`, '_blank');
    
    // Delivery Details
    if (deliveryConfirmation && (deliveryConfirmation.delivery_date || deliveryConfirmation.received_by)) {
        renderDeliveryConfirmation(deliveryConfirmation, dispatch.delivery_address);
    }
    
    // Documents
    if (hasDocuments) {
        renderDocuments(deliveryConfirmation);
    }
    
        // Manifest
        renderManifest(dispatch.items);
        
        // Show Requested Manifest Badge if applicable
        if (dispatch.is_request_manifest) {
            document.getElementById('manifestTitlePlaceholder').innerHTML = `
                <div class="flex items-center gap-2">
                    Material Manifest
                    <span class="px-2 py-0.5 bg-amber-50 text-amber-600 border border-amber-100 rounded text-[9px] font-bold uppercase tracking-widest">Requested Manifest</span>
                </div>
            `;
            document.getElementById('manifestDescription').textContent = "This manifest displays items as originally requested because specific dispatch records for this shipment are not fully recorded.";
        }

        // Swap visibility
    document.getElementById('loader').classList.add('hidden');
    document.getElementById('mainContent').classList.remove('hidden');
}

function renderStatusPipe(status, delivery) {
    const actualStatus = status || (delivery ? (delivery.confirmation_date ? 'confirmed' : 'delivered') : 'dispatched');
    const stages = [
        { key: 'prepared', label: 'Draft', matched: ['prepared', 'dispatched', 'in_transit', 'delivered', 'confirmed', 'completed'], completed: ['dispatched', 'in_transit', 'delivered', 'confirmed', 'completed'] },
        { key: 'dispatched', label: 'Pending', matched: ['dispatched', 'in_transit', 'delivered', 'confirmed', 'completed'], completed: ['in_transit', 'delivered', 'confirmed', 'completed'] },
        { key: 'in_transit', label: actualStatus === 'rejected' ? 'Rejected' : 'Approved', matched: ['in_transit', 'delivered', 'confirmed', 'completed'], completed: ['delivered', 'confirmed', 'completed'], rejected: actualStatus === 'rejected' },
        { key: 'delivered', label: 'Dispatch', matched: ['delivered', 'confirmed', 'completed'], completed: ['confirmed', 'completed'] },
        { key: 'confirmed', label: 'Delivery', matched: ['confirmed', 'completed'], completed: ['completed'] }
    ];

    const container = document.getElementById('statusPipe');
    container.innerHTML = stages.map(stage => {
        let classes = 'status-stage';
        if (stage.matched.includes(actualStatus)) classes += ' active';
        if (stage.completed.includes(actualStatus)) classes += ' completed';
        if (stage.rejected) classes += ' rejected';
        return `<div class="${classes}">${stage.label}</div>`;
    }).join('');
}

function renderMetrics(dispatch, delivery) {
    const actualStatus = dispatch.dispatch_status || (delivery ? (delivery.confirmation_date ? 'confirmed' : 'delivered') : 'dispatched');
    const statusMap = {
        'prepared': { color: 'blue', label: 'Requisition Prepared' },
        'dispatched': { color: 'amber', label: 'Sent to Courier' },
        'in_transit': { color: 'indigo', label: 'In Transit' },
        'delivered': { color: 'emerald', label: 'Gate Entry Done' },
        'confirmed': { color: 'emerald', label: 'Fully Confirmed' },
        'returned': { color: 'rose', label: 'Returning Inbound' }
    };
    const st = statusMap[actualStatus] || { color: 'gray', label: actualStatus };

    const metrics = [
        { label: 'Operational Status', value: `<div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-${st.color}-500 animate-pulse"></span><span class="text-sm font-bold text-gray-900 uppercase tracking-tight">${st.label}</span></div>` },
        { label: 'Associated Request', value: `REQ#${dispatch.material_request_id || 'EXT-00'}` },
        { label: 'Dispatch Timestamp', value: formatDate(dispatch.dispatch_date, true) },
        { label: 'Manifest Volume', value: `${dispatch.items ? dispatch.items.length : 0} <span class="text-gray-400">Inventory Units</span>` }
    ];

    document.getElementById('metricsGrid').innerHTML = metrics.map(m => `
        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">${m.label}</p>
            <div class="text-sm font-bold text-gray-900">${m.value}</div>
        </div>
    `).join('');
}

function renderLogisticsCard(dispatch) {
    document.getElementById('logisticsCard').innerHTML = `
        <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-200">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Logistics Strategy</h3>
        </div>
        <div class="p-6 space-y-4">
            ${renderRow('Destination Site', dispatch.site_code || 'N/A')}
            ${renderRow('Contractor Partner', `<span class="text-indigo-600">${dispatch.vendor_company_name || (dispatch.vendor_name || 'Corporate Logistics')}</span>`)}
            ${renderRow('Expected Arrival', `<span class="text-rose-600">${formatDate(dispatch.expected_delivery_date) || '--'}</span>`)}
            ${renderRow('Carrier Body', dispatch.courier_name || 'Internal Transit')}
            ${renderRow('Tracking Ledger', `<span class="text-blue-600 font-mono">${dispatch.tracking_number || 'NOT_ASSIGNED'}</span>`)}
        </div>
    `;
}

function renderContactCard(dispatch) {
    document.getElementById('contactCard').innerHTML = `
        <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-200">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Recovery & Contact</h3>
        </div>
        <div class="p-6 space-y-4">
            ${renderRow('PoC Identity', dispatch.contact_person_name || 'N/A')}
            ${renderRow('Response Line', dispatch.contact_person_phone || '--')}
            <div class="pt-2">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-tight block mb-2">Primary Delivery Node</span>
                <div class="bg-gray-50 p-4 rounded-xl text-sm font-semibold text-gray-600 leading-relaxed italic border border-gray-100">
                    ${(dispatch.delivery_address || '').replace(/\n/g, '<br>')}
                </div>
            </div>
        </div>
    `;
}

function renderDeliveryConfirmation(delivery, originalAddress) {
    document.getElementById('deliveryConfirmationSection').innerHTML = `
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <svg class="w-5 h-5 text-emerald-600 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                Delivery Confirmation
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                ${delivery.delivery_date ? `
                <div class="bg-emerald-50 p-4 rounded-lg">
                    <div class="flex items-center"><span class="text-sm font-medium text-emerald-800">Delivery Date</span></div>
                    <p class="mt-2 text-lg font-semibold text-emerald-900">
                        ${formatDate(delivery.delivery_date)}
                        ${delivery.delivery_time ? `<span class="text-sm font-normal">at ${delivery.delivery_time}</span>` : ''}
                    </p>
                </div>` : ''}
                
                ${delivery.received_by ? `
                <div class="bg-blue-50 p-4 rounded-lg">
                    <div class="flex items-center"><span class="text-sm font-medium text-blue-800">Received By</span></div>
                    <p class="mt-2 text-lg font-semibold text-blue-900">${delivery.received_by}</p>
                    ${delivery.received_by_phone ? `<p class="text-sm text-blue-700">${delivery.received_by_phone}</p>` : ''}
                </div>` : ''}
                
                ${delivery.confirmation_date ? `
                <div class="bg-purple-50 p-4 rounded-lg">
                    <div class="flex items-center"><span class="text-sm font-medium text-purple-800">Confirmed</span></div>
                    <p class="mt-2 text-lg font-semibold text-purple-900">${formatDate(delivery.confirmation_date, true)}</p>
                </div>` : ''}
            </div>
            
            ${delivery.actual_delivery_address && delivery.actual_delivery_address !== originalAddress ? `
            <div class="mt-6 p-4 bg-yellow-50 rounded-lg">
                <h4 class="text-sm font-medium text-yellow-800 mb-2">Actual Delivery Address</h4>
                <p class="text-sm text-yellow-700">${delivery.actual_delivery_address.replace(/\n/g, '<br>')}</p>
            </div>` : ''}
            
            ${delivery.delivery_notes ? `
            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                <h4 class="text-sm font-medium text-gray-800 mb-2">Delivery Notes</h4>
                <p class="text-sm text-gray-700">${delivery.delivery_notes.replace(/\n/g, '<br>')}</p>
            </div>` : ''}
        </div>
    </div>`;
}

function renderDocuments(delivery) {
    const docs = [];
    if (delivery.lr_copy_path) {
        docs.push({ name: 'LR Copy', type: 'Delivery Receipt', path: delivery.lr_copy_path, color: 'red' });
    }
    if (delivery.additional_documents && Array.isArray(delivery.additional_documents)) {
        delivery.additional_documents.forEach((doc, idx) => {
            if (typeof doc === 'string') docs.push({ name: `Document ${idx + 1}`, type: 'Additional Document', path: doc, color: 'blue' });
            else docs.push({ name: doc.name || `Document ${idx+1}`, type: doc.type || 'Additional Document', path: doc.path, color: 'blue' });
        });
    }

    document.getElementById('documentsSection').innerHTML = `
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <svg class="w-5 h-5 text-indigo-600 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path></svg>
                Documents & Attachments
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                ${docs.map(doc => `
                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center">
                        <svg class="w-8 h-8 text-${doc.color}-600 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path></svg>
                        <div><p class="text-sm font-medium text-gray-900">${doc.name}</p><p class="text-xs text-gray-500">${doc.type}</p></div>
                    </div>
                    <div class="mt-3">
                        <a href="../../../${doc.path}" target="_blank" class="text-sm text-blue-600 hover:text-blue-800 font-medium">View Document →</a>
                    </div>
                </div>`).join('')}
                ${docs.length === 0 ? '<div class="col-span-full text-center py-8 text-gray-500">No documents uploaded</div>' : ''}
            </div>
        </div>
    </div>`;
}

function renderManifest(items) {
    let totalValue = 0;
    const rows = (items || []).map((item, idx) => {
        totalValue += parseFloat(item.total_cost || 0);
        return `
            <tr class="hover:bg-gray-50/50 transition-colors">
                <td class="px-6 py-4 text-xs font-bold text-gray-400">${idx + 1}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 mr-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <div><div class="text-xs font-bold text-gray-900">${item.item_name}</div><div class="text-[10px] font-bold text-gray-400 uppercase tracking-tight">${item.item_code}</div></div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-xs font-bold text-gray-900">${formatNumber(item.quantity_dispatched)} <span class="text-gray-400 font-medium">${item.unit}</span></div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-xs font-bold text-gray-900">₹${formatCurrency(item.unit_cost)}</div>
                    <div class="text-[10px] font-bold text-emerald-600 mt-0.5">TTL: ₹${formatCurrency(item.total_cost)}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    ${item.serial_numbers ? `<div class="text-[10px] font-bold text-blue-600 font-mono uppercase truncate max-w-[120px]" title="${item.serial_numbers}">SN: ${item.serial_numbers}</div>` : ''}
                    ${item.batch_number ? `<div class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter mt-1">BTCH: ${item.batch_number}</div>` : ''}
                </td>
                <td class="px-6 py-4 whitespace-nowrap"><span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-widest bg-blue-50 text-blue-700 border border-blue-100">SENT: ${(item.item_condition || 'N/A').toUpperCase()}</span></td>
                <td class="px-6 py-4 text-[10px] text-gray-500 font-medium">${item.remarks || '--'}</td>
            </tr>`;
    }).join('');

    document.getElementById('manifestSection').innerHTML = `
        <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-200">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest" id="manifestTitlePlaceholder">Material Manifest</h3>
            <p id="manifestDescription" class="text-[10px] text-gray-400 mt-1"></p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-left w-12">#</th>
                        <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-left">Item Intelligence</th>
                        <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-left">Sent / Recv</th>
                        <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-left">Valuation</th>
                        <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-left">Logistics Meta</th>
                        <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-left">Condition</th>
                        <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-left">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    ${rows || '<tr><td colspan="7" class="px-6 py-12 text-center text-gray-500">No items found</td></tr>'}
                </tbody>
                <tfoot class="bg-gray-50/50">
                    <tr><td colspan="3" class="px-6 py-4 text-right text-[11px] font-bold text-gray-400 uppercase tracking-widest">Total Valuation</td><td class="px-6 py-4 text-sm font-bold text-emerald-600">₹${formatCurrency(totalValue)}</td><td colspan="3"></td></tr>
                </tfoot>
            </table>
        </div>`;
}

// Helpers
function formatDate(dateStr, includeTime = false) {
    if (!dateStr) return null;
    const date = new Date(dateStr);
    const options = { day: '2-digit', month: 'short', year: 'numeric' };
    if (includeTime) {
        options.hour = '2-digit';
        options.minute = '2-digit';
        options.hour12 = true;
    }
    return date.toLocaleDateString('en-GB', options);
}

function renderRow(label, value) {
    return `<div class="flex justify-between items-center py-1">
        <span class="text-[11px] font-bold text-gray-400 uppercase tracking-tight">${label}</span>
        <span class="text-sm font-bold text-gray-900">${value}</span>
    </div>`;
}

function formatCurrency(val) {
    return parseFloat(val || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatNumber(val) {
    return parseFloat(val || 0).toLocaleString('en-IN');
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../includes/admin_layout.php';
?>