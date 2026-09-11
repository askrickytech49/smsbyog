<?php
include("auth.php");

// Ensure admin is logged in
if (!isset($_SESSION['token'])) {
    if (isset($_COOKIE['remember_me'])) {
        $_SESSION['token'] = $_COOKIE['remember_me'];
    } else {
        header('Location: login.php'); exit;
    }
}

$admin_sql = mysqli_query($conn, "SELECT * FROM login_token WHERE token='" . $_SESSION['token'] . "'");
if (mysqli_num_rows($admin_sql) == 0) { header('Location: login.php'); exit; }

$admin_data  = mysqli_fetch_array($admin_sql);
$admin_user  = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM user_data WHERE id='" . $admin_data['user_id'] . "' AND status='1'"));

if (!in_array($admin_user['type'], ["admin", "super_admin"])) {
    header('Location: login.php'); exit;
}

// Validate target user_id
$target_id = (int)($_GET['user_id'] ?? 0);
if (!$target_id) { echo "Invalid ID"; exit; }

$target_sql  = mysqli_query($conn, "SELECT * FROM user_data WHERE id='$target_id'");
if (mysqli_num_rows($target_sql) == 0) { echo "User not found."; exit; }
$target_user = mysqli_fetch_assoc($target_sql);

// Get or create target user's login token
$token_sql  = mysqli_query($conn, "SELECT token FROM login_token WHERE user_id='$target_id'");
if (mysqli_num_rows($token_sql) > 0) {
    $row          = mysqli_fetch_assoc($token_sql);
    $target_token = $row['token'];
} else {
    // Create a fresh token for the target user
    $chars        = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $target_token = '';
    for ($i = 0; $i < 30; $i++) { $target_token .= $chars[rand(0, strlen($chars) - 1)]; }
    $ip           = $_SERVER['REMOTE_ADDR'];
    $conn->query("INSERT INTO login_token(user_id, token, create_date, device, browser, ip, status) VALUES ('$target_id', '$target_token', NOW(), 'Admin Ghost', 'Admin Ghost', '$ip', '1')");
}

// ─── GHOST MODE: save admin token, switch to user token ───────────────────────
$_SESSION['ghost_admin_token'] = $_SESSION['token'];   // save admin token
$_SESSION['ghost_admin_id']    = $admin_data['user_id'];
$_SESSION['ghost_user_name']   = $target_user['name'];
$_SESSION['ghost_user_email']  = $target_user['email'];
$_SESSION['token']             = $target_token;         // switch to user
// Remove admin session marker so the user side doesn't think they're admin
unset($_SESSION['admin']);
// ──────────────────────────────────────────────────────────────────────────────

// Redirect cleanly to the user's dashboard (one level up from /admin/)
header('Location: ../dashboard');
exit;