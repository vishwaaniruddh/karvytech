<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../models/Inventory.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$dispatchId = $_GET['id'] ?? null;
if (!$dispatchId) {
    header('Location: index.php');
    exit;
}

$inventoryModel = new Inventory();
$dispatch = $inventoryModel->getDispatchDetails($dispatchId);

// Fetch additional context if needed (consignor info is usually static or from settings)
$deliveryConfirmation = $inventoryModel->getDeliveryConfirmationDetails($dispatchId);

if (!$dispatch) {
    die("Dispatch record not found.");
}

$title = 'Delivery Challan - ' . $dispatch['dispatch_number'];
ob_start();
?>

<style>
    /* Document Style Interface */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

    :root {
        --challan-blue: #0f172a;
        --challan-blue-light: #1e293b;
        --challan-border: #e2e8f0;
        --challan-text-muted: #64748b;
    }

    .challan-container {
        font-family: 'Inter', sans-serif;
        background: white;
        width: 100%;
        max-width: none;
        margin: 0;
        padding: 40px;
        min-height: 100vh;
        color: #1a1a1a;
        box-sizing: border-box;
    }

    /* Print specific tweaks */
    @media print {
        .no-print { display: none !important; }
        .challan-container { padding: 0; border: none; width: 100% !important; margin: 0 !important; }
        body { background: white; margin: 0; padding: 0; }
        .main-content { padding: 0 !important; margin: 0 !important; border: none !important; box-shadow: none !important; }
        .admin-sidebar, .admin-header, #sidebar-overlay, #sidebarToggle, #toggleSidebar { display: none !important; }
        .flex.h-screen { height: auto !important; display: block !important; }
        main { padding: 0 !important; margin: 0 !important; background: white !important; }
        @page { margin: 1cm; size: auto; }
    }

    .challan-header {
        background-color: var(--challan-blue);
        color: white;
        padding: 12px 20px;
        text-align: center;
        border-radius: 4px 4px 0 0;
    }

    .challan-header h1 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .doc-info-bar {
        display: flex;
        justify-content: space-between;
        padding: 10px 20px;
        border: 1px solid var(--challan-blue);
        border-top: none;
        font-size: 13px;
    }

    .section-title-bar {
        background-color: #3b82f6;
        color: white;
        padding: 6px 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        display: flex;
    }

    .address-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        border-left: 1px solid var(--challan-blue);
        border-right: 1px solid var(--challan-blue);
    }

    .address-column {
        padding: 15px 20px;
        border-right: 1px solid #f1f5f9;
        font-size: 12px;
    }

    .address-column:last-child { border-right: none; }

    .info-row { display: flex; margin-bottom: 4px; }
    .info-label { width: 120px; font-weight: 600; color: var(--challan-text-muted); }
    .info-value { flex: 1; font-weight: 500; }

    .challan-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid var(--challan-blue);
        margin-top: -1px;
    }

    .challan-table th {
        background-color: #f8fafc;
        border: 1px solid var(--challan-blue);
        padding: 8px 10px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--challan-blue);
    }

    .challan-table td {
        border: 1px solid #e2e8f0;
        padding: 8px 10px;
        font-size: 12px;
    }

    .dispatch-details-section {
        border: 1px solid var(--challan-blue);
        border-top: none;
    }

    .logistics-table {
        width: 100%;
        border-collapse: collapse;
    }

    .logistics-table td {
        border: 1px solid #cbd5e1;
        padding: 6px 15px;
        font-size: 11px;
    }

    .logistics-label {
        width: 25%;
        background-color: #f1f5f9;
        color: #334155;
        font-weight: 700;
        text-transform: capitalize;
    }

    .logistics-value {
        width: 75%;
        background: white;
        font-weight: 600;
    }

    .signature-section {
        display: grid;
        grid-template-columns: 1.2fr 1fr;
        border: 1px solid var(--challan-blue);
        border-top: none;
        min-height: 140px;
    }

    .signatory-box {
        padding: 15px 20px;
        border-right: 1px solid #cbd5e1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        text-align: center;
    }

    .signatory-box:last-child { border-right: none; }

    .signatory-line {
        border-top: 1px solid var(--challan-blue);
        padding-top: 8px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        color: #475569;
    }

    .challan-footer-notes {
        padding: 8px 20px;
        font-size: 10px;
        color: #64748b;
        background: #f8fafc;
        border: 1px solid var(--challan-blue);
        border-top: none;
    }

    .action-bar {
        position: sticky;
        top: 0;
        z-index: 50;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(8px);
        border-bottom: 1px solid #e2e8f0;
        padding: 12px 0;
    }

    .btn-action {
        background: var(--challan-blue);
        color: white;
        padding: 8px 20px;
        border-radius: 8px;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 12px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
    }

    .btn-action:hover { transform: translateY(-1px); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
</style>

<div class="action-bar no-print">
    <div class="max-w-screen-2xl mx-auto flex justify-between items-center px-4">
        <a href="index.php" class="text-xs font-bold text-gray-400 hover:text-gray-900 flex items-center gap-2 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            BACK TO DISPATCHES
        </a>
        <button onclick="window.print()" class="btn-action">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            PRINT CHALLAN
        </button>
    </div>
</div>

<div class="challan-container" id="challanContent">
    <!-- Header -->
    <div class="challan-header">
        <h1>Delivery Challan / Bill of Supply</h1>
    </div>

    <!-- Info Bar -->
    <div class="doc-info-bar">
        <div><span class="font-bold">DC No:</span> <span class="text-blue-700 font-extrabold ml-1 uppercase"><?php echo htmlspecialchars($dispatch['dispatch_number']); ?></span></div>
        <div><span class="font-bold">DATE:</span> <span class="ml-1"><?php echo date('d-M-y', strtotime($dispatch['dispatch_date'])); ?></span></div>
    </div>

    <div class="section-title-bar">
        <div style="flex: 1;">Ship To - Address (Consignee)</div>
        <div style="flex: 1;">Dispatch From (Consignor)</div>
    </div>

    <!-- Address Grid -->
    <div class="address-grid">
        <!-- Consignee Column -->
        <div class="address-column">
            <div class="info-row">
                <span class="info-label">Customer:</span>
                <span class="info-value font-bold"><?php echo htmlspecialchars($dispatch['site_name'] ?: 'N/A'); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Address:</span>
                <span class="info-value text-[11px]"><?php echo nl2br(htmlspecialchars($dispatch['delivery_address'] ?: '--')); ?></span>
            </div>
            <div class="mt-4"></div>
            <div class="info-row">
                <span class="info-label">Contact Person:</span>
                <span class="info-value font-bold"><?php echo htmlspecialchars($dispatch['contact_person_name'] ?: '--'); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Contact Number:</span>
                <span class="info-value font-bold"><?php echo htmlspecialchars($dispatch['contact_person_phone'] ?: '--'); ?></span>
            </div>
        </div>

        <!-- Consignor Column -->
        <div class="address-column">
            <div class="info-row">
                <span class="info-label">Name:</span>
                <span class="info-value font-bold">KARVY TECHNOLOGIES PVT. LTD.</span>
            </div>
            <div class="info-row">
                <span class="info-label">Address:</span>
                <span class="info-value text-[11px]">401, 4th Floor, 58 West, Road No. 19, Andheri (West), Mumbai - 400053</span>
            </div>
            <div class="info-row">
                <span class="info-label">GSTN:</span>
                <span class="info-value text-red-600 font-bold uppercase">27AAFCK5434Q1ZY</span>
            </div>
            <div class="info-row mt-2">
                <span class="info-label">Prepared By:</span>
                <span class="info-value font-bold"><?php echo htmlspecialchars($dispatch['dispatched_by_name'] ?? 'Authorized Personnel'); ?></span>
            </div>
        </div>
    </div>

    <!-- Material Table -->
    <table class="challan-table">
        <thead>
            <tr>
                <th width="5%">Sr.</th>
                <th>Material Description</th>
                <th width="15%">HSN Code</th>
                <th width="10%">Qty</th>
                <th width="10%">UOM</th>
                <th width="12%">Rate (₹)</th>
                <th width="12%">Amount (₹)</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $items = $dispatch['items'] ?? [];
            $grandTotal = 0;
            if (empty($items)): 
            ?>
            <tr><td colspan="7" align="center" class="py-10 text-gray-400 italic">No items linked to this dispatch.</td></tr>
            <?php else: ?>
                <?php foreach ($items as $idx => $item): 
                    $qty = floatval($item['quantity_dispatched']);
                    $rate = floatval($item['unit_cost'] ?: 0);
                    $amount = $qty * $rate;
                    $grandTotal += $amount;
                ?>
                <tr>
                    <td align="center"><?php echo $idx + 1; ?></td>
                    <td>
                        <div class="font-bold"><?php echo htmlspecialchars($item['item_name']); ?></div>
                        <div class="text-[10px] text-slate-500 uppercase">Code: <?php echo htmlspecialchars($item['item_code']); ?></div>
                        <?php if ($item['serial_numbers']): ?>
                            <div class="text-[9px] text-blue-600 font-mono mt-1">SN: <?php echo htmlspecialchars($item['serial_numbers']); ?></div>
                        <?php endif; ?>
                    </td>
                    <td align="center"><?php echo htmlspecialchars($item['hsn_code'] ?? '--'); ?></td>
                    <td align="right" class="font-bold"><?php echo number_format($qty); ?></td>
                    <td align="center"><?php echo htmlspecialchars($item['unit']); ?></td>
                    <td align="right">₹<?php echo number_format($rate, 2); ?></td>
                    <td align="right" class="font-bold">₹<?php echo number_format($amount, 2); ?></td>
                </tr>
                <?php endforeach; ?>
                
                <!-- Spacer rows to maintain document length -->
                <?php for($i = count($items); $i < 5; $i++): ?>
                    <tr><td class="py-6"></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                <?php endfor; ?>
            <?php endif; ?>
            
            <tr class="bg-gray-50/50">
                <td colspan="6" align="right" class="font-extrabold uppercase text-[10px] py-3 pr-4">Grand Total (Estimated Value)</td>
                <td align="right" class="font-extrabold text-blue-800">₹<?php echo number_format($grandTotal, 2); ?></td>
            </tr>
        </tbody>
    </table>

    <!-- Logistics Details -->
    <div class="section-title-bar py-1">
        <div class="w-full text-center">Logistics Details</div>
    </div>
    <div class="dispatch-details-section">
        <table class="logistics-table">
            <tr>
                <td class="logistics-label">Dispatch Through:</td>
                <td class="logistics-value"><?php echo htmlspecialchars($dispatch['courier_name'] ?: 'Internal Transit'); ?></td>
            </tr>
            <tr>
                <td class="logistics-label">Dispatch Date:</td>
                <td class="logistics-value"><?php echo date('d-M-y', strtotime($dispatch['dispatch_date'])); ?></td>
            </tr>
            <tr>
                <td class="logistics-label">Tracking / Pocket No:</td>
                <td class="logistics-value font-mono"><?php echo htmlspecialchars($dispatch['tracking_number'] ?: '--'); ?></td>
            </tr>
            <tr>
                <td class="logistics-label">Expected Arrival:</td>
                <td class="logistics-value"><?php echo $dispatch['expected_delivery_date'] ? date('d-M-y', strtotime($dispatch['expected_delivery_date'])) : '--'; ?></td>
            </tr>
            <tr>
                <td class="logistics-label">Status:</td>
                <td class="logistics-value capitalize"><?php echo str_replace('_', ' ', $dispatch['dispatch_status']); ?></td>
            </tr>
        </table>
    </div>

    <!-- Signatories -->
    <div class="signature-section">
        <div class="signatory-box">
            <div>
                <div class="text-[11px] mb-1 font-bold">For <span class="text-blue-700">Karvy Technologies Pvt Ltd</span></div>
                <div class="text-[13px] font-bold text-slate-800">◆ KARVY TECHNOLOGIES PVT. LTD. ◆</div>
                <div class="text-[9px] text-gray-400 italic">Authorized Signatory | Mumbai</div>
            </div>
            <div class="signatory-line">Authorized Signatory</div>
        </div>
        <div class="signatory-box">
            <div class="text-[11px] mb-1 font-bold">For (Recipient)</div>
            <div class="signatory-line">Received by (Stamp & Sign)</div>
        </div>
    </div>

    <div class="challan-footer-notes">
        <p>1) This challan is only for transportation purpose.</p>
        <p>2) The Receiver should confirm the material quantity & acknowledge with signed & stamp.</p>
        <p>3) Computer generated document, no physical signature required unless specifically stated.</p>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../includes/admin_layout.php';
?>
