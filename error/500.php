<?php
http_response_code(500);
$title = 'Server Error - ' . (defined('APP_NAME') ? APP_NAME : 'Site Installation Management');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full bg-white shadow-lg rounded-lg p-6 text-center">
            <div class="text-red-500 text-6xl mb-4">⚠️</div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Server Error</h1>
            <p class="text-gray-600 mb-4">
                Something went wrong on our end. Please try again later.
            </p>
            <div class="space-y-2">
                <a href="/project" class="block w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition duration-200">
                    Return to Home
                </a>
                <button onclick="history.back()" class="block w-full bg-gray-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition duration-200">
                    Go Back
                </button>
            </div>
        </div>
    </div>
</body>
</html>