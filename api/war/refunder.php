<?php
require_once __DIR__ . '/../../include/config.php';
$today = date('Y-m-d');

$sql = mysqli_query($conn, "SELECT * FROM active_number WHERE sms_text <> '' AND status ='3' AND DATE(buy_time) = '$today'");
while($number_data = mysqli_fetch_array($sql)){
$sql2 = mysqli_query($conn,"SELECT * FROM user_data WHERE id='".$number_data['user_id']."' AND status='1'");
if(mysqli_num_rows($sql2) == 1){
$user_data = mysqli_fetch_assoc($sql2);
$sql3 = mysqli_query($conn,"SELECT * FROM user_wallet WHERE user_id='".$number_data['user_id']."'");
$user_wallet = mysqli_fetch_assoc($sql3);
if($user_wallet['balance'] >= $number_data['service_price']){
    $cut_balance = $user_wallet['balance'] - $number_data['service_price'];
    $user_otp = $user_wallet['total_otp'];
    $add_otp = $user_otp + 1;
  mysqli_query($conn, "UPDATE user_wallet SET balance='$cut_balance', total_otp='$add_otp' WHERE user_id='".$user_data['id']."'");
mysqli_query($conn, "UPDATE active_number SET status='1' WHERE id='" . $number_data['id'] . "'");
echo "Money Cut";
echo "<br>";
}else{
    echo "Money Failed Cut";
echo "<br>";

}
}

}