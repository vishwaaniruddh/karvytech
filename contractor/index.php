<?php
require_once '../config/auth.php';
require_once '../config/database.php';
require_once '../models/Site.php';
require_once '../models/SiteDelegation.php';
require_once '../models/SiteSurvey.php';
require_once '../models/Installation.php';
require_once '../models/MaterialRequest.php';

// Require vendor authentication
Auth::requireVendor();

$vendorId = Auth::getVendorId();
$currentUser = Auth::getCurrentUser();
$title = 'Contractor Dashboard';

// Initialize models
$siteModel = new Site();
$delegationModel = new SiteDelegation();
$surveyModel = new SiteSurvey();
$installationModel = new Installation();
$requestModel = new MaterialRequest();

$db = Database::getInstance()->getConnection();

// Initialize stats
$stats = [
    'sites' => ['total' => 0, 'pending_survey' => 0, 'completed_survey' => 0, 'installed' => 0],
    'installations' => ['total' => 0, 'assigned' => 0, 'in_progress' => 0, 'completed' => 0, 'overdue' => 0],
    'materials' => ['total' => 0, 'pending' => 0, 'approved' => 0, 'dispatched' => 0, 'delivered' => 0],
    'recent_activities' => []
];

try {
    // 1. Site & Survey Statistics for this Vendor
    $stmt = $db->prepare("
        SELECT 
            COUNT(DISTINCT s.id) as total,
            SUM(CASE WHEN (ss.id IS NULL AND dsr.id IS NULL) THEN 1 ELSE 0 END) as pending_survey,
            SUM(CASE WHEN (ss.id IS NOT NULL OR dsr.id IS NOT NULL) THEN 1 ELSE 0 END) as completed_survey,
            SUM(CASE WHEN s.installation_status = 1 THEN 1 ELSE 0 END) as installed
        FROM sites s
        INNER JOIN site_delegations sd ON s.id = sd.site_id AND sd.status = 'active'
        LEFT JOIN site_surveys ss ON s.id = ss.site_id
        LEFT JOIN (
            SELECT site_id, MAX(id) as id FROM dynamic_survey_responses GROUP BY site_id
        ) dsr ON s.id = dsr.site_id
        WHERE sd.vendor_id = ?
    ");
    $stmt->execute([$vendorId]);
    $stats['sites'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: $stats['sites'];

    // 2. Installation Statistics
    $stmt = $db->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'assigned' THEN 1 ELSE 0 END) as assigned,
            SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN (status != 'completed' AND delegation_date < DATE_SUB(NOW(), INTERVAL 7 DAY)) THEN 1 ELSE 0 END) as overdue
        FROM installation_delegations
        WHERE vendor_id = ?
    ");
    $stmt->execute([$vendorId]);
    $stats['installations'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: $stats['installations'];

    // 3. Material Request Statistics
    $stmt = $db->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'dispatched' THEN 1 ELSE 0 END) as dispatched,
            SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered
        FROM material_requests
        WHERE vendor_id = ?
    ");
    $stmt->execute([$vendorId]);
    $stats['materials'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: $stats['materials'];

    // 4. Recent Local Activities
    $stmt = $db->prepare("
        (SELECT 'survey' as type, CONCAT('Survey Update: ', s.site_id) as activity, ss.updated_at as activity_date
         FROM site_surveys ss JOIN sites s ON ss.site_id = s.id WHERE ss.vendor_id = ? 
         ORDER BY ss.updated_at DESC LIMIT 5)
        UNION ALL
        (SELECT 'installation' as type, CONCAT('Installation Task: ', s.site_id) as activity, id.updated_at as activity_date
         FROM installation_delegations id JOIN sites s ON id.site_id = s.id WHERE id.vendor_id = ?
         ORDER BY id.updated_at DESC LIMIT 5)
        UNION ALL
        (SELECT 'request' as type, CONCAT('Material Request for: ', s.site_id) as activity, mr.created_date as activity_date
         FROM material_requests mr JOIN sites s ON mr.site_id = s.id WHERE mr.vendor_id = ?
         ORDER BY mr.created_date DESC LIMIT 5)
        ORDER BY activity_date DESC LIMIT 8
    ");
    $stmt->execute([$vendorId, $vendorId, $vendorId]);
    $stats['recent_activities'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log("Contractor Dashboard Error: " . $e->getMessage());
}

ob_start();
?>

<!-- Header Section -->
<div class="mb-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Operations Control</h1>
            <p class="text-gray-500 text-sm mt-1">Real-time oversight for <?php echo htmlspecialchars($currentUser['username']); ?></p>
        </div>
        <div class="mt-4 md:mt-0">
            <span class="inline-flex items-center px-4 py-2 bg-blue-50 text-blue-700 rounded-xl text-sm font-semibold border border-blue-100 shadow-sm">
                <span class="w-2 h-2 bg-blue-500 rounded-full mr-2 animate-pulse"></span>
                Vendor ID: <?php echo str_pad($vendorId, 4, '0', STR_PAD_LEFT); ?>
            </span>
        </div>
    </div>
</div>

<!-- Primary Stats Row -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Active Assignments -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-all">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
            <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-full uppercase tracking-wider">Active Assignments</span>
        </div>
        <div class="text-2xl font-bold text-gray-800"><?php echo $stats['sites']['total']; ?></div>
        <div class="text-xs text-gray-500 mt-1 uppercase tracking-wider font-semibold">Delegated sites</div>
    </div>

    <!-- Survey Pipeline -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-all">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center text-amber-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
            </div>
            <span class="text-xs font-bold text-amber-600 bg-amber-50 px-2.5 py-1 rounded-full uppercase tracking-wider">Surveying</span>
        </div>
        <div class="text-2xl font-bold text-gray-800"><?php echo $stats['sites']['pending_survey']; ?></div>
        <div class="text-xs text-gray-500 mt-1 uppercase tracking-wider font-semibold">Pending reports</div>
    </div>

    <!-- Active Installations -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-all">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center text-purple-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </div>
            <span class="text-xs font-bold text-purple-600 bg-purple-50 px-2.5 py-1 rounded-full uppercase tracking-wider">Installations</span>
        </div>
        <div class="text-2xl font-bold text-gray-800"><?php echo $stats['installations']['in_progress']; ?></div>
        <div class="text-xs text-gray-500 mt-1 uppercase tracking-wider font-semibold">Active tasks</div>
    </div>

    <!-- Logistics Requests -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-all">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full uppercase tracking-wider">Materials</span>
        </div>
        <div class="text-2xl font-bold text-gray-800"><?php echo $stats['materials']['pending']; ?></div>
        <div class="text-xs text-gray-500 mt-1 uppercase tracking-wider font-semibold">Awaiting logic</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Left Column: Detailed Distributions -->
    <div class="lg:col-span-2 space-y-8">
        <!-- Site Life Cycle -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-lg font-bold text-gray-800 tracking-tight">Portfolio Life-Cycle</h3>
                <div class="text-xs font-bold text-gray-400 uppercase tracking-widest">Analytics</div>
            </div>
            
            <div class="space-y-6">
                <!-- Survey Completion -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-semibold text-gray-700">Survey Completion Rate</span>
                        <span class="text-sm font-bold text-gray-900"><?php 
                            $surveyPct = $stats['sites']['total'] > 0 ? round(($stats['sites']['completed_survey'] / $stats['sites']['total']) * 100) : 0;
                            echo $surveyPct; 
                        ?>%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 h-full rounded-full transition-all duration-1000" style="width: <?php echo $surveyPct; ?>%"></div>
                    </div>
                    <div class="flex justify-between mt-2">
                        <span class="text-xs text-gray-500"><?php echo $stats['sites']['completed_survey']; ?> Finalized</span>
                        <span class="text-xs text-gray-500"><?php echo $stats['sites']['pending_survey']; ?> Pending</span>
                    </div>
                </div>

                <!-- Installation Progress -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-semibold text-gray-700">Overall Installation Success</span>
                        <span class="text-sm font-bold text-gray-900"><?php 
                            $instPct = $stats['sites']['total'] > 0 ? round(($stats['sites']['installed'] / $stats['sites']['total']) * 100) : 0;
                            echo $instPct; 
                        ?>%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                        <div class="bg-gradient-to-r from-emerald-500 to-teal-600 h-full rounded-full transition-all duration-1000" style="width: <?php echo $instPct; ?>%"></div>
                    </div>
                    <div class="flex justify-between mt-2">
                        <span class="text-xs text-gray-500"><?php echo $stats['sites']['installed']; ?> Sites Live</span>
                        <span class="text-xs text-gray-500"><?php echo $stats['sites']['total'] - $stats['sites']['installed']; ?> Remaining</span>
                    </div>
                </div>
            </div>

            <!-- Material Requests Breakdown -->
            <div class="mt-10 pt-8 border-t border-gray-50">
                <h4 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6">Material Cycle Distribution</h4>
                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-gray-50 rounded-xl p-4 text-center">
                        <div class="text-xl font-bold text-gray-900"><?php echo $stats['materials']['approved']; ?></div>
                        <div class="text-[10px] font-bold text-gray-500 uppercase">Approved</div>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4 text-center">
                        <div class="text-xl font-bold text-gray-900"><?php echo $stats['materials']['dispatched']; ?></div>
                        <div class="text-[10px] font-bold text-gray-400 uppercase">In Transit</div>
                    </div>
                    <div class="bg-green-50 rounded-xl p-4 text-center">
                        <div class="text-xl font-bold text-green-700"><?php echo $stats['materials']['delivered']; ?></div>
                        <div class="text-[10px] font-bold text-green-600 uppercase">Delivered</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Launch Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="sites/" class="group p-6 bg-white rounded-2xl border border-gray-100 hover:border-blue-500 hover:shadow-lg transition-all text-center">
                <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                </div>
                <div class="text-xs font-bold text-gray-900">My Sites</div>
            </a>
            <a href="surveys.php" class="group p-6 bg-white rounded-2xl border border-gray-100 hover:border-amber-500 hover:shadow-lg transition-all text-center">
                <div class="w-10 h-10 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                </div>
                <div class="text-xs font-bold text-gray-900">Surveys</div>
            </a>
            <a href="installations.php" class="group p-6 bg-white rounded-2xl border border-gray-100 hover:border-purple-500 hover:shadow-lg transition-all text-center">
                <div class="w-10 h-10 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
                <div class="text-xs font-bold text-gray-900">Active Jobs</div>
            </a>
            <a href="material-requests-list.php" class="group p-6 bg-white rounded-2xl border border-gray-100 hover:border-emerald-500 hover:shadow-lg transition-all text-center">
                <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div class="text-xs font-bold text-gray-900">Inventory</div>
            </a>
        </div>
    </div>

    <!-- Right Column: Recent Activities -->
    <div class="space-y-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-50 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900">Operational Log</h3>
                <span class="p-1 px-2 bg-blue-50 text-blue-600 text-[10px] font-bold rounded-md uppercase">Recent</span>
            </div>
            <div class="p-0">
                <?php if (empty($stats['recent_activities'])): ?>
                    <div class="p-12 text-center">
                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-300">
                             <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <p class="text-sm text-gray-400">No active operational logs</p>
                    </div>
                <?php else: ?>
                    <div class="divide-y divide-gray-50">
                        <?php foreach ($stats['recent_activities'] as $activity): ?>
                        <div class="p-4 flex items-start space-x-4 hover:bg-gray-50/50 transition-colors">
                            <div class="mt-1 flex-shrink-0">
                                <?php
                                $dotColor = 'bg-gray-400';
                                if ($activity['type'] == 'survey') $dotColor = 'bg-amber-400';
                                if ($activity['type'] == 'installation') $dotColor = 'bg-purple-400';
                                if ($activity['type'] == 'request') $dotColor = 'bg-emerald-400';
                                ?>
                                <div class="w-2 h-2 rounded-full <?php echo $dotColor; ?>"></div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate"><?php echo htmlspecialchars($activity['activity']); ?></p>
                                <p class="text-[10px] font-medium text-gray-400 uppercase mt-0.5"><?php echo date('M j, Y • g:i A', strtotime($activity['activity_date'])); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="p-4 bg-gray-50 text-center">
                        <button onclick="location.reload()" class="text-xs font-bold text-blue-600 hover:text-blue-800 transition-colors">Poll for updates</button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- System Status -->
        <div class="p-6 bg-gradient-to-br from-gray-900 to-slate-800 rounded-2xl text-white shadow-xl shadow-gray-200">
            <h4 class="text-sm font-bold text-gray-400 uppercase mb-4 tracking-wider">Sync Integrity</h4>
            <div class="flex items-center space-x-4 mb-6">
                <div class="relative flex-shrink-0">
                    <div class="w-12 h-12 bg-emerald-500/20 rounded-full flex items-center justify-center border border-emerald-500/30">
                        <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    </div>
                    <div class="absolute -top-1 -right-1 w-4 h-4 bg-emerald-500 rounded-full border-2 border-gray-900 flex items-center justify-center">
                        <div class="w-1 h-1 bg-white rounded-full"></div>
                    </div>
                </div>
                <div>
                    <div class="text-lg font-bold">Secure Access</div>
                    <div class="text-[10px] text-emerald-400 font-bold uppercase tracking-widest mt-0.5">Verified Contractor Node</div>
                </div>
            </div>
            <div class="space-y-3">
                <div class="flex justify-between text-xs py-2 border-b border-white/5">
                    <span class="text-gray-400">Server Time</span>
                    <span class="font-mono text-gray-200"><?php echo date('H:i:s T'); ?></span>
                </div>
                <div class="flex justify-between text-xs py-2 border-b border-white/5">
                    <span class="text-gray-400">Active Role</span>
                    <span class="font-bold text-amber-400 uppercase"><?php echo htmlspecialchars($currentUser['role']); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Custom animations for the dashboard */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.grid > div {
    animation: fadeIn 0.4s ease-out forwards;
}

.lg\:col-span-2 > div:nth-child(1) { animation-delay: 0.1s; }
.lg\:col-span-2 > div:nth-child(2) { animation-delay: 0.2s; }
.space-y-8 > div:nth-child(1) { animation-delay: 0.3s; }
.space-y-8 > div:nth-child(2) { animation-delay: 0.4s; }
</style>

<?php
$content = ob_get_clean();
include '../includes/vendor_layout.php';
?>
