<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';

// Auth::requireRole(ADMIN_ROLE);

$title = 'Bulk Image Repository';
ob_start();
?>

<div class="min-h-screen bg-gray-50 pb-12">
    <!-- Header Area -->
    <div class="bg-white border-b border-gray-200 sticky top-0 z-20">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                        <div class="p-2 bg-pink-600 rounded-lg text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h14a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        Image Repository (Bulk Import)
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">Upload images to get reference URLs for your Excel/CSV imports</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="surveys.php" class="inline-flex items-center px-4 py-2 bg-gray-900 text-white text-sm font-bold rounded-xl hover:bg-black transition-all shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Back to Surveys
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto px-4 mt-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Left: Uploader -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                    <h3 class="font-bold text-gray-900 mb-4">Upload New Images</h3>
                    <div id="drop-zone" class="border-2 border-dashed border-gray-300 rounded-2xl p-8 text-center hover:border-pink-400 hover:bg-pink-50 transition-all cursor-pointer relative">
                        <input type="file" id="image-files" multiple accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        <svg class="w-12 h-12 text-pink-200 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <p class="text-xs text-gray-400 font-bold">DRAG IMAGES HERE</p>
                        <p class="text-[10px] text-gray-400 mt-1">Multiple selection supported</p>
                    </div>

                    <div id="upload-progress-container" class="hidden mt-6">
                        <div class="flex justify-between mb-1">
                            <span class="text-[10px] font-bold text-pink-600 uppercase" id="progress-status">Uploading...</span>
                            <span class="text-[10px] font-bold text-gray-900" id="progress-percent">0%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-1.5 overflow-hidden">
                            <div id="progress-bar" class="bg-pink-600 h-full w-0 transition-all duration-300"></div>
                        </div>
                    </div>

                    <div class="mt-8 p-4 bg-amber-50 rounded-xl border border-amber-100 italic">
                        <div class="flex gap-2">
                             <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                             <p class="text-[11px] text-amber-800 leading-relaxed">
                                 <strong>Temporary Storage:</strong> These images are stored in the <code>tmp_images</code> folder for reference. They will be copied to survey folders during bulk import.
                             </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Gallery -->
            <div class="lg:col-span-3">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden min-h-[600px] flex flex-col">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-gray-900">Image Library</h3>
                            <span id="img-count" class="px-2 py-0.5 bg-gray-200 text-gray-600 text-[10px] font-bold rounded-full">0</span>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="relative">
                                <input type="text" id="filter-gallery" placeholder="Filter by name..." class="bg-white border border-gray-200 rounded-lg px-3 py-1 text-xs outline-none focus:ring-1 focus:ring-pink-500 w-48">
                            </div>
                            <button onclick="loadImages()" class="text-xs text-indigo-600 font-bold hover:underline">Refresh</button>
                        </div>
                    </div>
                    <div id="image-gallery" class="p-6 grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6 flex-1 content-start">
                        <!-- Loaded via JS -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let allPhotos = [];

document.addEventListener('DOMContentLoaded', () => {
    loadImages();
    document.getElementById('image-files').addEventListener('change', handleUpload);
    document.getElementById('filter-gallery').addEventListener('input', e => {
        const term = e.target.value.toLowerCase();
        renderGallery(allPhotos.filter(img => img.filename.toLowerCase().includes(term)));
    });
});

async function handleUpload() {
    const files = document.getElementById('image-files').files;
    if (files.length === 0) return;

    const container = document.getElementById('upload-progress-container');
    const bar = document.getElementById('progress-bar');
    const percentTxt = document.getElementById('progress-percent');
    const statusTxt = document.getElementById('progress-status');

    container.classList.remove('hidden');
    
    let uploadedCount = 0;
    const total = files.length;

    for (let i = 0; i < total; i++) {
        const file = files[i];
        statusTxt.textContent = `Uploading ${i+1}/${total}: ${file.name}`;
        
        const formData = new FormData();
        formData.append('image', file);
        
        try {
            const response = await fetch('api/upload-bulk-image.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.success) {
                uploadedCount++;
            }
        } catch (err) { console.error(err); }

        const percent = Math.round(((i + 1) / total) * 100);
        bar.style.width = percent + '%';
        percentTxt.textContent = percent + '%';
    }

    statusTxt.textContent = 'Upload Complete!';
    setTimeout(() => {
        container.classList.add('hidden');
        loadImages();
    }, 1500);
}

async function loadImages() {
    try {
        const response = await fetch('api/get-bulk-images.php');
        const data = await response.json();
        if (data.success) {
            allPhotos = data.images;
            document.getElementById('img-count').textContent = allPhotos.length;
            renderGallery(allPhotos);
        }
    } catch (err) { console.error(err); }
}

function renderGallery(images) {
    const gallery = document.getElementById('image-gallery');
    if (!images || images.length === 0) {
        gallery.innerHTML = '<div class="col-span-full text-center py-20 text-gray-400 italic">No images found.</div>';
        return;
    }
    
    gallery.innerHTML = images.map(img => `
        <div class="group relative bg-gray-50 rounded-xl overflow-hidden border border-gray-100 aspect-square shadow-sm transition-all hover:shadow-md hover:border-pink-200">
            <img src="../../${img.path}" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center p-3 gap-2">
                <button onclick="copyToClipboard('${img.url}')" class="w-full py-1.5 bg-white text-black text-[10px] font-bold rounded-lg hover:bg-gray-100 transition-colors">Copy URL</button>
                <button onclick="deleteImage('${img.filename}')" class="w-full py-1.5 bg-rose-600 text-white text-[10px] font-bold rounded-lg hover:bg-rose-700 transition-colors">Delete</button>
                <div class="text-[8px] text-white font-mono break-all text-center px-1">${img.filename}</div>
            </div>
        </div>
    `).join('');
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 1500,
            timerProgressBar: true
        });
        Toast.fire({ icon: 'success', title: 'Image URL copied to clipboard' });
    });
}

async function deleteImage(filename) {
    const result = await Swal.fire({
        title: 'Delete Image?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        confirmButtonText: 'Yes, remove it'
    });

    if (result.isConfirmed) {
        try {
            const response = await fetch('api/delete-bulk-image.php', {
                method: 'POST',
                body: JSON.stringify({ filename }),
                headers: { 'Content-Type': 'application/json' }
            });
            const data = await response.json();
            if (data.success) { loadImages(); }
        } catch (err) {}
    }
}
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../includes/admin_layout.php';
?>
