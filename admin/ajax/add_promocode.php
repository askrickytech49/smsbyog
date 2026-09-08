<?php
include("../auth.php");
$current_time_in_ist = date('Y-m-d H:i:s');        

$promo_code = $_POST['promo_code'];
$for_user = $_POST['for_user'];
$per_amount = $_POST['per_amount'];

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
if($promo_code !="" && $for_user !="" && $per_amount !=""){
if(is_numeric($for_user) && is_numeric($per_amount)){
if ($promo_code <= 0 && $for_user <= 0) {
echo'<script>
    $(document).ready(function() {
        Swal.fire({
            title: "Warning!",
            text: "The value is not less than 0.",
            icon: "warning",
            button: "Ok",
            
        });
    });
</script>';   
}else{

$sql=mysqli_query($conn,"SELECT * FROM promocode WHERE promocode='$promo_code'");
if(mysqli_num_rows($sql) ==0){
    $sql3 = mysqli_query($conn,"INSERT INTO promocode (promocode, for_user, amount, date) VALUES ('$promo_code','$for_user','$per_amount','$current_time_in_ist')");

echo'<script>
    $(document).ready(function() {
        Swal.fire({
            title: "Success",
            text: "Details Added Successful",
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
            text: "Service id Already Added",
            icon: "warning",
            button: "Ok",
            
        });
    });
</script>';   
}
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