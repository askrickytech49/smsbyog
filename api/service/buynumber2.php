<?php
date_default_timezone_set('Africa/Lagos');
include __DIR__ . '/../../include/config.php';
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
            $percent = $data['discount'];
            $final_percent = ($percent / 100) * $price;
            $sub = $price - $final_percent;
            return $sub;
        }
    } else {
        return $price;
    }
}
// --- CONFIGURATION (Must match your fetch script) ---

// ----------------------------------------------------

if (!isset($_GET['server']) || $_GET['server'] == "") {
    echo '{"status":"500","message":"Invalid Server"}';
} elseif (!isset($_GET['service']) || $_GET['service'] == "") {
    echo '{"status":"500","message":"Invalid Service"}';
} elseif (!isset($_GET['token']) || $_GET['token'] == "") {
    echo '{"status":"500","message":"Token Blank"}';
} else {
    $token = mysqli_real_escape_string($conn, $_GET['token']);
    $check_token = check_token($token, $conn);

    if ($check_token === false) {
    echo '{"status":"500","message":"Token Expired Please Logout And Login Again"}';
} else {
    $server   = mysqli_real_escape_string($conn, $_GET['server']);
    $service  = mysqli_real_escape_string($conn, $_GET['service']);
    // ALWAYS use 'any' so 5sim natively picks the cheapest working operator available right now
    $operator = 'any';
    $user_id  = $check_token;

    $sql4 = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='2'");
    $api_data = mysqli_fetch_assoc($sql4);
    
    $api_key = $api_data['api_key'];
    $api_url = rtrim($api_data['api_url'], '/');
    $conversion_rate = $api_data['rate'];
    $fixed_profit = $api_data['profit_amount'];

    // 1. Fetch REAL-TIME PRICE from 5sim JSON API
    // 5sim Prices are at: /v1/guest/prices?country=$server
    
    // $price_url = "{$api_url}/v1/guest/prices?country={$server}";
    $price_url = "https://5sim.net/v1/guest/prices?country=" . $server . "&product=" . $service;

    $ch_p = curl_init($price_url);
    curl_setopt($ch_p, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch_p, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $api_key",
        "Accept: application/json"
    ]);
    $price_res = curl_exec($ch_p);
    $api_prices = json_decode($price_res, true);
    curl_close($ch_p); // Always close your curl handles
    
    $raw_api_price = 0;
    
    // 3. Navigate the response: [Product][Country][Operator]['cost']
    // When querying with both country & product, 5SIM returns: {product: {country: {operator: {cost, count}}}}
    // Build a sorted list of operators with stock (highest stock first for best chance of success)
    $available_operators = [];
    if (isset($api_prices[$server][$service])) {
        foreach ($api_prices[$server][$service] as $op_name => $op_data) {
            if (isset($op_data['cost']) && $op_data['count'] > 0) {
                $available_operators[] = [
                    'name'  => $op_name,
                    'cost'  => (float)$op_data['cost'],
                    'count' => (int)$op_data['count'],
                ];
                if ($raw_api_price <= 0 || (float)$op_data['cost'] < $raw_api_price) {
                    $raw_api_price = (float)$op_data['cost'];
                }
            }
        }
    }
    // Sort by stock count descending (try the operator with most stock first)
    usort($available_operators, fn($a, $b) => $b['count'] - $a['count']);

    // 4. Handle errors if no operators found at all
    if ($raw_api_price <= 0 || empty($available_operators)) {
        echo json_encode([
            "status" => "500",
            "message" => "No operators available for " . ucfirst($service) . " in " . ucfirst($server)
        ]);
        exit;
    }

    // 2. Calculate Final Price
   // Convert API USD price to Naira
$base_price_naira = $raw_api_price * $conversion_rate;

// Add fixed profit
$base_price = $base_price_naira + $fixed_profit;

// Apply custom user discount if any
$service_price = custom_price($user_id, $service, $server, $base_price, $conn);
    $service_price = round($service_price, 2);

    // 3. Wallet Check
    $sql2 = mysqli_query($conn, "SELECT balance FROM user_wallet WHERE user_id='$user_id'");
    $user_wallet = mysqli_fetch_assoc($sql2);
    if ($user_wallet['balance'] < $service_price) {
        echo '{"status":"500","message":"Insufficient Balance. Amount Required: ₦'.$service_price.' Kindly Top-Up Your Balance"}';
        exit;
    }

    // 4. RETRY LOOP: Try each operator until one actually delivers a number
    // 5SIM's stock counts are often stale/cached, so we try 'any' first, then each specific operator.
    $operators_to_try = ['any']; // Try 'any' first as it's fastest when it works
    foreach ($available_operators as $op) {
        $operators_to_try[] = $op['name'];
    }

    $response = null;
    $result = '';
    $last_error = '';

    foreach ($operators_to_try as $try_operator) {
        $buy_url = "{$api_url}/v1/user/buy/activation/{$server}/{$try_operator}/{$service}";
        
        $ch = curl_init($buy_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $api_key",
            "Accept: application/json"
        ]);
        $result = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($result, true);

        // If we got a valid response with an ID, we succeeded!
        if ($response && isset($response['id'])) {
            $operator = $try_operator; // Record which operator worked
            break;
        }

        // Record the error and try next operator
        $last_error = trim($result) ?: 'API_LIMIT_OR_NO_NUMBERS';
        $response = null; // Reset so loop continues
    }

    // If ALL operators failed
    if (!$response || !isset($response['id'])) {
        echo json_encode([
            "status" => "500",
            "message" => "All operators are currently busy for " . ucfirst($service) . " in " . ucfirst($server) . ". Please try another country or try again shortly."
        ]);
        exit;
    } else {
        $random_order = generateRandomString();
        mysqli_begin_transaction($conn, MYSQLI_TRANS_START_READ_WRITE);
        try {
            $sql200 = mysqli_query($conn, "SELECT balance, total_otp FROM user_wallet WHERE user_id='$user_id' FOR UPDATE");
            $user_data = mysqli_fetch_assoc($sql200);
            
            if ($user_data['balance'] >= $service_price) {
                $cut_balance = $user_data['balance'] - $service_price;
                $add_otp = $user_data['total_otp'] + 1;
                $current_time = date('Y-m-d H:i:s');

                // 5sim variables: $response['id'] is number_id, $response['phone'] is number
                $num_id = $response['id'];
                $phone = $response['phone'];
                // Store the real expiry time from 5sim API response
                $expires_at = isset($response['expires'])
                    ? date('Y-m-d H:i:s', strtotime($response['expires']))
                    : date('Y-m-d H:i:s', strtotime('+20 minutes'));

                $sql5 = mysqli_query($conn, "UPDATE user_wallet SET balance='$cut_balance', total_otp='$add_otp' WHERE user_id='$user_id'");
                
                $service_display = ucfirst($service) . " (" . ucfirst($operator) . ")";
                
                $sql6 = mysqli_query($conn, "INSERT INTO active_number(user_id, api_id, number_id, number, server_id, service_id, order_id, buy_time, expires_at, status, sms_text, service_price, service_name, active_status) 
                VALUES ('$user_id', '2', '$num_id', '$phone', '$server', '$service', '$random_order', '$current_time', '$expires_at', '2', '', '$service_price', '$service_display', '2')");
                
                if ($sql5 && $sql6) {
                    mysqli_commit($conn); 
                    if (ob_get_length()) ob_clean(); 
                    echo '{"status":"200","message":"Number Purchased","res":"ACCESS_NUMBER:' . $random_order . ':' . $phone . '"}';
                    exit; 
                } else {
                    throw new Exception("Database Transaction Failed");
                }
            } else {
                echo '{"status":"500","message":"Insufficient Balance"}';
                exit;
            }
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo '{"status":"500","message":"' . $e->getMessage() . '"}';
            exit;
        }
    }
}
}

