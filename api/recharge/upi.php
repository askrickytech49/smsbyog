<?php
include __DIR__ . '/../../include/config.php';

if (!isset($_GET['txn_id']) || $_GET['txn_id'] == "") {
echo'{"status":"500","message":"Invalid txn id"}';
} elseif (!isset($_GET['token']) || $_GET['token'] == "") {
echo'{"status":"500","message":"Token Blank"}';
} else {
$token = mysqli_real_escape_string($conn,$_GET['token']); 
// $find_token = new radiumsahil();
$check_token = check_token($token, $conn);
// $find_token->closeConnection();
if($check_token === false){
echo'{"status":"500","message":"Token Expired Please Logout And Login Again"}';
}else{
$current_time_in_ist = date('Y-m-d H:i:s');        
$user_id = $check_token;
$txn_id = mysqli_real_escape_string($conn,$_GET['txn_id']); 
$pattern = "/^[a-zA-Z0-9 ]+$/"; 
if (preg_match($pattern, $txn_id)) {
  if (strlen($txn_id) > 12) {
   echo'{"status":"500","message":"Utr Not More Than 12 Digits"}';
    } else {
$numberAsString = (string)$txn_id;
if ($numberAsString[0] === '0') {
 echo'{"status":"500","message":"Invalid Utr Enter"}';
} else {
$sql1 = mysqli_query($conn,"SELECT * FROM upi_recharge WHERE txn_id='".$txn_id."'");
if(mysqli_num_rows($sql1) == 0){
$sql5=mysqli_query($conn,"SELECT * FROM user_wallet WHERE user_id='".$user_id."'");
$user_data=mysqli_fetch_assoc($sql5);
$user_balance = $user_data['balance'];
$total_recharge = $user_data['total_recharge'];
$upi_merchant = $site_data['upi_merchant_id'];
$upi_token = $site_data['upi_merchant_token'];
$min_recharge = $site_data['upi_min_recharge'];
   $url = "https://payments-tesseract.bharatpe.in/api/v1/merchant/transactions?module=PAYMENT_QR&merchantId={$upi_merchant}";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["token: $upi_token"]);
        $output = curl_exec ($ch);
        curl_close ($ch);
        $transactions = json_decode($output)->data->transactions;
$msg = "";        
      foreach($transactions as $transaction){
     if($transaction->bankReferenceNo == $txn_id){
     $amount = $transaction->amount;      
    if($amount >= $min_recharge){
                    $ref_no = $transaction->bankReferenceNo;
                    $amount = $transaction->amount;
                    $payee_name = $transaction->payerName;
                    $payee_handle = $transaction->payerHandle;
                    $add_balance = $user_balance + $amount;
                    $add_total_rc = $total_recharge + $amount;
                    $sql50=mysqli_query($conn,"SELECT * FROM refer_data WHERE user_id='".$user_id."'");
                    $refer_data=mysqli_fetch_assoc($sql50);
                              
                    $sqladd = "INSERT INTO upi_recharge (user_id, amount, txn_id, recharge_time, status) VALUES ('$user_id', '$amount', '$txn_id', '$current_time_in_ist', '1')";
                     mysqli_query($conn, $sqladd);
                    $sql6 = mysqli_query($conn, "UPDATE user_wallet SET balance='$add_balance', total_recharge='$add_total_rc' WHERE user_id='".$user_id."'"); 
                      $sqladd2 = "INSERT INTO user_transaction (user_id, amount, date, type, txn_id, status) VALUES ('$user_id', '$amount', '$current_time_in_ist', 'Upi Recharge', '$txn_id', '1')";
                     mysqli_query($conn, $sqladd2);   
                         if($refer_data['refer_by'] !=""){
                             $sql510=mysqli_query($conn,"SELECT * FROM refer_data WHERE own_code='".$refer_data['refer_by']."'");
                       $refer_data1=mysqli_fetch_assoc($sql510);
                        $by_balance = $refer_data1['balance']; 
                        $by_total_earn = $refer_data1['total_earn']; 
                        $add_refer_bal = ($site_data['refer_percent'] / 100) * $amount;  
                         $total_add_refer = $by_balance + $add_refer_bal; 
                           $total_earn_refer = $by_total_earn + $add_refer_bal;                                                                 
                       mysqli_query($conn, "UPDATE refer_data SET balance='$total_add_refer', total_earn='$total_earn_refer' WHERE own_code='".$refer_data['refer_by']."'"); 
                      
                    }                                
                        $msg = '{"status":"200","message":"₦'.$amount.' Credit In Your Account"}';                     
                    }else{
                        $msg = '{"status":"500","message":"You Have Pay Less Than '.$min_recharge.' Rs Contact Our Support Team"}';
                                  
                    }   
}
}
if($msg == ""){
$msg = '{"status":"500","message":"Payment Not Found"}';                      
}
echo $msg; 
}else{
echo'{"status":"500","message":"Utr id Already Used"}';
}
}
}
}else{
echo'{"status":"500","message":"Symbol Not Allowed"}';
}
}
mysqli_close($conn);
}
