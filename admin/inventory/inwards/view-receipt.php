<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../models/Inventory.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$receiptId = $_GET['id'] ?? null;
if (!$receiptId) {
    header('Location: index.php');
    exit;
}

$inventoryModel = new Inventory();

// Get receipt details
$receipt = $inventoryModel->getInwardReceiptDetails($receiptId);
if (!$receipt) {
    header('Location: index.php');
    exit;
}

$title = 'Inward Receipt Details - ' . $receipt['receipt_number'];
ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Inward Receipt Details</h1>
        <p class="mt-2 text-sm text-gray-700">Receipt Number: <?php echo htmlspecialchars($receipt['receipt_number']); ?></p>
    </div>
    <div class="flex space-x-2">
        <a href="index.php" class="btn btn-secondary">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
            </svg>
            Back to Receipts
        </a>
        <?php if ($receipt['status'] === 'pending'): ?>
            <button onclick="verifyReceipt(<?php echo $receipt['id']; ?>)" class="btn btn-success">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
                Verify Receipt
            </button>
        <?php endif; ?>
        <button onclick="printReceipt()" class="btn btn-primary">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z" clip-rule="evenodd"></path>
            </svg>
            Print Receipt
        </button>
    </div>
</div>

<!-- Receipt Status Card -->
<div class="card mb-6">
    <div class="card-header">
        <div class="flex items-center justify-between">
            <h3 class="card-title">Receipt Status</h3>
            <?php
            $statusClasses = [
                'pending' => 'bg-yellow-100 text-yellow-800',
                'verified' => 'bg-green-100 text-green-800',
                'rejected' => 'bg-red-100 text-red-800'
            ];
            $statusClass = $statusClasses[$receipt['status']] ?? 'bg-gray-100 text-gray-800';
            ?>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?php echo $statusClass; ?>">
                <?php echo ucfirst($receipt['status']); ?>
            </span>
        </div>
    </div>
    <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Created Date</label>
                <p class="text-sm text-gray-900"><?php echo date('d M Y, H:i', strtotime($receipt['created_at'])); ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Received By</label>
                <p class="text-sm text-gray-900"><?php echo htmlspecialchars($receipt['received_by_name']); ?></p>
            </div>
            <?php if ($receipt['verified_by_name']): ?>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Verified By</label>
                <p class="text-sm text-gray-900"><?php echo htmlspecialchars($receipt['verified_by_name']); ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Receipt Information -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Basic Information -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Receipt Information</h3>
        </div>
        <div class="card-body">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Receipt Number</label>
                    <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded font-mono"><?php echo htmlspecialchars($receipt['receipt_number']); ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Receipt Date</label>
                    <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo date('d M Y', strtotime($receipt['receipt_date'])); ?></p>
                </div>
                <?php if ($receipt['purchase_order_number']): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Purchase Order Number</label>
                    <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo htmlspecialchars($receipt['purchase_order_number']); ?></p>
                </div>
                <?php endif; ?>
                <?php if ($receipt['invoice_number']): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Invoice Number</label>
                    <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo htmlspecialchars($receipt['invoice_number']); ?></p>
                </div>
                <?php endif; ?>
                <?php if ($receipt['invoice_date']): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Invoice Date</label>
                    <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo date('d M Y', strtotime($receipt['invoice_date'])); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Supplier Information -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Supplier Information</h3>
        </div>
        <div class="card-body">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Supplier Name</label>
                    <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo htmlspecialchars($receipt['supplier_name']); ?></p>
                </div>
                <?php if ($receipt['supplier_contact']): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Supplier Contact</label>
                    <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded"><?php echo htmlspecialchars($receipt['supplier_contact']); ?></p>
                </div>
                <?php endif; ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Total Amount</label>
                    <p class="text-lg font-semibold text-green-600 bg-green-50 p-2 rounded">₹<?php echo number_format($receipt['total_amount'], 2); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Receipt Items -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Receipt Items</h3>
        <span class="text-sm text-gray-500"><?php echo count($receipt['items']); ?> items</span>
    </div>
    <div class="card-body">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Item Details</th>
                        <th>Quantity</th>
                        <th>Unit Cost</th>
                        <th>Total Cost</th>
                        <th>Quality</th>
                        <th>Batch/Expiry</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($receipt['items'] as $item): ?>
                    <tr>
                        <td>
                            <div>
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($item['item_name']); ?></div>
                                <div class="text-sm text-gray-500">Code: <?php echo htmlspecialchars($item['item_code']); ?></div>
                                <div class="text-sm text-gray-500">Category: <?php echo htmlspecialchars($item['category']); ?></div>
                            </div>
                        </td>
                        <td>
                            <div class="text-sm text-gray-900"><?php echo number_format($item['quantity_received'], 2); ?></div>
                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($item['unit']); ?></div>
                        </td>
                        <td>
                            <div class="text-sm text-gray-900">₹<?php echo number_format($item['unit_cost'], 2); ?></div>
                        </td>
                        <td>
                            <div class="text-sm font-medium text-gray-900">₹<?php echo number_format($item['total_cost'], 2); ?></div>
                        </td>
                        <td>
                            <?php
                            $qualityClasses = [
                                'good' => 'bg-green-100 text-green-800',
                                'damaged' => 'bg-yellow-100 text-yellow-800',
                                'rejected' => 'bg-red-100 text-red-800'
                            ];
                            $qualityClass = $qualityClasses[$item['quality_status']] ?? 'bg-gray-100 text-gray-800';
                            ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?php echo $qualityClass; ?>">
                                <?php echo ucfirst($item['quality_status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($item['batch_number']): ?>
                                <div class="text-sm text-gray-900">Batch: <?php echo htmlspecialchars($item['batch_number']); ?></div>
                            <?php endif; ?>
                            <?php if ($item['expiry_date']): ?>
                                <div class="text-sm text-gray-500">Exp: <?php echo date('d M Y', strtotime($item['expiry_date'])); ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($item['remarks'] ?: '-'); ?></div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50">
                        <td colspan="3" class="text-right font-medium">Total Amount:</td>
                        <td class="font-bold text-green-600">₹<?php echo number_format($receipt['total_amount'], 2); ?></td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php if ($receipt['remarks']): ?>
<!-- Remarks -->
<div class="card mt-6">
    <div class="card-header">
        <h3 class="card-title">Remarks</h3>
    </div>
    <div class="card-body">
        <div class="text-sm text-gray-900 bg-gray-50 p-3 rounded">
            <?php echo nl2br(htmlspecialchars($receipt['remarks'])); ?>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function verifyReceipt(receiptId) {
    if (confirm('Are you sure you want to verify this receipt?')) {
        fetch('verify-receipt.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ receipt_id: receiptId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Receipt verified successfully!', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred while verifying the receipt.', 'error');
        });
    }
}

function printReceipt() {
    window.print();
}

// Print styles
const printStyles = `
    <style>
        @media print {
            .btn, .card-header, nav, .no-print { display: none !important; }
            .card { border: 1px solid #ddd; margin-bottom: 20px; }
            .card-body { padding: 15px; }
            body { font-size: 12px; }
            .data-table { font-size: 11px; }
            .data-table th, .data-table td { padding: 8px 4px; }
        }
    </style>
`;

document.head.insertAdjacentHTML('beforeend', printStyles);
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../includes/admin_layout.php';
?>