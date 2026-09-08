<?php
function tigerSmsHealth() {
    $start = microtime(true);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => "https://api.tiger-sms.com/stubs/handler_api.php?action=getBalance&api_key=" . TIGER_SMS_API_KEY,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10
    ]);

    $response = curl_exec($ch);
    $error    = curl_errno($ch);
    curl_close($ch);

    $latency = round((microtime(true) - $start) * 1000);

    if ($error || !$response || stripos($response, 'ACCESS_BALANCE') === false) {
        return [
            'status'  => 'down',
            'uptime'  => 'N/A',
            'latency' => $latency
        ];
    }

    return [
        'status'  => 'up',
        'uptime'  => '99.9%',
        'latency' => $latency
    ];
}
