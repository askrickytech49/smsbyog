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
    $sql1 = mysqli_query($conn, "SELECT * FROM active_number WHERE order_id='$order_id'");
    if (mysqli_num_rows($sql1) == 1) {
      $active_data = mysqli_fetch_assoc($sql1);
     
      
      $sql3 = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='1'");
      $api_data = mysqli_fetch_assoc($sql3);
      $sql4 = mysqli_query($conn, "SELECT * FROM user_wallet WHERE user_id='$user_id'");
      $user_data = mysqli_fetch_assoc($sql4);
      $user_balance = $user_data['balance'];
      $user_otp = $user_data['total_otp'];
      $number_id = $active_data['number_id']; // This is the transaction_id
        $api_url = $api_data['api_url'];
        $api_key = $api_data['api_key'];
              
        // 1. Fetch SMS Code using Header Authentication
        $url = "{$api_url}/api/code?transaction_id={$number_id}";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "API-KEY: $api_key",
            "Accept: application/json"
        ]);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
                
        $response = trim($response);
        
        // --- LOGIC START ---
        
        // SUCCESS: Code received (HTTP 200)
        if ($http_code == 200) {
            $sms = mysqli_real_escape_string($conn, $response);
            if ($active_data['sms_text'] != $sms) {
                // Update local DB and mark as finished
                mysqli_query($conn, "UPDATE active_number SET sms_text='$sms', status='1', active_status='1' WHERE id='" . $active_data['id'] . "'");
            }
            echo '{"status":"200","message":"Code Receive","sms":"' . $sms . '"}';
        } 
        
        // WAITING: Code not received yet (HTTP 409)
        else if ($http_code == 409) {
            if ($active_data['sms_text'] != "") {
                echo '{"status":"200","message":"Code Receive","sms":"' . $active_data['sms_text'] . '"}';
            } else {
                echo '{"status":"300","message":"Waiting for SMS."}';
            }
        } 
        
        // CANCELLED/EXPIRED: (HTTP 410, 404, or 400)
        else if (in_array($http_code, [410, 404, 400])) {
            if ($active_data['sms_text'] != "") {
                // If we already have a code, just close the order locally
                mysqli_query($conn, "UPDATE active_number SET active_status='1', status='1' WHERE id='" . $active_data['id'] . "'");
                echo '{"status":"200","message":"Code Receive","sms":"' . $active_data['sms_text'] . '"}';
            } else {
                // No code received, perform local refund
                if ($active_data['status'] == 2) {
                    $add_balance = $user_data['balance'] + $active_data['service_price'];
                    $cut_otp = $user_data['total_otp'] - 1;
                    
                    // Mark as Cancelled (Status 3) and Refund Wallet
                    mysqli_query($conn, "UPDATE active_number SET active_status='1', status='3' WHERE id='" . $active_data['id'] . "'");
                    mysqli_query($conn, "UPDATE user_wallet SET balance='$add_balance', total_otp='$cut_otp' WHERE user_id='$user_id'");
        
                    // --- CALL CANCEL API ---
                    // Even if it already expired, we send a cancel request to be safe
                    $rent_url = "{$api_url}/api/cancel ";
                    $ch = curl_init($rent_url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POST, 1); // Set as POST request
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                        'transaction_id' => $number_id 
                    ]));
                    
                    // Set the API Key in the Header
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        "API-KEY: $api_key",
                        "Accept: application/json"
                    ]);
                    
                    $result = curl_exec($ch);
                    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    
                    $response = json_decode($result, true);
                    
                    echo '{"status":"500","message":"Number Canceled/Expired. Refunded."}';
                } else {
                    echo '{"status":"500","message":"Error: Order already processed."}';
                }
            }
        } 
        
        // DEFAULT: Fallback or internal error handling
        else {
            // Check local timer — USA server uses 8-minute window
            $givenTime = strtotime($active_data['buy_time']);
            $currentTime = time();
            $eightMinutesInSeconds = 8 * 60;

            if (($givenTime + $eightMinutesInSeconds) <= $currentTime) {
                $left = "00:00";
            } else {
                $timeLeftSeconds = ($givenTime + $eightMinutesInSeconds) - $currentTime;
                $left = $timeLeftSeconds * 1000;
            }
               
            if ($active_data['sms_text'] != "") {
                echo '{"status":"200","message":"Code Receive","sms":"' . $active_data['sms_text'] . '"}';
            } else {
                echo '{"status":"300","message":"Waiting for SMS."}';
            }
        }
      
    } else {
      echo '{"status":"500","message":"Invalid Order Id Or Cancelled #1"}';
    }
  }
}