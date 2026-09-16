<?php
include("auth.php");
if(!isset($_SESSION['token'])){ if(isset($_COOKIE['remember_me'])){$_SESSION['token']=$_COOKIE['remember_me'];}else{header('Location: login.php');exit;} }
$aq=mysqli_query($conn,"SELECT * FROM login_token WHERE token='".$_SESSION['token']."'");
if(mysqli_num_rows($aq)==0){header('Location: login.php');exit;}
$ad=mysqli_fetch_array($aq); $au=mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM user_data WHERE id='".$ad['user_id']."' AND status='1'"));
if(!in_array($au['type'],["admin","super_admin"])){header('Location: login.php');exit;}
$number_visibility = admin_user_visibility_sql($conn, 'a.user_id');

function admin_cancelled_api_source_name($api_id) {
  return [
    8 => 'Tiger SMS',
    2 => '5SIM',
    3 => 'USA + Canada',
    1 => 'USA Only',
  ][(int)$api_id] ?? 'Unknown API';
}

$sql=mysqli_query($conn,"SELECT a.*,u.email FROM active_number a LEFT JOIN user_data u ON a.user_id=u.id WHERE $number_visibility AND a.status='3' ORDER BY a.id DESC");
$page_title='Cancelled Numbers';
?>
<?php include __DIR__.'/include/layout_start.php'; ?>
<div class="page-header">
  <div>
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="dashboard">Dashboard</a></li><li class="breadcrumb-item active">Cancelled Numbers</li></ol></nav>
  </div>
</div>
<div class="admin-card">
  <div class="admin-card-body p-0">
    <div class="table-responsive admin-mobile-table-responsive">
      <table class="admin-table admin-mobile-table admin-datatable" style="width:100%" data-order='[[ 5, "desc" ]]'>
        <thead><tr><th>User</th><th>Number</th><th>Service</th><th>API Source</th><th>Price</th><th>Time</th></tr></thead>
        <tbody>
        <?php while($r=mysqli_fetch_assoc($sql)): ?>
        <tr>
          <td data-label="User" style="font-size:12px;color:var(--text-muted)"><?=htmlspecialchars($r['email']??$r['user_id'])?></td>
          <td data-label="Number"><strong>+<?=htmlspecialchars(ltrim($r['number'], '+'))?></strong></td>
          <td data-label="Service"><?=htmlspecialchars($r['service_name']??$r['service_id'])?></td>
          <td data-label="API Source"><span class="badge bg-light text-dark"><?=htmlspecialchars(admin_cancelled_api_source_name($r['api_id']??0))?></span></td>
          <td data-label="Price">₦<?=number_format($r['service_price']??0)?></td>
          <td data-label="Time" style="font-size:12px;color:var(--text-muted)"><?=htmlspecialchars($r['buy_time']??'')?></td>
        </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include __DIR__.'/include/layout_end.php'; ?>
