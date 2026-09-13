<?php
include __DIR__ . '/../include/config.php';
$api_sql = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='8'");
$api_data = mysqli_fetch_assoc($api_sql);
$api_url = $api_data['api_url'];
$api_key = $api_data['api_key'];

$url = "{$api_url}/stubs/handler_api.php?api_key={$api_key}&action=getPrices";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$raw = curl_exec($ch);
curl_close($ch);
$allPrices = json_decode($raw, true);
print_r(array_slice($allPrices, 0, 1));
