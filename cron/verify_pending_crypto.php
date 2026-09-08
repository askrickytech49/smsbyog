<?php
include 'C:/xampp/htdocs/smsbyog/include/config.php';

$q = mysqli_query($conn, "
    SELECT txn_id 
    FROM user_transaction 
    WHERE status='0' AND type='Crypto Recharge'
    ORDER BY id ASC
    LIMIT 20
");

while ($row = mysqli_fetch_assoc($q)) {
    $reference = $row['txn_id'];

    // Call your verifier internally
    $_GET['reference'] = $reference;
    include 'C:/xampp/htdocs/smsbyog/payments/verify_cryptomus.php';

    sleep(2); // be gentle to API
}
