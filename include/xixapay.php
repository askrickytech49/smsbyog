<?php

function virtualRequest($endpoint, $method = 'POST', $data = [], $headers = [])
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    // 🟢 DYNAMIC HEADERS ROUTING LAYER
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    } else {
        // Safe backward-compatible fallback using standard global constants
        $secretKey = defined('XIXA_SECRET') ? XIXA_SECRET : '';
        $apiKey    = defined('XIXA_API') ? XIXA_API : '';
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$secretKey}",
            "api-key: {$apiKey}",
            "Content-Type: application/json"
        ]);
    }

    if (!empty($data)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        return ['error' => $error];
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $decoded = json_decode($response, true);

    if ($httpCode >= 400) {
        return [
            'error' => 'HTTP Error',
            'status_code' => $httpCode,
            'response' => $decoded
        ];
    }

    return $decoded;
}