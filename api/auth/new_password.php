<?php
include __DIR__ . '/../../include/config.php';
if(!isset($_POST['new_password']) || $_POST['new_password'] ==""){
echo'{"status": "2","msg": "Enter New password"}';
}elseif(!isset($_POST['confirm_password']) || $_POST['confirm_password'] ==""){
echo'{"status": "2","msg": "Enter New Password"}';
}elseif (strlen($_POST['new_password']) < 6) {
echo'{"status": "2","msg": "New password must be at least 6 characters long"}';
}elseif(!isset($_POST['token']) || $_POST['token'] ==""){
echo'{"status": "3","msg": "token missing"}';
} else {
$token = mysqli_real_escape_string($conn,$_POST['token']); 
$check_token = check_token($token, $conn);
if($check_token === false){
echo'{"status": "2","msg": "Recovery Email Expired!"}';
}else{
$new_password=md5($_POST['new_password']);
$confirm_password=md5($_POST['confirm_password']);
if($new_password == $confirm_password){
$new_token = md5(rand());
$sql = $conn->query("UPDATE user_data SET password='$new_password' WHERE id='$check_token'");
$sql2 = $conn->query("UPDATE login_token SET token='$new_token' WHERE user_id='$check_token'");
if($sql && $sql2){
echo'{"status": "1","msg": "Password Updated Successful"}';
} else {
echo'{"status": "2","msg": "Something Went Wrong"}';
}
}else{
echo'{"status": "2","msg": "Confirm Password Not Match"}';
}
}
mysqli_close($conn);
}
?>