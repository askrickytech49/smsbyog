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
$sql=mysqli_query($conn,"SELECT * FROM promocode ORDER BY id DESC");

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>Promcodes- @getallscripts</title>
<?php include("include/head.php"); ?>  
  <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
  
</head>
<script>
        $(document).ready(function() {
            // Remove "active" class from all <a> elements
            $('#dashboard').removeClass("active");
            
            // Add "active" class to the specific element with ID "faq"
            $("#promocode").addClass("active");
        });
    </script>
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
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active" aria-current="page">Promocode</li>
            </ol>
          </div>

        <!---Container Fluid-->
                  <!-- Row -->
          <div class="row">
            <!-- Datatables -->
            
            <div class="col">
              <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
<a href="add_promocode"><button class="btn btn-sm btn-primary" >Add Promocode</button></a>               
                </div>
                <div class="table-responsive p-3">
<?php


if (isset($_POST['delete'])) {
    $unban = $_POST['id'];
$sql2=mysqli_query($conn,"DELETE FROM `promocode` WHERE `id` ='".$unban."'");
echo'<div class="alert alert-success" role="alert">
       Delete success
    </div>';
echo"<meta http-equiv='refresh' content='0'>";
    
}
?>                   
                  <table class="table align-items-center table-flush" id="dataTable">
                    <thead class="thead-light">
                      <tr>
                                                <th>Promocode</th>
                                                 <th>For User / Total Collect</th>
                                               <th>Create Date</th>
                                               <th>Delete</th>
                                                  </tr>
                    </thead>
                     <tbody>
                                                                   <?php
        $i=1;
        while($data=mysqli_fetch_array($sql)){
        $code_id=$data['id'];
       $sql2=mysqli_query($conn,"SELECT * FROM promocode_history WHERE code_id='".$code_id."'");
        $sql3=mysqli_num_rows($sql2);
    
        ?>
          <tr>
           <td><?php echo $data['promocode'];?></td>
           <td><?php echo $data['for_user'];?>/<?php echo $sql3;?></td>
          <td><?php echo $data['date'];?></td>
       <td><form method="post"><input type="hidden" name="id" value="<?php echo $data['id'];?>"><button class="btn btn-sm btn-danger" type="submit" name="delete" >Delete</button></form></td>                                                                          
          </tr>                                       
         <?php
          $i++;
          }
          ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            
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
  <!-- Page level plugins -->
  <script src="vendor/datatables/jquery.dataTables.min.js"></script>
  <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
  <script>
    $(document).ready(function () {
      $('#dataTable').DataTable(); // ID From dataTable 
      $('#dataTableHover').DataTable(); // ID From dataTable with Hover
    });
  </script>  
</body>

</html>
<?php
}else{
    header('Location: login.php'); exit;
}
}
mysqli_close($conn);

?>