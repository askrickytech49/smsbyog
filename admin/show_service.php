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
  if (in_array($final_admin['type'], ["admin", "super_admin"])) {
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
      <title>Show Service- @getallscripts</title>
      <?php include ("include/head.php"); ?>
    </head>
    <script>
      $(document).ready(function () {
        // Remove "active" class from all <a> elements
        $('#dashboard').removeClass("active");

        // Add "active" class to the specific element with ID "faq"
        $("#show_service").addClass("active");
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
                  <li class="breadcrumb-item active" aria-current="page">Show Service</li>
                </ol>
              </div>

              <div class="row">
                <div class="col">
                  <!-- Form Basic -->
                  <div class="card mb-4" id="loading">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                      <h6 class="m-0 font-weight-bold text-primary">Show Service</h6>
                    </div>
                    <div class="card-body">
                      <div class="form-group">
                        <?php
                        $query = "SELECT * FROM otp_server";
                        $statement = mysqli_query($conn, $query);
                        ?>
                        <form action="view_service" method="get">
                          <select name="id" class="form-control mb-3">
                            <?php
                            while ($row = mysqli_fetch_array($statement)) {
                              ?>
                              <option value="<?php echo $row['id']; ?>"><?php echo $row['server_name']; ?></option>
                              <?php
                            }
                            ?>
                          </select>
                      </div>
                      <button type="submit" id="update" class="btn btn-primary w-100 mb-2">Submit</button><br>
                    </div>
                    </form>
                    <!---Container Fluid-->
                  </div>
                  <!-- Footer -->
                  <?php // include("include/copyright.php"); ?>
                  <!-- Footer -->
                </div>
              </div>

              <!-- Scroll to top -->
              <a class="scroll-to-top rounded" href="#page-top">
                <i class="fas fa-angle-up"></i>
              </a>
              <?php include ("include/script.php"); ?>

    </body>

    </html>
    <?php
  } else {
    header('Location: login.php'); exit;
  }
}
mysqli_close($conn);
?>