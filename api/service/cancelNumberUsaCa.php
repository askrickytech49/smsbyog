<?php
date_default_timezone_set('Africa/Lagos');
include_once __DIR__ . '/../../include/config.php';

if (!isset($_GET['order_id']) || $_GET['order_id'] == "") {
    echo '{"status":"500","message":"Invalid Order id"}';
} elseif (!isset($_GET['token']) || $_GET['token'] == "") {
    echo '{"status":"500","message":"Token Blank"}';
} else {
    $token       = mysqli_real_escape_string($conn, $_GET['token']);
    $check_token = check_token($token, $conn);
    if ($check_token === false) {
        echo '{"status":"500","message":"Token Expired Please Logout And Login Again"}';
    } else {
        $user_id  = $check_token;
        $order_id = mysqli_real_escape_string($conn, $_GET['order_id']);

        $sql1 = mysqli_query($conn, "SELECT * FROM active_number WHERE order_id='$order_id' AND active_status='2'");
        if (mysqli_num_rows($sql1) != 1) {
            echo '{"status":"500","message":"Invalid Order Id Or Already Cancelled"}';
        } else {
            $active_data = mysqli_fetch_assoc($sql1);

            // --- Minimum wait time check ---
            if ($active_data['sms_text'] == "") {
                $sql_wait = mysqli_query($conn, "SELECT * FROM time_wait WHERE server_id='" . $active_data['server_id'] . "' AND service_id='" . $active_data['service_id'] . "'");
                if (mysqli_num_rows($sql_wait) == 1) {
                    $wait_data       = mysqli_fetch_assoc($sql_wait);
                    $givenTime       = strtotime($active_data['buy_time']);
                    $waitUntil       = $givenTime + $wait_data['wait_sec'];
                    $timeLeftSeconds = $waitUntil - time();
                    if ($timeLeftSeconds > 0) {
                        $minutes = ceil($timeLeftSeconds / 60);
                        echo '{"status":"600","message":"You Can Cancel Number After ' . $minutes . ' Min"}';
                        exit;
                    }
                }
            }

            // --- SMS already received — just close the record, no refund ---
            if ($active_data['sms_text'] != "") {
                $sql_close = mysqli_query($conn, "SELECT active_status FROM active_number WHERE order_id='$order_id'");
                $row_close = mysqli_fetch_assoc($sql_close);
                if ($row_close['active_status'] == 1) {
                    echo '{"status":"500","message":"Already Cancelled"}';
                } else {
                    mysqli_query($conn, "UPDATE active_number SET active_status='1', status='1' WHERE id='" . (int)$active_data['id'] . "'");
                    echo '{"status":"200","message":"Number Cancelled"}';
                }
                exit;
            }

            // --- No SMS received — fetch USA/CA API credentials ---
            $sql4     = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='3'");
            $api_data = mysqli_fetch_assoc($sql4);
            $api_url  = rtrim($api_data['api_url'], '/');
            $api_key  = $api_data['api_key'];
            $number_id = $active_data['number_id'];

            // Check upstream first — if SMS was already delivered, don't refund
            $status_url = "{$api_url}/sms-otp/status/{$number_id}";
            $upstream   = getfunction($status_url, $api_key);

            if (isset($upstream['status']) && $upstream['status'] === 'completed' && !empty($upstream['otp_code'])) {
                // Upstream confirms delivery — save code and close without refund
                $sms = mysqli_real_escape_string($conn, $upstream['otp_code']);
                mysqli_query($conn, "UPDATE active_number SET active_status='1', status='1', sms_text='$sms' WHERE id='" . (int)$active_data['id'] . "'");
                echo '{"status":"200","message":"SMS was already received. Number closed.","sms":"' . $upstream['otp_code'] . '"}';
                exit;
            }

            // Atomic cancel + refund with FOR UPDATE lock
            mysqli_begin_transaction($conn);
            try {
                // Lock the active_number row to prevent concurrent double-refund
                $lock = mysqli_query($conn, "
                    SELECT id, sms_text, service_price
                    FROM active_number
                    WHERE order_id='$order_id'
                      AND active_status='2'
                      AND status='2'
                      AND api_id='3'
                    LIMIT 1 FOR UPDATE
                ");

                if (mysqli_num_rows($lock) != 1) {
                    throw new Exception("Already Cancelled");
                }

                $locked = mysqli_fetch_assoc($lock);

                if ($locked['sms_text'] != "") {
                    // SMS arrived between the upstream check and the lock — close without refund
                    mysqli_query($conn, "UPDATE active_number SET active_status='1', status='1' WHERE id='" . (int)$locked['id'] . "'");
                    mysqli_commit($conn);
                    echo '{"status":"200","message":"Number Cancelled (SMS was received, no refund)"}';
                    exit;
                }

                // Lock wallet row to prevent race condition on balance
                $sql_wallet = mysqli_query($conn, "SELECT balance, total_otp FROM user_wallet WHERE user_id='$user_id' FOR UPDATE");
                $wallet     = mysqli_fetch_assoc($sql_wallet);
                $add_balance = $wallet['balance'] + $locked['service_price'];
                $cut_otp     = max(0, $wallet['total_otp'] - 1);

                mysqli_query($conn, "UPDATE active_number SET active_status='1', status='3' WHERE id='" . (int)$locked['id'] . "'");
                mysqli_query($conn, "UPDATE user_wallet SET balance='$add_balance', total_otp='$cut_otp' WHERE user_id='$user_id'");

                mysqli_commit($conn);
                echo '{"status":"200","message":"Number Cancelled & Refunded"}';

            } catch (Exception $e) {
                mysqli_rollback($conn);
                echo '{"status":"500","message":"' . $e->getMessage() . '"}';
            }
        }
    }
}
