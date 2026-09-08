<?php
/**
 * auth_guard.php
 * Include at the TOP of every protected user-facing page (after session_start + config).
 *
 * What it does:
 *  - Logged-out user  → redirect to login
 *  - Cookie remember  → restore session from cookie (DB-validated)
 *  - Invalid/blocked  → destroy session + cookie, redirect to login
 *
 * Usage:
 *   session_start();
 *   include 'include/config.php';
 *   include 'include/auth_guard.php';
 */

if (!defined('AUTH_GUARD')) {
    define('AUTH_GUARD', true);

    // Helper: wipe both session and remember_me cookie cleanly
    function clear_auth_session(): void {
        $_SESSION = [];
        session_destroy();
        if (isset($_COOKIE['remember_me'])) {
            setcookie('remember_me', '', [
                'expires'  => time() - 3600,
                'path'     => '/',
                'domain'   => $_SERVER['HTTP_HOST'] ?? '',
                'secure'   => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
    }

    // 1. No session token — try cookie fallback
    if (empty($_SESSION['token'])) {
        if (!empty($_COOKIE['remember_me'])) {
            // Validate cookie token against DB before trusting it
            global $conn;
            $cookie_token = mysqli_real_escape_string($conn, $_COOKIE['remember_me']);
            $valid = mysqli_query($conn,
                "SELECT lt.user_id FROM login_token lt
                 JOIN user_data u ON lt.user_id = u.id
                 WHERE lt.token='$cookie_token' AND lt.status='1' AND u.status='1'
                 LIMIT 1"
            );
            if ($valid && mysqli_num_rows($valid) === 1) {
                $_SESSION['token'] = $_COOKIE['remember_me'];
            } else {
                // Cookie is invalid/expired
                clear_auth_session();
                redirect('login');
            }
        } else {
            // No session, no cookie
            clear_auth_session();
            redirect('login');
        }
    }

    // 2. Session token exists — DB-validate it
    global $conn;
    $session_token = mysqli_real_escape_string($conn, $_SESSION['token']);
    $token_check = mysqli_query($conn,
        "SELECT lt.user_id, u.status, u.type
         FROM login_token lt
         JOIN user_data u ON lt.user_id = u.id
         WHERE lt.token='$session_token' AND lt.status='1'
         LIMIT 1"
    );

    if (!$token_check || mysqli_num_rows($token_check) === 0) {
        // Token not found or expired
        clear_auth_session();
        redirect('login');
    }

    $token_row = mysqli_fetch_assoc($token_check);

    if ((int)$token_row['status'] !== 1) {
        // Account blocked
        clear_auth_session();
        redirect('login?msg=block');
    }
}
