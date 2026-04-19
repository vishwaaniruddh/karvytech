<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/Vendor.php';
require_once __DIR__ . '/../../models/VendorPermission.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$vendorModel = new Vendor();
$permissionModel = new VendorPermission();
$allPermissions = $permissionModel->getAllPermissions();

$title = 'Vendor Management';
ob_start();
?>

<style>
/* ── Vendors Page Premium Styles ── */
.v-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:16px}
.v-title-area h1{font-size:1.5rem;font-weight:800;color:#0f172a;letter-spacing:-.02em}
.v-title-area p{font-size:13px;font-weight:500;color:#64748b;margin-top:4px}
.v-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap}

/* ── Stat Cards (Match Dashboard2) ── */
.v-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 32px; }
@media(max-width:1024px){.v-stats{grid-template-columns:repeat(2,1fr)}}
@media(max-width:640px){.v-stats{grid-template-columns:1fr}}

.stat-card {
    position: relative;
    border-radius: 20px;
    padding: 24px 28px;
    color: #ffffff;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 140px;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: -50px;
    right: -50px;
    width: 150px;
    height: 150px;
    border-radius: 50%;
    opacity: 0.08;
    transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.stat-card:hover {
    transform: translateY(-4px);
}

.stat-card:hover::before {
    opacity: 0.14;
    transform: scale(1.2);
}

.stat-card.card-slate {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    box-shadow: 0 8px 32px rgba(15, 23, 42, 0.25);
}
.stat-card.card-slate::before { background: #3b82f6; }

.stat-card.card-cyan {
    background: linear-gradient(135deg, #164e63 0%, #0891b2 100%);
    box-shadow: 0 8px 32px rgba(8, 145, 178, 0.25);
}
.stat-card.card-cyan::before { background: #22d3ee; }

.stat-card.card-amber {
    background: linear-gradient(135deg, #78350f 0%, #92400e 100%);
    box-shadow: 0 8px 32px rgba(146, 64, 14, 0.2);
}
.stat-card.card-amber::before { background: #fbbf24; }

.stat-card.card-purple {
    background: linear-gradient(135deg, #2e1065 0%, #4c1d95 100%);
    box-shadow: 0 8px 32px rgba(76, 29, 149, 0.2);
}
.stat-card.card-purple::before { background: #a78bfa; }

/* Active outline if filtered */
.stat-card.active { border: 2px solid rgba(255,255,255,0.8); }

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
    font-weight: 800;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.6);
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
.stat-icon-ring svg { width: 22px; height: 22px; }

/* Search Bar */
.v-search-wrap{background:#fff;border:1px solid #f1f5f9;border-radius:16px;padding:14px 20px;margin-bottom:20px;display:flex;align-items:center;gap:12px;flex-wrap:wrap}
.v-search{position:relative;flex:1;min-width:220px}
.v-search input{width:100%;padding:10px 14px 10px 40px;border:1px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:500;color:#1e293b;background:#f8fafc;transition:all .2s ease;outline:none}
.v-search input:focus{border-color:#6366f1;background:#fff;box-shadow:0 0 0 3px rgba(99,102,241,.08)}
.v-search .v-search-icon{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#94a3b8}
.v-filter select{padding:10px 32px 10px 14px;border:1px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:500;color:#1e293b;background:#f8fafc;cursor:pointer;outline:none;transition:all .2s ease;-webkit-appearance:none;appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%2394a3b8' viewBox='0 0 20 20'%3E%3Cpath d='M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 10px center}
.v-filter select:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.08)}
.v-count{display:flex;align-items:center;gap:6px;padding:8px 14px;background:#f1f5f9;border-radius:10px;font-size:12px;font-weight:600;color:#475569}
.v-count span{font-weight:800;color:#0f172a}

/* Premium Table */
.v-table-wrap{background:#fff;border:1px solid #f1f5f9;border-radius:16px;overflow:hidden;position:relative;min-height:300px}
.v-table-loading{position:absolute;inset:0;background:rgba(255,255,255,.85);z-index:10;display:none;align-items:center;justify-content:center}
.v-table-loading.show{display:flex}
.v-table-loading .spinner{width:32px;height:32px;border:3px solid #e2e8f0;border-top-color:#6366f1;border-radius:50%;animation:spin .6s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
.v-table{width:100%;border-collapse:separate;border-spacing:0}
.v-table thead{background:linear-gradient(135deg,#f8fafc,#f1f5f9)}
.v-table th{padding:12px 16px;text-align:left;font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#64748b;border-bottom:1px solid #e2e8f0;white-space:nowrap}
.v-table td{padding:14px 16px;font-size:13px;color:#334155;border-bottom:1px solid #f8fafc;vertical-align:middle}
.v-table tbody tr{transition:all .15s ease}
.v-table tbody tr:hover{background:#fafbff}
.v-table tbody tr:last-child td{border-bottom:none}

/* Row Number */
.v-row-num{width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;background:#f1f5f9;border-radius:8px;font-size:11px;font-weight:700;color:#94a3b8}

/* Avatar */
.v-avatar{width:36px;height:36px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;flex-shrink:0;transition:transform .2s ease}
.v-table tbody tr:hover .v-avatar{transform:scale(1.1)}
.v-avatar-blue{background:#eff6ff;color:#3b82f6}
.v-avatar-green{background:#ecfdf5;color:#059669}
.v-avatar-purple{background:#f5f3ff;color:#7c3aed}
.v-avatar-orange{background:#fff7ed;color:#ea580c}

/* Name Cell */
.v-name{font-weight:600;color:#0f172a;cursor:pointer;transition:color .15s}
.v-table tbody tr:hover .v-name{color:#4f46e5}
.v-code{font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.02em;margin-top:1px}

/* Info Cell */
.v-info-primary{font-size:13px;font-weight:500;color:#334155}
.v-info-secondary{font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;margin-top:1px}

/* Contact Cell */
.v-contact-row{display:flex;align-items:center;gap:6px;font-size:12px;color:#64748b;font-weight:500}
.v-contact-row svg{width:13px;height:13px;color:#cbd5e1;flex-shrink:0}
.v-contact-row+.v-contact-row{margin-top:4px}

/* Compliance Dots */
.v-compliance{display:flex;align-items:center;gap:5px}
.v-dot{width:7px;height:7px;border-radius:50%;position:relative}
.v-dot[data-tip]:hover::after{content:attr(data-tip);position:absolute;bottom:calc(100% + 6px);left:50%;transform:translateX(-50%);padding:3px 7px;background:#0f172a;color:#fff;font-size:9px;font-weight:600;border-radius:5px;white-space:nowrap;z-index:10}
.v-dot[data-tip]:hover::before{content:'';position:absolute;bottom:calc(100% + 2px);left:50%;transform:translateX(-50%);border:3px solid transparent;border-top-color:#0f172a;z-index:10}
.v-dot-ok{background:#10b981}
.v-dot-miss{background:#e2e8f0}

/* Status Pill */
.v-pill{display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:100px;font-size:10px;font-weight:700;letter-spacing:.04em;text-transform:uppercase}
.v-pill-active{background:#ecfdf5;color:#059669}
.v-pill-active::before{content:'';width:5px;height:5px;border-radius:50%;background:#10b981;animation:pulse-dot 2s infinite}
.v-pill-inactive{background:#fef2f2;color:#dc2626}
.v-pill-inactive::before{content:'';width:5px;height:5px;border-radius:50%;background:#ef4444}
@keyframes pulse-dot{0%,100%{opacity:1}50%{opacity:.4}}

/* Action Buttons */
.v-act{display:flex;align-items:center;gap:5px;justify-content:flex-end}
.v-act-btn{width:30px;height:30px;display:inline-flex;align-items:center;justify-content:center;border-radius:9px;border:1px solid transparent;cursor:pointer;transition:all .2s ease;position:relative;background:transparent;padding:0}
.v-act-btn svg{width:14px;height:14px}
.v-act-btn.v-perm{color:#94a3b8}
.v-act-btn.v-perm:hover{background:#f5f3ff;color:#7c3aed;border-color:#ede9fe}
.v-act-btn.v-view{color:#94a3b8}
.v-act-btn.v-view:hover{background:#f8fafc;color:#334155;border-color:#e2e8f0}
.v-act-btn.v-edit{color:#94a3b8}
.v-act-btn.v-edit:hover{background:#eff6ff;color:#3b82f6;border-color:#bfdbfe}
.v-act-btn.v-toggle{color:#94a3b8}
.v-act-btn.v-toggle:hover{background:#fffbeb;color:#f59e0b;border-color:#fde68a}
.v-act-btn.v-delete{color:#94a3b8}
.v-act-btn.v-delete:hover{background:#fef2f2;color:#ef4444;border-color:#fecaca}
/* Tooltip */
.v-act-btn[data-tip]:hover::after{content:attr(data-tip);position:absolute;bottom:calc(100% + 6px);left:50%;transform:translateX(-50%);padding:4px 8px;background:#0f172a;color:#fff;font-size:10px;font-weight:600;border-radius:6px;white-space:nowrap;z-index:10;pointer-events:none;animation:tipFade .15s ease}
.v-act-btn[data-tip]:hover::before{content:'';position:absolute;bottom:calc(100% + 2px);left:50%;transform:translateX(-50%);border:4px solid transparent;border-top-color:#0f172a;z-index:10}
@keyframes tipFade{from{opacity:0;transform:translateX(-50%) translateY(4px)}to{opacity:1;transform:translateX(-50%) translateY(0)}}

/* Premium Pagination */
.v-pag{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-top:1px solid #f1f5f9;flex-wrap:wrap;gap:12px}
.v-pag-info{font-size:12px;font-weight:500;color:#64748b}
.v-pag-info strong{font-weight:700;color:#0f172a}
.v-pag-nav{display:flex;align-items:center;gap:4px}
.v-pag-btn{min-width:32px;height:32px;display:inline-flex;align-items:center;justify-content:center;border-radius:8px;border:1px solid #e2e8f0;background:#fff;font-size:12px;font-weight:600;color:#475569;cursor:pointer;transition:all .2s ease;text-decoration:none;padding:0 6px}
.v-pag-btn:hover{background:#f8fafc;border-color:#c7d2fe;color:#4f46e5}
.v-pag-btn.active{background:linear-gradient(135deg,#4f46e5,#6366f1);color:#fff;border-color:transparent;box-shadow:0 2px 6px rgba(99,102,241,.3)}
.v-pag-btn.disabled{opacity:.4;cursor:not-allowed;pointer-events:none}
.v-pag-dots{font-size:12px;font-weight:600;color:#94a3b8;padding:0 4px}

/* Buttons */
.v-btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:10px;font-size:12px;font-weight:600;border:none;cursor:pointer;transition:all .2s ease;text-decoration:none}
.v-btn-primary{background:linear-gradient(135deg,#4f46e5,#6366f1);color:#fff;box-shadow:0 2px 8px rgba(99,102,241,.25)}
.v-btn-primary:hover{box-shadow:0 4px 12px rgba(99,102,241,.35);transform:translateY(-1px)}
.v-btn-secondary{background:#f8fafc;color:#475569;border:1px solid #e2e8f0}
.v-btn-secondary:hover{background:#f1f5f9;border-color:#cbd5e1}
.v-btn svg{width:14px;height:14px}

/* Empty State */
.v-empty{text-align:center;padding:48px 24px}
.v-empty svg{width:48px;height:48px;color:#cbd5e1;margin:0 auto 12px}
.v-empty p{font-size:13px;font-weight:500;color:#94a3b8}

/* Modal */
.form-section-title{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#4b5563;margin-bottom:1rem;border-bottom:1px solid #f3f4f6;padding-bottom:.5rem}

/* Responsive */
@media(max-width:768px){
    .v-header{flex-direction:column;align-items:flex-start}
    .v-actions{width:100%}
    .v-search-wrap{flex-direction:column}
}
</style>

<!-- Header -->
<div class="v-header">
    <div class="v-title-area">
        <h1>Vendor Management</h1>
        <p>Manage vendor information, banking details, and portal access permissions</p>
    </div>
    <div class="v-actions">
        <button onclick="exportVendorsData()" class="v-btn v-btn-secondary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Export
        </button>
        <button onclick="resetVendorForm(); openModal('vendorModal')" class="v-btn v-btn-primary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Add Vendor
        </button>
    </div>
</div>

<!-- Statistics Cards Row -->
<div class="v-stats" id="statsGrid">
    <div class="stat-card card-slate active" onclick="filterByStatus('')" id="stat-total">
        <div style="display:flex; align-items:flex-start; justify-content:space-between;">
            <div>
                <div class="stat-value" id="count-total">...</div>
                <div class="stat-label">Total Vendors</div>
            </div>
            <div class="stat-icon-ring">
                <svg fill="none" stroke="#60a5fa" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
        </div>
        <div style="margin-top:16px; display:flex; align-items:center; gap:6px;">
            <div style="width:24px; height:3px; border-radius:2px; background:rgba(96,165,250,0.4);"></div>
            <span style="font-size:10px; font-weight:600; color:rgba(255,255,255,0.35);">System Registered</span>
        </div>
    </div>
    
    <div class="stat-card card-cyan" onclick="filterByStatus('active')" id="stat-active">
        <div style="display:flex; align-items:flex-start; justify-content:space-between;">
            <div>
                <div class="stat-value" id="count-active">...</div>
                <div class="stat-label">Active Vendors</div>
            </div>
            <div class="stat-icon-ring">
                <svg fill="none" stroke="#22d3ee" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div style="margin-top:16px; display:flex; align-items:center; gap:6px;">
            <div style="width:24px; height:3px; border-radius:2px; background:rgba(34,211,238,0.4);"></div>
            <span style="font-size:10px; font-weight:600; color:rgba(255,255,255,0.35);">Operational</span>
        </div>
    </div>
    
    <div class="stat-card card-amber" id="stat-delegations">
        <div style="display:flex; align-items:flex-start; justify-content:space-between;">
            <div>
                <div class="stat-value" id="count-delegations">...</div>
                <div class="stat-label">Active Sites</div>
            </div>
            <div class="stat-icon-ring">
                <svg fill="none" stroke="#fbbf24" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
        </div>
        <div style="margin-top:16px; display:flex; align-items:center; gap:6px;">
            <div style="width:24px; height:3px; border-radius:2px; background:rgba(251,191,36,0.4);"></div>
            <span style="font-size:10px; font-weight:600; color:rgba(255,255,255,0.35);">With Delegations</span>
        </div>
    </div>
    
    <div class="stat-card card-purple" id="stat-documents">
        <div style="display:flex; align-items:flex-start; justify-content:space-between;">
            <div>
                <div class="stat-value" id="count-documents">...</div>
                <div class="stat-label">Documented</div>
            </div>
            <div class="stat-icon-ring">
                <svg fill="none" stroke="#a78bfa" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>
        <div style="margin-top:16px; display:flex; align-items:center; gap:6px;">
            <div style="width:24px; height:3px; border-radius:2px; background:rgba(167,139,250,0.4);"></div>
            <span style="font-size:10px; font-weight:600; color:rgba(255,255,255,0.35);">Full Compliance</span>
        </div>
    </div>
</div>
<!-- Search Bar -->
<div class="v-search-wrap">
    <div class="v-search">
        <svg class="v-search-icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input type="text" id="searchInput" placeholder="Search vendors, codes, or contact details..." onkeyup="debounceFilter()">
    </div>
    <div class="v-filter">
        <select id="statusFilter" onchange="applyFilters()">
            <option value="">All Status</option>
            <option value="active">Active Only</option>
            <option value="inactive">Inactive Only</option>
        </select>
    </div>
    <div class="v-count" id="vendorCountBadge">
        <svg width="14" height="14" fill="none" stroke="#64748b" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        <span id="count-inline">—</span> vendors
    </div>
</div>

<!-- Vendors Table -->
<div class="v-table-wrap">
    <div id="tableLoading" class="v-table-loading">
        <div class="spinner"></div>
    </div>
    <div style="overflow-x:auto;">
        <table class="v-table">
            <thead>
                <tr>
                    <th style="width:50px;text-align:center">#</th>
                    <th>Vendor Profile</th>
                    <th>Company Info</th>
                    <th>Contact Details</th>
                    <th style="width:90px">Compliance</th>
                    <th style="width:100px">Status</th>
                    <th style="width:180px;text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody id="vendorsTableBody">
                <!-- Data will be injected here via API -->
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div id="paginationContainer" class="v-pag" style="display:none;">
        <div id="paginationInfo" class="v-pag-info"></div>
        <div class="v-pag-nav" id="paginationNav">
            <!-- Pagination buttons injected here -->
        </div>
    </div>
</div>

<!-- Vendor Modal (Add/Edit) -->
<div id="vendorModal" class="modal">
    <div class="modal-content max-w-5xl rounded-3xl p-0 overflow-hidden">
        <div class="bg-gray-50/80 px-8 py-6 border-b flex justify-between items-center">
            <div>
                <h3 id="vendorModalTitle" class="text-xl font-bold text-gray-900">Add New Vendor</h3>
                <p class="text-xs text-gray-500 mt-1">Complete all sections to ensure full vendor compliance.</p>
            </div>
            <button onclick="closeModal('vendorModal')" class="text-gray-400 hover:text-gray-600 p-2 bg-white rounded-xl shadow-sm border border-gray-100">&times;</button>
        </div>
        <form id="vendorForm" class="p-8 space-y-10 max-h-[80vh] overflow-y-auto" enctype="multipart/form-data">
            <input type="hidden" name="vendor_id" id="edit_vendor_id">
            
            <!-- Basic Information -->
            <section>
                <div class="form-section-title">Basic Information</div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">Vendor Code</label>
                        <input type="text" name="vendor_code" id="vendor_code" placeholder="Auto-generated if empty"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm outline-none focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">Mobility ID</label>
                        <input type="text" name="mobility_id" id="mobility_id" placeholder="App ID"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm outline-none">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">Mobility Password</label>
                        <input type="password" name="mobility_password" id="mobility_password" placeholder="New password"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm outline-none">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">Name *</label>
                        <input type="text" name="vendorName" id="vendorName" placeholder="Full name"
                            class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-2xl text-sm outline-none focus:ring-2 focus:ring-blue-100 font-semibold" required>
                    </div>
                </div>
            </section>

            <!-- Company Information -->
            <section>
                <div class="form-section-title">Company Information</div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">Company Name</label>
                        <input type="text" name="company_name" id="company_name" placeholder="Legal Entity"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm outline-none">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">Address</label>
                        <input type="text" name="address" id="address" placeholder="Full Address"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm outline-none">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">Email</label>
                        <input type="email" name="email" id="email" placeholder="official@example.com"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm outline-none">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">Contact Number</label>
                        <input type="tel" name="contact_number" id="contact_number" placeholder="+91 00000 00000"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm outline-none">
                    </div>
                </div>
            </section>

            <!-- Banking Information -->
            <section>
                <div class="form-section-title">Banking Information</div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">Bank Name</label>
                        <input type="text" name="bank_name" id="bank_name" placeholder="e.g. HDFC Bank"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm outline-none">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">Account Number</label>
                        <input type="text" name="account_number" id="account_number" placeholder="0000 0000 0000"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm outline-none">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">IFSC Code</label>
                        <input type="text" name="ifsc_code" id="ifsc_code" placeholder="HDFC0001234"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm outline-none uppercase">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">GST Number</label>
                        <input type="text" name="gst_number" id="gst_number" placeholder="22AAAAA0000A1Z5"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm outline-none uppercase">
                    </div>
                </div>
            </section>

            <!-- Legal Documentation -->
            <section>
                <div class="form-section-title">Legal Documentation</div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">PAN Card Number</label>
                        <input type="text" name="pan_card_number" id="pan_card_number" placeholder="ABCDE1234F"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm outline-none uppercase">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">Aadhaar Card Number</label>
                        <input type="text" name="aadhaar_number" id="aadhaar_number" placeholder="0000 0000 0000"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm outline-none">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">MSME Number</label>
                        <input type="text" name="msme_number" id="msme_number" placeholder="UDYAM-XX-00-1234567"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm outline-none">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">ESIC Number</label>
                        <input type="text" name="esic_number" id="esic_number" placeholder="00-00-000000-000-0000"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm outline-none">
                    </div>
                </div>
            </section>

            <!-- Additional Information -->
            <section>
                <div class="form-section-title">Additional Information & Files</div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">PF Number</label>
                        <input type="text" name="pf_number" id="pf_number" placeholder="XX/XXX/00000"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm outline-none">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">PVC Status</label>
                        <select name="pvc_status" id="pvc_status" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm outline-none appearance-none cursor-pointer">
                            <option value="">Select</option>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">Experience Letter</label>
                        <input type="file" name="experience_letter" id="experience_letter" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                            class="w-full px-3 py-1.5 bg-white border border-dashed border-gray-200 rounded-2xl text-[11px] outline-none">
                        <p class="text-[10px] text-gray-400 mt-1" id="experience_letter_filename"></p>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-gray-400 uppercase ml-1">Photograph</label>
                        <input type="file" name="photograph" id="photograph" accept=".jpg,.jpeg,.png"
                            class="w-full px-3 py-1.5 bg-white border border-dashed border-gray-200 rounded-2xl text-[11px] outline-none">
                        <p class="text-[10px] text-gray-400 mt-1" id="photograph_filename"></p>
                    </div>
                </div>
            </section>

            <div class="pt-8 border-t flex justify-end gap-3">
                <button type="button" onclick="closeModal('vendorModal')" class="px-6 py-3 text-sm font-bold text-gray-400 hover:text-gray-600 transition-colors uppercase tracking-widest">Cancel</button>
                <button type="submit" id="submitVendorBtn"
                    class="px-12 py-3 bg-blue-600 text-white text-sm font-bold rounded-2xl shadow-xl shadow-blue-100 hover:bg-blue-700 transition-all active:scale-95 uppercase tracking-widest">Update Vendor</button>
            </div>
        </form>
    </div>
</div>

<!-- View Vendor Modal -->
<div id="viewVendorModal" class="modal">
    <div class="modal-content max-w-2xl rounded-3xl p-8 bg-white shadow-2xl">
        <div class="flex items-start justify-between mb-8">
            <div class="flex items-center gap-4">
                <div id="view_avatar_circle"
                    class="w-16 h-16 rounded-2xl bg-blue-50 flex items-center justify-center text-2xl font-black text-blue-600 border border-blue-100/50">
                </div>
                <div>
                    <h3 id="view_vendor_name" class="text-xl font-bold text-gray-900 leading-tight">---</h3>
                    <div class="flex items-center gap-2 mt-1">
                        <span id="view_vendor_code_badge" class="text-[10px] font-bold text-blue-600 uppercase tracking-wider">---</span>
                        <span class="text-gray-300">•</span>
                        <div id="view_status_badge_container" class="flex items-center">
                            <div id="view_status_dot" class="w-1.5 h-1.5 rounded-full mr-1.5"></div>
                            <span id="view_status_badge" class="text-[10px] font-bold uppercase tracking-wider">---</span>
                        </div>
                    </div>
                </div>
            </div>
            <button onclick="closeModal('viewVendorModal')" class="text-gray-400 hover:text-gray-600 transition-colors p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
            <div class="space-y-6">
                <div>
                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-2">Company Information</p>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg></div>
                            <div>
                                <p id="view_company_name" class="text-sm font-bold text-gray-800">---</p>
                                <p class="text-[11px] text-gray-400">Legal Entity Name</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg></div>
                            <div>
                                <p id="view_gst_number" class="text-sm font-bold text-gray-800">---</p>
                                <p class="text-[11px] text-gray-400">GST Registration</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="space-y-6">
                 <div>
                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-2">Contact Details</p>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg></div>
                            <div>
                                <p id="view_email" class="text-sm font-bold text-gray-800">---</p>
                                <p class="text-[11px] text-gray-400">Official Email</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg></div>
                            <div>
                                <p id="view_phone" class="text-sm font-bold text-gray-800">---</p>
                                <p class="text-[11px] text-gray-400">Mobile Number</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-6 border-t border-gray-100 flex justify-end gap-2">
            <button onclick="closeModal('viewVendorModal')" class="px-6 py-2 text-sm font-bold text-gray-400">Close</button>
            <button onclick="editVendorFromView()" class="px-8 py-2 bg-gray-900 text-white text-sm font-bold rounded-2xl">Edit Profile</button>
        </div>
    </div>
</div>

<!-- Permissions Modal -->
<div id="permissionsModal" class="modal">
    <div class="modal-content max-w-xl rounded-3xl p-8 bg-white overflow-hidden">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-xl font-bold text-gray-900">Module Permissions</h3>
                <p class="text-xs text-gray-500 mt-1" id="perm_vendor_name">Configure portal access for this vendor.</p>
            </div>
            <button onclick="closeModal('permissionsModal')" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        
        <form id="permissionsForm" class="space-y-4">
            <input type="hidden" id="perm_vendor_id" name="vendor_id">
            <div class="space-y-3" id="permissionsList">
                <?php foreach ($allPermissions as $key => $label): ?>
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-transparent hover:border-blue-100 transition-all">
                        <div class="flex-1 pr-4">
                            <label class="text-sm font-bold text-gray-900"><?php echo $label; ?></label>
                            <p class="text-[10px] text-gray-500 mt-0.5"><?php echo getPermissionDescription($key); ?></p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="perm_<?php echo $key; ?>" name="permissions[<?php echo $key; ?>]" value="1" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="pt-6">
                <button type="submit" class="w-full py-3 bg-blue-600 text-white font-bold rounded-2xl shadow-lg shadow-blue-100">Update Permissions</button>
            </div>
        </form>
    </div>
</div>

<script>
    let currentData = null;
    let searchTimer;

    function debounceFilter() {
        if (searchTimer) clearTimeout(searchTimer);
        searchTimer = setTimeout(() => applyFilters(), 500);
    }

    function applyFilters(page = 1) {
        const search = document.getElementById('searchInput').value;
        const status = document.getElementById('statusFilter').value;

        const params = new URLSearchParams(window.location.search);
        params.set('page', page);
        if (search) params.set('search', search); else params.delete('search');
        if (status) params.set('status', status); else params.delete('status');

        window.history.pushState({}, '', '?' + params.toString());
        fetchVendors();
    }

    function filterByStatus(status) {
        document.getElementById('statusFilter').value = status;
        applyFilters(1);
    }

    async function fetchVendors() {
        const loading = document.getElementById('tableLoading');
        loading.classList.add('show');

        try {
            const params = new URLSearchParams(window.location.search);
            const response = await fetch(`../../api/vendors.php?${params.toString()}`);
            const result = await response.json();

            if (result.success) {
                currentData = result.data;
                renderStats(result.data.stats);
                renderTable(result.data.vendors);
                renderPagination(result.data.pagination);
            }
        } catch (error) {
            console.error('Fetch error:', error);
            showToast('Failed to load vendors data.', 'error');
        } finally {
            loading.classList.remove('show');
        }
    }

    function renderStats(stats) {
        document.getElementById('count-total').textContent = (stats.total || 0).toLocaleString();
        document.getElementById('count-active').textContent = (stats.total_active || 0).toLocaleString();
        document.getElementById('count-delegations').textContent = (stats.with_delegations || 0).toLocaleString();
        document.getElementById('count-documents').textContent = (stats.with_documents || 0).toLocaleString();
        document.getElementById('count-inline').textContent = (stats.total || 0).toLocaleString();

        const params = new URLSearchParams(window.location.search);
        const status = params.get('status');

        document.querySelectorAll('.v-stat').forEach(c => c.classList.remove('active'));
        if (status === 'active') document.getElementById('stat-active').classList.add('active');
        else if (status === 'inactive') document.getElementById('stat-total').classList.add('active');
        else if (!status || status === '') document.getElementById('stat-total').classList.add('active');
    }

    function renderTable(vendors) {
        const tbody = document.getElementById('vendorsTableBody');
        tbody.innerHTML = '';

        if (vendors.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align:center;padding:48px 24px;">
                        <div class="v-empty">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <p>No vendors found. Try adjusting your filters.</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        const params = new URLSearchParams(window.location.search);
        const page = parseInt(params.get('page')) || 1;
        const limit = 10;

        vendors.forEach((vendor, index) => {
            const initial = vendor.name.charAt(0).toUpperCase();
            const avatarColors = ['v-avatar-blue', 'v-avatar-green', 'v-avatar-purple', 'v-avatar-orange'];
            const avatarClass = avatarColors[vendor.name.length % avatarColors.length];

            const row = `
                <tr>
                    <td style="text-align:center"><span class="v-row-num">${(page - 1) * limit + index + 1}</span></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            <div class="v-avatar ${avatarClass}">${initial}</div>
                            <div>
                                <div class="v-name" onclick="viewVendor(${vendor.id})">${vendor.name}</div>
                                <div class="v-code">${vendor.vendor_code || '---'}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="v-info-primary">${vendor.company_name || 'Individual'}</div>
                        <div class="v-info-secondary">${vendor.gst_number || 'No GST'}</div>
                    </td>
                    <td>
                        <div class="v-contact-row">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            ${vendor.email || '---'}
                        </div>
                        <div class="v-contact-row">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            ${vendor.phone || '---'}
                        </div>
                    </td>
                    <td>
                        <div class="v-compliance">
                            <span class="v-dot ${vendor.experience_letter_path ? 'v-dot-ok' : 'v-dot-miss'}" data-tip="${vendor.experience_letter_path ? 'Experience Letter ✓' : 'Missing Exp. Letter'}"></span>
                            <span class="v-dot ${vendor.photograph_path ? 'v-dot-ok' : 'v-dot-miss'}" data-tip="${vendor.photograph_path ? 'Photograph ✓' : 'Missing Photo'}"></span>
                            <span class="v-dot ${vendor.gst_number && vendor.pan_card_number ? 'v-dot-ok' : 'v-dot-miss'}" data-tip="${vendor.gst_number && vendor.pan_card_number ? 'Tax Docs ✓' : 'Missing Tax Docs'}"></span>
                        </div>
                    </td>
                    <td>
                        <span class="v-pill ${vendor.status === 'active' ? 'v-pill-active' : 'v-pill-inactive'}">
                            ${vendor.status.toUpperCase()}
                        </span>
                    </td>
                    <td>
                        <div class="v-act">
                            <button onclick="managePermissions(${vendor.id})" class="v-act-btn v-perm" data-tip="Permissions">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                            </button>
                            <button onclick="viewVendor(${vendor.id})" class="v-act-btn v-view" data-tip="View">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                            <button onclick="editVendor(${vendor.id})" class="v-act-btn v-edit" data-tip="Edit">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <button onclick="toggleVendorStatus(${vendor.id})" class="v-act-btn v-toggle" data-tip="Toggle Status">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                            </button>
                            <button onclick="deleteVendor(${vendor.id})" class="v-act-btn v-delete" data-tip="Delete">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            tbody.innerHTML += row;
        });
    }

    function renderPagination(pagination) {
        const container = document.getElementById('paginationContainer');
        const info = document.getElementById('paginationInfo');
        const nav = document.getElementById('paginationNav');

        if (pagination.total_pages <= 1) {
            container.style.display = 'none';
            return;
        }

        container.style.display = '';
        const start = ((pagination.current_page - 1) * pagination.limit) + 1;
        const end = Math.min(pagination.current_page * pagination.limit, pagination.total_records);
        info.innerHTML = `Showing <strong>${start}</strong> to <strong>${end}</strong> of <strong>${pagination.total_records.toLocaleString()}</strong> results`;

        nav.innerHTML = '';
        const current = pagination.current_page;
        const total = pagination.total_pages;

        const prevIcon = '<svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>';
        const nextIcon = '<svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>';

        const addBtn = (content, page, isActive = false, isDisabled = false) => {
            const btn = document.createElement('a');
            btn.href = '#';
            btn.className = `v-pag-btn${isActive ? ' active' : ''}${isDisabled ? ' disabled' : ''}`;
            btn.innerHTML = content;
            if (!isDisabled) btn.onclick = (e) => { e.preventDefault(); applyFilters(page); };
            nav.appendChild(btn);
        };

        // Previous
        addBtn(prevIcon, current - 1, false, current === 1);

        // Page numbers
        const rangeStart = Math.max(1, current - 2);
        const rangeEnd = Math.min(total, current + 2);

        if (rangeStart > 1) {
            addBtn('1', 1);
            if (rangeStart > 2) {
                const dots = document.createElement('span');
                dots.className = 'v-pag-dots';
                dots.textContent = '…';
                nav.appendChild(dots);
            }
        }

        for (let i = rangeStart; i <= rangeEnd; i++) {
            addBtn(i.toString(), i, i === current);
        }

        if (rangeEnd < total) {
            if (rangeEnd < total - 1) {
                const dots = document.createElement('span');
                dots.className = 'v-pag-dots';
                dots.textContent = '…';
                nav.appendChild(dots);
            }
            addBtn(total.toString(), total);
        }

        // Next
        addBtn(nextIcon, current + 1, false, current === total);
    }

    function resetVendorForm() {
        document.getElementById('vendorForm').reset();
        document.getElementById('edit_vendor_id').value = '';
        document.getElementById('vendorModalTitle').textContent = 'Add New Vendor';
        document.getElementById('submitVendorBtn').textContent = 'Save Vendor';
        document.getElementById('experience_letter_filename').textContent = '';
        document.getElementById('photograph_filename').textContent = '';
    }

    document.getElementById('vendorForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        const id = document.getElementById('edit_vendor_id').value;
        const action = id ? `update&id=${id}` : 'create';
        
        const response = await fetch(`../../api/vendors.php?action=${action}`, { method: 'POST', body: formData });
        const result = await response.json();
        
        if (result.success) {
            closeModal('vendorModal');
            showToast(result.message, 'success');
            fetchVendors();
        } else {
            showToast(result.message, 'error');
        }
    });

    async function viewVendor(id) {
        const res = await fetch(`../../api/vendors.php?action=view&id=${id}`);
        const data = await res.json();
        if (data.success) {
            const v = data.vendor;
            document.getElementById('view_vendor_name').textContent = v.name;
            document.getElementById('view_company_name').textContent = v.company_name || 'Individual';
            document.getElementById('view_vendor_code_badge').textContent = v.vendor_code || 'No Code';
            document.getElementById('view_gst_number').textContent = v.gst_number || 'Not Registered';
            document.getElementById('view_email').textContent = v.email || 'No email';
            document.getElementById('view_phone').textContent = v.phone || 'No phone';
            document.getElementById('view_avatar_circle').textContent = v.name.charAt(0).toUpperCase();
            
            const statusBadge = document.getElementById('view_status_badge');
            const statusDot = document.getElementById('view_status_dot');
            statusBadge.textContent = v.status.toUpperCase();
            statusBadge.className = `text-[10px] font-bold uppercase tracking-wider ${v.status === 'active' ? 'text-emerald-600' : 'text-gray-400'}`;
            statusDot.className = `w-1.5 h-1.5 rounded-full mr-1.5 ${v.status === 'active' ? 'bg-emerald-500' : 'bg-gray-300'}`;
            
            document.querySelector('#viewVendorModal button[onclick="editVendorFromView()"]').onclick = () => {
                closeModal('viewVendorModal');
                editVendor(id);
            };
            
            openModal('viewVendorModal');
        }
    }

    async function editVendor(id) {
        const res = await fetch(`../../api/vendors.php?action=edit&id=${id}`);
        const data = await res.json();
        if (data.success) {
            const v = data.vendor;
            document.getElementById('edit_vendor_id').value = v.id;
            document.getElementById('vendorModalTitle').textContent = 'Edit Vendor Profile';
            document.getElementById('submitVendorBtn').textContent = 'Update Vendor';
            
            const form = document.getElementById('vendorForm');
            form.querySelector('[name="vendorName"]').value = v.name || '';
            form.querySelector('[name="vendor_code"]').value = v.vendor_code || '';
            form.querySelector('[name="mobility_id"]').value = v.mobility_id || '';
            form.querySelector('[name="mobility_password"]').value = ''; // Always clear password on edit
            form.querySelector('[name="company_name"]').value = v.company_name || '';
            form.querySelector('[name="address"]').value = v.address || '';
            form.querySelector('[name="email"]').value = v.email || '';
            form.querySelector('[name="contact_number"]').value = v.phone || '';
            form.querySelector('[name="bank_name"]').value = v.bank_name || '';
            form.querySelector('[name="account_number"]').value = v.account_number || '';
            form.querySelector('[name="ifsc_code"]').value = v.ifsc_code || '';
            form.querySelector('[name="gst_number"]').value = v.gst_number || '';
            form.querySelector('[name="pan_card_number"]').value = v.pan_card_number || '';
            form.querySelector('[name="aadhaar_number"]').value = v.aadhaar_number || '';
            form.querySelector('[name="msme_number"]').value = v.msme_number || '';
            form.querySelector('[name="esic_number"]').value = v.esic_number || '';
            form.querySelector('[name="pf_number"]').value = v.pf_number || '';
            form.querySelector('[name="pvc_status"]').value = v.pvc_status || '';

            // Show current filenames if any
            document.getElementById('experience_letter_filename').textContent = v.experience_letter_path ? 'Current: ' + v.experience_letter_path.split('/').pop() : '';
            document.getElementById('photograph_filename').textContent = v.photograph_path ? 'Current: ' + v.photograph_path.split('/').pop() : '';
            
            openModal('vendorModal');
        }
    }

    async function toggleVendorStatus(id) {
        const confirmed = await showConfirm(
            'Change Status',
            'Are you sure you want to change this vendor\'s status?',
            { confirmType: 'primary', confirmText: 'Yes, Change' }
        );
        if (!confirmed) return;

        const res = await fetch(`../../api/vendors.php?action=toggle-status&id=${id}`, { method: 'POST' });
        const data = await res.json();
        if (data.success) {
            showToast(data.message, 'success');
            fetchVendors();
        } else {
            showToast(data.message, 'error');
        }
    }

    async function deleteVendor(id) {
        const confirmed = await showConfirm(
            'Delete Vendor',
            'Are you sure you want to permanently delete this vendor? This action cannot be undone.',
            { confirmType: 'danger', confirmText: 'Yes, Delete' }
        );
        if (!confirmed) return;

        const res = await fetch(`../../api/vendors.php?action=delete&id=${id}`, { method: 'POST' });
        const data = await res.json();
        if (data.success) {
            showToast(data.message, 'success');
            fetchVendors();
        } else {
            showToast(data.message, 'error');
        }
    }

    async function managePermissions(id) {
        try {
            const res = await fetch(`get-vendor-permissions.php?vendor_id=${id}`);
            const result = await res.json();
            if (result.success) {
                document.getElementById('perm_vendor_id').value = id;
                document.getElementById('perm_vendor_name').textContent = `Manage permissions for ${result.vendor.name}`;
                
                document.querySelectorAll('#permissionsList input[type="checkbox"]').forEach(cb => cb.checked = false);
                
                Object.keys(result.permissions).forEach(key => {
                    const cb = document.getElementById(`perm_${key}`);
                    if (cb) cb.checked = result.permissions[key];
                });
                
                openModal('permissionsModal');
            }
        } catch (e) {
            console.error(e);
        }
    }

    document.getElementById('permissionsForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const res = await fetch('update-permissions.php', { method: 'POST', body: formData });
        const result = await res.json();
        if (result.success) {
            closeModal('permissionsModal');
            showToast('Permissions updated successfully', 'success');
        } else {
            showToast(result.message || 'Failed to update permissions', 'error');
        }
    });

    function exportVendorsData() {
        const params = new URLSearchParams(window.location.search);
        window.open(`export-vendors.php?${params.toString()}`, '_blank');
    }

    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func(...args), wait);
        };
    }

    document.addEventListener('DOMContentLoaded', fetchVendors);
</script>

<?php
function getPermissionDescription($key) {
    $descriptions = [
        'view_sites' => 'Allow vendor to view and manage their assigned sites',
        'update_progress' => 'Allow vendor to update installation progress',
        'view_masters' => 'Allow vendor to view master data (customers, banks, etc.)',
        'view_reports' => 'Allow vendor to view reports and analytics',
        'view_inventory' => 'Allow vendor to view inventory information',
        'view_material_requests' => 'Allow vendor to view material requests'
    ];
    return $descriptions[$key] ?? 'Permission description not available';
}

$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>