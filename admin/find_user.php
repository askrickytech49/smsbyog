<?php
include("auth.php");
if(!isset($_SESSION['token'])){ if(isset($_COOKIE['remember_me'])){$_SESSION['token']=$_COOKIE['remember_me'];}else{header('Location: login.php');exit;} }
$aq=mysqli_query($conn,"SELECT * FROM login_token WHERE token='".$_SESSION['token']."'");
if(mysqli_num_rows($aq)==0){header('Location: login.php');exit;}
$ad=mysqli_fetch_array($aq); $au=mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM user_data WHERE id='".$ad['user_id']."' AND status='1'"));
if(!in_array($au['type'],["admin","super_admin"])){header('Location: login.php');exit;}

$found=null; $error='';
if(isset($_POST['search'])){
    $em=mysqli_real_escape_string($conn,$_POST['email']??'');
    $q=mysqli_query($conn,"SELECT u.*,w.balance,w.total_recharge,w.total_otp FROM user_data u LEFT JOIN user_wallet w ON u.id=w.user_id WHERE u.email='$em' LIMIT 1");
    if(mysqli_num_rows($q)>0){ $found=mysqli_fetch_assoc($q); }
    else{ $error='No user found with that email address.'; }
}
$page_title='Find User';
?>
<?php include __DIR__.'/include/layout_start.php'; ?>
<div class="page-header">
  <div>
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="dashboard">Dashboard</a></li><li class="breadcrumb-item active">Find User</li></ol></nav>
  </div>
</div>

<div class="row justify-content-center">
  <div class="col-12 col-md-8 col-lg-6">
    <div class="admin-card mb-4">
      <div class="admin-card-header"><h6>Search by Email</h6></div>
      <div class="admin-card-body">
        <form method="post">
          <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" placeholder="user@example.com" required value="<?=htmlspecialchars($_POST['email']??'')?>">
          </div>
          <button type="submit" name="search" class="btn btn-primary w-100"><i class="bi bi-search me-2"></i>Search</button>
        </form>
      </div>
    </div>

    <?php if($error): ?>
    <div class="alert alert-danger"><?=$error?></div>
    <?php endif; ?>

    <?php if($found): ?>
    <div class="admin-card">
      <div class="admin-card-header"><h6>User Found</h6></div>
      <div class="admin-card-body">
        <div class="d-flex align-items-center gap-3 mb-4">
          <div class="user-avatar" style="width:52px;height:52px;font-size:20px"><?=strtoupper(substr($found['name']??'U',0,1))?></div>
          <div>
            <div style="font-size:16px;font-weight:700"><?=htmlspecialchars($found['name']??'-')?></div>
            <div style="font-size:13px;color:var(--text-muted)"><?=htmlspecialchars($found['email'])?></div>
          </div>
          <span class="status-badge ms-auto <?=$found['status']=='1'?'badge-active':'badge-blocked'?>"><?=$found['status']=='1'?'Active':'Blocked'?></span>
        </div>
        <div class="row g-3 mb-4">
          <div class="col-4">
            <div style="background:var(--bg);border-radius:10px;padding:14px;text-align:center">
              <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.5px">Balance</div>
              <div style="font-size:18px;font-weight:800;color:var(--text)">₦<?=number_format($found['balance']??0)?></div>
            </div>
          </div>
          <div class="col-4">
            <div style="background:var(--bg);border-radius:10px;padding:14px;text-align:center">
              <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.5px">Recharged</div>
              <div style="font-size:18px;font-weight:800;color:var(--text)">₦<?=number_format($found['total_recharge']??0)?></div>
            </div>
          </div>
          <div class="col-4">
            <div style="background:var(--bg);border-radius:10px;padding:14px;text-align:center">
              <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.5px">OTP Bought</div>
              <div style="font-size:18px;font-weight:800;color:var(--text)"><?=number_format($found['total_otp']??0)?></div>
            </div>
          </div>
        </div>
        <a href="edit_user?user_id=<?=$found['id']?>" class="btn btn-primary w-100"><i class="bi bi-pencil me-2"></i>Edit This User</a>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php include __DIR__.'/include/layout_end.php'; ?>
