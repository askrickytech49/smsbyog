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
    $server = mysqli_real_escape_string($conn, $_GET['server']); // Local DB ID
    $service = mysqli_real_escape_string($conn, $_GET['service']); // Service ID (e.g. 'zf')

    $user_id = $check_token;
    
    $sql4 = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='3'");
    $api_data = mysqli_fetch_assoc($sql4);
    $api_key = $api_data['api_key'];
    $api_url = $api_data['api_url'];
    $conversion_rate = $api_data['rate'];
    $fixed_profit = $api_data['profit_amount'];
    
    $price_url = $api_data['api_url'] . '/sms-otp/services';
    $api_prices = getfunction($price_url, $api_data['api_key']);
    
    $raw_api_price = 0;
    $service_display_name = $service;
    
    if (is_array($api_prices)) {
    
        foreach ($api_prices as $s_data) {
    
            if (isset($s_data['service_code']) && $s_data['service_code'] == $service ) {
                $raw_api_price = (float)($s_data['price_usd'] ?? 0);
                $service_display_name = $s_data['service_name'] ?? $service;
                break;
            }
        }
    }
    
        if ($raw_api_price <= 0) {
            echo json_encode(["status" => "500", "message" => "This service is temporarily unavailable. Please try again later."]);
         exit;
    }
    
    // 2. Calculate Final Price
    $base_price_naira = $raw_api_price * $conversion_rate;

    // Add fixed profit
    $base_price = $base_price_naira + $fixed_profit;
    $service_price = custom_price($user_id, $service, $server, $base_price, $conn);
    $service_price = round($service_price, 2);
    
    // 3. Wallet Check
    $sql2 = mysqli_query($conn, "SELECT balance FROM user_wallet WHERE user_id='$user_id'");
    $user_wallet = mysqli_fetch_assoc($sql2);
    if ($user_wallet['balance'] < $service_price) {
        echo '{"status":"500","message":"Insufficient Balance. Required: '.$service_price.'"}';
        exit;
    }
    
    // 4. Request the number and preserve the provider response for diagnosis.
        $rent_url = "{$api_url}/sms-otp/request";
        $payload = json_encode([
            'serviceCode' => $service,
            'countryCode' => $server,
        ]);
        $ch = curl_init($rent_url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'X-API-Key: ' . $api_key,
                'Accept: application/json',
                'Content-Type: application/json',
            ],
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $raw_response = curl_exec($ch);
        $curl_error = curl_error($ch);
        $http_code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $response = $raw_response ? json_decode($raw_response) : null;

        if (!isset($response->transaction_id) || !isset($response->phone_number) || empty($response->transaction_id) || empty($response->phone_number)) {
            $provider_message = '';
            if (is_object($response)) {
                $provider_message = $response->message ?? $response->error ?? $response->detail ?? '';
            }
            $provider_message = trim((string)$provider_message);
            $diagnostic_text = strtolower($provider_message . ' ' . (string)$raw_response);
            if ($curl_error !== '' || $http_code >= 500) {
                $message = 'The service is temporarily unavailable. Please try again later.';
            } elseif ($http_code === 401 || $http_code === 403) {
                $message = 'This service is temporarily unavailable. Please try again later.';
            } elseif (preg_match('/stock|available|number|inventory|country|operator|out of|not found/', $diagnostic_text)) {
                $message = 'No numbers are currently available for this service. Please try again later or choose another country.';
            } else {
                $message = 'We could not complete this request. Please try again later.';
            }
            error_log("Dino purchase failed: HTTP {$http_code}; service={$service}; country={$server}; response=" . (string)$raw_response);
            echo json_encode(["status" => "500", "message" => $message]);
            exit;
        }
        
        // --- Purchase Successful (HTTP 200) ---
        $api_order_id = $response->transaction_id;
        $phone_number = $response->phone_number;
        $random_order = generateRandomString();
        // Always use local time + 8 minutes for expiry.
        // DinoMMO returns expires_at in UTC which causes timezone mismatch
        // with buy_time (saved in Africa/Lagos). Using local +8min keeps it consistent.
        $expires_at = date('Y-m-d H:i:s', strtotime('+8 minutes'));
        
        mysqli_begin_transaction($conn, MYSQLI_TRANS_START_READ_WRITE);
        try {
            $sql200 = mysqli_query($conn, "SELECT balance, total_otp FROM user_wallet WHERE user_id='$user_id' FOR UPDATE");
            $user_data = mysqli_fetch_assoc($sql200);
            
            if ($user_data['balance'] >= $service_price) {
                $cut_balance = $user_data['balance'] - $service_price;
                $add_otp = $user_data['total_otp'] + 1;
                $current_time = date('Y-m-d H:i:s');
        
                // Update Wallet
                $sql5 = mysqli_query($conn, "UPDATE user_wallet SET balance='$cut_balance', total_otp='$add_otp' WHERE user_id='$user_id'");
                
                // Insert Active Number
                $sql6 = mysqli_query($conn, "INSERT INTO active_number(user_id, api_id, number_id, number, server_id, service_id, order_id, buy_time, expires_at, status, sms_text, service_price, service_name, active_status) 
                VALUES ('$user_id', '3', '$api_order_id', '$phone_number', '$server', '$service', '$random_order', '$current_time', '$expires_at', '2', '', '$service_price', '$service_display_name', '2')");
                
                if ($sql5 && $sql6) {
                    mysqli_commit($conn); 
                    if (ob_get_length()) ob_clean(); 
                    echo json_encode([
                        "status" => "200", 
                        "message" => "Number Purchased", 
                        "res" => "ACCESS_NUMBER:$random_order:$phone_number"
                    ]);
                    exit; 
                } else {
                    throw new Exception("Database Update Failed");
                }
            } else {
                echo json_encode(["status" => "500", "message" => "Insufficient Balance"]);
                exit;
            }
        } catch (Exception $e) {
            mysqli_rollback($conn);
            if (ob_get_length()) ob_clean();
            echo json_encode(["status" => "500", "message" => $e->getMessage()]);
            exit;
        }
    }
}

