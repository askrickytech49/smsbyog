<?php
$ch = curl_init('https://5sim.net/v1/guest/products/any/any');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$raw = curl_exec($ch);
echo curl_error($ch) . "\n";
echo substr($raw, 0, 500);
