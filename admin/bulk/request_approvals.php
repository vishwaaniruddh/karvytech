<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/MaterialRequest.php';

// Auth::requireRole(ADMIN_ROLE);

$title = 'Material Request Approvals';
ob_start();
?>

<div class="min-h-screen bg-transparent pb-12">
    <!-- Header Area -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                        <div class="p-2 bg-indigo-600 rounded-lg text-white shadow-lg shadow-indigo-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        Request Approval Hub
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">Review and process field material requisitions</p>
                </div>
                <div class="flex items-center gap-3">
                    <button onclick="bulkApprove()" class="px-6 py-2.5 bg-emerald-600 text-white text-sm font-bold rounded-xl hover:bg-emerald-700 transition-all shadow-md active:scale-95">Bulk Approve</button>
                    <button onclick="bulkReject()" class="px-6 py-2.5 bg-rose-600 text-white text-sm font-bold rounded-xl hover:bg-rose-700 transition-all shadow-md active:scale-95">Bulk Reject</button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto px-4 mt-8">
        <div class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-8 py-5 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <h3 class="font-black text-gray-900 tracking-tight text-sm uppercase">Verification Queue</h3>
                    <span id="pending-count" class="px-2.5 py-1 bg-white border border-gray-200 text-indigo-600 text-[10px] font-black rounded-lg shadow-sm">0</span>
                </div>
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <input type="text" id="req-search" onkeyup="filterRequests()" placeholder="Search site or vendor..." class="pl-9 pr-4 py-2 border border-gray-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-indigo-500 outline-none w-64">
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto min-h-[500px]">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-white border-b">
                        <tr>
                            <th class="px-8 py-5 text-[10px] font-bold text-gray-400 uppercase tracking-widest"><input type="checkbox" id="select-all" class="rounded text-indigo-600"></th>
                            <th class="px-8 py-5 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Requisition</th>
                            <th class="px-8 py-5 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Requester</th>
                            <th class="px-8 py-5 text-[10px] font-bold text-gray-400 uppercase tracking-widest w-1/3">Bill of Materials</th>
                            <th class="px-8 py-5 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center">Process</th>
                        </tr>
                    </thead>
                    <tbody id="request-list-body" class="divide-y divide-gray-50">
                        <!-- Loaded via JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', loadRequests);

async function loadRequests() {
    const body = document.getElementById('request-list-body');
    body.innerHTML = '<tr><td colspan="5" class="text-center py-32 text-gray-400 font-bold tracking-widest text-xs uppercase animate-pulse">Establishing secure connection to queue...</td></tr>';
    
    try {
        const response = await fetch('api/material_requests.php?action=get_pending');
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('pending-count').textContent = data.requests.length;
            if (data.requests.length === 0) {
                body.innerHTML = '<tr><td colspan="5" class="text-center py-32 text-gray-400 italic">Queue clear. No pending requisitions at this moment.</td></tr>';
                return;
            }
            
            body.innerHTML = data.requests.map(r => `
                <tr class="hover:bg-gray-50/80 transition-all group">
                    <td class="px-8 py-6"><input type="checkbox" class="req-checkbox rounded" value="${r.id}"></td>
                    <td class="px-8 py-6">
                        <div class="flex flex-col">
                            <span class="text-[10px] font-black text-indigo-400 space-x-1 mb-1 tracking-tighter uppercase">RQ-${String(r.id).padStart(5, '0')}</span>
                            <span class="text-sm font-black text-gray-900 mb-0.5 tracking-tight">${r.site_code}</span>
                            <span class="text-[10px] font-bold text-gray-400 max-w-[150px] truncate leading-tight">${r.location || 'Site Location Undefined'}</span>
                        </div>
                    </td>
                    <td class="px-8 py-6">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center font-black text-xs uppercase shadow-sm">${(r.vendor_name || 'G')[0]}</div>
                            <div class="flex flex-col">
                                <span class="text-xs font-black text-gray-900">${r.vendor_name || 'Internal Requisition'}</span>
                                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">${r.vendor_company_name || 'Corporate Head Office'}</span>
                            </div>
                        </div>
                    </td>
                    <td class="px-8 py-6">
                        <div class="p-4 bg-gray-50/50 rounded-2xl border border-gray-100/50 space-y-2">
                            ${parseItems(r.items).map(i => `
                                <div class="flex items-center justify-between gap-6">
                                    <div class="flex flex-col">
                                        <span class="text-[10px] font-black text-gray-800 uppercase leading-none">${i.material_name}</span>
                                        <span class="text-[8px] font-bold text-gray-400 uppercase tracking-tighter">${i.item_code || 'N/A'}</span>
                                    </div>
                                    <span class="px-2 py-0.5 bg-white border border-gray-200 text-[10px] font-black text-indigo-600 rounded-lg shadow-sm tabular-nums">${i.quantity} ${i.unit}</span>
                                </div>
                            `).join('')}
                        </div>
                    </td>
                    <td class="px-8 py-6 text-center">
                        <div class="flex items-center justify-center gap-2">
                             <button onclick="processRequest(${r.id}, 'approved')" class="p-2.5 bg-emerald-50 text-emerald-600 rounded-xl hover:bg-emerald-600 hover:text-white transition-all shadow-sm" title="Authorize"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></button>
                             <button onclick="processRequest(${r.id}, 'rejected')" class="p-2.5 bg-rose-50 text-rose-600 rounded-xl hover:bg-rose-600 hover:text-white transition-all shadow-sm" title="Revoke"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }
    } catch (err) { console.error(err); }
}

function parseItems(j) { try { return JSON.parse(j) || []; } catch(e) { return []; } }

async function processRequest(id, status) {
    const { value: remarks } = await Swal.fire({
        title: status === 'approved' ? 'Authorize Requisition' : 'Revoke Requisition',
        text: `Are you sure you want to mark this request as ${status}?`,
        input: 'textarea',
        inputPlaceholder: 'Enter review remarks...',
        confirmButtonColor: status === 'approved' ? '#10b981' : '#e11d48',
        confirmButtonText: status === 'approved' ? 'Confirm Approval' : 'Confirm Rejection',
        showCancelButton: true,
        background: '#fff',
        customClass: { popup: 'rounded-3xl', confirmButton: 'rounded-xl px-6 py-3 font-bold', cancelButton: 'rounded-xl px-6 py-3 font-bold' }
    });

    if (remarks !== undefined) {
        try {
            const response = await fetch('api/material_requests.php?action=process', {
                method: 'POST',
                body: JSON.stringify({ id, status, remarks }),
                headers: { 'Content-Type': 'application/json' }
            });
            const data = await response.json();
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Processed', text: `Requisition finalized successfully`, timer: 2000, showConfirmButton: false });
                loadRequests();
            }
        } catch(e) {}
    }
}
</script>

<?php
$content = ob_get_clean();
if (isset($_GET['ajax'])) {
    echo $content;
} else {
    require_once __DIR__ . '/../../includes/admin_layout.php';
}
?>
