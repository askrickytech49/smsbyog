<?php
include 'include/config.php';

// Fix the user's total_otp
$res = mysqli_query($conn, "SELECT u.id FROM user_data u WHERE u.email='rickyessential49@gmail.com'");
$user = mysqli_fetch_assoc($res);
$uid = $user['id'];

// Count actual OTP received
$res2 = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM active_number WHERE user_id='$uid'");
$cnt = mysqli_fetch_assoc($res2);
echo "Actual number purchases: {$cnt['cnt']}\n";

// Fix: set total_otp to 0 minimum
mysqli_query($conn, "UPDATE user_wallet SET total_otp = GREATEST(total_otp, 0) WHERE user_id='$uid'");

// Unblock the user
mysqli_query($conn, "UPDATE user_data SET status='1' WHERE id='$uid'");

echo "User unblocked and total_otp fixed.\n";

// Verify
$res3 = mysqli_query($conn, "SELECT * FROM user_wallet WHERE user_id='$uid'");
$w = mysqli_fetch_assoc($res3);
echo "New total_otp: {$w['total_otp']}\n";
