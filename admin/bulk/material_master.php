<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/BoqItem.php';

// Auth::requireRole(ADMIN_ROLE);

$title = 'Material Master Management';
ob_start();

$itemModel = new BoqItem();
$stats = $itemModel->getStats();
?>

<div class="min-h-screen bg-transparent pb-12">
    <!-- Header Area -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                        <div class="p-2 bg-emerald-600 rounded-lg text-white shadow-lg shadow-emerald-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        Material Technical Master
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">Bulk manage your enterprise technical catalog and BOQ items</p>
                </div>
                <div class="flex items-center gap-3">
                    <button onclick="downloadTemplate()" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gray-50 transition-all shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Download Template
                    </button>
                    <a href="../boq/items.php" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 transition-all shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        Detailed Master
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto px-4 mt-8">
        <!-- Stats Grid (Import) -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-2xl border border-gray-200 p-6 flex items-center gap-4 shadow-sm hover:shadow-md transition-shadow">
                <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                </div>
                <div>
                    <div class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">Total Items</div>
                    <div class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['total']); ?></div>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 p-6 flex items-center gap-4 shadow-sm hover:shadow-md transition-shadow">
                <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                </div>
                <div>
                    <div class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">Categories</div>
                    <div class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['categories']); ?></div>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 p-6 flex items-center gap-4 shadow-sm hover:shadow-md transition-shadow">
                <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                </div>
                <div>
                    <div class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">Serial Tracking</div>
                    <div class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['serial_required']); ?></div>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 p-6 flex items-center gap-4 shadow-sm hover:shadow-md transition-shadow">
                <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div>
                    <div class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">Active Catalog</div>
                    <div class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['active']); ?></div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left: Guidelines -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-3xl shadow-sm border border-gray-200 p-8">
                    <h3 class="font-bold text-gray-900 mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                        Batch Update Protocol
                    </h3>
                    <div class="space-y-4">
                        <div class="p-4 bg-emerald-50/50 rounded-2xl border border-emerald-100">
                             <div class="text-xs font-bold text-emerald-700 uppercase mb-1">Row Reconciliation</div>
                             <p class="text-xs text-gray-600 leading-relaxed font-medium">Use <strong>Item Code</strong> as the unique key. If we find a match, we perform an <strong>UPDATE</strong>; otherwise, a <strong>CREATE</strong>.</p>
                        </div>
                        <div class="p-4 bg-blue-50/50 rounded-2xl border border-blue-100">
                             <div class="text-xs font-bold text-blue-700 uppercase mb-1">Data Types</div>
                             <p class="text-xs text-gray-600 leading-relaxed font-medium">Serial Required expects 'Yes' or 'No'. Descriptive fields support plain text and limited HTML.</p>
                        </div>
                    </div>
                    <div class="mt-8 pt-6 border-t border-gray-100">
                        <p class="text-[11px] text-gray-400 italic">For large updates (1000+ rows), please perform operations during off-peak hours to ensure zero downtime.</p>
                    </div>
                </div>
            </div>

            <!-- Right: Processing Terminal -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-3xl shadow-sm border border-gray-200 p-10">
                    <div id="drop-zone" class="relative group border-2 border-dashed border-gray-300 rounded-3xl p-20 text-center transition-all hover:border-emerald-500 hover:bg-emerald-50/20 cursor-pointer">
                        <input type="file" id="bulk-file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept=".xlsx,.xls,.csv">
                        <div class="w-24 h-24 bg-emerald-100/50 rounded-3xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 group-hover:bg-emerald-100 transition-all duration-300">
                            <svg class="w-12 h-12 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        </div>
                        <h3 class="text-2xl font-extrabold text-gray-900 mb-2 tracking-tight">Ingest Master Data</h3>
                        <p class="text-gray-500 font-medium">Drop your material manifest here or click to choose from disk</p>
                    </div>

                    <div id="import-progress" class="hidden mt-10 p-6 bg-gray-50 rounded-2xl border border-gray-200">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                                <span id="import-status" class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.2em]">Ready for stream</span>
                            </div>
                            <span id="import-percent" class="text-sm font-black text-gray-900">0%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-1.5 overflow-hidden">
                            <div id="import-bar" class="bg-emerald-600 h-full w-0 transition-all duration-500 ease-out"></div>
                        </div>
                    </div>
                </div>

                <!-- Live Results Terminal -->
                <div id="import-results" class="hidden bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-8 py-6 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                        <h4 class="font-bold text-gray-900 flex items-center gap-2">
                             <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                             Ingestion Log
                        </h4>
                        <div id="import-summary" class="flex gap-2"></div>
                    </div>
                    <div class="overflow-x-auto max-h-[500px]">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-white sticky top-0 z-10 shadow-sm border-b">
                                <tr>
                                    <th class="px-8 py-5 text-[10px] font-bold text-gray-400 uppercase tracking-widest">SEQ</th>
                                    <th class="px-8 py-5 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Metadata</th>
                                    <th class="px-8 py-5 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Status</th>
                                    <th class="px-8 py-5 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Response</th>
                                </tr>
                            </thead>
                            <tbody id="import-list-body" class="divide-y divide-gray-50"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('bulk-file').addEventListener('change', handleUpload);

async function handleUpload() {
    const file = document.getElementById('bulk-file').files[0];
    if (!file) return;

    const progressContainer = document.getElementById('import-progress');
    const resultsTable = document.getElementById('import-results');

    progressContainer.classList.remove('hidden');
    resultsTable.classList.add('hidden');
    
    updateProgress(20, 'Mounting manifest...');

    const formData = new FormData();
    formData.append('file', file);

    try {
        const response = await fetch('api/process-bulk-materials.php', {
            method: 'POST',
            body: formData
        });
        updateProgress(70, 'Committing records to database...');
        const data = await response.json();
        
        if (data.success) {
            updateProgress(100, 'Ingestion Finalized');
            showResults(data);
        } else {
            Swal.fire('Ingestion Failed', data.message || 'System error', 'error');
            progressContainer.classList.add('hidden');
        }
    } catch (err) {
        Swal.fire('Terminal Error', 'Failed to reach ingestion endpoint', 'error');
        progressContainer.classList.add('hidden');
    }
}

function updateProgress(p, label) {
    document.getElementById('import-bar').style.width = p + '%';
    document.getElementById('import-percent').textContent = p + '%';
    document.getElementById('import-status').textContent = label;
}

function showResults(data) {
    const resultsContainer = document.getElementById('import-results');
    const listBody = document.getElementById('import-list-body');
    const summary = document.getElementById('import-summary');

    resultsContainer.classList.remove('hidden');
    document.getElementById('import-progress').classList.add('hidden');

    summary.innerHTML = `
        <span class="px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-[10px] font-black tracking-tighter ring-1 ring-emerald-100 uppercase">New: ${data.created}</span>
        <span class="px-3 py-1 bg-blue-50 text-blue-700 rounded-full text-[10px] font-black tracking-tighter ring-1 ring-blue-100 uppercase">Sync: ${data.updated}</span>
        <span class="px-3 py-1 bg-rose-50 text-rose-700 rounded-full text-[10px] font-black tracking-tighter ring-1 ring-rose-100 uppercase">ERR: ${data.failed}</span>
    `;

    listBody.innerHTML = data.rows.map(r => `
        <tr class="${r.status === 'failed' ? 'bg-rose-50/30' : 'hover:bg-gray-50/50 transition-colors'}">
            <td class="px-8 py-5 text-[10px] font-bold text-gray-400">#${String(r.row).padStart(3, '0')}</td>
            <td class="px-8 py-5">
                <div class="text-sm font-bold text-gray-900">${r.name || 'Undefined'}</div>
                <div class="text-[10px] font-bold text-gray-400 font-mono tracking-tighter">${r.code || 'NULL'}</div>
            </td>
            <td class="px-8 py-5">
                <span class="px-2.5 py-1 rounded-lg text-[9px] font-black tracking-widest uppercase ${
                    r.action === 'create' ? 'bg-emerald-100 text-emerald-700' : 
                    (r.action === 'update' ? 'bg-blue-100 text-blue-700' : 'bg-rose-100 text-rose-700')
                }">${r.action || r.status}</span>
            </td>
            <td class="px-8 py-5 text-[11px] font-medium text-gray-500">${r.message}</td>
        </tr>
    `).join('');
}

function downloadTemplate() { window.location.href = 'api/download-material-template.php'; }
</script>

<?php
$content = ob_get_clean();
if (isset($_GET['ajax'])) {
    echo $content;
} else {
    require_once __DIR__ . '/../../includes/admin_layout.php';
}
?>
