<?php
$ch = curl_init("https://5sim.net/v1/guest/prices");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$raw = curl_exec($ch);
echo "Size: " . strlen($raw) . "\n";
$data = json_decode($raw, true);
echo "Countries: " . count($data) . "\n";
if (isset($data['argentina'])) {
    echo "Argentina services: " . count($data['argentina']) . "\n";
}
