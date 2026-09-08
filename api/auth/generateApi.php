<?php
function generateRandomString32($length = 32) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $random_string = '';

    for ($i = 0; $i < $length; $i++) {
        $random_string .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $random_string;
}
include __DIR__ . '/../../include/config.php';
if(!isset($_POST['token']) || $_POST['token'] ==""){
echo'{"status": "3","msg": "token missing"}';
} else {
$token = mysqli_real_escape_string($conn,$_POST['token']); 
// $find_token = new radiumsahil();
$check_token = check_token($token, $conn);
// $find_token->closeConnection();
if($check_token === false){
echo'{"status": "300","msg": "Token Expired Please Logout And Login Again"}';
}else{
    $current_time_in_ist = date('Y-m-d H:i:s');        
      
    $random= generateRandomString32();
        $sql="SELECT * FROM `user_api` WHERE user_id='".$check_token."'";
         	$result=mysqli_query($conn, $sql);
      if(mysqli_num_rows($result)==0){
          mysqli_query($conn,"INSERT INTO user_api (user_id, api_key, create_time) VALUES ('".$user_id."', '".$random."', '".$current_time_in_ist."')");
      }else{        	
        mysqli_query($conn,"UPDATE user_api SET api_key='$random' WHERE user_id='$check_token'");        
        }
echo'{"status": "200","msg": "Key Generated","api_key": "'.$random.'"}';
        
}
mysqli_close($conn);
}
?>