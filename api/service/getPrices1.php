<?php
include __DIR__ . '/../../include/config.php';
include __DIR__ . '/../../include/api_active_check.php';
require_api_active($conn, 8);
include_once __DIR__ . '/../../include/api_cache.php';

function custom_price($user_id, $service_id, $server_id, $price, $conn) {
    $sql = mysqli_query($conn, "SELECT type, discount FROM custom_price WHERE user_id='$user_id' AND service_id='$service_id' AND server_id='$server_id'");
    if ($sql && mysqli_num_rows($sql) > 0) {
        $data = mysqli_fetch_assoc($sql);
        if ($data['type'] === 'flat') return (float)$data['discount'];
        if ($data['type'] === 'percent') return $price - (($data['discount'] / 100) * $price);
    }
    return $price;
}

header('Content-Type: application/json');

if (empty($_GET['token']) || empty($_GET['service']) || empty($_GET['country'])) {
    echo json_encode(['prices' => [], 'error' => 'Missing price request fields']);
    exit;
}

$token = mysqli_real_escape_string($conn, $_GET['token']);
$user_id = check_token($token, $conn);
if ($user_id === false) {
    echo json_encode(['prices' => [], 'error' => 'Token Expired']);
    exit;
}

$service = mysqli_real_escape_string($conn, $_GET['service']);
$country = mysqli_real_escape_string($conn, $_GET['country']);
if (!preg_match('/^\d+$/', $country)) {
    echo json_encode(['prices' => [], 'error' => 'BAD_COUNTRY']);
    exit;
}
$api_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT api_url, api_key, rate, profit_amount FROM api_detail WHERE id='8'"));
if (!$api_row) {
    echo json_encode(['prices' => [], 'error' => 'API Configuration Not Found']);
    exit;
}

$cache_key = 'tigersms_offer_prices_' . $country . '_' . $service;
$payload = api_cache_get($cache_key, 45);
if (!$payload) {
    $url = rtrim($api_row['api_url'], '/') . '/stubs/handler_api.php?api_key='
        . urlencode($api_row['api_key']) . '&action=getOffers&countries='
        . urlencode($country) . '&services=' . urlencode($service);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $raw = curl_exec($ch);
    $http_code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $payload = $raw ? json_decode($raw, true) : null;
    if ($http_code >= 200 && $http_code < 300 && is_array($payload)) {
        api_cache_set($cache_key, $payload);
    } else {
        $payload = api_cache_get_stale($cache_key);
    }
}

$bucket_data = $payload['data'][$service][$country] ?? null;
$bucket_prices = is_array($bucket_data['map'] ?? null) ? $bucket_data['map'] : [];

$provider_ids_by_price = [];
$v3_cache_key = 'tigersms_prices_v3_' . $country . '_' . $service;
$v3_payload = api_cache_get($v3_cache_key, 45);
if (!$v3_payload) {
    $v3_url = rtrim($api_row['api_url'], '/') . '/stubs/handler_api.php?api_key='
        . urlencode($api_row['api_key']) . '&action=getPricesV3&country='
        . urlencode($country) . '&service=' . urlencode($service);
    $v3_ch = curl_init($v3_url);
    curl_setopt($v3_ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($v3_ch, CURLOPT_CONNECTTIMEOUT, 3);
    curl_setopt($v3_ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($v3_ch, CURLOPT_SSL_VERIFYPEER, false);
    $v3_raw = curl_exec($v3_ch);
    curl_close($v3_ch);
    $v3_payload = $v3_raw ? json_decode($v3_raw, true) : null;
    if (is_array($v3_payload)) api_cache_set($v3_cache_key, $v3_payload);
}

$providers = $v3_payload[$country][$service]['providers'] ?? [];
foreach ($providers as $provider) {
    $provider_id = (string)($provider['provider_id'] ?? '');
    foreach (($provider['price'] ?? []) as $provider_price) {
        $price_key = number_format((float)$provider_price, 4, '.', '');
        if ($provider_id !== '') $provider_ids_by_price[$price_key][] = $provider_id;
    }
}

$fallback_prices = [];
$fallback_url = rtrim($api_row['api_url'], '/') . '/stubs/handler_api.php?api_key='
    . urlencode($api_row['api_key']) . '&action=getFreePrices&country='
    . urlencode($country) . '&service=' . urlencode($service);
$fallback_ch = curl_init($fallback_url);
curl_setopt($fallback_ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($fallback_ch, CURLOPT_CONNECTTIMEOUT, 3);
curl_setopt($fallback_ch, CURLOPT_TIMEOUT, 8);
curl_setopt($fallback_ch, CURLOPT_SSL_VERIFYPEER, false);
$fallback_raw = curl_exec($fallback_ch);
curl_close($fallback_ch);
$fallback_payload = $fallback_raw ? json_decode($fallback_raw, true) : null;
$fallback_data = $fallback_payload[$country][$service] ?? null;
if (is_array($fallback_data['prices'] ?? null)) {
    $fallback_prices = $fallback_data['prices'];
}

$combined_prices = [];
foreach ([$bucket_prices, $fallback_prices] as $price_set) {
    if (!is_array($price_set)) continue;
    foreach ($price_set as $usd => $stock) {
        $price_key = number_format((float)$usd, 4, '.', '');
        $stock_count = (int)$stock;
        if ($stock_count <= 0) continue;
        if (!isset($combined_prices[$price_key])) {
            $combined_prices[$price_key] = 0;
        }
        $combined_prices[$price_key] = max($combined_prices[$price_key], $stock_count);
    }
}

if (isset($v3_payload[$country][$service]['prices']) && is_array($v3_payload[$country][$service]['prices'])) {
    foreach ($v3_payload[$country][$service]['prices'] as $price_key => $stock_count) {
        $normalized_key = number_format((float)$price_key, 4, '.', '');
        $combined_prices[$normalized_key] = max((int)$stock_count, $combined_prices[$normalized_key] ?? 0);
    }
}

$prices = [];
foreach ($combined_prices as $usd => $stock_count) {
    $usd_price = (float)$usd;
    if ($usd_price <= 0 || $stock_count <= 0) continue;

    $base_price = ($usd_price * (float)$api_row['rate']) + (float)$api_row['profit_amount'];
    $customer_price = round(custom_price($user_id, $service, $country, $base_price, $conn), 2);
    if ($customer_price <= 0) continue;

    $prices[] = [
        'max_price' => round($usd_price, 4),
        'stock' => (int)$stock_count,
        'price' => $customer_price,
        'provider_ids' => array_values(array_unique($provider_ids_by_price[$usd] ?? [])),
    ];
}

usort($prices, fn($a, $b) => $a['max_price'] <=> $b['max_price']);
echo json_encode(['prices' => $prices]);