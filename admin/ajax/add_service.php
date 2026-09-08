<?php
$server_id = $_POST['server_id'];
$service_code = $_POST['service_code'];
$name = $_POST['name'];
$price = $_POST['price'];

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
if($server_id !="" && $service_code !="" && $name !="" && $price !=""){
if(is_numeric($price)){
$sql=mysqli_query($conn,"SELECT * FROM service WHERE service_id='$service_code' and server_id='$server_id'");
if(mysqli_num_rows($sql) ==0){
    $sql3 = mysqli_query($conn,"INSERT INTO service (server_id, service_price, service_name, service_id, status) VALUES ('$server_id','$price','$name','$service_code','1')");

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