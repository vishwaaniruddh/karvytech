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

try {
    $inventoryModel = new Inventory();
    $dispatch = $inventoryModel->getDispatchDetails($dispatchId);

    if (!$dispatch) {
        header('Location: index.php?error=dispatch_not_found');
        exit;
    }
} catch (Exception $e) {
    error_log("Error in print-dispatch.php: " . $e->getMessage());
    header('Location: index.php?error=database_error');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dispatch Slip - <?php echo htmlspecialchars($dispatch['dispatch_number']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: white;
            color: #000;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .document-title {
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
        }
        
        .dispatch-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .info-section {
            border: 1px solid #000;
            padding: 15px;
        }
        
        .info-section h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            font-weight: bold;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 8px;
        }
        
        .info-label {
            font-weight: bold;
            width: 120px;
            flex-shrink: 0;
        }
        
        .info-value {
            flex: 1;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            font-size: 12px;
        }
        
        .items-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .items-table .text-right {
            text-align: right;
        }
        
        .items-table .text-center {
            text-align: center;
        }
        
        .total-row {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 30px;
            margin-top: 50px;
        }
        
        .signature-box {
            border-top: 1px solid #000;
            padding-top: 10px;
            text-align: center;
            min-height: 60px;
        }
        
        .signature-label {
            font-weight: bold;
            margin-top: 10px;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 15px;
            }
            
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <!-- Print Button (hidden when printing) -->
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #007cba; color: white; border: none; border-radius: 4px; cursor: pointer;">
            Print Dispatch Slip
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px;">
            Close
        </button>
    </div>

    <!-- Header -->
    <div class="header">
        <div class="company-name">Site Installation Management System</div>
        <div class="document-title">MATERIAL DISPATCH SLIP</div>
        <div style="margin-top: 10px; font-size: 14px;">
            Dispatch No: <strong><?php echo htmlspecialchars($dispatch['dispatch_number']); ?></strong>
        </div>
    </div>

    <!-- Dispatch Information -->
    <div class="dispatch-info">
        <div class="info-section">
            <h3>DISPATCH DETAILS</h3>
            <div class="info-row">
                <span class="info-label">Dispatch Date:</span>
                <span class="info-value"><?php echo date('d M Y', strtotime($dispatch['dispatch_date'])); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Status:</span>
                <span class="info-value"><?php echo ucfirst(str_replace('_', ' ', $dispatch['dispatch_status'])); ?></span>
            </div>
            <?php if ($dispatch['expected_delivery_date']): ?>
            <div class="info-row">
                <span class="info-label">Expected Delivery:</span>
                <span class="info-value"><?php echo date('d M Y', strtotime($dispatch['expected_delivery_date'])); ?></span>
            </div>
            <?php endif; ?>
            <?php if ($dispatch['courier_name']): ?>
            <div class="info-row">
                <span class="info-label">Courier:</span>
                <span class="info-value"><?php echo htmlspecialchars($dispatch['courier_name']); ?></span>
            </div>
            <?php endif; ?>
            <?php if ($dispatch['tracking_number']): ?>
            <div class="info-row">
                <span class="info-label">Tracking No:</span>
                <span class="info-value"><?php echo htmlspecialchars($dispatch['tracking_number']); ?></span>
            </div>
            <?php endif; ?>
        </div>

        <div class="info-section">
            <h3>DELIVERY INFORMATION</h3>
            <?php if ($dispatch['site_code']): ?>
            <div class="info-row">
                <span class="info-label">Site:</span>
                <span class="info-value"><?php echo htmlspecialchars($dispatch['site_code']); ?></span>
            </div>
            <?php endif; ?>
            <?php if ($dispatch['vendor_name']): ?>
            <div class="info-row">
                <span class="info-label">Vendor:</span>
                <span class="info-value"><?php echo htmlspecialchars($dispatch['vendor_name']); ?></span>
            </div>
            <?php endif; ?>
            <div class="info-row">
                <span class="info-label">Contact Person:</span>
                <span class="info-value"><?php echo htmlspecialchars($dispatch['contact_person_name']); ?></span>
            </div>
            <?php if ($dispatch['contact_person_phone']): ?>
            <div class="info-row">
                <span class="info-label">Phone:</span>
                <span class="info-value"><?php echo htmlspecialchars($dispatch['contact_person_phone']); ?></span>
            </div>
            <?php endif; ?>
            <div class="info-row">
                <span class="info-label">Address:</span>
                <span class="info-value"><?php echo htmlspecialchars($dispatch['delivery_address']); ?></span>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">S.No</th>
                <th style="width: 25%;">Item Description</th>
                <th style="width: 15%;">Item Code</th>
                <th style="width: 10%;">Quantity</th>
                <th style="width: 8%;">Unit</th>
                <th style="width: 12%;">Unit Cost</th>
                <th style="width: 12%;">Total Cost</th>
                <th style="width: 13%;">Batch/Serial</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($dispatch['items'])): ?>
                <?php 
                $totalValue = 0;
                $serialNo = 1;
                foreach ($dispatch['items'] as $item): 
                    $totalValue += $item['total_cost'] ?? 0;
                ?>
                <tr>
                    <td class="text-center"><?php echo $serialNo++; ?></td>
                    <td><?php echo htmlspecialchars($item['item_name'] ?? 'Unknown Item'); ?></td>
                    <td><?php echo htmlspecialchars($item['item_code'] ?? 'N/A'); ?></td>
                    <td class="text-right"><?php echo number_format($item['quantity_dispatched'] ?? 0, 2); ?></td>
                    <td class="text-center"><?php echo htmlspecialchars($item['unit'] ?? 'Nos'); ?></td>
                    <td class="text-right">₹<?php echo number_format($item['unit_cost'] ?? 0, 2); ?></td>
                    <td class="text-right">₹<?php echo number_format($item['total_cost'] ?? 0, 2); ?></td>
                    <td class="text-center"><?php echo htmlspecialchars($item['batch_number'] ?? '-'); ?></td>
                </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="6" class="text-right">TOTAL VALUE:</td>
                    <td class="text-right">₹<?php echo number_format($totalValue, 2); ?></td>
                    <td></td>
                </tr>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center">No items found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ($dispatch['delivery_remarks']): ?>
    <!-- Remarks -->
    <div style="margin-bottom: 30px;">
        <strong>Delivery Remarks:</strong><br>
        <?php echo htmlspecialchars($dispatch['delivery_remarks']); ?>
    </div>
    <?php endif; ?>

    <!-- Signatures -->
    <div class="signatures">
        <div class="signature-box">
            <div class="signature-label">Prepared By</div>
            <div style="margin-top: 20px;">Date: ___________</div>
        </div>
        <div class="signature-box">
            <div class="signature-label">Dispatched By</div>
            <div style="margin-top: 20px;">Date: ___________</div>
        </div>
        <div class="signature-box">
            <div class="signature-label">Received By</div>
            <div style="margin-top: 20px;">Date: ___________</div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>This is a computer generated document. No signature required.</p>
        <p>Generated on: <?php echo date('d M Y H:i:s'); ?></p>
    </div>

    <script>
        // Auto-print when page loads (optional)
        // window.onload = function() { window.print(); };
    </script>
</body>
</html>