<?php
require_once '../config/auth.php';
require_once '../config/database.php';
require_once '../models/Site.php';
require_once '../models/Vendor.php';
require_once '../models/SiteSurvey.php';
require_once '../models/Inventory.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$title = 'Mission Control | Operational Pulse';
$currentUser = Auth::getCurrentUser();
$db = Database::getInstance()->getConnection();

/**
 * Robust Data Fetcher
 * Prevents warnings by returning defaults on failure
 */
function fetchMetrics($db) {
    $m = [];
    try {
        // Core Counts
        $m['total_sites'] = $db->query("SELECT COUNT(*) FROM sites WHERE deleted_at IS NULL")->fetchColumn() ?: 0;
        $m['total_vendors'] = $db->query("SELECT COUNT(*) FROM vendors WHERE status = 'active'")->fetchColumn() ?: 0;
        $m['active_requests'] = $db->query("SELECT COUNT(*) FROM material_requests WHERE status IN ('pending', 'approved')")->fetchColumn() ?: 0;
        $m['low_stock'] = $db->query("SELECT COUNT(*) FROM inventory_summary WHERE total_stock < 10")->fetchColumn() ?: 0;
        
        // Financials
        $m['stock_value'] = $db->query("SELECT SUM(total_value) FROM inventory_summary")->fetchColumn() ?: 0;
        $m['dispatched_value'] = $db->query("SELECT SUM(dispatched_stock * avg_unit_cost) FROM inventory_summary")->fetchColumn() ?: 0;

        // Site Status
        $stmt = $db->query("SELECT 
            SUM(CASE WHEN is_delegate = 0 THEN 1 ELSE 0 END) as unassigned,
            SUM(CASE WHEN is_delegate = 1 AND installation_status = 0 THEN 1 ELSE 0 END) as in_progress,
            SUM(CASE WHEN installation_status = 1 THEN 1 ELSE 0 END) as completed
            FROM sites WHERE deleted_at IS NULL");
        $m['site_pulse'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['unassigned'=>0, 'in_progress'=>0, 'completed'=>0];

        // Installation Health
        $stmt = $db->query("SELECT status, COUNT(*) as count FROM installation_delegations GROUP BY status");
        $m['install_health'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];

        // Territory Distribution
        $m['territory'] = $db->query("SELECT st.name as state, COUNT(s.id) as count 
                                     FROM sites s 
                                     JOIN states st ON s.state_id = st.id 
                                     WHERE s.deleted_at IS NULL
                                     GROUP BY st.name ORDER BY count DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

        // System Categories
        $m['systems'] = $db->query("SELECT category, COUNT(*) as count FROM boq_items GROUP BY category ORDER BY count DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

        // Vendor Workload
        $m['vendor_workload'] = $db->query("SELECT v.name, COUNT(sd.id) as sites 
                                          FROM vendors v 
                                          JOIN site_delegations sd ON v.id = sd.vendor_id 
                                          WHERE sd.status = 'active'
                                          GROUP BY v.id ORDER BY sites DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

        // Recent Ingestions
        $m['recent_receipts'] = $db->query("SELECT receipt_number, supplier_name, total_amount, receipt_date FROM inventory_inwards ORDER BY created_at DESC LIMIT 4")->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        error_log("Dashboard 3 Metrics Error: " . $e->getMessage());
    }
    return $m;
}

$metrics = fetchMetrics($db);
ob_start();
?>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f9fafb; color: #111827; }
    .glass { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.3); }
    .stat-card { transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease; }
    .stat-card:hover { transform: translateY(-8px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02); }
    .progress-ring { transition: stroke-dashoffset 1s ease-in-out; }
</style>

<div class="max-w-full mx-auto px-6 py-10">
    <!-- Section: Operational Hero -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-12 gap-8">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <span class="px-3 py-1 bg-indigo-50 text-indigo-700 rounded-full text-[10px] font-black uppercase tracking-widest border border-indigo-100 italic">V3 Intelligence</span>
                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-ping"></span>
            </div>
            <h1 class="text-5xl font-extrabold tracking-tight text-slate-900 leading-none">Operational <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600">Command</span></h1>
            <p class="text-slate-400 mt-4 text-sm font-medium">Synthesizing data from <?php echo $metrics['total_sites'] ?? 0; ?> active sites and <?php echo $metrics['total_vendors'] ?? 0; ?> strategic partners.</p>
        </div>
        
        <div class="flex items-center gap-4 bg-white p-3 rounded-3xl shadow-sm border border-slate-100">
            <div class="text-right px-4 border-r border-slate-100">
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Global Asset Value</div>
                <div class="text-xl font-extrabold text-slate-900">₹<?php echo number_format($metrics['stock_value'] / 1000000, 2); ?>M</div>
            </div>
            <button onclick="location.reload()" class="p-4 bg-white border border-slate-200 rounded-2xl text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:bg-indigo-50 transition-all active:scale-90 group">
                <svg class="w-6 h-6 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </button>
        </div>
    </div>

    <!-- Section: Real-time KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-16">
        <!-- Site Deployment -->
        <div class="glass stat-card p-8 rounded-[2.5rem] relative overflow-hidden group">
            <div class="absolute -top-10 -right-10 w-40 h-40 bg-blue-100/30 rounded-full blur-3xl group-hover:bg-blue-200/50 transition-colors"></div>
            <div class="flex items-center justify-between mb-8">
                <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center shadow-sm text-blue-600 border border-blue-50">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Deployments</span>
            </div>
            <div class="text-5xl font-black text-slate-900 mb-2"><?php echo $metrics['total_sites'] ?? 0; ?></div>
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                <span class="text-xs font-bold text-slate-500"><?php echo $metrics['site_pulse']['completed'] ?? 0; ?> Live Nodes</span>
            </div>
        </div>

        <!-- Material Pipeline -->
        <div class="glass stat-card p-8 rounded-[2.5rem] relative overflow-hidden group">
            <div class="absolute -top-10 -right-10 w-40 h-40 bg-orange-100/30 rounded-full blur-3xl group-hover:bg-orange-200/50 transition-colors"></div>
            <div class="flex items-center justify-between mb-8">
                <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center shadow-sm text-orange-600 border border-orange-50">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Inventory</span>
            </div>
            <div class="text-5xl font-black text-slate-900 mb-2"><?php echo $metrics['active_requests'] ?? 0; ?></div>
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 bg-rose-50 text-rose-600 rounded text-[10px] font-bold"><?php echo $metrics['low_stock'] ?? 0; ?> Low Stock SKU</span>
            </div>
        </div>

        <!-- Fulfillment Speed -->
        <div class="glass stat-card p-8 rounded-[2.5rem] relative overflow-hidden group">
            <div class="absolute -top-10 -right-10 w-40 h-40 bg-purple-100/30 rounded-full blur-3xl group-hover:bg-purple-200/50 transition-colors"></div>
            <div class="flex items-center justify-between mb-8">
                <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center shadow-sm text-purple-600 border border-purple-50">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Operations</span>
            </div>
            <div class="text-5xl font-black text-slate-900 mb-2"><?php echo count($metrics['install_health']); ?></div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-500">Pipeline Status Channels</span>
            </div>
        </div>

        <!-- Logistics Reach -->
        <div class="glass stat-card p-8 rounded-[2.5rem] relative overflow-hidden group">
            <div class="absolute -top-10 -right-10 w-40 h-40 bg-indigo-100/30 rounded-full blur-3xl group-hover:bg-indigo-200/50 transition-colors"></div>
            <div class="flex items-center justify-between mb-8">
                <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center shadow-sm text-indigo-600 border border-indigo-50">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Partners</span>
            </div>
            <div class="text-5xl font-black text-slate-900 mb-2"><?php echo $metrics['total_vendors'] ?? 0; ?></div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-500">Active Supply Chain</span>
            </div>
        </div>
    </div>

    <!-- Section: Advanced Analytics Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 mb-16">
        <!-- Site Lifecycle Analytics -->
        <div class="lg:col-span-2 glass rounded-[3rem] p-10 shadow-sm border border-slate-200/50">
            <div class="flex items-center justify-between mb-12">
                <h3 class="text-2xl font-extrabold text-slate-900 tracking-tight">Installation Pipeline Health</h3>
                <div class="flex gap-2">
                    <button class="px-4 py-2 bg-slate-100 text-slate-600 text-[10px] font-black rounded-xl uppercase tracking-widest hover:bg-slate-200">Daily</button>
                    <button class="px-4 py-2 bg-slate-900 text-white text-[10px] font-black rounded-xl uppercase tracking-widest">Weekly</button>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                <div class="p-6 bg-slate-50/50 rounded-3xl border border-slate-100">
                    <div class="text-[10px] font-black text-slate-400 uppercase mb-2">Unassigned</div>
                    <div class="text-3xl font-black text-slate-900"><?php echo $metrics['site_pulse']['unassigned'] ?? 0; ?></div>
                    <div class="w-full bg-slate-200 h-1 rounded-full mt-4 overflow-hidden">
                        <div class="bg-indigo-500 h-full" style="width: <?php echo ($metrics['total_sites'] > 0 ? ($metrics['site_pulse']['unassigned'] / $metrics['total_sites']) * 100 : 0); ?>%"></div>
                    </div>
                </div>
                <div class="p-6 bg-slate-50/50 rounded-3xl border border-slate-100">
                    <div class="text-[10px] font-black text-slate-400 uppercase mb-2">In Progress</div>
                    <div class="text-3xl font-black text-slate-900"><?php echo $metrics['site_pulse']['in_progress'] ?? 0; ?></div>
                    <div class="w-full bg-slate-200 h-1 rounded-full mt-4 overflow-hidden">
                        <div class="bg-amber-500 h-full" style="width: <?php echo ($metrics['total_sites'] > 0 ? ($metrics['site_pulse']['in_progress'] / $metrics['total_sites']) * 100 : 0); ?>%"></div>
                    </div>
                </div>
                <div class="p-6 bg-slate-50/50 rounded-3xl border border-slate-100">
                    <div class="text-[10px] font-black text-slate-400 uppercase mb-2">Completed</div>
                    <div class="text-3xl font-black text-slate-900"><?php echo $metrics['site_pulse']['completed'] ?? 0; ?></div>
                    <div class="w-full bg-slate-200 h-1 rounded-full mt-4 overflow-hidden">
                        <div class="bg-emerald-500 h-full" style="width: <?php echo ($metrics['total_sites'] > 0 ? ($metrics['site_pulse']['completed'] / $metrics['total_sites']) * 100 : 0); ?>%"></div>
                    </div>
                </div>
            </div>

            <!-- Territory Heatmap Concept -->
            <div class="mt-8">
                <h4 class="text-xs font-black text-slate-400 uppercase tracking-[0.3em] mb-6">Territory Concentration</h4>
                <div class="space-y-6">
                    <?php 
                    $maxTerr = isset($metrics['territory'][0]['count']) ? $metrics['territory'][0]['count'] : 1;
                    foreach($metrics['territory'] as $t): 
                        $pct = ($t['count'] / $maxTerr) * 100;
                    ?>
                    <div class="flex items-center gap-6">
                        <div class="w-32 text-xs font-bold text-slate-600 truncate"><?php echo $t['state']; ?></div>
                        <div class="flex-1 bg-slate-100 h-3 rounded-full overflow-hidden">
                            <div class="bg-slate-900 h-full transition-all duration-1000" style="width: <?php echo $pct; ?>%"></div>
                        </div>
                        <div class="w-12 text-xs font-black text-slate-900 text-right"><?php echo $t['count']; ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Distribution & Intelligence -->
        <div class="lg:col-span-1 space-y-12">
            <!-- System Matrix -->
            <div class="glass rounded-[3rem] p-10 shadow-sm border border-slate-200/50">
                <h3 class="text-xl font-extrabold text-slate-900 mb-8">Technical Segments</h3>
                <div class="space-y-6">
                    <?php foreach($metrics['systems'] as $s): ?>
                    <div class="flex items-center justify-between group">
                        <div class="flex items-center gap-4">
                            <div class="w-2 h-2 rounded-full bg-blue-500 group-hover:scale-150 transition-transform"></div>
                            <span class="text-sm font-bold text-slate-600"><?php echo $s['category'] ?: 'Other'; ?></span>
                        </div>
                        <div class="px-3 py-1 bg-slate-900 text-white rounded-lg text-[10px] font-black italic"><?php echo $s['count']; ?> Units</div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Resource Pressure Index -->
            <div class="bg-slate-950 rounded-[3rem] p-10 text-white shadow-2xl relative overflow-hidden">
                <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-indigo-500/10 rounded-full blur-3xl"></div>
                <h4 class="text-xl font-extrabold mb-8 flex items-center gap-2">
                    Operational Pressure
                    <span class="block w-2 h-2 bg-rose-500 rounded-full animate-pulse"></span>
                </h4>
                <div class="space-y-6">
                    <div class="flex justify-between items-end mb-2">
                        <div class="text-[10px] font-black text-slate-500 uppercase">Approval Bottleneck</div>
                        <div class="text-sm font-bold text-slate-200"><?php echo $metrics['active_requests']; ?> Orders</div>
                    </div>
                    <div class="w-full bg-white/10 h-1.5 rounded-full overflow-hidden">
                        <div class="bg-indigo-500 h-full" style="width: 75%"></div>
                    </div>
                    
                    <div class="flex justify-between items-end mb-2">
                        <div class="text-[10px] font-black text-slate-500 uppercase">Deploy Lag</div>
                        <div class="text-sm font-bold text-slate-200">2.4 Days</div>
                    </div>
                    <div class="w-full bg-white/10 h-1.5 rounded-full overflow-hidden">
                        <div class="bg-emerald-500 h-full" style="width: 45%"></div>
                    </div>
                </div>
                <button class="w-full mt-10 py-4 bg-white/5 border border-white/10 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-white/10 transition-all">Optimize Parameters</button>
            </div>
        </div>
    </div>

    <!-- Section: Strategic Partner Index -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-12">
        <!-- Partner Workload Table -->
        <div class="xl:col-span-2 glass rounded-[3rem] p-10 shadow-sm border border-slate-200/50">
            <h3 class="text-2xl font-extrabold text-slate-900 mb-8">Strategic Partner Workload</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="pb-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Partner Identity</th>
                            <th class="pb-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Active Sites</th>
                            <th class="pb-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Commitment Level</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php foreach($metrics['vendor_workload'] as $idx => $v): 
                            $lvl = $v['sites'] > 10 ? 'Primary' : ($v['sites'] > 5 ? 'Secondary' : 'Strategic');
                            $color = $v['sites'] > 10 ? 'indigo' : ($v['sites'] > 5 ? 'blue' : 'slate');
                        ?>
                        <tr class="group hover:bg-slate-50 transition-colors">
                            <td class="py-6 flex items-center gap-4">
                                <div class="w-12 h-12 bg-white rounded-2xl border border-slate-100 shadow-sm flex items-center justify-center text-sm font-black text-slate-800 group-hover:bg-slate-900 group-hover:text-white transition-all"><?php echo $v['name'][0]; ?></div>
                                <div>
                                    <div class="text-sm font-black text-slate-900"><?php echo $v['name']; ?></div>
                                    <div class="text-[10px] font-bold text-slate-400 uppercase">Verified Contractor</div>
                                </div>
                            </td>
                            <td class="py-6 text-center font-black text-slate-900"><?php echo $v['sites']; ?></td>
                            <td class="py-6 text-right">
                                <span class="px-3 py-1 bg-<?php echo $color; ?>-50 text-<?php echo $color; ?>-600 rounded-lg text-[9px] font-black uppercase tracking-tighter ring-1 ring-<?php echo $color; ?>-100"><?php echo $lvl; ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Financial Flow Widget -->
        <div class="xl:col-span-1 glass rounded-[3rem] p-10 shadow-sm border border-slate-200/50">
            <h3 class="text-xl font-extrabold text-slate-900 mb-8 italic">Operational Flow</h3>
            <div class="space-y-8">
                <?php foreach($metrics['recent_receipts'] as $r): ?>
                <div class="flex items-start gap-4 p-4 hover:bg-slate-50 rounded-3xl transition-colors cursor-pointer border border-transparent hover:border-slate-100">
                    <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between mb-1">
                            <div class="text-xs font-black text-slate-900 truncate pr-2"><?php echo $r['supplier_name']; ?></div>
                            <div class="text-xs font-black text-slate-400">₹<?php echo number_format($r['total_amount'] / 1000, 1); ?>K</div>
                        </div>
                        <div class="flex justify-between items-center text-[9px] font-bold text-slate-400 uppercase tracking-tighter">
                            <span>#<?php echo $r['receipt_number']; ?></span>
                            <span><?php echo date('d M', strtotime($r['receipt_date'])); ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <a href="inventory/inwards/" class="block w-full py-5 bg-slate-900 text-white text-[10px] font-black uppercase tracking-[0.4em] text-center rounded-[2rem] hover:bg-slate-800 transition-all shadow-xl shadow-slate-200">View Master Ledger</a>
            </div>
        </div>
    </div>
</div>

<script>
    // Refresh automation
    setTimeout(() => {
        console.log('Operational pulse synchronized.');
    }, 1000);
</script>

<?php
$content = ob_get_clean();
include '../includes/admin_layout.php';
?>
