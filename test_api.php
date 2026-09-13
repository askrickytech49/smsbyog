<?php
include 'include/config.php';

$sql4 = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='8'");
$api_data = mysqli_fetch_assoc($sql4);
$api_url = $api_data['api_url'];
$api_key = $api_data['api_key'];

// 1. Check what countries TigerSMS offers
$countries_url = "{$api_url}/stubs/handler_api.php?api_key={$api_key}&action=getCountries";
echo "Fetching: $countries_url\n\n";
$ch = curl_init($countries_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$res = curl_exec($ch);
curl_close($ch);

$countries = json_decode($res, true);
if ($countries) {
    echo "Total countries: " . count($countries) . "\n\n";
    echo "First 20 countries:\n";
    $i = 0;
    foreach ($countries as $code => $data) {
        echo "  code=$code => " . json_encode($data) . "\n";
        if (++$i >= 20) break;
    }
    
    // Check what Nigeria's code actually is
    echo "\n\nSearching for Nigeria:\n";
    foreach ($countries as $code => $data) {
        $name = '';
        if (is_array($data)) {
            $name = $data['eng'] ?? $data['name'] ?? $data['text_en'] ?? '';
        } else {
            $name = (string)$data;
        }
        if (stripos($name, 'niger') !== false) {
            echo "  code=$code => " . json_encode($data) . "\n";
        }
    }
} else {
    echo "Failed to decode response. Raw:\n";
    echo substr($res, 0, 1000) . "\n";
}

// 2. Check what's currently in the DB
echo "\n\n--- Current otp_server entries for api_id=8 ---\n";
$r = mysqli_query($conn, "SELECT * FROM otp_server WHERE api_id='8'");
while ($row = mysqli_fetch_assoc($r)) {
    echo "  id={$row['id']}, server_code={$row['server_code']}, name={$row['server_name']}\n";
}
