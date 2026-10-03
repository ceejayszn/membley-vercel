<?php
require_once 'auth.php';

// Clear the persistent cookie
clear_persistent_admin_cookie();

// Destroy session completely
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}
$_SESSION = [];
session_destroy();

// Redirect to login
header('Location: login.php');
exit;
