<?php
$ch = curl_init('https://5sim.net/v1/guest/prices');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$raw = curl_exec($ch);
echo "Error: " . curl_error($ch) . "\n";
echo "Length: " . strlen($raw) . "\n";
