<?php
header('Content-Type: text/plain');

// ================= CONFIG =================
$TRX  = 400;
$USDT = 1400;

// CoinGecko API
$apiUrl = 'https://api.coingecko.com/api/v3/simple/price?ids=tron,tether&vs_currencies=ngn';

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 6,
    CURLOPT_SSL_VERIFYPEER => false
]);

$response = curl_exec($ch);
curl_close($ch);

if ($response) {
    $data = json_decode($response, true);

    if (isset($data['tron']['ngn'])) {
        $TRX = round($data['tron']['ngn'], 2);
    }
    if (isset($data['tether']['ngn'])) {
        $USDT = round($data['tether']['ngn'], 2);
    }
}

// 🔥 RETURN EVERYTHING THE JS COULD EVER ASK FOR
echo json_encode([
    "code" => "200000",
    "data" => [
        // original kucoin-style
        "TRX"  => $TRX,
        "USDT" => $USDT,

        // fallback aliases (THIS FIXES ₦undefined)
        "NGN"   => $TRX,
        "rate"  => $TRX,
        "price" => $TRX
    ],
    "currency" => "NGN"
]);
