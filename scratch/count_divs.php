<?php
$content = file_get_contents('c:\xampp\htdocs\project\shared\edit-survey2.php');
// Remove comments to avoid false positives
$content = preg_replace('/<!--.*?-->/s', '', $content);
$opens = substr_count($content, '<div');
$closes = substr_count($content, '</div');
echo "Opens: $opens, Closes: $closes\n";
