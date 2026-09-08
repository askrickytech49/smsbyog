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
}

$admin_data = mysqli_fetch_array($admin_sql);
$admin_sql2 = mysqli_query($conn,"SELECT * FROM user_data WHERE id='".$admin_data['user_id']."' AND status='1'");
$final_admin = mysqli_fetch_array($admin_sql2);

if(!in_array($final_admin['type'], ["admin", "super_admin"])){
    header('Location: login.php'); exit;
}

$msg = "";
if (isset($_POST['update_bank'])) {
    $bank_name = mysqli_real_escape_string($conn, trim($_POST['bank_name']));
    $account_name = mysqli_real_escape_string($conn, trim($_POST['account_name']));
    $account_number = mysqli_real_escape_string($conn, trim($_POST['account_number']));

    if (empty($bank_name) || empty($account_name) || empty($account_number)) {
        $msg = "<div class='alert alert-danger'>All fields are required.</div>";
    } else {
        $update = mysqli_query($conn, "UPDATE system_bank_details SET bank_name='$bank_name', account_name='$account_name', account_number='$account_number' WHERE id=1");
        if ($update) {
            $msg = "<div class='alert alert-success'>Bank Details updated successfully!</div>";
        } else {
            $msg = "<div class='alert alert-danger'>Error updating details in database.</div>";
        }
    }
}

// Fetch current details
$bank_query = mysqli_query($conn, "SELECT * FROM system_bank_details WHERE id = 1");
$bank = mysqli_fetch_assoc($bank_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>System Bank Configuration - Admin</title>
  <?php include("include/head.php"); ?>  
</head>
<body id="page-top">
  <div id="wrapper">
    <?php include ("include/slidebar.php"); ?>
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <?php include ("include/topbar.php"); ?>

        <div class="container-fluid" id="container-wrapper">
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">System Gateway Bank Config</h1>
          </div>

          <div class="row">
            <div class="col-lg-6">
              <div class="card p-4 mb-4">
                <?= $msg; ?>
                <form action="" method="POST">
                  <div class="form-group mb-3">
                    <label class="font-weight-bold">Bank Name</label>
                    <input type="text" name="bank_name" class="form-control" value="<?php echo htmlspecialchars($bank['bank_name'] ?? ''); ?>" required>
                  </div>
                  <div class="form-group mb-3">
                    <label class="font-weight-bold">Account Name</label>
                    <input type="text" name="account_name" class="form-control" value="<?php echo htmlspecialchars($bank['account_name'] ?? ''); ?>" required>
                  </div>
                  <div class="form-group mb-4">
                    <label class="font-weight-bold">Account Number</label>
                    <input type="text" name="account_number" class="form-control" value="<?php echo htmlspecialchars($bank['account_number'] ?? ''); ?>" required>
                  </div>
                  <button type="submit" name="update_bank" class="btn btn-primary btn-block">Save Configuration Settings</button>
                </form>
              </div>
            </div>
          </div>
        </div>

      </div>
      <?php include("include/copyright.php"); ?>
    </div>
  </div>
  <?php include("include/script.php"); ?>
</body>
</html>
<?php mysqli_close($conn); ?>