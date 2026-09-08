<?php
session_start();
include  'include/config.php';
require __DIR__ . '/class/class.control.php';
if(!isset($_SESSION['token'])){
	if(isset($_COOKIE['remember_me'])) {
		$radium_token = $_COOKIE['remember_me'];
		$_SESSION['token'] = $radium_token;
	}else{
		redirect('login');
	}
}
$wallet = new radiumsahil();
$userdata = $wallet->userdata();
$userwallet = $wallet->userwallet();
$referwallet = $wallet->refer_data();
if($userdata===false){
	unset($_SESSION['token']);
	session_destroy();
	if(isset($_COOKIE['remember_me'])) {
		unset($_COOKIE['remember_me']);
		setcookie('remember_me', $token, [
			'expires' => time() - 3600,
			'path' => '/',
			'domain' => $_SERVER['HTTP_HOST'],
			'secure' => true,
			'httponly' => true,
			'samesite' => 'radium'
		]);
		

	}
		redirect('login');	
}
$wallet->closeConnection();
include 'theam/' . THEAM . '/refer_wallet.php';
?>