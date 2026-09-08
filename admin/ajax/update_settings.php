<?php
$token = $_POST['token'];
$mechant_id = $_POST['mechant_id'];
$payment_qr = $_POST['payment_qr'];
$payment_upi = $_POST['payment_upi'];
$minimum_add = $_POST['minimum_add'];
include("../auth.php");
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
    if($final_admin['type'] == "admin"){   
if($mechant_id !="" && $token !="" && $payment_qr !="" && $payment_upi !="" && $minimum_add !=""){
  $sql3 = mysqli_query($conn,"UPDATE settings SET upi_merchant_token='$token' , upi_merchant_id='$mechant_id' , upi_qr='$payment_qr' ,  upi_id='$payment_upi' , upi_min_recharge='$minimum_add' WHERE id='1'");
 
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