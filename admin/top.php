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
        $today_start = date('Y-m-d 00:00:00');

        $sql = "
        SELECT server_id, service_id, COUNT(*) as service_count
        FROM active_number
        WHERE status = 1 AND buy_time >= '$today_start'
        GROUP BY server_id, service_id
        ORDER BY service_count DESC
        ";
        $result = $conn->query($sql);

        $sql9 = "
        SELECT user_id, SUM(service_price) as total_amount, COUNT(*) as service_count
        FROM active_number
        WHERE status = 1 AND buy_time >= '$today_start'
        GROUP BY user_id
        ORDER BY total_amount DESC
        ";
        $result1 = $conn->query($sql9);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Top Performance - AuthPadi</title>
<?php include ("include/head.php"); ?>
<link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">

<style>
:root{
    --auth:#e10700;
}
.page-title{
    font-weight:800;
    font-size:1.4rem;
}
.card{
    border:none;
    border-radius:14px;
    box-shadow:0 12px 30px rgba(0,0,0,.06);
}
.card-header{
    background:#fff;
    border-bottom:1px solid #f1f1f1;
}
.card-header h6{
    font-weight:700;
    color:#111;
}
.table thead th{
    font-size:12px;
    text-transform:uppercase;
    color:#6b7280;
    border-bottom:1px solid #eee;
}
.table td{
    vertical-align:middle;
}
.badge-success{
    background:#16a34a;
}
.badge-danger{
    background:#dc2626;
}
.img-profile{
    border:2px solid #f1f1f1;
}
</style>
</head>

<script>
$(document).ready(function(){
    $('#dashboard').removeClass("active");
    $("#top").addClass("active");
});
</script>

<body id="page-top">
<div id="wrapper">

<?php include ("include/slidebar.php"); ?>

<div id="content-wrapper" class="d-flex flex-column">
<div id="content">

<?php include ("include/topbar.php"); ?>

<div class="container-fluid" id="container-wrapper">

<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="page-title">Top Performance (Today)</h1>
</div>

<!-- SERVICE SALES -->
<div class="row mb-4">
<div class="col-12">
<div class="card">
<div class="card-header">
<h6>🔥 Today Service Sell History</h6>
</div>
<div class="card-body table-responsive">

<table class="table table-hover" id="dataTable">
<thead>
<tr>
<th>Logo</th>
<th>Server</th>
<th>Service</th>
<th>Count</th>
</tr>
</thead>
<tbody>
<?php
while ($data = mysqli_fetch_array($result)) {
    $sql2 = mysqli_query($conn, "SELECT * FROM service_icon WHERE short_code='" . $data['service_id'] . "'");
    $img = mysqli_num_rows($sql2)==0 ? "https://i.ibb.co/ySRhxqh/default.png" : mysqli_fetch_assoc($sql2)['img_url'];

    $sql4 = mysqli_query($conn, "SELECT * FROM otp_server WHERE id='" . $data['server_id'] . "'");
    $server_name = mysqli_num_rows($sql4)==0 ? "Deleted Server" : mysqli_fetch_assoc($sql4)['server_name'];

    $sql6 = mysqli_query($conn, "SELECT * FROM service WHERE service_id='" . $data['service_id'] . "'");
    $service_name = mysqli_num_rows($sql6)==0 ? "Deleted Service" : mysqli_fetch_assoc($sql6)['service_name'];
?>
<tr>
<td><img src="<?php echo $img; ?>" class="img-profile rounded-circle" width="34"></td>
<td><?php echo $server_name; ?> (<?php echo $data['server_id']; ?>)</td>
<td><?php echo $service_name; ?> (<?php echo $data['service_id']; ?>)</td>
<td><strong><?php echo $data['service_count']; ?></strong></td>
</tr>
<?php } ?>
</tbody>
</table>

</div>
</div>
</div>
</div>

<!-- TOP USERS -->
<div class="row">
<div class="col-12">
<div class="card">
<div class="card-header">
<h6>🚀 Today Active Users</h6>
</div>
<div class="card-body table-responsive">

<table class="table table-hover" id="dataTable2">
<thead>
<tr>
<th>Email</th>
<th>Today Purchased</th>
<th>Today OTP</th>
<th>Balance</th>
<th>Total Recharge</th>
<th>Total OTP</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php
$today = date('Y-m-d');
while ($data = mysqli_fetch_array($result1)) {
    $user_id = $data['user_id'];
    $sql10 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM user_data WHERE id='$user_id'"));
    $sql13 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM user_wallet WHERE user_id='$user_id'"));
    $sql15 = mysqli_fetch_assoc(mysqli_query($conn,"
        SELECT COUNT(*) AS count 
        FROM active_number 
        WHERE user_id='$user_id' AND DATE(buy_time)='$today' AND status='1'
    "));
    $status = $sql10['status']=="1" ? "badge badge-success" : "badge badge-danger";
    $status1 = $sql10['status']=="1" ? "Active" : "Blocked";
?>
<tr>
<td><?php echo $sql10['email']; ?></td>
<td>₦<?php echo $data['total_amount']; ?></td>
<td><?php echo $sql15['count']; ?></td>
<td>₦<?php echo $sql13['balance']; ?></td>
<td>₦<?php echo $sql13['total_recharge']; ?></td>
<td><?php echo $sql13['total_otp']; ?></td>
<td><span class="<?php echo $status; ?>"><?php echo $status1; ?></span></td>
<td>
<a href="edit_user?user_id=<?php echo $user_id; ?>" class="btn btn-sm btn-primary">
Edit
</a>
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

<a class="scroll-to-top rounded" href="#page-top">
<i class="fas fa-angle-up"></i>
</a>

<?php include ("include/script.php"); ?>
<script src="vendor/datatables/jquery.dataTables.min.js"></script>
<script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script>
$(document).ready(function(){
    $('#dataTable, #dataTable2').DataTable({ ordering:false });
});
</script>

</body>
</html>
<?php
    } else {
        header('Location: login.php'); exit;
    }
}
mysqli_close($conn);
?>
