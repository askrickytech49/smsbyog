<?php
/**
 * mode_check.php
 * Include AFTER config.php on every user-facing page.
 * Checks maintenance_mode and redirects if active.
 * Dev page (/dev) is always exempt.
 */
if (!defined('MODE_CHECK')) {
    define('MODE_CHECK', true);

    $current_script = basename($_SERVER['PHP_SELF']);

    // Pages always exempt from maintenance/paydev
    $exempt = ['dev.php', 'maintenance.php'];

    if (!in_array($current_script, $exempt)) {

        // Reload fresh settings (in case $site_data is stale)
        global $conn;
        $mode_row = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT maintenance_mode, dev_payment_mode FROM settings WHERE id='1' LIMIT 1"
        ));

        $maintenance_on = (int)($mode_row['maintenance_mode'] ?? 0);

        // If maintenance mode ON — redirect all users to maintenance page
        if ($maintenance_on) {
            header('Location: ' . $base_path . '/maintenance');
            exit;
        }
    }
}
