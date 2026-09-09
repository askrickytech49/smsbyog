<?php
include("auth.php");
if(!isset($_SESSION['token'])){ if(isset($_COOKIE['remember_me'])){$_SESSION['token']=$_COOKIE['remember_me'];}else{header('Location: login.php');exit;} }
$aq=mysqli_query($conn,"SELECT * FROM login_token WHERE token='".$_SESSION['token']."'");
if(mysqli_num_rows($aq)==0){header('Location: login.php');exit;}
$ad=mysqli_fetch_array($aq); $au=mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM user_data WHERE id='".$ad['user_id']."' AND status='1'"));
if(!in_array($au['type'],["admin","super_admin"])){header('Location: login.php');exit;}

$msg=''; $msg_type='';
if(isset($_POST['add'])){
    $name=mysqli_real_escape_string($conn,trim($_POST['server_name']??''));
    $code=mysqli_real_escape_string($conn,trim($_POST['server_code']??''));
    $api_id=(int)($_POST['api_id']??0);
    $status=mysqli_real_escape_string($conn,$_POST['status']??'1');
    if(!$name||!$code){ $msg='Name and code are required.'; $msg_type='danger'; }
    else{ mysqli_query($conn,"INSERT INTO otp_server(server_name,server_code,api_id,status) VALUES('$name','$code','$api_id','$status')"); $msg='Server added.'; $msg_type='success'; }
}
$apis=mysqli_query($conn,"SELECT id,api_name FROM api_detail ORDER BY id");
$page_title='Add Server';
?>
<?php include __DIR__.'/include/layout_start.php'; ?>
<div class="page-header">
  <div>
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="dashboard">Dashboard</a></li><li class="breadcrumb-item"><a href="show_server">OTP Servers</a></li><li class="breadcrumb-item active">Add</li></ol></nav>
  </div>
  <a href="show_server" class="btn btn-light-action"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>
<div class="row justify-content-center">
  <div class="col-12 col-md-7 col-lg-5">
    <?php if($msg): ?><div class="alert alert-<?=$msg_type?> mb-3"><?=$msg?></div><?php endif; ?>
    <div class="admin-card">
      <div class="admin-card-header"><h6>Server Details</h6></div>
      <div class="admin-card-body">
        <form method="post">
          <div class="mb-3"><label class="form-label">Server Name</label><input type="text" name="server_name" class="form-control" placeholder="e.g. Nigeria (Server 1)" required></div>
          <div class="mb-3"><label class="form-label">Server Code</label><input type="text" name="server_code" class="form-control" placeholder="e.g. 7" required></div>
          <div class="mb-3">
            <label class="form-label">API Provider</label>
            <select name="api_id" class="form-select" required>
              <option value="">Select API</option>
              <?php while($a=mysqli_fetch_assoc($apis)): ?>
              <option value="<?=$a['id']?>"><?=htmlspecialchars($a['api_name'])?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="mb-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select"><option value="1">Active</option><option value="0">Inactive</option></select>
          </div>
          <button type="submit" name="add" class="btn btn-primary w-100"><i class="bi bi-plus-lg me-2"></i>Add Server</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__.'/include/layout_end.php'; ?>
