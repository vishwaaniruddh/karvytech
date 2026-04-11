<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Render</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">Test Page - No Layout</h1>
        <div class="bg-white shadow rounded-lg p-6">
            <p class="text-gray-700">If you can see this clearly without any purple circle, the issue is in the admin_layout.php file.</p>
            <p class="text-gray-700 mt-2">If you still see the purple circle, it's a browser cache or CSS issue.</p>
        </div>
        
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h2 class="text-lg font-semibold text-blue-900 mb-2">Debug Info:</h2>
            <ul class="list-disc list-inside text-sm text-blue-800">
                <li>Browser: Check your browser console (F12) for errors</li>
                <li>Cache: Try Ctrl+Shift+Delete to clear cache</li>
                <li>Incognito: Try opening in incognito/private mode</li>
            </ul>
        </div>
    </div>
</body>
</html>
