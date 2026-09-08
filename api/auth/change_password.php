<?php
include __DIR__ . '/../../include/config.php';
if(!isset($_POST['old_password']) || $_POST['old_password'] ==""){
echo'{"status": "2","msg": "Enter Old password"}';
}elseif(!isset($_POST['new_password']) || $_POST['new_password'] ==""){
echo'{"status": "2","msg": "Enter New Password"}';
}elseif (strlen($_POST['new_password']) < 6) {
echo'{"status": "2","msg": "New password must be at least 6 characters long"}';
}elseif(!isset($_POST['token']) || $_POST['token'] ==""){
echo'{"status": "3","msg": "token missing"}';
} else {
$token = mysqli_real_escape_string($conn,$_POST['token']); 
$check_token = check_token($token, $conn);
// $closeConnection();
if($check_token === false){
echo'{"status": "3","msg": "Token Expired Please Logout And Login Again"}';
}else{
$old_password=md5($_POST['old_password']);
$new_password=md5($_POST['new_password']);
$sql=$conn->query("SELECT* FROM user_data WHERE id='$check_token'");
$data=$sql->fetch_assoc();
$data_pass=$data['password'];
if($old_password == $data_pass){
$sql2=$conn->query("UPDATE user_data SET password='$new_password' WHERE id='$check_token'");
echo'{"status": "1","msg": "Password Update Successful"}';
}else{
echo'{"status": "2","msg": "Old Password Not Match"}';
}
}
mysqli_close($conn);
}
?>