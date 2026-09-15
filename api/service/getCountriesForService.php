<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('memory_limit', '256M');
set_time_limit(60);

/**
 * Server 1 Tiger SMS country list endpoint.
 * Trust the Tiger API catalog and only normalize display names for the UI.
 */
include __DIR__ . '/../../include/config.php';
include __DIR__ . '/../../include/api_active_check.php';
require_api_active($conn, 8);

function custom_price($user_id, $service_id, $server_id, $price, $conn) {
    $sql = mysqli_query($conn, "SELECT * FROM custom_price WHERE user_id='" . $user_id . "' AND service_id='" . $service_id . "' AND server_id='" . $server_id . "'");
    if (mysqli_num_rows($sql) > 0) {
        $data = mysqli_fetch_assoc($sql);
        if ($data['type'] == "flat") {
            return $data['discount'];
        } elseif ($data['type'] == "percent") {
            $percent = (float)$data['discount'];
            $final_percent = ($percent / 100) * $price;
            return $price - $final_percent;
        }
    }
    return $price;
}

function tiger_country_name($countryCode, $countriesList) {
    $code = (string)$countryCode;

    if (is_array($countriesList)) {
        if (isset($countriesList[$code]) && is_array($countriesList[$code])) {
            return $countriesList[$code]['eng']
                ?? $countriesList[$code]['name']
                ?? $countriesList[$code]['en']
                ?? 'Country ' . $code;
        }

        foreach ($countriesList as $item) {
            if (is_array($item) && (string)($item['id'] ?? '') === $code) {
                return $item['eng'] ?? $item['name'] ?? $item['en'] ?? 'Country ' . $code;
            }
        }
    }

    return 'Country ' . $code;
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

$api_sql = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='8'");
$api_data = mysqli_fetch_assoc($api_sql);
if (!$api_data) {
    echo json_encode(['countries' => [], 'error' => 'Tiger API config missing']);
    exit;
}

$api_url = rtrim((string)($api_data['api_url'] ?? ''), '/');
$api_key = (string)($api_data['api_key'] ?? '');
$conversion_rate = (float)($api_data['rate'] ?? 1500);
$fixed_profit    = (float)($api_data['profit_amount'] ?? 200);

include_once __DIR__ . '/../../include/api_cache.php';
$cache_key_prices = 'tigersms_getPrices';
$allPrices = api_cache_get($cache_key_prices, 120);

if (!$allPrices) {
    $url = "{$api_url}/stubs/handler_api.php?api_key=" . urlencode($api_key) . "&action=getPrices";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $raw = curl_exec($ch);
    $http_code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $allPrices = $raw ? json_decode($raw, true) : [];

    if ($http_code < 200 || $http_code >= 300 || !is_array($allPrices) || empty($allPrices)) {
        $allPrices = api_cache_get_stale($cache_key_prices);
    } elseif (is_array($allPrices)) {
        api_cache_set($cache_key_prices, $allPrices);
    }
}

$cache_key_countries = 'tigersms_getCountries';
$countriesList = api_cache_get($cache_key_countries, 300);

if (!$countriesList) {
    $countries_url = "{$api_url}/stubs/handler_api.php?api_key=" . urlencode($api_key) . "&action=getCountries";
    $ch2 = curl_init($countries_url);
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch2, CURLOPT_CONNECTTIMEOUT, 3);
    curl_setopt($ch2, CURLOPT_TIMEOUT, 6);
    curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
    $rawCountries = curl_exec($ch2);
    $countries_http_code = (int)curl_getinfo($ch2, CURLINFO_HTTP_CODE);
    curl_close($ch2);
    $countriesList = $rawCountries ? json_decode($rawCountries, true) : [];
    if ($countries_http_code < 200 || $countries_http_code >= 300 || !is_array($countriesList) || empty($countriesList)) {
        $countriesList = api_cache_get_stale($cache_key_countries);
    } elseif (is_array($countriesList)) {
        api_cache_set($cache_key_countries, $countriesList);
    }
}

$final = [];
if (is_array($allPrices)) {
    foreach ($allPrices as $countryCode => $services) {
        if (!is_array($services) || !isset($services[$service])) continue;

        $details = $services[$service];
        $count = (int)($details['count'] ?? 0);
        $cost  = (float)($details['cost'] ?? 0);
        if ($count <= 0 || $cost <= 0) continue;

        $base_price_naira = $cost * $conversion_rate;
        $base_with_profit = $base_price_naira + $fixed_profit;
        $final_price = round((float)custom_price($user_id, $service, $countryCode, $base_with_profit, $conn), 2);
        if ($final_price <= 0) continue;

        $countryName = tiger_country_name($countryCode, $countriesList);

        $final[] = [
            'country_code' => (string)$countryCode,
            'country_name' => $countryName,
            'price' => $final_price,
            'stock' => $count,
        ];
    }
}

usort($final, function($a, $b) { return strcasecmp($a['country_name'], $b['country_name']); });

if (ob_get_length()) ob_clean();
header('Content-Type: application/json');
echo json_encode(['countries' => $final]);
exit;
