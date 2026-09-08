<?php
include __DIR__ . '/../../include/config.php';
require __DIR__ . '/../../payments/payment_credit.php';

$input = file_get_contents("php://input");
$data  = json_decode($input, true);

if (!isset($data['result']['order_id'])) exit;

$order_id = $data['result']['order_id'];
$status   = $data['result']['payment_status'];
$usd      = floatval($data['result']['amount']);

if ($status !== 'paid') exit;

// Verify signature
$sign = $_SERVER['HTTP_SIGN'] ?? '';
$expected = md5(base64_encode($input) . CRYPTOMUS_API_KEY);
if ($sign !== $expected) exit;

// USD → NGN
$rateApi = json_decode(@file_get_contents(
    "https://api.exchangerate-api.com/v4/latest/USD"
), true);

$usd_ngn = $rateApi['rates']['NGN'] ?? 1400;
$ngn = round($usd * $usd_ngn, 2);

if ($ngn <= 0) exit;

// Credit wallet
creditTransaction($order_id, $ngn);

http_response_code(200);
echo "OK";
