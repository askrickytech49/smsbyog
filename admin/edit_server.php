<?php
include("auth.php");
if(!isset($_SESSION['token'])){
    if(isset($_COOKIE['remember_me'])){
        $_SESSION['token'] = $_COOKIE['remember_me'];
    } else {
        header('Location: login.php'); exit;
    }
}
$admin_sql = mysqli_query($conn,"SELECT * FROM login_token WHERE token='".$_SESSION['token']."'");
if(mysqli_num_rows($admin_sql) == 0) { header('Location: login.php'); exit; }
$admin_data = mysqli_fetch_array($admin_sql);
$admin_sql2 = mysqli_query($conn,"SELECT * FROM user_data WHERE id='".$admin_data['user_id']."' AND status='1'");
$final_admin = mysqli_fetch_array($admin_sql2);
if(!in_array($final_admin['type'], ["admin", "super_admin"])){ header('Location: login.php'); exit; }

if(!isset($_GET['id']) || $_GET['id']==""){
    echo "invalid id"; return;
}

$id = (int)$_GET['id'];
$sql = mysqli_query($conn,"SELECT * FROM otp_server WHERE id='$id'");
if(mysqli_num_rows($sql)==0){ echo "invalid id"; return; }
$server_data = mysqli_fetch_assoc($sql);

$sql2 = mysqli_query($conn,"SELECT * FROM api_detail WHERE id='".$server_data['api_id']."'");
$api_data = mysqli_fetch_assoc($sql2);

// Handle form submission
if(isset($_POST['submit'])){
    $server_name = mysqli_real_escape_string($conn, $_POST['server_name']);
    $server_code = mysqli_real_escape_string($conn, $_POST['server_code']);
    $api_id      = (int)$_POST['api_id'];
    $status      = $_POST['status'] == '1' ? '1' : '0';
    
    mysqli_query($conn, "UPDATE otp_server SET server_name='$server_name', server_code='$server_code', api_id='$api_id', status='$status' WHERE id='$id'");
    
    // Refresh data after update
    $sql = mysqli_query($conn,"SELECT * FROM otp_server WHERE id='$id'");
    $server_data = mysqli_fetch_assoc($sql);
    $sql2 = mysqli_query($conn,"SELECT * FROM api_detail WHERE id='".$server_data['api_id']."'");
    $api_data = mysqli_fetch_assoc($sql2);
    $success = true;
}

$apis = mysqli_query($conn,"SELECT id, api_name FROM api_detail ORDER BY id");

$page_title = 'Edit Server';
?>
<?php include __DIR__.'/include/layout_start.php'; ?>
<div class="page-header">
  <div>
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="dashboard">Dashboard</a></li><li class="breadcrumb-item"><a href="show_server">OTP Servers</a></li><li class="breadcrumb-item active">Edit Server</li></ol></nav>
  </div>
</div>

<?php if(!empty($success)): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius:12px;">
  <i class="bi bi-check-circle me-2"></i> Server updated successfully!
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="admin-card" style="max-width:700px;">
  <div class="admin-card-header">
    <h6 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Server Details</h6>
  </div>
  <div class="admin-card-body">
    <form method="POST">
      <div class="mb-3">
        <label class="form-label fw-600">API Provider</label>
        <select name="api_id" class="form-select" style="border-radius:10px; height:46px;">
          <?php while($row = mysqli_fetch_assoc($apis)): ?>
            <option value="<?= $row['id'] ?>" <?= $row['id'] == $server_data['api_id'] ? 'selected' : '' ?>><?= htmlspecialchars($row['api_name']) ?></option>
          <?php endwhile; ?>
        </select>
        <small class="text-muted">Current: <strong><?= htmlspecialchars($api_data['api_name'] ?? 'N/A') ?></strong></small>
      </div>

      <div class="mb-3">
        <label class="form-label fw-600">Server Name</label>
        <input type="text" class="form-control" name="server_name" value="<?= htmlspecialchars($server_data['server_name']) ?>" style="border-radius:10px; height:46px;" required>
      </div>

      <div class="mb-3">
        <label class="form-label fw-600">Country Code</label>
        <input type="text" class="form-control" name="server_code" value="<?= htmlspecialchars($server_data['server_code']) ?>" style="border-radius:10px; height:46px;" required>
        <small class="text-muted">e.g. <code>nigeria</code>, <code>england</code>, <code>usa</code> for 5SIM</small>
      </div>

      <div class="mb-3">
        <label class="form-label fw-600">Status</label>
        <select name="status" class="form-select" style="border-radius:10px; height:46px;">
          <option value="1" <?= $server_data['status'] == '1' ? 'selected' : '' ?>>Active</option>
          <option value="0" <?= $server_data['status'] != '1' ? 'selected' : '' ?>>Inactive</option>
        </select>
      </div>

      <button type="submit" name="submit" class="btn btn-primary w-100" style="height:48px; border-radius:12px; font-weight:600;">
        <i class="bi bi-check-lg me-1"></i> Update Server
      </button>
    </form>
  </div>
</div>
<?php include __DIR__.'/include/layout_end.php'; ?>