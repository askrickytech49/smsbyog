<?php

// Load config ONCE
include 'C:/xampp/htdocs/smsbyog/include/config.php';

$log = 'C:/xampp/htdocs/smsbyog/cron/naira_cron.log';

file_put_contents($log, date('Y-m-d H:i:s') . " - CRON STARTED\n", FILE_APPEND);

// Correct filter (as seen in DB)
$q = mysqli_query($conn, "
    SELECT txn_id
    FROM user_transaction
    WHERE status = '0'
      AND type = 'Korapay Recharge'
    ORDER BY id ASC
    LIMIT 20
");

file_put_contents(
    $log,
    date('Y-m-d H:i:s') . " - FOUND " . mysqli_num_rows($q) . " PENDING KORAPAY TXNS\n",
    FILE_APPEND
);

while ($row = mysqli_fetch_assoc($q)) {

    $_GET['reference'] = $row['txn_id'];

    // Capture verifier output safely
    ob_start();
    include 'C:/xampp/htdocs/smsbyog/payments/verify_pending_naira.php';
    ob_end_clean();

    file_put_contents(
        $log,
        date('Y-m-d H:i:s') . " - VERIFIED {$row['txn_id']}\n",
        FILE_APPEND
    );

    sleep(2);
}

file_put_contents($log, date('Y-m-d H:i:s') . " - CRON FINISHED\n\n", FILE_APPEND);
