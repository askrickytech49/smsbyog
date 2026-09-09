<?php
include("auth.php");
if(!isset($_SESSION['token'])){ if(isset($_COOKIE['remember_me'])){$_SESSION['token']=$_COOKIE['remember_me'];}else{header('Location: login.php');exit;} }
$aq=mysqli_query($conn,"SELECT * FROM login_token WHERE token='".$_SESSION['token']."'");
if(mysqli_num_rows($aq)==0){header('Location: login.php');exit;}
$ad=mysqli_fetch_array($aq);
$au=mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM user_data WHERE id='".$ad['user_id']."' AND status='1'"));
if(!in_array($au['type'],["admin","super_admin"])){header('Location: login.php');exit;}

$sql = mysqli_query($conn,"SELECT u.*,w.balance,w.total_recharge,w.total_otp FROM user_data u LEFT JOIN user_wallet w ON u.id=w.user_id ORDER BY u.id DESC");
$page_title = 'All Users';
?>
<?php include __DIR__.'/include/layout_start.php'; ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-people me-2 text-red"></i>All Users</h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="dashboard">Dashboard</a></li><li class="breadcrumb-item active">All Users</li></ol></nav>
  </div>
</div>
<div class="admin-card">
  <div class="admin-card-header"><h6>User List</h6></div>
  <div class="admin-card-body p-0">
    <div class="table-responsive">
      <table class="admin-table admin-datatable" style="width:100%">
        <thead><tr>
          <th>User</th><th>Balance</th><th>Recharged</th><th>OTP Bought</th><th>Status</th><th>Action</th>
        </tr></thead>
        <tbody>
        <?php while($r=mysqli_fetch_assoc($sql)): ?>
        <tr>
          <td>
            <div class="d-flex align-items-center gap-2">
              <div class="user-avatar"><?=strtoupper(substr($r['name']??'U',0,1))?></div>
              <div>
                <div style="font-weight:600;font-size:13px"><?=htmlspecialchars($r['name']??'-')?></div>
                <div style="font-size:12px;color:var(--text-muted)"><?=htmlspecialchars($r['email'])?></div>
              </div>
            </div>
          </td>
          <td><strong>₦<?=number_format($r['balance']??0)?></strong></td>
          <td>₦<?=number_format($r['total_recharge']??0)?></td>
          <td><?=number_format($r['total_otp']??0)?></td>
          <td>
            <span class="status-badge <?=$r['status']=='1'?'badge-active':'badge-blocked'?>">
              <?=$r['status']=='1'?'Active':'Blocked'?>
            </span>
          </td>
          <td>
            <a href="edit_user?user_id=<?=$r['id']?>" class="btn btn-sm btn-primary">
              <i class="bi bi-pencil me-1"></i>Edit
            </a>
          </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include __DIR__.'/include/layout_end.php'; ?>
