<?php
$service = 'whatsapp';
$url = "https://5sim.net/v1/guest/prices?product=" . urlencode($service);
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$raw = curl_exec($ch);
curl_close($ch);
$pricesData = $raw ? json_decode($raw, true) : [];
print_r(array_slice($pricesData, 0, 1));
