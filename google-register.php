<!doctype html>
<html lang="en" dir="ltr">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <meta http-equiv="X-UA-Compatible" content="ie=edge">
   <title> Google Register - <?php echo $web_name;?></title>     
</head>

<body>
    <wc-toast id="tt" position="top-right"> </wc-toast>


<?php
require_once __DIR__ . "/include/config.php";

if(isset($_SESSION['token'])){
	redirect('index');
}

require "vendor/autoload.php";

function generateRandomString($length = 10)
{
    $characters =
        "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $random_string = "";

    for ($i = 0; $i < $length; $i++) {
        $random_string .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $random_string;
}

function createUser($conn,$name,$username,$email,$password,$type,$img_url,$status) {
    $date = date("Y-m-d H:i:s");
    $sql = $conn->prepare("INSERT INTO user_data(name, username, email, password, type, register_date, image_url, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $sql->bind_param("sssssssi",$name,$username,$email,$password,$type,$date,$img_url,$status);
    $sql->execute();
}

function createWallet($conn, $user_id, $balance, $total_recharge, $total_otp)
{
    $sql = $conn->prepare("INSERT INTO user_wallet(user_id, balance, total_recharge, total_otp) VALUES (?, ?, ?, ?)");
    $sql->bind_param("siii", $user_id, $balance, $total_recharge, $total_otp);
    $sql->execute();
}

$client = new Google_Client();
$client->setClientId($site_data["client_id"]);
$client->setClientSecret($site_data["client_secret"]);
$client->setRedirectUri($site_data["web_url"] . "/google-register");
$client->setScopes("openid email profile");

if (isset($_GET["code"])) {
    $client->authenticate($_GET["code"]);
    $access_token = $client->getAccessToken();

    if ($access_token) {
        $service = new Google_Service_Oauth2($client);
        $userInfo = $service->userinfo->get();
        $email = mysqli_real_escape_string($conn, $userInfo->getEmail());
        $name = mysqli_real_escape_string($conn,$userInfo->getGivenName()." ".$userInfo->getFamilyName());
        $username = generateRandomString();
        $password = md5($userInfo->getId().'@tg');

        $sql = $conn->prepare("SELECT * FROM user_data WHERE email=?");
        $sql->bind_param("s", $email);
        $sql->execute();
        $result = $sql->get_result();

        if ($result->num_rows == 0) {
            $type = "user";
            $img_url = $userInfo->getPicture();
            $status = 1;

            createUser($conn,$name,$username,$email,$password,$type,$img_url,$status);

            $inserted_id = $conn->insert_id;
            $balance = 0;
            $total_recharge = 0;
            $total_otp = 0;

            createWallet($conn,$inserted_id,$balance,$total_recharge,$total_otp);
             echo"<script type='module'>
      import { toast } from 'https://cdn.skypack.dev/wc-toast';
      toast.success('Register Successful');
      </script>";           
            header("Refresh:2; url=index");
            exit();
        } else {
            header("Location: /");
            exit();
        }
    } else {
        header("Location: /");
        exit();
    }
} else {
    header("Location: ");    
}

$auth_url = $client->createAuthUrl();
header("Location: " . filter_var($auth_url, FILTER_SANITIZE_URL));
exit();
?>
</body>
</html>