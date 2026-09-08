<?php
date_default_timezone_set('Africa/Lagos');
include_once __DIR__ . '/../../include/config.php';

if (!isset($_GET['order_id']) || $_GET['order_id'] == "") {
    echo '{"status":"500","message":"Invalid Order id"}';
} elseif (!isset($_GET['token']) || $_GET['token'] == "") {
    echo '{"status":"500","message":"Token Blank"}';
} else {
    $token    = mysqli_real_escape_string($conn, $_GET['token']);
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

            // --- SMS already received — just close the record ---
            if ($active_data['sms_text'] != "") {
                $sql_close = mysqli_query($conn, "SELECT active_status FROM active_number WHERE order_id='$order_id'");
                $row_close = mysqli_fetch_assoc($sql_close);
                if ($row_close['active_status'] == 1) {
                    echo '{"status":"500","message":"Already Cancelled"}';
                } else {
                    mysqli_query($conn, "UPDATE active_number SET active_status='1', status='1' WHERE id='" . $active_data['id'] . "'");
                    echo '{"status":"200","message":"Number Cancelled"}';
                }
                exit;
            }

            // --- No SMS received — fetch upstream credentials and cancel ---
            $sql3      = mysqli_query($conn, "SELECT * FROM otp_server WHERE id='" . $active_data['server_id'] . "'");
            $server    = mysqli_fetch_assoc($sql3);
            $sql4      = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='" . $server['api_id'] . "'");
            $api_data  = mysqli_fetch_assoc($sql4);
            $api_url   = $api_data['api_url'];
            $api_key   = $api_data['api_key'];
            $number_id = $active_data['number_id'];

            // Call upstream cancel (status=8) before touching the DB
            $cancel_url = "{$api_url}/stubs/handler_api.php?api_key={$api_key}&action=setStatus&id={$number_id}&status=8";
            $ch = curl_init($cancel_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_exec($ch);
            curl_close($ch);

            // Atomic refund with FOR UPDATE to prevent double-credit race condition
            mysqli_begin_transaction($conn);
            try {
                $lock = mysqli_query($conn, "SELECT id, sms_text, service_price FROM active_number WHERE order_id='$order_id' AND active_status='2' AND status='2' LIMIT 1 FOR UPDATE");

                if (mysqli_num_rows($lock) != 1) {
                    throw new Exception("Already Cancelled");
                }

                $locked = mysqli_fetch_assoc($lock);

                if ($locked['sms_text'] != "") {
                    // SMS arrived just before we got the lock — close without refund
                    mysqli_query($conn, "UPDATE active_number SET active_status='1', status='1' WHERE id='" . (int)$locked['id'] . "'");
                    mysqli_commit($conn);
                    echo '{"status":"200","message":"Number Cancelled (SMS was received, no refund)"}';
                    exit;
                }

                // FIX: FOR UPDATE on wallet SELECT prevents race condition on balance read
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
