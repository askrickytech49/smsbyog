<?php

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
    if(in_array($final_admin['type'], ["admin", "super_admin"])){   
    $server_id = $_POST['server_id'];
$service_id = $_POST['service_id'];
$type = $_POST['type'];
$user_email = $_POST['user_email'];
$custom_value = $_POST['custom_value'];



if($server_id !="" && $service_id !="" && $type !="" && $user_email !="" && $custom_value !=""){
$sql=mysqli_query($conn,"SELECT * FROM user_data WHERE email='$user_email'");
if(mysqli_num_rows($sql) !=0){
$datas=mysqli_fetch_assoc($sql);
$id=$datas['id'];
$sql2 = mysqli_query($conn,"SELECT * FROM custom_price WHERE server_id='".$server_id."' AND service_id='".$service_id."' AND user_id='".$id."'");
if(mysqli_num_rows($sql2) == 0){
mysqli_query($conn,"INSERT INTO `custom_price` (`user_id`, `type`, `discount`, `service_id`, `server_id`) VALUES ('".$id."', '".$type."', '".$custom_value."', '".$service_id."', '".$server_id."')");
echo'<script>
$(document).ready(function() {
    Swal.fire({
        title: "Success",
        text: "Added Success",
        icon: "success",
        button: "Ok",
        
    });
});
</script>';   
}else{
    echo'<script>
    $(document).ready(function() {
        Swal.fire({
            title: "Error!",
            text: "Already Added",
            icon: "error",
            button: "Ok",
            
        });
    });
</script>';     
}
}else{
echo'<script>
    $(document).ready(function() {
        Swal.fire({
            title: "Error!",
            text: "User Not Found",
            icon: "error",
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