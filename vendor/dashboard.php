<?php
// Redirect to the main vendor dashboard
require_once '../config/auth.php'; // This includes constants.php
header('Location: ' . url('vendor/'));
exit;
?>