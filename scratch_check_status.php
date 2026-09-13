<?php
include 'include/config.php';

$res = mysqli_query($conn, "SELECT u.id FROM user_data u WHERE u.email='rickyessential49@gmail.com'");
$user = mysqli_fetch_assoc($res);
$uid = $user['id'];

// Count records by status
$res1 = mysqli_query($conn, "SELECT status, COUNT(*) as cnt FROM active_number WHERE user_id='$uid' GROUP BY status");
echo "=== Records for rickyessential49@gmail.com (user_id=$uid) ===\n";
while ($r = mysqli_fetch_assoc($res1)) {
    $label = $r['status'] == '1' ? 'Completed (shows in Number History)' : 
            ($r['status'] == '2' ? 'Active/Waiting' : 
            ($r['status'] == '3' ? 'Cancelled/Refunded (shows in Cancelled Numbers)' : 'Unknown'));
    echo "Status {$r['status']} ($label): {$r['cnt']} records\n";
}

// Check if cancelled numbers page query would find them
$res2 = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM active_number a LEFT JOIN user_data u ON a.user_id=u.id WHERE a.status='3' AND u.email='rickyessential49@gmail.com'");
$r2 = mysqli_fetch_assoc($res2);
echo "\nCancelled Numbers query would find: {$r2['cnt']} records for your email\n";

// Check total cancelled numbers in the system
$res3 = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM active_number WHERE status='3'");
$r3 = mysqli_fetch_assoc($res3);
echo "Total cancelled numbers in entire system: {$r3['cnt']}\n";
