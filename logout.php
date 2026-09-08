<?php
session_start();
include __DIR__ . '/include/config.php';

/* Unset all session variables */
$_SESSION = [];

/* Destroy session */
session_destroy();

/* Properly delete remember_me cookie */
if (isset($_COOKIE['remember_me'])) {
    setcookie(
        'remember_me',
        '',
        [
            'expires'  => time() - 3600,
            'path'     => '/',
            'domain'   => $_SERVER['HTTP_HOST'],
            'secure'   => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax'
        ]
    );
}

/* Redirect to login */
redirect('login');
