<?php
if (session_status() == PHP_SESSION_NONE) {
    // Keep session alive for 30 days (2592000 seconds) without annoying auto-logouts
    $lifetime = 2592000;
    session_set_cookie_params([
        'lifetime' => $lifetime,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

function check_auth() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: login.php');
        exit;
    }
}

function get_logged_in_user() {
    return isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'Admin';
}
