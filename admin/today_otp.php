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
if(in_array($final_admin['type'], ["admin", "super_admin"])){
$sql = mysqli_query($conn,"
SELECT 
active_number.*, 
user_data.email, 
api_detail.display_name AS api_name
FROM active_number
LEFT JOIN user_data ON user_data.id = active_number.user_id
LEFT JOIN api_detail ON api_detail.id = active_number.api_id
WHERE active_number.status='1'
ORDER BY active_number.id DESC
");

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>Number History- MyOGSMS</title>
<?php include("include/head.php"); ?>  
  <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
  
</head>
<script>
        $(document).ready(function() {
            // Remove "active" class from all <a> elements
            $('#dashboard').removeClass("active");
            
            // Add "active" class to the specific element with ID "faq"
            $("#today_otp").addClass("active");
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
              <li class="breadcrumb-item active" aria-current="page">Number History</li>
            </ol>
          </div>

        <!---Container Fluid-->
                  <!-- Row -->
          <div class="row">
            <!-- Datatables -->
            <div class="col">
              <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">Number History</h6>
                </div>
                <div class="table-responsive p-3">
                  <table class="table align-items-center table-flush" id="dataTable">
                    <thead class="thead-light">
                      <tr>
                          <th>Email</th>
                                                <th>Number</th>
                                                <th>API</th>
                                                <th>Buy Time</th>
                                                <th>Service name</th>
                                                <th>Server id</th>
                                                <th>Price</th>
                                                <th>Sms</th>
                       </tr>
                    </thead>
                     <tbody>
                                                                   <?php
while($data = mysqli_fetch_assoc($sql)){
?>
<tr>

<td><?php echo $data['email']; ?></td>

<td><?php echo $data['number']; ?></td>

<td>
<span class="badge badge-primary"><?php echo $data['api_name']; ?></span>
</td>

<td><?php echo $data['buy_time']; ?></td>

<td><?php echo $data['service_name']; ?></td>

<td><?php echo $data['server_id']; ?></td>
<td>₦<?php echo number_format($data['service_price'],2); ?></td>
<td><?php echo $data['sms_text']; ?></td>

</tr>

<?php } ?>
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

    $('#dataTable').DataTable({
        pageLength: 10,
        lengthMenu: [10,25,50,100],
        order: [[3, "desc"]]
    });

    $('#dataTableHover').DataTable();

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