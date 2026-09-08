<?php
function generateRandomString($length = 20) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $random_string = '';

    for ($i = 0; $i < $length; $i++) {
        $random_string .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $random_string;
}

include __DIR__ . '/../../../../include/config.php';
$current_time_in_ist = date('Y-m-d H:i:s');        

if(!isset($_GET['token']) || $_GET['token'] ==""){
echo'{"status": "300","message": "token missing"}';
} else {
$token = mysqli_real_escape_string($conn,$_GET['token']); 
// $find_token = new radiumsahil();
$check_token = check_token($token, $conn);
// $find_token->closeConnection();
if($check_token === false){
echo'{"status": "300","msg": "Token Expired Please Logout And Login Again"}';
}else{
$sql=$conn->query("SELECT* FROM crypto_data WHERE network='sol' && user_id='$check_token' && status='2'");
if(mysqli_num_rows($sql)== 0){

$endpoint = 'https://api.cryptomus.com/v1/wallet'; // Replace with the actual API endpoint URL
$order_id = generateRandomString();
$crypto_currency = "SOL";
$crypto_network = "sol";

$data = array(
    "currency" => $crypto_currency,
    "network" => $crypto_network,
    "order_id" => $order_id,
    "url_callback" => $website_url."/api/recharge/crypto/callback.php"
);
$data = json_encode($data);
$sign = md5(base64_encode($data) . $site_data['crypto_api_key']);
$headers = array(
    'merchant: '.$site_data['crypto_merchant_id'].'',
    'sign: '.$sign.'',
    'Content-Type: application/json'
);

$ch = curl_init($endpoint);

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
$address = $data['result']['address'];
if($address != ""){
$sql6 = mysqli_query($conn,"INSERT INTO crypto_data(user_id, order_id, address, currency, network, active_time, status) VALUES ('".$check_token."','".$order_id."','".$address."','".$crypto_currency."','".$crypto_network."','".$current_time_in_ist."','2')");
echo'{"ok":true,"message":"ADDRESS GENERATED.."}';
}else{
echo'{"ok":false,"message":"Something Went Wrong #2"}';
}

}else{
echo'{"ok":false,"message":"Already Have Address"}';
}
}

}
?>