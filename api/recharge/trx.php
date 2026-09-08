<?php
include __DIR__ . '/../../../../include/config.php';
if(!isset($_GET['token']) || $_GET['token'] ==""){
echo'{"status": "300","message": "token missing"}';
} else {
$token = mysqli_real_escape_string($conn,$_GET['token']); 
// $find_token = new radiumsahil();
$check_token = check_token($token, $conn);
// $find_token->closeConnection();
if($check_token === false){
echo'{"status": "300","msg": "Token Expired Please Logout And Login Again"}';
}else{
$sql=$conn->query("SELECT* FROM crypto_data WHERE network='tron' && user_id='$check_token' && status='2'");
if(mysqli_num_rows($sql)== 0){
echo'{"ok":false,"message":"No Any Active Address "}';
}else{
    mysqli_query($conn,"UPDATE crypto_data SET status='3' WHERE network='tron' and user_id='$check_token'");
echo'{"ok":true,"message":"Address deleted successfully."}';    
}
}

}
?>