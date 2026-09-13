<?php
include 'include/config.php';

$res = mysqli_query($conn, "SELECT u.id FROM user_data u WHERE u.email='rickyessential49@gmail.com'");
$user = mysqli_fetch_assoc($res);
$uid = $user['id'];

$res2 = mysqli_query($conn, "SELECT * FROM user_wallet WHERE user_id='$uid'");
$wallet = mysqli_fetch_assoc($res2);

// Calculate what the balance SHOULD be:
// total_recharge - (sum of all non-refunded purchases)
$res3 = mysqli_query($conn, "SELECT COALESCE(SUM(CAST(service_price AS DECIMAL(10,2))), 0) AS total FROM active_number WHERE user_id='$uid' AND status != '3'");
$non_refunded = mysqli_fetch_assoc($res3);

$correct_balance = $wallet['total_recharge'] - $non_refunded['total'];

echo "Total Recharge: ₦{$wallet['total_recharge']}\n";
echo "Non-refunded purchases: ₦{$non_refunded['total']}\n";
echo "Current balance: ₦{$wallet['balance']}\n";
echo "Correct balance: ₦$correct_balance\n";
echo "Difference (missing money): ₦" . ($correct_balance - $wallet['balance']) . "\n\n";

// Fix it
mysqli_query($conn, "UPDATE user_wallet SET balance='$correct_balance' WHERE user_id='$uid'");
echo "Balance corrected to ₦$correct_balance\n";
