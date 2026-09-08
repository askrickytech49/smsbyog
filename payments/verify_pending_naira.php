<?php
if (!isset($conn)) {
    include __DIR__ . '/../include/config.php';
}


$reference = $_GET['reference'] ?? '';
if (!$reference) {
    echo json_encode(['status' => 'invalid']);
    return;
}

file_put_contents(__DIR__ . "/korapay_log.txt", $response . PHP_EOL, FILE_APPEND);


/**
 * Call Korapay API
 */
$ch = curl_init("https://api.korapay.com/merchant/api/v1/charges/{$reference}");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer " . KORAPAY_SECRET_KEY,
        "Content-Type: application/json"
    ],
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
curl_close($ch);

$res = json_decode($response, true);

/**
 * If not successful yet, leave it pending
 */
if (!isset($res['data']['status']) || $res['data']['status'] !== 'success') {
    echo json_encode(['status' => 'pending']);
    return;
}


$amount = floatval($res['data']['amount']);

/**
 * Fetch transaction
 */
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

/**
 * Prevent double credit
 */
if ($row['status'] == '1') {
    echo json_encode(['status' => 'completed']);
    return;
}


/**
 * Credit wallet
 */
mysqli_query($conn, "
    UPDATE user_wallet
    SET balance = balance + {$amount},
        total_recharge = total_recharge + {$amount}
    WHERE user_id='{$row['user_id']}'
");

/**
 * Mark transaction completed
 */
mysqli_query($conn, "
    UPDATE user_transaction
    SET status='1'
    WHERE txn_id='$reference'
");

echo json_encode(['status' => 'completed']);
