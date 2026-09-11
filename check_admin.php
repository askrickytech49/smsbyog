<?php
require 'include/config.php';

$email = '49rickyai@gmail.com';
$r = $conn->query("SELECT u.id, u.name, u.email, u.type, u.password, u.status, u.register_date, w.balance, w.total_recharge, w.total_otp, w.total_sms FROM user_data u LEFT JOIN user_wallet w ON u.id = w.user_id WHERE u.email = '$email'");

if ($r->num_rows > 0) {
    $d = $r->fetch_assoc();
    echo "Account found!\n";
    echo "ID: {$d['id']}\n";
    echo "Name: {$d['name']}\n";
    echo "Email: {$d['email']}\n";
    echo "Type: {$d['type']}\n";
    echo "Status: {$d['status']}\n";
    echo "Registered: {$d['register_date']}\n";
    echo "Password hash: {$d['password']}\n";
    echo "Balance: {$d['balance']}\n";
    echo "Total Recharge: {$d['total_recharge']}\n";
    echo "Total OTP: {$d['total_otp']}\n";
    echo "Total SMS: {$d['total_sms']}\n";
} else {
    echo "Account NOT FOUND!\n";
}
?>
