<?php
function tigerSmsHealth() {
    $start = microtime(true);
    
    $cache_file = __DIR__ . '/tiger_health_cache.json';
    $cache_ttl = 60; // Cache for 60 seconds
    
    if (file_exists($cache_file) && (time() - filemtime($cache_file) < $cache_ttl)) {
        $data = json_decode(file_get_contents($cache_file), true);
        if ($data) return $data;
    }

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => "https://api.tiger-sms.com/stubs/handler_api.php?action=getBalance&api_key=" . TIGER_SMS_API_KEY,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 2
    ]);

    $response = curl_exec($ch);
    $error    = curl_errno($ch);
    curl_close($ch);

    $latency = round((microtime(true) - $start) * 1000);

    if ($error || !$response || stripos($response, 'ACCESS_BALANCE') === false) {
        $result = [
            'status'  => 'down',
            'uptime'  => 'N/A',
            'latency' => $latency
        ];
        file_put_contents($cache_file, json_encode($result));
        return $result;
    }

    $result = [
        'status'  => 'up',
        'uptime'  => '99.9%',
        'latency' => $latency
    ];

    file_put_contents($cache_file, json_encode($result));
    return $result;
}
