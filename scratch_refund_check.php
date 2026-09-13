<?php
include 'include/config.php';

// Get user ID
$res = mysqli_query($conn, "SELECT u.id FROM user_data u WHERE u.email='rickyessential49@gmail.com'");
$user = mysqli_fetch_assoc($res);
$uid = $user['id'];

// Get wallet info
$res2 = mysqli_query($conn, "SELECT * FROM user_wallet WHERE user_id='$uid'");
$wallet = mysqli_fetch_assoc($res2);
echo "=== Current Wallet ===\n";
echo "Balance: ₦{$wallet['balance']}\n";
echo "Total Recharge: ₦{$wallet['total_recharge']}\n";
echo "Total OTP: {$wallet['total_otp']}\n\n";

// Get all active_number records
echo "=== All Number Records ===\n";
echo str_pad("ID", 6) . str_pad("Service", 25) . str_pad("Price", 12) . str_pad("Status", 10) . str_pad("Active_St", 12) . str_pad("SMS", 6) . "Date\n";
echo str_repeat("-", 100) . "\n";

$res3 = mysqli_query($conn, "SELECT * FROM active_number WHERE user_id='$uid' ORDER BY id DESC");
$total_bought = 0;
$total_refunded = 0;
$total_not_refunded = 0;
while ($row = mysqli_fetch_assoc($res3)) {
    $sms = empty($row['sms_text']) ? '—' : 'YES';
    // status: 1=cancelled(no SMS received), 2=active/waiting, 3=refunded/completed
    // active_status: 1=inactive/done, 2=active
    $status_label = '';
    if ($row['status'] == '1') { $status_label = 'Cancelled'; }
    elseif ($row['status'] == '2') { $status_label = 'Active'; }
    elseif ($row['status'] == '3') { $status_label = 'Refunded'; }
    else { $status_label = "Unk({$row['status']})"; }
    
    $active_label = $row['active_status'] == '1' ? 'Done' : 'Active';
    
    echo str_pad($row['id'], 6) . str_pad(substr($row['service_name'],0,23), 25) . str_pad("₦{$row['service_price']}", 12) . str_pad($status_label, 10) . str_pad($active_label, 12) . str_pad($sms, 6) . $row['buy_time'] . "\n";
    
    $total_bought += (float)$row['service_price'];
    if ($row['status'] == '3') {
        $total_refunded += (float)$row['service_price'];
    }
    if ($row['status'] == '1' && empty($row['sms_text'])) {
        $total_not_refunded += (float)$row['service_price'];
    }
}

echo "\n=== SUMMARY ===\n";
echo "Total spent on numbers: ₦$total_bought\n";
echo "Total refunded (status=3): ₦$total_refunded\n";
echo "Cancelled but NOT refunded (status=1, no SMS): ₦$total_not_refunded\n";
echo "Expected balance: ₦" . ($wallet['total_recharge'] - $total_bought + $total_refunded) . "\n";
echo "Actual balance: ₦{$wallet['balance']}\n";
