<?php
include("auth.php");
if(!isset($_SESSION['token'])){ if(isset($_COOKIE['remember_me'])){$_SESSION['token']=$_COOKIE['remember_me'];}else{header('Location: login.php');exit;} }
$aq=mysqli_query($conn,"SELECT * FROM login_token WHERE token='".$_SESSION['token']."'");
if(mysqli_num_rows($aq)==0){header('Location: login.php');exit;}
$ad=mysqli_fetch_array($aq); $au=mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM user_data WHERE id='".$ad['user_id']."' AND status='1'"));
if(!in_array($au['type'],["admin","super_admin"])){header('Location: login.php');exit;}

// Delete service
if(isset($_POST['delete'])){
    $did=(int)$_POST['id'];
    mysqli_query($conn,"DELETE FROM service WHERE id='$did'");
    header('Location: show_service'); exit;
}

// Filter by server
$server_id = isset($_GET['server_id']) ? (int)$_GET['server_id'] : 0;
$where = $server_id ? "WHERE server_id='$server_id'" : '';
$sql = mysqli_query($conn, "SELECT * FROM service $where ORDER BY id DESC");
$servers = mysqli_query($conn, "SELECT * FROM otp_server ORDER BY id");
$page_title = 'Services';
?>
<?php include __DIR__.'/include/layout_start.php'; ?>
<div class="page-header">
  <div>
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="dashboard">Dashboard</a></li><li class="breadcrumb-item active">Services</li></ol></nav>
  </div>
  <div class="d-flex gap-2">
    <a href="add_service" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Service</a>
  </div>
</div>

<!-- Filter by server -->
<div class="admin-card mb-3">
  <div class="admin-card-body">
    <form method="get" class="d-flex gap-2 align-items-end flex-wrap">
      <div>
        <label class="form-label">Filter by Server</label>
        <select name="server_id" class="form-select" style="min-width:200px">
          <option value="0">All Servers</option>
          <?php while($s=mysqli_fetch_assoc($servers)): ?>
          <option value="<?=$s['id']?>" <?=$server_id==$s['id']?'selected':''?>><?=htmlspecialchars($s['server_name'])?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <button class="btn btn-primary" type="submit"><i class="bi bi-funnel me-1"></i>Filter</button>
      <?php if($server_id): ?><a href="show_service" class="btn btn-light-action">Clear</a><?php endif; ?>
    </form>
  </div>
</div>

<div class="admin-card">
  <div class="admin-card-body p-0">
    <div class="table-responsive">
      <table class="admin-table admin-datatable" style="width:100%">
        <thead>
          <tr><th>Service Name</th><th>Service ID</th><th>Price</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php while($r=mysqli_fetch_assoc($sql)): ?>
        <tr>
          <td><strong><?=htmlspecialchars(strip_tags($r['service_name']??'-'))?></strong></td>
          <td><code style="background:var(--bg);padding:3px 8px;border-radius:6px;font-size:12px"><?=htmlspecialchars($r['service_id']??'')?></code></td>
          <td><?=isset($r['service_price'])&&$r['service_price']>0?'₦'.number_format($r['service_price']):'<span style="color:var(--text-muted);font-size:12px">Dynamic</span>'?></td>
          <td><span class="status-badge <?=$r['status']=='1'?'badge-active':'badge-blocked'?>"><?=$r['status']=='1'?'Active':'Inactive'?></span></td>
          <td class="d-flex gap-2">
            <a href="edit_service?id=<?=$r['id']?>" class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i></a>
            <form method="post" onsubmit="return confirm('Delete this service?')" style="display:inline">
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
