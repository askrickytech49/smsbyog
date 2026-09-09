<?php
include("auth.php");
if(!isset($_SESSION['token'])){ if(isset($_COOKIE['remember_me'])){$_SESSION['token']=$_COOKIE['remember_me'];}else{header('Location: login.php');exit;} }
$aq=mysqli_query($conn,"SELECT * FROM login_token WHERE token='".$_SESSION['token']."'");
if(mysqli_num_rows($aq)==0){header('Location: login.php');exit;}
$ad=mysqli_fetch_array($aq); $au=mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM user_data WHERE id='".$ad['user_id']."' AND status='1'"));
if(!in_array($au['type'],["admin","super_admin"])){header('Location: login.php');exit;}

$sql=mysqli_query($conn,"SELECT a.*,u.email FROM active_number a LEFT JOIN user_data u ON a.user_id=u.id WHERE a.status='1' ORDER BY a.id DESC");
$page_title='Number History';
?>
<?php include __DIR__.'/include/layout_start.php'; ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-clock-history me-2 text-red"></i>Number History</h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="dashboard">Dashboard</a></li><li class="breadcrumb-item active">Number History</li></ol></nav>
  </div>
</div>
<div class="admin-card">
  <div class="admin-card-body p-0">
    <div class="table-responsive">
      <table class="admin-table admin-datatable" style="width:100%">
        <thead><tr><th>User</th><th>Number</th><th>Service</th><th>Price</th><th>OTP Code</th><th>Time</th></tr></thead>
        <tbody>
        <?php while($r=mysqli_fetch_assoc($sql)): ?>
        <tr>
          <td style="font-size:12px;color:var(--text-muted)"><?=htmlspecialchars($r['email']??$r['user_id'])?></td>
          <td><strong>+<?=htmlspecialchars($r['number'])?></strong></td>
          <td><?=htmlspecialchars($r['service_name']??$r['service_id'])?></td>
          <td>₦<?=number_format($r['service_price']??0)?></td>
          <td>
            <?php if($r['sms_text']): ?>
              <code style="background:rgba(22,163,74,.1);color:var(--success);padding:3px 8px;border-radius:6px;font-size:13px"><?=htmlspecialchars($r['sms_text'])?></code>
            <?php else: ?>
              <span style="color:var(--text-muted);font-size:12px">—</span>
            <?php endif; ?>
          </td>
          <td style="font-size:12px;color:var(--text-muted)"><?=htmlspecialchars($r['buy_time']??'')?></td>
        </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include __DIR__.'/include/layout_end.php'; ?>
