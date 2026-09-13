<?php
$service = 'whatsapp';
$url = "https://5sim.net/v1/guest/prices?product=" . urlencode($service);
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$raw = curl_exec($ch);
$data = json_decode($raw, true);

print_r(array_keys($data));
$firstKey = array_keys($data)[0];
print_r(array_keys($data[$firstKey]));
if (isset($data[$firstKey]['whatsapp'])) {
    echo "Has whatsapp key\n";
} else {
    echo "No whatsapp key\n";
    $firstSub = array_keys($data[$firstKey])[0];
    echo "First sub key: $firstSub\n";
    print_r($data[$firstKey][$firstSub]);
}
