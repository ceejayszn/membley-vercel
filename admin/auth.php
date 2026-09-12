<?php
if (session_status() == PHP_SESSION_NONE) {
    // Set session cookie to expire after 1 day (86400 seconds)
    $lifetime = 86400; // 1 day
    session_set_cookie_params([
        'lifetime' => $lifetime,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
    // Initialize last activity timestamp
    if (!isset($_SESSION['last_activity'])) {
        $_SESSION['last_activity'] = time();
    } else if (time() - $_SESSION['last_activity'] > $lifetime) {
        // Inactivity timeout exceeded, destroy session
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        header('Location: login.php');
        exit;
    }
    // Update last activity time on each request
    $_SESSION['last_activity'] = time();
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
