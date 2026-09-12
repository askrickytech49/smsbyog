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
  $check_token = check_token($token, $conn);
  if ($check_token === false) {
    echo '{"status":"500","message":"Token Expired Please Logout And Login Again"}';
  } else {
    $user_id = $check_token;
    $order_id = mysqli_real_escape_string($conn, $_GET['order_id']);
    $sql1 = mysqli_query($conn, "SELECT * FROM active_number WHERE order_id='$order_id' AND active_status='2'");
    if (mysqli_num_rows($sql1) == 1) {
      $active_data = mysqli_fetch_assoc($sql1);
      $sql3 = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='" . $active_data['api_id'] . "'");
      $api_data = mysqli_fetch_assoc($sql3);
      $sql4 = mysqli_query($conn, "SELECT * FROM user_wallet WHERE user_id='$user_id'");
      $user_data = mysqli_fetch_assoc($sql4);
      $number_id = $active_data['number_id'];
      $api_url = $api_data['api_url'];
      $api_key = $api_data['api_key'];

      $url = "{$api_url}/stubs/handler_api.php?api_key={$api_key}&action=getStatus&id={$number_id}";
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $response = curl_exec($ch);
      curl_close($ch);

      // --- TIMEOUT CHECK (runs before upstream response handling) ---
      $givenTime = strtotime($active_data['buy_time']);
      $currentTime = time();
      $twentyMinutesInSeconds = 20 * 60;
      $isExpired = (($givenTime + $twentyMinutesInSeconds) <= $currentTime);

      // SMS already received — always return it regardless of upstream status
      if ($active_data['sms_text'] != "") {
        if ($active_data['active_status'] == 2) {
          // Close the record if it is still open
          mysqli_query($conn, "UPDATE active_number SET active_status='1', status='1' WHERE id='" . $active_data['id'] . "'");
        }
        echo '{"status":"200","message":"Code Receive","sms":"' . $active_data['sms_text'] . '"}';

      // Timer has expired and no SMS — auto-refund
      } elseif ($isExpired) {
        mysqli_begin_transaction($conn);
        try {
          // Re-read with lock to prevent double refund
          $lock = mysqli_query($conn, "SELECT id, active_status, sms_text, service_price FROM active_number WHERE order_id='$order_id' AND active_status='2' AND status='2' LIMIT 1 FOR UPDATE");
          if (mysqli_num_rows($lock) == 1) {
            $locked = mysqli_fetch_assoc($lock);
            if ($locked['sms_text'] == "") {
              // FIX: call setStatus=8 (cancel), not setStatus=3 (retry)
              $cancel_url = "{$api_url}/stubs/handler_api.php?api_key={$api_key}&action=setStatus&id={$number_id}&status=8";
              $ch = curl_init($cancel_url);
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
              curl_exec($ch);
              curl_close($ch);

              $sql_wallet = mysqli_query($conn, "SELECT balance, total_otp FROM user_wallet WHERE user_id='$user_id' FOR UPDATE");
              $wallet = mysqli_fetch_assoc($sql_wallet);
              $add_balance = $wallet['balance'] + $locked['service_price'];
              $cut_otp = max(0, $wallet['total_otp'] - 1);

              mysqli_query($conn, "UPDATE active_number SET active_status='1', status='3' WHERE id='" . $locked['id'] . "'");
              mysqli_query($conn, "UPDATE user_wallet SET balance='$add_balance', total_otp='$cut_otp' WHERE user_id='$user_id'");
              mysqli_commit($conn);
              echo '{"status":"500","message":"Number Expired. Refund processed."}';
            } else {
              // SMS arrived between the first read and the lock — close normally
              mysqli_query($conn, "UPDATE active_number SET active_status='1', status='1' WHERE id='" . $locked['id'] . "'");
              mysqli_commit($conn);
              echo '{"status":"200","message":"Code Receive","sms":"' . $locked['sms_text'] . '"}';
            }
          } else {
            // Already processed by another concurrent request
            mysqli_commit($conn);
            echo '{"status":"500","message":"Number Expired or Already Processed"}';
          }
        } catch (Exception $e) {
          mysqli_rollback($conn);
          echo '{"status":"500","message":"Server Error During Refund"}';
        }

      // Upstream says: cancel triggered by provider
      } elseif ($response == 'STATUS_CANCEL') {
        // FIX: call setStatus=8 (cancel), not setStatus=3 (retry)
        $cancel_url = "{$api_url}/stubs/handler_api.php?api_key={$api_key}&action=setStatus&id={$number_id}&status=8";
        $ch = curl_init($cancel_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_exec($ch);
        curl_close($ch);

        mysqli_begin_transaction($conn);
        try {
          $lock = mysqli_query($conn, "SELECT id, sms_text, service_price FROM active_number WHERE order_id='$order_id' AND active_status='2' AND status='2' LIMIT 1 FOR UPDATE");
          if (mysqli_num_rows($lock) == 1) {
            $locked = mysqli_fetch_assoc($lock);
            $sql_wallet = mysqli_query($conn, "SELECT balance, total_otp FROM user_wallet WHERE user_id='$user_id' FOR UPDATE");
            $wallet = mysqli_fetch_assoc($sql_wallet);
            $add_balance = $wallet['balance'] + $locked['service_price'];
            $cut_otp = max(0, $wallet['total_otp'] - 1);
            mysqli_query($conn, "UPDATE active_number SET active_status='1', status='3' WHERE id='" . $locked['id'] . "'");
            mysqli_query($conn, "UPDATE user_wallet SET balance='$add_balance', total_otp='$cut_otp' WHERE user_id='$user_id'");
            mysqli_commit($conn);
          } else {
            mysqli_commit($conn);
          }
        } catch (Exception $e) {
          mysqli_rollback($conn);
        }
        echo '{"status":"500","message":"Number Canceled. Refund processed."}';

      // Upstream says: SMS received
      } elseif (explode(':', $response)[0] == 'STATUS_OK') {
        $sms = explode('STATUS_OK:', $response)[1];
        mysqli_query($conn, "UPDATE active_number SET sms_text='" . mysqli_real_escape_string($conn, $sms) . "', status='1' WHERE id='" . $active_data['id'] . "'");
        // Confirm receipt with upstream
        $confirm_url = "{$api_url}/stubs/handler_api.php?api_key={$api_key}&action=setStatus&id={$number_id}&status=3";
        $ch = curl_init($confirm_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_exec($ch);
        curl_close($ch);
        echo '{"status":"200","message":"Code Receive","sms":"' . $sms . '"}';

      // Upstream says: waiting
      } elseif ($response == 'STATUS_WAIT_CODE' || $response == 'STATUS_WAIT_RETRY') {
        echo '{"status":"300","message":"Waiting for SMS."}';

      // Fallback — unknown upstream response, still within time window
      } else {
        echo '{"status":"300","message":"Waiting for SMS."}';
      }

    } else {
      echo '{"status":"500","message":"Invalid Order Id Or Cancelled #1"}';
    }
  }
}
