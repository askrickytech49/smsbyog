<?php
include __DIR__ . '/../../include/config.php';
if(!isset($_POST['token']) || $_POST['token'] ==""){
echo'{"status": "300","msg": "token missing"}';
} else {
$token = mysqli_real_escape_string($conn,$_POST['token']); 
// $find_token = new radiumsahil();
$check_token = check_token($token, $conn);
// $find_token->closeConnection();
if($check_token === false){
echo'{"status": "300","msg": "Token Expired Please Logout And Login Again"}';
}else{
$sql=$conn->query("SELECT* FROM user_wallet WHERE user_id='$check_token'");
$data=$sql->fetch_assoc();
$data_balance=$data['balance'];
echo'{"status": "200","balance": "'.$data_balance.'"}';
}
mysqli_close($conn);
}
?>