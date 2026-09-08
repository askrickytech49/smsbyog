<?php
date_default_timezone_set('Africa/Lagos');
include __DIR__ . '/../../include/config.php';

if (!isset($_GET['token']) || $_GET['token'] == "") {
    echo '{"status":"500","message":"Token Blank"}';
} else {
    $token = mysqli_real_escape_string($conn, $_GET['token']);
    $check_token = check_token($token, $conn);
    if ($check_token === false) {
        echo '{"status":"500","message":"Token Expired Please Logout And Login Again"}';
    } else {
        $user_id = $check_token;
        $sql = "SELECT * FROM active_number WHERE user_id='$user_id' AND active_status='2' AND api_id='1' ORDER BY id DESC";
        $result = mysqli_query($conn, $sql);

        if ($result === false) {
            echo '{"status":"500","message":"Database Query Error: ' . mysqli_error($conn) . '"}';
        } else {
            // Fetch USA API credentials once
            $sql_api = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='1'");
            $api_data = mysqli_fetch_assoc($sql_api);
            $api_url  = $api_data['api_url'];
            $api_key  = $api_data['api_key'];

            $final = array();

            while ($row = mysqli_fetch_array($result)) {
                // Logo
                $sql_logo = mysqli_query($conn, "SELECT img_url FROM service_icon WHERE short_code='" . mysqli_real_escape_string($conn, $row['service_id']) . "'");
                $logo_url = (mysqli_num_rows($sql_logo) > 0)
                    ? mysqli_fetch_assoc($sql_logo)['img_url']
                    : "https://i.ibb.co/ySRhxqh/default.png";

                // Timer — USA server uses 8-minute window
                $givenTime      = strtotime($row['buy_time']);
                $currentTime    = time();
                $timeoutSeconds = 8 * 60;
                $expiryTime     = $givenTime + $timeoutSeconds;

                if ($expiryTime <= $currentTime) {
                    $left = "00:00";
                } else {
                    $left = ($expiryTime - $currentTime) * 1000;
                }

                // AUTO-REFUND on timeout
                if ($left === "00:00" && $row['active_status'] == 2) {

                    if ($row['sms_text'] == "") {
                        $number_id = $row['number_id'];

                        // FIX: trim trailing space from cancel URL
                        $cancel_url = rtrim($api_url, '/') . "/api/cancel";
                        $ch = curl_init($cancel_url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['transaction_id' => $number_id]));
                        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                            "API-KEY: $api_key",
                            "Accept: application/json",
                            "Content-Type: application/json"
                        ]);
                        curl_exec($ch);
                        curl_close($ch);

                        // Atomic refund with FOR UPDATE lock
                        mysqli_begin_transaction($conn);
                        try {
                            $lock = mysqli_query($conn, "SELECT id, sms_text, service_price FROM active_number WHERE id='" . (int)$row['id'] . "' AND active_status='2' AND status='2' LIMIT 1 FOR UPDATE");
                            if (mysqli_num_rows($lock) == 1) {
                                $locked = mysqli_fetch_assoc($lock);
                                if ($locked['sms_text'] == "") {
                                    $sql_wallet = mysqli_query($conn, "SELECT balance, total_otp FROM user_wallet WHERE user_id='$user_id' FOR UPDATE");
                                    $wallet = mysqli_fetch_assoc($sql_wallet);
                                    $add_balance = $wallet['balance'] + $locked['service_price'];
                                    $cut_otp     = max(0, $wallet['total_otp'] - 1);

                                    mysqli_query($conn, "UPDATE active_number SET active_status='1', status='3' WHERE id='" . (int)$locked['id'] . "'");
                                    mysqli_query($conn, "UPDATE user_wallet SET balance='$add_balance', total_otp='$cut_otp' WHERE user_id='$user_id'");
                                } else {
                                    // SMS arrived between reads — close without refund
                                    mysqli_query($conn, "UPDATE active_number SET active_status='1', status='1' WHERE id='" . (int)$locked['id'] . "'");
                                }
                            }
                            mysqli_commit($conn);
                        } catch (Exception $e) {
                            mysqli_rollback($conn);
                        }

                    } else {
                        // FIX: was commented out — SMS received, close the record
                        mysqli_query($conn, "UPDATE active_number SET active_status='1', status='1' WHERE id='" . (int)$row['id'] . "'");
                    }
                    continue;
                }

                array_push($final, array(
                    'id'         => $row['order_id'],
                    'number'     => $row['number'],
                    'amount'     => $row['service_price'],
                    'left_time'  => $left,
                    'app'        => $row['service_name'],
                    'sms'        => $row['sms_text'],
                    'service_id' => $row['service_id'],
                    'server_id'  => $row['server_id'],
                    'logo_url'   => $logo_url,
                ));
            }

            echo json_encode(['status' => '200', 'data' => $final]);
        }
        mysqli_close($conn);
    }
}
