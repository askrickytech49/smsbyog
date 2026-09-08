<?php
include("auth.php");

if (!isset($_SESSION['token'])) {
    if (isset($_COOKIE['remember_me'])) {
        $_SESSION['token'] = $_COOKIE['remember_me'];
    } else {
        header('Location: login.php'); exit;
    }
}

$admin_sql = mysqli_query($conn, "SELECT * FROM login_token WHERE token='" . $_SESSION['token'] . "'");
if (mysqli_num_rows($admin_sql) == 0) {
    header('Location: login.php'); exit;
}

$admin_data = mysqli_fetch_array($admin_sql);
$admin_sql2 = mysqli_query(
    $conn,
    "SELECT * FROM user_data WHERE id='" . $admin_data['user_id'] . "' AND status='1'"
);
$final_admin = mysqli_fetch_array($admin_sql2);

if ($final_admin['type'] !== "admin") {
    header('Location: login.php'); exit;
}

/*
|--------------------------------------------------------------------------
| Fetch Top Services
|--------------------------------------------------------------------------
*/
$sql = mysqli_query($conn, "SELECT * FROM top_services ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Top Service - @getallscripts</title>
    <?php include("include/head.php"); ?>
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
</head>

<script>
$(document).ready(function () {
    $('#dashboard').removeClass("active");
    $("#top_service").addClass("active");
});
</script>

<body id="page-top">
<div id="wrapper">

<?php include("include/slidebar.php"); ?>

<div id="content-wrapper" class="d-flex flex-column">
<div id="content">

<?php include("include/topbar.php"); ?>

<div class="container-fluid" id="container-wrapper">
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Home</a></li>
        <li class="breadcrumb-item active">Top Service</li>
    </ol>
</div>

<div class="row">
<div class="col">
<div class="card mb-4">

<div class="card-header py-3 d-flex justify-content-between">
    <a href="add_top_service" class="btn btn-sm btn-primary">Add Top Service</a>
</div>

<div class="table-responsive p-3">

<?php
if (isset($_POST['delete'])) {
    $delete_id = mysqli_real_escape_string($conn, $_POST['id']);
    mysqli_query($conn, "DELETE FROM top_services WHERE id='$delete_id'");
    echo "<div class='alert alert-success'>Delete success</div>";
    echo "<meta http-equiv='refresh' content='0'>";
}
?>

<table class="table align-items-center table-flush" id="dataTable">
<thead class="thead-light">
<tr>
    <th>Server</th>
    <th>Service</th>
    <th>Delete</th>
</tr>
</thead>

<tbody>
<?php
while ($data = mysqli_fetch_assoc($sql)) {

    /* Resolve Server */
    $server_name = "<span class='text-danger'>Deleted Server</span>";
    $srv = mysqli_query(
        $conn,
        "SELECT server_name FROM otp_server WHERE id='" . $data['server_name'] . "' LIMIT 1"
    );
    if (mysqli_num_rows($srv) === 1) {
        $server_row = mysqli_fetch_assoc($srv);
        $server_name = $server_row['server_name'];
    }

    /* Resolve Service */
    $service_label = "<span class='text-danger'>Deleted Service</span>";
    $svc = mysqli_query(
        $conn,
        "SELECT service_name, service_id 
         FROM service 
         WHERE id='" . $data['service_id'] . "' LIMIT 1"
    );
    if (mysqli_num_rows($svc) === 1) {
        $svc_row = mysqli_fetch_assoc($svc);
        $service_label = $svc_row['service_name'] . " (" . $svc_row['service_id'] . ")";
    }
?>
<tr>
    <td><?= $server_name; ?></td>
    <td><?= $service_label; ?></td>
    <td>
        <form method="post">
            <input type="hidden" name="id" value="<?= $data['id']; ?>">
            <button class="btn btn-sm btn-danger" name="delete">Delete</button>
        </form>
    </td>
</tr>
<?php } ?>
</tbody>
</table>

</div>
</div>
</div>
</div>

<?php include("include/copyright.php"); ?>

</div>
</div>
</div>

<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<?php include("include/script.php"); ?>

<script src="vendor/datatables/jquery.dataTables.min.js"></script>
<script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script>
$(document).ready(function () {
    $('#dataTable').DataTable();
});
</script>

</body>
</html>

<?php mysqli_close($conn); ?>
