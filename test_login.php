<?php
$_POST['email'] = 'junioranthony0011@gmail.com';
$_POST['password'] = 'movet123';
$_SERVER['HTTP_USER_AGENT'] = 'CLI';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_HOST'] = 'localhost';

require 'api/auth/login.php';
?>
