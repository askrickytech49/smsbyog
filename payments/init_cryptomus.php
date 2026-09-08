<?php
session_start();
include 'C:/xampp/htdocs/smsbyog/include/config.php';
require __DIR__ . '/../class/class.control.php';

// --------------------
// AUTH CHECK
// --------------------
if (empty($_SESSION['token'])) {
    http_response_code(401);
    exit;
}

// --------------------
// VALIDATE AMOUNT (USD)
// --------------------
$usd = floatval($_POST['amount'] ?? 0);
if ($usd < 1) {
    die("Minimum funding is $1");
}

// --------------------
// USER DATA
// --------------------
$wallet  = new radiumsahil();
$user    = $wallet->userdata();
$user_id = $user['id'];

// --------------------
// GENERATE TRANSACTION ID
// --------------------
$txn_id = "CRYPTO_" . uniqid();

// --------------------
// CREATE PENDING TRANSACTION
// amount stays 0 until verified
// --------------------
mysqli_query($wallet->conn, "
    INSERT INTO user_transaction
    (user_id, amount, date, type, txn_id, status)
    VALUES
    ('$user_id','0',NOW(),'Crypto Recharge','$txn_id','0')
");

// --------------------
// CREATE CRYPTOMUS PAYMENT
// --------------------
$data = [
    "amount"   => (string)$usd,
    "currency" => "USD",
    "order_id" => $txn_id,

    // These URLs are OPTIONAL but safe to keep
    // Webhooks are ignored in your setup
    "callback_url" => WEBSITE_URL . "/api/webhooks/cryptomus.php",
    "success_url"  => WEBSITE_URL . "/recharge?crypto_pending=1&crypto_ref={$txn_id}",
    "fail_url"     => WEBSITE_URL . "/recharge?failed=1"
];

// Sign request
$encoded = base64_encode(json_encode($data));
$sign    = md5($encoded . CRYPTOMUS_API_KEY);

// Send request
$ch = curl_init("https://api.cryptomus.com/v1/payment");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($data),
    CURLOPT_HTTPHEADER     => [
        "merchant: " . CRYPTOMUS_MERCHANT_ID,
        "sign: " . $sign,
        "Content-Type: application/json"
    ]
]);

$response = curl_exec($ch);
curl_close($ch);

$res = json_decode($response, true);

// --------------------
// VALIDATE RESPONSE
// --------------------
if (!isset($res['result']['url'])) {
    die("Unable to create crypto invoice");
}

$payment_url = $res['result']['url'];

// --------------------
// 🔥 FINAL BEHAVIOR
// Open Cryptomus in NEW TAB
// Keep user on dashboard
// --------------------
// --------------------
// RETURN PAYMENT URL AS JSON
// --------------------
header('Content-Type: application/json');
echo json_encode([
    'status' => 'ok',
    'payment_url' => $payment_url,
    'reference' => $txn_id
]);
exit;

