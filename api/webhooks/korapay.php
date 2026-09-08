
<?php
file_put_contents(
    __DIR__ . '/_PROOF.txt',
    date('Y-m-d H:i:s') . " WEBHOOK HIT\n",
    FILE_APPEND
);

include __DIR__ . '/../../include/config.php';
require __DIR__ . '/../../payments/payment_credit.php';

// Log hit
file_put_contents(
    __DIR__ . '/korapay_webhook.log',
    date('Y-m-d H:i:s') . " HIT\n",
    FILE_APPEND
);

$input = file_get_contents("php://input");
$data  = json_decode($input, true);

if (!isset($data['event']) || $data['event'] !== 'charge.success') {
    http_response_code(200);
    exit;
}

$reference = $data['data']['reference'] ?? '';
$amount    = floatval($data['data']['amount'] ?? 0);
$status    = $data['data']['status'] ?? '';

if ($status !== 'success' || !$reference || $amount <= 0) {
    exit;
}

// Credit wallet
creditTransaction($reference, $amount);

http_response_code(200);
echo "OK";
