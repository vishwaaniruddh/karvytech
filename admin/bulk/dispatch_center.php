<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/MaterialRequest.php';

// Auth::requireRole(ADMIN_ROLE);

$title = 'Logistics Dispatch Center';
ob_start();
?>

<div class="min-h-screen bg-transparent pb-12">
    <!-- Header Area -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                        <div class="p-2 bg-slate-900 rounded-lg text-white shadow-lg shadow-gray-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        </div>
                        Central Dispatch Center
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">Manage outbound logistics for authorized material requests</p>
                </div>
                <div class="flex gap-4">
                     <div class="bg-gray-100 p-1 rounded-2xl flex border border-gray-200">
                         <span class="px-5 py-2 bg-white text-slate-900 text-xs font-black rounded-xl shadow-sm">Ready to Ship</span>
                         <span class="px-5 py-2 text-gray-400 text-xs font-black">In Transit</span>
                         <span class="px-5 py-2 text-gray-400 text-xs font-black">Delivered</span>
                     </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto px-4 mt-8">
        <div class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-8 py-6 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                <h3 class="font-black text-gray-900 tracking-tight text-sm uppercase">Fulfillment Queue</h3>
            </div>
            <div class="overflow-x-auto min-h-[500px]">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-white border-b">
                        <tr>
                            <th class="px-8 py-5 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Tracking ID</th>
                            <th class="px-8 py-5 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Site Manifest</th>
                            <th class="px-8 py-5 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Consignee</th>
                            <th class="px-8 py-5 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Status</th>
                            <th class="px-8 py-5 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center">Fulfillment</th>
                        </tr>
                    </thead>
                    <tbody id="dispatch-list-body" class="divide-y divide-gray-50">
                        <!-- JS Loaded -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', loadDispatchQueue);

async function loadDispatchQueue() {
    const body = document.getElementById('dispatch-list-body');
    body.innerHTML = '<tr><td colspan="5" class="text-center py-32 text-gray-400 font-bold uppercase tracking-widest text-xs animate-pulse">Syncing with logistics server...</td></tr>';
    
    try {
        const response = await fetch('api/material_requests.php?action=get_approved');
        const data = await response.json();
        
        if (data.success) {
            if (data.requests.length === 0) {
                body.innerHTML = '<tr><td colspan="5" class="text-center py-32 text-gray-400 italic">No pending dispatches found. All field requisitions are currently in transit or fulfilled.</td></tr>';
                return;
            }
            
            body.innerHTML = data.requests.map(r => `
                <tr class="hover:bg-gray-50/80 transition-all">
                    <td class="px-8 py-6">
                        <span class="text-[10px] font-black font-mono text-gray-400 uppercase tracking-tighter">REQ-${String(r.id).padStart(5, '0')}</span>
                    </td>
                    <td class="px-8 py-6">
                        <div class="flex flex-col">
                            <span class="text-sm font-black text-emerald-700 tracking-tight">${r.site_code}</span>
                            <span class="text-[10px] font-bold text-gray-400 truncate max-w-[200px] leading-tight">${r.location}</span>
                        </div>
                    </td>
                    <td class="px-8 py-6">
                        <div class="flex flex-col">
                            <span class="text-xs font-black text-gray-900">${r.vendor_name}</span>
                            <span class="text-[9px] font-black text-indigo-500 uppercase tracking-widest">Authorized Site Partner</span>
                        </div>
                    </td>
                    <td class="px-8 py-6">
                        <div class="flex items-center gap-2">
                             <div class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></div>
                             <span class="px-3 py-1 bg-blue-50 text-blue-700 text-[10px] font-black rounded-lg ring-1 ring-blue-100 uppercase tracking-widest">${r.status}</span>
                        </div>
                    </td>
                    <td class="px-8 py-6 text-center">
                        <button onclick="openDispatchModal(${r.id})" class="px-6 py-2.5 bg-slate-900 text-white text-[10px] font-black rounded-xl hover:bg-black transition-all shadow-md active:scale-95 uppercase tracking-widest">
                            Initiate Shipping
                        </button>
                    </td>
                </tr>
            `).join('');
        }
    } catch (err) { console.error(err); }
}

async function openDispatchModal(id) {
    const { value: v } = await Swal.fire({
        title: 'Dispatch Documentation',
        html: `
            <div class="p-6 space-y-6 text-left">
                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest mb-2 block">Logistics Partner</label>
                    <input id="c" class="w-full px-4 py-3 border border-gray-200 rounded-xl font-bold text-sm focus:ring-2 focus:ring-slate-900 outline-none" placeholder="e.g. Blue Dart, Delhivery...">
                </div>
                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest mb-2 block">AWB / Tracking Reference</label>
                    <input id="t" class="w-full px-4 py-3 border border-gray-200 rounded-xl font-bold text-sm focus:ring-2 focus:ring-slate-900 outline-none" placeholder="Enter tracking ID">
                </div>
                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest mb-2 block">Effective Dispatch Date</label>
                    <input id="d" type="date" class="w-full px-4 py-3 border border-gray-200 rounded-xl font-bold text-sm focus:ring-2 focus:ring-slate-900 outline-none" value="${new Date().toISOString().split('T')[0]}">
                </div>
            </div>
        `,
        confirmButtonText: 'Finalize Shipping',
        confirmButtonColor: '#0f172a',
        showCancelButton: true,
        background: '#fff',
        customClass: { popup: 'rounded-3xl', confirmButton: 'rounded-xl px-8 py-4', cancelButton: 'rounded-xl px-8 py-4 font-bold' },
        preConfirm: () => {
            const courier = document.getElementById('c').value;
            const tracking = document.getElementById('t').value;
            if (!courier || !tracking) { Swal.showValidationMessage('Logistics data is mandatory for dispatch'); return false; }
            return { courier_name: courier, tracking_number: tracking, dispatch_date: document.getElementById('d').value }
        }
    });

    if (v) {
        try {
            const res = await fetch('api/material_requests.php?action=dispatch', { 
                method: 'POST', 
                body: JSON.stringify({ id, ...v }), 
                headers: { 'Content-Type': 'application/json' } 
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Manifest Created', text: 'Shipping documentation finalized', showConfirmButton: false, timer: 2000 });
                loadDispatchQueue();
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
