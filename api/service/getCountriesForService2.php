<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('memory_limit', '256M');
set_time_limit(60);

/**
 * getCountriesForService2.php — Server 2 (5SIM)
 * Given a service/product code, returns all countries that have it in stock,
 * along with per-country pricing (converted to Naira with markup).
 */
include __DIR__ . '/../../include/config.php';
include __DIR__ . '/../../include/api_active_check.php';
// require_api_active($conn, 2);

function custom_price($user_id, $service_id, $server_id, $price, $conn) {
    $sql = mysqli_query($conn, "SELECT * FROM custom_price WHERE user_id='" . $user_id . "' AND service_id='" . $service_id . "' AND server_id='" . $server_id . "'");
    if (mysqli_num_rows($sql) > 0) {
        $data = mysqli_fetch_assoc($sql);
        if ($data['type'] == "flat") {
            return $data['discount'];
        } elseif ($data['type'] == "percent") {
            $percent = $data['discount'];
            $final_percent = ($percent / 100) * $price;
            return $price - $final_percent;
        }
    }
    return $price;
}

if (!isset($_GET['token']) || $_GET['token'] == "") {
    echo json_encode(['countries' => [], 'error' => 'Token Blank']);
    exit;
}
if (!isset($_GET['service']) || $_GET['service'] == "") {
    echo json_encode(['countries' => [], 'error' => 'Service not specified']);
    exit;
}

$token = mysqli_real_escape_string($conn, $_GET['token']);
$check_token = check_token($token, $conn);

if ($check_token === false) {
    echo json_encode(['countries' => [], 'error' => 'Token Expired']);
    exit;
}

$service = mysqli_real_escape_string($conn, $_GET['service']);
$user_id = $check_token;

// Fetch 5SIM API details - get rate, profit, and excluded operators from DB
$api_sql = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='2'");
$api_data = $api_sql ? mysqli_fetch_assoc($api_sql) : null;
$conversion_rate = $api_data ? (float)$api_data['rate'] : 1500;
$fixed_profit    = $api_data ? (float)$api_data['profit_amount'] : 200;


// Fetch prices filtered by product with caching (2-minute TTL)
include_once __DIR__ . '/../../include/api_cache.php';
$cache_key = '5sim_prices_' . $service;
$pricesData = api_cache_get($cache_key, 120);

if (!$pricesData) {
    $url = "https://5sim.net/v1/guest/prices?product=" . urlencode($service);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $raw = curl_exec($ch);
    $http_code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $pricesData = $raw ? json_decode($raw, true) : [];
    
    // Do not replace a good cache with a provider error or empty response.
    if ($http_code < 200 || $http_code >= 300 || !is_array($pricesData) || !isset($pricesData[$service]) || !is_array($pricesData[$service])) {
        $pricesData = api_cache_get_stale($cache_key);
    } elseif ($pricesData && is_array($pricesData)) {
        api_cache_set($cache_key, $pricesData);
    }
}

if (!is_array($pricesData) || !isset($pricesData[$service]) || !is_array($pricesData[$service])) {
    echo json_encode(['countries' => [], 'error' => '5sim country data temporarily unavailable']);
    exit;
}

// When requesting a specific product, 5SIM returns {"productName": {"countryName": {"operator": {...}}}}
$serviceData = $pricesData[$service] ?? [];

// Fetch countries list for display names
$ch2 = curl_init('https://5sim.net/v1/guest/countries');
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_TIMEOUT, 10);
curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
$rawCountries = curl_exec($ch2);
curl_close($ch2);
$countriesList = $rawCountries ? json_decode($rawCountries, true) : [];

// Build countries list
$final = [];

foreach ($serviceData as $country => $operators) {
    if (!is_array($operators)) continue;

    // 5sim exposes both US pools under one country. Present them as two
    // selectable countries while retaining the real provider country code.
    if (strtolower($country) === 'usa') {
        foreach (['virtual63' => 'USA', 'virtual28' => 'USA 2'] as $operatorKey => $displayName) {
            if (!isset($operators[$operatorKey]) || !is_array($operators[$operatorKey])) continue;
            $count = (int)($operators[$operatorKey]['count'] ?? 0);
            $cost = (float)($operators[$operatorKey]['cost'] ?? 0);
            if ($count <= 0 || $cost <= 0) continue;

            $base_with_profit = ($cost * $conversion_rate) + $fixed_profit;
            $final_price = round(custom_price($user_id, $service, 'usa', $base_with_profit, $conn), 2);
            if ($final_price <= 0) continue;

            $final[] = [
                'country_code' => $operatorKey === 'virtual28' ? 'usa2' : 'usa',
                'country_name' => $displayName,
                'operator' => $operatorKey,
                'price' => $final_price,
                'stock' => $count,
            ];
        }
        continue;
    }

    // All other countries continue to use the cheapest available operator.
    $bestCost = PHP_FLOAT_MAX;
    $bestOperator = 'any';
    $totalStock = 0;
    foreach ($operators as $opName => $opDetails) {
        $count = (int)($opDetails['count'] ?? 0);
        $cost = (float)($opDetails['cost'] ?? 0);
        if ($count <= 0) continue;
        $totalStock += $count;
        if ($cost > 0 && $cost < $bestCost) {
            $bestCost = $cost;
            $bestOperator = $opName;
        }
    }
    if ($totalStock <= 0 || $bestCost == PHP_FLOAT_MAX) continue;

    $base_with_profit = ($bestCost * $conversion_rate) + $fixed_profit;
    $final_price = round(custom_price($user_id, $service, $country, $base_with_profit, $conn), 2);
    if ($final_price <= 0) continue;

    $countryName = ucfirst(str_replace('_', ' ', $country));
    if (isset($countriesList[$country]) && !empty($countriesList[$country]['text_en'])) {
        $countryName = $countriesList[$country]['text_en'];
    }
    $final[] = [
        'country_code' => $country,
        'country_name' => $countryName,
        'operator' => $bestOperator,
        'price' => $final_price,
        'stock' => $totalStock,
    ];
}

// Sort by country name
usort($final, function($a, $b) { return strcasecmp($a['country_name'], $b['country_name']); });

if (ob_get_length()) ob_clean();
header('Content-Type: application/json');
echo json_encode(['countries' => $final]);
exit;
