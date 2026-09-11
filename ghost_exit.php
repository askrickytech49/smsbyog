<?php
session_start();

// Only valid if we are actually in ghost mode
if (isset($_SESSION['ghost_admin_token'])) {
    // Restore the admin's original session token
    $_SESSION['token']  = $_SESSION['ghost_admin_token'];
    $_SESSION['admin']  = $_SESSION['ghost_admin_token'];

    // Clean up ghost vars
    unset($_SESSION['ghost_admin_token']);
    unset($_SESSION['ghost_admin_id']);
    unset($_SESSION['ghost_user_name']);
    unset($_SESSION['ghost_user_email']);
}

// Always redirect to admin dashboard
header('Location: admin/dashboard');
exit;
