<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/MaterialRequest.php';
require_once __DIR__ . '/../models/Inventory.php';
require_once __DIR__ . '/../models/BoqItem.php';

// Require vendor authentication
Auth::requireVendor();

$vendorId = Auth::getVendorId();

$materialRequestModel = new MaterialRequest();
$inventoryModel = new Inventory();

// Get received materials for this vendor
$receivedMaterials = $inventoryModel->getReceivedMaterialsForVendor($vendorId);

// Group materials by dispatch
$dispatchGroups = [];
foreach ($receivedMaterials as $material) {
    if (!isset($dispatchGroups[$material['id']])) {
        $dispatchGroups[$material['id']] = [
            'dispatch_info' => $material,
            'items' => $inventoryModel->getDispatchItemsSummary($material['id'])
        ];
    }
}

// Calculate statistics
$stats = [
    'pending' => 0,
    'delivered' => 0,
    'confirmed' => 0,
    'total_items' => 0
];

foreach ($dispatchGroups as $group) {
    $status = $group['dispatch_info']['dispatch_status'] ?? 'delivered';
    if ($status === 'dispatched') $stats['pending']++;
    elseif ($status === 'delivered') $stats['delivered']++;
    elseif ($status === 'confirmed') $stats['confirmed']++;
    
    foreach($group['items'] as $item) {
        $stats['total_items'] += $item['quantity_dispatched'];
    }
}

$title = 'Track Received Materials';
ob_start();
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Technical Manifests</h1>
            <p class="mt-1 text-sm text-gray-500 font-bold uppercase tracking-widest opacity-60">Visual audit of all materials dispatched to your organization.</p>
        </div>
        <div class="flex items-center gap-3">
             <a href="inventory/index.php" class="inline-flex items-center px-5 py-2.5 bg-blue-600 text-white text-sm font-bold rounded-2xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                Table View
            </a>
            <a href="dashboard.php" class="inline-flex items-center px-5 py-2.5 bg-white text-gray-700 text-sm font-bold rounded-2xl border border-gray-200 hover:bg-gray-50 transition-all shadow-sm">
                Dashboard
            </a>
        </div>
    </div>

    <!-- Rapid Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
        <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3">Pending Action</p>
            <div class="flex items-center justify-between">
                <span class="text-3xl font-black text-gray-900"><?php echo $stats['pending']; ?></span>
                <div class="w-10 h-10 bg-amber-50 text-amber-500 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3">Delivered</p>
            <div class="flex items-center justify-between">
                <span class="text-3xl font-black text-gray-900"><?php echo $stats['delivered']; ?></span>
                <div class="w-10 h-10 bg-blue-50 text-blue-500 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3">Confirmed</p>
            <div class="flex items-center justify-between">
                <span class="text-3xl font-black text-gray-900"><?php echo $stats['confirmed']; ?></span>
                <div class="w-10 h-10 bg-green-50 text-green-500 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3">Total Volume</p>
            <div class="flex items-center justify-between">
                <span class="text-3xl font-black text-gray-900"><?php echo number_format($stats['total_items']); ?></span>
                <div class="w-10 h-10 bg-purple-50 text-purple-500 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Dispatch Feed -->
    <?php if (empty($dispatchGroups)): ?>
    <div class="bg-white rounded-[3rem] border border-gray-100 p-20 text-center">
        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m13-8V4a1 1 0 00-1-1H7a1 1 0 00-1 1v1m8 0V4a1 1 0 00-1-1H9a1 1 0 00-1 1v1"></path></svg>
        </div>
        <h3 class="text-lg font-black text-gray-900 uppercase tracking-tight">Empty Manifest Archive</h3>
        <p class="text-sm text-gray-400 mt-2 max-w-xs mx-auto font-bold uppercase tracking-widest text-[10px]">No materials have been flagged for your organization yet.</p>
    </div>
    <?php else: ?>
    <div class="space-y-8">
        <?php foreach ($dispatchGroups as $dispatchId => $group): 
            $dispatch = $group['dispatch_info'];
            $items = $group['items'];
            $status = $dispatch['dispatch_status'] ?? 'delivered';
            
            $statusMap = [
                'dispatched' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'border' => 'border-amber-100', 'label' => 'Pending Action'],
                'delivered' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'border' => 'border-blue-100', 'label' => 'Received'],
                'confirmed' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'border' => 'border-emerald-100', 'label' => 'Audited']
            ];
            $currentStatus = $statusMap[$status] ?? $statusMap['delivered'];
        ?>
        
        <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden hover:shadow-md transition-shadow">
            <div class="px-8 py-6 border-b border-gray-50 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-5">
                    <div class="w-14 h-14 bg-gray-50 rounded-2xl flex items-center justify-center shadow-inner">
                         <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-3">
                            <h3 class="text-xl font-black text-gray-900 tracking-tight">Receipt #<?php echo htmlspecialchars($dispatch['dispatch_number']); ?></h3>
                            <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest <?php echo $currentStatus['bg'] . ' ' . $currentStatus['text'] . ' border ' . $currentStatus['border']; ?>">
                                <?php echo $currentStatus['label']; ?>
                            </span>
                        </div>
                        <div class="flex items-center gap-4 mt-1.5 text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                            <span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg> <?php echo htmlspecialchars($dispatch['site_code'] ?? 'GENERAL'); ?></span>
                            <span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg> <?php echo date('d M Y', strtotime($dispatch['dispatch_date'])); ?></span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                     <div class="text-right">
                        <p class="text-[10px] font-black text-gray-300 uppercase tracking-widest">Manifest Volume</p>
                        <p class="text-lg font-black text-gray-900"><?php echo count($items); ?> Categories</p>
                    </div>
                    <a href="material-received-from-admin.php?id=<?php echo $dispatchId; ?>" class="p-3 bg-gray-50 hover:bg-gray-100 rounded-2xl transition-all border border-gray-100">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>
            </div>
            
            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php foreach ($items as $item): ?>
                    <div class="bg-gray-50/50 p-6 rounded-3xl border border-gray-100 hover:border-blue-100 transition-all group">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-10 h-10 bg-white rounded-xl shadow-sm flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-all text-blue-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            </div>
                            <div class="text-right">
                                <span class="text-2xl font-black text-gray-900"><?php echo $item['quantity_dispatched']; ?></span>
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest"><?php echo htmlspecialchars($item['unit'] ?? 'Units'); ?></p>
                            </div>
                        </div>
                        <h4 class="text-sm font-black text-gray-900 truncate tracking-tight"><?php echo htmlspecialchars($item['item_name']); ?></h4>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1"><?php echo htmlspecialchars($item['item_code']); ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($status === 'dispatched'): ?>
                <div class="mt-8 pt-8 border-t border-gray-50 flex items-center justify-between">
                    <p class="text-[10px] font-bold text-amber-500 uppercase tracking-widest flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Pending physical verification & acknowledgment
                    </p>
                    <button onclick="acceptDispatch(<?php echo $dispatchId; ?>)" class="px-6 py-3 bg-emerald-600 text-white text-xs font-black uppercase tracking-[0.1em] rounded-2xl hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-100">
                        Confirm Receipt
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<script>
function acceptDispatch(dispatchId) {
    if (!confirm('Confirm that you have physically verified all materials in this shipment?')) return;
    
    fetch('process-material-acceptance.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ dispatch_id: dispatchId, action: 'accept_all' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Manifest audited successfully!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Verification service temporarily unavailable.');
    });
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../includes/vendor_layout.php';
?>
