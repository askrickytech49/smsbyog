<?php
include 'include/config.php';

// VerifySMS (USA Only)
$api_sql = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='1'");
$api_data = mysqli_fetch_assoc($api_sql);
$api_url = rtrim($api_data['api_url'], '/');
$api_key = $api_data['api_key'];
$services_url = "{$api_url}/api/services?api_key={$api_key}";

echo "--- VerifySMS (USA Only) ---\n";
echo "URL: $services_url\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $services_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
curl_close($ch);
echo substr($res, 0, 200) . "\n\n";

// DinoMMO (USA+CA)
$api_sql = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='3'");
$api_data = mysqli_fetch_assoc($api_sql);
$api_url = rtrim($api_data['api_url'], '/');
$api_key = $api_data['api_key'];
$services_url = "{$api_url}/sms-otp/services";

echo "--- DinoMMO (USA+CA) ---\n";
echo "URL: $services_url\n";
echo "KEY: $api_key\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $services_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $api_key",
    "Accept: application/json"
]);
$res = curl_exec($ch);
curl_close($ch);
echo substr($res, 0, 200) . "\n\n";

