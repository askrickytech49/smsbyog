<?php
include  'include/config.php';
if(isset($_SESSION['token'])){
	redirect('dashboard');
}
require 'vendor/autoload.php'; // Include the Google API Client Library

function generateRandomString($length = 30) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $random_string = '';

    for ($i = 0; $i < $length; $i++) {
        $random_string .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $random_string;
}
$user_agent = $_SERVER['HTTP_USER_AGENT'];
$deviceString = "";
$current_time_in_ist = date('Y-m-d H:i:s');
if (preg_match('/iPhone|iPad|iPod/i', $user_agent)) {
    $deviceString = "Device: Apple iOS";
} elseif (preg_match('/Android/i', $user_agent)) {
    $deviceString = "Device: Android";
} elseif (preg_match('/Windows Phone/i', $user_agent)) {
    $deviceString = "Device: Windows Phone";
} elseif (preg_match('/Macintosh|Mac OS X/i', $user_agent)) {
    $deviceString = "Device: Macintosh (Mac)";
} elseif (preg_match('/Windows/i', $user_agent)) {
    $deviceString = "Device: Windows";
} elseif (preg_match('/Linux/i', $user_agent)) {
    $deviceString = "Device: Linux";
} else {
    $deviceString = "Device: Unknown";
}

$user_agent = $_SERVER['HTTP_USER_AGENT'];
$browserData = "";

// Use a regular expression to extract browser and version
if (preg_match('/(MSIE|Edge|Firefox|Chrome|Safari|Opera)[\/\s](\d+\.\d+)/i', $user_agent, $matches)) {
    $browser = $matches[1]; // Browser name
    $version = $matches[2]; // Browser version
    $browserData = "Browser: " . $browser . " " . $version;
} else {
    $browserData = "Browser information not found.";
}


session_start();


$client = new Google_Client();
$client->setClientId($site_data['client_id']);
$client->setClientSecret($site_data['client_secret']);
$client->setRedirectUri(WEBSITE_URL.'/google-login');
$client->setScopes("openid email profile");
if (isset($_GET['code'])) {
    $client->authenticate($_GET['code']);
    $access_token = $client->getAccessToken();

    if ($access_token) {
        // Token is valid; let's fetch the email
        $service = new Google_Service_Oauth2($client); // Create the service object
        $userInfo = $service->userinfo->get();
        $email = $userInfo->getEmail();
        $profilePicUrl = $userInfo->getPicture();
        $sql = $conn->prepare("SELECT * FROM user_data WHERE email=?");
$sql->bind_param("s", $email);
$sql->execute();
$result = $sql->get_result();
$data = $result->fetch_assoc();
// check if user exist
if ($result->num_rows > 0) {
if($data['status']=="1"){
$user_id=$data['id'];
$token = generateRandomString();
$user_ip = $_SERVER['REMOTE_ADDR'];
$_SESSION['token'] = $token;
setcookie('remember_me', $token, [
	'expires' => strtotime('+1 months'),
	'path' => '/',
	'domain' => $_SERVER['HTTP_HOST'],
	'secure' => true,
	'httponly' => true,
	'samesite' => 'radium'
]);
if($data['type'] == "admin"){
$_SESSION['admin'] = $token;
}
// if($data['image_url'] != $profilePicUrl){
// $sql234 = $conn->query("UPDATE user_data SET image_url='".$profilePicUrl."' WHERE id='".$data['id']."'");
// }
$sql22 = mysqli_query($conn,"SELECT * FROM login_token WHERE user_id='".$user_id."'");
if(mysqli_num_rows($sql22)==0){
$sql2 = $conn->query("INSERT INTO login_token(user_id, token, create_date, device, browser, ip, status) VALUES ('".$user_id."','".$token."','".$current_time_in_ist."','".$deviceString."','".$browserData."','".$user_ip."','1')");
}else{
$sql23 = $conn->query("UPDATE login_token SET token='".$token."' WHERE user_id='".$user_id."'");
}
redirect('dashboard');
}else{
    header('Location: index?msg=block');
}    
} else {
   header('Location: index?msg=not_found');
}
    exit;
    } else {
           redirect('index');

    }
    
} else {
    $auth_url = $client->createAuthUrl();
    header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
}
