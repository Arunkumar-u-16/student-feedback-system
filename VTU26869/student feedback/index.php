<?php
// index.php
require_once 'config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];

switch ($role) {
    case 'admin':
        header("Location: admin/dashboard.php");
        break;
    case 'faculty':
        header("Location: faculty/dashboard.php");
        break;
    case 'student':
        header("Location: student/dashboard.php");
        break;
    default:
        header("Location: login.php");
        break;
}
exit();
?>
