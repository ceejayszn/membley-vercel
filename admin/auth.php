<?php
if (session_status() == PHP_SESSION_NONE) {
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
