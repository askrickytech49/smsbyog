<?php
session_start();
include  'include/config.php';
if(isset($_SESSION['token'])){
	redirect('dashboard');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Change Success - Allsmsverify</title>
    <link rel="shortcut icon" href="./images/logo-png.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="./css/index.css">
    <link rel="stylesheet" href="./css/account_created.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
</head>
<body>
    
<div class="success-div position-relative">
    <i class="bi bi-x icon-absolute"></i>
    <img class="password-img" src="./images/password.svg" alt="Password Changed">
    <h2 class="general-heading text-center mt-4">Password Changed</h2>
    <p class="my-4 w-75 text-center">Your password has been successfully reset. You can now log in with your new credentials and continue using our services without any issues.</p>
    <a href="login">
        <button id="loginBtn" class="btn-color create-btn w-100">Back to Login</button>
    </a>
</div>


    <script src="./js/password_changed.js"></script>
</body>
</html>