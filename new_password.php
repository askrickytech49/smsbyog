<?php
include  'include/config.php';
$token = $_GET['token'] ?? "";
$check_token = check_token($token, $conn);
if($check_token === false){
    redirect('login');	
	exit;		
} else {		
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password - Allsmsverify</title>
    <link rel="shortcut icon" href="./images/logo-png.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="./css/index.css">
    <link rel="stylesheet" href="./css/signin.css">
    <link rel="stylesheet" href="./css/new_password.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>  
    <wc-toast id="tt" position="top-right"> </wc-toast>
</head>
<body>
    <div class="main-img-div">
        <img src="./images/hero-image.png" class="hero-img_" alt="Allsmsverify Hero Image">
    </div>
    <div class="form-area-div">
        <div class="logo-div-mini mb-4">
            <img class="logo-img-mini" src="./images/logo-png.png" alt="Allsmsverify logo">
            <h1 class="logo-text-mini">Allsmsverify</h1>
        </div>
        <div class="form-div">
            <h1 class="small-heading fw-600">New Password</h1>
            <p class="">Please create a new password that you dont use on any other site.</p>
                <div class="mb-5">
                    <label for="new_password" class="form-label">New Password</label>
                    <div class="position-relative">
                      <input type="password" class="form-control" id="new_password">
                      <input type="hidden" id="tokens" value="<?php echo $token; ?>">
                      <i class="bi bi-eye-slash toggle-password" id="togglePassword"></i>
                    </div>
                    <small class="mt-0 grey">• minimum of 8 characters</small>
                </div>
                <div class="mb-5">
                  <label for="confirm_password" class="form-label">Confirm Password</label>
                  <div class="position-relative">
                    <input type="password" class="form-control" id="confirm_password">
                    <i class="bi bi-eye-slash toggle-password" id="togglePassword2"></i>
                  </div>
                </div>
                <button id="change_pass" type="submit" class="btn-color create-btn w-100 mt-5">Set Password</button>       
        </div>
    </div>

    <script src="./js/password_changed.js?v=1"></script>
    <?php include ('partial/custom_js.php'); ?>
</body>
</html>
<?php } ?>