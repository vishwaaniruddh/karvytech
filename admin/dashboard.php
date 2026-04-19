<?php
require_once '../config/auth.php';
require_once '../config/database.php';
require_once '../models/Site.php';
require_once '../models/Vendor.php';
require_once '../models/SiteSurvey.php';
require_once '../models/Inventory.php';
require_once '../models/Installation.php';
require_once '../models/MaterialRequest.php';

if (!Auth::isInternal()) {
    header('Location: ' . url('/shared/403.php?role=internal'));
    exit();
}

$title = 'Admin Dashboard';
$currentUser = Auth::getCurrentUser();
$installationModel = new Installation();

// ── Defaults ──
$totalSites = 0;
$siteStats = ['delegated' => 0, 'surveyed' => 0, 'installed' => 0, 'pending' => 0];
$vendorStats = ['total' => 0, 'active' => 0, 'inactive' => 0];
$surveyStats = ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0, 'completed' => 0];
$installationStats = ['total' => 0, 'assigned' => 0, 'in_progress' => 0, 'completed' => 0, 'overdue' => 0, 'on_hold' => 0];
$inventoryStats = ['total_items' => 0, 'total_quantity' => 0, 'low_stock_items' => 0];
$requestStats = ['total' => 0, 'pending' => 0, 'approved' => 0, 'dispatched' => 0, 'delivered' => 0];
$dispatchStats = ['total' => 0, 'prepared' => 0, 'in_transit' => 0, 'delivered' => 0];
$boqStats = ['total_items' => 0, 'active' => 0];
$userStats = ['total' => 0, 'active' => 0, 'roles' => []];
$weeklyActivity = [];
$monthlyTrends = [];
$topVendors = [];
$stateDistribution = [];
$recentActivities = [];
$surveyMonthly = [];
$installMonthly = [];
$dispatchMonthly = [];
$priorityBreakdown = [];
$avgCompletionDays = 0;

try {
    $db = Database::getInstance()->getConnection();

    // ── 1. Site Stats ──
    $stmt = $db->query("SELECT COUNT(*) as total FROM sites WHERE deleted_at IS NULL");
    $totalSites = $stmt->fetch()['total'];
    $stmt = $db->query("SELECT 
        SUM(CASE WHEN is_delegate = 1 THEN 1 ELSE 0 END) as delegated,
        SUM(CASE WHEN survey_status = 1 THEN 1 ELSE 0 END) as surveyed,
        SUM(CASE WHEN installation_status = 1 THEN 1 ELSE 0 END) as installed,
        SUM(CASE WHEN is_delegate = 0 THEN 1 ELSE 0 END) as pending
        FROM sites WHERE deleted_at IS NULL");
    $siteStats = $stmt->fetch();

    // ── 2. Vendor Stats ──
    $stmt = $db->query("SELECT COUNT(*) as total,
        SUM(CASE WHEN status='active' THEN 1 ELSE 0 END) as active,
        SUM(CASE WHEN status='inactive' THEN 1 ELSE 0 END) as inactive FROM vendors");
    $vendorStats = $stmt->fetch();

    // ── 3. Survey Stats (extended) ──
    $stmt = $db->query("SELECT COUNT(*) as total,
        SUM(CASE WHEN survey_status='pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN survey_status='approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN survey_status='completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN survey_status='rejected' THEN 1 ELSE 0 END) as rejected
        FROM site_surveys");
    $surveyStats = $stmt->fetch();

    // ── 4. Installation Stats (extended) ──
    $stmt = $db->query("SELECT COUNT(*) as total,
        SUM(CASE WHEN status='assigned' THEN 1 ELSE 0 END) as assigned,
        SUM(CASE WHEN status='in_progress' THEN 1 ELSE 0 END) as in_progress,
        SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status='on_hold' THEN 1 ELSE 0 END) as on_hold,
        SUM(CASE WHEN status NOT IN ('completed','cancelled') AND expected_completion_date < CURDATE() THEN 1 ELSE 0 END) as overdue
        FROM installation_delegations");
    $installationStats = $stmt->fetch();

    // ── 5. Inventory Stats ──
    $stmt = $db->query("SELECT COUNT(DISTINCT item_name) as total_items,
        COALESCE(SUM(total_stock),0) as total_quantity,
        SUM(CASE WHEN total_stock < 10 THEN 1 ELSE 0 END) as low_stock_items
        FROM inventory_summary");
    $inventoryStats = $stmt->fetch() ?: $inventoryStats;

    // ── 6. Material Requests ──
    $stmt = $db->query("SELECT COUNT(*) as total,
        SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status='approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status IN ('dispatched','partially_dispatched') THEN 1 ELSE 0 END) as dispatched,
        SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as delivered
        FROM material_requests");
    $requestStats = $stmt->fetch() ?: $requestStats;

    // ── 7. Dispatch Stats ──
    $stmt = $db->query("SELECT COUNT(*) as total,
        SUM(CASE WHEN dispatch_status='prepared' THEN 1 ELSE 0 END) as prepared,
        SUM(CASE WHEN dispatch_status IN ('dispatched','in_transit') THEN 1 ELSE 0 END) as in_transit,
        SUM(CASE WHEN dispatch_status='delivered' THEN 1 ELSE 0 END) as delivered
        FROM inventory_dispatches");
    $dispatchStats = $stmt->fetch() ?: $dispatchStats;

    // ── 8. BOQ Master Items ──
    $stmt = $db->query("SELECT COUNT(*) as total_items,
        SUM(CASE WHEN status='active' THEN 1 ELSE 0 END) as active FROM boq_items");
    $boqStats = $stmt->fetch() ?: $boqStats;

    // ── 9. Users by Role ──
    $stmt = $db->query("SELECT COUNT(*) as total,
        SUM(CASE WHEN status='active' THEN 1 ELSE 0 END) as active FROM users");
    $userStats = $stmt->fetch();
    $stmt = $db->query("SELECT role, COUNT(*) as cnt FROM users GROUP BY role ORDER BY cnt DESC");
    $userStats['roles'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ── 10. State-Level Distribution (top 10) ──
    $stmt = $db->query("SELECT COALESCE(state,'Unknown') as state, COUNT(*) as cnt 
        FROM sites WHERE deleted_at IS NULL GROUP BY state ORDER BY cnt DESC LIMIT 10");
    $stateDistribution = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ── 11. Weekly Activity Heatmap (last 7 days) ──
    $stmt = $db->query("SELECT DATE(created_at) as day, COUNT(*) as cnt 
        FROM sites WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND deleted_at IS NULL
        GROUP BY DATE(created_at) ORDER BY day");
    $weeklyActivity = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ── 12. Monthly Trends (Sites, Surveys, Installations - 6 months) ──
    $stmt = $db->query("SELECT DATE_FORMAT(created_at,'%Y-%m') as month, COUNT(*) as count
        FROM sites WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) AND deleted_at IS NULL
        GROUP BY DATE_FORMAT(created_at,'%Y-%m') ORDER BY month");
    $monthlyTrends = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $db->query("SELECT DATE_FORMAT(created_at,'%Y-%m') as month, COUNT(*) as count
        FROM site_surveys WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(created_at,'%Y-%m') ORDER BY month");
    $surveyMonthly = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $db->query("SELECT DATE_FORMAT(created_at,'%Y-%m') as month, COUNT(*) as count
        FROM installation_delegations WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(created_at,'%Y-%m') ORDER BY month");
    $installMonthly = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $db->query("SELECT DATE_FORMAT(created_at,'%Y-%m') as month, COUNT(*) as count
        FROM inventory_dispatches WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(created_at,'%Y-%m') ORDER BY month");
    $dispatchMonthly = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ── 13. Installation Priority Breakdown ──
    $stmt = $db->query("SELECT priority, COUNT(*) as cnt FROM installation_delegations GROUP BY priority");
    $priorityBreakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ── 14. Average Installation Time ──
    $stmt = $db->query("SELECT AVG(DATEDIFF(actual_completion_date, actual_start_date)) as avg_days
        FROM installation_delegations WHERE status='completed' AND actual_completion_date IS NOT NULL AND actual_start_date IS NOT NULL");
    $avgRow = $stmt->fetch();
    $avgCompletionDays = round($avgRow['avg_days'] ?? 0, 1);

    // ── 15. Top Vendors ──
    $stmt = $db->query("SELECT v.name,
        COUNT(DISTINCT ss.id) as surveys_completed,
        COUNT(DISTINCT id.id) as installations_completed
        FROM vendors v
        LEFT JOIN site_surveys ss ON v.id = ss.vendor_id AND ss.survey_status = 'approved'
        LEFT JOIN installation_delegations id ON v.id = id.vendor_id AND id.status = 'completed'
        WHERE v.status = 'active'
        GROUP BY v.id, v.name
        ORDER BY (COUNT(DISTINCT ss.id) + COUNT(DISTINCT id.id)) DESC LIMIT 5");
    $topVendors = $stmt->fetchAll() ?: [];

    // ── 16. Recent Activities ──
    $stmt = $db->query("(SELECT 'survey' as type, CONCAT('Survey for ', COALESCE(s.site_id,'Unknown')) as activity, ss.created_at as dt
        FROM site_surveys ss LEFT JOIN sites s ON ss.site_id=s.id ORDER BY ss.created_at DESC LIMIT 3)
        UNION ALL
        (SELECT 'installation', CONCAT('Install: ', COALESCE(s.site_id,'Unknown')), id.created_at
        FROM installation_delegations id LEFT JOIN sites s ON id.site_id=s.id ORDER BY id.created_at DESC LIMIT 3)
        UNION ALL
        (SELECT 'dispatch', CONCAT('Dispatch #', d.dispatch_number), d.created_at
        FROM inventory_dispatches d ORDER BY d.created_at DESC LIMIT 3)
        UNION ALL
        (SELECT 'request', CONCAT('Material Req: ', COALESCE(s.site_id,'Unknown')), mr.created_at
        FROM material_requests mr LEFT JOIN sites s ON mr.site_id=s.id ORDER BY mr.created_at DESC LIMIT 3)
        ORDER BY dt DESC LIMIT 10");
    $recentActivities = $stmt->fetchAll() ?: [];

} catch (Exception $e) {
    error_log("Admin Dashboard Error: " . $e->getMessage());
}

// Percentages
$surveyPct = $totalSites > 0 ? round(($siteStats['surveyed'] ?? 0) / $totalSites * 100) : 0;
$instPct = $totalSites > 0 ? round(($siteStats['installed'] ?? 0) / $totalSites * 100) : 0;
$delegPct = $totalSites > 0 ? round(($siteStats['delegated'] ?? 0) / $totalSites * 100) : 0;

// Build unified monthly labels
$allMonths = [];
foreach (array_merge($monthlyTrends, $surveyMonthly, $installMonthly, $dispatchMonthly) as $m) {
    $allMonths[$m['month']] = true;
}
ksort($allMonths);
$monthLabels = array_keys($allMonths);
$siteCounts = array_fill_keys($monthLabels, 0);
$surveyCounts = array_fill_keys($monthLabels, 0);
$installCounts = array_fill_keys($monthLabels, 0);
$dispatchCounts = array_fill_keys($monthLabels, 0);
foreach ($monthlyTrends as $m)
    $siteCounts[$m['month']] = (int) $m['count'];
foreach ($surveyMonthly as $m)
    $surveyCounts[$m['month']] = (int) $m['count'];
foreach ($installMonthly as $m)
    $installCounts[$m['month']] = (int) $m['count'];
foreach ($dispatchMonthly as $m)
    $dispatchCounts[$m['month']] = (int) $m['count'];

$prettyMonths = array_map(function ($m) {
    $parts = explode('-', $m);
    return date('M', mktime(0, 0, 0, (int) $parts[1], 1, (int) $parts[0]));
}, $monthLabels);

ob_start();
?>

<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.44.0/dist/apexcharts.min.js"></script>

<style>
.adm{font-family:'Inter',sans-serif}
/* ── Stat Cards ── */
.sc{position:relative;overflow:hidden;border-radius:18px;padding:22px;transition:all .4s cubic-bezier(.4,0,.2,1);border:1px solid rgba(255,255,255,.08)}
.sc::before{content:'';position:absolute;top:-40%;right:-30%;width:140px;height:140px;border-radius:50%;opacity:.07;transition:all .5s ease}
.sc:hover{transform:translateY(-3px)}.sc:hover::before{opacity:.14;transform:scale(1.2)}
.sc-slate{background:linear-gradient(135deg,#0f172a,#1e293b);box-shadow:0 6px 24px rgba(15,23,42,.25)}.sc-slate::before{background:#3b82f6}
.sc-blue{background:linear-gradient(135deg,#1e3a8a,#1d4ed8);box-shadow:0 6px 24px rgba(29,78,216,.25)}.sc-blue::before{background:#60a5fa}
.sc-green{background:linear-gradient(135deg,#064e3b,#047857);box-shadow:0 6px 24px rgba(4,120,87,.2)}.sc-green::before{background:#34d399}
.sc-amber{background:linear-gradient(135deg,#78350f,#b45309);box-shadow:0 6px 24px rgba(180,83,9,.2)}.sc-amber::before{background:#fbbf24}
.sc-purple{background:linear-gradient(135deg,#4c1d95,#6d28d9);box-shadow:0 6px 24px rgba(109,40,217,.25)}.sc-purple::before{background:#a78bfa}
.sc-cyan{background:linear-gradient(135deg,#164e63,#0891b2);box-shadow:0 6px 24px rgba(8,145,178,.25)}.sc-cyan::before{background:#22d3ee}
.sc-rose{background:linear-gradient(135deg,#881337,#be123c);box-shadow:0 6px 24px rgba(190,18,60,.2)}.sc-rose::before{background:#fb7185}
.sc-fuchsia{background:linear-gradient(135deg,#701a75,#a21caf);box-shadow:0 6px 24px rgba(162,28,175,.2)}.sc-fuchsia::before{background:#f0abfc}
.sc-teal{background:linear-gradient(135deg,#134e4a,#0d9488);box-shadow:0 6px 24px rgba(13,148,136,.2)}.sc-teal::before{background:#5eead4}
.sc-indigo{background:linear-gradient(135deg,#312e81,#4338ca);box-shadow:0 6px 24px rgba(67,56,202,.25)}.sc-indigo::before{background:#818cf8}
.sv{font-size:2rem;font-weight:900;line-height:1;color:#fff;font-variant-numeric:tabular-nums;letter-spacing:-.03em}
.sl{font-size:.58rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:rgba(255,255,255,.55);margin-top:5px}
.si{width:40px;height:40px;border-radius:12px;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,.1);backdrop-filter:blur(8px);transition:all .3s ease}
.sc:hover .si{background:rgba(255,255,255,.18);transform:scale(1.08)}
.ss{margin-top:12px;display:flex;align-items:center;gap:6px}
.sb{width:20px;height:3px;border-radius:2px}
.sh{font-size:9px;font-weight:600;color:rgba(255,255,255,.35)}
/* ── Panels ── */
.dp{background:#fff;border:1px solid #f1f5f9;border-radius:18px;overflow:hidden}
.dp-h{padding:18px 22px;border-bottom:1px solid #f8fafc;display:flex;align-items:center;justify-content:space-between}
.dp-t{font-size:13px;font-weight:800;color:#0f172a;letter-spacing:-.01em}
.dp-b{font-size:8px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;padding:4px 10px;border-radius:100px}
.dp-bd{padding:20px 22px}
/* ── Progress ── */
.pt{height:6px;background:#f1f5f9;border-radius:100px;overflow:hidden}
.pf{height:100%;border-radius:100px;transition:width 1.2s cubic-bezier(.4,0,.2,1)}
/* ── Activity ── */
.ai{display:flex;align-items:flex-start;gap:10px;padding:10px 0;border-bottom:1px solid #f8fafc}
.ai:last-child{border-bottom:none}
.ad{width:7px;height:7px;border-radius:50%;margin-top:5px;flex-shrink:0}
/* ── Vendor Row ── */
.vr{display:flex;align-items:center;justify-content:space-between;padding:10px 14px;background:#f8fafc;border-radius:12px;transition:all .2s ease;border:1px solid transparent}
.vr:hover{border-color:#e2e8f0;transform:translateX(3px)}
/* ── Anim ── */
@keyframes fu{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
.fu{animation:fu .5s cubic-bezier(.4,0,.2,1) both}
.fd1{animation-delay:.04s}.fd2{animation-delay:.08s}.fd3{animation-delay:.12s}.fd4{animation-delay:.16s}.fd5{animation-delay:.2s}
/* ── Responsive ── */
@media(max-width:1200px){.g4{grid-template-columns:repeat(2,1fr)!important}.g5{grid-template-columns:repeat(2,1fr)!important}}
@media(max-width:768px){.g4,.g5,.g3,.g2{grid-template-columns:1fr!important}}
</style>

<div class="adm">
    <!-- ═══════ ROW 1: STAT CARDS (5×2) ═══════ -->
    <div class="g5" style="display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:22px;">
        <div class="sc sc-slate fu"><div style="display:flex;align-items:flex-start;justify-content:space-between"><div><div class="sv cv" data-target="<?php echo $totalSites; ?>">0</div><div class="sl">Total Sites</div></div><div class="si"><svg width="18" height="18" fill="none" stroke="#60a5fa" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg></div></div><div class="ss"><div class="sb" style="background:rgba(96,165,250,.4)"></div><span class="sh">Registered portfolio</span></div></div>

        <div class="sc sc-blue fu fd1"><div style="display:flex;align-items:flex-start;justify-content:space-between"><div><div class="sv cv" data-target="<?php echo $siteStats['delegated'] ?? 0; ?>">0</div><div class="sl">Delegated</div></div><div class="si"><svg width="18" height="18" fill="none" stroke="#60a5fa" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div></div><div class="ss"><div class="sb" style="background:rgba(96,165,250,.4)"></div><span class="sh"><?php echo $siteStats['pending'] ?? 0; ?> unassigned</span></div></div>

        <div class="sc sc-green fu fd2"><div style="display:flex;align-items:flex-start;justify-content:space-between"><div><div class="sv cv" data-target="<?php echo $siteStats['surveyed'] ?? 0; ?>">0</div><div class="sl">Surveyed</div></div><div class="si"><svg width="18" height="18" fill="none" stroke="#34d399" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div></div><div class="ss"><div class="sb" style="background:rgba(52,211,153,.4)"></div><span class="sh"><?php echo $surveyPct; ?>% completion</span></div></div>

        <div class="sc sc-purple fu fd3"><div style="display:flex;align-items:flex-start;justify-content:space-between"><div><div class="sv cv" data-target="<?php echo $siteStats['installed'] ?? 0; ?>">0</div><div class="sl">Installed</div></div><div class="si"><svg width="18" height="18" fill="none" stroke="#a78bfa" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg></div></div><div class="ss"><div class="sb" style="background:rgba(167,139,250,.4)"></div><span class="sh"><?php echo $instPct; ?>% live</span></div></div>

        <div class="sc sc-cyan fu fd4"><div style="display:flex;align-items:flex-start;justify-content:space-between"><div><div class="sv cv" data-target="<?php echo $vendorStats['active'] ?? 0; ?>">0</div><div class="sl">Active Vendors</div></div><div class="si"><svg width="18" height="18" fill="none" stroke="#22d3ee" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg></div></div><div class="ss"><div class="sb" style="background:rgba(34,211,238,.4)"></div><span class="sh"><?php echo $vendorStats['total'] ?? 0; ?> total</span></div></div>

        <div class="sc sc-amber fu fd2"><div style="display:flex;align-items:flex-start;justify-content:space-between"><div><div class="sv cv" data-target="<?php echo $surveyStats['pending'] ?? 0; ?>">0</div><div class="sl">Pending Surveys</div></div><div class="si"><svg width="18" height="18" fill="none" stroke="#fbbf24" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div></div><div class="ss"><div class="sb" style="background:rgba(251,191,36,.4)"></div><span class="sh"><?php echo $surveyStats['approved'] ?? 0; ?> approved</span></div></div>

        <div class="sc sc-rose fu fd3"><div style="display:flex;align-items:flex-start;justify-content:space-between"><div><div class="sv cv" data-target="<?php echo $installationStats['in_progress'] ?? 0; ?>">0</div><div class="sl">Active Installs</div></div><div class="si"><svg width="18" height="18" fill="none" stroke="#fb7185" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div></div><div class="ss"><div class="sb" style="background:rgba(251,113,133,.4)"></div><span class="sh"><?php echo $installationStats['overdue'] ?? 0; ?> overdue</span></div></div>

        <div class="sc sc-fuchsia fu fd4"><div style="display:flex;align-items:flex-start;justify-content:space-between"><div><div class="sv cv" data-target="<?php echo $requestStats['total'] ?? 0; ?>">0</div><div class="sl">Material Requests</div></div><div class="si"><svg width="18" height="18" fill="none" stroke="#f0abfc" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div></div><div class="ss"><div class="sb" style="background:rgba(240,171,252,.4)"></div><span class="sh"><?php echo $requestStats['pending'] ?? 0; ?> pending</span></div></div>

        <div class="sc sc-teal fu fd4"><div style="display:flex;align-items:flex-start;justify-content:space-between"><div><div class="sv cv" data-target="<?php echo $dispatchStats['total'] ?? 0; ?>">0</div><div class="sl">Dispatches</div></div><div class="si"><svg width="18" height="18" fill="none" stroke="#5eead4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7h12l-4 7H8l-4-7h4zm0 0L6 3H3m5 4v10a2 2 0 104 0V7"/></svg></div></div><div class="ss"><div class="sb" style="background:rgba(94,234,212,.4)"></div><span class="sh"><?php echo $dispatchStats['delivered'] ?? 0; ?> delivered</span></div></div>

        <div class="sc sc-indigo fu fd5"><div style="display:flex;align-items:flex-start;justify-content:space-between"><div><div class="sv cv" data-target="<?php echo $userStats['total'] ?? 0; ?>">0</div><div class="sl">System Users</div></div><div class="si"><svg width="18" height="18" fill="none" stroke="#818cf8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div></div><div class="ss"><div class="sb" style="background:rgba(129,140,248,.4)"></div><span class="sh"><?php echo $userStats['active'] ?? 0; ?> active</span></div></div>
    </div>

    <!-- ═══════ ROW 2: MULTI-SERIES AREA CHART + DONUT ═══════ -->
    <div class="g3" style="display:grid;grid-template-columns:2fr 1fr;gap:18px;margin-bottom:22px;">
        <div class="dp fu fd2">
            <div class="dp-h"><h3 class="dp-t">Operations Trend — 6 Months</h3><span class="dp-b" style="background:#f0f9ff;color:#0369a1;">Multi-Series</span></div>
            <div class="dp-bd"><div id="areaChart" style="height:280px;"></div></div>
        </div>
        <div class="dp fu fd3">
            <div class="dp-h"><h3 class="dp-t">Site Pipeline</h3><span class="dp-b" style="background:#faf5ff;color:#7c3aed;">Distribution</span></div>
            <div class="dp-bd"><div id="pipelineDonut" style="height:280px;"></div></div>
        </div>
    </div>

    <!-- ═══════ ROW 3: GEO BAR + INSTALLATION RADIAL + PRIORITY ═══════ -->
    <div class="g3" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:18px;margin-bottom:22px;">
        <div class="dp fu fd2">
            <div class="dp-h"><h3 class="dp-t">Top States</h3><span class="dp-b" style="background:#ecfdf5;color:#047857;">Geography</span></div>
            <div class="dp-bd"><div id="stateBar" style="height:260px;"></div></div>
        </div>
        <div class="dp fu fd3">
            <div class="dp-h"><h3 class="dp-t">Install Phases</h3><span class="dp-b" style="background:#fef2f2;color:#dc2626;">Radial</span></div>
            <div class="dp-bd"><div id="installRadial" style="height:260px;"></div></div>
        </div>
        <div class="dp fu fd4">
            <div class="dp-h"><h3 class="dp-t">Priority Matrix</h3><span class="dp-b" style="background:#fffbeb;color:#b45309;">Installs</span></div>
            <div class="dp-bd"><div id="priorityChart" style="height:260px;"></div></div>
        </div>
    </div>

    <!-- ═══════ ROW 4: PROGRESS + MATERIAL + ACTIVITY ═══════ -->
    <div class="g2" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:18px;margin-bottom:22px;">

        <!-- Portfolio Life-Cycle -->
        <div class="dp fu fd2">
            <div class="dp-h"><h3 class="dp-t">Portfolio Life-Cycle</h3><span class="dp-b" style="background:#f8fafc;color:#94a3b8;">Progress</span></div>
            <div class="dp-bd">
                <?php
                $bars = [
                    ['label' => 'Delegation', 'pct' => $delegPct, 'grad' => '#3b82f6,#6366f1', 'done' => $siteStats['delegated'] ?? 0, 'rem' => $siteStats['pending'] ?? 0, 'doneLabel' => 'Assigned', 'remLabel' => 'Pending'],
                    ['label' => 'Survey', 'pct' => $surveyPct, 'grad' => '#f59e0b,#f97316', 'done' => $siteStats['surveyed'] ?? 0, 'rem' => $totalSites - ($siteStats['surveyed'] ?? 0), 'doneLabel' => 'Done', 'remLabel' => 'Remaining'],
                    ['label' => 'Installation', 'pct' => $instPct, 'grad' => '#10b981,#06b6d4', 'done' => $siteStats['installed'] ?? 0, 'rem' => $totalSites - ($siteStats['installed'] ?? 0), 'doneLabel' => 'Live', 'remLabel' => 'Remaining'],
                ];
                foreach ($bars as $b): ?>
                    <div style="margin-bottom:20px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                            <span style="font-size:12px;font-weight:600;color:#334155;"><?php echo $b['label']; ?></span>
                            <span style="font-size:12px;font-weight:800;color:#0f172a;"><?php echo $b['pct']; ?>%</span>
                        </div>
                        <div class="pt"><div class="pf" style="width:<?php echo $b['pct']; ?>%;background:linear-gradient(90deg,<?php echo $b['grad']; ?>);"></div></div>
                        <div style="display:flex;justify-content:space-between;margin-top:6px;">
                            <span style="font-size:9px;font-weight:600;color:#94a3b8;"><?php echo $b['done']; ?>     <?php echo $b['doneLabel']; ?></span>
                            <span style="font-size:9px;font-weight:600;color:#94a3b8;"><?php echo $b['rem']; ?>     <?php echo $b['remLabel']; ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Avg Install Time widget -->
                <div style="margin-top:16px;padding:14px;background:linear-gradient(135deg,#0f172a,#1e293b);border-radius:14px;display:flex;align-items:center;gap:14px;">
                    <div style="width:44px;height:44px;border-radius:12px;background:rgba(99,102,241,.15);display:flex;align-items:center;justify-content:center;">
                        <svg width="20" height="20" fill="none" stroke="#818cf8" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <div style="font-size:18px;font-weight:900;color:#fff;"><?php echo $avgCompletionDays; ?> days</div>
                        <div style="font-size:9px;font-weight:600;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.08em;">Avg Install Duration</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Material Cycle Distribution -->
        <div class="dp fu fd3">
            <div class="dp-h"><h3 class="dp-t">Supply Chain</h3><span class="dp-b" style="background:#fefce8;color:#a16207;">Logistics</span></div>
            <div class="dp-bd">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:18px;">
                    <?php
                    $mats = [
                        ['val' => $requestStats['total'] ?? 0, 'lbl' => 'Total', 'bg' => '#f8fafc', 'c' => '#0f172a'],
                        ['val' => $requestStats['approved'] ?? 0, 'lbl' => 'Approved', 'bg' => '#eff6ff', 'c' => '#2563eb'],
                        ['val' => $requestStats['dispatched'] ?? 0, 'lbl' => 'Dispatched', 'bg' => '#fefce8', 'c' => '#ca8a04'],
                        ['val' => $requestStats['delivered'] ?? 0, 'lbl' => 'Delivered', 'bg' => '#ecfdf5', 'c' => '#059669'],
                    ];
                    foreach ($mats as $m): ?>
                        <div style="text-align:center;padding:14px 8px;background:<?php echo $m['bg']; ?>;border-radius:12px;">
                            <div style="font-size:22px;font-weight:900;color:<?php echo $m['c']; ?>;letter-spacing:-.03em;"><?php echo $m['val']; ?></div>
                            <div style="font-size:8px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;margin-top:3px;"><?php echo $m['lbl']; ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Dispatch pipeline -->
                <p style="font-size:9px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#94a3b8;margin-bottom:10px;">Dispatch Pipeline</p>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-bottom:16px;">
                    <div style="text-align:center;padding:12px;background:#faf5ff;border-radius:10px;">
                        <div style="font-size:18px;font-weight:900;color:#7c3aed;"><?php echo $dispatchStats['prepared'] ?? 0; ?></div>
                        <div style="font-size:8px;font-weight:700;color:#a78bfa;text-transform:uppercase;margin-top:2px;">Prepared</div>
                    </div>
                    <div style="text-align:center;padding:12px;background:#fff7ed;border-radius:10px;">
                        <div style="font-size:18px;font-weight:900;color:#ea580c;"><?php echo $dispatchStats['in_transit'] ?? 0; ?></div>
                        <div style="font-size:8px;font-weight:700;color:#f97316;text-transform:uppercase;margin-top:2px;">In Transit</div>
                    </div>
                    <div style="text-align:center;padding:12px;background:#ecfdf5;border-radius:10px;">
                        <div style="font-size:18px;font-weight:900;color:#059669;"><?php echo $dispatchStats['delivered'] ?? 0; ?></div>
                        <div style="font-size:8px;font-weight:700;color:#10b981;text-transform:uppercase;margin-top:2px;">Delivered</div>
                    </div>
                </div>

                <!-- BOQ & Inventory -->
                <div style="padding:14px;background:linear-gradient(135deg,#0f172a,#1e293b);border-radius:14px;">
                    <p style="font-size:9px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:rgba(255,255,255,.4);margin-bottom:10px;">Inventory Snapshot</p>
                    <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.04);"><span style="font-size:11px;color:#64748b">BOQ Items</span><span style="font-size:11px;font-weight:700;color:#e2e8f0;"><?php echo $boqStats['active'] ?? 0; ?> active</span></div>
                    <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.04);"><span style="font-size:11px;color:#64748b">Stock Items</span><span style="font-size:11px;font-weight:700;color:#e2e8f0;"><?php echo number_format($inventoryStats['total_items'] ?? 0); ?></span></div>
                    <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.04);"><span style="font-size:11px;color:#64748b">Total Qty</span><span style="font-size:11px;font-weight:700;color:#e2e8f0;"><?php echo number_format($inventoryStats['total_quantity'] ?? 0); ?></span></div>
                    <div style="display:flex;justify-content:space-between;padding:6px 0;"><span style="font-size:11px;color:#64748b">Low Stock</span><span style="font-size:11px;font-weight:700;color:<?php echo ($inventoryStats['low_stock_items'] ?? 0) > 0 ? '#f87171' : '#34d399'; ?>;"><?php echo $inventoryStats['low_stock_items'] ?? 0; ?></span></div>
                </div>
            </div>
        </div>

        <!-- Activity Feed -->
        <div class="dp fu fd4">
            <div class="dp-h"><h3 class="dp-t">Activity Feed</h3><span class="dp-b" style="background:#fef2f2;color:#dc2626;">Live</span></div>
            <div style="padding:4px 18px 18px;max-height:480px;overflow-y:auto;">
                <?php if (empty($recentActivities)): ?>
                    <div style="text-align:center;padding:40px 0;"><p style="font-size:11px;color:#94a3b8;">No recent activity</p></div>
                <?php else:
                    foreach ($recentActivities as $a):
                        $dc = '#94a3b8';
                        if ($a['type'] == 'survey')
                            $dc = '#f59e0b';
                        if ($a['type'] == 'installation')
                            $dc = '#8b5cf6';
                        if ($a['type'] == 'request')
                            $dc = '#10b981';
                        if ($a['type'] == 'dispatch')
                            $dc = '#3b82f6';
                        ?>
                        <div class="ai">
                            <div class="ad" style="background:<?php echo $dc; ?>"></div>
                            <div style="flex:1;min-width:0;">
                                <p style="font-size:11px;font-weight:600;color:#1e293b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars($a['activity']); ?></p>
                                <p style="font-size:9px;font-weight:500;color:#94a3b8;margin-top:2px;"><?php echo date('M j, Y · g:i A', strtotime($a['dt'])); ?></p>
                            </div>
                        </div>
                    <?php endforeach; endif; ?>
            </div>

            <!-- Top Vendors inline -->
            <div style="padding:0 18px 18px;">
                <div style="padding:14px;background:linear-gradient(135deg,#0f172a,#1e293b);border-radius:14px;">
                    <p style="font-size:9px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:rgba(255,255,255,.4);margin-bottom:10px;">Top Vendors</p>
                    <?php foreach (array_slice($topVendors, 0, 3) as $i => $v): ?>
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:6px 0;<?php echo $i < 2 ? 'border-bottom:1px solid rgba(255,255,255,.04);' : ''; ?>">
                            <div style="display:flex;align-items:center;gap:8px;">
                                <span style="width:20px;height:20px;border-radius:6px;background:linear-gradient(135deg,#3b82f6,#6366f1);display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:800;color:#fff;"><?php echo $i + 1; ?></span>
                                <span style="font-size:11px;font-weight:600;color:#e2e8f0;"><?php echo htmlspecialchars($v['name']); ?></span>
                            </div>
                            <span style="font-size:11px;font-weight:800;color:#fbbf24;"><?php echo $v['surveys_completed'] + $v['installations_completed']; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded',()=>{
    // Counter animation
    document.querySelectorAll('.cv[data-target]').forEach(el=>{
        const t=parseInt(el.dataset.target)||0,dur=1000,st=performance.now();
        function upd(now){const e=now-st,p=Math.min(e/dur,1),ea=1-Math.pow(1-p,4);el.textContent=Math.round(t*ea);if(p<1)requestAnimationFrame(upd)}
        requestAnimationFrame(upd);
    });

    const apexFont={fontFamily:'Inter,sans-serif'};

    // ── Multi-series Area Chart ──
    new ApexCharts(document.querySelector('#areaChart'),{
        chart:{type:'area',height:280,toolbar:{show:false},fontFamily:'Inter',animations:{speed:800}},
        series:[
            {name:'Sites Added',data:<?php echo json_encode(array_values($siteCounts)); ?>},
            {name:'Surveys',data:<?php echo json_encode(array_values($surveyCounts)); ?>},
            {name:'Installations',data:<?php echo json_encode(array_values($installCounts)); ?>},
            {name:'Dispatches',data:<?php echo json_encode(array_values($dispatchCounts)); ?>}
        ],
        xaxis:{categories:<?php echo json_encode($prettyMonths); ?>,labels:{style:{fontSize:'10px',fontWeight:600,colors:'#94a3b8'}}},
        yaxis:{labels:{style:{fontSize:'10px',fontWeight:600,colors:'#94a3b8'}}},
        colors:['#3b82f6','#f59e0b','#10b981','#8b5cf6'],
        fill:{type:'gradient',gradient:{shadeIntensity:1,opacityFrom:.45,opacityTo:.05,stops:[0,95,100]}},
        stroke:{curve:'smooth',width:2.5},
        grid:{borderColor:'#f1f5f9',strokeDashArray:4},
        dataLabels:{enabled:false},
        tooltip:{theme:'dark',style:{fontSize:'11px'},y:{formatter:v=>v+' items'}},
        legend:{position:'top',horizontalAlign:'right',fontSize:'11px',fontWeight:600,markers:{width:8,height:8,radius:4}}
    }).render();

    // ── Pipeline Donut ──
    new ApexCharts(document.querySelector('#pipelineDonut'),{
        chart:{type:'donut',height:280,fontFamily:'Inter'},
        series:[<?php echo ($siteStats['pending'] ?? 0); ?>,<?php echo ($siteStats['delegated'] ?? 0); ?>,<?php echo ($siteStats['surveyed'] ?? 0); ?>,<?php echo ($siteStats['installed'] ?? 0); ?>],
        labels:['Pending','Delegated','Surveyed','Installed'],
        colors:['#94a3b8','#3b82f6','#f59e0b','#10b981'],
        plotOptions:{pie:{donut:{size:'72%',labels:{show:true,total:{show:true,label:'Total',fontSize:'12px',fontWeight:800,color:'#94a3b8',formatter:()=>'<?php echo $totalSites; ?>'}}}}},
        stroke:{width:0},
        dataLabels:{enabled:false},
        legend:{position:'bottom',fontSize:'10px',fontWeight:600,markers:{width:8,height:8,radius:4}},
        tooltip:{theme:'dark',style:{fontSize:'11px'}}
    }).render();

    // ── State Horizontal Bar ──
    new ApexCharts(document.querySelector('#stateBar'),{
        chart:{type:'bar',height:260,toolbar:{show:false},fontFamily:'Inter'},
        series:[{name:'Sites',data:<?php echo json_encode(array_map(function ($s) {
            return (int) $s['cnt']; }, $stateDistribution)); ?>}],
        xaxis:{categories:<?php echo json_encode(array_map(function ($s) {
            return $s['state']; }, $stateDistribution)); ?>,labels:{style:{fontSize:'10px',fontWeight:600,colors:'#64748b'}}},
        plotOptions:{bar:{horizontal:true,borderRadius:6,barHeight:'55%',distributed:true}},
        colors:['#3b82f6','#6366f1','#8b5cf6','#a78bfa','#c4b5fd','#ddd6fe','#60a5fa','#38bdf8','#22d3ee','#2dd4bf'],
        grid:{borderColor:'#f1f5f9',xaxis:{lines:{show:true}},yaxis:{lines:{show:false}}},
        dataLabels:{enabled:true,style:{fontSize:'10px',fontWeight:700},offsetX:4},
        legend:{show:false},
        tooltip:{theme:'dark',style:{fontSize:'11px'}}
    }).render();

    // ── Installation Radial Bar ──
    const instTotal=<?php echo max(1, $installationStats['total'] ?? 1); ?>;
    new ApexCharts(document.querySelector('#installRadial'),{
        chart:{type:'radialBar',height:260,fontFamily:'Inter'},
        series:[
            Math.round((<?php echo $installationStats['assigned'] ?? 0; ?>/instTotal)*100),
            Math.round((<?php echo $installationStats['in_progress'] ?? 0; ?>/instTotal)*100),
            Math.round((<?php echo $installationStats['completed'] ?? 0; ?>/instTotal)*100),
            Math.round((<?php echo $installationStats['on_hold'] ?? 0; ?>/instTotal)*100)
        ],
        labels:['Assigned','In Progress','Completed','On Hold'],
        colors:['#6366f1','#f59e0b','#10b981','#ef4444'],
        plotOptions:{radialBar:{hollow:{size:'30%'},dataLabels:{name:{fontSize:'11px',fontWeight:700},value:{fontSize:'14px',fontWeight:900,formatter:v=>v+'%'},total:{show:true,label:'Total',fontSize:'10px',formatter:()=>'<?php echo $installationStats['total'] ?? 0; ?>'}},track:{background:'#f1f5f9'}}},
        stroke:{lineCap:'round'}
    }).render();

    // ── Priority Polar ──
    const priData=<?php echo json_encode($priorityBreakdown); ?>;
    const priLabels=priData.map(p=>(p.priority||'unknown').charAt(0).toUpperCase()+(p.priority||'unknown').slice(1));
    const priCounts=priData.map(p=>parseInt(p.cnt));
    new ApexCharts(document.querySelector('#priorityChart'),{
        chart:{type:'polarArea',height:260,fontFamily:'Inter'},
        series:priCounts.length?priCounts:[0],
        labels:priLabels.length?priLabels:['No Data'],
        colors:['#10b981','#3b82f6','#f59e0b','#ef4444'],
        plotOptions:{polarArea:{rings:{strokeWidth:1,strokeColor:'#f1f5f9'},spokes:{strokeWidth:1,connectorColors:'#f1f5f9'}}},
        fill:{opacity:.85},
        stroke:{width:1,colors:['#fff']},
        legend:{position:'bottom',fontSize:'10px',fontWeight:600,markers:{width:8,height:8,radius:4}},
        tooltip:{theme:'dark',style:{fontSize:'11px'}}
    }).render();
});
</script>

<?php
$content = ob_get_clean();
include '../includes/admin_layout.php';
?>

