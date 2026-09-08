<?php

function sendMessage($chatId, $message) {
    $botToken = '6290021443:AAFU4DyKabNwSiJ6hQQhAJYgfXS9cwiTMs8';
    $url = "https://api.telegram.org/bot$botToken/sendMessage";

    $params = [
        'chat_id' => $chatId,
        'text' => $message,
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Error: ' . curl_error($ch);
    }

    curl_close($ch);

    return $result;
}



function convertCurrency($amount, $fromCurrency, $toCurrency) {
    $url = "https://api.exchangerate-api.com/v4/latest/{$fromCurrency}";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);

    if ($response === false) {
        return "cURL Error: " . curl_error($ch);
    } else {
        $data = json_decode($response, true);

        if ($data) {
            $exchangeRate = $data['rates'][$toCurrency];
            $convertedAmount = $amount * $exchangeRate;
            return $convertedAmount;
        } else {
             $exchangeRate = 83;
            $convertedAmount = $amount * $exchangeRate;
            return $convertedAmount;
        }
    }

    curl_close($ch);
}



require_once __DIR__ . '/../../../include/config.php';
$current_time_in_ist = date('Y-m-d H:i:s');        

$allowedIPs = ['91.227.144.54','51.79.177.60']; 

$visitorIP = $_SERVER['REMOTE_ADDR'];

if (in_array($visitorIP, $allowedIPs)) {
   $data = file_get_contents('php://input');    
    $datas = json_decode($data, true);
$sign = $datas['sign'];
unset($datas['sign']);    
$hash = md5(base64_encode(json_encode($datas, JSON_UNESCAPED_UNICODE)) .$site_data['crypto_api_key']);

if (hash_equals($hash, $sign)) {
if($datas['type'] == "wallet"){
if($datas['status'] == "paid"){
$order_id = $datas['order_id'];
$sql=$conn->query("SELECT* FROM crypto_data WHERE order_id='".$order_id."' and status='2'");
if(mysqli_num_rows($sql) == 1){
$crypto_data = mysqli_fetch_assoc($sql);
$user_id = $crypto_data['user_id'];
$sql5=mysqli_query($conn,"SELECT * FROM user_wallet WHERE user_id='".$user_id."'");
$user_data=mysqli_fetch_assoc($sql5);
$user_balance = $user_data['balance'];
$total_recharge = $user_data['total_recharge'];
$usd_amount = $datas['payment_amount_usd'];
$inr_amount = convertCurrency($usd_amount, 'USD', 'INR');
$inr_amount = number_format($inr_amount, 2);

$add_balance = $inr_amount + $user_balance;
$add_total_rc = $inr_amount + $total_recharge;
                    $sql50=mysqli_query($conn,"SELECT * FROM refer_data WHERE user_id='".$user_id."'");
                    $refer_data=mysqli_fetch_assoc($sql50);
                              
                    $sqladd = "INSERT INTO crypto_recharge (user_id, amount, order_id, recharge_time, status) VALUES ('$user_id', '$inr_amount', '$order_id', '$current_time_in_ist', '1')";
                     mysqli_query($conn, $sqladd);
                    $sql6 = mysqli_query($conn, "UPDATE user_wallet SET balance='$add_balance', total_recharge='$add_total_rc' WHERE user_id='".$user_id."'"); 
                      $sqladd2 = "INSERT INTO user_transaction (user_id, amount, date, type, txn_id, status) VALUES ('$user_id', '$inr_amount', '$current_time_in_ist', 'Crypto Recharge', '$order_id', '1')";
                     mysqli_query($conn, $sqladd2); 
    mysqli_query($conn,"UPDATE crypto_data SET status='1' WHERE order_id='$order_id'");                           
                /*         if($refer_data['refer_by'] !=""){
                             $sql510=mysqli_query($conn,"SELECT * FROM refer_data WHERE own_code='".$refer_data['refer_by']."'");
                       $refer_data1=mysqli_fetch_assoc($sql510);
                        $by_balance = $refer_data1['balance']; 
                        $by_total_earn = $refer_data1['total_earn']; 
                        $add_refer_bal = ($site_data['refer_percent'] / 100) * $inr_amount;  
                         $total_add_refer = $by_balance + $add_refer_bal; 
                           $total_earn_refer = $by_total_earn + $add_refer_bal;                                                                 
                       mysqli_query($conn, "UPDATE refer_data SET balance='$total_add_refer', total_earn='$total_earn_refer' WHERE own_code='".$refer_data['refer_by']."'"); 
                }        */     
} else {
$msg = "1";
}
}else{
$msg = "2";
}
}else{
$msg = "3";
}
}else{
$msg = "4";
}
mysqli_close($conn);
} else {
    echo 'Access denied. Your IP is not on the whitelist.';
    $msg = $visitorIP;
}
sendMessage('1366549165', 'Error : '.$msg.'');


?>
