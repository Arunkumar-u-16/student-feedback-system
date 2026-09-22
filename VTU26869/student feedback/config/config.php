<?php
// config/config.php

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Application Constants
define('APP_NAME', 'Student Feedback System');

// Dynamic BASE_URL - Works on mobile and localhost automatically
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
define('BASE_URL', $protocol . $host . '/student%20feedback/');

// Error Reporting (Development)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CSRF Protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function check_auth($role = null)
{
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . BASE_URL . "login.php");
        exit();
    }
    if ($role && $_SESSION['role'] !== $role) {
        header("Location: " . BASE_URL . "index.php?error=unauthorized");
        exit();
    }
}
?>
