<?php
session_start();
include __DIR__ . '/include/config.php';

// Logged-in users — check type and route appropriately
if (!empty($_SESSION['token'])) {
    $tk = mysqli_real_escape_string($conn, $_SESSION['token']);
    $tkq = mysqli_query($conn, "SELECT u.type FROM login_token lt JOIN user_data u ON lt.user_id=u.id WHERE lt.token='$tk' AND lt.status='1' AND u.status='1' LIMIT 1");
    if ($tkq && mysqli_num_rows($tkq) > 0) {
        $tkr = mysqli_fetch_assoc($tkq);
        if (in_array($tkr['type'], ['admin', 'super_admin'])) {
            redirect('admin/dashboard');
        } else {
            redirect('dashboard');
        }
    }
}

// Cookie remember — validate and restore session
if (!empty($_COOKIE['remember_me'])) {
    $ck = mysqli_real_escape_string($conn, $_COOKIE['remember_me']);
    $cv = mysqli_query($conn, "SELECT u.type FROM login_token lt JOIN user_data u ON lt.user_id=u.id WHERE lt.token='$ck' AND lt.status='1' AND u.status='1' LIMIT 1");
    if ($cv && mysqli_num_rows($cv) === 1) {
        $cr = mysqli_fetch_assoc($cv);
        $_SESSION['token'] = $_COOKIE['remember_me'];
        if (in_array($cr['type'], ['admin', 'super_admin'])) {
            redirect('admin/dashboard');
        } else {
            redirect('dashboard');
        }
    }
}

// Not logged in — go to login
redirect('login');
