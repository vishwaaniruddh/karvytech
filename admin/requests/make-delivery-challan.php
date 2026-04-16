<?php
require_once __DIR__ . '/../../config/auth.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$title = 'Make Delivery Challan';
ob_start();
?>

<style>
    /* Premium Document Styles */
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
        max-width: 1000px;
        margin: 20px auto;
        padding: 40px;
        box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        border: 1px solid var(--challan-border);
        border-radius: 8px;
        color: #1a1a1a;
    }

    .challan-header {
        background-color: var(--challan-blue);
        color: white;
        padding: 12px 20px;
        text-align: center;
        border-radius: 4px 4px 0 0;
        margin-bottom: 0;
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
        letter-spacing: 0.025em;
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
    }

    .address-column:last-child {
        border-right: none;
    }

    .info-row {
        display: flex;
        margin-bottom: 4px;
        font-size: 12px;
    }

    .info-label {
        width: 120px;
        font-weight: 600;
        color: var(--challan-text-muted);
    }

    .info-value {
        flex: 1;
        font-weight: 500;
    }

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
        vertical-align: top;
    }

    .dispatch-details-section {
        border: 1px solid var(--challan-blue);
        border-top: none;
        padding: 0;
    }

    .dispatch-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        padding: 15px 20px;
    }

    .footer-section {
        display: grid;
        grid-template-columns: 1fr 1fr;
        margin-top: 30px;
        gap: 40px;
    }

    .signatory-box {
        text-align: center;
        padding-top: 60px;
    }

    .signatory-line {
        border-top: 1.5px solid #1e293b;
        padding-top: 8px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .disclaimer-text {
        margin-top: 30px;
        font-size: 10px;
        color: var(--challan-text-muted);
        line-height: 1.5;
    }

    .brand-accent {
        color: #2563eb;
        font-weight: 800;
    }

    @media print {
        body * { visibility: hidden; }
        .challan-container, .challan-container * { visibility: visible; }
        .challan-container {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 20px;
            box-shadow: none;
            border: none;
        }
        .no-print { display: none; }
    }
</style>

<div class="px-4 py-8 no-print">
    <div class="max-w-4xl mx-auto flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Generate Delivery Challan</h2>
            <p class="text-sm text-slate-500 font-medium mt-1 uppercase tracking-widest">Document Preview & Preparation</p>
        </div>
        <div class="flex gap-3">
             <button onclick="window.print()" class="px-6 py-2.5 bg-slate-900 text-white font-bold rounded-xl hover:bg-slate-800 transition-all shadow-lg flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print Documents
            </button>
        </div>
    </div>
</div>

<div class="challan-container">
    <!-- Main Document Header -->
    <div class="challan-header">
        <h1>Delivery Challan / Bill of Supply</h1>
    </div>

    <!-- DC Info Bar -->
    <div class="doc-info-bar">
        <div><span class="font-bold">DC No:</span> <span class="text-blue-700 font-extrabold ml-1 uppercase">KTEX-26-27-DC097-9591</span></div>
        <div><span class="font-bold">DATE:</span> <span class="ml-1"><?php echo date('d-M-y'); ?></span></div>
    </div>

    <!-- Section Titles -->
    <div class="section-title-bar">
        <div style="flex: 1;">Ship To - Address (Consignee)</div>
        <div style="flex: 1;">Dispatch From (Consignor)</div>
    </div>

    <!-- Address Details -->
    <div class="address-grid">
        <div class="address-column">
            <div class="info-row">
                <span class="info-label">Customer Name:</span>
                <span class="info-value font-bold">Reliance Retail Limited (Trends)</span>
            </div>
            <div class="info-row">
                <span class="info-label">Address:</span>
                <span class="info-value">Mira Road (9591), Mira Road, Mumbai</span>
            </div>
            <div class="mt-4"></div>
            <div class="info-row">
                <span class="info-label">Contact Person:</span>
                <span class="info-value">Bipin</span>
            </div>
            <div class="info-row">
                <span class="info-label">Contact Number:</span>
                <span class="info-value font-bold">7021374410</span>
            </div>
        </div>
        <div class="address-column">
            <div class="info-row">
                <span class="info-label">Name:</span>
                <span class="info-value font-bold tracking-tight">KARVY TECHNOLOGIES PVT. LTD.</span>
            </div>
            <div class="info-row">
                <span class="info-label">Address:</span>
                <span class="info-value text-[11px] leading-tight">401, 4th Floor, 58 West, Road No. 19, Subway Road, Above DCB Bank, Andheri (West), Mumbai - 400053</span>
            </div>
            <div class="info-row">
                <span class="info-label">GSTN:</span>
                <span class="info-value text-red-600 font-bold">27AAFCK5434Q1ZY</span>
            </div>
            <div class="mt-3"></div>
            <div class="info-row">
                <span class="info-label">Contact Person:</span>
                <span class="info-value">Bela</span>
            </div>
            <div class="info-row">
                <span class="info-label">Contact Number:</span>
                <span class="info-value">8425851115</span>
            </div>
        </div>
    </div>

    <!-- Material Table -->
    <table class="challan-table">
        <thead>
            <tr>
                <th width="5%">Sr No.</th>
                <th width="15%">Make</th>
                <th>Material Description</th>
                <th width="12%">HSN Code</th>
                <th width="8%">Qty</th>
                <th width="10%">UOM</th>
                <th width="12%">Rate (₹)</th>
                <th width="12%">Amount (₹)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td align="center">1</td>
                <td>Legrand</td>
                <td>24 Port Patch Panels & Cable Managers</td>
                <td align="center">-</td>
                <td align="right" class="font-bold">2</td>
                <td>Nos</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td align="center">2</td>
                <td>Legrand</td>
                <td>I/O Only</td>
                <td align="center">-</td>
                <td align="right" class="font-bold">89</td>
                <td>Nos</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td align="center">3</td>
                <td></td>
                <td>Cable Ties</td>
                <td align="center">-</td>
                <td align="right" class="font-bold">2</td>
                <td>Packets</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td align="center">4</td>
                <td></td>
                <td>PVC Pipe</td>
                <td align="center">-</td>
                <td align="right" class="font-bold">50</td>
                <td>Nos</td>
                <td></td>
                <td></td>
            </tr>
            <!-- Totals row -->
            <tr>
                <td colspan="7" align="right" class="font-extrabold uppercase text-[11px]">Grand Total</td>
                <td class="bg-gray-50"></td>
            </tr>
        </tbody>
    </table>

    <!-- Dispatch Details -->
    <div class="section-title-bar py-1">
        <div class="w-full text-center">Dispatch Details</div>
    </div>
    <div class="dispatch-details-section">
        <div class="dispatch-grid">
            <div>
                <div class="info-row">
                    <span class="info-label">Dispatch Through:</span>
                    <span class="info-value">Transport</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Dispatch Date:</span>
                    <span class="info-value">12-Apr-26</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Docket No:</span>
                    <span class="info-value">--</span>
                </div>
            </div>
            <div>
                <div class="info-row">
                    <span class="info-label">No of Boxes:</span>
                    <span class="info-value">--</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Weight (Kgs):</span>
                    <span class="info-value">--</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Prepared by:</span>
                    <span class="info-value font-mono font-bold">KT-00102</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Verified by:</span>
                    <span class="info-value">--</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Signatories -->
    <div class="footer-section">
        <div class="signatory-box">
            <div class="text-[11px] mb-1 font-bold">For <span class="brand-accent">Karvy Technologies Pvt Ltd</span></div>
            <div class="text-[9px] text-gray-400 italic mb-10">Authorized Signatory / Mumbai</div>
            <div class="signatory-line">KARVY TECHNOLOGIES PVT. LTD. | Authorized Signatory</div>
        </div>
        <div class="signatory-box">
            <div class="text-[11px] mb-1 font-bold">For (Recipient)</div>
            <div class="mb-10"></div>
            <div class="signatory-line">Received by (Stamp & Sign)</div>
        </div>
    </div>

    <!-- Statutory Notes -->
    <div class="disclaimer-text">
        <p>1) This challan is only for transportation purpose.</p>
        <p>2) The Receiver should confirm the material quantity & acknowledge with signed & stamp.</p>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>
