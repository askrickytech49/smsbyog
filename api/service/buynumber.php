<?php
date_default_timezone_set('Africa/Lagos');
include __DIR__ . '/../../include/config.php';
include_once __DIR__ . '/../../include/tiger_number_guard.php';

function generateRandomString($length = 20)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $random_string = '';

    for ($i = 0; $i < $length; $i++) {
        $random_string .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $random_string;
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
    echo '{"status":"500","message":"Invalid Server"}';
    exit;
}
if (!isset($_GET['service']) || $_GET['service'] == "") {
    echo '{"status":"500","message":"Invalid Service"}';
    exit;
}
if (!isset($_GET['token']) || $_GET['token'] == "") {
    echo '{"status":"500","message":"Token Blank"}';
    exit;
}

$token = mysqli_real_escape_string($conn, $_GET['token']);
$check_token = check_token($token, $conn);

if ($check_token === false) {
    echo '{"status":"500","message":"Token Expired Please Logout And Login Again"}';
    exit;
}

$server_code = trim((string)$_GET['server']);
$server_code = preg_replace('/\D+/', '', $server_code);
if ($server_code === '' || !preg_match('/^\d+$/', $server_code)) {
    echo '{"status":"500","message":"BAD_COUNTRY"}';
    exit;
}
$server = $server_code;
$service = mysqli_real_escape_string($conn, $_GET['service']);
$user_id = $check_token;

$requested_max_price = isset($_GET['max_price']) && is_numeric($_GET['max_price'])
    ? round((float)$_GET['max_price'], 4)
    : 0;
$requested_provider_ids = '';
if (!empty($_GET['provider_ids'])) {
    $requested_provider_ids = preg_replace('/[^0-9,]/', '', (string)$_GET['provider_ids']);
}

include_once __DIR__ . '/../../include/api_cache.php';
$country_catalog = api_cache_get('tigersms_getCountries', 3600);
if (!$country_catalog) {
    $country_catalog = api_cache_get_stale('tigersms_getCountries');
}
$country_catalog_by_id = [];
if (is_array($country_catalog)) {
    foreach ($country_catalog as $country_item) {
        if (isset($country_item['id'])) {
            $country_catalog_by_id[(string)$country_item['id']] = $country_item;
        }
    }
}
$provider_country_name = 'Country ' . $server_code;
if (isset($country_catalog_by_id[$server_code])) {
    $provider_country_name = $country_catalog_by_id[$server_code]['eng']
        ?? $country_catalog_by_id[$server_code]['name']
        ?? $country_catalog_by_id[$server_code]['en']
        ?? $provider_country_name;
}

$sql4 = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='8'");
if (mysqli_num_rows($sql4) == 0) {
    echo '{"status":"500","message":"API Configuration Not Found"}';
    exit;
}
$api_data = mysqli_fetch_assoc($sql4);
$api_key = $api_data['api_key'];
$api_url = rtrim((string)$api_data['api_url'], '/');
$conversion_rate = (float)($api_data['rate'] ?? 1500);
$fixed_profit = (float)($api_data['profit_amount'] ?? 200);

$price_url = "{$api_url}/stubs/handler_api.php?api_key=" . urlencode($api_key) . "&action=getPrices&country=" . urlencode($server_code);
$ch_p = curl_init($price_url);
curl_setopt($ch_p, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch_p, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch_p, CURLOPT_TIMEOUT, 15);
curl_setopt($ch_p, CURLOPT_SSL_VERIFYPEER, false);
$price_res = curl_exec($ch_p);
curl_close($ch_p);
$api_prices = json_decode($price_res, true);

$raw_api_price = 0;
if (isset($api_prices[$server_code][$service])) {
    $s_data = $api_prices[$server_code][$service];
    $raw_api_price = $s_data['cost'] ?? $s_data['price'] ?? (is_numeric($s_data) ? $s_data : 0);
}

if ((float)$raw_api_price <= 0) {
    echo '{"status":"500","message":"Service currently unavailable (Price 0)"}';
    exit;
}

if ($requested_max_price > 0) {
    $raw_api_price = $requested_max_price;
}

$base_price = ((float)$raw_api_price * $conversion_rate) + $fixed_profit;
$service_price = round((float)custom_price($user_id, $service, $server, $base_price, $conn), 2);

$sql2 = mysqli_query($conn, "SELECT balance, total_otp FROM user_wallet WHERE user_id='" . $user_id . "'");
$user_wallet = mysqli_fetch_assoc($sql2);
$user_balance = (float)($user_wallet['balance'] ?? 0);

if ($user_balance < $service_price) {
    echo '{"status":"500","message":"Insufficient Balance. Required: ' . $service_price . '"}';
    exit;
}

$base_number_url = "{$api_url}/stubs/handler_api.php?api_key=" . urlencode($api_key) . "&action=getNumber&service=" . urlencode($service) . "&country=" . urlencode($server_code);
$price_query = $requested_max_price > 0 ? '&maxPrice=' . urlencode(number_format($requested_max_price, 4, '.', '')) : '';
$provider_query = $requested_provider_ids !== '' ? '&providerIds=' . urlencode($requested_provider_ids) : '';
$result = '';
$response = [];
$purchase_urls = [$base_number_url . $price_query . $provider_query];
if ($provider_query !== '') {
    $purchase_urls[] = $base_number_url . $price_query;
}

foreach ($purchase_urls as $url) {
    for ($attempt = 0; $attempt < 2; $attempt++) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = trim((string)curl_exec($ch));
        curl_close($ch);
        $response = explode(':', $result);
        if (($response[0] ?? '') !== 'NO_NUMBERS') break;
    }
    if (($response[0] ?? '') === 'ACCESS_NUMBER') break;
    if (($response[0] ?? '') !== 'NO_NUMBERS') break;
}

if (($response[0] ?? '') != "ACCESS_NUMBER") {
    $err_msg = $response[0] ?? 'API_ERROR';
    error_log("TigerSMS purchase failed: response=" . $result . "; service={$service}; country={$server_code}");
    if ($err_msg == "NO_NUMBERS") {
        $err_msg = "No numbers available for this service right now. Please try again later.";
    } elseif ($err_msg == "NO_BALANCE") {
        $err_msg = "Service temporarily unavailable. Please try again later.";
    } else {
        $err_msg = "We could not complete this request. Please try again later.";
    }
    echo json_encode(["status" => "500", "message" => $err_msg]);
    exit;
}

$country_match = tiger_number_matches_country($provider_country_name, $response[2] ?? '');
if ($country_match === false) {
    $cancel_url = "{$api_url}/stubs/handler_api.php?api_key=" . urlencode($api_key) . "&action=setStatus&id=" . urlencode($response[1]) . "&status=8";
    $cancel_ch = curl_init($cancel_url);
    curl_setopt($cancel_ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($cancel_ch, CURLOPT_CONNECTTIMEOUT, 3);
    curl_setopt($cancel_ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($cancel_ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_exec($cancel_ch);
    curl_close($cancel_ch);
    echo '{"status":"500","message":"Provider returned a number from the wrong country. No charge was made."}';
    exit;
}

$random_order = generateRandomString();
$expires_at = date('Y-m-d H:i:s', strtotime('+20 minutes'));

mysqli_begin_transaction($conn, MYSQLI_TRANS_START_READ_WRITE);
try {
    $sql200 = mysqli_query($conn, "SELECT * FROM user_wallet WHERE user_id='" . $user_id . "' FOR UPDATE");
    $user_data = mysqli_fetch_assoc($sql200);
    $user_balance = (float)($user_data['balance'] ?? 0);
    $current_time_in_ist = date('Y-m-d H:i:s');

    $service_query = mysqli_query($conn, "SELECT service_name FROM service WHERE service_id = '$service' LIMIT 1");
    $db_service = mysqli_fetch_assoc($service_query);
    $service_name = strip_tags($db_service['service_name'] ?? $service);

    if ($user_balance >= $service_price) {
        $cut_balance = $user_balance - $service_price;
        $user_otp = (int)($user_data['total_otp'] ?? 0);
        $add_otp = $user_otp + 1;

        $sql5 = mysqli_query($conn, "UPDATE user_wallet SET balance='$cut_balance', total_otp='$add_otp' WHERE user_id='$user_id'");
        $provider_country_name = mysqli_real_escape_string($conn, $provider_country_name);
        $sql6 = mysqli_query($conn, "INSERT INTO active_number(user_id, api_id, number_id, number, server_id, provider_country_name, service_id, order_id, buy_time, expires_at, status, sms_text, service_price, service_name, active_status)
            VALUES ('$user_id', '8', '{$response[1]}', '{$response[2]}', '$server', '$provider_country_name', '$service', '$random_order', '$current_time_in_ist', '$expires_at', '2', '', '$service_price', '$service_name', '2')");

        if ($sql5 && $sql6) {
            mysqli_commit($conn);
            if (ob_get_length()) ob_clean();
            echo '{"status":"200","message":"Number Purchased","res":"ACCESS_NUMBER:' . $random_order . ':' . $response[2] . '"}';
            exit;
        }

        throw new Exception('Sql Error #1');
    }

    echo '{"status":"500","message":"Insufficient Balance"}';
    exit;
} catch (Exception $e) {
    mysqli_rollback($conn);
    if (ob_get_length()) ob_clean();
    echo '{"status":"500","message":"' . $e->getMessage() . '"}';
    exit;
}
