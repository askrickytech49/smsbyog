<?php
session_start();

include  'include/config.php';
if(isset($_SESSION['token'])){
	redirect('dashboard');
}
if(isset($_GET['msg'])){
$error_data=$_GET['msg'];
 if($error_data=="not_found"){
$msg1="Account Not Found Please Register In Website Then Login";
$button_msg='Register Now';
$button_url="register";
 }else if($error_data=="block"){
$msg1="Your Account Blocked By Admin Please Contact Our Support Team";
$button_msg='Contact Now';
$button_url=$site_data['support_url']; 
 }else{
$msg1="You don’t have permission to access this page. Go Home!!";
$button_msg='Back To Home';
$button_url="index";  
 }
}else{
	redirect('index');	
}
include 'theam/' . THEAM . '/google-action.php';

?>