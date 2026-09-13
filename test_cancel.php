<?php
$c = mysqli_connect('localhost','root','','smsbyogc_main');
$r = mysqli_query($c, 'SELECT number_id FROM active_number ORDER BY id DESC LIMIT 1');
$nid = mysqli_fetch_assoc($r)['number_id'];
$r2 = mysqli_query($c, 'SELECT api_key FROM api_detail WHERE id=2');
$key = mysqli_fetch_assoc($r2)['api_key'];
$ch = curl_init('https://5sim.net/v1/user/cancel/'.$nid);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer '.$key, 'Accept: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
echo "Result: " . curl_exec($ch) . "\n";
