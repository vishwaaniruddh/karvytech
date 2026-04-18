<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../models/Inventory.php';

// Require vendor authentication
Auth::requireVendor();

$vendorId = Auth::getVendorId();
$inventoryModel = new Inventory();

// Get summary statistics (Server-side for initial load)
$totalDispatches = $inventoryModel->getContractorDispatchCount($vendorId);
$totalItems = $inventoryModel->getContractorTotalItems($vendorId);
$pendingConfirmations = $inventoryModel->getContractorPendingConfirmations($vendorId);

// Get accepted count
require_once __DIR__ . '/../../config/database.php';
$db = Database::getInstance()->getConnection();
$acceptedCount = $db->query("SELECT COUNT(*) FROM inventory_dispatches WHERE vendor_id = $vendorId AND dispatch_status = 'confirmed'")->fetchColumn();

// Get delivered count
$deliveredCount = $db->query("SELECT COUNT(*) FROM inventory_dispatches WHERE vendor_id = $vendorId AND dispatch_status = 'delivered'")->fetchColumn();

$title = 'Inventory Command Center';
ob_start();
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap');

    .inv-dashboard { font-family: 'Inter', sans-serif; }

    /* ── Stat Cards ───────────────────────── */
    .stat-card {
        position: relative;
        overflow: hidden;
        border-radius: 20px;
        padding: 28px;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(255,255,255,0.08);
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
    .stat-card:hover { transform: translateY(-4px); }
    .stat-card:hover::before { opacity: 0.14; transform: scale(1.2); }

    .stat-card.card-total {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        box-shadow: 0 8px 32px rgba(15, 23, 42, 0.25);
    }
    .stat-card.card-total::before { background: #3b82f6; }

    .stat-card.card-delivered {
        background: linear-gradient(135deg, #1e3a5f 0%, #1e40af 100%);
        box-shadow: 0 8px 32px rgba(30, 64, 175, 0.2);
    }
    .stat-card.card-delivered::before { background: #60a5fa; }

    .stat-card.card-confirmed {
        background: linear-gradient(135deg, #064e3b 0%, #065f46 100%);
        box-shadow: 0 8px 32px rgba(6, 95, 70, 0.2);
    }
    .stat-card.card-confirmed::before { background: #34d399; }

    .stat-card.card-pending {
        background: linear-gradient(135deg, #78350f 0%, #92400e 100%);
        box-shadow: 0 8px 32px rgba(146, 64, 14, 0.2);
    }
    .stat-card.card-pending::before { background: #fbbf24; }

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
        color: rgba(255,255,255,0.5);
        margin-top: 8px;
    }
    .stat-icon-ring {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,0.08);
        backdrop-filter: blur(8px);
        transition: all 0.3s ease;
    }
    .stat-card:hover .stat-icon-ring { background: rgba(255,255,255,0.14); transform: scale(1.08); }

    /* ── Filter Bar ───────────────────────── */
    .filter-bar {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        padding: 24px 28px;
    }
    .filter-input {
        width: 100%;
        padding: 10px 14px 10px 40px;
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 500;
        color: #1e293b;
        transition: all 0.25s ease;
        outline: none;
    }
    .filter-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
        background: #fff;
    }
    .filter-select {
        width: 100%;
        padding: 10px 14px;
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 500;
        color: #1e293b;
        transition: all 0.25s ease;
        outline: none;
        cursor: pointer;
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 10px center;
        background-repeat: no-repeat;
        background-size: 1.2em 1.2em;
    }
    .filter-select:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
    }
    .filter-date {
        width: 100%;
        padding: 10px 14px;
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 500;
        color: #1e293b;
        transition: all 0.25s ease;
        outline: none;
    }
    .filter-date:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
    }
    .filter-label {
        display: block;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #94a3b8;
        margin-bottom: 8px;
    }

    /* ── Data Table ───────────────────────── */
    .data-panel {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.04);
        overflow: hidden;
    }
    .data-panel-header {
        padding: 20px 28px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .data-table {
        width: 100%;
        text-align: left;
        border-collapse: collapse;
    }
    .data-table thead th {
        padding: 14px 24px;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #94a3b8;
        background: #f8fafc;
        border-bottom: 1px solid #f1f5f9;
        white-space: nowrap;
    }
    .data-table tbody tr {
        border-bottom: 1px solid #f8fafc;
        transition: all 0.2s ease;
    }
    .data-table tbody tr:hover {
        background: linear-gradient(90deg, #f0f9ff 0%, #f8fafc 100%);
    }
    .data-table tbody td {
        padding: 16px 24px;
        vertical-align: middle;
    }

    /* ── Status Badges ────────────────────── */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 14px;
        border-radius: 100px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .status-badge .dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
    }
    .status-dispatched {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
    }
    .status-dispatched .dot { background: #f59e0b; }

    .status-in_transit {
        background: #dbeafe;
        color: #1e40af;
        border: 1px solid #bfdbfe;
    }
    .status-in_transit .dot { background: #3b82f6; animation: pulse-dot 2s infinite; }

    .status-delivered {
        background: #e0e7ff;
        color: #3730a3;
        border: 1px solid #c7d2fe;
    }
    .status-delivered .dot { background: #6366f1; }

    .status-confirmed {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }
    .status-confirmed .dot { background: #10b981; }

    @keyframes pulse-dot {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(1.4); }
    }

    /* ── Action Buttons ───────────────────── */
    .action-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 16px;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.25s ease;
        letter-spacing: 0.02em;
    }
    .action-btn-view {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
    }
    .action-btn-view:hover {
        background: #e2e8f0;
        color: #1e293b;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    .action-btn-audit {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: #ffffff;
        border: none;
        box-shadow: 0 2px 8px rgba(59,130,246,0.3);
    }
    .action-btn-audit:hover {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        transform: translateY(-1px);
        box-shadow: 0 4px 16px rgba(59,130,246,0.4);
    }

    /* ── Pagination ────────────────────────── */
    .pag-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        padding: 0 10px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 600;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .pag-btn:hover { background: #f1f5f9; color: #1e293b; }
    .pag-btn.active {
        background: linear-gradient(135deg, #1e293b, #334155);
        color: #fff;
        border-color: #334155;
        box-shadow: 0 2px 8px rgba(30,41,59,0.2);
    }
    .pag-btn:disabled { opacity: 0.35; pointer-events: none; }

    /* ── Header Actions ───────────────────── */
    .header-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.02em;
        text-decoration: none;
        transition: all 0.25s ease;
        cursor: pointer;
        border: none;
    }
    .header-btn-export {
        background: #f0fdf4;
        color: #166534;
        border: 1px solid #bbf7d0;
    }
    .header-btn-export:hover {
        background: #dcfce7;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(22,101,52,0.1);
    }
    .header-btn-back {
        background: #f8fafc;
        color: #475569;
        border: 1px solid #e2e8f0;
    }
    .header-btn-back:hover {
        background: #f1f5f9;
        transform: translateY(-1px);
    }

    /* ── Skeleton loader ──────────────────── */
    .skeleton {
        background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
        background-size: 200% 100%;
        animation: shimmer 1.5s infinite;
        border-radius: 8px;
    }
    @keyframes shimmer {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    /* ── Counter animation ────────────────── */
    .counter-animate {
        display: inline-block;
        transition: all 0.3s ease;
    }

    /* ── Responsive ───────────────────────── */
    @media (max-width: 1024px) {
        .stat-value { font-size: 2rem; }
        .data-table thead th, .data-table tbody td { padding: 12px 16px; }
    }
    @media (max-width: 768px) {
        .stats-grid { grid-template-columns: repeat(2, 1fr) !important; }
        .filter-grid { grid-template-columns: 1fr !important; }
    }
</style>

<div class="inv-dashboard">
    <!-- ═══════════════════ HEADER ═══════════════════ -->
    <div style="display:flex; flex-wrap:wrap; align-items:flex-end; justify-content:space-between; gap:16px; margin-bottom:32px;">
        <div>
            <div style="display:flex; align-items:center; gap:12px; margin-bottom:6px;">
                <div style="width:10px; height:10px; border-radius:50%; background:linear-gradient(135deg,#3b82f6,#8b5cf6); box-shadow:0 0 12px rgba(59,130,246,0.4);"></div>
                <span style="font-size:10px; font-weight:800; letter-spacing:0.2em; text-transform:uppercase; color:#3b82f6;">Inventory Command Center</span>
            </div>
            <h1 style="font-size:28px; font-weight:900; color:#0f172a; letter-spacing:-0.03em; line-height:1.2;">Material Receipts</h1>
            <p style="font-size:13px; font-weight:500; color:#94a3b8; margin-top:6px;">Track, audit, and reconcile dispatched materials from Karvy Admin.</p>
        </div>
        <div style="display:flex; align-items:center; gap:10px;">
            <button onclick="exportToExcel()" class="header-btn header-btn-export">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export Excel
            </button>
            <a href="../dashboard.php" class="header-btn header-btn-back">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Dashboard
            </a>
        </div>
    </div>

    <!-- ═══════════════════ STAT CARDS ═══════════════════ -->
    <div class="stats-grid" style="display:grid; grid-template-columns:repeat(4, 1fr); gap:20px; margin-bottom:28px;">
        <!-- Total Manifests -->
        <div class="stat-card card-total">
            <div style="display:flex; align-items:flex-start; justify-content:space-between;">
                <div>
                    <div class="stat-value" data-target="<?php echo $totalDispatches; ?>">0</div>
                    <div class="stat-label">Total Manifests</div>
                </div>
                <div class="stat-icon-ring">
                    <svg width="22" height="22" fill="none" stroke="#60a5fa" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            <div style="margin-top:16px; display:flex; align-items:center; gap:6px;">
                <div style="width:24px; height:3px; border-radius:2px; background:rgba(96,165,250,0.4);"></div>
                <span style="font-size:10px; font-weight:600; color:rgba(255,255,255,0.35);">All dispatches received</span>
            </div>
        </div>

        <!-- Delivered -->
        <div class="stat-card card-delivered">
            <div style="display:flex; align-items:flex-start; justify-content:space-between;">
                <div>
                    <div class="stat-value" data-target="<?php echo $deliveredCount; ?>">0</div>
                    <div class="stat-label">Delivered</div>
                </div>
                <div class="stat-icon-ring">
                    <svg width="22" height="22" fill="none" stroke="#93c5fd" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3L9 8m-5 5h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293h3.172a1 1 0 00.707-.293l2.414-2.414A1 1 0 0117.414 13H20"/></svg>
                </div>
            </div>
            <div style="margin-top:16px; display:flex; align-items:center; gap:6px;">
                <div style="width:24px; height:3px; border-radius:2px; background:rgba(147,197,253,0.4);"></div>
                <span style="font-size:10px; font-weight:600; color:rgba(255,255,255,0.35);">Awaiting audit review</span>
            </div>
        </div>

        <!-- Confirmed -->
        <div class="stat-card card-confirmed">
            <div style="display:flex; align-items:flex-start; justify-content:space-between;">
                <div>
                    <div class="stat-value" data-target="<?php echo $acceptedCount; ?>">0</div>
                    <div class="stat-label">Confirmed</div>
                </div>
                <div class="stat-icon-ring">
                    <svg width="22" height="22" fill="none" stroke="#6ee7b7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div style="margin-top:16px; display:flex; align-items:center; gap:6px;">
                <div style="width:24px; height:3px; border-radius:2px; background:rgba(52,211,153,0.4);"></div>
                <span style="font-size:10px; font-weight:600; color:rgba(255,255,255,0.35);">Audit completed</span>
            </div>
        </div>

        <!-- Pending Action -->
        <div class="stat-card card-pending">
            <div style="display:flex; align-items:flex-start; justify-content:space-between;">
                <div>
                    <div class="stat-value" data-target="<?php echo $pendingConfirmations; ?>">0</div>
                    <div class="stat-label">Pending Action</div>
                </div>
                <div class="stat-icon-ring">
                    <svg width="22" height="22" fill="none" stroke="#fcd34d" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div style="margin-top:16px; display:flex; align-items:center; gap:6px;">
                <div style="width:24px; height:3px; border-radius:2px; background:rgba(251,191,36,0.4);"></div>
                <span style="font-size:10px; font-weight:600; color:rgba(255,255,255,0.35);">Requires your attention</span>
            </div>
        </div>
    </div>

    <!-- ═══════════════════ FILTER BAR ═══════════════════ -->
    <div class="filter-bar" style="margin-bottom:24px;">
        <div class="filter-grid" style="display:grid; grid-template-columns:2fr 1fr 1fr 1fr; gap:20px; align-items:end;">
            <div>
                <label class="filter-label">Search Dispatch</label>
                <div style="position:relative;">
                    <svg style="position:absolute; left:13px; top:50%; transform:translateY(-50%); pointer-events:none;" width="15" height="15" fill="none" stroke="#94a3b8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" id="searchInput" placeholder="Search by Dispatch #, Site, or Tracking..." class="filter-input">
                </div>
            </div>
            <div>
                <label class="filter-label">Status</label>
                <select id="statusFilter" class="filter-select">
                    <option value="">All Statuses</option>
                    <option value="dispatched">Dispatched</option>
                    <option value="in_transit">In Transit</option>
                    <option value="delivered">Delivered</option>
                    <option value="confirmed">Confirmed</option>
                </select>
            </div>
            <div>
                <label class="filter-label">Date From</label>
                <input type="date" id="dateFrom" class="filter-date">
            </div>
            <div>
                <label class="filter-label">Date To</label>
                <input type="date" id="dateTo" class="filter-date">
            </div>
        </div>
    </div>

    <!-- ═══════════════════ DATA TABLE ═══════════════════ -->
    <div class="data-panel" style="margin-bottom:32px;">
        <div class="data-panel-header">
            <div>
                <h2 style="font-size:14px; font-weight:800; color:#0f172a; letter-spacing:-0.01em;">Dispatch Ledger</h2>
                <p style="font-size:11px; font-weight:500; color:#94a3b8; margin-top:2px;">Showing <span id="showingStart">0</span>–<span id="showingEnd">0</span> of <span id="totalResults">0</span> records</p>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
                <div style="width:8px; height:8px; border-radius:50%; background:#22c55e; box-shadow:0 0 8px rgba(34,197,94,0.4); animation:pulse-dot 2s infinite;"></div>
                <span style="font-size:10px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:0.1em;">Live</span>
            </div>
        </div>
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:50px; text-align:center;">#</th>
                        <th>Dispatch Manifest</th>
                        <th>Deployment Site</th>
                        <th>Logistics</th>
                        <th style="text-align:center;">Items</th>
                        <th style="text-align:center;">Status</th>
                        <th style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody id="materialsTableBody">
                    <!-- Loaded via JS -->
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div style="padding:18px 28px; border-top:1px solid #f1f5f9; display:flex; align-items:center; justify-content:space-between;">
            <div style="font-size:11px; font-weight:600; color:#94a3b8;">
                Displaying <span id="showingStart2" style="font-weight:700; color:#475569;">0</span>–<span id="showingEnd2" style="font-weight:700; color:#475569;">0</span> of <span id="totalResults2" style="font-weight:700; color:#475569;">0</span> manifests
            </div>
            <div id="paginationControls" style="display:flex; gap:6px;">
                <!-- Loaded via JS -->
            </div>
        </div>
    </div>
</div>

<script>
// ── COUNTER ANIMATION ──────────────────────────
function animateCounters() {
    document.querySelectorAll('.stat-value[data-target]').forEach(el => {
        const target = parseInt(el.dataset.target) || 0;
        const duration = 1200;
        const start = performance.now();
        
        function update(now) {
            const elapsed = now - start;
            const progress = Math.min(elapsed / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 4); // ease-out quart
            el.textContent = Math.round(target * eased);
            if (progress < 1) requestAnimationFrame(update);
        }
        requestAnimationFrame(update);
    });
}

// ── DATA FETCHING ──────────────────────────────
let currentPage = 1;
const limit = 10;

function fetchMaterials(page = 1) {
    currentPage = page;
    const search = document.getElementById('searchInput').value;
    const status = document.getElementById('statusFilter').value;
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    
    const url = `api/get-received-materials.php?page=${page}&limit=${limit}&search=${encodeURIComponent(search)}&status=${status}&date_from=${dateFrom}&date_to=${dateTo}`;
    
    // Skeleton loading state
    const tbody = document.getElementById('materialsTableBody');
    let skeletonHtml = '';
    for (let i = 0; i < 5; i++) {
        skeletonHtml += `<tr>
            <td style="padding:18px 24px; text-align:center;"><div class="skeleton" style="width:24px; height:14px; margin:0 auto;"></div></td>
            <td style="padding:18px 24px;"><div class="skeleton" style="width:70%; height:14px; margin-bottom:6px;"></div><div class="skeleton" style="width:40%; height:10px;"></div></td>
            <td style="padding:18px 24px;"><div class="skeleton" style="width:60%; height:14px; margin-bottom:6px;"></div><div class="skeleton" style="width:80%; height:10px;"></div></td>
            <td style="padding:18px 24px;"><div class="skeleton" style="width:50%; height:14px; margin-bottom:6px;"></div><div class="skeleton" style="width:65%; height:10px;"></div></td>
            <td style="padding:18px 24px; text-align:center;"><div class="skeleton" style="width:40px; height:28px; margin:0 auto; border-radius:8px;"></div></td>
            <td style="padding:18px 24px; text-align:center;"><div class="skeleton" style="width:80px; height:26px; margin:0 auto; border-radius:20px;"></div></td>
            <td style="padding:18px 24px; text-align:center;"><div class="skeleton" style="width:90px; height:32px; margin:0 auto; border-radius:10px;"></div></td>
        </tr>`;
    }
    tbody.innerHTML = skeletonHtml;

    fetch(url)
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                renderTable(res.data, res.pagination);
                renderPagination(res.pagination);
            }
        })
        .catch(err => {
            console.error('Fetch error:', err);
            tbody.innerHTML = `<tr><td colspan="7" style="padding:60px 0; text-align:center;">
                <svg style="margin:0 auto 12px;" width="32" height="32" fill="none" stroke="#e11d48" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <p style="font-size:12px; font-weight:700; color:#e11d48; text-transform:uppercase; letter-spacing:0.1em;">Connection Interrupted</p>
                <p style="font-size:11px; color:#94a3b8; margin-top:4px;">Unable to synchronize with the inventory ledger.</p>
            </td></tr>`;
        });
}

function renderTable(data, pagination) {
    const tbody = document.getElementById('materialsTableBody');

    if (data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" style="padding:60px 0; text-align:center;">
            <svg style="margin:0 auto 12px;" width="40" height="40" fill="none" stroke="#cbd5e1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            <p style="font-size:12px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.1em;">No Records Found</p>
            <p style="font-size:11px; color:#cbd5e1; margin-top:4px;">Try adjusting your search filters.</p>
        </td></tr>`;
        updatePagInfo(0, 0, 0);
        return;
    }

    const statusConfig = {
        'dispatched': { cls: 'status-dispatched', label: 'Dispatched' },
        'in_transit': { cls: 'status-in_transit', label: 'In Transit' },
        'delivered':  { cls: 'status-delivered',  label: 'Delivered' },
        'confirmed':  { cls: 'status-confirmed',  label: 'Confirmed' },
    };

    let html = '';
    data.forEach((row, index) => {
        const serial = ((pagination.page - 1) * pagination.limit) + index + 1;
        const st = statusConfig[row.dispatch_status] || statusConfig['dispatched'];
        const date = row.dispatch_date ? new Date(row.dispatch_date).toLocaleDateString('en-GB', {day:'2-digit', month:'short', year:'numeric'}) : '—';
        const itemCount = row.actual_item_count || row.total_items || 0;
        
        const isAuditable = row.dispatch_status !== 'confirmed';
        const actionLabel = row.dispatch_status === 'confirmed' ? 'Review' : 'Audit';
        const actionCls = row.dispatch_status === 'confirmed' ? 'action-btn-view' : 'action-btn-audit';

        html += `<tr>
            <td style="text-align:center;">
                <span style="font-family:'JetBrains Mono',monospace; font-size:11px; font-weight:700; color:#cbd5e1;">${String(serial).padStart(2,'0')}</span>
            </td>
            <td>
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:36px; height:36px; border-radius:10px; background:linear-gradient(135deg,#eff6ff,#dbeafe); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <svg width="16" height="16" fill="none" stroke="#3b82f6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                        <p style="font-size:12px; font-weight:700; color:#0f172a; letter-spacing:-0.01em;">${row.dispatch_number}</p>
                        <p style="font-size:10px; font-weight:500; color:#94a3b8; margin-top:2px;">${date}</p>
                    </div>
                </div>
            </td>
            <td>
                <p style="font-size:12px; font-weight:700; color:#1e293b;">${row.site_code || 'GENERAL'}</p>
                <p style="font-size:10px; font-weight:500; color:#94a3b8; margin-top:2px; max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${row.site_location || 'Central Warehouse'}</p>
            </td>
            <td>
                <p style="font-size:11px; font-weight:600; color:#475569;">${row.courier_name || 'Direct'}</p>
                <p style="font-family:'JetBrains Mono',monospace; font-size:10px; font-weight:500; color:#94a3b8; margin-top:2px;">${row.tracking_number || '—'}</p>
            </td>
            <td style="text-align:center;">
                <span style="display:inline-flex; align-items:center; justify-content:center; min-width:38px; height:28px; padding:0 10px; border-radius:8px; background:#f1f5f9; font-size:12px; font-weight:800; color:#334155; font-family:'JetBrains Mono',monospace;">${itemCount}</span>
            </td>
            <td style="text-align:center;">
                <span class="status-badge ${st.cls}">
                    <span class="dot"></span>
                    ${st.label}
                </span>
            </td>
            <td style="text-align:center;">
                <a href="../audit-dispatch.php?id=${row.id}" class="action-btn ${actionCls}">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    ${actionLabel}
                </a>
            </td>
        </tr>`;
    });

    tbody.innerHTML = html;

    const start = ((pagination.page - 1) * pagination.limit) + 1;
    const end = Math.min(pagination.page * pagination.limit, pagination.total);
    updatePagInfo(start, end, pagination.total);
}

function updatePagInfo(start, end, total) {
    ['showingStart','showingStart2'].forEach(id => document.getElementById(id).textContent = start);
    ['showingEnd','showingEnd2'].forEach(id => document.getElementById(id).textContent = end);
    ['totalResults','totalResults2'].forEach(id => document.getElementById(id).textContent = total);
}

function renderPagination(pagination) {
    const container = document.getElementById('paginationControls');
    let html = '';

    html += `<button onclick="fetchMaterials(${pagination.page - 1})" class="pag-btn" ${pagination.page === 1 ? 'disabled' : ''}>
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    </button>`;

    const totalPages = pagination.pages || pagination.totalPages || 1;
    const maxVisible = 5;
    let startPage = Math.max(1, pagination.page - Math.floor(maxVisible / 2));
    let endPage = Math.min(totalPages, startPage + maxVisible - 1);
    if (endPage - startPage < maxVisible - 1) startPage = Math.max(1, endPage - maxVisible + 1);

    if (startPage > 1) {
        html += `<button onclick="fetchMaterials(1)" class="pag-btn">1</button>`;
        if (startPage > 2) html += `<span style="padding:0 4px; color:#cbd5e1; font-size:12px;">…</span>`;
    }

    for (let i = startPage; i <= endPage; i++) {
        html += `<button onclick="fetchMaterials(${i})" class="pag-btn ${i === pagination.page ? 'active' : ''}">${i}</button>`;
    }

    if (endPage < totalPages) {
        if (endPage < totalPages - 1) html += `<span style="padding:0 4px; color:#cbd5e1; font-size:12px;">…</span>`;
        html += `<button onclick="fetchMaterials(${totalPages})" class="pag-btn">${totalPages}</button>`;
    }

    html += `<button onclick="fetchMaterials(${pagination.page + 1})" class="pag-btn" ${pagination.page >= totalPages ? 'disabled' : ''}>
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </button>`;

    container.innerHTML = html;
}

function exportToExcel() {
    const search = document.getElementById('searchInput').value;
    const status = document.getElementById('statusFilter').value;
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    
    const url = `api/export-received-materials.php?search=${encodeURIComponent(search)}&status=${status}&date_from=${dateFrom}&date_to=${dateTo}`;
    window.location.href = url;
}

// ── EVENT LISTENERS ────────────────────────────
let searchDebounce;
document.getElementById('searchInput').addEventListener('input', () => {
    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(() => fetchMaterials(1), 350);
});
document.getElementById('statusFilter').addEventListener('change', () => fetchMaterials(1));
document.getElementById('dateFrom').addEventListener('change', () => fetchMaterials(1));
document.getElementById('dateTo').addEventListener('change', () => fetchMaterials(1));

// ── INITIALIZATION ─────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    animateCounters();
    fetchMaterials(1);
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/vendor_layout.php';
?>