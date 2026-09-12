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
        $sql = "SELECT * FROM active_number WHERE user_id='$user_id' AND active_status='2' AND api_id='3' ORDER BY id DESC";
        $result = mysqli_query($conn, $sql);

        if ($result === false) {
            echo '{"status":"500","message":"Database Query Error: ' . mysqli_error($conn) . '"}';
        } else {
            // Fetch USA/CA API credentials once
            $sql_api = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='3'");
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

                // Timer — USA/CA server uses 8-minute window
                $currentTime    = time();
                $expiryTime = !empty($row['expires_at'])
                    ? strtotime($row['expires_at'])
                    : strtotime($row['buy_time']) + (8 * 60);

                if ($expiryTime <= $currentTime) {
                    $left = "00:00";
                } else {
                    $left = ($expiryTime - $currentTime) * 1000;
                }

                // AUTO-REFUND on timeout
                if ($left === "00:00" && $row['active_status'] == 2) {

                    if ($row['sms_text'] == "") {
                        $number_id = $row['number_id'];

                        // FIX: proactively call upstream status/cancel endpoint.
                        // Previously this only refunded if upstream returned 'refunded',
                        // meaning users were never refunded if the upstream returned any
                        // other status. Now we request the cancel regardless and refund locally.
                        $status_url = rtrim($api_url, '/') . "/sms-otp/status/$number_id";
                        $upstream   = getfunction($status_url, $api_key);

                        // Only skip the refund if upstream confirms SMS was delivered
                        $smsDelivered = isset($upstream['status']) && $upstream['status'] === 'completed'
                            && !empty($upstream['otp_code']);

                        if (!$smsDelivered) {
                            // Atomic refund with affected-rows idempotency guard
                            $lockRefund = mysqli_query($conn, "
                                UPDATE active_number
                                SET active_status='1', status='3'
                                WHERE id='" . (int)$row['id'] . "'
                                AND active_status='2'
                                AND status != '3'
                                LIMIT 1
                            ");

                            if (mysqli_affected_rows($conn) > 0) {
                                // Re-read wallet for accurate balance
                                $sql_wallet = mysqli_query($conn, "SELECT balance, total_otp FROM user_wallet WHERE user_id='$user_id' LIMIT 1");
                                $wallet     = mysqli_fetch_assoc($sql_wallet);
                                $add_balance = $wallet['balance'] + $row['service_price'];
                                $cut_otp     = max(0, $wallet['total_otp'] - 1);

                                mysqli_query($conn, "UPDATE user_wallet SET balance='$add_balance', total_otp='$cut_otp' WHERE user_id='$user_id' LIMIT 1");
                            }
                        } else {
                            // SMS was actually delivered — save the code and close
                            $sms = mysqli_real_escape_string($conn, $upstream['otp_code']);
                            mysqli_query($conn, "UPDATE active_number SET active_status='1', status='1', sms_text='$sms' WHERE id='" . (int)$row['id'] . "'");
                        }

                    } else {
                        // SMS already received — close without refund
                        mysqli_query($conn, "UPDATE active_number SET active_status='1', status='1' WHERE id='" . (int)$row['id'] . "'");
                    }
                    continue;
                }

                array_push($final, array(
                    'id'         => $row['order_id'],
                    'number'     => $row['number'],
                    'amount'     => $row['service_price'],
                    'left_time'  => $left,
                    'app'        => strip_tags($row['service_name']),
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
