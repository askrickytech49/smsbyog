<?php
include 'include/config.php';
$sql4 = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='8'");
$api_data = mysqli_fetch_assoc($sql4);
$api_url = $api_data['api_url'];
$api_key = $api_data['api_key'];

$url = "{$api_url}/stubs/handler_api.php?api_key={$api_key}&action=getPrices";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$raw = curl_exec($ch);
curl_close($ch);
$data = json_decode($raw, true);

echo "Total countries in prices: " . count($data) . "\n";
// Print structure of first country
foreach ($data as $country => $services) {
    echo "Country: $country\n";
    echo "Total services: " . count($services) . "\n";
    break;
}
