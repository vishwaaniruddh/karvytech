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
$title = 'Operations Dashboard';

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
    // 1. Survey & BOQ Statistics
    $stmt = $db->prepare("
        SELECT 
            COUNT(DISTINCT s.id) as survey_total,
            SUM(CASE WHEN COALESCE(ss.survey_status, dsr.survey_status, 'pending') IN ('approved', 'completed') THEN 1 ELSE 0 END) as survey_done,
            SUM(CASE WHEN COALESCE(ss.survey_status, dsr.survey_status, 'pending') NOT IN ('approved', 'completed') THEN 1 ELSE 0 END) as survey_pending,
            SUM(CASE WHEN s.is_material_request_generated = 1 THEN 1 ELSE 0 END) as boq_received,
            SUM(CASE WHEN s.is_material_request_generated = 0 OR s.is_material_request_generated IS NULL THEN 1 ELSE 0 END) as boq_pending
        FROM site_delegations sd
        INNER JOIN sites s ON sd.site_id = s.id
        LEFT JOIN site_surveys ss ON sd.id = ss.delegation_id
        LEFT JOIN (
            SELECT id, delegation_id, site_id, survey_status
            FROM dynamic_survey_responses 
            WHERE id IN (SELECT MAX(id) FROM dynamic_survey_responses GROUP BY site_id)
        ) dsr ON (sd.id = dsr.delegation_id OR s.id = dsr.site_id)
        WHERE sd.vendor_id = ?
    ");
    $stmt->execute([$vendorId]);
    $surveyStats = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['survey_total' => 0, 'survey_done' => 0, 'survey_pending' => 0, 'boq_received' => 0, 'boq_pending' => 0];

    // 2. Installation Statistics
    $stmt = $db->prepare("
        SELECT 
            COUNT(DISTINCT site_id) as inst_total,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as inst_done,
            SUM(CASE WHEN status != 'completed' THEN 1 ELSE 0 END) as inst_pending
        FROM installation_delegations
        WHERE vendor_id = ?
    ");
    $stmt->execute([$vendorId]);
    $instStats = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['inst_total' => 0, 'inst_done' => 0, 'inst_pending' => 0];

    // Combine for easy access
    $stats['sites'] = [
        'survey_total' => (int) $surveyStats['survey_total'],
        'survey_done' => (int) $surveyStats['survey_done'],
        'survey_pending' => (int) $surveyStats['survey_pending'],
        'inst_total' => (int) $instStats['inst_total'],
        'inst_done' => (int) $instStats['inst_done'],
        'inst_pending' => (int) $instStats['inst_pending'],
        'boq_received' => (int) $surveyStats['boq_received'],
        'boq_pending' => (int) $surveyStats['boq_pending']
    ];

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

$surveyPct = $stats['sites']['survey_total'] > 0 ? round(($stats['sites']['survey_done'] / $stats['sites']['survey_total']) * 100) : 0;
$instPct = $stats['sites']['inst_total'] > 0 ? round(($stats['sites']['inst_done'] / $stats['sites']['inst_total']) * 100) : 0;

ob_start();
?>

<style>
    .dash {
        font-family: 'Inter', sans-serif;
    }

    /* ── Stat Cards (dark gradient, matching inventory) ─── */
    .stat-card {
        position: relative;
        overflow: hidden;
        border-radius: 20px;
        padding: 28px;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: -40%;
        right: -30%;
        width: 180px;
        height: 180px;
        border-radius: 50%;
        opacity: 0.08;
        transition: all 0.5s ease;
    }

    .stat-card:hover {
        transform: translateY(-4px);
    }

    .stat-card:hover::before {
        opacity: 0.14;
        transform: scale(1.2);
    }

    .stat-card.card-blue {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        box-shadow: 0 8px 32px rgba(15, 23, 42, 0.25);
    }

    .stat-card.card-blue::before {
        background: #3b82f6;
    }

    .stat-card.card-slate {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        box-shadow: 0 8px 32px rgba(15, 23, 42, 0.25);
    }

    .stat-card.card-slate::before {
        background: #3b82f6;
    }

    .stat-card.card-amber {
        background: linear-gradient(135deg, #78350f 0%, #92400e 100%);
        box-shadow: 0 8px 32px rgba(146, 64, 14, 0.2);
    }

    .stat-card.card-amber::before {
        background: #fbbf24;
    }

    .stat-card.card-purple {
        background: linear-gradient(135deg, #2e1065 0%, #4c1d95 100%);
        box-shadow: 0 8px 32px rgba(76, 29, 149, 0.2);
    }

    .stat-card.card-purple::before {
        background: #a78bfa;
    }

    .stat-card.card-green {
        background: linear-gradient(135deg, #064e3b 0%, #065f46 100%);
        box-shadow: 0 8px 32px rgba(6, 95, 70, 0.2);
    }

    .stat-card.card-green::before {
        background: #34d399;
    }

    .stat-card.card-cyan {
        background: linear-gradient(135deg, #164e63 0%, #0891b2 100%);
        box-shadow: 0 8px 32px rgba(8, 145, 178, 0.25);
    }

    .stat-card.card-cyan::before {
        background: #22d3ee;
    }

    .stat-card.card-rose {
        background: linear-gradient(135deg, #881337 0%, #be123c 100%);
        box-shadow: 0 8px 32px rgba(190, 18, 60, 0.2);
    }

    .stat-card.card-rose::before {
        background: #fb7185;
    }

    .stat-card.card-fuchsia {
        background: linear-gradient(135deg, #701a75 0%, #a21caf 100%);
        box-shadow: 0 8px 32px rgba(162, 28, 175, 0.2);
    }

    .stat-card.card-fuchsia::before {
        background: #f0abfc;
    }

    .stat-value {
        font-size: 2.5rem;
        font-weight: 900;
        line-height: 1;
        color: #ffffff;
        font-variant-numeric: tabular-nums;
        letter-spacing: -0.03em;
    }

    .stat-label {
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 0.15em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.5);
        margin-top: 8px;
    }

    .stat-icon-ring {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(8px);
        transition: all 0.3s ease;
    }

    .stat-card:hover .stat-icon-ring {
        background: rgba(255, 255, 255, 0.14);
        transform: scale(1.08);
    }

    /* ── Panel Cards ────────────────────── */
    .dash-panel {
        background: #ffffff;
        border: 1px solid #f1f5f9;
        border-radius: 20px;
        overflow: hidden;
    }

    .dash-panel-head {
        padding: 20px 24px;
        border-bottom: 1px solid #f8fafc;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .panel-title {
        font-size: 14px;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.01em;
    }

    .panel-badge {
        font-size: 9px;
        font-weight: 800;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        padding: 4px 10px;
        border-radius: 8px;
    }

    /* ── Progress Bars ──────────────────── */
    .progress-track {
        width: 100%;
        height: 8px;
        background: #f1f5f9;
        border-radius: 100px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        border-radius: 100px;
        transition: width 1.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* ── Quick Launch ───────────────────── */
    .quick-link {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        padding: 24px 16px;
        background: #ffffff;
        border: 1px solid #f1f5f9;
        border-radius: 16px;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .quick-link:hover {
        border-color: transparent;
        transform: translateY(-3px);
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.08);
    }

    .quick-link-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.25s ease;
    }

    .quick-link:hover .quick-link-icon {
        transform: scale(1.1) rotate(-3deg);
    }

    /* ── Activity Feed ──────────────────── */
    .activity-item {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 14px 20px;
        transition: background 0.15s ease;
    }

    .activity-item:hover {
        background: #f8fafc;
    }

    .activity-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-top: 5px;
        flex-shrink: 0;
    }

    /* ── Counter Animation ──────────────── */
    .counter-val {
        display: inline-block;
    }

    @keyframes fade-up {
        from {
            opacity: 0;
            transform: translateY(16px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fade-up {
        animation: fade-up 0.5s cubic-bezier(0.4, 0, 0.2, 1) both;
    }

    .fade-up-d1 {
        animation-delay: 0.05s;
    }

    .fade-up-d2 {
        animation-delay: 0.1s;
    }

    .fade-up-d3 {
        animation-delay: 0.15s;
    }

    .fade-up-d4 {
        animation-delay: 0.2s;
    }
</style>

<div class="dash">
    <!-- ═══════════ HEADER ═══════════ -->
    <!-- <div style="display:flex; flex-wrap:wrap; align-items:flex-end; justify-content:space-between; gap:16px; margin-bottom:32px;">
        <div>
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
                <div style="width:8px; height:8px; border-radius:50%; background:linear-gradient(135deg,#3b82f6,#8b5cf6); box-shadow:0 0 10px rgba(59,130,246,0.4);"></div>
                <span style="font-size:10px; font-weight:800; letter-spacing:0.18em; text-transform:uppercase; color:#3b82f6;">Operations Center</span>
            </div>
            <h1 style="font-size:26px; font-weight:900; color:#0f172a; letter-spacing:-0.03em; line-height:1.1;">Welcome back, <?php echo htmlspecialchars($currentUser['username']); ?></h1>
            <p style="font-size:13px; font-weight:500; color:#94a3b8; margin-top:6px;">Here's your operational overview for today.</p>
        </div>
        <div style="display:flex; align-items:center; gap:8px;">
            <div style="display:flex; align-items:center; gap:8px; padding:8px 16px; background:#f0f9ff; border:1px solid #bae6fd; border-radius:12px;">
                <div style="width:6px; height:6px; border-radius:50%; background:#3b82f6; animation:pulse 2s infinite;"></div>
                <span style="font-size:11px; font-weight:700; color:#0369a1; font-variant-numeric:tabular-nums;">VID-<?php echo str_pad($vendorId, 4, '0', STR_PAD_LEFT); ?></span>
            </div>
            <div style="padding:8px 16px; background:#f8fafc; border:1px solid #f1f5f9; border-radius:12px;">
                <span style="font-size:11px; font-weight:600; color:#64748b; font-family:'JetBrains Mono',monospace;"><?php echo date('d M Y'); ?></span>
            </div>
        </div>
    </div> -->

    <!-- ═══════════ STAT CARDS (Dark Gradient) ═══════════ -->
    <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:20px; margin-bottom:28px;">
        <!-- Survey Total -->
        <div class="stat-card card-slate fade-up">
            <div style="display:flex; align-items:flex-start; justify-content:space-between;">
                <div>
                    <div class="stat-value counter-val" data-target="<?php echo $stats['sites']['survey_total']; ?>">0
                    </div>
                    <div class="stat-label">Total Survey Sites</div>
                </div>
                <div class="stat-icon-ring"><svg width="22" height="22" fill="none" stroke="#60a5fa"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg></div>
            </div>
            <div style="margin-top:16px; display:flex; align-items:center; gap:6px;">
                <div style="width:24px; height:3px; border-radius:2px; background:rgba(96,165,250,0.4);"></div><span
                    style="font-size:10px; font-weight:600; color:rgba(255,255,255,0.35);">Delegated for survey</span>
            </div>
        </div>

        <!-- Survey Done -->
        <div class="stat-card card-green fade-up fade-up-d1">
            <div style="display:flex; align-items:flex-start; justify-content:space-between;">
                <div>
                    <div class="stat-value counter-val" data-target="<?php echo $stats['sites']['survey_done']; ?>">0
                    </div>
                    <div class="stat-label">Surveys Done</div>
                </div>
                <div class="stat-icon-ring"><svg width="22" height="22" fill="none" stroke="#34d399"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg></div>
            </div>
            <div style="margin-top:16px; display:flex; align-items:center; gap:6px;">
                <div style="width:24px; height:3px; border-radius:2px; background:rgba(52,211,153,0.4);"></div><span
                    style="font-size:10px; font-weight:600; color:rgba(255,255,255,0.35);">Approved reports</span>
            </div>
        </div>

        <!-- Survey Pending -->
        <div class="stat-card card-amber fade-up fade-up-d2">
            <div style="display:flex; align-items:flex-start; justify-content:space-between;">
                <div>
                    <div class="stat-value counter-val" data-target="<?php echo $stats['sites']['survey_pending']; ?>">0
                    </div>
                    <div class="stat-label">Surveys Pending</div>
                </div>
                <div class="stat-icon-ring"><svg width="22" height="22" fill="none" stroke="#fbbf24"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg></div>
            </div>
            <div style="margin-top:16px; display:flex; align-items:center; gap:6px;">
                <div style="width:24px; height:3px; border-radius:2px; background:rgba(251,191,36,0.4);"></div><span
                    style="font-size:10px; font-weight:600; color:rgba(255,255,255,0.35);">Awaiting submission</span>
            </div>
        </div>

        <!-- Total Installs -->
        <div class="stat-card card-purple fade-up fade-up-d3">
            <div style="display:flex; align-items:flex-start; justify-content:space-between;">
                <div>
                    <div class="stat-value counter-val" data-target="<?php echo $stats['sites']['inst_total']; ?>">0
                    </div>
                    <div class="stat-label">Total Installs</div>
                </div>
                <div class="stat-icon-ring"><svg width="22" height="22" fill="none" stroke="#a78bfa"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg></div>
            </div>
            <div style="margin-top:16px; display:flex; align-items:center; gap:6px;">
                <div style="width:24px; height:3px; border-radius:2px; background:rgba(167,139,250,0.4);"></div><span
                    style="font-size:10px; font-weight:600; color:rgba(255,255,255,0.35);">Delegated for install</span>
            </div>
        </div>

        <!-- Inst Done -->
        <div class="stat-card card-cyan fade-up fade-up-d2">
            <div style="display:flex; align-items:flex-start; justify-content:space-between;">
                <div>
                    <div class="stat-value counter-val" data-target="<?php echo $stats['sites']['inst_done']; ?>">0
                    </div>
                    <div class="stat-label">Installs Done</div>
                </div>
                <div class="stat-icon-ring"><svg width="22" height="22" fill="none" stroke="#22d3ee"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg></div>
            </div>
            <div style="margin-top:16px; display:flex; align-items:center; gap:6px;">
                <div style="width:24px; height:3px; border-radius:2px; background:rgba(34,211,238,0.4);"></div><span
                    style="font-size:10px; font-weight:600; color:rgba(255,255,255,0.35);">Completed deployments</span>
            </div>
        </div>

        <!-- Inst Pending -->
        <div class="stat-card card-rose fade-up fade-up-d3">
            <div style="display:flex; align-items:flex-start; justify-content:space-between;">
                <div>
                    <div class="stat-value counter-val" data-target="<?php echo $stats['sites']['inst_pending']; ?>">0
                    </div>
                    <div class="stat-label">Installs Pending</div>
                </div>
                <div class="stat-icon-ring"><svg width="22" height="22" fill="none" stroke="#fb7185"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg></div>
            </div>
            <div style="margin-top:16px; display:flex; align-items:center; gap:6px;">
                <div style="width:24px; height:3px; border-radius:2px; background:rgba(251,113,133,0.4);"></div><span
                    style="font-size:10px; font-weight:600; color:rgba(255,255,255,0.35);">Tasks in progress</span>
            </div>
        </div>

        <!-- BOQ Received -->
        <div class="stat-card card-blue fade-up fade-up-d4">
            <div style="display:flex; align-items:flex-start; justify-content:space-between;">
                <div>
                    <div class="stat-value counter-val" data-target="<?php echo $stats['sites']['boq_received']; ?>">0
                    </div>
                    <div class="stat-label">BOQ Received</div>
                </div>
                <div class="stat-icon-ring"><svg width="22" height="22" fill="none" stroke="#60a5fa"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg></div>
            </div>
            <div style="margin-top:16px; display:flex; align-items:center; gap:6px;">
                <div style="width:24px; height:3px; border-radius:2px; background:rgba(96,165,250,0.4);"></div><span
                    style="font-size:10px; font-weight:600; color:rgba(255,255,255,0.35);">Manifests generated</span>
            </div>
        </div>

        <!-- BOQ Not Received -->
        <div class="stat-card card-fuchsia fade-up fade-up-d4">
            <div style="display:flex; align-items:flex-start; justify-content:space-between;">
                <div>
                    <div class="stat-value counter-val" data-target="<?php echo $stats['sites']['boq_pending']; ?>">0
                    </div>
                    <div class="stat-label">BOQ Missing</div>
                </div>
                <div class="stat-icon-ring"><svg width="22" height="22" fill="none" stroke="#f0abfc"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg></div>
            </div>
            <div style="margin-top:16px; display:flex; align-items:center; gap:6px;">
                <div style="width:24px; height:3px; border-radius:2px; background:rgba(240,171,252,0.4);"></div><span
                    style="font-size:10px; font-weight:600; color:rgba(255,255,255,0.35);">Awaiting material
                    logistics</span>
            </div>
        </div>
    </div>

    <!-- ═══════════ MAIN GRID ═══════════ -->
    <div style="display:grid; grid-template-columns:2fr 1fr; gap:24px;">
        <!-- LEFT COLUMN -->
        <div style="display:flex; flex-direction:column; gap:24px;">

            <!-- Portfolio Life-Cycle -->
            <div class="dash-panel fade-up fade-up-d2">
                <div class="dash-panel-head">
                    <h3 class="panel-title">Portfolio Life-Cycle</h3>
                    <span class="panel-badge" style="background:#f8fafc; color:#94a3b8;">Analytics</span>
                </div>
                <div style="padding:24px;">
                    <!-- Survey Completion -->
                    <div style="margin-bottom:24px;">
                        <div
                            style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                            <span style="font-size:13px; font-weight:600; color:#334155;">Survey Completion Rate</span>
                            <span
                                style="font-size:13px; font-weight:800; color:#0f172a;"><?php echo $surveyPct; ?>%</span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill"
                                style="width:<?php echo $surveyPct; ?>%; background:linear-gradient(90deg,#3b82f6,#6366f1);">
                            </div>
                        </div>
                        <div style="display:flex; justify-content:space-between; margin-top:8px;">
                            <span
                                style="font-size:10px; font-weight:600; color:#94a3b8;"><?php echo $stats['sites']['survey_done']; ?>
                                Completed</span>
                            <span
                                style="font-size:10px; font-weight:600; color:#94a3b8;"><?php echo $stats['sites']['survey_pending']; ?>
                                Pending</span>
                        </div>
                    </div>

                    <!-- Installation Progress -->
                    <div style="margin-bottom:24px;">
                        <div
                            style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                            <span style="font-size:13px; font-weight:600; color:#334155;">Installation Success</span>
                            <span
                                style="font-size:13px; font-weight:800; color:#0f172a;"><?php echo $instPct; ?>%</span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill"
                                style="width:<?php echo $instPct; ?>%; background:linear-gradient(90deg,#10b981,#06b6d4);">
                            </div>
                        </div>
                        <div style="display:flex; justify-content:space-between; margin-top:8px;">
                            <span
                                style="font-size:10px; font-weight:600; color:#94a3b8;"><?php echo $stats['sites']['inst_done']; ?>
                                Sites Live</span>
                            <span
                                style="font-size:10px; font-weight:600; color:#94a3b8;"><?php echo $stats['sites']['inst_total'] - $stats['sites']['inst_done']; ?>
                                Remaining</span>
                        </div>
                    </div>

                    <!-- Material Distribution -->
                    <div style="padding-top:20px; border-top:1px solid #f1f5f9;">
                        <p
                            style="font-size:10px; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:#94a3b8; margin-bottom:14px;">
                            Material Cycle Distribution</p>
                        <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:12px;">
                            <div style="text-align:center; padding:14px 8px; background:#f8fafc; border-radius:12px;">
                                <div style="font-size:20px; font-weight:900; color:#0f172a; letter-spacing:-0.03em;">
                                    <?php echo $stats['materials']['total']; ?></div>
                                <div
                                    style="font-size:9px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.08em; margin-top:4px;">
                                    Total</div>
                            </div>
                            <div style="text-align:center; padding:14px 8px; background:#eff6ff; border-radius:12px;">
                                <div style="font-size:20px; font-weight:900; color:#2563eb; letter-spacing:-0.03em;">
                                    <?php echo $stats['materials']['approved']; ?></div>
                                <div
                                    style="font-size:9px; font-weight:700; color:#3b82f6; text-transform:uppercase; letter-spacing:0.08em; margin-top:4px;">
                                    Approved</div>
                            </div>
                            <div style="text-align:center; padding:14px 8px; background:#fefce8; border-radius:12px;">
                                <div style="font-size:20px; font-weight:900; color:#ca8a04; letter-spacing:-0.03em;">
                                    <?php echo $stats['materials']['dispatched']; ?></div>
                                <div
                                    style="font-size:9px; font-weight:700; color:#d97706; text-transform:uppercase; letter-spacing:0.08em; margin-top:4px;">
                                    In Transit</div>
                            </div>
                            <div style="text-align:center; padding:14px 8px; background:#ecfdf5; border-radius:12px;">
                                <div style="font-size:20px; font-weight:900; color:#059669; letter-spacing:-0.03em;">
                                    <?php echo $stats['materials']['delivered']; ?></div>
                                <div
                                    style="font-size:9px; font-weight:700; color:#10b981; text-transform:uppercase; letter-spacing:0.08em; margin-top:4px;">
                                    Delivered</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Launch -->
            <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:14px;" class="fade-up fade-up-d3">
                <a href="sites/" class="quick-link">
                    <div class="quick-link-icon" style="background:#eff6ff;">
                        <svg width="18" height="18" fill="none" stroke="#3b82f6" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <span style="font-size:11px; font-weight:700; color:#0f172a;">My Sites</span>
                </a>
                <a href="surveys.php" class="quick-link">
                    <div class="quick-link-icon" style="background:#fffbeb;">
                        <svg width="18" height="18" fill="none" stroke="#f59e0b" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                    <span style="font-size:11px; font-weight:700; color:#0f172a;">Surveys</span>
                </a>
                <a href="installations.php" class="quick-link">
                    <div class="quick-link-icon" style="background:#f5f3ff;">
                        <svg width="18" height="18" fill="none" stroke="#8b5cf6" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <span style="font-size:11px; font-weight:700; color:#0f172a;">Active Jobs</span>
                </a>
                <a href="inventory/" class="quick-link">
                    <div class="quick-link-icon" style="background:#ecfdf5;">
                        <svg width="18" height="18" fill="none" stroke="#10b981" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <span style="font-size:11px; font-weight:700; color:#0f172a;">Inventory</span>
                </a>
            </div>
        </div>

        <!-- RIGHT COLUMN -->
        <div style="display:flex; flex-direction:column; gap:24px;">
            <!-- Activity Feed -->
            <div class="dash-panel fade-up fade-up-d3" style="flex:1;">
                <div class="dash-panel-head">
                    <h3 class="panel-title">Activity Feed</h3>
                    <span class="panel-badge" style="background:#eff6ff; color:#3b82f6;">Live</span>
                </div>
                <?php if (empty($stats['recent_activities'])): ?>
                    <div style="padding:48px 20px; text-align:center;">
                        <div
                            style="width:48px; height:48px; border-radius:14px; background:#f8fafc; display:flex; align-items:center; justify-content:center; margin:0 auto 12px;">
                            <svg width="20" height="20" fill="none" stroke="#cbd5e1" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p style="font-size:12px; font-weight:600; color:#94a3b8;">No recent activity</p>
                    </div>
                <?php else: ?>
                    <div>
                        <?php foreach ($stats['recent_activities'] as $activity): ?>
                            <?php
                            $dotColor = '#94a3b8';
                            $bgColor = '#f8fafc';
                            if ($activity['type'] == 'survey') {
                                $dotColor = '#f59e0b';
                                $bgColor = '#fffbeb';
                            }
                            if ($activity['type'] == 'installation') {
                                $dotColor = '#8b5cf6';
                                $bgColor = '#f5f3ff';
                            }
                            if ($activity['type'] == 'request') {
                                $dotColor = '#10b981';
                                $bgColor = '#ecfdf5';
                            }
                            ?>
                            <div class="activity-item" style="border-bottom:1px solid #f8fafc;">
                                <div class="activity-dot"
                                    style="background:<?php echo $dotColor; ?>; box-shadow:0 0 0 3px <?php echo $bgColor; ?>;">
                                </div>
                                <div style="flex:1; min-width:0;">
                                    <p
                                        style="font-size:12px; font-weight:600; color:#1e293b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                        <?php echo htmlspecialchars($activity['activity']); ?></p>
                                    <p style="font-size:10px; font-weight:500; color:#94a3b8; margin-top:3px;">
                                        <?php echo date('M j, Y • g:i A', strtotime($activity['activity_date'])); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div style="padding:12px; text-align:center; border-top:1px solid #f8fafc;">
                        <button onclick="location.reload()"
                            style="background:none; border:none; font-size:11px; font-weight:700; color:#3b82f6; cursor:pointer; padding:4px 12px; border-radius:8px; transition:background 0.15s;"
                            onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background='none'">Refresh
                            Feed</button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- System Status -->
            <div class="fade-up fade-up-d4"
                style="background:linear-gradient(135deg,#0f172a,#1e293b); border-radius:20px; padding:24px; color:#fff;">
                <div style="display:flex; align-items:center; gap:12px; margin-bottom:20px;">
                    <div
                        style="width:40px; height:40px; border-radius:12px; background:rgba(16,185,129,0.12); border:1px solid rgba(16,185,129,0.2); display:flex; align-items:center; justify-content:center;">
                        <svg width="18" height="18" fill="none" stroke="#34d399" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div>
                        <p style="font-size:14px; font-weight:700;">Secure Access</p>
                        <p
                            style="font-size:9px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#34d399; margin-top:2px;">
                            Verified Node</p>
                    </div>
                </div>
                <div style="display:flex; flex-direction:column; gap:0;">
                    <div
                        style="display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid rgba(255,255,255,0.04);">
                        <span style="font-size:11px; font-weight:500; color:#64748b;">Server Time</span>
                        <span
                            style="font-size:11px; font-weight:600; color:#e2e8f0; font-family:'JetBrains Mono',monospace;"><?php echo date('H:i:s T'); ?></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; padding:10px 0;">
                        <span style="font-size:11px; font-weight:500; color:#64748b;">Active Role</span>
                        <span
                            style="font-size:11px; font-weight:700; color:#fbbf24; text-transform:uppercase; letter-spacing:0.06em;"><?php echo htmlspecialchars($currentUser['role']); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Counter animation
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.counter-val[data-target]').forEach(el => {
            const target = parseInt(el.dataset.target) || 0;
            const duration = 1000;
            const start = performance.now();
            function update(now) {
                const elapsed = now - start;
                const progress = Math.min(elapsed / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 4);
                el.textContent = Math.round(target * eased);
                if (progress < 1) requestAnimationFrame(update);
            }
            requestAnimationFrame(update);
        });
    });
</script>

<?php
$content = ob_get_clean();
include '../includes/vendor_layout.php';
?>