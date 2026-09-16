<?php
include 'include/api_cache.php';
$ch = curl_init('https://5sim.net/v1/guest/prices');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 300);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$raw = curl_exec($ch);
curl_close($ch);
if($raw){
    $json = json_decode($raw, true);
    if($json) {
        api_cache_set('5sim_guest_prices', $json);
        echo 'Cached!';
    } else {
        echo 'Invalid JSON';
    }
} else {
    echo 'Failed to fetch';
}
