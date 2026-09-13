<?php
include __DIR__ . '/../include/config.php';
$_GET['token'] = 'DUMMY'; // Bypass for test
$_GET['service'] = 'zf'; // test service code for Dino

// We have to mimic token check if it fails, or just include the logic directly.
$api_sql = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='3'");
$api_data = mysqli_fetch_assoc($api_sql);
$api_url = rtrim($api_data['api_url'], '/');
$api_key = $api_data['api_key'];

$services_url = "{$api_url}/sms-otp/services";
$ch = curl_init($services_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "X-API-Key: $api_key",
    "Accept: application/json",
    "Content-Type: application/json"
]);
$raw = curl_exec($ch);
curl_close($ch);
$api_prices = json_decode($raw, true);

$base_cost = 0;
if (is_array($api_prices)) {
    foreach ($api_prices as $details) {
        if (($details['service_code'] ?? '') === 'zf') {
            $base_cost = (float)($details['price_usd'] ?? 0);
            print_r($details);
            break;
        }
    }
}
echo "Base cost for zf: $base_cost\n";
