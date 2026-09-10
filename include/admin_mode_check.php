<?php
/**
 * admin_mode_check.php
 * Include AFTER auth.php on every admin page.
 * Checks dev_payment_mode and redirects to paydev screen if active.
 * The dev.php page is always exempt.
 */
if (!defined('ADMIN_MODE_CHECK')) {
    define('ADMIN_MODE_CHECK', true);

    $current_script = basename($_SERVER['PHP_SELF']);
    $exempt_admin = ['paydev.php', 'login.php'];

    if (!in_array($current_script, $exempt_admin)) {
        global $conn;
        $mode_row = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT dev_payment_mode FROM settings WHERE id='1' LIMIT 1"
        ));
        $paydev_on = (int)($mode_row['dev_payment_mode'] ?? 0);

        if ($paydev_on) {
            // Determine correct path to paydev.php
            $admin_base = rtrim(dirname($_SERVER['PHP_SELF']), '/');
            header('Location: ' . $admin_base . '/paydev.php');
            exit;
        }
    }
}
