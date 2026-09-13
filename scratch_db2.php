<?php
$url = 'http://localhost/smsbyog/api/service/getServices2All.php';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$raw = curl_exec($ch);
curl_close($ch);
echo "Response Length: " . strlen($raw) . "\n";
echo "Response Preview:\n";
echo substr($raw, 0, 1000);
