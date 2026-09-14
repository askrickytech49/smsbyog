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
    $provider_server = ($server === 'usa2') ? 'usa' : $server;
    $service  = mysqli_real_escape_string($conn, $_GET['service']);
    $requested_operator = isset($_GET['operator_id']) ? mysqli_real_escape_string($conn, strtolower(trim($_GET['operator_id']))) : '';
    $user_id  = $check_token;

    $api_url = "https://5sim.net";
    $api_key = "eyJhbGciOiJSUzUxMiIsInR5cCI6IkpXVCJ9.eyJleHAiOjE4MjA2ODU5OTksImlhdCI6MTc4OTE0OTk5OSwicmF5IjoiYmMxNTVkYzI1NGNkZjlhZThlYTg3OTFjN2Y1MzYwNGEiLCJzdWIiOjQ0ODMyNjV9.PxCFWUR6bP29BpMt1PKfAdHSmdmXUQriKLq6nPYEkWldyephtuijh4BqnU_EtMTgxXdLXwmX-hNJKKBNEMZBG-p8WK1o6usLPOdTwWu3Lw0yOcS0e-YwPUxTPKu0ocZSdSP5FJtCUZMTKCTIe7nZmWBngLkyUmuPQzbNG12KF5JL0G6_G8_iG3WBQSMg7yeQF-13l6KOzc5aA56V4PdgiVHlTYbibicINth7evneW7I7pT_HLStvbUjLtgA8mEWsSvUFMEknyflUkwZi2Yo2sjSOEZH50Tc5KYz7iKFIq7p5KKmf3J_5pM7PFri1I8yXpSqUeEjUoGtN4QnDRCSRmg";
    // Fetch rate and profit from DB
    $api_sql2 = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='2'");
    $api_data = $api_sql2 ? mysqli_fetch_assoc($api_sql2) : null;
    $conversion_rate = $api_data ? (float)$api_data['rate'] : 1500;
    $fixed_profit = $api_data ? (float)$api_data['profit_amount'] : 200;

    // 1. Fetch REAL-TIME PRICE from 5sim JSON API
    $price_url = "https://5sim.net/v1/guest/prices?country=" . urlencode($provider_server) . "&product=" . urlencode($service);

    $fetch_prices = function (bool $with_auth) use ($price_url, $api_key): array {
        $ch = curl_init($price_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $headers = ['Accept: application/json'];
        if ($with_auth) $headers[] = "Authorization: Bearer $api_key";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $body = curl_exec($ch);
        $http_code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = is_string($body) ? json_decode($body, true) : null;
        return [
            'http_code' => $http_code,
            'data' => is_array($decoded) ? $decoded : [],
        ];
    };

    $price_response = $fetch_prices(true);
    if ($price_response['http_code'] < 200 || $price_response['http_code'] >= 300 || !$price_response['data']) {
        $price_response = $fetch_prices(false);
    }
    $api_prices = $price_response['data'];

    // Parse operators data safely from response
    $operators_data = [];
    if (isset($api_prices[$provider_server][$service]) && is_array($api_prices[$provider_server][$service])) {
        $operators_data = $api_prices[$provider_server][$service];
    } elseif (isset($api_prices[$service][$provider_server]) && is_array($api_prices[$service][$provider_server])) {
        $operators_data = $api_prices[$service][$provider_server];
    } elseif (isset($api_prices[$service]) && is_array($api_prices[$service])) {
        $operators_data = $api_prices[$service];
    } elseif (isset($api_prices[$server]) && is_array($api_prices[$server])) {
        $operators_data = $api_prices[$server];
    }

    // Filter operators with active stock
    $available_operators = [];
    foreach ($operators_data as $op_name => $op_data) {
        // Skip excluded operators (admin toggle)
        if (is_array($op_data) && isset($op_data['cost']) && (int)($op_data['count'] ?? 0) > 0) {
            $available_operators[$op_name] = [
                'name'  => $op_name,
                'cost'  => (float)$op_data['cost'],
                'count' => (int)$op_data['count'],
            ];
        }
    }

    if (empty($available_operators)) {
        echo json_encode([
            "status" => "500",
            "message" => "No numbers currently available for " . ucfirst($service) . " in " . ucfirst($server) . ". Please try another country."
        ]);
        exit;
    }

    // Determine target operator:
    $target_operator = null;
    if ($requested_operator !== '' && $requested_operator !== 'any') {
        if (isset($available_operators[$requested_operator])) {
            $target_operator = $available_operators[$requested_operator];
        } else {
            // The selected operator ran out of stock between page load and click
            echo json_encode([
                "status" => "500",
                "message" => "Numbers for " . ucfirst($service) . " are currently out of stock. Please refresh the page to view current availability."
            ]);
            exit;
        }
    } else {
        // Fallback to the cheapest available operator with stock
        $sorted_by_price = array_values($available_operators);
        usort($sorted_by_price, function($a, $b) {
            return ($a['cost'] < $b['cost']) ? -1 : 1;
        });
        $target_operator = $sorted_by_price[0];
    }

    $target_op_name = $target_operator['name'];
    $raw_api_price  = $target_operator['cost'];

    // 2. Calculate Final Price matching the chosen operator
    $base_price_naira = $raw_api_price * $conversion_rate;
    $base_price = $base_price_naira + $fixed_profit;
    $service_price = custom_price($user_id, $service, $server, $base_price, $conn);
    $service_price = round($service_price, 2);

    // 3. Wallet Check
    $sql2 = mysqli_query($conn, "SELECT balance FROM user_wallet WHERE user_id='$user_id'");
    $user_wallet = mysqli_fetch_assoc($sql2);
    if ($user_wallet['balance'] < $service_price) {
        echo json_encode([
            "status" => "500",
            "message" => "Insufficient Balance. Amount Required: ₦" . number_format($service_price, 2) . ". Kindly Top-Up Your Balance"
        ]);
        exit;
    }

    // 4. BUY: Never use 'any'! Always request the specific operator to prevent 5sim overcharging.
    // Allow fallback only to other operators that have the exact same price or lower.
    $operators_to_try = [$target_op_name];
    foreach ($available_operators as $op_name => $op_info) {
        if ($op_name !== $target_op_name && $op_info['cost'] <= $raw_api_price) {
            $operators_to_try[] = $op_name;
        }
    }

    $response = null;
    $result = '';
    $last_error = '';
    $purchased_operator = $target_op_name;

    foreach ($operators_to_try as $try_operator) {
        $encoded_server = urlencode($server);
        $encoded_op     = urlencode($try_operator);
        $encoded_svc    = urlencode($service);
        $buy_url = "{$api_url}/v1/user/buy/activation/" . urlencode($provider_server) . "/{$encoded_op}/{$encoded_svc}";
        
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
            $purchased_operator = $try_operator;
            break;
        }

        $last_error = trim($result) ?: 'API_LIMIT_OR_NO_NUMBERS';
        $response = null;
    }

    // If all operators at this price failed
    if (!$response || !isset($response['id'])) {
        $country_display = ucfirst(str_replace('_', ' ', $server));
        error_log("5sim purchase failed: response=" . $result . "; service={$service}; country={$server}; operator={$target_op_name}");
        echo json_encode([
            "status" => "500",
            "message" => "Numbers for " . ucfirst($service) . " in " . $country_display . " are currently out of stock. Please try again in a few moments or select another country."
        ]);
        exit;
    } else {
        $operator = $purchased_operator;
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
                
                $service_display = ucfirst($service);
                
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

