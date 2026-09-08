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
     
      
      $sql3 = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='3'");
      $api_data = mysqli_fetch_assoc($sql3);
      $sql4 = mysqli_query($conn, "SELECT * FROM user_wallet WHERE user_id='$user_id'");
      $user_data = mysqli_fetch_assoc($sql4);
      $user_balance = $user_data['balance'];
      $user_otp = $user_data['total_otp'];
      $number_id = $active_data['number_id']; // This is the transaction_id
        $api_url = $api_data['api_url'];
        $api_key = $api_data['api_key'];
              
        // 1. Fetch SMS Code using Header Authentication
        $rent_url = "{$api_url}/sms-otp/status/$number_id";
        $data = getfunction($rent_url, $api_key); 
        
        if (!$data['transaction_id']) {
            echo '{"status":"500","message":"Error order id "}';
            exit;
        }
        if (isset($data['status']) && !empty($data['otp_code']) && $data['status'] == 'completed') {
            $sms = $data['otp_code'];
            if ($active_data['sms_text'] != $sms) {
                // Update local DB and mark as finished
                mysqli_query($conn, "UPDATE active_number SET sms_text='$sms', status='1', active_status='1' WHERE id='" . $active_data['id'] . "'");
            }
            echo '{"status":"200","message":"Code Receive","sms":"' . $sms . '"}';
        } 
        
        else if (isset($data['status']) && $data['status'] == 'active') {
            if ($active_data['sms_text'] != "") {
                echo '{"status":"200","message":"Code Receive","sms":"' . $active_data['sms_text'] . '"}';
            } else {
                echo '{"status":"300","message":"Waiting for SMS."}';
            }
        } 
        
        else if (isset($data['status']) && $data['status'] == 'refunded') {
            if ($active_data['sms_text'] != "") {
                // If we already have a code, just close the order locally
                mysqli_query($conn, "UPDATE active_number SET active_status='1', status='1' WHERE id='" . $active_data['id'] . "'");
                echo '{"status":"200","message":"Code Receive","sms":"' . $active_data['sms_text'] . '"}';
            } else {
                // No code received, perform local refund
                if ($active_data['status'] == 2) {

                    // SAFE UPDATE FIRST
                    $update = mysqli_query($conn, "
                        UPDATE active_number 
                        SET 
                            active_status = '1',
                            status = '3'
                        WHERE 
                            id = '" . (int)$active_data['id'] . "'
                        AND 
                            status != '3'
                        LIMIT 1
                    ");
                
                    // ONLY REFUND IF UPDATE ACTUALLY HAPPENED
                    if (mysqli_affected_rows($conn) > 0) {
                
                        // REFRESH USER WALLET
                        $walletQuery = mysqli_query($conn, "
                            SELECT balance,total_otp 
                            FROM user_wallet 
                            WHERE user_id='$user_id' 
                            LIMIT 1
                        ");
                
                        $walletData = mysqli_fetch_assoc($walletQuery);
                
                        $add_balance = $walletData['balance'] + $active_data['service_price'];
                        $cut_otp     = max(0, $walletData['total_otp'] - 1);
                
                        // UPDATE WALLET
                        mysqli_query($conn, "
                            UPDATE user_wallet 
                            SET 
                                balance='$add_balance',
                                total_otp='$cut_otp'
                            WHERE user_id='$user_id'
                            LIMIT 1
                        ");
                
                        echo '{"status":"500","message":"Number Canceled/Expired. Refunded."}';
                
                    } else {

                        // ALREADY REFUNDED BEFORE
                        echo '{"status":"500","message":"Already Processed"}';
                    }
                }  else {
                    echo '{"status":"500","message":"Error: Order already cancel and refunded"}';
                }
            }
        }
        // DEFAULT: Fallback or internal error handling
        else {
            // Check local timer — USA/CA server uses 8-minute window
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