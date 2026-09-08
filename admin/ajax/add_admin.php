<?php
include("../auth.php");
$email = $_POST['email'];
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
if($email !=""){
$sql=mysqli_query($conn,"SELECT * FROM user_data WHERE email='$email'");
if(mysqli_num_rows($sql) !=0){
$datas=mysqli_fetch_assoc($sql);
$id=$datas['id'];
mysqli_query($conn,"UPDATE user_data SET type='admin' WHERE id='".$id."'");
echo'<script>
    $(document).ready(function() {
        Swal.fire({
            title: "Success!",
            text: "Admin Post Assing",
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