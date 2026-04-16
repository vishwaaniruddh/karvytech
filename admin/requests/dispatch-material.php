<?php
require_once __DIR__ . '/../../config/auth.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$requestId = $_GET['request_id'] ?? null;
if (!$requestId) {
    header('Location: index.php');
    exit;
}

$title = 'Interactive Dispatch - Request #' . $requestId;
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
        .main-content { padding: 0 !important; margin: 0 !important; }
    }

    .vendor-info-bar {
        background-color: #f1f5f9;
        border: 1px solid var(--challan-blue);
        border-bottom: none;
        padding: 8px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 11px;
    }

    .vendor-tag {
        background: #e0f2fe;
        color: #0369a1;
        padding: 2px 8px;
        border-radius: 4px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.025em;
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

    /* Interactive Table Styling */
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
        padding: 0;
        font-size: 12px;
    }

    /* Invisible Inputs for Document look */
    .ghost-input {
        width: 100%;
        padding: 8px 10px;
        border: none;
        background: transparent;
        font-size: 12px;
        font-family: inherit;
        color: inherit;
        outline: none;
        transition: background 0.2s;
    }

    .ghost-input:focus {
        background: #f0f9ff;
    }

    .ghost-input.qty-input { text-align: right; font-weight: 700; color: #1e40af; }
    .ghost-input.numeric { text-align: right; }

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
        padding: 0;
        font-size: 11px;
    }

    .logistics-label {
        width: 20%;
        background-color: #f1f5f9;
        color: #334155;
        font-weight: 700;
        padding: 6px 15px;
        text-transform: capitalize;
    }

    .logistics-value {
        width: 80%;
        padding: 0;
        background: white;
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
    }

    .signatory-box:last-child { border-right: none; }

    .signatory-line {
        border-top: 1px solid #94a3b8;
        padding-top: 6px;
        font-size: 10px;
        font-weight: 700;
        text-align: center;
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

    .signatory-box { text-align: center; padding-top: 40px; }
    .signatory-line { border-top: 1px solid var(--challan-blue); padding-top: 8px; font-size: 10px; font-weight: 700; text-transform: uppercase; }

    /* Skeleton Loading */
    .skeleton {
        background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%);
        background-size: 200% 100%;
        animation: skeleton-loading 1.5s infinite;
        border-radius: 4px;
        height: 14px;
        width: 80%;
    }

    @keyframes skeleton-loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    .hidden { display: none !important; }

    .action-bar {
        position: sticky;
        top: 0;
        z-index: 50;
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(8px);
        border-bottom: 1px solid #e2e8f0;
        padding: 16px 0;
    }

    .btn-finalize {
        background: var(--challan-blue);
        color: white;
        padding: 10px 24px;
        border-radius: 12px;
        font-weight: 800;
        text-transform: uppercase;
        font-size: 13px;
        letter-spacing: 0.05em;
        transition: all 0.2s;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    }

    .btn-finalize:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1); }
    .btn-finalize:disabled { background: #94a3b8; cursor: not-allowed; }

    #notification-container { position: fixed; top: 24px; right: 24px; z-index: 9999; }
</style>

<div id="notification-container"></div>

<div class="action-bar no-print">
    <div class="max-w-screen-2xl mx-auto flex justify-between items-center px-4">
        <div>
            <!-- Heading removed as requested -->
        </div>
        <div class="flex gap-3">
            <button id="submitBtn" class="btn-finalize flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                Confirm & Finalize Dispatch
            </button>
            <button id="printBtn" class="btn-finalize hidden flex items-center gap-2 bg-slate-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Print Challan
            </button>
        </div>
    </div>
</div>

<div class="challan-container" id="challanApp">
    <form id="dispatchDataForm">
        <input type="hidden" name="material_request_id" value="<?php echo $requestId; ?>">
        
        <!-- Vendor Info Bar -->
        <div id="vendorInfoBar" class="vendor-info-bar hidden">
            <div class="flex items-center gap-2">
                <span class="vendor-tag">Vendor / Requester:</span>
                <span id="labelVendorName" class="font-bold text-slate-800"></span>
            </div>
            <div class="flex gap-4">
                <div><span class="text-slate-500 font-medium">Contact:</span> <span id="labelVendorContact" class="font-bold"></span></div>
                <div><span class="text-slate-500 font-medium">Phone:</span> <span id="labelVendorPhone" class="font-bold"></span></div>
            </div>
        </div>

        <!-- header -->
        <div class="challan-header">
            <h1>Delivery Challan / Bill of Supply</h1>
        </div>

        <!-- Info Bar -->
        <div class="doc-info-bar">
            <div><span class="font-bold">DC No:</span> <span id="labelDispatchNo" class="text-blue-700 font-extrabold ml-1 uppercase">PENDING GENERATION</span></div>
            <div><span class="font-bold">DATE:</span> <span class="ml-1"><?php echo date('d-M-y'); ?></span></div>
        </div>

        <div class="section-title-bar">
            <div style="flex: 1;">Ship To - Address (Consignee)</div>
            <div style="flex: 1;">Dispatch From (Consignor)</div>
        </div>

        <!-- Address Grid -->
        <div class="address-grid">
            <div class="address-column" id="consigneeArea">
                <div class="space-y-1">
                    <div class="skeleton"></div>
                    <div class="skeleton"></div>
                    <div class="skeleton w-1/2"></div>
                </div>
            </div>
            <div class="address-column" id="consignorArea">
                <div class="info-row">
                    <span class="info-label">Name:</span>
                    <span class="info-value font-bold">KARVY TECHNOLOGIES PVT. LTD.</span>
                </div>
                <!-- Static consignor info for now as per reference -->
                <div class="info-row">
                    <span class="info-label">Address:</span>
                    <span class="info-value text-[11px]">401, 4th Floor, 58 West, Road No. 19, Andheri (West), Mumbai - 400053</span>
                </div>
                <div class="info-row">
                    <span class="info-label">GSTN:</span>
                    <span class="info-value text-red-600 font-bold uppercase">27AAFCK5434Q1ZY</span>
                </div>
                <div class="info-row mt-2">
                    <span class="info-label">Contact Person:</span>
                    <span class="info-value"><input type="text" name="consignor_contact_person" class="ghost-input py-0 px-0 h-auto font-bold underline decoration-dotted" value="Bela"></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Contact Number:</span>
                    <span class="info-value"><input type="text" name="consignor_contact_phone" class="ghost-input py-0 px-0 h-auto font-bold underline decoration-dotted" value="8425851115"></span>
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
            <tbody id="itemsTableBody">
                <!-- Injected via JS -->
            </tbody>
        </table>

        <!-- Dispatch Details -->
        <div class="section-title-bar py-1">
            <div class="w-full text-center">Logistics Details</div>
        </div>
        <div class="dispatch-details-section">
            <table class="logistics-table">
                <tr>
                    <td class="logistics-label">Dispatch Through:</td>
                    <td class="logistics-value">
                        <select name="courier_name" id="courier_name" class="ghost-input font-bold" required>
                            <option value="">Select Transport...</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="logistics-label">Dispatch Date:</td>
                    <td class="logistics-value">
                        <input type="text" name="dispatch_date" value="<?php echo date('d-M-y'); ?>" class="ghost-input font-bold" required>
                    </td>
                </tr>
                <tr>
                    <td class="logistics-label">Docket No:</td>
                    <td class="logistics-value">
                        <input type="text" name="pod_number" class="ghost-input font-bold" placeholder="---">
                    </td>
                </tr>
                <tr>
                    <td class="logistics-label">No. of Boxes:</td>
                    <td class="logistics-value">
                        <input type="text" name="box_count" class="ghost-input font-bold" placeholder="---">
                    </td>
                </tr>
                <tr>
                    <td class="logistics-label">Weight (Kgs):</td>
                    <td class="logistics-value">
                        <input type="text" name="weight" class="ghost-input font-bold" placeholder="---">
                    </td>
                </tr>
                <tr>
                    <td class="logistics-label">Prepared by:</td>
                    <td class="logistics-value">
                        <input type="text" name="prepared_by_name" class="ghost-input font-bold" value="KT-00102">
                    </td>
                </tr>
                <tr>
                    <td class="logistics-label">Verified by:</td>
                    <td class="logistics-value">
                        <input type="text" name="verified_by_name" class="ghost-input font-bold" placeholder="---">
                    </td>
                </tr>
            </table>
        </div>

        <!-- Signatories -->
        <div class="signature-section">
            <div class="signatory-box">
                <div>
                    <div class="text-[11px] mb-1 font-bold">For <span class="brand-accent">Karvy Technologies Pvt Ltd</span></div>
                    <div class="text-[14px] font-bold text-slate-800">◆ KARVY TECHNOLOGIES PVT. LTD. ◆</div>
                    <div class="text-[9px] text-gray-400 italic">Authorized Signatory | Mumbai</div>
                    <div class="text-[9px] text-gray-500">GSTIN: 27AAFCK5434Q1ZY</div>
                </div>
                <div class="signatory-line">KARVY TECHNOLOGIES PVT. LTD. | Authorized Signatory</div>
            </div>
            <div class="signatory-box">
                <div class="text-[11px] mb-1 font-bold">For (Recipient)</div>
                <div class="signatory-line">Received by (Stamp & Sign)</div>
            </div>
        </div>

        <div class="challan-footer-notes">
            <p>1) This challan is only for transportation purpose.</p>
            <p>2) The Receiver should confirm the material quantity & acknowledge with signed & stamp.</p>
        </div>

        <!-- Inputs for mapping Consignee hidden values -->
        <input type="hidden" name="delivery_address" id="hidden_delivery_address">
    </form>
</div>

<script>
const REQUEST_ID = <?php echo $requestId; ?>;

document.addEventListener('DOMContentLoaded', initInteractiveDispatch);

async function initInteractiveDispatch() {
    try {
        const response = await fetch(`../../api/material_requests.php?action=get_dispatch_data&request_id=${REQUEST_ID}`);
        const result = await response.json();

        if (!result.success) {
            showNotification('Error', result.message, 'error');
            return;
        }

        const { request, items, couriers } = result.data;
        
        populateVendorBar(request);
        populateConsignee(request);
        populateItems(items);
        populateCouriers(couriers);
        
    } catch (error) {
        console.error(error);
        showNotification('System Error', 'Failed to fetch request data.', 'error');
    }
}

function populateVendorBar(request) {
    const bar = document.getElementById('vendorInfoBar');
    const name = document.getElementById('labelVendorName');
    const contact = document.getElementById('labelVendorContact');
    const phone = document.getElementById('labelVendorPhone');

    if (request.vendor_company_name) {
        name.textContent = request.vendor_company_name;
        contact.textContent = request.vendor_contact || 'N/A';
        phone.textContent = request.vendor_phone || 'N/A';
        bar.classList.remove('hidden');
    }
}

function populateConsignee(request) {
    const area = document.getElementById('consigneeArea');
    area.innerHTML = `
        <div class="info-row">
            <span class="info-label">Customer:</span>
            <span class="info-value font-bold">${request.site_name}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Address:</span>
            <span class="info-value">${request.site_location || request.address || '--'}</span>
        </div>
        <div class="mt-4"></div>
        <div class="info-row">
            <span class="info-label">Contact Person:</span>
            <span class="info-value"><input type="text" name="contact_person_name" class="ghost-input py-0 px-0 h-auto font-bold underline decoration-dotted" value="${request.site_contact || request.vendor_contact || ''}"></span>
        </div>
        <div class="info-row">
            <span class="info-label">Contact Number:</span>
            <span class="info-value"><input type="text" name="contact_person_phone" class="ghost-input py-0 px-0 h-auto font-bold underline decoration-dotted" value="${request.site_phone || request.vendor_phone || ''}"></span>
        </div>
    `;

    // Populate delivery address hidded field
    document.getElementById('hidden_delivery_address').value = request.site_location || request.address || '';
}

function populateItems(items) {
    const tbody = document.getElementById('itemsTableBody');
    tbody.innerHTML = items.map((item, idx) => {
        const maxQty = item.stock ? Math.min(parseInt(item.original.quantity), item.stock.available_qty) : parseInt(item.original.quantity);
        return `
            <tr>
                <td align="center" class="py-2">${idx + 1}</td>
                <td>
                    <div class="px-2 font-bold">${item.item_name}</div>
                    <div class="px-2 text-[10px] text-slate-500 uppercase tracking-tighter">Code: ${item.item_code}</div>
                    <input type="hidden" name="items[${idx}][boq_item_id]" value="${item.boq_item_id || ''}">
                    <input type="hidden" name="items[${idx}][material_name]" value="${item.item_name}">
                </td>
                <td><input type="text" name="items[${idx}][hsn_code]" class="ghost-input" placeholder="-"></td>
                <td><input type="number" name="items[${idx}][dispatch_quantity]" class="ghost-input qty-input" value="${maxQty}" max="${maxQty}" min="0" onchange="calculateAmounts()"></td>
                <td align="center"><div class="px-2">${item.unit}</div></td>
                <td><input type="number" step="0.01" name="items[${idx}][unit_cost]" class="ghost-input numeric rate-input" placeholder="0.00" onchange="calculateAmounts()"></td>
                <td><input type="text" class="ghost-input numeric amount-display" readonly value="0.00"></td>
            </tr>
        `;
    }).join('');
    
    // Add spacer rows for aesthetics
    if(items.length < 5) {
        for(let i=0; i < (5-items.length); i++) {
            tbody.innerHTML += `<tr><td class="py-6"></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>`;
        }
    }

    // Add Grand Total row
    tbody.innerHTML += `
        <tr class="bg-slate-50">
            <td colspan="6" align="right" class="font-extrabold uppercase text-[10px] py-3 pr-4">Grand Total (Estimated Value)</td>
            <td><input type="text" id="grandTotalDisplay" class="ghost-input numeric font-extrabold text-blue-800" readonly value="₹ 0.00"></td>
        </tr>
    `;
}

function populateCouriers(couriers) {
    const sel = document.getElementById('courier_name');
    couriers.forEach(c => {
        const opt = document.createElement('option');
        opt.value = c.courier_name;
        opt.textContent = c.courier_name;
        sel.appendChild(opt);
    });
}

function calculateAmounts() {
    const rows = document.querySelectorAll('#itemsTableBody tr:not(.bg-slate-50)');
    let grandTotal = 0;
    
    rows.forEach(row => {
        const qtyInp = row.querySelector('.qty-input');
        const rateInp = row.querySelector('.rate-input');
        const amountDisp = row.querySelector('.amount-display');
        
        if (qtyInp && rateInp && amountDisp) {
            const qty = parseFloat(qtyInp.value) || 0;
            const rate = parseFloat(rateInp.value) || 0;
            const amount = qty * rate;
            amountDisp.value = amount.toFixed(2);
            grandTotal += amount;
        }
    });
    
    document.getElementById('grandTotalDisplay').value = '₹ ' + grandTotal.toLocaleString('en-IN', {minimumFractionDigits: 2});
}

document.getElementById('submitBtn').addEventListener('click', async () => {
    const form = document.getElementById('dispatchDataForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const btn = document.getElementById('submitBtn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = 'Processing Shipment...';

    try {
        const formData = new FormData(form);
        const response = await fetch('process-material-dispatch.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            showNotification('Success', 'Dispatch registered successfully!', 'success');
            
            // Update UI to show DC Number and Print Button
            document.getElementById('labelDispatchNo').textContent = result.data.dispatch_number;
            document.getElementById('submitBtn').classList.add('hidden');
            document.getElementById('printBtn').classList.remove('hidden');
            document.getElementById('pageTitle').textContent = 'Delivery Challan Generated';
            
            // Disable all inputs to lock the document
            document.querySelectorAll('.ghost-input').forEach(inp => inp.readOnly = true);
            document.querySelectorAll('select.ghost-input').forEach(sel => sel.disabled = true);
            
        } else {
            showNotification('Dispatch Failed', result.message, 'error');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    } catch (err) {
        console.error(err);
        showNotification('Fatal Error', 'An error occurred during submission.', 'error');
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
});

document.getElementById('printBtn').addEventListener('click', () => {
    window.print();
});

function showNotification(title, message, type = 'success') {
    const container = document.getElementById('notification-container');
    const card = document.createElement('div');
    card.className = `p-4 mb-4 rounded-xl shadow-2xl border flex flex-col gap-1 min-w-[300px] animate-in slide-in-from-right duration-300 ${
        type === 'success' ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'
    }`;
    card.innerHTML = `
        <div class="font-bold text-sm ${type === 'success' ? 'text-green-800' : 'text-red-800'}">${title}</div>
        <div class="text-xs text-slate-600">${message}</div>
    `;
    container.appendChild(card);
    setTimeout(() => card.remove(), 4000);
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>
