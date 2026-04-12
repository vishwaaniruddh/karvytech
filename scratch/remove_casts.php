<?php
$file = __DIR__ . '/../models/Inventory.php';
$content = file_get_contents($file);

// Replace CAST(column AS CHAR) with column
// We use a regex that handles potential spaces
$pattern = '/CAST\(\s*([^ ]+)\s+AS\s+CHAR\s*\)/i';
$newContent = preg_replace($pattern, '$1', $content);

if ($newContent !== null && $newContent !== $content) {
    file_put_contents($file, $newContent);
    echo "Successfully removed CAST calls from Inventory.php\n";
} else {
    echo "No CAST calls found or error occurred.\n";
}
