<?php
include 'C:/xampp/htdocs/smsbyog/include/config.php';

$reference = $_GET['reference'] ?? '';
if (!$reference) {
    echo json_encode(['status' => 'invalid']);
    exit;
}

// Call Cryptomus API to get order info
$payload = json_encode([
    "order_id" => $reference
]);

$sign = md5(base64_encode($payload) . CRYPTOMUS_API_KEY);

$ch = curl_init("https://api.cryptomus.com/v1/payment/info");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "merchant: " . CRYPTOMUS_MERCHANT_ID,
        "sign: " . $sign
    ]
]);

$response = curl_exec($ch);
curl_close($ch);

$res = json_decode($response, true);

if (
    !isset($res['result']['payment_status']) ||
    $res['result']['payment_status'] !== 'paid'
) {
    echo json_encode(['status' => 'pending']);
    exit;
}

// Convert USD → NGN
$usd = floatval($res['result']['amount']);
$rateApi = json_decode(@file_get_contents(
    "https://api.exchangerate-api.com/v4/latest/USD"
), true);

$usd_ngn = $rateApi['rates']['NGN'] ?? 1400;
$ngn = round($usd * $usd_ngn, 2);

// Fetch transaction
$tx = mysqli_query($conn, "
    SELECT user_id, status 
    FROM user_transaction 
    WHERE txn_id='$reference'
    LIMIT 1
");

if (!$tx || mysqli_num_rows($tx) === 0) {
    echo json_encode(['status' => 'not_found']);
    exit;
}

$row = mysqli_fetch_assoc($tx);
if ($row['status'] == '1') {
    echo json_encode(['status' => 'completed']);
    exit;
}

// Credit wallet
mysqli_query($conn, "
    UPDATE user_wallet
    SET balance = balance + $ngn,
        total_recharge = total_recharge + $ngn
    WHERE user_id='{$row['user_id']}'
");

mysqli_query($conn, "
    UPDATE user_transaction
    SET amount='$ngn', status='1'
    WHERE txn_id='$reference'
");

echo json_encode(['status' => 'completed']);
