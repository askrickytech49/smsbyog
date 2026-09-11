<?php
include("auth.php");
if(!isset($_SESSION['token'])){
    if(isset($_COOKIE['remember_me'])){ $_SESSION['token']=$_COOKIE['remember_me']; }
    else{ header('Location: login.php'); exit; }
}
$admin_sql = mysqli_query($conn,"SELECT * FROM login_token WHERE token='".$_SESSION['token']."'");
if(mysqli_num_rows($admin_sql)==0){ header('Location: login.php'); exit; }
$admin_data = mysqli_fetch_array($admin_sql);
$admin_sql2 = mysqli_query($conn,"SELECT * FROM user_data WHERE id='".$admin_data['user_id']."' AND status='1'");
$final_admin = mysqli_fetch_array($admin_sql2);
if(!in_array($final_admin['type'],["admin","super_admin"])){ header('Location: login.php'); exit; }

// CSV Export
if(isset($_GET['export_users']) && $_GET['export_users']==='csv'){
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=smsbyog_users_'.date('Ymd').'.csv');
    $out = fopen('php://output','w');
    fputcsv($out,['Name','Email','Registered Date','Balance','Total Recharge','OTP Bought','Status']);
    $q = mysqli_query($conn,"SELECT u.*,w.balance,w.total_recharge,w.total_otp FROM user_data u LEFT JOIN user_wallet w ON u.id=w.user_id ORDER BY u.id DESC");
    while($r=mysqli_fetch_assoc($q)){
        fputcsv($out,[$r['name'],$r['email'],$r['register_date'],$r['balance']??0,$r['total_recharge']??0,$r['total_otp']??0,$r['status']==1?'Active':'Blocked']);
    }
    fclose($out); exit;
}

// ── KPI DATA ──────────────────────────────────────────────────────
$total_users  = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM user_data"))[0];
$today_users  = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM user_data WHERE DATE(register_date)=CURDATE()"))[0];
$total_funded = mysqli_fetch_row(mysqli_query($conn,"SELECT IFNULL(SUM(amount),0) FROM user_transaction WHERE status='1'"))[0];
$today_funded = mysqli_fetch_row(mysqli_query($conn,"SELECT IFNULL(SUM(amount),0) FROM user_transaction WHERE status='1' AND DATE(date)=CURDATE()"))[0];
$total_otp    = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM active_number WHERE status='1'"))[0];
$today_otp    = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM active_number WHERE status='1' AND DATE(buy_time)=CURDATE()"))[0];
$total_bal    = mysqli_fetch_row(mysqli_query($conn,"SELECT IFNULL(SUM(w.balance),0) FROM user_wallet w JOIN user_data u ON w.user_id=u.id WHERE u.status='1'"))[0];
$total_pending= mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM user_transaction WHERE status='0'"))[0];

// ── FUNNEL ────────────────────────────────────────────────────────
$funded_users = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(DISTINCT user_id) FROM user_transaction WHERE status='1'"))[0];
$otp_users    = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(DISTINCT user_id) FROM active_number WHERE status='1'"))[0];

// ── 7-DAY REVENUE ─────────────────────────────────────────────────
$rev7_labels=[]; $rev7_data=[];
for($i=6;$i>=0;$i--){
    $d = date('Y-m-d',strtotime("-$i days"));
    $rev7_labels[] = date('M d',strtotime($d));
    $v = mysqli_fetch_row(mysqli_query($conn,"SELECT IFNULL(SUM(amount),0) FROM user_transaction WHERE status='1' AND DATE(date)='$d'"))[0];
    $rev7_data[] = (float)$v;
}

// ── 30-DAY REVENUE ────────────────────────────────────────────────
$rev30_labels=[]; $rev30_data=[];
for($i=29;$i>=0;$i--){
    $d = date('Y-m-d',strtotime("-$i days"));
    $rev30_labels[] = date('M d',strtotime($d));
    $v = mysqli_fetch_row(mysqli_query($conn,"SELECT IFNULL(SUM(amount),0) FROM user_transaction WHERE status='1' AND DATE(date)='$d'"))[0];
    $rev30_data[] = (float)$v;
}

// ── TOP 10 USERS ──────────────────────────────────────────────────
$top10 = mysqli_query($conn,"SELECT u.name,u.email,w.balance,w.total_recharge FROM user_data u JOIN user_wallet w ON u.id=w.user_id ORDER BY w.total_recharge DESC LIMIT 10");

$page_title = 'Dashboard';
?>
<?php include __DIR__.'/include/layout_start.php'; ?>

<!-- Page Header -->
<div class="page-header">
  <div>
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item active">Dashboard</li></ol></nav>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <?php if($total_pending>0): ?>
    <a href="transactions" class="btn btn-sm btn-warning d-flex align-items-center gap-1">
      <i class="bi bi-clock"></i> <?=$total_pending?> Pending
    </a>
    <?php endif; ?>
    <a href="?export_users=csv" class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1">
      <i class="bi bi-download"></i> Export Users
    </a>
  </div>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">
    <div class="kpi-card">
      <div class="kpi-icon blue"><i class="bi bi-people-fill"></i></div>
      <div class="kpi-info">
        <div class="kpi-label">Total Users</div>
        <div class="kpi-value"><?=number_format($total_users)?></div>
        <div class="kpi-sub">+<?=$today_users?> today</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="kpi-card">
      <div class="kpi-icon green"><i class="bi bi-cash-coin"></i></div>
      <div class="kpi-info">
        <div class="kpi-label">Total Funded</div>
        <div class="kpi-value">₦<?=number_format($total_funded)?></div>
        <div class="kpi-sub">₦<?=number_format($today_funded)?> today</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="kpi-card">
      <div class="kpi-icon red"><i class="bi bi-phone"></i></div>
      <div class="kpi-info">
        <div class="kpi-label">OTP Sold</div>
        <div class="kpi-value"><?=number_format($total_otp)?></div>
        <div class="kpi-sub"><?=$today_otp?> today</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="kpi-card">
      <div class="kpi-icon orange"><i class="bi bi-wallet2"></i></div>
      <div class="kpi-info">
        <div class="kpi-label">User Balances</div>
        <div class="kpi-value">₦<?=number_format($total_bal)?></div>
        <div class="kpi-sub">active wallets</div>
      </div>
    </div>
  </div>
</div>

<!-- Charts -->
<div class="row g-3 mb-4">
  <div class="col-12 col-lg-7">
    <div class="admin-card h-100">
      <div class="admin-card-header">
        <h6><i class="bi bi-graph-up-arrow me-2 text-red"></i>7-Day Revenue</h6>
      </div>
      <div class="admin-card-body">
        <div class="chart-container" style="height:260px">
          <canvas id="rev7Chart"></canvas>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-lg-5">
    <div class="admin-card h-100">
      <div class="admin-card-header">
        <h6><i class="bi bi-bar-chart-fill me-2 text-red"></i>User Funnel</h6>
      </div>
      <div class="admin-card-body">
        <div class="d-flex flex-column gap-4 pt-2">
          <?php
          $funnel = [
            ['label'=>'Registered','value'=>$total_users,'icon'=>'bi-person-plus','color'=>'blue'],
            ['label'=>'Funded Wallet','value'=>$funded_users,'icon'=>'bi-credit-card','color'=>'green'],
            ['label'=>'Bought OTP','value'=>$otp_users,'icon'=>'bi-phone','color'=>'red'],
          ];
          foreach($funnel as $f): $pct = $total_users>0?round(($f['value']/$total_users)*100):0; ?>
          <div>
            <div class="d-flex justify-content-between mb-2">
              <span style="font-size:14px;font-weight:700;"><i class="bi <?=$f['icon']?> me-1"></i><?=$f['label']?></span>
              <span style="font-size:13px;color:var(--text-muted);font-weight:600;"><?=number_format($f['value'])?> <small>(<?=$pct?>%)</small></span>
            </div>
            <div style="background:var(--bg);border-radius:999px;height:10px;overflow:hidden;">
              <div class="funnel-bar" data-pct="<?=$pct?>"
                   style="width:0%;height:100%;background:var(--red);border-radius:999px;transition:width 1s cubic-bezier(.4,0,.2,1);"></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- 30-Day Revenue + Top Users -->
<div class="row g-3 mb-4">
  <div class="col-12 col-lg-8">
    <div class="admin-card">
      <div class="admin-card-header">
        <h6><i class="bi bi-calendar3 me-2 text-red"></i>30-Day Revenue</h6>
      </div>
      <div class="admin-card-body">
        <div class="chart-container" style="height:260px">
          <canvas id="rev30Chart"></canvas>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-lg-4">
    <div class="admin-card h-100">
      <div class="admin-card-header">
        <h6><i class="bi bi-trophy me-2 text-red"></i>Top 10 Users</h6>
      </div>
      <div class="admin-card-body p-0">
        <div style="overflow-y:auto;max-height:280px;">
          <table class="admin-table">
            <thead><tr><th>#</th><th>User</th><th>Recharged</th></tr></thead>
            <tbody>
            <?php $rank=1; while($row=mysqli_fetch_assoc($top10)): ?>
            <tr>
              <td><span class="rank-badge <?=$rank<=3?'top':''?>"><?=$rank?></span></td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div class="user-avatar"><?=strtoupper(substr($row['name']??'U',0,1))?></div>
                  <div style="min-width:0">
                    <div style="font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:110px"><?=htmlspecialchars($row['name']??'-')?></div>
                    <div style="font-size:11px;color:var(--text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:110px"><?=htmlspecialchars($row['email']??'')?></div>
                  </div>
                </div>
              </td>
              <td><strong>₦<?=number_format($row['total_recharge']??0)?></strong></td>
            </tr>
            <?php $rank++; endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Chart.js Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const red = '#e10700';

  // ── 7-Day Revenue Chart ──────────────────────────────────────
  new Chart(document.getElementById('rev7Chart'), {
    type: 'line',
    data: {
      labels: <?=json_encode($rev7_labels)?>,
      datasets: [{
        data: <?=json_encode($rev7_data)?>,
        borderColor: red,
        backgroundColor: 'rgba(225,7,0,.08)',
        fill: true,
        tension: .4,
        pointRadius: 5,
        pointBackgroundColor: red,
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
        borderWidth: 2.5
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      animation: { duration: 1000, easing: 'easeInOutQuart' },
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: '#0f172a',
          titleColor: '#94a3b8',
          bodyColor: '#fff',
          padding: 10,
          cornerRadius: 8,
          callbacks: { label: ctx => ' ₦' + Number(ctx.raw).toLocaleString() }
        }
      },
      scales: {
        x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#94a3b8' } },
        y: { grid: { color: '#f1f5f9', drawBorder: false }, ticks: { font: { size: 11 }, color: '#94a3b8', callback: v => '₦' + Number(v).toLocaleString() } }
      }
    }
  });

  // ── 30-Day Revenue Chart ─────────────────────────────────────
  new Chart(document.getElementById('rev30Chart'), {
    type: 'bar',
    data: {
      labels: <?=json_encode($rev30_labels)?>,
      datasets: [{
        data: <?=json_encode($rev30_data)?>,
        backgroundColor: 'rgba(225,7,0,.75)',
        hoverBackgroundColor: red,
        borderRadius: 5,
        borderSkipped: false
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      animation: { duration: 1000, easing: 'easeInOutQuart' },
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: '#0f172a',
          titleColor: '#94a3b8',
          bodyColor: '#fff',
          padding: 10,
          cornerRadius: 8,
          callbacks: { label: ctx => ' ₦' + Number(ctx.raw).toLocaleString() }
        }
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: {
            display: true,
            maxRotation: 45,
            minRotation: 45,
            font: { size: 9 },
            color: '#94a3b8',
            autoSkip: true,
            maxTicksLimit: 10
          }
        },
        y: { grid: { color: '#f1f5f9', drawBorder: false }, ticks: { font: { size: 11 }, color: '#94a3b8', callback: v => '₦' + Number(v).toLocaleString() } }
      }
    }
  });

  // ── Funnel progress bar animation ────────────────────────────
  setTimeout(function() {
    document.querySelectorAll('.funnel-bar').forEach(function(bar) {
      bar.style.width = bar.dataset.pct + '%';
    });
  }, 300);
});
</script>

<?php include __DIR__.'/include/layout_end.php'; ?>
