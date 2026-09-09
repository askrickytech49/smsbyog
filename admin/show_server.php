<?php
include("auth.php");
if(!isset($_SESSION['token'])){ if(isset($_COOKIE['remember_me'])){$_SESSION['token']=$_COOKIE['remember_me'];}else{header('Location: login.php');exit;} }
$aq=mysqli_query($conn,"SELECT * FROM login_token WHERE token='".$_SESSION['token']."'");
if(mysqli_num_rows($aq)==0){header('Location: login.php');exit;}
$ad=mysqli_fetch_array($aq); $au=mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM user_data WHERE id='".$ad['user_id']."' AND status='1'"));
if(!in_array($au['type'],["admin","super_admin"])){header('Location: login.php');exit;}

if(isset($_POST['delete'])){ $did=(int)$_POST['id']; mysqli_query($conn,"DELETE FROM otp_server WHERE id='$did'"); header('Location: show_server'); exit; }
$sql=mysqli_query($conn,"SELECT s.*,a.api_name FROM otp_server s LEFT JOIN api_detail a ON s.api_id=a.id ORDER BY s.id DESC");
$page_title='OTP Servers';
?>
<?php include __DIR__.'/include/layout_start.php'; ?>
<div class="page-header">
  <div>
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="dashboard">Dashboard</a></li><li class="breadcrumb-item active">OTP Servers</li></ol></nav>
  </div>
  <a href="add_server" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Server</a>
</div>
<div class="admin-card">
  <div class="admin-card-body p-0">
    <div class="table-responsive">
      <table class="admin-table admin-datatable" style="width:100%">
        <thead><tr><th>Server Name</th><th>Country Code</th><th>API Provider</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php while($r=mysqli_fetch_assoc($sql)): ?>
        <tr>
          <td><strong><?=htmlspecialchars($r['server_name'])?></strong></td>
          <td><code style="background:var(--bg);padding:3px 8px;border-radius:6px"><?=htmlspecialchars($r['server_code'])?></code></td>
          <td><?=htmlspecialchars($r['api_name']??$r['api_id'])?></td>
          <td><span class="status-badge <?=$r['status']=='1'?'badge-active':'badge-blocked'?>"><?=$r['status']=='1'?'Active':'Inactive'?></span></td>
          <td class="d-flex gap-2">
            <a href="edit_server?id=<?=$r['id']?>" class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i></a>
            <form method="post" onsubmit="return confirm('Delete this server?')" style="display:inline">
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
