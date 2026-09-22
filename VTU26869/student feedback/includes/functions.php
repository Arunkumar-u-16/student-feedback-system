<?php
// includes/functions.php

/**
 * Sanitize output (XSS Prevention)
 */
function e($text)
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * CSRF Token Generator
 */
function csrf_token()
{
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF Token
 */
function validate_csrf($token)
{
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Format Date
 */
function format_date($date)
{
    return date('d M Y, h:i A', strtotime($date));
}

/**
 * Flash Messages
 */
function set_flash_message($type, $message)
{
    $_SESSION['flash'] = [
        'type' => $type, // success, danger, warning, info
        'message' => $message
    ];
}

function display_flash_message()
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        echo "<div class='alert alert-{$flash['type']} alert-dismissible fade show' role='alert'>
                {$flash['message']}
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
              </div>";
        unset($_SESSION['flash']);
    }
}

/**
 * Redirect with Message
 */
function redirect($path, $type = null, $message = null)
{
    if ($type && $message) {
        set_flash_message($type, $message);
    }
    header("Location: " . BASE_URL . $path);
    exit();
}
?>
