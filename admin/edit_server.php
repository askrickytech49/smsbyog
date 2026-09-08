<?php
include("auth.php");
if(!isset($_SESSION['token'])){
	if(isset($_COOKIE['remember_me'])) {
		$radium_token = $_COOKIE['remember_me'];
		$_SESSION['token'] = $radium_token;
	}else{
	header('Location: login.php'); exit;
	}
}
$admin_sql = mysqli_query($conn,"SELECT * FROM login_token WHERE token='".$_SESSION['token']."'");
if(mysqli_num_rows($admin_sql) == 0) {
    header('Location: login.php'); exit;
}else{
$admin_data = mysqli_fetch_array($admin_sql);
$admin_sql2 = mysqli_query($conn,"SELECT * FROM user_data WHERE  id='".$admin_data['user_id']."' AND status='1'");
$final_admin = mysqli_fetch_array($admin_sql2);
if($final_admin['type'] == "admin"){
if($_GET['id']==""){
echo"invalid id";
return;
}else{
$id = $_GET['id'];
}
$sql=mysqli_query($conn,"SELECT * FROM otp_server WHERE id='".$id."'");
if(mysqli_num_rows($sql)==0){
echo"invalid id";
return;
}
$server_data = mysqli_fetch_assoc($sql);
$sql2=mysqli_query($conn,"SELECT * FROM api_detail WHERE id='".$server_data['api_id']."'");
$api_data = mysqli_fetch_assoc($sql2);


?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>Edit Server - @getallscripts</title>
<?php include("include/head.php"); ?>  
</head>

<body id="page-top">
  <div id="wrapper">
    <!-- Sidebar -->
<?php include ("include/slidebar.php"); ?>
    <!-- Sidebar -->
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <!-- TopBar -->
<?php include ("include/topbar.php"); ?>              
        <!-- Topbar -->

        <!-- Container Fluid-->
        <div class="container-fluid" id="container-wrapper">
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <!-- <h1 class="h3 mb-0 text-gray-800">Dashboard</h1> -->
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active" aria-current="page">Edit Server </li>
            </ol>
          </div>

          <div class="row">
            <div class="col">
              <!-- Form Basic -->
              <div class="card mb-4" id="loading">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">Server Details </h6>
                </div>
                <div class="card-body">
                <div class="form-group">
                      <label for="exampleInputPassword1">Select Api</label>
                   <?php
              $query = "SELECT * FROM api_detail";
            $statement = mysqli_query($conn,$query);
                                            ?>  
                  <select name="server_id" id="api_id" class="form-control mb-3">
                      <?php
                                                   while($row=mysqli_fetch_array($statement))
 
                                                    {
                                                        ?>
                                                            <option value="<?php echo $row['id']; ?>"><?php echo $row['api_name']; ?></option>
                                                        <?php
                                                    }
                                                ?>
                  </select>
                     </div>
                     <div class="form-group">
                      <label for="exampleInputEmail1">Current Api Name</label>
                      <input type="text" class="form-control" id="api_name" value="<?php echo $api_data['api_name'];?>" placeholder=""readonly>
                    <input type="hidden" id="id" value="<?php echo $id;?>">
                    </div>
                    <div class="form-group">
                      <label for="exampleInputPassword1">Server Name</label>
                      <input type="text" class="form-control" id="server_name" value="<?php echo $server_data['server_name'];?>" placeholder="Enter Service Price">
                    </div>
                    <div class="form-group">
                      <label for="exampleInputPassword1">Country Id</label>
                      <input type="text" class="form-control" id="country_name" value="<?php echo $server_data['server_code'];?>" placeholder="Enter Service Name">
                    </div>
                   <button type="submit" id="update" class="btn btn-primary w-100 mb-2">Submit</button><br>
                </div>
              
        <!---Container Fluid-->
      </div>
      <!-- Footer -->
<?php include("include/copyright.php"); ?>
      <!-- Footer -->
    </div>
  </div>

  <!-- Scroll to top -->
  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>
<?php include("include/script.php"); ?>
<script>
$(document).ready(function() {
    // Attach a click event handler to the button

    $("#update").click(function() {
        Notiflix.Block.Dots('#loading', 'Please Wait');
    var name = $("#server_name").val();
    var slug = $("#country_name").val();
    var description = $("#api_id").val();
      var id = $("#id").val(); 
        var params = {
        name: name,
        slug: slug,
        description: description,
         id: id,
        };

        $.ajax({
            type: "POST",
            url: "ajax/edit_category.php",
            data: params,
            error: function (e) {
                console.log(e);
            },
            success: function (data) {
                   Notiflix.Block.Remove('#loading');
             $('#update').html(data);
                $('#update').html("Update");

            }
        });
    });
});
</script>

</body>

</html>
<?php
}else{
    header('Location: login.php'); exit;
}
}
?>