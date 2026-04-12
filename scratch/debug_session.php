<?php
session_start();
require_once __DIR__ . '/../config/auth.php';
echo "Session Data:\n";
print_r($_SESSION);
echo "\nUser Role: " . Auth::getRole() . "\n";
echo "Is Vendor: " . (Auth::isVendor() ? 'Yes' : 'No') . "\n";
echo "Is Admin: " . (Auth::isAdmin() ? 'Yes' : 'No') . "\n";
