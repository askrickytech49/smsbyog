<?php
include __DIR__ . '/../../include/config.php';

if(!isset($_GET['token']) || $_GET['token'] == "") {
echo'{"status":"500","message":"Token Blank"}';

} else {
$token = mysqli_real_escape_string($conn,$_GET['token']); 
// $find_token = new radiumsahil();
$check_token = check_token($token, $conn);
// $find_token->closeConnection();
if ($check_token === false) {
    echo '{"status":"500","message":"Token Expired Please Logout And Login Again"}';
} else {
    $user_id = $check_token;
    // Fetch active numbers specifically for API ID 2 (5sim)
    $sql = "SELECT * FROM active_number WHERE user_id = '$user_id' AND active_status = '2' AND api_id = '2' ORDER BY id DESC";
    $result = mysqli_query($conn, $sql);

    if ($result === false) {
        echo '{"status":"500","message":"Database Query Error: ' . mysqli_error($conn) . '"}';
    } else {
        $final = array();
        
        // Fetch 5sim API details once to save database resources
        $sql_api = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='2'");
        $api_data = mysqli_fetch_assoc($sql_api);
        $api_key = $api_data['api_key'];
        $api_url = rtrim($api_data['api_url'], '/');

        while ($row = mysqli_fetch_array($result)) {
            // Logo Logic
            $sql_logo = mysqli_query($conn, "SELECT img_url FROM service_icon WHERE short_code='" . mysqli_real_escape_string($conn, $row['service_id']) . "'");
            $logo_url = (mysqli_num_rows($sql_logo) > 0) ? mysqli_fetch_assoc($sql_logo)['img_url'] : "https://i.ibb.co/ySRhxqh/default.png";

            // Timer — use stored expires_at if available, else fall back to buy_time + 20min
            $currentTime = time();
            if (!empty($row['expires_at'])) {
                $expiryTime = strtotime($row['expires_at']);
            } else {
                $expiryTime = strtotime($row['buy_time']) + (20 * 60);
            }

            if ($expiryTime <= $currentTime) {
                $left = "00:00";
            } else {
                $timeLeftSeconds = $expiryTime - $currentTime;
                $left = $timeLeftSeconds * 1000; 
            }

            // AUTO-REFUND LOGIC (If time is up)
            if ($left == "00:00" && $row['active_status'] == 2) {
                
                // 1. If no SMS received, attempt to cancel on 5sim and refund
                if (empty($row['sms_text'])) {
                    $number_id = $row['number_id'];
                    $cancel_url = "{$api_url}/v1/user/cancel/{$number_id}";
                    
                    $ch = curl_init($cancel_url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        "Authorization: Bearer $api_key",
                        "Accept: application/json"
                    ]);
                    $cancel_res = curl_exec($ch);
                    curl_close($ch);

                    // Proceed with refund in local DB
                    mysqli_begin_transaction($conn);
                    try {
                        $sql_wallet = mysqli_query($conn, "SELECT balance, total_otp FROM user_wallet WHERE user_id='$user_id' FOR UPDATE");
                        $user_wallet = mysqli_fetch_assoc($sql_wallet);
                        
                        $add_balance = $user_wallet['balance'] + $row['service_price'];
                        $cut_otp = $user_wallet['total_otp'] - 1;

                        // Mark as Cancelled/Refunded (status 3)
                        mysqli_query($conn, "UPDATE active_number SET active_status='1', status='3' WHERE id='" . $row['id'] . "'");
                        mysqli_query($conn, "UPDATE user_wallet SET balance='$add_balance', total_otp='$cut_otp' WHERE user_id='$user_id'");
                        
                        mysqli_commit($conn);
                    } catch (Exception $e) {
                        mysqli_rollback($conn);
                    }
                } else {
                    // SMS was received, but time expired. Just mark as completed (status 1)
                    mysqli_query($conn, "UPDATE active_number SET active_status='1', status='1' WHERE id='" . $row['id'] . "'");
                }
                continue; // Skip adding this to the active list
            }

            // Add valid active orders to the array
            array_push($final, array(
                'id' => $row['order_id'],
                'number' => $row['number'],
                'amount' => $row['service_price'],
                'left_time' => $left,
                'app' => strip_tags($row['service_name']),
                'sms' => $row['sms_text'],
                'service_id' => $row['service_id'],
                'server_id' => $row['server_id'],
                'logo_url' => $logo_url,
            ));
        }

        echo json_encode([
            'status' => '200',
            'data' => $final
        ]);
    }
    mysqli_close($conn);
}
}


