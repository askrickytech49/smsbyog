<?php
session_start();

include  'include/config.php';
require __DIR__ . '/class/class.control.php';
if (empty($_SESSION['token'])) {
    session_destroy();

    if (isset($_COOKIE['remember_me'])) {
        setcookie('remember_me','',[
            'expires'=>time()-3600,
            'path'=>'/',
            'domain'=>$_SERVER['HTTP_HOST'],
            'secure'=>isset($_SERVER['HTTPS']),
            'httponly'=>true,
            'samesite'=>'Lax'
        ]);
    }

    redirect('login');

}
$wallet = new radiumsahil();
$userdata = $wallet->userdata();
$userwallet = $wallet->userwallet();
$referwallet = $wallet->refer_data();
$refer_users = $wallet->refer_users();
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
include __DIR__ . '/theme/' . THEAM . '/refer.php';

?>