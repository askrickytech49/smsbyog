<?php
include("auth.php");
if(!isset($_SESSION['token'])){ if(isset($_COOKIE['remember_me'])){$_SESSION['token']=$_COOKIE['remember_me'];}else{header('Location: login.php');exit;} }
$aq=mysqli_query($conn,"SELECT * FROM login_token WHERE token='".$_SESSION['token']."'");
if(mysqli_num_rows($aq)==0){header('Location: login.php');exit;}
$ad=mysqli_fetch_array($aq); $au=mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM user_data WHERE id='".$ad['user_id']."' AND status='1'"));
if(!in_array($au['type'],["admin","super_admin"])){header('Location: login.php');exit;}

if(isset($_POST['delete'])){ $did=(int)$_POST['id']; mysqli_query($conn,"DELETE FROM top_services WHERE id='$did'"); header('Location: top_service'); exit; }
$sql=mysqli_query($conn,"SELECT ts.*,s.service_name,s.service_id,o.server_name FROM top_services ts LEFT JOIN service s ON ts.service_id=s.id LEFT JOIN otp_server o ON ts.server_name=o.id WHERE ts.status='1' ORDER BY ts.id DESC");
$page_title='Top Services';
?>
<?php include __DIR__.'/include/layout_start.php'; ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-star me-2 text-red"></i>Top Services</h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="dashboard">Dashboard</a></li><li class="breadcrumb-item active">Top Services</li></ol></nav>
  </div>
  <a href="add_top_service" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Top Service</a>
</div>
<div class="admin-card">
  <div class="admin-card-body p-0">
    <div class="table-responsive">
      <table class="admin-table admin-datatable" style="width:100%">
        <thead><tr><th>Service</th><th>Service ID</th><th>Server</th><th>Action</th></tr></thead>
        <tbody>
        <?php while($r=mysqli_fetch_assoc($sql)): ?>
        <tr>
          <td><strong><?=htmlspecialchars($r['service_name']??'-')?></strong></td>
          <td><code style="background:var(--bg);padding:3px 8px;border-radius:6px;font-size:12px"><?=htmlspecialchars($r['service_id']??$r['service_id'])?></code></td>
          <td><?=htmlspecialchars($r['server_name']??$r['server_name']??'-')?></td>
          <td>
            <form method="post" onsubmit="return confirm('Remove this top service?')" style="display:inline">
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
