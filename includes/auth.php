<?php
// =============================================
// Authentication Check
// =============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Please login to access this page.';
    header('Location: ' . $base_path . 'login.php');
    exit();
}

// Check role-based access
if (isset($required_role)) {
    if ($_SESSION['role'] !== $required_role) {
        $_SESSION['error'] = 'You do not have permission to access this page.';
        header('Location: ' . $base_path . 'index.php');
        exit();
    }
}
?>