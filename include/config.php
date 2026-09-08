<?php
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.
//  error_reporting(0);
date_default_timezone_set('Africa/Lagos');

define('DB_SERVER', 'localhost'); //localhost
define('DB_USERNAME', 'u223553481_smsbyog_user'); // db username
define('DB_PASSWORD', '~u9AycO=C'); // db password
define('DB_DATABASE', 'u223553481_smsbyog'); // db name
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


define('TIGER_SMS_API_KEY', 'XndMC7R152i2KtNP3lLxh9mvmXfCRBCO');

// ================= SQUAD CONFIG =================
// define('SQUAD_SECRET_KEY', 'sk_280a02d675ce4081570e746855852c04aaa4d1a1');

// XIXAPAY 

define('XIXA_BUSINESS', '325a25c99df02668a41c6afaac5d07218058224a');
define('XIXA_SECRET', '6918086853e184a1cdfbb3e1a2a8b2b0f025422622647c54ef21bfa03e0e07cbf8a86450e32a655fe407a16902a253ec87254c5ba24ebadcef5778a3');
define('XIXA_API', '3ae1c4869449be57e8d4cded27177f68670be5d0');

define('POCKETFI_BUSINESS', '29987');
define('POCKETFI_API', '30618|gll6BJCl47tswJHCpKDA3Ypn8rFT8tdqabfPQQ1ce5ae9fee');
define('POCKETFI_SECRET', '7ff0fe1c60898b6259976dbe7403dd8c51b7e574e5d47b70a05d1c3242d3170f');


// ===== CRYPTOMUS =====
define('CRYPTOMUS_API_KEY', 'XEwi1JAohAotUJ3rG8YU6VR2e7pLKi4yEiSuIWp0DlWbnD0gcSYvUAlI6G2AV6fzknHHjE2jB4AC03UnQvMQLptk2LnN7juNGzEB3771iXCsDT4P5DGITMCoqehrlNK3');
define('CRYPTOMUS_MERCHANT_ID', '2c91101a-cb07-45ea-a37f-3c487c6fa792');

// ===== KORAPAY =====
define('KORAPAY_SECRET_KEY', 'sk_live_KDXp5wL1JRXnH8XCSp1ZiygbdauWdzAxhvdhAqLc');

// ===== LIMITS =====
define('MIN_USD', 1);
define('MAX_USD', 10000);

define('MIN_NGN', 100);
define('MAX_NGN', 5000000);


$site_sql = $conn->query("SELECT * FROM settings WHERE id='1'");
$site_data = $site_sql->fetch_assoc();
$theam = $site_data['theam'];
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$hosted_folder = dirname($_SERVER['SCRIPT_NAME']);

$website_url = $protocol.'://'.$_SERVER['HTTP_HOST'];

// Base path for redirects — works in both root and subfolder installs
$base_path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
if ($base_path === '.' || $base_path === '') $base_path = '';

function redirect($path) {
    global $base_path;
    // If it's already a full URL, use as-is
    if (strpos($path, 'http') === 0) {
        header('Location: ' . $path);
    } else {
        header('Location: ' . $base_path . '/' . ltrim($path, '/'));
    }
    exit;
}

$web_name=$site_data['web_name'];

define("THEAM", $theam);
define("WEBSITE_URL", $website_url);
define("SECRET_KEY", "#");
function check_token($token, $conn) {
    $sql = mysqli_query($conn, "SELECT * FROM `login_token` WHERE token='$token' and status='1'");
    if(mysqli_num_rows($sql) == 0) {
        return false;
    } else {
        $data = mysqli_fetch_assoc($sql);
        $user_id = $data['user_id'];
        $sql20 = mysqli_query($conn, "SELECT * FROM `user_wallet` WHERE user_id='$user_id'");
        $data1 = mysqli_fetch_assoc($sql20);
         check_activities($data1['balance'], $data1['total_otp'], $data1['total_recharge'], $user_id, $conn);
        $sql2 = mysqli_query($conn, "SELECT * FROM `user_data` WHERE id='$user_id' and status='1'");
        if(mysqli_num_rows($sql2) == 0) {
            return false;
        } else {
            return $user_id;
        }
    }
}
function check_activities($balance, $total_otp, $lifetime, $token, $conn) {
    $oauthid = $token;
    
       function negativebal($value) {
        return ($value < 0) ? 1 : 0;
    }

    if (negativebal($balance)==1 || negativebal($total_otp)==1 || negativebal($lifetime)==1 || ($balance > $lifetime)) {
        $sql2 = mysqli_query($conn, "SELECT * FROM user_data WHERE id = '$oauthid' AND status = '2'");
        
        if (mysqli_num_rows($sql2) > 0) {
            return "Already Action";
        } else {
            mysqli_query($conn, "UPDATE user_data SET status = '2' WHERE id = '$oauthid'");
            return "#1";
        }
    }
    
    return "No action required";
}
function getfunction($endpoint, $api) {
        $headers = [
            'X-API-Key: ' . $api,
            'Accept: application/json',
        ];
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
        ]);
    
        $response = curl_exec($curl);
        curl_close($curl);
        
        $response = json_decode($response, true);
        
        return $response;
    }
function purchaser($country, $service, $route, $api) {

        $headers = [
            'X-API-Key: ' . $api,
            'Accept: application/json',
            'Content-Type: application/json'
        ];
    
        $postData = json_encode([
            'serviceCode' => $service,
            'countryCode' => $country
        ]);
    
        // --- CURL START ---
        $ch = curl_init($route);
    
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $response =json_decode($response);
    
        // curl_close($ch);
        // $endpoint = "sms-otp/request";
        return $response;
    }
?>