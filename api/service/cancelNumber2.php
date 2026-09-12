<?php
include_once __DIR__ . '/../../include/config.php';

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
    
    // Fetch order that is still active
    $sql1 = mysqli_query($conn, "SELECT * FROM active_number WHERE order_id='$order_id' and active_status='2'");
    
    if (mysqli_num_rows($sql1) == 1) {
        $active_data = mysqli_fetch_assoc($sql1);

        // --- 1. Wait Time Check Logic ---
        function check_wait($active_data, $conn) {
            if ($active_data['sms_text'] == "") {
                $sql30 = mysqli_query($conn, "SELECT * FROM time_wait WHERE server_id='" . $active_data['server_id'] . "' and service_id='" . $active_data['service_id'] . "'");
                if (mysqli_num_rows($sql30) == 1) {
                    $wait_data = mysqli_fetch_assoc($sql30);
                    $givenTime = strtotime($active_data['buy_time']);
                    $currentTime = time();
                    $twoMinutesInSeconds = $wait_data['wait_sec'];
                    $timeLeftSeconds = ($givenTime + $twoMinutesInSeconds) - $currentTime;
                    
                    if ($timeLeftSeconds > 0) {
                        $minutes = ceil($timeLeftSeconds / 60);
                        return '{"status":"600","message":"You Can Cancel Number After ' . $minutes . ' Min"}';
                    }
                }
            }
            return "";
        }

        $waiting = check_wait($active_data, $conn);
        if ($waiting != "") {
            echo $waiting;
            exit;
        }

        // --- 2. 5sim API Cancel Logic ---
        $sql4 = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='2'"); // 5sim is ID 2
        $api_data = mysqli_fetch_assoc($sql4);
        
        $api_url = rtrim($api_data['api_url'], '/');
        $api_key = $api_data['api_key'];
        $number_id = $active_data['number_id'];

        // 5sim Endpoint: /v1/user/cancel/{id}
        $url = "{$api_url}/v1/user/cancel/{$number_id}";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $api_key",
            "Accept: application/json"
        ]);
        $result = curl_exec($ch);
        $response = json_decode($result, true);
        curl_close($ch);

        // Note: 5sim returns status 'CANCELED' or 'BANNED' on success
        $raw_result = strtolower(trim($result));
        if (isset($response['status']) || $raw_result == "order not found" || $raw_result == "already canceled") {
            
            mysqli_begin_transaction($conn);
            try {
                // Re-verify order status inside transaction
                $sql_lock = mysqli_query($conn, "SELECT * FROM active_number WHERE order_id='$order_id' AND active_status='2' FOR UPDATE");
                
                if (mysqli_num_rows($sql_lock) == 1) {
                    $order_to_cancel = mysqli_fetch_assoc($sql_lock);
                    
                    // IF NO SMS: Refund user
                    if ($order_to_cancel['sms_text'] == "") {
                        $sql_user = mysqli_query($conn, "SELECT balance, total_otp FROM user_wallet WHERE user_id='$user_id' FOR UPDATE");
                        $user_wallet = mysqli_fetch_assoc($sql_user);
                        
                        $refund_amount = $order_to_cancel['service_price'];
                        $new_balance = $user_wallet['balance'] + $refund_amount;
                        $new_otp_count = $user_wallet['total_otp'] - 1;

                        // Update Wallet & Order Status
                        mysqli_query($conn, "UPDATE user_wallet SET balance='$new_balance', total_otp='$new_otp_count' WHERE user_id='$user_id'");
                        mysqli_query($conn, "UPDATE active_number SET active_status='1', status='3' WHERE id='" . $order_to_cancel['id'] . "'");
                        
                        mysqli_commit($conn);
                        echo '{"status":"200","message":"Number Cancelled & Refunded"}';
                    } else {
                        // IF SMS RECEIVED: Cannot refund, just close order
                        mysqli_query($conn, "UPDATE active_number SET active_status='1', status='1' WHERE id='" . $order_to_cancel['id'] . "'");
                        mysqli_commit($conn);
                        echo '{"status":"200","message":"Order Closed (SMS was received)"}';
                    }
                } else {
                    echo '{"status":"500","message":"Already Cancelled"}';
                }
            } catch (Exception $e) {
                mysqli_rollback($conn);
                echo '{"status":"500","message":"Database Error During Cancellation"}';
            }
        } else {
            // It failed. Either we got JSON with errors, or a plain text error string
            $err_msg = $response['errors'] ?? $response['message'] ?? ($result ? trim($result) : 'API Cancellation Failed');
            echo '{"status":"500","message":"API Error: ' . htmlspecialchars($err_msg) . '"}';
        }

    } else {
        echo '{"status":"500","message":"Invalid Order Id Or Already Cancelled"}';
    }
}
}
?>