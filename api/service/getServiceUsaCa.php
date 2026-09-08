<?php
include __DIR__ . '/../../include/config.php';
include __DIR__ . '/../../include/service_icons.php';

function custom_price_usaca($user_id, $service_id, $server_id, $price, $conn) {
    $sql = mysqli_query($conn, "SELECT * FROM custom_price WHERE user_id='$user_id' AND service_id='$service_id' AND server_id='$server_id'");
    if (mysqli_num_rows($sql) > 0) {
        $data = mysqli_fetch_assoc($sql);
        if ($data['type'] == "flat") return $data['discount'];
        elseif ($data['type'] == "percent") {
            return $price - ($data['discount'] / 100) * $price;
        }
    }
    return $price;
}

if (!isset($_GET['token']) || $_GET['token'] == "") {
    echo '{"status":"500","message":"Token Blank"}';
} elseif (!isset($_GET['server']) || $_GET['server'] == "") {
    echo '{"status":"500","message":"Invalid Server"}';
} else {
    $token  = mysqli_real_escape_string($conn, $_GET['token']);
    $server = mysqli_real_escape_string($conn, $_GET['server']);

    $check_token = check_token($token, $conn);
    if ($check_token === false) {
        echo '{"status":"500","message":"Token Expired"}';
        exit;
    }

    $user_id = $check_token;

    // Fetch DinoMMO API credentials
    $sql_api = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='3'");
    $api_data = mysqli_fetch_assoc($sql_api);
    $api_url  = rtrim($api_data['api_url'], '/');
    $api_key  = $api_data['api_key'];
    $conversion_rate = $api_data['rate'];
    $fixed_profit    = $api_data['profit_amount'];

    // Fetch services from DinoMMO
    $services_url = "{$api_url}/sms-otp/services";
    $api_prices   = getfunction($services_url, $api_key);

    if (!is_array($api_prices)) {
        echo '{"status":"500","message":"Failed to fetch services from provider"}';
        exit;
    }

    $final = [];

    foreach ($api_prices as $details) {
        $service_code  = $details['service_code']  ?? '';
        $service_name  = $details['service_name']  ?? $service_code;
        $raw_api_price = (float)($details['price_usd'] ?? 0);
        $is_active     = $details['is_active']     ?? false;

        if ($raw_api_price <= 0 || !$is_active) continue;

        $base_price   = ($raw_api_price * $conversion_rate) + $fixed_profit;
        $final_price  = round(custom_price_usaca($user_id, $service_code, $server, $base_price, $conn), 2);

        if ($final_price <= 0) continue;

        $logo_url = getServiceIcon($service_name, $service_code);

        array_push($final, [
            'id'            => $service_code,
            'service_name'  => $service_name,
            'service_price' => $final_price,
            'server_id'     => $server,
            'logo_url'      => $logo_url,
            'stock'         => 1,
        ]);
    }

    // Sort alphabetically
    usort($final, fn($a, $b) => strcmp($a['service_name'], $b['service_name']));

    echo json_encode(['status' => '200', 'service' => $final]);
}
