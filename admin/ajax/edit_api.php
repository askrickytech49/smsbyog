<?php
$api_name = $_POST['api_name'];
$api_url = $_POST['api_url'];
$api_key = $_POST['api_key'];
$rate = $_POST['rate'];
$profit_amount = $_POST['profit_amount'];
$id = $_POST['id'];

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
if($api_name !="" && $api_url !="" && $api_key !=""  && $rate !=""  && $profit_amount !=""  &&  $id !=""){
 
  $sql = "UPDATE api_detail SET api_name='$api_name', api_key='$api_key', profit_amount='$profit_amount', rate='$rate', api_url='$api_url' WHERE id='$id'";
  $done=mysqli_query($conn,$sql);
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