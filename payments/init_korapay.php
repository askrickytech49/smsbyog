<?php
session_start();
include __DIR__ . '/../include/config.php';
require __DIR__ . '/../class/class.control.php';

if (empty($_SESSION['token'])) {
    http_response_code(401);
    exit;
}

$amount = floatval($_POST['amount'] ?? 0);
if ($amount < 100) {
    die("Minimum funding is ₦100");
}

$wallet = new radiumsahil();
$user   = $wallet->userdata();

if (!$user) {
    die("User not found");
}

$user_id = $user['id'];
$txn_id  = "KORA_" . uniqid();

// 🔴 Create pending transaction
mysqli_query($wallet->conn, "
    INSERT INTO user_transaction
    (user_id, amount, date, type, txn_id, status)
    VALUES
    ('$user_id','$amount',NOW(),'Korapay Recharge','$txn_id','0')
");

// Convert to kobo
 

// 🔵 Initialize Korapay hosted checkout
$payload = [
    "amount" => $amount,
    "currency"  => "NGN",
    "reference" => $txn_id,
    "redirect_url" => WEBSITE_URL . "/recharge?success=1",
    "customer"  => [
        "email" => $user['email'],
        "name"  => $user['name']
    ]
];

$ch = curl_init("https://api.korapay.com/merchant/api/v1/charges/initialize");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer " . KORAPAY_SECRET_KEY,
        "Content-Type: application/json"
    ]
]);

$response = curl_exec($ch);
curl_close($ch);

$res = json_decode($response, true);

// 🧪 Debug helper (remove later)
if (!isset($res['data']['checkout_url'])) {
    file_put_contents(
        __DIR__ . '/korapay_init_error.log',
        date('Y-m-d H:i:s') . " " . json_encode($res) . PHP_EOL,
        FILE_APPEND
    );
    die("Unable to initialize payment");
}

// 🚀 Redirect user to Korapay
header("Location: " . $res['data']['checkout_url']);
exit;
