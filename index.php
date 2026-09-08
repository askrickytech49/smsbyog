<?php
session_start();
include __DIR__ . '/include/config.php';

// Logged-in users go straight to dashboard
if (!empty($_SESSION['token'])) {
    redirect('dashboard');
}

// Cookie remember — validate and restore session
if (!empty($_COOKIE['remember_me'])) {
    $ck = mysqli_real_escape_string($conn, $_COOKIE['remember_me']);
    $cv = mysqli_query($conn, "SELECT lt.user_id FROM login_token lt JOIN user_data u ON lt.user_id=u.id WHERE lt.token='$ck' AND lt.status='1' AND u.status='1' LIMIT 1");
    if ($cv && mysqli_num_rows($cv) === 1) {
        $_SESSION['token'] = $_COOKIE['remember_me'];
        redirect('dashboard');
    }
}

// Not logged in — go to login
redirect('login');
