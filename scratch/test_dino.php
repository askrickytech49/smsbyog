<?php
$ch = curl_init('https://api.dinommo.com/sms-otp/services'); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-API-Key: 7b848074d2bcf3169dd7811984247cc4', 'Accept: application/json']); 
$raw = curl_exec($ch); 
print_r(array_slice(json_decode($raw, true), 0, 1));
