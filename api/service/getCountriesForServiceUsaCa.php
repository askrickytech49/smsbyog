<?php
/**
 * getCountriesForServiceUsaCa.php — USA + Canada (Dino)
 * Given a service code, returns USA and Canada with their respective prices (Naira).
 */
include __DIR__ . '/../../include/config.php';
include __DIR__ . '/../../include/api_active_check.php';
require_api_active($conn, 3);

function custom_price_usaca($user_id, $service_id, $server_id, $price, $conn) {
    $sql = mysqli_query($conn, "SELECT * FROM custom_price WHERE user_id='$user_id' AND service_id='$service_id' AND server_id='$server_id'");
    if (mysqli_num_rows($sql) > 0) {
        $data = mysqli_fetch_assoc($sql);
        if ($data['type'] == "flat") return $data['discount'];
        elseif ($data['type'] == "percent") return $price - ($data['discount'] / 100) * $price;
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

$token   = mysqli_real_escape_string($conn, $_GET['token']);
$service = mysqli_real_escape_string($conn, $_GET['service']);
$check_token = check_token($token, $conn);

if ($check_token === false) {
    echo json_encode(['countries' => [], 'error' => 'Token Expired']);
    exit;
}
$user_id = $check_token;

$api_sql = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='3'");
$api_data = mysqli_fetch_assoc($api_sql);
$api_url = rtrim($api_data['api_url'], '/');
$api_key = $api_data['api_key'];
$conversion_rate = (float)$api_data['rate'];
$fixed_profit    = (float)$api_data['profit_amount'];

$services_url = "{$api_url}/sms-otp/services";
$api_prices = getfunction($services_url, $api_key);

$base_cost = 0;
if (is_array($api_prices)) {
    foreach ($api_prices as $details) {
        if (($details['service_code'] ?? '') === $service && ($details['is_active'] ?? false)) {
            $base_cost = (float)($details['price_usd'] ?? 0);
            break;
        }
    }
}

if ($base_cost <= 0) {
    echo json_encode(['countries' => [], 'error' => 'Service out of stock']);
    exit;
}

$base_price_naira = $base_cost * $conversion_rate;
$base_with_profit = $base_price_naira + $fixed_profit;

// Calc for USA
$price_us = round(custom_price_usaca($user_id, $service, 'us', $base_with_profit, $conn), 2);
// Calc for Canada
$price_ca = round(custom_price_usaca($user_id, $service, 'ca', $base_with_profit, $conn), 2);

$final = [];
if ($price_us > 0) {
    $final[] = [
        'country_code' => 'us',
        'country_name' => 'United States',
        'price'        => $price_us,
        'stock'        => 'Available'
    ];
}
if ($price_ca > 0) {
    $final[] = [
        'country_code' => 'ca',
        'country_name' => 'Canada',
        'price'        => $price_ca,
        'stock'        => 'Available'
    ];
}

if (ob_get_length()) ob_clean();
header('Content-Type: application/json');
echo json_encode(['countries' => $final]);
exit;
