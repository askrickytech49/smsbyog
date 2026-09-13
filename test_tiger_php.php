<?php
include 'include/config.php';
$sql = mysqli_query($conn, "SELECT api_url, api_key FROM api_detail WHERE id='8'");
$row = mysqli_fetch_assoc($sql);
$url = $row['api_url'] . "/stubs/handler_api.php?api_key=" . $row['api_key'] . "&action=getPrices";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
curl_close($ch);

$data = json_decode($res, true);
echo "Countries: " . count($data) . "\n";
if (isset($data['9'])) {
    echo "Services in Argentina (9): " . count($data['9']) . "\n";
    $first = array_key_first($data['9']);
    echo "First service: $first => " . json_encode($data['9'][$first]) . "\n";
}
