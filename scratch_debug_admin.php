<?php
include 'include/config.php';

// Check the user
$res = mysqli_query($conn, "SELECT * FROM user_data WHERE email='rickyessential49@gmail.com'");
$user = mysqli_fetch_assoc($res);
echo "User ID: {$user['id']}\n";
echo "User table: user_data\n\n";

// Check what user_id is stored in active_number for this user
$res2 = mysqli_query($conn, "SELECT user_id, COUNT(*) as cnt FROM active_number WHERE user_id='{$user['id']}' GROUP BY user_id");
$r2 = mysqli_fetch_assoc($res2);
echo "Records in active_number with user_id={$user['id']}: " . ($r2['cnt'] ?? 0) . "\n\n";

// Check the JOIN - this is what the admin page does
$res3 = mysqli_query($conn, "SELECT a.user_id, u.email, u.id as uid FROM active_number a LEFT JOIN user_data u ON a.user_id=u.id WHERE a.user_id='{$user['id']}' AND a.status='3' LIMIT 3");
echo "JOIN results (what admin page sees):\n";
while ($r = mysqli_fetch_assoc($res3)) {
    echo "  active_number.user_id={$r['user_id']}, user_data.id=" . ($r['uid'] ?? 'NULL') . ", email=" . ($r['email'] ?? 'NULL') . "\n";
}

// Check if maybe the user is in a different table
echo "\n--- Checking all tables for this email ---\n";
$tables = ['user_data'];
foreach ($tables as $t) {
    $res4 = mysqli_query($conn, "SELECT id FROM $t WHERE email='rickyessential49@gmail.com'");
    if ($res4 && $r4 = mysqli_fetch_assoc($res4)) {
        echo "Found in $t with id={$r4['id']}\n";
    }
}

// Most importantly - check if the admin page query returns these records at ALL
$res5 = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM active_number a LEFT JOIN user_data u ON a.user_id=u.id WHERE a.status='3'");
$r5 = mysqli_fetch_assoc($res5);
echo "\nTotal records from admin cancelled page query: {$r5['cnt']}\n";

// Check last 5 records by ID to see if user's records are at the end
$res6 = mysqli_query($conn, "SELECT a.id, a.user_id, u.email, a.service_name, a.buy_time FROM active_number a LEFT JOIN user_data u ON a.user_id=u.id WHERE a.status='3' ORDER BY a.id DESC LIMIT 5");
echo "\nNewest 5 cancelled records:\n";
while ($r = mysqli_fetch_assoc($res6)) {
    echo "  id={$r['id']} user={$r['email']} service={$r['service_name']} time={$r['buy_time']}\n";
}
