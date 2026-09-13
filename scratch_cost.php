<?php
$ch = curl_init('https://5sim.net/v1/guest/prices?product=whatsapp');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$raw = curl_exec($ch);
$prices = json_decode($raw, true);

$firstCountry = array_key_first($prices);
$firstProduct = array_key_first($prices[$firstCountry]);
$firstOp = array_key_first($prices[$firstCountry][$firstProduct]);

$cost = $prices[$firstCountry][$firstProduct][$firstOp]['cost'];
echo "Cost for $firstCountry / $firstOp: $cost\n";
