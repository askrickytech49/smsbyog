<?php
// Log out admin session and redirect to user login page
session_start();
$_SESSION = [];
session_destroy();

// Clear remember_me cookie
if (isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

// Redirect to user login
header('Location: ../login');
exit;
