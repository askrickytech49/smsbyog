<?php
$user_id = $_POST['user_id'];
$balance = $_POST['balance'];
$recharge = $_POST['recharge'];
$total_otp = $_POST['total_otp'];


include("../auth.php");
function sendTelegramMessage($chat_id, $message_text) {
    $apiToken = "6609792423:AAFU7uPwAch1Xz7s4jrFQIaNc3NYMTvtEfs"; // Replace with your bot's API token

    $data = [
        'chat_id' => $chat_id,
        'text' => $message_text
    ];

    $url = "https://api.telegram.org/bot$apiToken/sendMessage";

    // Initialize curl session
    $ch = curl_init($url);

    // Configure curl options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    
    // Execute the curl session
    $response = curl_exec($ch);
    
    // Check for errors
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    
    // Close the curl session
    curl_close($ch);
    
    // Return the response
    return $response;
}
if(isset($_SESSION['token']) =="") {
    if(isset($_COOKIE['remember_me'])) {
		$radium_token = $_COOKIE['remember_me'];
		$_SESSION['token'] = $radium_token;
	}else{
        echo"<script>  setTimeout(function(){
            window.location.href = 'login';
         }, 100);
</script>"; 
	}       
}else{
    $admin_sql = mysqli_query($conn,"SELECT * FROM login_token WHERE token='".$_SESSION['token']."'");
    if(mysqli_num_rows($admin_sql) == 0) {
        echo"<script>  setTimeout(function(){
            window.location.href = 'login';
         }, 100);
</script>"; 
    }else{
    $admin_data = mysqli_fetch_array($admin_sql);
    $admin_sql2 = mysqli_query($conn,"SELECT * FROM user_data WHERE  id='".$admin_data['user_id']."' AND status='1'");
    $final_admin = mysqli_fetch_array($admin_sql2);
    if(in_array($final_admin['type'], ["admin", "super_admin"])){   
if($user_id !="" && $balance !="" && $recharge !="" && $total_otp !=""){
if(is_numeric($balance) && is_numeric($recharge) && is_numeric($total_otp)){
$sql=mysqli_query($conn,"SELECT * FROM user_data WHERE id='$user_id'");
if(mysqli_num_rows($sql) !=0){
$data3=mysqli_fetch_assoc($sql);
$user_id=$data3['id'];
  $sql3 = mysqli_query($conn,"UPDATE user_wallet SET balance='$balance' , total_recharge='$recharge' , total_otp='$total_otp' WHERE user_id='$user_id'");
  $message1 = "⭐ NEW USER BAN ⭐\n";
  $message1 .= "Email: ".$user_id."\n";
  $message1 .= "Balance: ₦" . $balance . "\n";
  $message1 .= "Total Recharge: ₦" . $recharge . "\n";
  $message1 .= "Total Otp: " . $total_otp."\n";
 $response = sendTelegramMessage("-1002195298182", $message1);
echo'<script>
    $(document).ready(function() {
        Swal.fire({
            title: "Success",
            text: "Details Update Successful",
            icon: "success",
            button: "Ok",
            
        });
    });
</script>';  
}else{
echo'<script>
    $(document).ready(function() {
        Swal.fire({
            title: "Warning!",
            text: "Invalid User Id",
            icon: "warning",
            button: "Ok",
            
        });
    });
</script>';   
}
}else {
echo'<script>
    $(document).ready(function() {
        Swal.fire({
            title: "Warning!",
            text: "Please Enter Numerical Values",
            icon: "warning",
            button: "Ok",
            
        });
    });
</script>';   
}
}else{
echo'<script>
    $(document).ready(function() {
        Swal.fire({
            title: "Warning!",
            text: "Please fill all the fields first",
            icon: "warning",
            button: "Ok",
            
        });
    });
</script>';   
}
}else{
    echo"<script>  setTimeout(function(){
        window.location.href = 'login';
     }, 100);
</script>";     
}
}
}
mysqli_close($conn);

?>