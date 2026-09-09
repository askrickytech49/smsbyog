<?php
include("auth.php");
if(!isset($_SESSION['token'])){ if(isset($_COOKIE['remember_me'])){$_SESSION['token']=$_COOKIE['remember_me'];}else{header('Location: login.php');exit;} }
$aq=mysqli_query($conn,"SELECT * FROM login_token WHERE token='".$_SESSION['token']."'");
if(mysqli_num_rows($aq)==0){header('Location: login.php');exit;}
$ad=mysqli_fetch_array($aq); $au=mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM user_data WHERE id='".$ad['user_id']."' AND status='1'"));
if(!in_array($au['type'],["admin","super_admin"])){header('Location: login.php');exit;}

if(isset($_POST['delete'])){ $did=(int)$_POST['id']; mysqli_query($conn,"DELETE FROM promocode WHERE id='$did'"); header('Location: promocode'); exit; }
$sql=mysqli_query($conn,"SELECT * FROM promocode ORDER BY id DESC");
$page_title='Promo Codes';
?>
<?php include __DIR__.'/include/layout_start.php'; ?>
<div class="page-header">
  <div>
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="dashboard">Dashboard</a></li><li class="breadcrumb-item active">Promo Codes</li></ol></nav>
  </div>
  <a href="add_promocode" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Promo Code</a>
</div>
<div class="admin-card">
  <div class="admin-card-body p-0">
    <div class="table-responsive">
      <table class="admin-table admin-datatable" style="width:100%">
        <thead><tr><th>Code</th><th>For User</th><th>Used</th><th>Date</th><th>Action</th></tr></thead>
        <tbody>
        <?php while($r=mysqli_fetch_assoc($sql)):
          $used=mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM promocode_history WHERE code_id='".$r['id']."'"))[0]??0; ?>
        <tr>
          <td><code style="background:var(--bg);padding:3px 8px;border-radius:6px;font-size:13px;font-weight:600;"><?=htmlspecialchars($r['promocode'])?></code></td>
          <td><?=htmlspecialchars($r['for_user'])?></td>
          <td><?=$used?></td>
          <td style="color:var(--text-muted);font-size:12px"><?=htmlspecialchars($r['date']??'')?></td>
          <td>
            <form method="post" onsubmit="return confirm('Delete this promo code?')" style="display:inline">
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
