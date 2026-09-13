<?php
include 'include/config.php';

// Check the user "Ricky Essential"
$res = mysqli_query($conn, "SELECT u.*, w.balance, w.total_recharge, w.total_otp FROM user_data u LEFT JOIN user_wallet w ON u.id=w.user_id WHERE u.email='rickyessential49@gmail.com'");
$user = mysqli_fetch_assoc($res);

echo "=== User: {$user['name']} ===\n";
echo "Status: {$user['status']} (1=active, 2=blocked)\n";
echo "Balance: ₦{$user['balance']}\n";
echo "Total Recharge: ₦{$user['total_recharge']}\n";
echo "Total OTP: {$user['total_otp']}\n\n";

// Check total purchases from active_number (this is what ban.php uses)
$res2 = mysqli_query($conn, "SELECT SUM(CAST(service_price AS DECIMAL(10, 2))) AS total_purchases FROM active_number WHERE status = '1' AND user_id='{$user['id']}'");
$purchases = mysqli_fetch_assoc($res2);
echo "Total Purchases (status=1, cancelled refunds): ₦{$purchases['total_purchases']}\n";

// Check ALL purchases
$res3 = mysqli_query($conn, "SELECT SUM(CAST(service_price AS DECIMAL(10, 2))) AS total_purchases FROM active_number WHERE user_id='{$user['id']}'");
$all = mysqli_fetch_assoc($res3);
echo "Total Purchases (all statuses): ₦{$all['total_purchases']}\n\n";

// The ban condition from ban.php:
// if ($row["total_purchases"]-50 > $user_wallet['total_recharge'])
$threshold = $purchases['total_purchases'] - 50;
echo "=== BAN CHECK (ban.php logic) ===\n";
echo "Formula: total_purchases({$purchases['total_purchases']}) - 50 = {$threshold}\n";
echo "Compare: {$threshold} > total_recharge({$user['total_recharge']})?\n";
echo "Result: " . ($threshold > $user['total_recharge'] ? "YES → USER GETS BANNED!" : "NO → User is safe") . "\n";
