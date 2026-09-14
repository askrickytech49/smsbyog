<?php
date_default_timezone_set('Africa/Lagos');
include __DIR__ . '/../../include/config.php';
include_once __DIR__ . '/../../include/wallet_ledger.php';
$current_time_in_ist = date('Y-m-d H:i:s');
if (!isset($_GET['order_id']) || $_GET['order_id'] == "") {
  echo '{"status":"500","message":"Invalid Order id"}';
} elseif (!isset($_GET['token']) || $_GET['token'] == "") {
  echo '{"status":"500","message":"Token Blank"}';
} else {
  $token = mysqli_real_escape_string($conn, $_GET['token']);
  // $find_token = new radiumsahil();
  $check_token = check_token($token, $conn);
  // $find_token->closeConnection();
  if ($check_token === false) {
    echo '{"status":"500","message":"Token Expired Please Logout And Login Again"}';
} else {
    $user_id = $check_token;
    $order_id = mysqli_real_escape_string($conn, $_GET['order_id']);
    
    // 1. Get the local record
    $sql1 = mysqli_query($conn, "SELECT * FROM active_number WHERE order_id='$order_id' and active_status='2'");
    
    if (mysqli_num_rows($sql1) == 1) {
        $active_data = mysqli_fetch_assoc($sql1);
        
        // 2. Fetch API Configuration (ID 2 for 5sim)
        $api_url = "https://5sim.net";
$api_key = "eyJhbGciOiJSUzUxMiIsInR5cCI6IkpXVCJ9.eyJleHAiOjE4MjA2ODU5OTksImlhdCI6MTc4OTE0OTk5OSwicmF5IjoiYmMxNTVkYzI1NGNkZjlhZThlYTg3OTFjN2Y1MzYwNGEiLCJzdWIiOjQ0ODMyNjV9.PxCFWUR6bP29BpMt1PKfAdHSmdmXUQriKLq6nPYEkWldyephtuijh4BqnU_EtMTgxXdLXwmX-hNJKKBNEMZBG-p8WK1o6usLPOdTwWu3Lw0yOcS0e-YwPUxTPKu0ocZSdSP5FJtCUZMTKCTIe7nZmWBngLkyUmuPQzbNG12KF5JL0G6_G8_iG3WBQSMg7yeQF-13l6KOzc5aA56V4PdgiVHlTYbibicINth7evneW7I7pT_HLStvbUjLtgA8mEWsSvUFMEknyflUkwZi2Yo2sjSOEZH50Tc5KYz7iKFIq7p5KKmf3J_5pM7PFri1I8yXpSqUeEjUoGtN4QnDRCSRmg";
        // api_key is hardcoded above

        // 3. 5sim API Call to Check Order
        $number_id = $active_data['number_id'];
        $url = "{$api_url}/v1/user/check/{$number_id}";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $api_key",
            "Accept: application/json"
        ]);
        $response_json = curl_exec($ch);
        curl_close($ch);
        
        $response = json_decode($response_json, true);

        // --- Logic based on 5sim Statuses ---
        
        // STATUS: RECEIVED (SMS has arrived)
        if (isset($response['status']) && $response['status'] == 'RECEIVED') {
            $sms_array = $response['sms'] ?? [];
            $latest_sms = end($sms_array); // Get the last SMS received
            $code = $latest_sms['code'] ?? NULL;
            $full_text = $latest_sms['text'] ?? NULL;

            if ($active_data['sms_text'] != $code) {
                // Update local DB with the code and mark as success
                mysqli_query($conn, "UPDATE active_number SET sms_text='$code', status='1' WHERE id='" . $active_data['id'] . "'");
                
                // 5sim Auto-finishes when code is received, but you can call /finish if needed.
            }
            echo '{"status":"200","message":"Code Received","sms":"' . $code . '"}';

        // STATUS: PENDING (Waiting for SMS)
        } else if (isset($response['status']) && $response['status'] == 'PENDING') {
            echo '{"status":"300","message":"Waiting for SMS."}';

        // STATUS: CANCELED / TIMEOUT / BANNED
        } else if (isset($response['status']) && in_array($response['status'], ['CANCELED', 'TIMEOUT', 'BANNED'])) {
            
            // Refund the user if no SMS was ever received
            if ($active_data['sms_text'] == "") {
                mysqli_begin_transaction($conn);
                try {
                    $sql_wallet = mysqli_query($conn, "SELECT balance, total_otp FROM user_wallet WHERE user_id='$user_id' FOR UPDATE");
                    $wallet = mysqli_fetch_assoc($sql_wallet);
                    $cut_otp = max(0, $wallet['total_otp'] - 1);
                    
                    mysqli_query($conn, "UPDATE active_number SET active_status='1', status='3' WHERE id='" . (int)$active_data['id'] . "' AND active_status='2' AND status='2'");
                    refund_once($conn, (int)$user_id, $active_data['order_id'], $active_data['service_price'], 'getMessage2');
                    mysqli_commit($conn);
                } catch (Exception $e) {
                    mysqli_rollback($conn);
                }
                
                echo '{"status":"500","message":"Order ' . ucfirst(strtolower($response['status'])) . ' - Refunded"}';
            } else {
                // If SMS was already received but order expired/canceled, just close it locally
                mysqli_query($conn, "UPDATE active_number SET active_status='1' WHERE id='" . $active_data['id'] . "'");
                echo '{"status":"500","message":"Order Closed"}';
            }

        // STATUS: FINISHED
        } else if (isset($response['status']) && $response['status'] == 'FINISHED') {
             mysqli_query($conn, "UPDATE active_number SET active_status='1' WHERE id='" . $active_data['id'] . "'");
             echo '{"status":"200","message":"Order Completed","sms":"' . $active_data['sms_text'] . '"}';
        
        } else {
            echo '{"status":"300","message":"Waiting for SMS."}';
        }

    } else {
        echo '{"status":"500","message":"Invalid Order Id Or Already Processed"}';
    }
}
}