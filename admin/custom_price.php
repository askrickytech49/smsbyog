<?php
include("auth.php");
if(!isset($_SESSION['token'])){ if(isset($_COOKIE['remember_me'])){$_SESSION['token']=$_COOKIE['remember_me'];}else{header('Location: login.php');exit;} }
$aq=mysqli_query($conn,"SELECT * FROM login_token WHERE token='".$_SESSION['token']."'");
if(mysqli_num_rows($aq)==0){header('Location: login.php');exit;}
$ad=mysqli_fetch_array($aq); $au=mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM user_data WHERE id='".$ad['user_id']."' AND status='1'"));
if(!in_array($au['type'],["admin","super_admin"])){header('Location: login.php');exit;}

if(isset($_POST['delete'])){
    $did=(int)$_POST['id'];
    mysqli_query($conn,"DELETE FROM custom_price WHERE id='$did'");
    header('Location: custom_price'); exit;
}

$sql=mysqli_query($conn,"SELECT cp.*,u.email,u.name FROM custom_price cp LEFT JOIN user_data u ON cp.user_id=u.id ORDER BY cp.id DESC");
$page_title='Custom Prices';
?>
<?php include __DIR__.'/include/layout_start.php'; ?>
<div class="page-header">
  <div>
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="dashboard">Dashboard</a></li><li class="breadcrumb-item active">Custom Prices</li></ol></nav>
  </div>
  <a href="add_custom_price" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Custom Price</a>
</div>
<div class="admin-card">
  <div class="admin-card-body p-0">
    <div class="table-responsive">
      <table class="admin-table admin-datatable" style="width:100%">
        <thead>
          <tr><th>User</th><th>Service ID</th><th>Server ID</th><th>Type</th><th>Discount</th><th>Action</th></tr>
        </thead>
        <tbody>
        <?php while($r=mysqli_fetch_assoc($sql)): ?>
        <tr>
          <td>
            <div class="d-flex align-items-center gap-2">
              <div class="user-avatar"><?=strtoupper(substr($r['name']??'U',0,1))?></div>
              <div>
                <div style="font-weight:600;font-size:13px"><?=htmlspecialchars($r['name']??'-')?></div>
                <div style="font-size:11px;color:var(--text-muted)"><?=htmlspecialchars($r['email']??'')?></div>
              </div>
            </div>
          </td>
          <td><code style="background:var(--bg);padding:3px 8px;border-radius:6px;font-size:12px"><?=htmlspecialchars($r['service_id']??'')?></code></td>
          <td><code style="background:var(--bg);padding:3px 8px;border-radius:6px;font-size:12px"><?=htmlspecialchars($r['server_id']??'')?></code></td>
          <td>
            <span class="status-badge <?=$r['type']=='flat'?'badge-active':'badge-pending'?>">
              <?=htmlspecialchars($r['type']??'')?>
            </span>
          </td>
          <td>
            <?php if($r['type']=='flat'): ?>
              <strong>₦<?=number_format($r['discount']??0)?></strong>
            <?php else: ?>
              <strong><?=htmlspecialchars($r['discount']??0)?>%</strong>
            <?php endif; ?>
          </td>
          <td>
            <form method="post" onsubmit="return confirm('Delete this custom price?')" style="display:inline">
              <input type="hidden" name="id" value="<?=$r['id']?>">
              <button class="btn btn-sm btn-outline-danger" name="delete"><i class="bi bi-trash"></i></button>
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
