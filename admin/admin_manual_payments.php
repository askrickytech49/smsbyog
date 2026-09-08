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

if($final_admin['type'] != "admin"){
    header('Location: login.php'); exit;
}

$msg = "";
// Handle Approvals / Rejections Actions
if (isset($_POST['action']) && isset($_POST['id'])) {
    $req_id = intval($_POST['id']);
    $action = $_POST['action'];

    $payment_query = mysqli_query($conn, "SELECT * FROM manual_payments WHERE id = '$req_id' AND status = 'pending'");
    if (mysqli_num_rows($payment_query) > 0) {
        $payment = mysqli_fetch_assoc($payment_query);
        $user_id = $payment['user_id'];
        $amount = $payment['amount'];
        $txn_id = $payment['txn_id'];
        $now = date('Y-m-d H:i:s');

        if ($action == 'approve') {
            // Start SQL Atomic Transaction block
            mysqli_begin_transaction($conn);
            try {
                // 1. Update verification state tracking
                mysqli_query($conn, "UPDATE manual_payments SET status='approved', processed_at='$now' WHERE id='$req_id'");
                
                // 2. Adjust User Balance Profile directly matching your schemas
                mysqli_query($conn, "UPDATE user_wallet SET balance = balance + $amount, total_recharge = total_recharge + $amount WHERE user_id='$user_id'");
                
                // 3. Register transaction ledger entries matching your table profile structure
                mysqli_query($conn, "INSERT INTO user_transaction (user_id, txn_id, amount, type, status, date, admin_note) 
                                     VALUES ('$user_id', '$txn_id', '$amount', 'Manual Recharge', '1', '$now', 'Approved by Admin')");
                
                mysqli_commit($conn);
                $msg = "<div class='alert alert-success'>Transaction #$txn_id approved and wallet updated.</div>";
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $msg = "<div class='alert alert-danger'>Execution error processing adjustments.</div>";
            }
        } elseif ($action == 'reject') {
            mysqli_query($conn, "UPDATE manual_payments SET status='rejected', processed_at='$now' WHERE id='$req_id'");
            mysqli_query($conn, "INSERT INTO user_transaction (user_id, txn_id, amount, type, status, date, admin_note) 
                                 VALUES ('$user_id', '$txn_id', '$amount', 'Manual Recharge', '-1', '$now', 'Rejected by Admin')");
            $msg = "<div class='alert alert-warning'>Transaction #$txn_id marked rejected.</div>";
        }
    }
}

// Read pending requests data context
$sql = mysqli_query($conn, "SELECT m.*, u.email FROM manual_payments m JOIN user_data u ON m.user_id = u.id ORDER BY m.id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Manage Manual Payments - Admin</title>
  <?php include("include/head.php"); ?>  
  <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
</head>
<body id="page-top">
  <div id="wrapper">
    <?php include ("include/slidebar.php"); ?>
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <?php include ("include/topbar.php"); ?>       
        
        <div class="container-fluid" id="container-wrapper">
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Verify Manual Payments</h1>
          </div>

          <?= $msg; ?>

          <div class="row">
            <div class="col-lg-12">
              <div class="card mb-4">
                <div class="table-responsive p-3">
                  <table class="table align-items-center table-flush" id="dataTable">
                    <thead class="thead-light">
                      <tr>
                        <th>User Email</th>
                        <th>Txn ID</th>
                        <th>Amount</th>
                        <th>Receipt</th>
                        <th>Status</th>
                        <th>Submitted At</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                    <?php while($data = mysqli_fetch_assoc($sql)){ ?>
                      <tr>
                        <td><strong><?php echo htmlspecialchars($data['email']); ?></strong></td>
                        <td><?php echo htmlspecialchars($data['txn_id']); ?></td>
                        <td>₦<?php echo number_format($data['amount'], 2); ?></td>
                        <td>
                          <a href="../<?php echo htmlspecialchars($data['receipt_image']); ?>" target="_blank" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i> View Receipt
                          </a>
                        </td>
                        <td>
                          <?php if($data['status'] == 'pending') { ?>
                            <span class="badge badge-warning">Pending</span>
                          <?php } elseif($data['status'] == 'approved') { ?>
                            <span class="badge badge-success">Approved</span>
                          <?php } else { ?>
                            <span class="badge badge-danger">Rejected</span>
                          <?php } ?>
                        </td>
                        <td><?php echo $data['created_at']; ?></td>
                        <td>
                          <?php if($data['status'] == 'pending') { ?>
                            <form action="" method="POST" style="display:inline-block;">
                              <input type="hidden" name="id" value="<?php echo $data['id']; ?>">
                              <button type="submit" name="action" value="approve" class="btn btn-sm btn-success" onclick="return confirm('Approve this transaction and credit user wallet?')">Approve</button>
                              <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger" onclick="return confirm('Reject this proof of payment?')">Reject</button>
                            </form>
                          <?php } else { echo '-'; } ?>
                        </td>
                      </tr>
                    <?php } ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
      <?php include("include/copyright.php"); ?>
    </div>
  </div>
  <a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>
  <?php include("include/script.php"); ?>
  <script src="vendor/datatables/jquery.dataTables.min.js"></script>
  <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
  <script>
    $(document).ready(function () {
        $('#dataTable').DataTable({ pageLength: 10, order: [[5, "desc"]] });
    });
  </script>
</body>
</html>
<?php mysqli_close($conn); ?>