<?php
require_once __DIR__ . '/../../include/config.php';
$email = $_POST["email"];

function sendPasswordResetEmail($userEmail, $resetLink) {
    $to = $userEmail;
    $subject = "Password Reset Request";
    $message = '
    <html>
    <head>
        <title>Password Reset Request</title>
    </head>
    <body>
        <p>Hey there,</p>
        <p>Someone requested a new password for your account.</p>
        <p><a href="' . $resetLink . '" style="display:inline-block;padding:10px 20px;color:#fff;background-color:#007BFF;text-decoration:none;border-radius:5px;">Reset Password</a></p>
        <p>If you didn’t make this request, then you can ignore this email 🙂</p>
    </body>
    </html>
    ';    
    $headers  = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: support@allsmsverify.com' . "\r\n";
    
    if (mail($to, $subject, $message, $headers)) {
        return true;
    } else {
        return false;
    }
}

if ($email != "") {
    $email = mysqli_real_escape_string($conn, $_POST["email"]);
    $token = md5(rand());
    $sql = mysqli_query($conn, "SELECT id, email FROM user_data WHERE email='$email'");
    
    if (mysqli_num_rows($sql) > 0) {       
        $row = mysqli_fetch_array($sql);
        $user_id = $row["id"];
        $get_mail = $row["email"];
        $sql2 = mysqli_query($conn, "UPDATE login_token SET token='$token' WHERE user_id='$user_id'");
        
        if ($sql2) {
            $resetLink = WEBSITE_URL."/new_password?token=".$token;
            if (sendPasswordResetEmail($get_mail, $resetLink)) {
                echo '{"status": "1", "msg": "We Emailed You a Password Reset Link"}';
            } else {
                echo '{"status": "2", "msg": "Reset Link Not Sent"}';
            }
        } else {
            echo '{"status": "2", "msg": "Something Went Wrong"}';
        }
    } else {
        echo '{"status": "2", "msg": "Email Id Not Exists"}';
    }
} else {
    echo '{"status": "2", "msg": "Something Went Wrong"}';
}
?>
