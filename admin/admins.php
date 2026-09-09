<?php
include("auth.php");
if(!isset($_SESSION['token'])){ if(isset($_COOKIE['remember_me'])){$_SESSION['token']=$_COOKIE['remember_me'];}else{header('Location: login.php');exit;} }
$aq=mysqli_query($conn,"SELECT * FROM login_token WHERE token='".$_SESSION['token']."'");
if(mysqli_num_rows($aq)==0){header('Location: login.php');exit;}
$ad=mysqli_fetch_array($aq);
$au=mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM user_data WHERE id='".$ad['user_id']."' AND status='1'"));
if(!in_array($au['type'],["admin","super_admin"])){header('Location: login.php');exit;}

if(isset($_POST['remove']) && isset($_POST['id'])){
    $rid=(int)$_POST['id'];
    mysqli_query($conn,"UPDATE user_data SET type='user' WHERE id='$rid'");
    header('Location: admins'); exit;
}
$sql=mysqli_query($conn,"SELECT * FROM user_data WHERE type='admin' AND id!=483790 ORDER BY id DESC");
$page_title='Admins';
?>
<?php include __DIR__.'/include/layout_start.php'; ?>
<div class="page-header">
  <div>
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="dashboard">Dashboard</a></li><li class="breadcrumb-item active">Admins</li></ol></nav>
  </div>
</div>
<div class="admin-card">
  <div class="admin-card-header"><h6>Admin Accounts</h6></div>
  <div class="admin-card-body p-0">
    <div class="table-responsive">
      <table class="admin-table admin-datatable" style="width:100%">
        <thead><tr><th>Admin</th><th>Type</th><th>Action</th></tr></thead>
        <tbody>
        <?php while($r=mysqli_fetch_assoc($sql)): ?>
        <tr>
          <td>
            <div class="d-flex align-items-center gap-2">
              <div class="user-avatar"><?=strtoupper(substr($r['name']??'A',0,1))?></div>
              <div>
                <div style="font-weight:600;font-size:13px"><?=htmlspecialchars($r['name']??'-')?></div>
                <div style="font-size:12px;color:var(--text-muted)"><?=htmlspecialchars($r['email'])?></div>
              </div>
            </div>
          </td>
          <td><span class="status-badge badge-active"><?=htmlspecialchars($r['type'])?></span></td>
          <td>
            <form method="post" onsubmit="return confirm('Remove admin privileges from this user?')">
              <input type="hidden" name="id" value="<?=$r['id']?>">
              <button class="btn btn-sm btn-outline-danger" name="remove"><i class="bi bi-person-dash me-1"></i>Remove</button>
            </form>
          </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include __DIR__.'/include/layout_end.php'; ?>
