<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/BoqItem.php';

// Auth::requireRole(ADMIN_ROLE);

$title = 'Material Operations Hub';
ob_start();
?>

<div class="min-h-screen bg-gray-50/50 pb-12 overflow-x-hidden">
    <!-- Hub Header -->
    <div id="hub-header" class="bg-white border-b border-gray-200">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-10 text-center">
            <h1 class="text-4xl font-black text-gray-900 tracking-tight mb-2">Technical Logistics Hub</h1>
            <p class="text-gray-500 font-medium max-w-2xl mx-auto">Select an optimized module to manage material masters, requisitions, and outbound shipments through our high-performance inventory stream.</p>
        </div>
    </div>

    <!-- Active Content Header (Breadcrumbs) - Initially Hidden -->
    <div id="active-header" class="hidden bg-white border-b border-gray-200 sticky top-0 z-30 shadow-sm animate-in slide-in-from-top duration-300">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <button onclick="backToHub()" class="p-2 hover:bg-gray-100 rounded-xl transition-colors text-gray-400 hover:text-gray-900 group">
                    <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </button>
                <div class="h-6 w-px bg-gray-200"></div>
                <div id="current-module-title" class="text-sm font-black text-gray-900 uppercase tracking-widest">Module Name</div>
            </div>
            <div class="flex items-center gap-2">
                 <span class="text-[10px] font-black text-emerald-500 uppercase tracking-[0.2em] bg-emerald-50 px-3 py-1 rounded-full ring-1 ring-emerald-100">Live Stream Active</span>
                 <button onclick="refreshModule()" class="p-2 hover:bg-indigo-50 text-gray-400 hover:text-indigo-600 rounded-lg transition-all" title="Reload Module">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                 </button>
            </div>
        </div>
    </div>

    <!-- Hub Selector -->
    <div id="hub-selector" class="max-w-7xl mx-auto px-4 mt-12 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
        <!-- Card 1: Master -->
        <div onclick="openModule('material_master.php', 'Material Master')" class="group relative bg-white rounded-[2.5rem] p-10 shadow-sm border border-gray-100 hover:shadow-2xl hover:shadow-emerald-200/50 hover:-translate-y-2 transition-all cursor-pointer overflow-hidden backdrop-blur-sm">
            <div class="absolute -right-4 -top-4 w-32 h-32 bg-emerald-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-700"></div>
            <div class="relative z-10">
                <div class="w-16 h-16 bg-emerald-600 rounded-3xl flex items-center justify-center text-white shadow-lg shadow-emerald-200 mb-8 group-hover:rotate-12 transition-transform">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <h3 class="text-2xl font-black text-gray-900 mb-3 tracking-tight">Material Master</h3>
                <p class="text-sm text-gray-500 font-medium leading-relaxed">Enterprise catalog manifest with batch ingestion protocols.</p>
                <div class="mt-8 flex items-center text-emerald-600 font-black text-xs uppercase tracking-widest gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                    Initialize Stream <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </div>
            </div>
        </div>

        <!-- Card 2: Requisition -->
        <div onclick="openModule('material_request.php', 'Material Request')" class="group relative bg-white rounded-[2.5rem] p-10 shadow-sm border border-gray-100 hover:shadow-2xl hover:shadow-blue-200/50 hover:-translate-y-2 transition-all cursor-pointer overflow-hidden backdrop-blur-sm">
            <div class="absolute -right-4 -top-4 w-32 h-32 bg-blue-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-700"></div>
            <div class="relative z-10">
                <div class="w-16 h-16 bg-blue-600 rounded-3xl flex items-center justify-center text-white shadow-lg shadow-blue-200 mb-8 group-hover:rotate-12 transition-transform">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                </div>
                <h3 class="text-2xl font-black text-gray-900 mb-3 tracking-tight">Site Requisition</h3>
                <p class="text-sm text-gray-500 font-medium leading-relaxed">Direct demand initiation with BOQ template matching.</p>
                <div class="mt-8 flex items-center text-blue-600 font-black text-xs uppercase tracking-widest gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                    Create Request <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </div>
            </div>
        </div>

        <!-- Card 3: Approvals -->
        <div onclick="openModule('request_approvals.php', 'Request Approvals')" class="group relative bg-white rounded-[2.5rem] p-10 shadow-sm border border-gray-100 hover:shadow-2xl hover:shadow-indigo-200/50 hover:-translate-y-2 transition-all cursor-pointer overflow-hidden backdrop-blur-sm">
            <div class="absolute -right-4 -top-4 w-32 h-32 bg-indigo-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-700"></div>
            <div class="relative z-10">
                <div class="w-16 h-16 bg-indigo-600 rounded-3xl flex items-center justify-center text-white shadow-lg shadow-indigo-200 mb-8 group-hover:rotate-12 transition-transform">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-2xl font-black text-gray-900 mb-3 tracking-tight">Approval Hub</h3>
                <p class="text-sm text-gray-500 font-medium leading-relaxed">Multi-tier verification queue for field requisitions.</p>
                <div class="mt-8 flex items-center text-indigo-600 font-black text-xs uppercase tracking-widest gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                    Open Queue <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </div>
            </div>
        </div>

        <!-- Card 4: Dispatch -->
        <div onclick="openModule('dispatch_center.php', 'Dispatch Center')" class="group relative bg-white rounded-[2.5rem] p-10 shadow-sm border border-gray-100 hover:shadow-2xl hover:shadow-slate-200/50 hover:-translate-y-2 transition-all cursor-pointer overflow-hidden backdrop-blur-sm">
            <div class="absolute -right-4 -top-4 w-32 h-32 bg-slate-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-700"></div>
            <div class="relative z-10">
                <div class="w-16 h-16 bg-slate-900 rounded-3xl flex items-center justify-center text-white shadow-lg shadow-gray-200 mb-8 group-hover:rotate-12 transition-transform">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                </div>
                <h3 class="text-2xl font-black text-gray-900 mb-3 tracking-tight">Dispatch Center</h3>
                <p class="text-sm text-gray-500 font-medium leading-relaxed">Outbound logistics manifest and real-time tracking.</p>
                <div class="mt-8 flex items-center text-slate-900 font-black text-xs uppercase tracking-widest gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                    Initiate Ship <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- AJAX Container -->
    <div id="module-viewport" class="hidden max-w-full mx-auto px-4 mt-0 opacity-0 transition-opacity duration-300">
        <!-- Module content injected here -->
    </div>
    
    <!-- Loading Overlay -->
    <div id="loader" class="hidden fixed inset-0 bg-white/60 backdrop-blur-sm z-50 flex flex-col items-center justify-center">
        <div class="w-16 h-16 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin mb-4"></div>
        <div class="text-[10px] font-black uppercase text-indigo-600 tracking-[0.3em] animate-pulse">Syncing Cloud Node</div>
    </div>
</div>

<script>
let currentFile = '';
let currentTitle = '';

async function openModule(file, title) {
    currentFile = file;
    currentTitle = title;
    
    const loader = document.getElementById('loader');
    const hubHeader = document.getElementById('hub-header');
    const hubSelector = document.getElementById('hub-selector');
    const activeHeader = document.getElementById('active-header');
    const viewport = document.getElementById('module-viewport');
    
    loader.classList.remove('hidden');
    
    try {
        const response = await fetch(`${file}?ajax=1`);
        const html = await response.text();
        
        viewport.innerHTML = html;
        
        // Hide Hub
        hubHeader.classList.add('hidden');
        hubSelector.classList.add('hidden');
        
        // Show Content
        activeHeader.classList.remove('hidden');
        viewport.classList.remove('hidden');
        document.getElementById('current-module-title').textContent = title;
        
        // Re-execute scripts in injected HTML
        viewport.querySelectorAll('script').forEach(oldScript => {
            const newScript = document.createElement('script');
            Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
            newScript.appendChild(document.createTextNode(oldScript.innerHTML));
            oldScript.parentNode.replaceChild(newScript, oldScript);
        });

        requestAnimationFrame(() => {
            viewport.classList.add('opacity-100');
            viewport.classList.remove('opacity-0');
        });

        // Track state for back button (optional)
        window.history.pushState({ file, title }, title, `?module=${file.split('.')[0]}`);

    } catch (err) {
        console.error('Module load failed:', err);
    } finally {
        setTimeout(() => loader.classList.add('hidden'), 300);
    }
}

function backToHub() {
    const hubHeader = document.getElementById('hub-header');
    const hubSelector = document.getElementById('hub-selector');
    const activeHeader = document.getElementById('active-header');
    const viewport = document.getElementById('module-viewport');
    
    viewport.classList.remove('opacity-100');
    viewport.classList.add('opacity-0');
    
    setTimeout(() => {
        viewport.classList.add('hidden');
        activeHeader.classList.add('hidden');
        hubHeader.classList.remove('hidden');
        hubSelector.classList.remove('hidden');
        viewport.innerHTML = '';
        window.history.pushState(null, '', 'materials.php');
    }, 300);
}

function refreshModule() {
    if (currentFile) openModule(currentFile, currentTitle);
}

// Handle Browser Back
window.onpopstate = function(event) {
    if (event.state) {
        openModule(event.state.file, event.state.title);
    } else {
        backToHub();
    }
};

// Auto-load if URL has module parameter
document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const mod = params.get('module');
    if (mod) {
        const map = {
            'material_master': 'Material Master',
            'material_request': 'Material Request',
            'request_approvals': 'Request Approvals',
            'dispatch_center': 'Dispatch Center'
        };
        if (map[mod]) openModule(`${mod}.php`, map[mod]);
    }
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../includes/admin_layout.php';
?>
