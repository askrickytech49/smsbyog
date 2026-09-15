<?php
include __DIR__ . '/../../include/config.php';
include __DIR__ . '/../../include/api_active_check.php';
require_api_active($conn, 8);

include __DIR__ . '/../../include/service_icons.php';

function makeCurlRequest($url, $api_key, $action, $country)
{
    $url .= '?api_key=' . urlencode($api_key);
    $url .= '&action=' . urlencode($action);
    $url .= '&country=' . urlencode($country);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

function custom_price($user_id, $service_id, $server_id, $price, $conn)
{
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

if (!isset($_GET['server']) || $_GET['server'] == "") {
    echo json_encode(['service' => [], 'error' => 'Invalid Server']);
    exit;
}
if (!isset($_GET['token']) || $_GET['token'] == "") {
    echo json_encode(['service' => [], 'error' => 'Invalid Token']);
    exit;
}

$token = mysqli_real_escape_string($conn, $_GET['token']);
$check_token = check_token($token, $conn);

if ($check_token === false) {
    echo json_encode(['service' => [], 'error' => 'Token Expired Please Logout And Login Again']);
    exit;
}

$country_code = trim((string)$_GET['server']);
if (!preg_match('/^\d+$/', $country_code)) {
    echo json_encode(['service' => [], 'error' => 'BAD_COUNTRY']);
    exit;
}
$server = $country_code;

$api_sql = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='8'");
$api_data = mysqli_fetch_assoc($api_sql);
if (!$api_data) {
    echo json_encode(['service' => [], 'error' => 'Tiger SMS API configuration missing']);
    exit;
}

$conversion_rate = (float)($api_data['rate'] ?? 1500);
$fixed_profit = (float)($api_data['profit_amount'] ?? 200);
$url = rtrim((string)$api_data['api_url'], '/') . '/stubs/handler_api.php';

include_once __DIR__ . '/../../include/api_cache.php';
$cache_key_prices = 'tiger_prices_' . $country_code;
$api_prices = api_cache_get($cache_key_prices, 120);
if (!$api_prices) {
    $price_response = makeCurlRequest($url, $api_data['api_key'], 'getPrices', $country_code);
    $api_prices = json_decode($price_response, true);
    if (is_array($api_prices)) {
        api_cache_set($cache_key_prices, $api_prices);
    }
}

$cache_key_stock = 'tiger_stock_' . $country_code;
$stock_data = api_cache_get($cache_key_stock, 120);
if (!$stock_data) {
    $stock_response = makeCurlRequest($url, $api_data['api_key'], 'getNumbersStatus', $country_code);
    $stock_data = json_decode($stock_response, true);
    if (is_array($stock_data)) {
        api_cache_set($cache_key_stock, $stock_data);
    }
}

$sql = "SELECT * FROM service";
$result = mysqli_query($conn, $sql);
$final = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $service_id = $row['service_id'];
        if (!isset($api_prices[$country_code]) || !is_array($api_prices[$country_code])) continue;
        if (!isset($api_prices[$country_code][$service_id])) continue;

        $raw_api_price = $api_prices[$country_code][$service_id]['cost'] ?? current($api_prices[$country_code][$service_id]);
        if ((float)$raw_api_price <= 0) continue;

        $base_price_naira = (float)$raw_api_price * $conversion_rate;
        $base_calculated_price = $base_price_naira + $fixed_profit;
        $final_price = round((float)custom_price($check_token, $service_id, $server, $base_calculated_price, $conn), 2);

        if ($final_price <= 0) continue;

        $logo_url = getServiceIcon($row['service_name'], $service_id);
        $short_code = $service_id . '_0';
        $send_stock = is_array($stock_data) ? ((int)($stock_data[$short_code] ?? 0)) : 0;

        $final[] = [
            'id' => $service_id,
            'service_name' => $row['service_name'],
            'service_price' => $final_price,
            'server_id' => $server,
            'logo_url' => $logo_url,
            'stock' => $send_stock,
        ];
    }
}

if (ob_get_length()) ob_clean();
header('Content-Type: application/json');
echo json_encode(['service' => $final]);
exit;
