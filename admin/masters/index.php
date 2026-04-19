<?php
require_once __DIR__ . '/../../controllers/BanksController.php';
require_once __DIR__ . '/../../controllers/CustomersController.php';
require_once __DIR__ . '/../../controllers/ZonesController.php';
require_once __DIR__ . '/../../controllers/CountriesController.php';
require_once __DIR__ . '/../../controllers/StatesController.php';
require_once __DIR__ . '/../../controllers/CitiesController.php';
require_once __DIR__ . '/../../controllers/BoqMasterController.php';
require_once __DIR__ . '/../../controllers/CouriersController.php';
require_once __DIR__ . '/../../controllers/SurveyMasterController.php';
require_once __DIR__ . '/../../controllers/InstallationMasterController.php';


// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);



// Determine which master to show
$masterType = $_GET['type'] ?? 'banks';
$validTypes = ['banks', 'customers', 'zones', 'countries', 'states', 'cities', 'boq', 'couriers', 'survey', 'installation'];

if (!in_array($masterType, $validTypes)) {
    $masterType = 'banks';
}


// echo 'masterType' . $masterType ; 
// Initialize appropriate controller
switch ($masterType) {
    case 'banks':
        $controller = new BanksController();
        $title = 'Banks Management';
        $singular = 'Bank';
        break;
    case 'customers':
        $controller = new CustomersController();
        $title = 'Customers Management';
        $singular = 'Customer';
        break;
    case 'zones':
        $controller = new ZonesController();
        $title = 'Zones Management';
        $singular = 'Zone';
        break;
    case 'countries':
        $controller = new CountriesController();
        $title = 'Countries Management';
        $singular = 'Country';
        break;
    case 'states':
        $controller = new StatesController();
        $title = 'States Management';
        $singular = 'State';
        break;
    case 'cities':
        $controller = new CitiesController();
        $title = 'Cities Management';
        $singular = 'City';
        break;
    case 'boq':
        $controller = new BoqMasterController();
        $title = 'BOQ Management';
        $singular = 'BOQ';
        break;
    case 'couriers':
        $controller = new CouriersController();
        $title = 'Courier Management';
        $singular = 'Courier';
        break;
    case 'survey':
        $controller = new SurveyMasterController();
        $title = 'Survey Type Management';
        $singular = 'Survey Type';
        break;
    case 'installation':
        $controller = new InstallationMasterController();
        $title = 'Installation Type Management';
        $singular = 'Installation Type';
        break;
    default:
        $controller = new BanksController();
        $title = 'Banks Management';
        $singular = 'Bank';
}

$data = $controller->index();

ob_start();
?>

<style>
/* ── Masters Page Premium Styles ── */
.m-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:16px}
.m-title-area h1{font-size:1.5rem;font-weight:800;color:#0f172a;letter-spacing:-.02em}
.m-title-area p{font-size:13px;font-weight:500;color:#64748b;margin-top:4px}
.m-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap}

/* Type Chips */
.m-chips{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:20px}
.m-chip{display:inline-flex;align-items:center;gap:5px;padding:6px 14px;border-radius:100px;font-size:11px;font-weight:700;letter-spacing:.02em;border:1px solid #e2e8f0;background:#fff;color:#64748b;cursor:pointer;transition:all .25s ease;text-decoration:none}
.m-chip:hover{border-color:#c7d2fe;color:#4f46e5;background:#eef2ff}
.m-chip.active{background:linear-gradient(135deg,#4f46e5,#6366f1);color:#fff;border-color:transparent;box-shadow:0 2px 8px rgba(99,102,241,.3)}
.m-chip svg{width:13px;height:13px}

/* Search Bar */
.m-search-wrap{background:#fff;border:1px solid #f1f5f9;border-radius:16px;padding:16px 20px;margin-bottom:20px;display:flex;align-items:center;gap:12px;flex-wrap:wrap}
.m-search{position:relative;flex:1;min-width:220px}
.m-search input{width:100%;padding:10px 14px 10px 40px;border:1px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:500;color:#1e293b;background:#f8fafc;transition:all .2s ease;outline:none}
.m-search input:focus{border-color:#6366f1;background:#fff;box-shadow:0 0 0 3px rgba(99,102,241,.08)}
.m-search .m-search-icon{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#94a3b8}
.m-filter select{padding:10px 32px 10px 14px;border:1px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:500;color:#1e293b;background:#f8fafc;cursor:pointer;outline:none;transition:all .2s ease;-webkit-appearance:none;appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%2394a3b8' viewBox='0 0 20 20'%3E%3Cpath d='M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 10px center}
.m-filter select:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.08)}
.m-count{display:flex;align-items:center;gap:6px;padding:8px 14px;background:#f1f5f9;border-radius:10px;font-size:12px;font-weight:600;color:#475569}
.m-count span{font-weight:800;color:#0f172a}

/* Premium Table */
.m-table-wrap{background:#fff;border:1px solid #f1f5f9;border-radius:16px;overflow:hidden}
.m-table{width:100%;border-collapse:separate;border-spacing:0}
.m-table thead{background:linear-gradient(135deg,#f8fafc,#f1f5f9)}
.m-table th{padding:12px 16px;text-align:left;font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#64748b;border-bottom:1px solid #e2e8f0;white-space:nowrap}
.m-table td{padding:14px 16px;font-size:13px;color:#334155;border-bottom:1px solid #f8fafc;vertical-align:middle}
.m-table tbody tr{transition:all .15s ease}
.m-table tbody tr:hover{background:#fafbff}
.m-table tbody tr:last-child td{border-bottom:none}

/* Row Number */
.m-row-num{width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;background:#f1f5f9;border-radius:8px;font-size:11px;font-weight:700;color:#94a3b8}

/* Name Cell */
.m-name{font-weight:600;color:#0f172a}
.m-id{font-size:11px;font-weight:500;color:#94a3b8;margin-top:2px}

/* Status Pill */
.m-pill{display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:100px;font-size:10px;font-weight:700;letter-spacing:.04em;text-transform:uppercase}
.m-pill-active{background:#ecfdf5;color:#059669}
.m-pill-active::before{content:'';width:5px;height:5px;border-radius:50%;background:#10b981}
.m-pill-inactive{background:#fef2f2;color:#dc2626}
.m-pill-inactive::before{content:'';width:5px;height:5px;border-radius:50%;background:#ef4444}
.m-pill-info{background:#eff6ff;color:#2563eb}

/* Action Buttons */
.m-act{display:flex;align-items:center;gap:6px}
.m-act-btn{width:32px;height:32px;display:inline-flex;align-items:center;justify-content:center;border-radius:10px;border:1px solid transparent;cursor:pointer;transition:all .2s ease;position:relative}
.m-act-btn svg{width:15px;height:15px}
.m-act-btn.m-view{background:#f8fafc;color:#64748b;border-color:#e2e8f0}
.m-act-btn.m-view:hover{background:#f1f5f9;color:#334155;border-color:#cbd5e1}
.m-act-btn.m-edit{background:#eff6ff;color:#3b82f6;border-color:#bfdbfe}
.m-act-btn.m-edit:hover{background:#dbeafe;color:#2563eb}
.m-act-btn.m-toggle-off{background:#fffbeb;color:#f59e0b;border-color:#fde68a}
.m-act-btn.m-toggle-off:hover{background:#fef3c7;color:#d97706}
.m-act-btn.m-toggle-on{background:#ecfdf5;color:#10b981;border-color:#a7f3d0}
.m-act-btn.m-toggle-on:hover{background:#d1fae5;color:#059669}
.m-act-btn.m-delete{background:#fef2f2;color:#ef4444;border-color:#fecaca}
.m-act-btn.m-delete:hover{background:#fee2e2;color:#dc2626}
/* Tooltip */
.m-act-btn[data-tip]:hover::after{content:attr(data-tip);position:absolute;bottom:calc(100% + 6px);left:50%;transform:translateX(-50%);padding:4px 8px;background:#0f172a;color:#fff;font-size:10px;font-weight:600;border-radius:6px;white-space:nowrap;z-index:10;pointer-events:none;animation:tipFade .15s ease}
.m-act-btn[data-tip]:hover::before{content:'';position:absolute;bottom:calc(100% + 2px);left:50%;transform:translateX(-50%);border:4px solid transparent;border-top-color:#0f172a;z-index:10}
@keyframes tipFade{from{opacity:0;transform:translateX(-50%) translateY(4px)}to{opacity:1;transform:translateX(-50%) translateY(0)}}

/* Date Cell */
.m-date{font-size:12px;font-weight:500;color:#64748b}
.m-date-by{font-size:10px;color:#94a3b8;margin-top:2px}

/* Premium Pagination */
.m-pag{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-top:1px solid #f1f5f9;flex-wrap:wrap;gap:12px}
.m-pag-info{font-size:12px;font-weight:500;color:#64748b}
.m-pag-info strong{font-weight:700;color:#0f172a}
.m-pag-nav{display:flex;align-items:center;gap:4px}
.m-pag-btn{min-width:32px;height:32px;display:inline-flex;align-items:center;justify-content:center;border-radius:8px;border:1px solid #e2e8f0;background:#fff;font-size:12px;font-weight:600;color:#475569;cursor:pointer;transition:all .2s ease;text-decoration:none;padding:0 6px}
.m-pag-btn:hover{background:#f8fafc;border-color:#c7d2fe;color:#4f46e5}
.m-pag-btn.active{background:linear-gradient(135deg,#4f46e5,#6366f1);color:#fff;border-color:transparent;box-shadow:0 2px 6px rgba(99,102,241,.3)}
.m-pag-btn.disabled{opacity:.4;cursor:not-allowed;pointer-events:none}
.m-pag-dots{font-size:12px;font-weight:600;color:#94a3b8;padding:0 4px}

/* Buttons */
.m-btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:10px;font-size:12px;font-weight:600;border:none;cursor:pointer;transition:all .2s ease;text-decoration:none}
.m-btn-primary{background:linear-gradient(135deg,#4f46e5,#6366f1);color:#fff;box-shadow:0 2px 8px rgba(99,102,241,.25)}
.m-btn-primary:hover{box-shadow:0 4px 12px rgba(99,102,241,.35);transform:translateY(-1px)}
.m-btn-secondary{background:#f8fafc;color:#475569;border:1px solid #e2e8f0}
.m-btn-secondary:hover{background:#f1f5f9;border-color:#cbd5e1}
.m-btn svg{width:14px;height:14px}

/* Empty State */
.m-empty{text-align:center;padding:48px 24px}
.m-empty svg{width:48px;height:48px;color:#cbd5e1;margin:0 auto 12px}
.m-empty p{font-size:13px;font-weight:500;color:#94a3b8}

/* Responsive */
@media(max-width:768px){
    .m-header{flex-direction:column;align-items:flex-start}
    .m-actions{width:100%}
    .m-chips{overflow-x:auto;flex-wrap:nowrap;padding-bottom:4px}
    .m-search-wrap{flex-direction:column}
}
</style>

<?php
// Define chip icons for each master type
$typeIcons = [
    'banks' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>',
    'customers' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
    'zones' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>',
    'countries' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
    'states' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
    'cities' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>',
    'boq' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>',
    'couriers' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12l-4 7H8l-4-7h4zm0 0L6 3H3m5 4v10a2 2 0 104 0V7"/></svg>',
    'survey' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
    'installation' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>',
];
$typeLabels = [
    'banks' => 'Banks', 'customers' => 'Customers', 'zones' => 'Zones',
    'countries' => 'Countries', 'states' => 'States', 'cities' => 'Cities',
    'boq' => 'BOQ', 'couriers' => 'Couriers', 'survey' => 'Survey Types',
    'installation' => 'Install Types'
];
?>

<!-- Header -->
<div class="m-header">
    <div class="m-title-area">
        <h1><?php echo $title; ?></h1>
        <p>Manage master data for <?php echo strtolower($singular); ?>s · <?php echo number_format($data['pagination']['total_records']); ?> records</p>
    </div>
    <div class="m-actions">
        <button onclick="exportToCSV()" class="m-btn m-btn-secondary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Export
        </button>
        <?php if ($masterType === 'cities'): ?>
        <a href="bulk_upload.php?type=cities" class="m-btn m-btn-secondary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
            Bulk Upload
        </a>
        <?php endif; ?>
        <button onclick="openCreateModal()" class="m-btn m-btn-primary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Add <?php echo $singular; ?>
        </button>
    </div>
</div>

<!-- Type Chips -->
<div class="m-chips">
    <?php
    // Survey & Installation use a different file
    $typeUrls = [
        'survey' => 'form-master.php?type=survey',
        'installation' => 'form-master.php?type=installation',
    ];
    foreach ($typeLabels as $type => $label):
        $chipUrl = $typeUrls[$type] ?? "?type={$type}";
    ?>
    <a href="<?php echo $chipUrl; ?>" class="m-chip <?php echo $masterType === $type ? 'active' : ''; ?>">
        <?php echo $typeIcons[$type] ?? ''; ?>
        <?php echo $label; ?>
    </a>
    <?php endforeach; ?>
</div>

<!-- Search Bar -->
<div class="m-search-wrap">
    <div class="m-search">
        <svg class="m-search-icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input type="text" id="searchInput" placeholder="Search <?php echo strtolower($singular); ?>s by name..." value="<?php echo htmlspecialchars($data['search']); ?>">
    </div>
    <div class="m-filter">
        <select id="statusFilter">
            <option value="">All Status</option>
            <option value="active" <?php echo $data['status_filter'] === 'active' ? 'selected' : ''; ?>>Active</option>
            <option value="inactive" <?php echo $data['status_filter'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
        </select>
    </div>
    <div class="m-count">
        <svg width="14" height="14" fill="none" stroke="#64748b" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
        <span><?php echo number_format($data['pagination']['total_records']); ?></span> records
    </div>
</div>

<!-- Data Table -->
<div class="m-table-wrap">
    <?php if (empty($data['records'])): ?>
    <div class="m-empty">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
        <p>No <?php echo strtolower($singular); ?>s found. Try adjusting your filters.</p>
    </div>
    <?php else: ?>
    <div style="overflow-x:auto;">
        <table class="m-table" id="mastersTable">
            <thead>
                <tr>
                    <th style="width:50px">#</th>
                    <th><?php echo $masterType === 'boq' ? 'BOQ Name' : 'Name'; ?></th>
                    <?php if ($masterType === 'boq'): ?>
                        <th>Serial Req.</th>
                    <?php endif; ?>
                    <?php if ($masterType === 'states'): ?>
                        <th>Country</th>
                    <?php endif; ?>
                    <?php if ($masterType === 'cities'): ?>
                        <th>State</th>
                        <th>Zone</th>
                        <th>Country</th>
                    <?php endif; ?>
                    <th style="width:100px">Status</th>
                    <th style="width:120px">Created</th>
                    <th style="width:170px">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $serial_number = (($data['pagination']['current_page'] - 1) * $data['pagination']['limit']) + 1;
                foreach ($data['records'] as $record): ?>
                    <tr>
                        <td><span class="m-row-num"><?php echo $serial_number++; ?></span></td>
                        <td>
                            <div class="m-name"><?php echo htmlspecialchars($masterType === 'boq' ? $record['boq_name'] : $record['name']); ?></div>
                            <div class="m-id">ID: <?php echo $masterType === 'boq' ? $record['boq_id'] : $record['id']; ?></div>
                        </td>
                        <?php if ($masterType === 'boq'): ?>
                            <td><span class="m-pill <?php echo $record['is_serial_number_required'] ? 'm-pill-info' : ''; ?>" style="<?php echo !$record['is_serial_number_required'] ? 'background:#f1f5f9;color:#94a3b8;' : ''; ?>"><?php echo $record['is_serial_number_required'] ? 'Required' : 'No'; ?></span></td>
                        <?php endif; ?>
                        <?php if ($masterType === 'states'): ?>
                            <td style="font-size:13px;color:#475569;"><?php echo htmlspecialchars($record['country_name'] ?? 'N/A'); ?></td>
                        <?php endif; ?>
                        <?php if ($masterType === 'cities'): ?>
                            <td style="font-size:13px;color:#475569;"><?php echo htmlspecialchars($record['state_name'] ?? 'N/A'); ?></td>
                            <td style="font-size:13px;color:#475569;"><?php echo htmlspecialchars($record['zone_name'] ?? 'N/A'); ?></td>
                            <td style="font-size:13px;color:#475569;"><?php echo htmlspecialchars($record['country_name'] ?? 'N/A'); ?></td>
                        <?php endif; ?>
                        <td>
                            <span class="m-pill <?php echo $record['status'] === 'active' ? 'm-pill-active' : 'm-pill-inactive'; ?>">
                                <?php echo ucfirst($record['status']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="m-date"><?php echo date('M j, Y', strtotime($record['created_at'])); ?></div>
                            <?php if (!empty($record['created_by_name'])): ?>
                                <div class="m-date-by">by <?php echo htmlspecialchars($record['created_by_name']); ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="m-act">
                                <button onclick="viewMaster(<?php echo $masterType === 'boq' ? $record['boq_id'] : $record['id']; ?>)" class="m-act-btn m-view" data-tip="View Details">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                                <button onclick="editMaster(<?php echo $masterType === 'boq' ? $record['boq_id'] : $record['id']; ?>)" class="m-act-btn m-edit" data-tip="Edit">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button onclick="toggleMasterStatus(<?php echo $masterType === 'boq' ? $record['boq_id'] : $record['id']; ?>)" class="m-act-btn <?php echo $record['status'] === 'active' ? 'm-toggle-off' : 'm-toggle-on'; ?>" data-tip="<?php echo $record['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>">
                                    <?php if ($record['status'] === 'active'): ?>
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                    <?php else: ?>
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <?php endif; ?>
                                </button>
                                <button onclick="deleteMaster(<?php echo $masterType === 'boq' ? $record['boq_id'] : $record['id']; ?>)" class="m-act-btn m-delete" data-tip="Delete">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ($data['pagination']['total_pages'] > 1): ?>
    <div class="m-pag">
        <div class="m-pag-info">
            Showing <strong><?php echo (($data['pagination']['current_page'] - 1) * $data['pagination']['limit']) + 1; ?></strong>
            to <strong><?php echo min($data['pagination']['current_page'] * $data['pagination']['limit'], $data['pagination']['total_records']); ?></strong>
            of <strong><?php echo number_format($data['pagination']['total_records']); ?></strong> results
        </div>
        <div class="m-pag-nav">
            <?php
            $current = $data['pagination']['current_page'];
            $total = $data['pagination']['total_pages'];
            $search_param = !empty($data['search']) ? '&search=' . urlencode($data['search']) : '';
            $status_param = !empty($data['status_filter']) ? '&status=' . urlencode($data['status_filter']) : '';
            $base_url = "?type={$masterType}";
            ?>
            <!-- Previous -->
            <a href="<?php echo $current > 1 ? $base_url . '&page=' . ($current - 1) . $search_param . $status_param : '#'; ?>" class="m-pag-btn <?php echo $current <= 1 ? 'disabled' : ''; ?>">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>

            <?php
            $start = max(1, $current - 2);
            $end = min($total, $current + 2);

            if ($start > 1): ?>
                <a href="<?php echo $base_url; ?>&page=1<?php echo $search_param . $status_param; ?>" class="m-pag-btn">1</a>
                <?php if ($start > 2): ?><span class="m-pag-dots">…</span><?php endif; ?>
            <?php endif;

            for ($i = $start; $i <= $end; $i++): ?>
                <a href="<?php echo $base_url; ?>&page=<?php echo $i; ?><?php echo $search_param . $status_param; ?>" class="m-pag-btn <?php echo $i == $current ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor;

            if ($end < $total): ?>
                <?php if ($end < $total - 1): ?><span class="m-pag-dots">…</span><?php endif; ?>
                <a href="<?php echo $base_url; ?>&page=<?php echo $total; ?><?php echo $search_param . $status_param; ?>" class="m-pag-btn"><?php echo $total; ?></a>
            <?php endif; ?>

            <!-- Next -->
            <a href="<?php echo $current < $total ? $base_url . '&page=' . ($current + 1) . $search_param . $status_param : '#'; ?>" class="m-pag-btn <?php echo $current >= $total ? 'disabled' : ''; ?>">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Create Master Modal -->
<div id="createMasterModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Add New <?php echo $singular; ?></h3>
            <button type="button" class="modal-close" onclick="closeModal('createMasterModal')">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <form id="createMasterForm">
            <div id="singleEntryForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="<?php echo $masterType === 'boq' ? 'boq_name' : 'name'; ?>" class="form-label"><?php echo $singular; ?> Name *</label>
                        <input type="text" id="<?php echo $masterType === 'boq' ? 'boq_name' : 'name'; ?>" name="<?php echo $masterType === 'boq' ? 'boq_name' : 'name'; ?>" class="form-input" required>
                    </div>

                    <?php if ($masterType === 'boq'): ?>
                        <div class="form-group">
                            <label class="flex items-center">
                                <input type="checkbox" id="is_serial_number_required" name="is_serial_number_required" class="form-checkbox">
                                <span class="ml-2">Serial Number Required</span>
                            </label>
                        </div>
                    <?php endif; ?>

                    <?php if ($masterType === 'states'): ?>
                        <div class="form-group">
                            <label for="country_id" class="form-label">Country *</label>
                            <select id="country_id" name="country_id" class="form-select" required onchange="loadStates(this.value)">
                                <option value="">Select Country</option>
                                <!-- Countries will be loaded dynamically -->
                            </select>
                        </div>
                    <?php endif; ?>

                    <?php if ($masterType === 'cities'): ?>
                        <div class="form-group">
                            <label for="country_id" class="form-label">Country *</label>
                            <select id="country_id" name="country_id" class="form-select" required onchange="loadStates(this.value)">
                                <option value="">Select Country</option>
                                <!-- Countries will be loaded dynamically -->
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="zone_id" class="form-label">Zone</label>
                            <select id="zone_id" name="zone_id" class="form-select" readonly>
                                <option value="">Auto-selected based on state</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="state_id" class="form-label">State *</label>
                            <select id="state_id" name="state_id" class="form-select" required onchange="loadZoneForState(this.value)">
                                <option value="">Select State</option>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="closeModal('createMasterModal')" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create <?php echo $singular; ?></button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    const currentMasterType = '<?php echo $masterType; ?>';
    const currentSingular = '<?php echo $singular; ?>';

    function changeMasterType(type) {
        window.location.href = `?type=${type}`;
    }

    // Search and filter functionality
    document.getElementById('searchInput').addEventListener('keyup', debounce(function() {
        applyFilters();
    }, 500));

    document.getElementById('statusFilter').addEventListener('change', applyFilters);

    function applyFilters() {
        const searchTerm = document.getElementById('searchInput').value;
        const status = document.getElementById('statusFilter').value;

        const url = new URL(window.location);
        url.searchParams.set('type', currentMasterType);

        if (searchTerm) url.searchParams.set('search', searchTerm);
        else url.searchParams.delete('search');

        if (status) url.searchParams.set('status', status);
        else url.searchParams.delete('status');

        url.searchParams.delete('page');
        window.location.href = url.toString();
    }


    // Form submission
    document.getElementById('createMasterForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        formData.append('action', 'create');
        formData.append('type', currentMasterType);

        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Creating...';

        fetch(`../../api/masters.php?path=${currentMasterType}`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal('createMasterModal');
                    showAlert(`${currentSingular} created successfully!`, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert(data.message || 'Failed to create record', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred while creating the record', 'error');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            });
    });

    // Master management functions
    function viewMaster(id) {
        fetch(`../../api/masters.php?path=${currentMasterType}/${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const record = data.data.record;
                    const nameField = currentMasterType === 'boq' ? 'boq_name' : 'name';
                    alert(`${currentSingular} Details:\n\nName: ${record[nameField]}\nStatus: ${record.status}\nCreated: ${formatDate(record.created_at)}\nUpdated: ${formatDate(record.updated_at)}`);
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Failed to load data', 'error');
            });
    }

    function editMaster(id) {
        // For now, show a simple prompt - can be enhanced with a modal later
        const nameField = currentMasterType === 'boq' ? 'boq_name' : 'name';
        const newName = prompt(`Enter new name for ${currentSingular}:`);
        if (newName && newName.trim()) {
            const formData = new FormData();
            formData.append(nameField, newName.trim());
            formData.append('status', 'active');

            fetch(`../../api/masters.php?path=${currentMasterType}/${id}`, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showAlert(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Failed to update', 'error');
                });
        }
    }

    function toggleMasterStatus(id) {
        confirmAction(`Are you sure you want to change this ${currentSingular.toLowerCase()}'s status?`, function() {
            fetch(`../../api/masters.php?path=${currentMasterType}/${id}/toggle-status`, {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showAlert(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Failed to update status', 'error');
                });
        });
    }

    function deleteMaster(id) {
        confirmAction(`Are you sure you want to delete this ${currentSingular.toLowerCase()}? This action cannot be undone.`, function() {
            fetch(`../../api/masters.php?path=${currentMasterType}/${id}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showAlert(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Failed to delete', 'error');
                });
        });
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    function confirmAction(message, callback) {
        if (confirm(message)) {
            callback();
        }
    }

    function showAlert(message, type) {
        // Create alert element
        const alertDiv = document.createElement('div');
        alertDiv.className = `fixed top-4 right-4 z-50 p-4 rounded-md shadow-lg ${
        type === 'success' ? 'bg-green-100 border border-green-400 text-green-700' :
        type === 'error' ? 'bg-red-100 border border-red-400 text-red-700' :
        'bg-blue-100 border border-blue-400 text-blue-700'
    }`;
        alertDiv.textContent = message;

        document.body.appendChild(alertDiv);

        // Remove after 3 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.parentNode.removeChild(alertDiv);
            }
        }, 3000);
    }

    // Load countries for states and cities forms
    function loadCountries() {
        const countrySelect = document.getElementById('country_id');
        if (!countrySelect) return;

        fetch('../../api/masters.php?path=countries')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    countrySelect.innerHTML = '<option value="">Select Country</option>';
                    data.data.records.forEach(country => {
                        countrySelect.innerHTML += `<option value="${country.id}">${country.name}</option>`;
                    });
                }
            })
            .catch(error => console.error('Error loading countries:', error));
    }

    // Load zones for dropdown
    function loadZones() {
        const zoneSelect = document.getElementById('zone_id');
        if (!zoneSelect) return;

        fetch('../../api/masters.php?path=zones')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Keep the zones data for later use
                    window.zonesData = data.data.records;
                }
            })
            .catch(error => console.error('Error loading zones:', error));
    }

    // Load states when country is selected
    function loadStates(countryId) {
        const stateSelect = document.getElementById('state_id');
        const zoneSelect = document.getElementById('zone_id');

        if (!stateSelect) return;

        stateSelect.innerHTML = '<option value="">Select State</option>';
        if (zoneSelect) {
            zoneSelect.innerHTML = '<option value="">Auto-selected based on state</option>';
        }

        if (!countryId) return;

        fetch(`../../api/masters.php?path=states&country_id=${countryId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    data.data.records.forEach(state => {
                        if (state.country_id == countryId) {
                            stateSelect.innerHTML += `<option value="${state.id}" data-zone-id="${state.zone_id}">${state.name}</option>`;
                        }
                    });
                }
            })
            .catch(error => console.error('Error loading states:', error));
    }

    // Load zone when state is selected
    function loadZoneForState(stateId) {
        const stateSelect = document.getElementById('state_id');
        const zoneSelect = document.getElementById('zone_id');

        if (!stateSelect || !zoneSelect || !stateId) {
            if (zoneSelect) {
                zoneSelect.innerHTML = '<option value="">Auto-selected based on state</option>';
            }
            return;
        }

        // Get the selected state option
        const selectedOption = stateSelect.options[stateSelect.selectedIndex];
        const zoneId = selectedOption.getAttribute('data-zone-id');

        if (zoneId && window.zonesData) {
            // Find the zone name
            const zone = window.zonesData.find(z => z.id == zoneId);
            if (zone) {
                zoneSelect.innerHTML = `<option value="${zone.id}" selected>${zone.name}</option>`;
            } else {
                zoneSelect.innerHTML = '<option value="">Zone not found</option>';
            }
        } else {
            // Fetch zone info from API if not available
            fetch(`../../api/masters.php?path=states/${stateId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data.record.zone_id) {
                        const zoneId = data.data.record.zone_id;
                        // Fetch zone details
                        fetch(`../../api/masters.php?path=zones/${zoneId}`)
                            .then(response => response.json())
                            .then(zoneData => {
                                if (zoneData.success) {
                                    const zone = zoneData.data.record;
                                    zoneSelect.innerHTML = `<option value="${zone.id}" selected>${zone.name}</option>`;
                                }
                            });
                    } else {
                        zoneSelect.innerHTML = '<option value="">No zone assigned</option>';
                    }
                })
                .catch(error => {
                    console.error('Error loading zone for state:', error);
                    zoneSelect.innerHTML = '<option value="">Error loading zone</option>';
                });
        }
    }

    function openCreateModal() {
        // Load countries for states and cities
        if (currentMasterType === 'states' || currentMasterType === 'cities') {
            loadCountries();
        }
        // Load zones for cities
        if (currentMasterType === 'cities') {
            loadZones();
        }
        openModal('createMasterModal');
    }

    function exportToCSV() {
        const searchTerm = document.getElementById('searchInput').value;
        const status = document.getElementById('statusFilter').value;
        
        const url = new URL('export.php', window.location.href);
        url.searchParams.set('type', currentMasterType);
        
        if (searchTerm) url.searchParams.set('search', searchTerm);
        if (status) url.searchParams.set('status', status);
        
        window.open(url.toString(), '_blank');
    }
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>