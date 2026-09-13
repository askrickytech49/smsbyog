<?php
$url1 = 'https://5sim.net/v1/guest/prices?country=any';
$url2 = 'https://5sim.net/v1/guest/prices';

function test_url($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $raw = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($raw, true);
    
    echo "Testing URL: $url\n";
    if (is_array($data)) {
        $keys = array_keys($data);
        echo "Root keys count: " . count($keys) . "\n";
        echo "First few keys: " . implode(', ', array_slice($keys, 0, 5)) . "\n";
        
        $firstKey = $keys[0];
        $subKeys = is_array($data[$firstKey]) ? array_keys($data[$firstKey]) : [];
        echo "First sub-key's keys (e.g. products?): " . implode(', ', array_slice($subKeys, 0, 5)) . "\n\n";
    } else {
        echo "Not an array or error fetching.\n\n";
    }
}

test_url($url1);
test_url($url2);
