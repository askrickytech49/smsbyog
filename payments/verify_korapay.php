<?php
include __DIR__ . '/../include/config.php';

$reference = $_GET['reference'] ?? '';
if (!$reference) exit('Invalid reference');

$ch = curl_init("https://api.korapay.com/merchant/api/v1/charges/$reference");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer " . KORAPAY_SECRET_KEY
    ]
]);

$response = curl_exec($ch);
curl_close($ch);

$res = json_decode($response, true);

if (!isset($res['data']['status']) || $res['data']['status'] !== 'success') {
    echo json_encode(['status' => 'pending']);
    exit;
}

$amount = floatval($res['data']['amount']);

$tx = mysqli_query($conn, "
    SELECT user_id, status FROM user_transaction
    WHERE txn_id='$reference'
    LIMIT 1
");

if (!$tx || mysqli_num_rows($tx) === 0) exit;

$row = mysqli_fetch_assoc($tx);
if ($row['status'] == '1') {
    echo json_encode(['status' => 'completed']);
    exit;
}

mysqli_query($conn, "
    UPDATE user_wallet
    SET balance = balance + $amount,
        total_recharge = total_recharge + $amount
    WHERE user_id='{$row['user_id']}'
");

mysqli_query($conn, "
    UPDATE user_transaction
    SET status='1'
    WHERE txn_id='$reference'
");

echo json_encode(['status' => 'completed']);
