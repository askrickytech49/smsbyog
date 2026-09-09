<?php
include("auth.php");
if(!isset($_SESSION['token'])){ if(isset($_COOKIE['remember_me'])){$_SESSION['token']=$_COOKIE['remember_me'];}else{header('Location: login.php');exit;} }
$aq=mysqli_query($conn,"SELECT * FROM login_token WHERE token='".$_SESSION['token']."'");
if(mysqli_num_rows($aq)==0){header('Location: login.php');exit;}
$ad=mysqli_fetch_array($aq); $au=mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM user_data WHERE id='".$ad['user_id']."' AND status='1'"));
if(!in_array($au['type'],["admin","super_admin"])){header('Location: login.php');exit;}

$today_sells=mysqli_query($conn,"SELECT service_name,server_id,COUNT(*) as cnt,SUM(service_price) as total FROM active_number WHERE status='1' AND DATE(buy_time)=CURDATE() GROUP BY service_name,server_id ORDER BY cnt DESC");
$active_users=mysqli_query($conn,"SELECT DISTINCT u.name,u.email FROM active_number a JOIN user_data u ON a.user_id=u.id WHERE a.active_status='2' ORDER BY a.id DESC LIMIT 20");
$page_title='Sell History';
?>
<?php include __DIR__.'/include/layout_start.php'; ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-bar-chart-line me-2 text-red"></i>Sell History</h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="dashboard">Dashboard</a></li><li class="breadcrumb-item active">Sell History</li></ol></nav>
  </div>
</div>
<div class="row g-4">
  <div class="col-12 col-lg-7">
    <div class="admin-card">
      <div class="admin-card-header"><h6>Today's Sales by Service</h6></div>
      <div class="admin-card-body p-0">
        <div class="table-responsive">
          <table class="admin-table admin-datatable" style="width:100%">
            <thead><tr><th>Service</th><th>Server</th><th>Count</th><th>Revenue</th></tr></thead>
            <tbody>
            <?php while($r=mysqli_fetch_assoc($today_sells)): ?>
            <tr>
              <td><strong><?=htmlspecialchars($r['service_name']??'-')?></strong></td>
              <td style="font-size:12px;color:var(--text-muted)"><?=htmlspecialchars($r['server_id']??'')?></td>
              <td><span class="status-badge badge-active"><?=$r['cnt']?></span></td>
              <td><strong>₦<?=number_format($r['total']??0)?></strong></td>
            </tr>
            <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-lg-5">
    <div class="admin-card">
      <div class="admin-card-header"><h6>Currently Active Users</h6></div>
      <div class="admin-card-body p-0">
        <div class="table-responsive">
          <table class="admin-table">
            <thead><tr><th>User</th></tr></thead>
            <tbody>
            <?php while($r=mysqli_fetch_assoc($active_users)): ?>
            <tr>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div class="user-avatar"><?=strtoupper(substr($r['name']??'U',0,1))?></div>
                  <div>
                    <div style="font-size:13px;font-weight:600"><?=htmlspecialchars($r['name']??'-')?></div>
                    <div style="font-size:11px;color:var(--text-muted)"><?=htmlspecialchars($r['email']??'')?></div>
                  </div>
                </div>
              </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__.'/include/layout_end.php'; ?>
