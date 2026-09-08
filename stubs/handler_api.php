<?php
date_default_timezone_set('Africa/Lagos');
$current_time_in_ist = date('Y-m-d H:i:s');        
require_once __DIR__ . '/../include/config.php';
function callapi($url) {
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);

    curl_close($ch);

    return $response;
}
function generateRandomString($length = 20) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $random_string = '';

    for ($i = 0; $i < $length; $i++) {
        $random_string .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $random_string;
}
$current_time_in_ist = date('Y-m-d H:i:s');                

if(isset($_GET['action']) && $_GET['action'] == "getNumber"){

    $api_key = mysqli_real_escape_string($conn,$_GET['api_key']);
    $service = mysqli_real_escape_string($conn,$_GET['service']);
    $country = mysqli_real_escape_string($conn,$_GET['country']);
    if($api_key == ""){
    echo "BAD_KEY";
    }elseif($service == ""){
    echo "BAD_SERVICE";
    }elseif($country == ""){
    echo "BAD_COUNTRY";
    }else{
$sql = mysqli_query($conn,"SELECT * FROM user_api WHERE api_key='$api_key'");
 if(mysqli_num_rows($sql) == 1){
$api_data = mysqli_fetch_assoc($sql);
$sql2 = mysqli_query($conn,"SELECT * FROM user_data WHERE id='".$api_data['user_id']."' and status='1'");
if(mysqli_num_rows($sql2) == 1){
$user_data = mysqli_fetch_assoc($sql2);
$sql3 = mysqli_query($conn,"SELECT * FROM otp_server WHERE id='$country'");
if(mysqli_num_rows($sql3) == 1){
$sql4 = mysqli_query($conn,"SELECT * FROM service WHERE service_id='$service' and server_id='$country'");
if(mysqli_num_rows($sql4) == 1){
$sql5 = mysqli_query($conn,"SELECT * FROM user_wallet WHERE user_id='".$api_data['user_id']."'");
$user_wallet = mysqli_fetch_assoc($sql5);
$otp_server = mysqli_fetch_assoc($sql3);
$service_data = mysqli_fetch_assoc($sql4);
if($user_wallet['balance'] >= $service_data['service_price']){
$sql6 = mysqli_query($conn,"SELECT * FROM api_detail WHERE id='".$otp_server["api_id"]."'");
$api_detail = mysqli_fetch_assoc($sql6);
$api_url = $api_detail['api_url'];
$api_key = $api_detail['api_key'];
$server_code = $otp_server['server_code'];
  $call_url = "{$api_url}/stubs/handler_api.php?api_key={$api_key}&action=getNumber&service={$service}&country={$server_code}";
$api_response = callapi($call_url);
     $api_response = explode(':', $api_response);
     
     if($api_response[0] !="ACCESS_NUMBER"){
        if($api_response[0] == "NO_BALANCE"){
        echo "NO_API_NUMBER";
        }else{
        echo $api_response[0];
        }       
        }else{
$user_id = $user_data['id'];        
$cut_balance = $user_wallet['balance'] - $service_data['service_price']; 
$user_otp = $user_wallet['total_otp'];
$add_otp = $user_otp + 1;     
$service_name = $service_data['service_name'];
$random_order = generateRandomString();
$sql7 = mysqli_query($conn, "UPDATE user_wallet SET balance='$cut_balance', total_otp='$add_otp' WHERE user_id='$user_id'");
if($sql7){
$sql8 = mysqli_query($conn,"INSERT INTO active_number(user_id, number_id, number, server_id, service_id, order_id, buy_time, status, sms_text, service_price, service_name, active_status) VALUES ('".$user_id."','".$api_response[1]."','".$api_response[2]."','".$country."','".$service."','".$random_order."','".$current_time_in_ist."','2','','".$service_data['service_price']."','".$service_name."','2')");
echo 'ACCESS_NUMBER:'.$random_order.':'.$api_response[2].'';
}else{
echo "SERVER_ERROR";
}   
}
}else{
echo "NO_BALANCE";
}
}else{
echo "BAD_SERVICE";
}
}else{
 echo "BAD_COUNTRY";
}
}else{
echo"ACCOUNT_BLOCKED";
}
 }else{
 echo "BAD_KEY";
 }         
}
}

if(isset($_GET['action']) && $_GET['action']=="getStatus"){
    $api_key = mysqli_real_escape_string($conn,$_GET['api_key']);
    $num_id = mysqli_real_escape_string($conn,$_GET['id']);
    
      if($api_key == ""){
    echo "BAD_KEY";
    }elseif($num_id == ""){
     echo "BAD_ID";
    }else{
$sql = mysqli_query($conn,"SELECT * FROM user_api WHERE api_key='$api_key'");
 if(mysqli_num_rows($sql) == 1){
$api_data = mysqli_fetch_assoc($sql);
$sql2 = mysqli_query($conn,"SELECT * FROM user_data WHERE id='".$api_data['user_id']."' and status='1'");
if(mysqli_num_rows($sql2) == 1){
$sql3 = mysqli_query($conn,"SELECT * FROM active_number WHERE order_id='$num_id' and user_id='".$api_data['user_id']."'");
if(mysqli_num_rows($sql3) == 1){
$sql7 = mysqli_query($conn,"SELECT * FROM active_number WHERE order_id='$num_id' and active_status='2'");
if(mysqli_num_rows($sql7) == 1){
$active_data = mysqli_fetch_assoc($sql3);
$sql4 = mysqli_query($conn,"SELECT * FROM otp_server WHERE id='".$active_data['server_id']."'");
$otp_server = mysqli_fetch_assoc($sql4);
$sql5 = mysqli_query($conn,"SELECT * FROM api_detail WHERE id='".$otp_server["api_id"]."'");
$api_detail = mysqli_fetch_assoc($sql5);
$sql8 = mysqli_query($conn,"SELECT * FROM user_wallet WHERE user_id='".$api_data['user_id']."'");
$user_wallet = mysqli_fetch_assoc($sql8);
$user_id = $api_data['user_id'];
$api_url = $api_detail['api_url'];
$api_key = $api_detail['api_key'];
$givenTime = strtotime($active_data['buy_time']);
$currentTime = time(); 
$twentyMinutesInSeconds = 20 * 60; 
$number_id = $active_data['number_id'];

if (($givenTime + $twentyMinutesInSeconds) <= $currentTime) {
   if($active_data['sms_text'] == ""){
        $call_url = "{$api_url}/stubs/handler_api.php?api_key={$api_key}&action=setStatus&id={$number_id}&status=8";
       $api_response = callapi($call_url);
$add_balance = $user_wallet['balance'] + $active_data['service_price'];  
$cut_otp = $user_wallet['total_otp'] - 1;    
mysqli_query($conn,"UPDATE active_number SET active_status='1', status='3' WHERE id='".$active_data['id']."'");  
mysqli_query($conn, "UPDATE user_wallet SET balance='$add_balance', total_otp='$cut_otp' WHERE user_id='".$user_id."'");
}else{
mysqli_query($conn,"UPDATE active_number SET active_status='1', status='1' WHERE id='".$active_data['id']."'");  
}
echo "STATUS_CANCEL";
} else {
$call_url = "{$api_url}/stubs/handler_api.php?api_key={$api_key}&action=getStatus&id={$number_id}";
 $api_response = callapi($call_url);
if($api_response == "STATUS_WAIT_CODE" || $api_response == "STATUS_WAIT_RETRY" ){
   if($active_data['sms_text'] != ""){
   echo $active_data['sms_text'];
 }else{
 echo $api_response;
 }  
}elseif($api_response == "STATUS_CANCEL" || $api_response == "NO_ACTIVATION"){
   if($active_data['sms_text'] == ""){
$add_balance = $user_wallet['balance'] + $active_data['service_price'];  
$cut_otp = $user_wallet['total_otp'] - 1;    
mysqli_query($conn,"UPDATE active_number SET active_status='1', status='3' WHERE id='".$active_data['id']."'");  
mysqli_query($conn, "UPDATE user_wallet SET balance='$add_balance', total_otp='$cut_otp' WHERE user_id='".$user_id."'");
}else{
mysqli_query($conn,"UPDATE active_number SET active_status='1', status='1' WHERE id='".$active_data['id']."'");  
}
echo "STATUS_CANCEL";
}elseif(explode(':', $api_response)[0] == 'STATUS_OK') {
           $sms = explode('STATUS_OK:', $api_response)[1];            
     if($active_data['sms_text'] != $sms){         
       $sql50 = mysqli_query($conn,"UPDATE active_number SET sms_text='".$sms."', status='1' WHERE id='".$active_data['id']."'"); 
       if($sql50){
       echo "STATUS_OK:".$sms;
       }else{
       echo"SERVER_ERROR";
       }
       }else{
       echo "STATUS_OK:".$sms;
       }   
}else{
echo "TRY_AGAIN";
}
}
}else{
echo "STATUS_CANCEL";
}
}else{
echo "NO_ACTIVATION";
}
}else{
echo"ACCOUNT_BLOCKED";
}
}else{
 echo "BAD_KEY";
}
}
}

if(isset($_GET['action']) && $_GET['action']=="setStatus" && ($_GET['status'])==8){
    $api_key = mysqli_real_escape_string($conn,$_GET['api_key']);
    $num_id = mysqli_real_escape_string($conn,$_GET['id']);
    
      if($api_key == ""){
    echo "BAD_KEY";
    }elseif($num_id == ""){
     echo "BAD_ID";
    }else{
$sql = mysqli_query($conn,"SELECT * FROM user_api WHERE api_key='$api_key'");
 if(mysqli_num_rows($sql) == 1){
$api_data = mysqli_fetch_assoc($sql);
$sql2 = mysqli_query($conn,"SELECT * FROM user_data WHERE id='".$api_data['user_id']."' and status='1'");
if(mysqli_num_rows($sql2) == 1){
$sql3 = mysqli_query($conn,"SELECT * FROM active_number WHERE order_id='$num_id' and user_id='".$api_data['user_id']."'");
if(mysqli_num_rows($sql3) == 1){
$sql7 = mysqli_query($conn,"SELECT * FROM active_number WHERE order_id='$num_id' and active_status='2'");
if(mysqli_num_rows($sql7) == 1){
$active_data = mysqli_fetch_assoc($sql3);
$sql4 = mysqli_query($conn,"SELECT * FROM otp_server WHERE id='".$active_data['server_id']."'");
$otp_server = mysqli_fetch_assoc($sql4);
$sql5 = mysqli_query($conn,"SELECT * FROM api_detail WHERE id='".$otp_server["api_id"]."'");
$api_detail = mysqli_fetch_assoc($sql5);
$sql8 = mysqli_query($conn,"SELECT * FROM user_wallet WHERE user_id='".$api_data['user_id']."'");
$user_wallet = mysqli_fetch_assoc($sql8);
$user_id = $api_data['user_id'];
$api_url = $api_detail['api_url'];
$api_key = $api_detail['api_key'];
$givenTime = strtotime($active_data['buy_time']);
$currentTime = time(); 
$twentyMinutesInSeconds = 20 * 60; 
$number_id = $active_data['number_id'];

if (($givenTime + $twentyMinutesInSeconds) <= $currentTime) {
   if($active_data['sms_text'] == ""){
        $call_url = "{$api_url}/stubs/handler_api.php?api_key={$api_key}&action=setStatus&id={$number_id}&status=8";
       $api_response = callapi($call_url);
$add_balance = $user_wallet['balance'] + $active_data['service_price'];  
$cut_otp = $user_wallet['total_otp'] - 1;    
mysqli_query($conn,"UPDATE active_number SET active_status='1', status='3' WHERE id='".$active_data['id']."'");  
mysqli_query($conn, "UPDATE user_wallet SET balance='$add_balance', total_otp='$cut_otp' WHERE user_id='".$user_id."'");
}else{
mysqli_query($conn,"UPDATE active_number SET active_status='1', status='1' WHERE id='".$active_data['id']."'");  
}
echo"STATUS_CANCEL";
} else {
  if($active_data['sms_text'] == ""){
        // FIX: use $number_id (upstream provider ID), not $num_id (local order string)
        $call_url = "{$api_url}/stubs/handler_api.php?api_key={$api_key}&action=setStatus&id={$number_id}&status=8";
       $api_response = callapi($call_url);
$add_balance = $user_wallet['balance'] + $active_data['service_price'];  
$cut_otp = $user_wallet['total_otp'] - 1;    
mysqli_query($conn,"UPDATE active_number SET active_status='1', status='3' WHERE id='".$active_data['id']."'");  
mysqli_query($conn, "UPDATE user_wallet SET balance='$add_balance', total_otp='$cut_otp' WHERE user_id='".$user_id."'");
}else{
mysqli_query($conn,"UPDATE active_number SET active_status='1', status='1' WHERE id='".$active_data['id']."'");  
}
echo "STATUS_CANCEL";
}
}else{
echo "STATUS_CANCEL";
}
}else{
echo "NO_ACTIVATION";
}
}else{
echo"ACCOUNT_BLOCKED";
}
}else{
 echo "BAD_KEY";
}
}
}

if(isset($_GET['action']) && $_GET['action']=="setStatus" && ($_GET['status'])==3){
    $api_key = mysqli_real_escape_string($conn,$_GET['api_key']);
    $num_id = mysqli_real_escape_string($conn,$_GET['id']);
    
      if($api_key == ""){
    echo "BAD_KEY";
    }elseif($num_id == ""){
     echo "BAD_ID";
    }else{
$sql = mysqli_query($conn,"SELECT * FROM user_api WHERE api_key='$api_key'");
 if(mysqli_num_rows($sql) == 1){
$api_data = mysqli_fetch_assoc($sql);
$sql2 = mysqli_query($conn,"SELECT * FROM user_data WHERE id='".$api_data['user_id']."' and status='1'");
if(mysqli_num_rows($sql2) == 1){
$sql3 = mysqli_query($conn,"SELECT * FROM active_number WHERE order_id='$num_id' and user_id='".$api_data['user_id']."'");
if(mysqli_num_rows($sql3) == 1){
$sql7 = mysqli_query($conn,"SELECT * FROM active_number WHERE order_id='$num_id' and active_status='2'");
if(mysqli_num_rows($sql7) == 1){
$active_data = mysqli_fetch_assoc($sql3);
$sql4 = mysqli_query($conn,"SELECT * FROM otp_server WHERE id='".$active_data['server_id']."'");
$otp_server = mysqli_fetch_assoc($sql4);
$sql5 = mysqli_query($conn,"SELECT * FROM api_detail WHERE id='".$otp_server["api_id"]."'");
$api_detail = mysqli_fetch_assoc($sql5);
$sql8 = mysqli_query($conn,"SELECT * FROM user_wallet WHERE user_id='".$api_data['user_id']."'");
$user_wallet = mysqli_fetch_assoc($sql8);
$user_id = $api_data['user_id'];
$api_url = $api_detail['api_url'];
$api_key = $api_detail['api_key'];
$givenTime = strtotime($active_data['buy_time']);
$currentTime = time(); 
$twentyMinutesInSeconds = 20 * 60; 
$number_id = $active_data['number_id'];

if (($givenTime + $twentyMinutesInSeconds) <= $currentTime) {
   if($active_data['sms_text'] == ""){
        $call_url = "{$api_url}/stubs/handler_api.php?api_key={$api_key}&action=setStatus&id={$number_id}&status=8";
       $api_response = callapi($call_url);
$add_balance = $user_wallet['balance'] + $active_data['service_price'];  
$cut_otp = $user_wallet['total_otp'] - 1;    
mysqli_query($conn,"UPDATE active_number SET active_status='1', status='3' WHERE id='".$active_data['id']."'");  
mysqli_query($conn, "UPDATE user_wallet SET balance='$add_balance', total_otp='$cut_otp' WHERE user_id='".$user_id."'");
}else{
mysqli_query($conn,"UPDATE active_number SET active_status='1', status='1' WHERE id='".$active_data['id']."'");  
}
echo"STATUS_CANCEL";
} else {
        $call_url = "{$api_url}/stubs/handler_api.php?api_key={$api_key}&action=setStatus&id={$num_id}&status=3";
       $api_response = callapi($call_url);

echo "ACCESS_RETRY_GET";
}
}else{
echo "STATUS_CANCEL";
}
}else{
echo "NO_ACTIVATION";
}
}else{
echo"ACCOUNT_BLOCKED";
}
}else{
 echo "BAD_KEY";
}
}  
}
if(isset($_GET['action']) && $_GET['action']=="getBalance"){
    $api_key = mysqli_real_escape_string($conn,$_GET['api_key']);
    
      if($api_key == ""){
    echo "BAD_KEY";
    }else{
$sql = mysqli_query($conn,"SELECT * FROM user_api WHERE api_key='$api_key'");
 if(mysqli_num_rows($sql) == 1){
$api_data = mysqli_fetch_assoc($sql);
$sql2 = mysqli_query($conn,"SELECT * FROM user_data WHERE id='".$api_data['user_id']."' and status='1'");
if(mysqli_num_rows($sql2) == 1){
$sql3 = mysqli_query($conn,"SELECT * FROM user_wallet WHERE user_id='".$api_data['user_id']."'");
$user_wallet = mysqli_fetch_assoc($sql3);
echo"ACCESS_BALANCE:".$user_wallet['balance'];
}else{
echo"ACCOUNT_BLOCKED";
}
}else{
 echo "BAD_KEY";
}    
}
}    
if(isset($_GET['action']) && $_GET['action']=="getCountries"){
    $api_key = mysqli_real_escape_string($conn,$_GET['api_key']);
    
      if($api_key == ""){
    echo "BAD_KEY";
    }else{
$sql = mysqli_query($conn,"SELECT * FROM user_api WHERE api_key='$api_key'");
 if(mysqli_num_rows($sql) == 1){
$api_data = mysqli_fetch_assoc($sql);
$sql2 = mysqli_query($conn,"SELECT * FROM user_data WHERE id='".$api_data['user_id']."' and status='1'");
if(mysqli_num_rows($sql2) == 1){
$sql4 = mysqli_query($conn,"SELECT * FROM otp_server ORDER BY ID ASC");

$response = array();

while ($row = $sql4->fetch_assoc()) {
    $response[$row['id']] = $row['server_name'];
}

$jsonResponse = json_encode($response);

echo $jsonResponse;
}else{
echo"ACCOUNT_BLOCKED";
}
}else{
 echo "BAD_KEY";
}    
}
}  
if(isset($_GET['action']) && $_GET['action']=="getServices"){
    $api_key = mysqli_real_escape_string($conn,$_GET['api_key']);
     $country = mysqli_real_escape_string($conn,$_GET['country']);
        
      if($api_key == ""){
    echo "BAD_KEY";
    }elseif($country == ""){
    echo "BAD_COUNTRY";
    }else{
$sql = mysqli_query($conn,"SELECT * FROM user_api WHERE api_key='$api_key'");
 if(mysqli_num_rows($sql) == 1){
$api_data = mysqli_fetch_assoc($sql);
$sql2 = mysqli_query($conn,"SELECT * FROM user_data WHERE id='".$api_data['user_id']."' and status='1'");
if(mysqli_num_rows($sql2) == 1){
$sql4 = mysqli_query($conn,"SELECT * FROM service WHERE server_id='$country'");
if(mysqli_num_rows($sql4) < 0){

$response = array();

while ($row = $sql4->fetch_assoc()) {
    $response[$row['service_id']] = $row['service_name'];
}

$jsonResponse = json_encode($response);

echo $jsonResponse;
}else{
 echo "BAD_COUNTRY";
}
}else{
echo"ACCOUNT_BLOCKED";
}
}else{
 echo "BAD_KEY";
}    
}
}  
?>
