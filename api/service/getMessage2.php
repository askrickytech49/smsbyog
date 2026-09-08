<?php
date_default_timezone_set('Africa/Lagos');
include __DIR__ . '/../../include/config.php';
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
        $sql3 = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='2'");
        $api_data = mysqli_fetch_assoc($sql3);
        
        $sql4 = mysqli_query($conn, "SELECT balance, total_otp FROM user_wallet WHERE user_id='$user_id'");
        $user_data = mysqli_fetch_assoc($sql4);

        $number_id = $active_data['number_id']; // This is the 5sim activation ID
        $api_url = rtrim($api_data['api_url'], '/');
        $api_key = $api_data['api_key'];

        // 3. 5sim API Call to Check Order
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
                $add_balance = $user_data['balance'] + $active_data['service_price'];
                $cut_otp = $user_data['total_otp'] - 1;
                
                mysqli_query($conn, "UPDATE active_number SET active_status='1', status='3' WHERE id='" . $active_data['id'] . "'");
                mysqli_query($conn, "UPDATE user_wallet SET balance='$add_balance', total_otp='$cut_otp' WHERE user_id='$user_id'");
                
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