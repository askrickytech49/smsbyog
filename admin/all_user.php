<?php
include ("auth.php");
if (!isset($_SESSION['token'])) {
  if (isset($_COOKIE['remember_me'])) {
    $radium_token = $_COOKIE['remember_me'];
    $_SESSION['token'] = $radium_token;
  } else {
    header('Location: login.php'); exit;
  }
}
$admin_sql = mysqli_query($conn, "SELECT * FROM login_token WHERE token='" . $_SESSION['token'] . "'");
if (mysqli_num_rows($admin_sql) == 0) {
  header('Location: login.php'); exit;
} else {
  $admin_data = mysqli_fetch_array($admin_sql);
  $admin_sql2 = mysqli_query($conn, "SELECT * FROM user_data WHERE  id='" . $admin_data['user_id'] . "' AND status='1'");
  $final_admin = mysqli_fetch_array($admin_sql2);
  if ($final_admin['type'] == "admin") {
    $sql = mysqli_query($conn, "SELECT * FROM user_data ORDER BY id DESC");

    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
      <title>All User - @getallscripts</title>
      <?php include ("include/head.php"); ?>
      <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">

    </head>
    <script>
      $(document).ready(function () {
        // Remove "active" class from all <a> elements
        $('#dashboard').removeClass("active");

        // Add "active" class to the specific element with ID "faq"
        $("#all_user").addClass("active");
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
                <!-- <h1 class="h3 mb-0 text-gray-800">Dashboard</h1> -->
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="#">Home</a></li>
                  <li class="breadcrumb-item active" aria-current="page">All User</li>
                </ol>
              </div>

              <!---Container Fluid-->
              <!-- Row -->
              <div class="row">
                <!-- Datatables -->
                <div class="col-lg-12">
                  <div class="card mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                      <h6 class="m-0 font-weight-bold text-primary">All User</h6>
                    </div>
                    <div class="table-responsive p-3">
                      <table class="table align-items-center table-flush" id="dataTable">
                        <thead class="thead-light">
                          <tr>
                            <th>Email</th>
                            <th>Balance</th>
                            <th>Life Time Recharge</th>
                            <th>Total Otp</th>
                            <th>Status</th>
                            <th>Action</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php
                          $i = 1;
                          while ($data = mysqli_fetch_array($sql)) {
                            $user_id = $data['id'];
                            $sql2 = mysqli_query($conn, "SELECT * FROM user_wallet WHERE user_id='" . $user_id . "'");
                            $sql3 = mysqli_fetch_assoc($sql2);
                            if ($data['status'] == "1") {
                              $status = "badge badge-success";
                              $status1 = "Active";
                            } else {
                              $status = "badge badge-danger";
                              $status1 = "Blocked";
                            }
                            ?>
                            <tr>
                              <td><?php echo $data['email']; ?></td>
                              <td><?php echo $sql3['balance']; ?></td>
                              <td><?php echo $sql3['total_recharge']; ?></td>
                              <td><?php echo $sql3['total_otp']; ?></td>
                              <td><span class="<?php echo $status; ?>"><?php echo $status1; ?></span></td>
                              <td><a href="edit_user?user_id=<?php echo $user_id; ?>" class="btn btn-sm btn-primary">Edit</a>
                              </td>
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
              <?php include ("include/copyright.php"); ?>
              <!-- Footer -->
            </div>
          </div>

          <!-- Scroll to top -->
          <a class="scroll-to-top rounded" href="#page-top">
            <i class="fas fa-angle-up"></i>
          </a>
          <?php include ("include/script.php"); ?>
          <!-- Page level plugins -->
          <script src="vendor/datatables/jquery.dataTables.min.js"></script>
          <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
          <script>
            $(document).ready(function () {
        $('#dataTable').DataTable({
                                ordering: false
                            });
              $('#dataTableHover').DataTable(); // ID From dataTable with Hover
            });
          </script>
    </body>

    </html>
    <?php
  } else {
    header('Location: login.php'); exit;
  }
}
?>