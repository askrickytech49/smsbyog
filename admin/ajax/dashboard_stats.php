<?php
require "../auth.php";

$today = date('Y-m-d');

echo json_encode([
  'total_recharge'=>(int)mysqli_fetch_assoc(
      mysqli_query($conn,"SELECT SUM(total_recharge) t FROM user_wallet")
  )['t'],
  'today_recharge'=>(int)mysqli_fetch_assoc(
      mysqli_query($conn,"SELECT SUM(amount) t FROM user_transaction WHERE DATE(date)='$today'")
  )['t']
]);
